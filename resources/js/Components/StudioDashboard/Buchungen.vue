<script setup>

import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Bestätigte Buchungen vom Server
const bookings = ref([])
const loading = ref(true)
const error = ref('')

// NEU: Rückmeldung nach „Abgeschlossen“
const message = ref('')
const busyId = ref(null)

// NEU: Abschließen darf nur die Schichtleitung, alle anderen sehen nur die Liste
const page = usePage()
const canComplete = computed(() => page.props.auth?.user?.role === 'admin')

// Aktuelle Uhrzeit, wird beim Neuladen aktualisiert → Button erscheint, sobald ein Termin begonnen hat
const now = ref(new Date())

// Anfangs nur die nächsten 5 zeigen, per Button alle
const showAll = ref(false)
const visibleBookings = computed(() =>
    showAll.value ? bookings.value : bookings.value.slice(0, 5)
)

let timer = null

async function load() {
    now.value = new Date()

    const response = await fetch('/admin/appointments/bookings', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
    })

    loading.value = false

    if (!response.ok) {
        // GEÄNDERT: Mitarbeiterinnen dürfen die Liste jetzt auch sehen
        error.value = response.status === 401 || response.status === 403
            ? 'Du hast keinen Zugriff auf die Buchungen dieses Studios.'
            : 'Die Buchungen konnten nicht geladen werden.'
        return
    }

    error.value = ''
    bookings.value = await response.json()
}

// Nur den Datumsteil, egal ob "2026-10-20" oder "2026-10-20 00:00:00"
function dateOnly(date) {
    return String(date ?? '').slice(0, 10)
}

// "2026-10-20" → "20"
function day(date) {
    return dateOnly(date).split('-')[2]
}

// "2026-10-20" → "OKT"
const months = ['JAN', 'FEB', 'MÄR', 'APR', 'MAI', 'JUN', 'JUL', 'AUG', 'SEP', 'OKT', 'NOV', 'DEZ']

function month(date) {
    return months[Number(dateOnly(date).split('-')[1]) - 1]
}

// Name aus dem Termin, sonst aus dem Kundenkonto (alte Termine)
function customerName(booking) {
    return booking.customer_name || booking.user?.name || 'Unbekannt'
}

// NEU: Hat der Termin schon begonnen?
function hasStarted(booking) {
    const start = new Date(`${dateOnly(booking.date)}T${String(booking.time).slice(0, 5)}:00`)

    return start <= now.value
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

// NEU: Termin abschließen → Kundin bekommt die Danke-Mail mit Link zur Bewertung
async function complete(booking) {
    const confirmed = window.confirm(
        `Termin von ${customerName(booking)} um ${String(booking.time).slice(0, 5)} Uhr als abgeschlossen markieren?\n\n`
        + 'Die Kundin bekommt dann eine Danke-Mail mit Link zur Bewertung.'
    )

    if (!confirmed) {
        return
    }

    busyId.value = booking.id
    message.value = ''

    try {
        const response = await fetch(`/admin/appointments/${booking.id}/complete`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        })

        const data = await response.json().catch(() => ({}))

        message.value = data.message ?? (response.ok
            ? 'Termin abgeschlossen.'
            : 'Der Termin konnte nicht abgeschlossen werden.')

        // Abgeschlossen (oder schon vorher) → aus der Liste nehmen
        if (response.ok || response.status === 409) {
            bookings.value = bookings.value.filter(item => item.id !== booking.id)
        }
    } catch {
        message.value = 'Server nicht erreichbar. Bitte versuche es erneut.'
    } finally {
        busyId.value = null
    }
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
                <h2>Aktuelle Buchungen</h2>
                <p>Bestätigte Termine ab heute</p>
            </div>

            <button
                v-if="bookings.length > 5"
                class="secondary-button"
                @click="showAll = !showAll"
            >
                {{ showAll ? 'Weniger anzeigen' : 'Alle anzeigen' }}
            </button>

        </div>


        <!-- NEU: Rückmeldung nach „Abgeschlossen“ -->
        <p v-if="message" class="message" role="status">{{ message }}</p>


        <!-- Hinweise -->
        <p v-if="error" class="hint error">{{ error }}</p>
        <p v-else-if="loading" class="hint">Buchungen werden geladen …</p>
        <p v-else-if="bookings.length === 0" class="hint">Noch keine bestätigten Buchungen.</p>


        <!-- Buchungen -->
        <div v-else class="appointment-list">

            <div
                v-for="booking in visibleBookings"
                :key="booking.id"
                class="appointment"
            >

                <div class="appointment-date">
                    <strong>{{ day(booking.date) }}</strong>
                    <span>{{ month(booking.date) }}</span>
                </div>

                <div class="appointment-info">
                    <strong>{{ customerName(booking) }}</strong>

                    <span>
                        {{ booking.service?.name }} · {{ String(booking.time).slice(0, 5) }} Uhr
                    </span>
                </div>

                <!-- NEU: Schichtleitung kann laufende/vergangene Termine abschließen -->
                <button
                    v-if="canComplete && hasStarted(booking)"
                    type="button"
                    class="complete-button"
                    :disabled="busyId === booking.id"
                    @click="complete(booking)"
                >
                    {{ busyId === booking.id ? 'Speichert …' : 'Abgeschlossen' }}
                </button>

                <span v-else class="status confirmed">
                    Bestätigt
                </span>

            </div>

        </div>

    </section>

</template>


<style scoped>

/* =========================================================
   AKTUELLE BUCHUNGEN
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

.message {
    margin: 0 0 1rem;

    padding: 0.55rem 0.75rem;

    border-radius: 8px;

    background: rgba(184, 115, 131, 0.08);

    font-size: 0.85rem;
}


/* =========================================================
   BUTTONS
   ========================================================= */

.secondary-button {
    padding: 0.5rem 0.8rem;

    border: none;
    border-radius: 7px;

    background: rgba(184, 115, 131, 0.08);

    color: var(--text-color);

    cursor: pointer;

    transition: 0.2s;
}

.secondary-button:hover {
    opacity: 0.8;
}

.complete-button {
    flex-shrink: 0;

    padding: 0.4rem 0.75rem;

    border: none;
    border-radius: 20px;

    background: #7d1024;

    color: white;

    font: inherit;
    font-size: 0.75rem;
    font-weight: 600;

    cursor: pointer;
}

.complete-button:hover:not(:disabled) {
    background: #64101f;
}

.complete-button:disabled {
    opacity: 0.6;

    cursor: default;
}

.secondary-button:focus-visible,
.complete-button:focus-visible {
    outline: 2px solid #7d1024;

    outline-offset: 2px;
}


/* =========================================================
   BUCHUNGEN
   ========================================================= */

.appointment-list {
    display: flex;

    flex-direction: column;
}

.appointment {
    display: flex;

    align-items: center;

    gap: 1rem;

    padding: 1rem 0;

    border-top: 1px solid rgba(109, 59, 71, 0.08);
}


/* =========================================================
   DATUM
   ========================================================= */

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


/* =========================================================
   BUCHUNGSINFORMATIONEN
   ========================================================= */

.appointment-info {
    display: flex;

    flex-direction: column;

    flex: 1;
}

.appointment-info span {
    margin-top: 0.2rem;

    font-size: 0.8rem;

    opacity: 0.6;
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

.status.confirmed {
    background: #e2f4e8;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {

    .appointment {
        align-items: flex-start;
    }

    .appointment .status,
    .appointment .complete-button {
        margin-left: auto;
    }

    .card-header {
        align-items: flex-start;

        gap: 1rem;
    }

}

</style>