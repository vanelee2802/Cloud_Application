<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ServiceManager from '@/Components/Admin/ServiceManager.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps({
    studios:          { type: Array,  default: () => [] },
    selectedStudioId: { type: Number, default: null },
    prices:           { type: Object, default: null },
})


/*
|--------------------------------------------------------------------------
| STUDIO WECHSELN
|--------------------------------------------------------------------------
*/

const studioId = ref(props.selectedStudioId)

function changeStudio() {

    router.get('/admin/preise', { studio: studioId.value }, {
        preserveScroll: true,
    })

}


/*
|--------------------------------------------------------------------------
| REITER
|--------------------------------------------------------------------------
| type = was beim Speichern an den Server geht (service | design | option)
| Dienstleistungen werden über den ServiceManager gepflegt (eigene Ansicht).
*/

// catalog = Art beim Anlegen/Entfernen (null = nicht anlegbar)
const TABS = [
    { key: 'service',    label: 'Dienstleistungen', type: 'service', catalog: null },
    { key: 'design',     label: 'Muster',           type: 'design',  catalog: 'muster' },
    { key: 'farbe',      label: 'Farben',           type: null,      catalog: 'farbe' },
    { key: 'effekt',     label: 'Effekte',          type: 'option',  catalog: 'effekt' },
    { key: 'verzierung', label: 'Verzierungen',     type: 'option',  catalog: 'verzierung' },
    { key: 'laenge',     label: 'Länge',            type: 'option',  catalog: null },
    { key: 'material',   label: 'Material',         type: 'option',  catalog: 'material' },
]

// Kategorien für neue Muster und Farben
const MUSTER_KATEGORIEN = ['French', 'Muster', 'Motiv']
const FARB_KATEGORIEN   = ['rot', 'rosa', 'blau', 'grün', 'gelb', 'orange', 'lila', 'braun', 'schwarz', 'weiß', 'nude', 'sonstige']

const activeTab = ref('service')

const currentTab = computed(() => TABS.find((tab) => tab.key === activeTab.value))

function rowsFor(tabKey) {

    if (!props.prices) return []

    if (tabKey === 'service') return props.prices.services ?? []
    if (tabKey === 'design')  return props.prices.designs ?? []
    if (tabKey === 'farbe')   return props.prices.colors ?? []

    return props.prices.options?.[tabKey] ?? []

}

function countFor(tabKey) {

    return rowsFor(tabKey).length

}

// NEU: Nach Änderungen im ServiceManager die Zähler in den Reitern aktualisieren
function reloadPrices() {

    router.reload({ only: ['prices'], preserveScroll: true })

}


/*
|--------------------------------------------------------------------------
| SUCHE (vor allem für die vielen Muster)
|--------------------------------------------------------------------------
*/

const suche = ref('')

const visibleRows = computed(() => {

    const begriff = suche.value.toLowerCase().trim()
    const rows    = rowsFor(activeTab.value)

    if (!begriff) return rows

    return rows.filter((row) =>
        [row.name, row.category].some((text) =>
            String(text ?? '').toLowerCase().includes(begriff)
        )
    )

})

watch(activeTab, () => {
    suche.value = ''
})


/*
|--------------------------------------------------------------------------
| EINGABEN
|--------------------------------------------------------------------------
| Werte werden lokal als Text gehalten (z. B. "12,50") und erst
| beim Verlassen des Feldes oder mit Enter gespeichert.
*/

const inputs = ref({})
const status = ref({})   // pro Zeile: 'saving' | 'saved' | Fehlermeldung

function rowKey(type, id) {

    return `${type}-${id}`

}

function formatInput(value) {

    return Number(value ?? 0).toFixed(2).replace('.', ',')

}

// Eingabefelder mit den aktuellen Preisen füllen
function fillInputs() {

    const result = {}

    for (const tab of TABS) {
        if (!tab.type) continue

        for (const row of rowsFor(tab.key)) {
            result[rowKey(tab.type, row.id)] = formatInput(row.price)
        }
    }

    inputs.value = result

}

fillInputs()

// Nach dem Speichern bzw. beim Studiowechsel neu füllen
watch(() => props.prices, fillInputs)

watch(() => props.selectedStudioId, (id) => {
    studioId.value = id
    status.value   = {}
})


function parsePrice(text) {

    const cleaned = String(text ?? '').replace('€', '').replace(/\s/g, '').replace(',', '.')

    return cleaned === '' ? null : Number(cleaned)

}

function save(row, type, price) {

    const key = rowKey(type, row.id)

    status.value[key] = 'saving'

    router.put(`/admin/preise/${props.selectedStudioId}`, {
        type,
        id: row.id,
        price,
    }, {
        preserveScroll: true,
        preserveState:  true,
        only: ['prices'],

        onSuccess: () => {
            status.value[key] = 'saved'

            setTimeout(() => {
                if (status.value[key] === 'saved') {
                    delete status.value[key]
                }
            }, 1800)
        },

        onError: (errors) => {
            status.value[key] = errors.price || 'Konnte nicht gespeichert werden.'
        },
    })

}

// Beim Verlassen des Feldes / Enter
function commit(row) {

    const type  = currentTab.value.type
    const key   = rowKey(type, row.id)
    const value = parsePrice(inputs.value[key])

    // Ungültige Eingabe
    if (value !== null && (Number.isNaN(value) || value < 0)) {
        status.value[key] = 'Bitte gib einen gültigen Preis ein.'
        return
    }

    // Leeres Feld bei Dienstleistung ist nicht erlaubt
    if (value === null && type === 'service') {
        status.value[key] = 'Bitte gib einen Preis ein.'
        return
    }

    // Nichts geändert → nicht speichern
    if (value !== null && Math.abs(value - Number(row.price)) < 0.005) {
        inputs.value[key] = formatInput(row.price)
        delete status.value[key]
        return
    }

    save(row, type, value)

}

// Eigenen Preis entfernen → Standardpreis gilt wieder
function resetToDefault(row) {

    save(row, currentTab.value.type, null)

}


/*
|--------------------------------------------------------------------------
| EIGENE EINTRÄGE ANLEGEN / ENTFERNEN
|--------------------------------------------------------------------------
| Muster, Effekte und Verzierungen brauchen ein Bild (SVG, PNG, WebP),
| damit sie direkt auf dem Nagel und in den Vorlagen funktionieren.
*/

const showForm  = ref(false)
const fileInput = ref(null)

const newItem = useForm({
    type:     '',
    name:     '',
    price:    '',
    category: '',
    hex_code: '#B0122E',
    image:    null,
    tags:     '',
})

const needsImage = computed(() =>
    ['muster', 'effekt', 'verzierung'].includes(currentTab.value.catalog)
)

function openForm() {

    newItem.reset()
    newItem.clearErrors()

    newItem.type     = currentTab.value.catalog
    newItem.category = currentTab.value.catalog === 'muster' ? 'Muster'
        : currentTab.value.catalog === 'farbe' ? 'rot'
        : ''

    if (fileInput.value) fileInput.value.value = ''

    showForm.value = true

}

function onFile(event) {

    newItem.image = event.target.files?.[0] ?? null

}

function submitNew() {

    newItem
        .transform((data) => ({
            ...data,
            price: data.price === '' ? null : String(data.price).replace(',', '.'),
        }))
        .post(`/admin/katalog/${props.selectedStudioId}`, {
            forceFormData:  true,
            preserveScroll: true,
            only: ['prices', 'flash'],
            onSuccess: () => {
                showForm.value = false
                newItem.reset()
            },
        })

}

function removeItem(row) {

    if (!window.confirm(`„${row.name}“ wirklich entfernen?`)) return

    router.delete(`/admin/katalog/${props.selectedStudioId}/${currentTab.value.catalog}/${row.id}`, {
        preserveScroll: true,
        only: ['prices', 'flash'],
    })

}

watch(activeTab, () => {
    showForm.value = false
})


/*
|--------------------------------------------------------------------------
| ANZEIGE
|--------------------------------------------------------------------------
*/

function euro(value) {

    return Number(value ?? 0).toLocaleString('de-DE', {
        style: 'currency',
        currency: 'EUR',
    })

}

function unitLabel(unit) {

    return unit === 'nagel' ? 'pro Nagel' : 'pauschal'

}

const selectedStudioName = computed(() =>
    props.studios.find((studio) => studio.id === props.selectedStudioId)?.name ?? ''
)
</script>


<template>
    <Head title="Preise" />

    <AuthenticatedLayout>

        <template #header>
            <h2 class="page-title">Preise</h2>
        </template>

        <div class="page">

            <!-- Kein Studio vorhanden -->
            <section
                v-if="!studios.length"
                class="card empty-state"
            >
                <strong>Noch kein Studio zugeordnet</strong>
                <span>Sobald dir ein Studio gehört, kannst du hier die Preise festlegen.</span>
            </section>

            <section
                v-else
                class="card"
            >

                <!-- Kopf: Studio-Auswahl -->
                <div class="card-header">

                    <div>
                        <h3>Preise für {{ selectedStudioName }}</h3>
                        <p v-if="activeTab === 'service'">
                            Diese Leistungen erscheinen auf der Leistungsseite und können dort gebucht werden.
                            Zusatzleistungen werden angezeigt, sind aber nicht einzeln buchbar.
                        </p>
                        <p v-else>
                            Leere Felder bei Mustern und Optionen bedeuten: Es gilt der Standardpreis.
                            Änderungen werden automatisch gespeichert, sobald du das Feld verlässt.
                        </p>
                    </div>

                    <label class="studio-select">
                        <span>Studio</span>
                        <select
                            v-model="studioId"
                            @change="changeStudio"
                        >
                            <option
                                v-for="studio in studios"
                                :key="studio.id"
                                :value="studio.id"
                            >
                                {{ studio.name }}
                            </option>
                        </select>
                    </label>

                </div>


                <!-- Reiter -->
                <div
                    class="tabs"
                    role="tablist"
                >
                    <button
                        v-for="tab in TABS"
                        :key="tab.key"
                        type="button"
                        role="tab"
                        class="tab"
                        :class="{ active: activeTab === tab.key }"
                        :aria-selected="activeTab === tab.key"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                        <span class="tab-count">{{ countFor(tab.key) }}</span>
                    </button>
                </div>


                <!-- NEU: Dienstleistungen anlegen, bearbeiten, löschen -->
                <ServiceManager
                    v-if="activeTab === 'service' && selectedStudioId"
                    :studio-id="selectedStudioId"
                    embedded
                    @changed="reloadPrices"
                />

                <template v-else>

                    <!-- Suche + Neu-Button -->
                    <div class="toolbar">

                        <input
                            v-if="rowsFor(activeTab).length > 8"
                            v-model="suche"
                            type="search"
                            class="search"
                            :placeholder="`${currentTab.label} durchsuchen`"
                            aria-label="Suchen"
                        >

                        <button
                            v-if="currentTab.catalog && !showForm"
                            type="button"
                            class="add-button"
                            @click="openForm"
                        >
                            + {{ currentTab.label }} hinzufügen
                        </button>

                    </div>


                    <!-- Neuer Eintrag -->
                    <form
                        v-if="showForm"
                        class="new-form"
                        @submit.prevent="submitNew"
                    >

                        <div class="form-grid">

                            <label class="field">
                                <span>Name</span>
                                <input v-model="newItem.name" type="text" maxlength="100" required>
                                <small v-if="newItem.errors.name" class="field-error">{{ newItem.errors.name }}</small>
                            </label>

                            <label
                                v-if="currentTab.catalog !== 'farbe'"
                                class="field"
                            >
                                <span>Preis (€){{ currentTab.catalog === 'verzierung' || currentTab.catalog === 'muster' ? ' pro Nagel' : '' }}</span>
                                <input v-model="newItem.price" type="text" inputmode="decimal" placeholder="z. B. 2,50" required>
                                <small v-if="newItem.errors.price" class="field-error">{{ newItem.errors.price }}</small>
                            </label>

                            <label
                                v-if="currentTab.catalog === 'muster'"
                                class="field"
                            >
                                <span>Kategorie</span>
                                <select v-model="newItem.category">
                                    <option v-for="kategorie in MUSTER_KATEGORIEN" :key="kategorie" :value="kategorie">
                                        {{ kategorie }}
                                    </option>
                                </select>
                            </label>

                            <label
                                v-if="currentTab.catalog === 'muster'"
                                class="field"
                            >
                                <span>
                                    Tags
                                    <em class="hint">mit Komma getrennt, z. B. herbst, gold, elegant</em>
                                </span>
                                <input v-model="newItem.tags" type="text" maxlength="255" placeholder="herbst, gold">
                                <small v-if="newItem.errors.tags" class="field-error">{{ newItem.errors.tags }}</small>
                            </label>

                            <template v-if="currentTab.catalog === 'farbe'">

                                <label class="field">
                                    <span>Farbgruppe (für den Filter)</span>
                                    <select v-model="newItem.category">
                                        <option v-for="kategorie in FARB_KATEGORIEN" :key="kategorie" :value="kategorie">
                                            {{ kategorie }}
                                        </option>
                                    </select>
                                </label>

                                <label class="field">
                                    <span>Farbe</span>
                                    <span class="color-row">
                                        <input v-model="newItem.hex_code" type="color" class="color-picker">
                                        <input v-model="newItem.hex_code" type="text" maxlength="7" class="hex-input">
                                    </span>
                                    <small v-if="newItem.errors.hex_code" class="field-error">{{ newItem.errors.hex_code }}</small>
                                </label>

                            </template>

                            <label
                                v-if="currentTab.catalog !== 'farbe'"
                                class="field field-wide"
                            >
                                <span>
                                    Bild {{ needsImage ? '' : '(optional)' }}
                                    <em class="hint">SVG, PNG oder WebP, max. 2 MB, transparenter Hintergrund, Hochformat 100 × 160</em>
                                </span>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept=".svg,.png,.webp,image/svg+xml,image/png,image/webp"
                                    :required="needsImage"
                                    @change="onFile"
                                >
                                <small v-if="newItem.errors.image" class="field-error">{{ newItem.errors.image }}</small>
                            </label>

                        </div>

                        <div class="form-actions">
                            <button type="button" class="cancel-button" @click="showForm = false">Abbrechen</button>
                            <button type="submit" class="save-button" :disabled="newItem.processing">
                                {{ newItem.processing ? 'Speichert …' : 'Anlegen' }}
                            </button>
                        </div>

                    </form>


                    <!-- Farben: Raster statt Preistabelle -->
                    <div
                        v-if="activeTab === 'farbe' && visibleRows.length"
                        class="color-grid"
                    >
                        <div
                            v-for="row in visibleRows"
                            :key="row.id"
                            class="color-card"
                        >
                            <span class="swatch" :style="{ backgroundColor: row.hex }"></span>
                            <span class="color-text">
                                <span class="name">{{ row.name }}</span>
                                <span class="sub">{{ row.category }} · {{ row.hex }}</span>
                            </span>
                            <button
                                v-if="row.is_own"
                                type="button"
                                class="remove-button"
                                :aria-label="`${row.name} entfernen`"
                                @click="removeItem(row)"
                            >
                                ×
                            </button>
                        </div>
                    </div>

                    <!-- Tabelle -->
                    <div
                        v-else-if="visibleRows.length"
                        class="table-wrap"
                    >
                        <table class="table">

                            <thead>
                                <tr>
                                    <th>Bezeichnung</th>
                                    <th>Einheit</th>
                                    <th class="right">Standard</th>
                                    <th class="right">Preis in diesem Studio</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="row in visibleRows"
                                    :key="row.id"
                                >

                                    <td class="name-cell">
                                        <img
                                            v-if="row.image"
                                            :src="row.image"
                                            alt=""
                                            class="thumb"
                                        >
                                        <span>
                                            <span class="name">
                                                {{ row.name }}
                                                <span v-if="row.is_own" class="own-badge">eigener Eintrag</span>
                                            </span>
                                            <span
                                                v-if="row.category && activeTab === 'design'"
                                                class="sub"
                                            >
                                                {{ row.category }}<template v-if="row.tags?.length"> · {{ row.tags.join(', ') }}</template>
                                            </span>
                                        </span>
                                    </td>

                                    <td class="muted">{{ unitLabel(row.unit) }}</td>

                                    <td class="right muted">
                                        {{ euro(row.default_price) }}
                                    </td>

                                    <td class="right">
                                        <div class="price-field">
                                            <input
                                                v-model="inputs[rowKey(currentTab.type, row.id)]"
                                                type="text"
                                                inputmode="decimal"
                                                class="price-input"
                                                :class="{ custom: row.is_custom }"
                                                :aria-label="`Preis für ${row.name}`"
                                                @blur="commit(row)"
                                                @keydown.enter.prevent="$event.target.blur()"
                                            >
                                            <span class="euro">€</span>
                                        </div>
                                    </td>

                                    <td class="status-cell">

                                        <span
                                            v-if="status[rowKey(currentTab.type, row.id)] === 'saving'"
                                            class="status muted"
                                        >
                                            Speichert …
                                        </span>

                                        <span
                                            v-else-if="status[rowKey(currentTab.type, row.id)] === 'saved'"
                                            class="status saved"
                                        >
                                            ✓ Gespeichert
                                        </span>

                                        <span
                                            v-else-if="status[rowKey(currentTab.type, row.id)]"
                                            class="status error"
                                        >
                                            {{ status[rowKey(currentTab.type, row.id)] }}
                                        </span>

                                        <button
                                            v-else-if="row.is_custom"
                                            type="button"
                                            class="reset-button"
                                            :title="`Zurück auf Standard (${euro(row.default_price)})`"
                                            @click="resetToDefault(row)"
                                        >
                                            Standard
                                        </button>

                                        <button
                                            v-if="row.is_own && currentTab.catalog"
                                            type="button"
                                            class="remove-button"
                                            :aria-label="`${row.name} entfernen`"
                                            title="Entfernen"
                                            @click="removeItem(row)"
                                        >
                                            ×
                                        </button>

                                    </td>

                                </tr>
                            </tbody>

                        </table>
                    </div>

                    <p
                        v-else
                        class="empty-row"
                    >
                        {{ rowsFor(activeTab).length ? 'Keine Treffer.' : 'Hier gibt es noch keine Einträge.' }}
                    </p>

                    <p
                        v-if="activeTab !== 'farbe'"
                        class="legend"
                    >
                        <span class="legend-dot"></span>
                        Eigener Preis dieses Studios (weicht vom Standard ab)
                    </p>

                </template>

            </section>

        </div>

    </AuthenticatedLayout>
</template>


<style scoped>

.page-title {
    margin: 0;
    color: #4f3540;
    font-size: 1.25rem;
    font-weight: 600;
}

.page {
    max-width: 64rem;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.card {
    padding: 1.5rem;
    background: #ffffff;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 12px;
    box-shadow:
        0 4px 12px rgba(80, 35, 48, 0.06),
        0 8px 20px rgba(80, 35, 48, 0.04);
}

.card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 1.25rem;
}

.card-header h3 {
    margin: 0;
    color: #4f3540;
    font-size: 1.15rem;
    font-weight: 700;
}

.card-header p {
    max-width: 34rem;
    margin: 0.35rem 0 0;
    color: #8a7a80;
    font-size: 0.8rem;
    line-height: 1.5;
}

.studio-select {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex-shrink: 0;
    color: #8a7a80;
    font-size: 0.75rem;
}

.studio-select select {
    min-width: 13rem;
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    border: 1px solid rgba(143, 83, 100, 0.25);
    border-radius: 8px;
    background-color: #fdfbfc;
    color: #4f3540;
    font-size: 0.9rem;
}


/* Reiter */
.tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 1rem;
    padding-bottom: 0.9rem;
    border-bottom: 1px solid rgba(143, 83, 100, 0.12);
}

.tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.85rem;
    border: 1px solid #e4e4e4;
    border-radius: 100vw;
    background: #ffffff;
    color: #555555;
    font-size: 0.82rem;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease;
}

.tab:hover {
    background: #f8f0f2;
    border-color: #c9aab1;
    color: #7d1024;
}

.tab.active {
    background: #7d1024;
    border-color: #7d1024;
    color: #ffffff;
}

.tab-count {
    min-width: 1.2rem;
    padding: 0 0.3rem;
    border-radius: 100vw;
    background: rgba(0, 0, 0, 0.06);
    font-size: 0.7rem;
    text-align: center;
}

.tab.active .tab-count {
    background: rgba(255, 255, 255, 0.22);
}


/* Suche */
.search {
    width: 100%;
    max-width: 20rem;
    margin-bottom: 0.9rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 8px;
    background: #fdfbfc;
    color: #4f3540;
    font-size: 0.85rem;
    box-sizing: border-box;
}

.search:focus {
    outline: none;
    border-color: #8f5364;
}


/* Tabelle */
.table-wrap {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
}

.table th {
    padding: 0.6rem;
    border-bottom: 1px solid rgba(143, 83, 100, 0.2);
    color: #8a7a80;
    font-size: 0.75rem;
    font-weight: 600;
    text-align: left;
    white-space: nowrap;
}

.table td {
    padding: 0.55rem 0.6rem;
    border-bottom: 1px solid rgba(143, 83, 100, 0.08);
    color: #4f3540;
    vertical-align: middle;
}

.right {
    text-align: right;
}

.muted {
    color: #8a7a80;
}

.name {
    display: block;
    font-weight: 600;
}

.sub {
    display: block;
    color: #a8999d;
    font-size: 0.72rem;
}


/* Preis-Eingabe */
.price-field {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.price-input {
    width: 6rem;
    padding: 0.4rem 0.55rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 7px;
    background: #fdfbfc;
    color: #4f3540;
    font: inherit;
    text-align: right;
}

.price-input:focus {
    outline: none;
    border-color: #7d1024;
    background: #ffffff;
}

/* Eigener Preis des Studios */
.price-input.custom {
    border-color: #7d1024;
    background: #f8f0f2;
    font-weight: 600;
}

.euro {
    color: #8a7a80;
}


/* Status rechts */
.status-cell {
    width: 9rem;
    white-space: nowrap;
}

.status {
    font-size: 0.75rem;
}

.status.saved {
    color: #477052;
}

.status.error {
    color: #a04646;
    white-space: normal;
}

.reset-button {
    padding: 0.3rem 0.6rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 6px;
    background: #ffffff;
    color: #8f5364;
    font-size: 0.72rem;
    cursor: pointer;
}

.reset-button:hover {
    background: #f5e7eb;
}


/* Hinweise */
.empty-row {
    margin: 1.5rem 0;
    color: #8a7a80;
    text-align: center;
}

.legend {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin: 1rem 0 0;
    color: #8a7a80;
    font-size: 0.75rem;
}

.legend-dot {
    width: 0.8rem;
    height: 0.8rem;
    border: 1px solid #7d1024;
    border-radius: 3px;
    background: #f8f0f2;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding: 3rem 1.5rem;
    color: #8a7a80;
    text-align: center;
}

.empty-state strong {
    color: #4f3540;
}



/* ========================================= */
/* EIGENE EINTRÄGE                            */
/* ========================================= */

.toolbar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.9rem;
}

.toolbar .search {
    margin-bottom: 0;
}

.add-button {
    margin-left: auto;
    padding: 0.5rem 0.9rem;
    border: 1px dashed #8f5364;
    border-radius: 8px;
    background: #fdf7f8;
    color: #7d1024;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
}

.add-button:hover {
    background: #f5e7eb;
}

.new-form {
    margin-bottom: 1.1rem;
    padding: 1rem 1.1rem;
    border: 1px solid rgba(143, 83, 100, 0.18);
    border-radius: 10px;
    background: #fdfbfc;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
    gap: 0.8rem 1rem;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    color: #4f3540;
    font-size: 0.78rem;
    font-weight: 600;
}

.field-wide {
    grid-column: 1 / -1;
}

.field input[type='text'],
.field select {
    padding: 0.45rem 0.6rem;
    border: 1px solid rgba(143, 83, 100, 0.25);
    border-radius: 7px;
    background: #ffffff;
    color: #4f3540;
    font: inherit;
    font-weight: 400;
}

.field input[type='file'] {
    font-weight: 400;
}

.hint {
    display: block;
    margin-top: 0.15rem;
    color: #8a7a80;
    font-size: 0.72rem;
    font-style: normal;
    font-weight: 400;
}

.field-error {
    color: #a04646;
    font-weight: 400;
}

.color-row {
    display: flex;
    gap: 0.5rem;
}

.color-picker {
    width: 2.6rem;
    height: 2.2rem;
    padding: 0;
    border: 1px solid rgba(143, 83, 100, 0.25);
    border-radius: 7px;
    background: none;
    cursor: pointer;
}

.hex-input {
    width: 6rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.9rem;
}

.cancel-button,
.save-button {
    padding: 0.5rem 1rem;
    border-radius: 7px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
}

.cancel-button {
    border: 1px solid rgba(143, 83, 100, 0.25);
    background: #ffffff;
    color: #8f5364;
}

.save-button {
    border: none;
    background: #7d1024;
    color: #ffffff;
}

.save-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.name-cell {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.thumb {
    width: 1.6rem;
    height: 2.4rem;
    flex-shrink: 0;
    object-fit: cover;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 50% 50% 42% 42% / 62% 62% 38% 38%;
    background: #f3dcd0;
}

.own-badge {
    margin-left: 0.35rem;
    padding: 0.05rem 0.45rem;
    border-radius: 100vw;
    background: #f3e5e8;
    color: #754354;
    font-size: 0.65rem;
    font-weight: 600;
    vertical-align: middle;
}

.remove-button {
    width: 1.7rem;
    height: 1.7rem;
    margin-left: 0.3rem;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #a08890;
    font-size: 1.1rem;
    line-height: 1;
    cursor: pointer;
}

.remove-button:hover {
    background: #f5e7eb;
    color: #7d1024;
}

/* Farben als Raster */
.color-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
    gap: 0.6rem;
}

.color-card {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.55rem 0.65rem;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 9px;
    background: #fdfbfc;
}

.swatch {
    width: 1.8rem;
    height: 1.8rem;
    flex-shrink: 0;
    border: 1px solid rgba(0, 0, 0, 0.12);
    border-radius: 50%;
}

.color-text {
    display: flex;
    flex: 1;
    min-width: 0;
    flex-direction: column;
}


@media (max-width: 768px) {

    .card-header {
        flex-direction: column;
    }

    .studio-select,
    .studio-select select {
        width: 100%;
    }

}

</style>