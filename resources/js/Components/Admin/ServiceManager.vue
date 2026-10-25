<script setup>
import { ref, watch } from 'vue'

// Leistungen eines Studios verwalten (Preisverwaltung der Inhaberin)

const props = defineProps({
    studioId: { type: Number, required: true },
    // true = eingebettet in eine andere Karte (z. B. Preisverwaltung): ohne eigenen Rahmen und Titel
    embedded: { type: Boolean, default: false },
})

// Nach Anlegen, Ändern oder Löschen – damit die Seite drumherum z. B. Zähler aktualisieren kann
const emit = defineEmits(['changed'])

const services = ref([])
const loading = ref(false)
const message = ref('')
const errors = ref({})

// null = Formular zu, 'new' = neue Leistung, Zahl = id der Leistung, die bearbeitet wird
const editingId = ref(null)
const form = ref(emptyForm())
const saving = ref(false)

function emptyForm() {
    return {
        name: '',
        category: '',
        description: '',
        price: '',
        price_is_from: false,
        duration_minutes: 60,
        is_addon: false,
        is_popular: false,
        is_active: true,
        sort_order: 0,
    }
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

async function request(url, method = 'GET', body = null) {
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: body ? JSON.stringify(body) : null,
    })

    const data = await response.json().catch(() => ({}))

    return { ok: response.ok, status: response.status, data }
}

async function load() {
    loading.value = true
    message.value = ''

    const { ok, data } = await request(`/admin/services?nail_studio_id=${props.studioId}`)

    loading.value = false

    if (!ok) {
        message.value = 'Die Leistungen konnten nicht geladen werden.'
        return
    }

    services.value = data.services
}

watch(() => props.studioId, load, { immediate: true })

// Vorschläge für das Kategorie-Feld
function categorySuggestions() {
    return [...new Set(services.value.map(service => service.category).filter(Boolean))]
}

function startCreate() {
    form.value = emptyForm()
    form.value.sort_order = (services.value.at(-1)?.sort_order ?? 0) + 10
    errors.value = {}
    editingId.value = 'new'
}

function startEdit(service) {
    form.value = { ...service, description: service.description ?? '' }
    errors.value = {}
    editingId.value = service.id
}

function cancel() {
    editingId.value = null
    errors.value = {}
}

async function save() {
    saving.value = true
    errors.value = {}
    message.value = ''

    const isNew = editingId.value === 'new'

    const { ok, status, data } = isNew
        ? await request('/admin/services', 'POST', { ...form.value, nail_studio_id: props.studioId })
        : await request(`/admin/services/${editingId.value}`, 'PATCH', form.value)

    saving.value = false

    if (status === 422) {
        errors.value = data.errors ?? {}
        return
    }

    if (!ok) {
        message.value = data.message ?? 'Die Leistung konnte nicht gespeichert werden.'
        return
    }

    editingId.value = null
    message.value = isNew ? 'Leistung angelegt.' : 'Änderungen gespeichert.'
    await load()
    emit('changed')
}

async function remove(service) {
    if (!confirm(`„${service.name}“ wirklich löschen?`)) {
        return
    }

    const { ok, data } = await request(`/admin/services/${service.id}`, 'DELETE')

    if (!ok) {
        message.value = data.message ?? 'Die Leistung konnte nicht gelöscht werden.'
        return
    }

    message.value = data.archived ? data.message : 'Leistung gelöscht.'
    await load()
    emit('changed')
}

const euro = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' })
</script>

<template>
    <section class="service-manager" :class="{ embedded }">
        <div class="manager-head">
            <h2 v-if="!embedded">Leistungen</h2>
            <button
                v-if="editingId === null"
                type="button"
                class="primary"
                @click="startCreate"
            >
                Leistung hinzufügen
            </button>
        </div>

        <p v-if="message" class="message" role="status">{{ message }}</p>

        <!-- Formular für neue oder bestehende Leistung -->
        <form v-if="editingId !== null" class="service-form" @submit.prevent="save">
            <h3>{{ editingId === 'new' ? 'Neue Leistung' : 'Leistung bearbeiten' }}</h3>

            <div class="grid">
                <label>
                    Name
                    <input v-model.trim="form.name" required maxlength="100">
                    <small v-if="errors.name" class="error">{{ errors.name[0] }}</small>
                </label>

                <label>
                    Kategorie
                    <input v-model.trim="form.category" list="service-categories" required maxlength="50">
                    <datalist id="service-categories">
                        <option v-for="category in categorySuggestions()" :key="category" :value="category" />
                    </datalist>
                    <small v-if="errors.category" class="error">{{ errors.category[0] }}</small>
                </label>

                <label class="wide">
                    Beschreibung
                    <textarea v-model.trim="form.description" rows="2" maxlength="500" />
                    <small v-if="errors.description" class="error">{{ errors.description[0] }}</small>
                </label>

                <label>
                    Preis in €
                    <input v-model="form.price" type="number" min="0" step="0.01" required>
                    <small v-if="errors.price" class="error">{{ errors.price[0] }}</small>
                </label>

                <label>
                    Dauer in Minuten
                    <input v-model.number="form.duration_minutes" type="number" min="0" max="600" required>
                    <small v-if="errors.duration_minutes" class="error">{{ errors.duration_minutes[0] }}</small>
                </label>

                <label>
                    Reihenfolge
                    <input v-model.number="form.sort_order" type="number" min="0">
                </label>
            </div>

            <div class="checks">
                <label><input v-model="form.price_is_from" type="checkbox"> „ab“-Preis</label>
                <label><input v-model="form.is_addon" type="checkbox"> Zusatzleistung (nicht einzeln buchbar)</label>
                <label><input v-model="form.is_popular" type="checkbox"> Als „Beliebt“ markieren</label>
                <label><input v-model="form.is_active" type="checkbox"> Auf der Startseite anzeigen</label>
            </div>

            <p class="hint">Bei Zusatzleistungen bedeutet Dauer 0 „Je nach Aufwand“.</p>

            <div class="actions">
                <button type="submit" class="primary" :disabled="saving">
                    {{ saving ? 'Wird gespeichert …' : 'Speichern' }}
                </button>
                <button type="button" class="secondary" @click="cancel">Abbrechen</button>
            </div>
        </form>

        <p v-if="loading" class="message">Leistungen werden geladen …</p>

        <p v-else-if="services.length === 0" class="message">
            Noch keine Leistungen. Lege die erste mit „Leistung hinzufügen“ an.
        </p>

        <div v-else class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Kategorie</th>
                        <th>Preis</th>
                        <th>Dauer</th>
                        <th>Status</th>
                        <th><span class="sr-only">Aktionen</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="service in services" :key="service.id" :class="{ inactive: !service.is_active }">
                        <td>
                            {{ service.name }}
                            <span v-if="service.is_popular" class="tag">Beliebt</span>
                            <span v-if="service.is_addon" class="tag">Zusatz</span>
                        </td>
                        <td>{{ service.category }}</td>
                        <td>{{ service.price_is_from ? 'ab ' : '' }}{{ euro.format(service.price) }}</td>
                        <td>{{ service.duration_minutes ? `${service.duration_minutes} Min.` : 'Je nach Aufwand' }}</td>
                        <td>{{ service.is_active ? 'Sichtbar' : 'Ausgeblendet' }}</td>
                        <td class="row-actions">
                            <button type="button" class="link" @click="startEdit(service)">Bearbeiten</button>
                            <button type="button" class="link danger" @click="remove(service)">Löschen</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style scoped>
.service-manager {
    padding: 1.5rem;
    border: 1px solid #f0e3e7;
    border-radius: 14px;
    background: #ffffff;
    color: #3d2930;
}

.service-manager.embedded {
    padding: 0;
    border: none;
    border-radius: 0;
}

.manager-head {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.manager-head h2 {
    margin-right: auto;
}

.manager-head h2,
.service-form h3 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-weight: 500;
}

.message {
    margin: 0.5rem 0 1rem;
    color: #806c73;
    font-size: 0.9rem;
}

.service-form {
    margin-bottom: 1.5rem;
    padding: 1.25rem;
    border-radius: 10px;
    background: #fff6f8;
}

.grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.9rem;
    margin-top: 1rem;
}

.grid label {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    font-size: 0.85rem;
}

.grid .wide {
    grid-column: 1 / -1;
}

input,
textarea {
    padding: 0.5rem 0.7rem;
    border: 1px solid #e6d2d8;
    border-radius: 8px;
    font: inherit;
}

.checks {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1.5rem;
    margin-top: 1rem;
    font-size: 0.85rem;
}

.hint {
    margin: 0.75rem 0 0;
    color: #8b7b80;
    font-size: 0.8rem;
}

.error {
    color: #9b2c3c;
}

.actions {
    display: flex;
    gap: 0.6rem;
    margin-top: 1rem;
}

button {
    font: inherit;
    cursor: pointer;
}

.primary {
    padding: 0.55rem 1.1rem;
    border: none;
    border-radius: 8px;
    background: #8f5364;
    color: #ffffff;
}

.primary:disabled {
    opacity: 0.6;
    cursor: default;
}

.secondary {
    padding: 0.55rem 1.1rem;
    border: 1px solid #e6d2d8;
    border-radius: 8px;
    background: #ffffff;
    color: #754353;
}

.link {
    padding: 0;
    border: none;
    background: none;
    color: #8f5364;
    text-decoration: underline;
}

.link.danger {
    color: #9b2c3c;
}

button:focus-visible,
input:focus-visible,
textarea:focus-visible {
    outline: 2px solid #8f5364;
    outline-offset: 2px;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

th,
td {
    padding: 0.7rem 0.5rem;
    border-bottom: 1px solid #f3e8eb;
    text-align: left;
    white-space: nowrap;
}

th {
    color: #8b7b80;
    font-weight: 500;
}

tr.inactive td {
    color: #b3a3a8;
}

.tag {
    margin-left: 0.4rem;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    background: #fbe9ee;
    color: #8f5364;
    font-size: 0.7rem;
}

.row-actions {
    display: flex;
    gap: 0.9rem;
    justify-content: flex-end;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
}

@media (max-width: 700px) {
    .grid {
        grid-template-columns: 1fr;
    }
}
</style>