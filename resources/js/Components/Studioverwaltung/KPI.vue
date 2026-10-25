<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const employees = computed(() => page.props.employees ?? [])
const vacationRequests = computed(() => page.props.vacationRequests ?? [])

const employeeCount = computed(() => employees.value.length)

const vacationCount = computed(() => vacationRequests.value.length)

/*
|--------------------------------------------------------------------------
| GEÄNDERT: Anfragen und Termine kommen jetzt vom Server
|--------------------------------------------------------------------------
| Vorher aus page.props – die Seiten geben diese Daten aber nicht mit,
| deshalb stand dort immer 0. Jetzt dieselben Quellen wie die Listen.
| null = (noch) nicht bekannt bzw. kein Zugriff → Anzeige „–“
*/

const requestCount = ref(null)
const appointmentCount = ref(null)

let timer = null

async function fetchCount(url) {
    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
            return null
        }

        const data = await response.json()

        return Array.isArray(data) ? data.length : null
    } catch {
        return null
    }
}

async function load() {
    const [requests, bookings] = await Promise.all([
        fetchCount('/admin/appointments/requests'),
        fetchCount('/admin/appointments/bookings'),
    ])

    requestCount.value = requests
    appointmentCount.value = bookings
}

function display(value) {
    return value === null ? '–' : value
}

onMounted(() => {
    load()
    timer = setInterval(load, 60000)   // jede Minute neu laden
})

onBeforeUnmount(() => {
    clearInterval(timer)
})
</script>

<template>

    <!-- ========================================================= -->
    <!-- KUNDENANFRAGEN -->
    <!-- ========================================================= -->

    <article class="stat-card">

        <div class="stat-icon">
            ♡
        </div>

        <div class="stat-content">

            <span class="stat-title">
                Kundenanfragen
            </span>

            <strong class="stat-number">
                {{ display(requestCount) }}
            </strong>

            <span class="stat-description">
                {{ requestCount === null ? 'bearbeitet die Schichtleitung' : 'offene Anfragen' }}
            </span>

        </div>

    </article>


    <!-- ========================================================= -->
    <!-- KOMMENDE TERMINE -->
    <!-- ========================================================= -->

    <article class="stat-card">

        <div class="stat-icon">
            ◷
        </div>

        <div class="stat-content">

            <span class="stat-title">
                Kommende Termine
            </span>

            <strong class="stat-number">
                {{ display(appointmentCount) }}
            </strong>

            <span class="stat-description">
                bezahlte Termine ab heute
            </span>

        </div>

    </article>


    <!-- ========================================================= -->
    <!-- URLAUBSANTRÄGE -->
    <!-- ========================================================= -->

    <article class="stat-card">

        <div class="stat-icon">
            ✦
        </div>

        <div class="stat-content">

            <span class="stat-title">
                Urlaubsanträge
            </span>

            <strong class="stat-number">
                {{ vacationCount }}
            </strong>

            <span class="stat-description">
                warten auf Bearbeitung
            </span>

        </div>

    </article>


    <!-- ========================================================= -->
    <!-- MITARBEITER -->
    <!-- ========================================================= -->

    <article class="stat-card">

        <div class="stat-icon">
            ♧
        </div>

        <div class="stat-content">

            <span class="stat-title">
                Mitarbeiter
            </span>

            <strong class="stat-number">
                {{ employeeCount }}
            </strong>

            <span class="stat-description">
                im Studio
            </span>

        </div>

    </article>

</template>

<style scoped>

/* ========================================================= */
/* STATISTIK-KARTE */
/* ========================================================= */

.stat-card {
    display: flex;
    align-items: center;

    gap: 1rem;

    padding: 1.25rem;

    background-color: white;

    border: 1px solid rgba(143, 83, 100, 0.12);

    border-radius: 12px;

    box-shadow:
        0 4px 12px rgba(80, 35, 48, 0.06),
        0 8px 20px rgba(80, 35, 48, 0.04);
}


/* ========================================================= */
/* ICON */
/* ========================================================= */

.stat-icon {
    width: 48px;
    height: 48px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background-color: #f5e7eb;

    color: #8f5364;

    font-size: 1.4rem;
}


/* ========================================================= */
/* INHALT */
/* ========================================================= */

.stat-content {
    display: flex;
    flex-direction: column;

    min-width: 0;
}


/* ========================================================= */
/* TITEL */
/* ========================================================= */

.stat-title {
    margin-bottom: 0.15rem;

    color: #6f5961;

    font-size: 0.85rem;
    font-weight: 600;
}


/* ========================================================= */
/* ZAHL */
/* ========================================================= */

.stat-number {
    color: #4f3540;

    font-size: 1.8rem;
    font-weight: 700;

    line-height: 1.2;
}


/* ========================================================= */
/* BESCHREIBUNG */
/* ========================================================= */

.stat-description {
    margin-top: 0.15rem;

    color: #8a7a80;

    font-size: 0.75rem;
}


/* ========================================================= */
/* RESPONSIVE */
/* ========================================================= */

@media (max-width: 768px) {

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 42px;
        height: 42px;

        font-size: 1.2rem;
    }

    .stat-number {
        font-size: 1.5rem;
    }

}


@media (max-width: 480px) {

    .stat-card {
        gap: 0.75rem;
    }

    .stat-icon {
        width: 38px;
        height: 38px;

        border-radius: 10px;

        font-size: 1rem;
    }

    .stat-title {
        font-size: 0.75rem;
    }

    .stat-number {
        font-size: 1.35rem;
    }

    .stat-description {
        font-size: 0.7rem;
    }

}

</style>