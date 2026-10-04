<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue'

import KPI from '@/Components/Studioverwaltung/KPI.vue'
import Kundenanfragen from '@/Components/Studioverwaltung/Kundenanfragen.vue'
import KommendeTermine from '@/Components/Studioverwaltung/KommendeTermine.vue'
import MitarbeiterVerwalten from '@/Components/Studioverwaltung/MitarbeiterVerwalten.vue'
import Mitarbeiterschichten from '@/Components/Studioverwaltung/Mitarbeiterschichten.vue'
import Urlaubsanträge from '@/Components/Studioverwaltung/Urlaubsanträge.vue'
</script>

<template>
    <pagesLayout>

        <div class="employee-page">

            <!-- Überschrift -->
            <header class="page-header">
                <div>
                    <h1>Studio Dashboard</h1>
                    <p>Übersicht über dein Nagelstudio</p>
                </div>
            </header>


            <!-- Kennzahlen -->
            <section class="stats-grid">
                <KPI />
            </section>


            <!-- Termine + Kundenanfragen -->
            <section class="dashboard-grid">

                <KommendeTermine />

                <Kundenanfragen />

            </section>


            <!-- Mitarbeiter verwalten -->
            <section class="dashboard-full">

                <MitarbeiterVerwalten />

            </section>


            <!-- Mitarbeiterschichten + Urlaubsanträge -->
            <section class="dashboard-grid">

                <Mitarbeiterschichten />

                <Urlaubsanträge />

            </section>

        </div>

    </pagesLayout>
</template>


<style scoped>

/* =========================================================
   EMPLOYEE DASHBOARD
   ========================================================= */

.employee-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;

    box-sizing: border-box;
}


/* =========================================================
   HEADER
   ========================================================= */

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0;

    color: var(--text-color);

    font-size: 2rem;
    font-weight: 700;
}

.page-header p {
    margin: 0.4rem 0 0;

    color: var(--text-color);
    opacity: 0.6;
}


/* =========================================================
   KENNZAHLEN
   ========================================================= */

.stats-grid {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 1rem;

    margin-bottom: 1.5rem;
}


/* =========================================================
   HAUPTBEREICHE
   ========================================================= */

.dashboard-grid {
    display: grid;

    grid-template-columns: 2fr 1fr;

    gap: 1.5rem;

    margin-bottom: 1.5rem;
}


/* =========================================================
   VOLLBREITER BEREICH
   ========================================================= */

.dashboard-full {
    width: 100%;

    margin-bottom: 1.5rem;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1000px) {

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 700px) {

    .employee-page {
        padding: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        align-items: flex-start;

        gap: 1rem;
    }

    .page-header h1 {
        font-size: 1.7rem;
    }

}

</style>