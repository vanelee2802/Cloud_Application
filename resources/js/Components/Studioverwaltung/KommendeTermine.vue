<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Daten vom Server
|--------------------------------------------------------------------------
| GEÄNDERT: Die Seite gibt keine Termine als Prop mit (page.props.appointments
| war immer leer). Jetzt lädt die Komponente die gebuchten Termine selbst –
| dieselbe Liste wie „Aktuelle Buchungen“ im Studio-Dashboard.
*/

const appointments = ref(page.props.appointments ?? [])
const loading = ref(true)
const error = ref('')

let timer = null

async function load() {
    try {
        const response = await fetch('/admin/appointments/bookings', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
            error.value = response.status === 401 || response.status === 403
                ? 'Du hast keinen Zugriff auf die Termine dieses Studios.'
                : 'Die Termine konnten nicht geladen werden.'
            return
        }

        error.value = ''
        appointments.value = await response.json()
    } catch {
        error.value = 'Die Termine konnten nicht geladen werden.'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    load()
    timer = setInterval(load, 60000)   // jede Minute neu laden
})

onBeforeUnmount(() => {
    clearInterval(timer)
})

/*
|--------------------------------------------------------------------------
| Kalender
|--------------------------------------------------------------------------
*/

const showCalendar = ref(false)

const calendarDate = ref(new Date())

function toggleCalendar() {
    showCalendar.value = !showCalendar.value
}

function previousMonth() {
    calendarDate.value = new Date(
        calendarDate.value.getFullYear(),
        calendarDate.value.getMonth() - 1,
        1
    )
}

function nextMonth() {
    calendarDate.value = new Date(
        calendarDate.value.getFullYear(),
        calendarDate.value.getMonth() + 1,
        1
    )
}

function getCalendarMonthName() {
    return calendarDate.value.toLocaleDateString('de-DE', {
        month: 'long',
        year: 'numeric',
    })
}

function getDaysInMonth() {
    return new Date(
        calendarDate.value.getFullYear(),
        calendarDate.value.getMonth() + 1,
        0
    ).getDate()
}

function getFirstDayOfMonth() {
    const day = new Date(
        calendarDate.value.getFullYear(),
        calendarDate.value.getMonth(),
        1
    ).getDay()

    return day === 0 ? 6 : day - 1
}

// NEU: Anzahl Termine pro Tag im angezeigten Monat, z. B. { 20: 2, 24: 1 }
const countsByDay = computed(() => {
    const year = calendarDate.value.getFullYear()
    const month = String(calendarDate.value.getMonth() + 1).padStart(2, '0')
    const prefix = `${year}-${month}-`

    const result = {}

    for (const appointment of appointments.value) {
        const date = dateOnly(appointment.date)

        if (date.startsWith(prefix)) {
            const day = Number(date.slice(8, 10))
            result[day] = (result[day] ?? 0) + 1
        }
    }

    return result
})

function dayLabel(day) {
    const count = countsByDay.value[day] ?? 0

    if (count === 0) {
        return `${day}.`
    }

    return count === 1 ? `${day}., 1 Termin` : `${day}., ${count} Termine`
}

/*
|--------------------------------------------------------------------------
| Datum und Uhrzeit
|--------------------------------------------------------------------------
*/

// Nur den Datumsteil, egal ob "2026-10-20" oder "2026-10-20 00:00:00"
function dateOnly(date) {
    return String(date ?? '').slice(0, 10)
}

function formatDate(date) {
    if (!date) {
        return ''
    }

    const value = new Date(`${dateOnly(date)}T00:00:00`)

    if (Number.isNaN(value.getTime())) {
        return date
    }

    return value.toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

function formatTime(time) {
    if (!time) {
        return ''
    }

    return String(time).substring(0, 5)
}

function formatAppointmentDate(appointment) {
    if (appointment?.date) {
        return formatDate(appointment.date)
    }

    return ''
}

function formatAppointmentTime(appointment) {
    if (appointment?.time) {
        return formatTime(appointment.time)
    }

    return ''
}
</script>

<template>
    <section class="dashboard-section">

        <div class="section-header">
            <div>
                <h2>Kommende Termine</h2>
            </div>

            <button
                class="secondary-button"
                type="button"
                @click="toggleCalendar"
            >
                {{ showCalendar ? 'Liste' : 'Kalender' }}
            </button>
        </div>

        <div
            v-if="showCalendar"
            class="calendar-view"
        >
            <div class="calendar-header">
                <button
                    type="button"
                    class="calendar-nav"
                    aria-label="Vorheriger Monat"
                    @click="previousMonth"
                >
                    ‹
                </button>

                <h3>
                    {{ getCalendarMonthName() }}
                </h3>

                <button
                    type="button"
                    class="calendar-nav"
                    aria-label="Nächster Monat"
                    @click="nextMonth"
                >
                    ›
                </button>
            </div>

            <div class="calendar-weekdays">
                <span>Mo</span>
                <span>Di</span>
                <span>Mi</span>
                <span>Do</span>
                <span>Fr</span>
                <span>Sa</span>
                <span>So</span>
            </div>

            <div class="calendar-grid">

                <div
                    v-for="empty in getFirstDayOfMonth()"
                    :key="`empty-${empty}`"
                    class="calendar-day calendar-day-empty"
                >
                </div>

                <!-- GEÄNDERT: Tage mit Terminen sind markiert -->
                <div
                    v-for="day in getDaysInMonth()"
                    :key="day"
                    class="calendar-day"
                    :class="{ 'has-appointments': countsByDay[day] }"
                    :aria-label="dayLabel(day)"
                >
                    <span>{{ day }}</span>
                    <span
                        v-if="countsByDay[day]"
                        class="day-count"
                        aria-hidden="true"
                    >
                        {{ countsByDay[day] }}
                    </span>
                </div>

            </div>
        </div>

        <p
            v-if="error"
            class="error-text"
        >
            {{ error }}
        </p>

        <p
            v-else-if="loading && !appointments.length"
            class="loading-text"
        >
            Termine werden geladen …
        </p>

        <div
            v-else-if="appointments.length"
            class="appointment-list"
        >
            <div
                v-for="appointment in appointments"
                :key="appointment.id"
                class="appointment-card"
            >
                <div class="appointment-time">
                    <strong>
                        {{ formatAppointmentTime(appointment) }}
                    </strong>

                    <span>
                        {{ formatAppointmentDate(appointment) }}
                    </span>
                </div>

                <div class="appointment-info">
                    <h3>
                        {{
                            appointment.customer_name ||
                            appointment.user?.name ||
                            appointment.customer?.name ||
                            'Kunde'
                        }}
                    </h3>

                    <p>
                        {{
                            appointment.service?.name ||
                            appointment.service_name ||
                            'Termin'
                        }}
                    </p>
                </div>

                <span
                    class="status-badge"
                    :class="
                        appointment.status === 'confirmed'
                            ? 'confirmed'
                            : 'pending'
                    "
                >
                    {{
                        appointment.status === 'confirmed'
                            ? 'Bestätigt'
                            : 'Ausstehend'
                    }}
                </span>
            </div>
        </div>

        <div
            v-else
            class="empty-state"
        >
            <div class="empty-icon">
                ◷
            </div>

            <strong>
                Keine kommenden Termine
            </strong>

            <span>
                Sobald eine Kundin einen Termin bezahlt hat, erscheint er hier.
            </span>
        </div>

    </section>
</template>

<style scoped>
.dashboard-section {
    background-color: white;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow:
        0 4px 12px rgba(80, 35, 48, 0.06),
        0 8px 20px rgba(80, 35, 48, 0.04);
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.section-header h2 {
    margin: 0;
    color: #4f3540;
    font-size: 1.2rem;
    font-weight: 700;
}

.secondary-button {
    padding: 0.6rem 1rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 8px;
    background-color: #fdf9fa;
    color: #8f5364;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.secondary-button:hover {
    background-color: #f5e7eb;
    border-color: rgba(143, 83, 100, 0.3);
}

.calendar-view {
    margin-bottom: 1.25rem;
    padding: 1rem;
    border: 1px solid rgba(143, 83, 100, 0.1);
    border-radius: 10px;
    background-color: #fdfbfc;
}

.calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.calendar-header h3 {
    margin: 0;
    color: #4f3540;
    font-size: 1rem;
    font-weight: 700;
    text-transform: capitalize;
}

.calendar-nav {
    width: 34px;
    height: 34px;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 7px;
    background-color: white;
    color: #8f5364;
    font-size: 1.3rem;
    line-height: 1;
    cursor: pointer;
}

.calendar-nav:hover {
    background-color: #f5e7eb;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.35rem;
    margin-bottom: 0.35rem;
}

.calendar-weekdays span {
    padding: 0.4rem 0;
    color: #8a7a80;
    font-size: 0.7rem;
    font-weight: 700;
    text-align: center;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.35rem;
}

.calendar-day {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    border-radius: 7px;
    background-color: white;
    color: #4f3540;
    font-size: 0.8rem;
}

.calendar-day:not(.calendar-day-empty):hover {
    background-color: #f5e7eb;
}

.calendar-day-empty {
    background-color: transparent;
}

/* NEU: Tag mit Terminen */
.calendar-day.has-appointments {
    background-color: #f5e7eb;
    font-weight: 700;
}

.day-count {
    position: absolute;
    top: 2px;
    right: 3px;
    min-width: 15px;
    height: 15px;
    padding: 0 3px;
    border-radius: 999px;
    background-color: #8f5364;
    color: white;
    font-size: 0.6rem;
    line-height: 15px;
    text-align: center;
    box-sizing: border-box;
}

.error-text {
    margin: 0;
    color: #b42318;
    font-size: 0.9rem;
}

.loading-text {
    margin: 0;
    color: #8a7a80;
    font-size: 0.9rem;
}

.appointment-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.appointment-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid rgba(143, 83, 100, 0.1);
    border-radius: 10px;
    background-color: #fdfbfc;
}

.appointment-time {
    display: flex;
    flex-direction: column;
    min-width: 75px;
}

.appointment-time strong {
    color: #4f3540;
    font-size: 0.95rem;
    font-weight: 700;
}

.appointment-time span {
    margin-top: 0.2rem;
    color: #8a7a80;
    font-size: 0.75rem;
}

.appointment-info {
    flex: 1;
    min-width: 0;
}

.appointment-info h3 {
    margin: 0 0 0.2rem;
    color: #4f3540;
    font-size: 0.95rem;
    font-weight: 700;
}

.appointment-info p {
    margin: 0;
    color: #6f5961;
    font-size: 0.85rem;
}

.status-badge {
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    white-space: nowrap;
}

.status-badge.confirmed {
    background-color: #e8f4ec;
    color: #477052;
}

.status-badge.pending {
    background-color: #f8eee1;
    color: #8a6742;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    min-height: 180px;
    text-align: center;
}

.empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    margin-bottom: 0.5rem;
    border-radius: 50%;
    background-color: #f5e7eb;
    color: #8f5364;
    font-size: 1.2rem;
}

.empty-state strong {
    color: #4f3540;
    font-size: 0.95rem;
}

.empty-state span {
    max-width: 280px;
    color: #8a7a80;
    font-size: 0.8rem;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .dashboard-section {
        padding: 1.25rem;
    }

    .appointment-card {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .appointment-info {
        min-width: calc(100% - 100px);
    }

    .status-badge {
        margin-left: auto;
    }
}

@media (max-width: 480px) {
    .dashboard-section {
        padding: 1rem;
    }

    .section-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .appointment-card {
        gap: 0.75rem;
    }

    .appointment-time {
        min-width: 65px;
    }

    .appointment-info {
        min-width: calc(100% - 80px);
    }

    .status-badge {
        margin-left: 0;
    }
}
</style>