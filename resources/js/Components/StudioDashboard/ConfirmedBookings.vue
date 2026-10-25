<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Gebuchte (bezahlte) Termine ab heute – Schichtleitung kann sie als „Abgeschlossen“ markieren.
// Danach bekommt die Kundin automatisch die Danke-Mail mit Link zur Bewertung.

// Abschließen darf nur die Schichtleitung, Mitarbeiterinnen sehen die Liste nur
const page = usePage()
const canComplete = computed(() => page.props.auth?.user?.role === 'admin')

const bookings = ref([])
const loading = ref(true)
const message = ref('')
const busyId = ref(null)

// Aktuelle Uhrzeit, jede Minute neu – damit der Button freigeschaltet wird, sobald ein Termin beginnt
const now = ref(new Date())
let timer = null

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

async function load() {
    loading.value = true

    try {
        const response = await fetch('/admin/appointments/bookings', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
            throw new Error()
        }

        bookings.value = await response.json()
    } catch {
        message.value = 'Die gebuchten Termine konnten nicht geladen werden.'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    load()
    timer = setInterval(() => { now.value = new Date() }, 60 * 1000)
})

onBeforeUnmount(() => clearInterval(timer))

function dateOnly(value) {
    return String(value ?? '').slice(0, 10)
}

function timeOnly(value) {
    return String(value ?? '').slice(0, 5)
}

function startsAt(booking) {
    return new Date(`${dateOnly(booking.date)}T${timeOnly(booking.time)}:00`)
}

function hasStarted(booking) {
    return startsAt(booking) <= now.value
}

const today = computed(() => now.value.toLocaleDateString('sv-SE'))

function dayLabel(booking) {
    const date = dateOnly(booking.date)

    if (date === today.value) {
        return 'Heute'
    }

    return new Date(`${date}T00:00:00`).toLocaleDateString('de-DE', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
    })
}

async function complete(booking) {
    if (!window.confirm(`Termin von ${booking.customer_name ?? 'der Kundin'} um ${timeOnly(booking.time)} als abgeschlossen markieren? Die Kundin bekommt dann die Danke-Mail.`)) {
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

        if (response.ok || response.status === 409) {
            // Abgeschlossen (oder schon vorher) → aus der Liste nehmen
            bookings.value = bookings.value.filter(item => item.id !== booking.id)
        }
    } catch {
        message.value = 'Server nicht erreichbar. Bitte versuche es erneut.'
    } finally {
        busyId.value = null
    }
}
</script>

<template>
    <section class="bookings">
        <div class="bookings-head">
            <h3>Gebuchte Termine</h3>
            <button type="button" class="reload-button" :disabled="loading" @click="load">
                Aktualisieren
            </button>
        </div>

        <p v-if="message" class="message" role="status">{{ message }}</p>

        <p v-if="loading" class="empty">Termine werden geladen …</p>

        <p v-else-if="bookings.length === 0" class="empty">
            Keine offenen gebuchten Termine.
        </p>

        <ul v-else class="booking-list">
            <li v-for="booking in bookings" :key="booking.id" class="booking">
                <div class="booking-when">
                    <span class="day">{{ dayLabel(booking) }}</span>
                    <span class="time">{{ timeOnly(booking.time) }}</span>
                </div>

                <div class="booking-info">
                    <span class="customer">
                        {{ booking.customer_name ?? booking.user?.name ?? 'Kundin' }}
                    </span>
                    <span class="service">{{ booking.service?.name }}</span>
                </div>

                <button
                    v-if="canComplete"
                    type="button"
                    class="complete-button"
                    :disabled="!hasStarted(booking) || busyId === booking.id"
                    :title="hasStarted(booking) ? 'Termin abschließen und Danke-Mail senden' : 'Geht erst ab Beginn des Termins'"
                    @click="complete(booking)"
                >
                    {{ busyId === booking.id ? 'Speichert …' : 'Abgeschlossen' }}
                </button>
            </li>
        </ul>

        <p v-if="canComplete" class="hint">
            Termine werden nach ihrem Ende auch automatisch abgeschlossen.
            Mit dem Button geht es sofort, zum Beispiel wenn eine Kundin früher fertig ist.
        </p>
    </section>
</template>

<style scoped>
.bookings {
    padding: 1.25rem;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 12px;
    background: #ffffff;
    color: #4f3540;
}

.bookings-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.9rem;
}

.bookings-head h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
}

.reload-button {
    padding: 0.35rem 0.75rem;
    border: 1px solid rgba(143, 83, 100, 0.25);
    border-radius: 7px;
    background: #ffffff;
    color: #8f5364;
    font-size: 0.78rem;
    cursor: pointer;
}

.message {
    margin: 0 0 0.9rem;
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    background: #f8f0f2;
    font-size: 0.85rem;
}

.empty {
    margin: 1rem 0;
    color: #8a7a80;
    text-align: center;
}

.booking-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.booking {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.7rem 0.85rem;
    border: 1px solid rgba(143, 83, 100, 0.1);
    border-radius: 10px;
    background: #fdfbfc;
}

.booking-when {
    display: flex;
    flex-direction: column;
    min-width: 4.5rem;
}

.day {
    color: #8a7a80;
    font-size: 0.72rem;
}

.time {
    font-size: 1.05rem;
    font-weight: 700;
}

.booking-info {
    display: flex;
    flex: 1;
    min-width: 0;
    flex-direction: column;
}

.customer {
    font-weight: 600;
    overflow-wrap: anywhere;
}

.service {
    color: #8a7a80;
    font-size: 0.8rem;
}

.complete-button {
    flex-shrink: 0;
    padding: 0.5rem 0.9rem;
    border: none;
    border-radius: 8px;
    background: #7d1024;
    color: #ffffff;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
}

.complete-button:hover:not(:disabled) {
    background: #64101f;
}

.complete-button:disabled {
    background: #d9c5ca;
    cursor: default;
}

.reload-button:focus-visible,
.complete-button:focus-visible {
    outline: 2px solid #7d1024;
    outline-offset: 2px;
}

.hint {
    margin: 0.9rem 0 0;
    color: #8a7a80;
    font-size: 0.75rem;
    line-height: 1.5;
}

@media (max-width: 600px) {
    .booking {
        flex-wrap: wrap;
    }

    .complete-button {
        width: 100%;
    }
}
</style>