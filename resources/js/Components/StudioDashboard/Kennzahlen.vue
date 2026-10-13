<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const customerRequests = computed(() => page.props.customerRequests ?? [])
const appointments = computed(() => page.props.appointments ?? [])
const vacationRequests = computed(() => page.props.vacationRequests ?? [])
const employees = computed(() => page.props.employees ?? [])

const requestCount = computed(() => customerRequests.value.length)
const appointmentCount = computed(() => appointments.value.length)
const vacationCount = computed(() => vacationRequests.value.length)
const employeeCount = computed(() => employees.value.length)
</script>


<template>

    <div class="stat-card">

        <span class="stat-title">
            Offene Buchungen
        </span>

        <strong>
            {{ requestCount }}
        </strong>

        <span class="stat-description">
            warten auf Bestätigung
        </span>

    </div>


    <div class="stat-card">

        <span class="stat-title">
            Bestätigte Buchungen
        </span>

        <strong>
            {{ appointmentCount }}
        </strong>

        <span class="stat-description">
            für diese Woche
        </span>

    </div>


    <div class="stat-card">

        <span class="stat-title">
            Offene Designs
        </span>

        <strong>
            {{ vacationCount }}
        </strong>

        <span class="stat-description">
            warten auf Bearbeitung
        </span>

    </div>


    <div class="stat-card">

        <span class="stat-title">
            Mitarbeiter
        </span>

        <strong>
            {{ employeeCount }}
        </strong>

        <span class="stat-description">
            im Studio
        </span>

    </div>

</template>


<style scoped>

/* =========================================================
   KENNZAHLEN
   ========================================================= */

.stat-card {
    padding: 1.4rem;

    background: white;

    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);

    box-sizing: border-box;

    transition: 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);

    box-shadow:
        0 4px 12px rgba(184, 115, 131, 0.06),
        0 10px 28px rgba(109, 59, 71, 0.08);
}


/* =========================================================
   TITEL
   ========================================================= */

.stat-title {
    display: block;

    margin-bottom: 0.7rem;

    color: var(--text-color);

    font-size: 0.9rem;

    opacity: 0.7;
}


/* =========================================================
   WERT
   ========================================================= */

.stat-card strong {
    display: block;

    margin-bottom: 0.4rem;

    color: var(--text-color);

    font-size: 1.8rem;
}


/* =========================================================
   BESCHREIBUNG
   ========================================================= */

.stat-description {
    color: var(--text-color);

    font-size: 0.8rem;

    opacity: 0.55;
}

</style>