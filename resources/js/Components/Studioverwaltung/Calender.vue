<template>
    <div class="calendar">

        <!-- ========================================================= -->
        <!-- KALENDER HEADER -->
        <!-- ========================================================= -->

        <div class="calendar-header">

            <div class="calendar-title">
                <h2>{{ monthName }}</h2>
            </div>

            <div class="calendar-navigation">

                <button
                    type="button"
                    class="calendar-nav-button"
                    @click="previousMonth"
                    aria-label="Vorheriger Monat"
                >
                    ‹
                </button>

                <button
                    type="button"
                    class="calendar-today-button"
                    @click="goToToday"
                >
                    Heute
                </button>

                <button
                    type="button"
                    class="calendar-nav-button"
                    @click="nextMonth"
                    aria-label="Nächster Monat"
                >
                    ›
                </button>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- WOCHENTAGE -->
        <!-- ========================================================= -->

        <div class="calendar-weekdays">

            <div
                v-for="day in weekdays"
                :key="day"
                class="calendar-weekday"
            >
                {{ day }}
            </div>

        </div>


        <!-- ========================================================= -->
        <!-- KALENDER-TAGE -->
        <!-- ========================================================= -->

        <div class="calendar-grid">

            <!-- Leere Felder vor dem ersten Tag -->
            <div
                v-for="empty in firstDayOfMonth"
                :key="`empty-${empty}`"
                class="calendar-day calendar-day-empty"
            ></div>


            <!-- Tage des Monats -->
            <button
                v-for="day in daysInMonth"
                :key="day"
                type="button"
                class="calendar-day"
                :class="{
                    'calendar-day-today': isToday(day),
                    'calendar-day-selected': isSelected(day),
                    'calendar-day-has-events':
                        getAppointmentsForDay(day).length > 0,
                }"
                @click="selectDay(day)"
            >

                <!-- Tagesnummer -->
                <span class="calendar-day-number">
                    {{ day }}
                </span>


                <!-- Termin-Hinweis -->
                <div
                    v-if="getAppointmentsForDay(day).length > 0"
                    class="calendar-day-events"
                >

                    <span class="calendar-event-dot"></span>

                    <span class="calendar-event-count">
                        {{ getAppointmentsForDay(day).length }}
                        {{
                            getAppointmentsForDay(day).length === 1
                                ? 'Termin'
                                : 'Termine'
                        }}
                    </span>

                </div>

            </button>

        </div>


        <!-- ========================================================= -->
        <!-- AUSGEWÄHLTER TAG -->
        <!-- ========================================================= -->

        <div
            v-if="selectedDate"
            class="calendar-selected-section"
        >

            <div class="calendar-selected-header">

                <div>

                    <span class="calendar-selected-label">
                        Ausgewählter Tag
                    </span>

                    <h3>
                        {{ selectedDateFormatted }}
                    </h3>

                </div>

                <span class="calendar-selected-count">
                    {{ selectedDayAppointments.length }}

                    {{
                        selectedDayAppointments.length === 1
                            ? 'Termin'
                            : 'Termine'
                    }}
                </span>

            </div>


            <!-- ===================================================== -->
            <!-- TERMINE -->
            <!-- ===================================================== -->

            <div
                v-if="selectedDayAppointments.length > 0"
                class="calendar-appointments"
            >

                <div
                    v-for="appointment in selectedDayAppointments"
                    :key="appointment.id"
                    class="calendar-appointment"
                >

                    <!-- Uhrzeit -->
                    <div class="calendar-appointment-time">

                        <strong>
                            {{ formatAppointmentTime(appointment) }}
                        </strong>

                    </div>


                    <!-- Termin-Informationen -->
                    <div class="calendar-appointment-info">

                        <h4>
                            {{
                                appointment.customer?.name ||
                                appointment.user?.name ||
                                appointment.customer_name ||
                                'Kunde'
                            }}
                        </h4>

                        <p>
                            {{
                                appointment.service?.name ||
                                appointment.service_name ||
                                'Termin'
                            }}
                        </p>

                    </div>


                    <!-- Status -->
                    <span
                        class="calendar-appointment-status"
                        :class="getAppointmentStatusClass(appointment.status)"
                    >
                        {{ getAppointmentStatusLabel(appointment.status) }}
                    </span>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- KEINE TERMINE -->
            <!-- ===================================================== -->

            <div
                v-else
                class="calendar-no-appointments"
            >

                <div class="calendar-no-appointments-icon">
                    ◷
                </div>

                <strong>
                    Keine Termine
                </strong>

                <span>
                    Für diesen Tag sind keine Termine eingetragen.
                </span>

            </div>

        </div>

    </div>
</template>


<script setup>
import { computed, ref } from 'vue'


/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
|
| Employee.vue übergibt die vorhandenen Termine:
|
| <Calendar :appointments="appointments" />
|
|--------------------------------------------------------------------------
*/

const props = defineProps({
    appointments: {
        type: Array,
        default: () => [],
    },
})


/*
|--------------------------------------------------------------------------
| KALENDER
|--------------------------------------------------------------------------
*/

const calendarDate = ref(new Date())


/*
|--------------------------------------------------------------------------
| AUSGEWÄHLTER TAG
|--------------------------------------------------------------------------
*/

const selectedDate = ref(null)


/*
|--------------------------------------------------------------------------
| WOCHENTAGE
|--------------------------------------------------------------------------
*/

const weekdays = [
    'Mo',
    'Di',
    'Mi',
    'Do',
    'Fr',
    'Sa',
    'So',
]


/*
|--------------------------------------------------------------------------
| MONATSNAME
|--------------------------------------------------------------------------
*/

const monthName = computed(() => {
    return calendarDate.value.toLocaleDateString('de-DE', {
        month: 'long',
        year: 'numeric',
    })
})


/*
|--------------------------------------------------------------------------
| TAGE DES MONATS
|--------------------------------------------------------------------------
*/

const daysInMonth = computed(() => {

    const year = calendarDate.value.getFullYear()
    const month = calendarDate.value.getMonth()

    const numberOfDays = new Date(
        year,
        month + 1,
        0
    ).getDate()

    return Array.from(
        { length: numberOfDays },
        (_, index) => index + 1
    )
})


/*
|--------------------------------------------------------------------------
| ERSTER WOCHENTAG DES MONATS
|--------------------------------------------------------------------------
|
| JavaScript:
|
| Sonntag = 0
| Montag  = 1
| Dienstag = 2
| ...
|
| Unser Kalender beginnt mit Montag.
|--------------------------------------------------------------------------
*/

const firstDayOfMonth = computed(() => {

    const year = calendarDate.value.getFullYear()
    const month = calendarDate.value.getMonth()

    const day = new Date(
        year,
        month,
        1
    ).getDay()

    return day === 0 ? 6 : day - 1
})


/*
|--------------------------------------------------------------------------
| VORHERIGER MONAT
|--------------------------------------------------------------------------
*/

function previousMonth() {

    calendarDate.value = new Date(
        calendarDate.value.getFullYear(),
        calendarDate.value.getMonth() - 1,
        1
    )
}


/*
|--------------------------------------------------------------------------
| NÄCHSTER MONAT
|--------------------------------------------------------------------------
*/

function nextMonth() {

    calendarDate.value = new Date(
        calendarDate.value.getFullYear(),
        calendarDate.value.getMonth() + 1,
        1
    )
}


/*
|--------------------------------------------------------------------------
| HEUTE
|--------------------------------------------------------------------------
*/

function goToToday() {

    const today = new Date()

    calendarDate.value = new Date(
        today.getFullYear(),
        today.getMonth(),
        1
    )

    selectedDate.value = new Date(today)
}


/*
|--------------------------------------------------------------------------
| IST DER TAG HEUTE?
|--------------------------------------------------------------------------
*/

function isToday(day) {

    const today = new Date()

    return (
        calendarDate.value.getFullYear() ===
            today.getFullYear() &&

        calendarDate.value.getMonth() ===
            today.getMonth() &&

        day === today.getDate()
    )
}


/*
|--------------------------------------------------------------------------
| TAG AUSWÄHLEN
|--------------------------------------------------------------------------
*/

function selectDay(day) {

    selectedDate.value = new Date(
        calendarDate.value.getFullYear(),
        calendarDate.value.getMonth(),
        day
    )
}


/*
|--------------------------------------------------------------------------
| IST DER TAG AUSGEWÄHLT?
|--------------------------------------------------------------------------
*/

function isSelected(day) {

    if (!selectedDate.value) {
        return false
    }

    return (
        calendarDate.value.getFullYear() ===
            selectedDate.value.getFullYear() &&

        calendarDate.value.getMonth() ===
            selectedDate.value.getMonth() &&

        day === selectedDate.value.getDate()
    )
}


/*
|--------------------------------------------------------------------------
| TERMIN-DATUM
|--------------------------------------------------------------------------
*/

function getAppointmentDate(appointment) {

    if (!appointment?.date) {
        return null
    }

    const value = new Date(appointment.date)

    if (Number.isNaN(value.getTime())) {
        return null
    }

    return value
}


/*
|--------------------------------------------------------------------------
| TERMINE EINES TAGES
|--------------------------------------------------------------------------
*/

function getAppointmentsForDay(day) {

    const year = calendarDate.value.getFullYear()
    const month = calendarDate.value.getMonth()

    return props.appointments
        .filter((appointment) => {

            const appointmentDate =
                getAppointmentDate(appointment)

            if (!appointmentDate) {
                return false
            }

            return (
                appointmentDate.getFullYear() === year &&
                appointmentDate.getMonth() === month &&
                appointmentDate.getDate() === day
            )
        })
        .sort((a, b) => {

            const timeA = String(a?.time ?? '')
            const timeB = String(b?.time ?? '')

            return timeA.localeCompare(timeB)
        })
}


/*
|--------------------------------------------------------------------------
| TERMINE DES AUSGEWÄHLTEN TAGES
|--------------------------------------------------------------------------
*/

const selectedDayAppointments = computed(() => {

    if (!selectedDate.value) {
        return []
    }

    return props.appointments
        .filter((appointment) => {

            const appointmentDate =
                getAppointmentDate(appointment)

            if (!appointmentDate) {
                return false
            }

            return (
                appointmentDate.getFullYear() ===
                    selectedDate.value.getFullYear() &&

                appointmentDate.getMonth() ===
                    selectedDate.value.getMonth() &&

                appointmentDate.getDate() ===
                    selectedDate.value.getDate()
            )
        })
        .sort((a, b) => {

            const timeA = String(a?.time ?? '')
            const timeB = String(b?.time ?? '')

            return timeA.localeCompare(timeB)
        })
})


/*
|--------------------------------------------------------------------------
| TERMIN-UHRZEIT
|--------------------------------------------------------------------------
*/

function formatAppointmentTime(appointment) {

    if (!appointment?.time) {
        return 'Keine Uhrzeit'
    }

    return String(appointment.time).substring(0, 5)
}


/*
|--------------------------------------------------------------------------
| AUSGEWÄHLTES DATUM
|--------------------------------------------------------------------------
*/

const selectedDateFormatted = computed(() => {

    if (!selectedDate.value) {
        return ''
    }

    return selectedDate.value.toLocaleDateString('de-DE', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    })
})


/*
|--------------------------------------------------------------------------
| TERMIN-STATUS
|--------------------------------------------------------------------------
*/

function getAppointmentStatusLabel(status) {

    if (status === 'confirmed') {
        return 'Bestätigt'
    }

    if (status === 'cancelled') {
        return 'Storniert'
    }

    if (status === 'completed') {
        return 'Abgeschlossen'
    }

    return 'Ausstehend'
}


/*
|--------------------------------------------------------------------------
| TERMIN-STATUS CSS-KLASSE
|--------------------------------------------------------------------------
*/

function getAppointmentStatusClass(status) {

    if (status === 'confirmed') {
        return 'confirmed'
    }

    if (status === 'cancelled') {
        return 'cancelled'
    }

    if (status === 'completed') {
        return 'completed'
    }

    return 'pending'
}
</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| KALENDER
|--------------------------------------------------------------------------
*/

.calendar {
    width: 100%;
    box-sizing: border-box;

    padding: 1.5rem;

    background: white;

    border-radius: 16px;

    box-shadow:
        0 4px 14px rgba(80, 35, 48, 0.08),
        0 10px 30px rgba(80, 35, 48, 0.06);
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 1rem;

    margin-bottom: 1.5rem;
}


.calendar-title h2 {
    margin: 0;

    color: var(--text-color, #4f3540);

    font-size: 1.4rem;
    font-weight: 700;

    text-transform: capitalize;
}


/*
|--------------------------------------------------------------------------
| NAVIGATION
|--------------------------------------------------------------------------
*/

.calendar-navigation {
    display: flex;
    align-items: center;

    gap: 0.5rem;
}


/*
|--------------------------------------------------------------------------
| PFEIL-BUTTONS
|--------------------------------------------------------------------------
*/

.calendar-nav-button {
    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: none;
    border-radius: 8px;

    background-color: #f5e7eb;
    color: #8f5364;

    font-size: 1.5rem;
    line-height: 1;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;
}


.calendar-nav-button:hover {
    background-color: #ead3da;

    transform: translateY(-1px);
}


.calendar-nav-button:active {
    transform: translateY(0);
}


/*
|--------------------------------------------------------------------------
| HEUTE-BUTTON
|--------------------------------------------------------------------------
*/

.calendar-today-button {
    padding: 0.55rem 0.9rem;

    border: 1px solid #dcbac5;
    border-radius: 8px;

    background-color: white;
    color: #8f5364;

    font-size: 0.9rem;
    font-weight: 600;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}


.calendar-today-button:hover {
    background-color: #8f5364;
    color: white;

    transform: translateY(-1px);
}


/*
|--------------------------------------------------------------------------
| WOCHENTAGE
|--------------------------------------------------------------------------
*/

.calendar-weekdays {
    display: grid;

    grid-template-columns: repeat(7, 1fr);

    margin-bottom: 0.5rem;
}


.calendar-weekday {
    padding: 0.7rem 0.25rem;

    text-align: center;

    color: #8f5364;

    font-size: 0.85rem;
    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| KALENDER GRID
|--------------------------------------------------------------------------
*/

.calendar-grid {
    display: grid;

    grid-template-columns: repeat(7, 1fr);

    gap: 0.4rem;
}


/*
|--------------------------------------------------------------------------
| KALENDERTAG
|--------------------------------------------------------------------------
*/

.calendar-day {
    position: relative;

    min-height: 80px;

    padding: 0.6rem;

    border: 1px solid #f0e3e7;
    border-radius: 10px;

    background-color: white;
    color: #4f3540;

    text-align: left;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}


.calendar-day:hover {
    background-color: #fcf5f7;

    border-color: #dcbac5;

    transform: translateY(-1px);

    box-shadow:
        0 3px 8px rgba(80, 35, 48, 0.08);
}


/*
|--------------------------------------------------------------------------
| LEERE TAGE
|--------------------------------------------------------------------------
*/

.calendar-day-empty {
    border: none;

    background: transparent;

    cursor: default;
}


.calendar-day-empty:hover {
    background: transparent;

    border: none;

    transform: none;

    box-shadow: none;
}


/*
|--------------------------------------------------------------------------
| TAGESNUMMER
|--------------------------------------------------------------------------
*/

.calendar-day-number {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 30px;
    height: 30px;

    border-radius: 50%;

    font-size: 0.95rem;
    font-weight: 600;
}


/*
|--------------------------------------------------------------------------
| HEUTIGER TAG
|--------------------------------------------------------------------------
*/

.calendar-day-today {
    border-color: #8f5364;
}


.calendar-day-today .calendar-day-number {
    background-color: #8f5364;

    color: white;
}


/*
|--------------------------------------------------------------------------
| AUSGEWÄHLTER TAG
|--------------------------------------------------------------------------
*/

.calendar-day-selected {
    border-color: #754353;

    background-color: #faf0f3;

    box-shadow:
        0 0 0 2px rgba(143, 83, 100, 0.15);
}


.calendar-day-selected .calendar-day-number {
    color: #754353;
}


/*
|--------------------------------------------------------------------------
| HEUTE + AUSGEWÄHLT
|--------------------------------------------------------------------------
*/

.calendar-day-today.calendar-day-selected
.calendar-day-number {
    background-color: #754353;

    color: white;
}


/*
|--------------------------------------------------------------------------
| TAG MIT TERMIN
|--------------------------------------------------------------------------
*/

.calendar-day-has-events {
    border-color: #dcbac5;
}


.calendar-day-has-events:hover {
    border-color: #8f5364;
}


/*
|--------------------------------------------------------------------------
| TERMINE IM TAG
|--------------------------------------------------------------------------
*/

.calendar-day-events {
    display: flex;
    align-items: center;

    gap: 0.35rem;

    margin-top: 0.45rem;
}


.calendar-event-dot {
    width: 7px;
    height: 7px;

    flex-shrink: 0;

    border-radius: 50%;

    background-color: #8f5364;
}


.calendar-event-count {
    color: #8f5364;

    font-size: 0.65rem;
    font-weight: 600;

    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| AUSGEWÄHLTER TAG BEREICH
|--------------------------------------------------------------------------
*/

.calendar-selected-section {
    margin-top: 1.5rem;
    padding-top: 1.25rem;

    border-top: 1px solid #f0e3e7;
}


/*
|--------------------------------------------------------------------------
| AUSGEWÄHLTER HEADER
|--------------------------------------------------------------------------
*/

.calendar-selected-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 1rem;

    margin-bottom: 1rem;
}


.calendar-selected-label {
    display: block;

    margin-bottom: 0.2rem;

    color: #8f5364;

    font-size: 0.75rem;
    font-weight: 600;

    text-transform: uppercase;
}


.calendar-selected-header h3 {
    margin: 0;

    color: #4f3540;

    font-size: 1rem;
    font-weight: 700;

    text-transform: capitalize;
}


.calendar-selected-count {
    padding: 0.4rem 0.7rem;

    border-radius: 20px;

    background-color: #faf0f3;
    color: #8f5364;

    font-size: 0.75rem;
    font-weight: 600;

    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| TERMINE
|--------------------------------------------------------------------------
*/

.calendar-appointments {
    display: flex;
    flex-direction: column;

    gap: 0.6rem;
}


/*
|--------------------------------------------------------------------------
| EINZELNER TERMIN
|--------------------------------------------------------------------------
*/

.calendar-appointment {
    display: flex;
    align-items: center;

    gap: 1rem;

    padding: 0.9rem 1rem;

    border: 1px solid #f0e3e7;
    border-radius: 10px;

    background-color: white;

    transition:
        background-color 0.2s ease,
        border-color 0.2s ease;
}


.calendar-appointment:hover {
    background-color: #fcf5f7;

    border-color: #dcbac5;
}


/*
|--------------------------------------------------------------------------
| UHRZEIT
|--------------------------------------------------------------------------
*/

.calendar-appointment-time {
    min-width: 60px;

    color: #8f5364;

    font-size: 0.9rem;
}


/*
|--------------------------------------------------------------------------
| TERMIN INFORMATION
|--------------------------------------------------------------------------
*/

.calendar-appointment-info {
    flex: 1;

    min-width: 0;
}


.calendar-appointment-info h4 {
    margin: 0 0 0.2rem;

    color: #4f3540;

    font-size: 0.9rem;
}


.calendar-appointment-info p {
    margin: 0;

    color: #777;

    font-size: 0.8rem;
}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

.calendar-appointment-status {
    flex-shrink: 0;

    padding: 0.35rem 0.65rem;

    border-radius: 20px;

    font-size: 0.7rem;
    font-weight: 600;
}


/*
|--------------------------------------------------------------------------
| BESTÄTIGT
|--------------------------------------------------------------------------
*/

.calendar-appointment-status.confirmed {
    background-color: #e8f5ec;

    color: #3d7a4d;
}


/*
|--------------------------------------------------------------------------
| AUSSTEHEND
|--------------------------------------------------------------------------
*/

.calendar-appointment-status.pending {
    background-color: #fff4df;

    color: #9a6a1d;
}


/*
|--------------------------------------------------------------------------
| STORNIERT
|--------------------------------------------------------------------------
*/

.calendar-appointment-status.cancelled {
    background-color: #fbe9ec;

    color: #a34f60;
}


/*
|--------------------------------------------------------------------------
| ABGESCHLOSSEN
|--------------------------------------------------------------------------
*/

.calendar-appointment-status.completed {
    background-color: #eeeaf5;

    color: #68567f;
}


/*
|--------------------------------------------------------------------------
| KEINE TERMINE
|--------------------------------------------------------------------------
*/

.calendar-no-appointments {
    display: flex;
    flex-direction: column;
    align-items: center;

    gap: 0.35rem;

    padding: 2rem 1rem;

    border-radius: 10px;

    background-color: #faf7f8;

    text-align: center;
}


.calendar-no-appointments-icon {
    margin-bottom: 0.25rem;

    color: #8f5364;

    font-size: 1.8rem;
}


.calendar-no-appointments strong {
    color: #4f3540;

    font-size: 0.9rem;
}


.calendar-no-appointments span:last-child {
    color: #888;

    font-size: 0.8rem;
}


/*
|--------------------------------------------------------------------------
| TABLET
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .calendar {
        padding: 1rem;
    }


    .calendar-header {
        flex-direction: column;

        align-items: stretch;
    }


    .calendar-title {
        text-align: center;
    }


    .calendar-navigation {
        justify-content: center;
    }


    .calendar-day {
        min-height: 65px;

        padding: 0.4rem;
    }


    .calendar-day-number {
        width: 28px;
        height: 28px;

        font-size: 0.85rem;
    }


    .calendar-weekday {
        font-size: 0.75rem;
    }


    .calendar-event-count {
        display: none;
    }


    .calendar-event-dot {
        width: 6px;
        height: 6px;
    }

}


/*
|--------------------------------------------------------------------------
| SMARTPHONE
|--------------------------------------------------------------------------
*/

@media (max-width: 480px) {

    .calendar {
        padding: 0.75rem;

        border-radius: 12px;
    }


    .calendar-title h2 {
        font-size: 1.2rem;
    }


    .calendar-navigation {
        gap: 0.35rem;
    }


    .calendar-nav-button {
        width: 36px;
        height: 36px;
    }


    .calendar-today-button {
        padding: 0.45rem 0.7rem;

        font-size: 0.8rem;
    }


    .calendar-grid {
        gap: 0.2rem;
    }


    .calendar-day {
        min-height: 52px;

        padding: 0.25rem;

        border-radius: 7px;
    }


    .calendar-day-number {
        width: 24px;
        height: 24px;

        font-size: 0.75rem;
    }


    .calendar-weekday {
        padding: 0.5rem 0.1rem;

        font-size: 0.7rem;
    }


    .calendar-selected-info {
        font-size: 0.85rem;
    }


    .calendar-appointment {
        align-items: flex-start;

        gap: 0.6rem;

        padding: 0.75rem;
    }


    .calendar-appointment-time {
        min-width: 45px;

        font-size: 0.8rem;
    }


    .calendar-appointment-info h4 {
        font-size: 0.8rem;
    }


    .calendar-appointment-info p {
        font-size: 0.7rem;
    }


    .calendar-appointment-status {
        display: none;
    }


    .calendar-selected-header {
        align-items: flex-start;
    }

}

</style>