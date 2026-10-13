<script setup>

import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

// Bestätigte Buchungen vom Server
const bookings = ref([])
const loading = ref(true)
const error = ref('')

// Anfangs nur die nächsten 5 zeigen, per Button alle
const showAll = ref(false)
const visibleBookings = computed(() =>
    showAll.value ? bookings.value : bookings.value.slice(0, 5)
)

let timer = null

async function load() {
    const response = await fetch('/admin/appointments/bookings', {
        headers: { Accept: 'application/json' },
    })

    loading.value = false

    if (!response.ok) {
        error.value = response.status === 401 || response.status === 403
            ? 'Nur die Studioleitung kann die Buchungen sehen.'
            : 'Die Buchungen konnten nicht geladen werden.'
        return
    }

    error.value = ''
    bookings.value = await response.json()
}

// "2026-10-20" → "20"
function day(date) {
    return date.split('-')[2]
}

// "2026-10-20" → "OKT"
const months = ['JAN', 'FEB', 'MÄR', 'APR', 'MAI', 'JUN', 'JUL', 'AUG', 'SEP', 'OKT', 'NOV', 'DEZ']

function month(date) {
    return months[Number(date.split('-')[1]) - 1]
}

// Name aus dem Termin, sonst aus dem Kundenkonto (alte Termine)
function customerName(booking) {
    return booking.customer_name || booking.user?.name || 'Unbekannt'
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
                        {{ booking.service?.name }} · {{ booking.time.slice(0, 5) }} Uhr
                    </span>
                </div>

                <span class="status confirmed">
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


/* =========================================================
   BUTTON
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

    .appointment .status {
        margin-left: auto;
    }

    .card-header {
        align-items: flex-start;

        gap: 1rem;
    }

}

</style>