<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
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
*/

const TABS = [
    { key: 'service',    label: 'Dienstleistungen', type: 'service' },
    { key: 'design',     label: 'Muster',           type: 'design' },
    { key: 'effekt',     label: 'Effekte',          type: 'option' },
    { key: 'verzierung', label: 'Verzierungen',     type: 'option' },
    { key: 'laenge',     label: 'Länge',            type: 'option' },
    { key: 'material',   label: 'Material',         type: 'option' },
]

const activeTab = ref('service')

const currentTab = computed(() => TABS.find((tab) => tab.key === activeTab.value))

function rowsFor(tabKey) {

    if (!props.prices) return []

    if (tabKey === 'service') return props.prices.services ?? []
    if (tabKey === 'design')  return props.prices.designs ?? []

    return props.prices.options?.[tabKey] ?? []

}

function countFor(tabKey) {

    return rowsFor(tabKey).length

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
                        <p>
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


                <!-- Suche (nur bei vielen Einträgen sinnvoll) -->
                <input
                    v-if="rowsFor(activeTab).length > 8"
                    v-model="suche"
                    type="search"
                    class="search"
                    :placeholder="`${currentTab.label} durchsuchen`"
                    aria-label="Suchen"
                >


                <!-- Tabelle -->
                <div
                    v-if="visibleRows.length"
                    class="table-wrap"
                >
                    <table class="table">

                        <thead>
                            <tr>
                                <th>Bezeichnung</th>
                                <th>Einheit</th>
                                <th
                                    v-if="activeTab !== 'service'"
                                    class="right"
                                >
                                    Standard
                                </th>
                                <th class="right">Preis in diesem Studio</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="row in visibleRows"
                                :key="row.id"
                            >

                                <td>
                                    <span class="name">{{ row.name }}</span>
                                    <span
                                        v-if="row.category && activeTab === 'design'"
                                        class="sub"
                                    >
                                        {{ row.category }}
                                    </span>
                                    <span
                                        v-if="row.duration_minutes"
                                        class="sub"
                                    >
                                        {{ row.duration_minutes }} Min.
                                    </span>
                                </td>

                                <td class="muted">{{ unitLabel(row.unit) }}</td>

                                <td
                                    v-if="activeTab !== 'service'"
                                    class="right muted"
                                >
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

                <p class="legend">
                    <span class="legend-dot"></span>
                    Eigener Preis dieses Studios (weicht vom Standard ab)
                </p>

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