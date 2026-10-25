<script setup>

import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

// Offene Anfragen vom Server
const requests = ref([])
const loading = ref(true)
const error = ref('')

// Hinweis nach einer Aktion
const flash = ref('')
const flashIsError = ref(false)

// ID der Anfrage, die gerade bearbeitet wird (Buttons sperren)
const busyId = ref(null)

let timer = null

// Heute als YYYY-MM-DD
const today = new Date().toLocaleDateString('sv-SE')


// ---------- Anfragen laden ----------

async function load() {
    const response = await fetch('/admin/appointments/requests', {
        headers: { Accept: 'application/json' },
    })

    loading.value = false

    if (!response.ok) {
        error.value = response.status === 401 || response.status === 403
            ? 'Nur die Studioleitung kann Anfragen bearbeiten.'
            : 'Die Anfragen konnten nicht geladen werden.'
        return
    }

    error.value = ''
    requests.value = await response.json()
}


// ---------- Aktionen an den Server schicken ----------

async function send(request, action, body = null) {
    busyId.value = request.id
    flash.value = ''

    const response = await fetch(`/admin/appointments/${request.id}/${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: body ? JSON.stringify(body) : null,
    })

    const data = await response.json()
    busyId.value = null

    flash.value = data.message ?? (response.ok ? 'Erledigt.' : 'Das hat leider nicht geklappt.')
    flashIsError.value = !response.ok

    // Bei Erfolg das Absage-Feld schließen
    if (response.ok) {
        openId.value = null
    }

    await load()
}

// Annehmen → Kundin bekommt Zahlungslink per E-Mail
function confirmRequest(request) {
    send(request, 'confirm')
}


// ---------- Absage-Feld mit Alternativtermin ----------

// Bei welcher Anfrage ist das Absage-Feld offen?
const openId = ref(null)

const altDate = ref('')
const altTime = ref('')
const altTimes = ref([])   // freie Plätze am gewählten Tag
const note = ref('')

// Absage-Feld öffnen oder schließen
function togglePanel(request) {
    if (openId.value === request.id) {
        openId.value = null
        return
    }

    openId.value = request.id
    altDate.value = ''
    altTime.value = ''
    altTimes.value = []
    note.value = ''
}

// Datum gewählt → freie Uhrzeiten laden, wie im Checkout
watch(altDate, async (date) => {
    altTime.value = ''
    altTimes.value = []

    if (!date || openId.value === null) return

    const request = requests.value.find((r) => r.id === openId.value)

       const response = await fetch(`/appointments/availability?nail_studio_id=${request.nail_studio_id}&date=${date}&service_id=${request.service_id ?? ''}`, {
        headers: { Accept: 'application/json' },
    })

    if (response.ok) {
        altTimes.value = (await response.json()).times
    }
})

// Ist die Uhrzeit wählbar? Frei UND (bei heute) noch nicht vorbei
function isBookable(slot) {
    if (slot.free === 0) return false

    if (altDate.value === today) {
        const now = new Date().toTimeString().slice(0, 5)   // z. B. "14:35"
        return slot.time > now
    }

    return true
}

// Absagen MIT einem Alternativtermin
function sendAlternative(request) {
    send(request, 'alternatives', {
        alternatives: [{ date: altDate.value, time: altTime.value }],
        note: note.value || null,
    })
}

// Absagen OHNE Alternativtermin
function declineWithout(request) {
    if (!confirm('Termin wirklich ohne Alternativvorschlag absagen?')) return

    send(request, 'decline', { note: note.value || null })
}


// ---------- Anzeige ----------

// "2026-10-20" → "20"
function day(date) {
    return date.split('-')[2]
}

// "2026-10-20" → "OKT"
const months = ['JAN', 'FEB', 'MÄR', 'APR', 'MAI', 'JUN', 'JUL', 'AUG', 'SEP', 'OKT', 'NOV', 'DEZ']

function month(date) {
    return months[Number(date.split('-')[1]) - 1]
}

onMounted(() => {
    load()
    timer = setInterval(load, 30000)   // alle 30 Sekunden neu laden
})

onBeforeUnmount(() => {
    clearInterval(timer)
})

</script>

<template>

    <section class="dashboard-card">

        <!-- Überschrift -->
        <div class="card-header">

            <div>
                <h2>Neue Anfragen</h2>
                <p>Termine für heute – bitte bearbeiten</p>
            </div>

            <span v-if="requests.length" class="count">
                {{ requests.length }}
            </span>

        </div>


        <!-- Hinweis nach einer Aktion -->
        <p v-if="flash" class="flash" :class="{ 'flash-error': flashIsError }">
            {{ flash }}
        </p>


        <!-- Hinweise -->
        <p v-if="error" class="hint error">{{ error }}</p>
        <p v-else-if="loading" class="hint">Anfragen werden geladen …</p>
        <p v-else-if="requests.length === 0" class="hint">Keine offenen Anfragen.</p>


        <!-- Anfragen -->
        <div v-else class="appointment-list">

            <div
                v-for="request in requests"
                :key="request.id"
                class="appointment"
            >

                <div class="appointment-date">
                    <strong>{{ day(request.date) }}</strong>
                    <span>{{ month(request.date) }}</span>
                </div>

                <div class="appointment-info">
                    <strong>{{ request.customer_name || 'Unbekannt' }}</strong>

                    <span>
                        {{ request.service?.name }} · {{ request.time.slice(0, 5) }} Uhr
                    </span>

                    <span v-if="request.customer_email">
                        {{ request.customer_email }}
                    </span>

                    <!-- Aktionen -->
                    <div class="actions">
                        <button
                            class="primary-button"
                            :disabled="busyId === request.id"
                            @click="confirmRequest(request)"
                        >
                            Annehmen
                        </button>

                        <button
                            class="secondary-button"
                            :disabled="busyId === request.id"
                            @click="togglePanel(request)"
                        >
                            {{ openId === request.id ? 'Schließen' : 'Absagen' }}
                        </button>
                    </div>

                    <!-- Absage-Feld mit Alternativtermin -->
                    <div v-if="openId === request.id" class="decline-panel">

                        <p class="panel-title">Alternativtermin vorschlagen</p>

                        <label :for="`alt-date-${request.id}`">Datum</label>
                        <input
                            :id="`alt-date-${request.id}`"
                            type="date"
                            :min="today"
                            v-model="altDate"
                        >

                        <label :for="`alt-time-${request.id}`">Uhrzeit</label>
                        <select
                            :id="`alt-time-${request.id}`"
                            v-model="altTime"
                            :disabled="!altDate"
                        >
                            <option value="">Uhrzeit auswählen</option>

                            <option
                                v-for="slot in altTimes"
                                :key="slot.time"
                                :value="slot.time"
                                :disabled="!isBookable(slot)"
                            >
                                {{ slot.time }}{{ isBookable(slot) ? '' : ' – nicht verfügbar' }}
                            </option>
                        </select>

                        <label :for="`alt-note-${request.id}`">Nachricht an die Kundin (optional)</label>
                        <textarea
                            :id="`alt-note-${request.id}`"
                            v-model="note"
                            rows="2"
                            placeholder="z. B. Heute sind wir leider ausgebucht."
                        ></textarea>

                        <div class="actions">
                            <button
                                class="primary-button"
                                :disabled="!altDate || !altTime || busyId === request.id"
                                @click="sendAlternative(request)"
                            >
                                Alternative vorschlagen
                            </button>

                            <button
                                class="secondary-button"
                                :disabled="busyId === request.id"
                                @click="declineWithout(request)"
                            >
                                Ohne Alternative absagen
                            </button>
                        </div>

                    </div>
                </div>

                <span class="status pending">
                    Anfrage
                </span>

            </div>

        </div>

    </section>

</template>


<style scoped>

/* =========================================================
   KARTE
   ========================================================= */

.dashboard-card {
    padding: 1.5rem;

    background: white;

    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);

    box-sizing: border-box;
}


/* =========================================================
   CARD HEADER
   ========================================================= */

.card-header {
    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 1.5rem;
}

.card-header h2 {
    margin: 0;

    font-size: 1.2rem;
}

.card-header p {
    margin: 0.3rem 0 0;

    font-size: 0.85rem;

    opacity: 0.6;
}

.count {
    min-width: 28px;

    padding: 0.3rem 0.6rem;

    border-radius: 20px;

    background: #fff3cd;

    font-size: 0.85rem;

    text-align: center;
}


/* =========================================================
   HINWEISE
   ========================================================= */

.hint {
    margin: 0;

    font-size: 0.9rem;

    opacity: 0.7;
}

.hint.error {
    color: #b42318;

    opacity: 1;
}

.flash {
    margin: 0 0 1rem;

    padding: 0.7rem 0.9rem;

    border-radius: 8px;

    background: #e2f4e8;

    font-size: 0.85rem;
}

.flash.flash-error {
    background: #fdecea;

    color: #b42318;
}


/* =========================================================
   ANFRAGEN
   ========================================================= */

.appointment-list {
    display: flex;

    flex-direction: column;
}

.appointment {
    display: flex;

    align-items: flex-start;

    gap: 1rem;

    padding: 1rem 0;

    border-top: 1px solid rgba(109, 59, 71, 0.08);
}

.appointment-date {
    width: 45px;

    text-align: center;
}

.appointment-date strong {
    display: block;

    font-size: 1.2rem;
}

.appointment-date span {
    font-size: 0.7rem;

    opacity: 0.5;
}

.appointment-info {
    display: flex;

    flex-direction: column;

    flex: 1;
}

.appointment-info > span {
    margin-top: 0.2rem;

    font-size: 0.8rem;

    opacity: 0.6;
}


/* =========================================================
   ABSAGE-FELD
   ========================================================= */

.decline-panel {
    display: flex;

    flex-direction: column;

    gap: 0.4rem;

    margin-top: 1rem;
    padding: 1rem;

    border-radius: 8px;

    background: rgba(184, 115, 131, 0.06);
}

.panel-title {
    margin: 0 0 0.4rem;

    font-weight: 600;
}

.decline-panel label {
    margin-top: 0.4rem;

    font-size: 0.8rem;

    opacity: 0.7;
}

.decline-panel input,
.decline-panel select,
.decline-panel textarea {
    padding: 0.5rem;

    border: 1px solid rgba(109, 59, 71, 0.2);
    border-radius: 6px;

    font: inherit;
}


/* =========================================================
   BUTTONS
   ========================================================= */

.actions {
    display: flex;

    flex-wrap: wrap;

    gap: 0.5rem;

    margin-top: 0.8rem;
}

.primary-button,
.secondary-button {
    padding: 0.5rem 0.8rem;

    border: none;
    border-radius: 7px;

    cursor: pointer;

    transition: 0.2s;
}

.primary-button {
    background: rgba(109, 59, 71, 0.9);

    color: white;
}

.secondary-button {
    background: rgba(184, 115, 131, 0.08);

    color: var(--text-color);
}

.primary-button:hover,
.secondary-button:hover {
    opacity: 0.8;
}

button:disabled {
    opacity: 0.5;

    cursor: not-allowed;
}


/* =========================================================
   STATUS
   ========================================================= */

.status {
    padding: 0.35rem 0.7rem;

    border-radius: 20px;

    font-size: 0.75rem;
}

.status.pending {
    background: #fff3cd;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {

    .appointment .status {
        margin-left: auto;
    }

}

</style>