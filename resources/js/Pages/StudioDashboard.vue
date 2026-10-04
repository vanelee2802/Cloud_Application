<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue'
import Kennzahlen from '@/Components/StudioDashboard/Kennzahlen.vue'
import Buchungen from '@/Components/StudioDashboard/Buchungen.vue'
import Schnellzugriff from '@/Components/StudioDashboard/Schnellzugriff.vue'

</script>

<template>
    <pagesLayout>

        <div class="dashboard">

            <!-- Überschrift -->
            <header class="dashboard-header">
                <div>
                    <h1>Studio Dashboard</h1>
                    <p>Übersicht über dein Nagelstudio</p>
                </div>
            </header>


            <!-- Kennzahlen -->
            <section class="stats-grid">
                <KPI />
            </section>


            <!-- Buchungen + Schnellzugriff -->
            <section class="dashboard-grid">
                <Buchungen />
                <Schnellzugriff />
            </section>

        </div>

    </pagesLayout>
</template>

<style scoped>

.dashboard {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    box-sizing: border-box;
}


/* =========================================================
   HEADER
   ========================================================= */

.dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    margin: 0;
    color: var(--text-color);
    font-size: 2rem;
    font-weight: 700;
}

.dashboard-header p {
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
   HAUPTBEREICH
   ========================================================= */

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
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
    .dashboard {
        padding: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-header {
        align-items: flex-start;
        gap: 1rem;
    }

    .dashboard-header h1 {
        font-size: 1.7rem;
    }
}

</style>