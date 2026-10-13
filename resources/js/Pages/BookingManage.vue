<template>
    <div class="booking-page">

        <div class="booking-card">

            <h1>Dein Termin</h1>

            <span class="status" :class="`status-${appointment.status}`">
                {{ statusLabel }}
            </span>


            <!-- Termindaten -->
            <div class="details">

                <div class="detail-row">
                    <span>Service</span>
                    <strong>{{ appointment.service }}</strong>
                </div>

                <div class="detail-row">
                    <span>Preis</span>
                    <strong>{{ formatPrice(appointment.price) }}</strong>
                </div>

                <div class="detail-row">
                    <span>{{ isOffer ? 'Dein Wunschtermin' : 'Termin' }}</span>
                    <strong :class="{ crossed: isOffer }">
                        {{ formatDate(appointment.date) }} um {{ appointment.time }} Uhr
                    </strong>
                </div>

            </div>


            <!-- Nachricht vom Studio -->
            <div v-if="appointment.admin_note" class="note">
                <span>Nachricht vom Studio</span>
                <p>{{ appointment.admin_note }}</p>
            </div>


            <!-- Anfrage für heute, Studioleitung hat noch nicht entschieden -->
            <div v-if="appointment.status === 'requested'" class="info-box">
                Das Studio prüft gerade, ob dein Termin heute möglich ist.
                Wir melden uns per E-Mail. Bezahlt wird erst nach der Bestätigung.
            </div>


            <!-- Bestätigt, Zahlung offen -->
            <template v-else-if="appointment.status === 'awaiting_payment'">
                <div class="info-box">
                    Dein Termin ist bestätigt. Bitte bezahle bis
                    <strong>{{ deadline }} Uhr</strong>,
                    sonst wird er wieder freigegeben.
                </div>

                <button class="primary-button" :disabled="loading" @click="post(payUrl)">
                    {{ loading ? 'Einen Moment …' : 'Jetzt bezahlen' }}
                </button>
            </template>


            <!-- Studioleitung hat einen Ersatztermin angeboten -->
            <template v-else-if="isOffer">

                <h2>Unser Vorschlag</h2>

                <p class="muted">
                    Dein Wunschtermin ist leider nicht möglich.
                    Wir können dir stattdessen diesen Termin anbieten:
                </p>

                <div
                    v-for="alt in alternatives"
                    :key="alt.id"
                    class="offer"
                >
                    <div class="offer-date">
                        <strong>{{ day(alt.date) }}</strong>
                        <span>{{ month(alt.date) }}</span>
                    </div>

                    <div class="offer-info">
                        <strong>{{ weekday(alt.date) }}, {{ formatDate(alt.date) }}</strong>
                        <span>{{ alt.time }} Uhr · {{ appointment.service }}</span>
                    </div>

                    <button
                        class="primary-button"
                        :disabled="loading"
                        @click="post(alt.acceptUrl)"
                    >
                        {{ loading ? 'Einen Moment …' : 'Annehmen und bezahlen' }}
                    </button>
                </div>

                <p class="other-option">
                    Passt dir der Vorschlag nicht?
                    <Link href="/">Dann buche einfach online einen neuen Termin.</Link>
                </p>

            </template>


            <!-- Bezahlt und gebucht -->
            <div v-else-if="appointment.status === 'confirmed'" class="success-box">
                Dein Termin ist fest gebucht. Wir freuen uns auf dich!
            </div>


            <!-- Abgesagt, abgelaufen, storniert -->
            <template v-else>
                <p class="muted">Dieser Termin ist nicht mehr aktiv.</p>

                <Link href="/" class="primary-button">
                    Neuen Termin buchen
                </Link>
            </template>

        </div>

    </div>
</template>


<script setup>

import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    appointment: Object,
    payUrl: String,
    alternatives: Array,
})

const loading = ref(false)

// Wurde ein Ersatztermin angeboten?
const isOffer = computed(() => props.appointment.status === 'alternatives_offered')

// Deutscher Text zum Status
const statusLabels = {
    requested: 'Anfrage wird geprüft',
    awaiting_payment: 'Bestätigt – Zahlung offen',
    alternatives_offered: 'Ersatztermin verfügbar',
    confirmed: 'Gebucht',
    rejected: 'Abgesagt',
    expired: 'Abgelaufen',
    cancelled: 'Storniert',
}

const statusLabel = computed(() =>
    statusLabels[props.appointment.status] ?? props.appointment.status
)

// Uhrzeit der Zahlungsfrist, z. B. "15:30"
const deadline = computed(() => {
    if (!props.appointment.payment_expires_at) return ''
    return new Date(props.appointment.payment_expires_at)
        .toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })
})

// "2026-10-21" → "21.10.2026"
function formatDate(date) {
    const [year, month, day] = date.split('-')
    return `${day}.${month}.${year}`
}

// "2026-10-21" → "21"
function day(date) {
    return date.split('-')[2]
}

// "2026-10-21" → "OKT"
const months = ['JAN', 'FEB', 'MÄR', 'APR', 'MAI', 'JUN', 'JUL', 'AUG', 'SEP', 'OKT', 'NOV', 'DEZ']

function month(date) {
    return months[Number(date.split('-')[1]) - 1]
}

// "2026-10-21" → "Mittwoch"
function weekday(date) {
    return new Date(`${date}T12:00:00`).toLocaleDateString('de-DE', { weekday: 'long' })
}

// "25.00" → "25,00 €"
function formatPrice(price) {
    return Number(price).toFixed(2).replace('.', ',') + ' €'
}

// Button-Klick: POST an die signierte Adresse, danach zu Stripe
async function post(url) {
    loading.value = true

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    })

    const data = await response.json()

    if (response.ok && data.checkout_url) {
        window.location.href = data.checkout_url
        return
    }

    loading.value = false
    alert(data.message ?? 'Das hat leider nicht geklappt.')

    // Seite mit dem aktuellen Stand neu laden (z. B. wenn der Vorschlag inzwischen vergeben ist)
    router.reload()
}

</script>


<style scoped>

/* =========================================================
   SEITE UND KARTE
   ========================================================= */

.booking-page {
    display: flex;

    justify-content: center;

    padding: 4rem 1rem;
}

.booking-card {
    width: 100%;
    max-width: 520px;

    padding: 2.5rem;

    background: white;

    border-radius: 16px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.08);

    box-sizing: border-box;
}

.booking-card h1 {
    margin: 0 0 0.8rem;
}

.booking-card h2 {
    margin: 2rem 0 0.4rem;

    font-size: 1.2rem;
}

.muted {
    margin: 0 0 1rem;

    font-size: 0.9rem;

    opacity: 0.7;
}


/* =========================================================
   STATUS
   ========================================================= */

.status {
    display: inline-block;

    padding: 0.35rem 0.8rem;

    border-radius: 20px;

    font-size: 0.8rem;
}

.status-requested,
.status-awaiting_payment,
.status-alternatives_offered {
    background: #fff3cd;
}

.status-confirmed {
    background: #e2f4e8;
}

.status-rejected,
.status-expired,
.status-cancelled {
    background: #fdecea;

    color: #b42318;
}


/* =========================================================
   TERMINDATEN
   ========================================================= */

.details {
    margin-top: 1.8rem;

    border-top: 1px solid rgba(109, 59, 71, 0.08);
}

.detail-row {
    display: flex;

    justify-content: space-between;

    gap: 1rem;

    padding: 0.8rem 0;

    border-bottom: 1px solid rgba(109, 59, 71, 0.08);
}

.detail-row span {
    font-size: 0.85rem;

    opacity: 0.6;
}

.detail-row strong {
    text-align: right;
}

.crossed {
    text-decoration: line-through;

    opacity: 0.5;
}


/* =========================================================
   HINWEISE
   ========================================================= */

.note,
.info-box,
.success-box {
    margin-top: 1.5rem;
    padding: 1rem 1.2rem;

    border-radius: 10px;

    font-size: 0.9rem;
}

.note {
    background: rgba(184, 115, 131, 0.08);
}

.note span {
    display: block;

    margin-bottom: 0.3rem;

    font-size: 0.75rem;

    opacity: 0.6;
}

.note p {
    margin: 0;
}

.info-box {
    background: #fff8e6;
}

.success-box {
    background: #e2f4e8;
}


/* =========================================================
   VORSCHLAG
   ========================================================= */

.offer {
    display: flex;

    flex-wrap: wrap;

    align-items: center;

    gap: 1rem;

    margin-bottom: 0.8rem;
    padding: 1.2rem;

    border: 1px solid rgba(109, 59, 71, 0.12);
    border-radius: 12px;
}

.offer-date {
    width: 50px;

    text-align: center;
}

.offer-date strong {
    display: block;

    font-size: 1.5rem;

    line-height: 1;

    color: #6d3b47;
}

.offer-date span {
    font-size: 0.7rem;

    opacity: 0.5;
}

.offer-info {
    display: flex;

    flex-direction: column;

    flex: 1;
}

.offer-info span {
    margin-top: 0.2rem;

    font-size: 0.85rem;

    opacity: 0.6;
}

.offer .primary-button {
    width: 100%;

    margin-top: 0.4rem;
}

.other-option {
    margin-top: 1.5rem;

    font-size: 0.85rem;

    opacity: 0.75;
}

.other-option a {
    color: #6d3b47;
}


/* =========================================================
   BUTTONS
   ========================================================= */

.primary-button {
    display: inline-block;

    margin-top: 1.5rem;
    padding: 0.8rem 1.4rem;

    border: none;
    border-radius: 8px;

    background: rgba(109, 59, 71, 0.9);

    color: white;

    font: inherit;
    font-size: 0.95rem;
    text-align: center;
    text-decoration: none;

    cursor: pointer;

    transition: 0.2s;
}

.primary-button:hover {
    opacity: 0.85;
}

.primary-button:disabled {
    opacity: 0.5;

    cursor: not-allowed;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 500px) {

    .booking-page {
        padding: 1.5rem 0.75rem;
    }

    .booking-card {
        padding: 1.5rem;
    }

}

</style>