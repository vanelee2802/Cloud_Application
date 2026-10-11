<template>
    <div class="booking-manage">

        <h1>Dein Termin</h1>

        <p class="status">{{ statusLabel }}</p>

        <div class="details">
            <p>
                <strong>{{ appointment.service }}</strong>
                · {{ formatPrice(appointment.price) }}
            </p>
            <p>
                {{ appointment.status === 'alternatives_offered' ? 'Dein Wunschtermin:' : 'Termin:' }}
                {{ formatDate(appointment.date) }} um {{ appointment.time }} Uhr
            </p>
        </div>

        <p v-if="appointment.admin_note" class="note">
            Nachricht vom Studio: {{ appointment.admin_note }}
        </p>

        <!-- Anfrage für heute, Studioleitung hat noch nicht entschieden -->
        <p v-if="appointment.status === 'requested'">
            Das Studio prüft gerade, ob dein Termin heute möglich ist.
            Wir melden uns per E-Mail. Bezahlt wird erst nach der Bestätigung.
        </p>

        <!-- Bestätigt, Zahlung offen -->
        <template v-else-if="appointment.status === 'awaiting_payment'">
            <p>
                Dein Termin ist bestätigt. Bitte bezahle bis
                <strong>{{ deadline }} Uhr</strong>,
                sonst wird der Termin wieder freigegeben.
            </p>
            <button class="primary" :disabled="loading" @click="post(payUrl)">
                {{ loading ? 'Einen Moment …' : 'Jetzt bezahlen' }}
            </button>
        </template>

        <!-- Studioleitung hat Ersatztermine angeboten -->
               <!-- Studioleitung hat einen Ersatztermin angeboten -->
        <template v-else-if="appointment.status === 'alternatives_offered'">
            <p>
                Dein Wunschtermin ist leider nicht möglich.
                Wir können dir stattdessen diesen Termin anbieten:
            </p>
            <div class="alternatives">
                <button
                    v-for="alt in alternatives"
                    :key="alt.id"
                    :disabled="loading"
                    @click="post(alt.acceptUrl)"
                >
                    {{ formatDate(alt.date) }} um {{ alt.time }} Uhr – annehmen und bezahlen
                </button>
            </div>

            <!-- NEU: Wenn der Vorschlag nicht passt -->
            <p class="other-option">
                Passt dir der Vorschlag nicht?
                <Link href="/">Dann buche einfach online einen neuen Termin.</Link>
            </p>
        </template>

        <!-- Bezahlt und gebucht -->
        <p v-else-if="appointment.status === 'confirmed'" class="success">
            Dein Termin ist fest gebucht. Wir freuen uns auf dich!
        </p>

        <!-- Abgesagt, abgelaufen, storniert -->
        <template v-else>
            <p>Dieser Termin ist nicht mehr aktiv.</p>
            <Link href="/" class="primary">Neuen Termin buchen</Link>
        </template>

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

// Deutscher Text zum Status
const statusLabels = {
    requested: 'Anfrage wird geprüft',
    awaiting_payment: 'Bestätigt – Zahlung offen',
    alternatives_offered: 'Ersatztermine verfügbar',
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

// "45.00" → "45,00 €"
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

    // Seite mit dem aktuellen Stand neu laden (z. B. wenn ein Vorschlag inzwischen vergeben ist)
    router.reload()
}

</script>


<style scoped>

.booking-manage {
    max-width: 560px;
    margin: 60px auto;
    padding: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.booking-manage h1 {
    margin-bottom: 8px;
}

.status {
    display: inline-block;
    margin-bottom: 20px;
    padding: 4px 12px;
    border-radius: 999px;
    background: #f1f1f1;
    font-size: 14px;
}

.details p {
    margin: 4px 0;
}

.note {
    margin: 20px 0;
    padding: 12px 14px;
    border-radius: 8px;
    background: #fff4e5;
}

.success {
    padding: 12px 14px;
    border-radius: 8px;
    background: #e8f6ee;
}

.alternatives {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

button,
.primary {
    display: inline-block;
    margin-top: 16px;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    background: #222;
    color: white;
    font-size: 15px;
    text-decoration: none;
    cursor: pointer;
}

.alternatives button {
    margin-top: 0;
    background: #f1f1f1;
    color: #222;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.other-option {
    margin-top: 24px;
    font-size: 14px;
    opacity: 0.8;
}

.other-option a {
    color: inherit;
}

</style>