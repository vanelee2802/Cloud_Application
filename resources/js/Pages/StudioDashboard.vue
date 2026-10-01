<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue'
</script>

<template>
    <pagesLayout>

        <div class="dashboard">

            <!-- Überschrift -->
            <div class="dashboard-header">
                <div>
                    <h1>Studio Dashboard</h1>
                    <p>Übersicht über dein Nagelstudio</p>
                </div>

                <button class="profile-button">
                    Admin
                </button>
            </div>


            <!-- Statistik -->
            <section class="stats">

                <div class="stat-card">
                    <span class="stat-title">
                        Offene Buchungen
                    </span>

                    <strong>12</strong>

                    <span class="stat-description">
                        warten auf Bestätigung
                    </span>
                </div>


                <div class="stat-card">
                    <span class="stat-title">
                        Bestätigte Buchungen
                    </span>

                    <strong>24</strong>

                    <span class="stat-description">
                        für diese Woche
                    </span>
                </div>


                <div class="stat-card">
                    <span class="stat-title">
                        Offene Designs
                    </span>

                    <strong>5</strong>

                    <span class="stat-description">
                        warten auf Bearbeitung
                    </span>
                </div>


                <div class="stat-card">
                    <span class="stat-title">
                        Umsatz
                    </span>

                    <strong>1.240 €</strong>

                    <span class="stat-description">
                        diesen Monat
                    </span>
                </div>

            </section>


            <!-- Hauptbereich -->
            <section class="dashboard-grid">

                <!-- Buchungen -->
                <div class="dashboard-card">

                    <div class="card-header">
                        <div>
                            <h2>Aktuelle Buchungen</h2>
                            <p>Die neuesten Termine</p>
                        </div>

                        <button class="secondary-button">
                            Alle anzeigen
                        </button>
                    </div>


                    <div class="appointment-list">

                        <div class="appointment">

                            <div class="appointment-date">
                                <strong>24</strong>
                                <span>SEP</span>
                            </div>

                            <div class="appointment-info">
                                <strong>Anna Müller</strong>
                                <span>
                                    French Nails · 14:00 Uhr
                                </span>
                            </div>

                            <span class="status pending">
                                Offen
                            </span>

                        </div>


                        <div class="appointment">

                            <div class="appointment-date">
                                <strong>25</strong>
                                <span>SEP</span>
                            </div>

                            <div class="appointment-info">
                                <strong>Lisa Schmidt</strong>
                                <span>
                                    Gel Nails · 16:00 Uhr
                                </span>
                            </div>

                            <span class="status confirmed">
                                Bestätigt
                            </span>

                        </div>


                        <div class="appointment">

                            <div class="appointment-date">
                                <strong>26</strong>
                                <span>SEP</span>
                            </div>

                            <div class="appointment-info">
                                <strong>Sarah Weber</strong>
                                <span>
                                    Design Nails · 11:00 Uhr
                                </span>
                            </div>

                            <span class="status confirmed">
                                Bestätigt
                            </span>

                        </div>

                    </div>

                </div>


                <!-- Schnellzugriff -->
                <div class="dashboard-card">

                    <div class="card-header">
                        <div>
                            <h2>Schnellzugriff</h2>
                            <p>Häufig verwendete Funktionen</p>
                        </div>
                    </div>


                    <div class="quick-actions">

                        <button class="action-button">
                            + Neue Buchung
                        </button>

                        <button class="action-button">
                            Designs verwalten
                        </button>

                        <button class="action-button">
                            Services bearbeiten
                        </button>

                        <button class="action-button">
                            Mitarbeiter verwalten
                        </button>

                    </div>

                </div>

            </section>


            <!-- Ausstehende Designs -->
            <section class="dashboard-card designs-card">

                <div class="card-header">

                    <div>
                        <h2>Ausstehende Designs</h2>

                        <p>
                            Designs, die noch bearbeitet werden müssen
                        </p>
                    </div>

                    <button class="secondary-button">
                        Alle anzeigen
                    </button>

                </div>


                <div class="design-list">

                    <div class="design-item">

                        <div class="design-placeholder">
                            ✦
                        </div>

                        <div class="design-info">
                            <strong>Design #1024</strong>
                            <span>Anna Müller</span>
                        </div>

                        <button class="primary-button">
                            Öffnen
                        </button>

                    </div>


                    <div class="design-item">

                        <div class="design-placeholder">
                            ✦
                        </div>

                        <div class="design-info">
                            <strong>Design #1025</strong>
                            <span>Lisa Schmidt</span>
                        </div>

                        <button class="primary-button">
                            Öffnen
                        </button>

                    </div>

                </div>

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
}


/* Header */

.dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 2rem;
}

.dashboard-header h1 {
    margin: 0;

    font-size: 2rem;
    font-weight: 700;
}

.dashboard-header p {
    margin: 0.4rem 0 0;

    opacity: 0.6;
}

.profile-button {
    padding: 0.7rem 1.2rem;

    border: none;
    border-radius: 8px;

    background-color: var(--text-color);
    color: white;

    cursor: pointer;
}


/* Statistik */

.stats {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 1rem;

    margin-bottom: 1.5rem;
}

.stat-card {
    padding: 1.4rem;

    background: white;

    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);
}

.stat-title {
    display: block;

    margin-bottom: 0.7rem;

    font-size: 0.9rem;

    opacity: 0.7;
}

.stat-card strong {
    display: block;

    margin-bottom: 0.4rem;

    font-size: 1.8rem;
}

.stat-description {
    font-size: 0.8rem;

    opacity: 0.55;
}


/* Dashboard Grid */

.dashboard-grid {
    display: grid;

    grid-template-columns: 2fr 1fr;

    gap: 1.5rem;

    margin-bottom: 1.5rem;
}

.dashboard-card {
    padding: 1.5rem;

    background: white;

    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);

    box-sizing: border-box;
}


/* Card Header */

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


/* Buttons */

.primary-button,
.secondary-button,
.action-button {
    border: none;

    border-radius: 7px;

    cursor: pointer;

    transition: 0.2s;
}

.primary-button {
    padding: 0.5rem 0.9rem;

    background: var(--text-color);

    color: white;
}

.secondary-button {
    padding: 0.5rem 0.8rem;

    background: rgba(184, 115, 131, 0.08);

    color: var(--text-color);
}

.primary-button:hover,
.secondary-button:hover,
.action-button:hover {
    opacity: 0.8;
}


/* Buchungen */

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


/* Status */

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


/* Schnellzugriff */

.quick-actions {
    display: flex;

    flex-direction: column;

    gap: 0.7rem;
}

.action-button {
    padding: 0.9rem;

    background: rgba(184, 115, 131, 0.08);

    color: var(--text-color);

    text-align: left;
}


/* Designs */

.designs-card {
    margin-bottom: 2rem;
}

.design-list {
    display: flex;

    flex-direction: column;
}

.design-item {
    display: flex;

    align-items: center;

    gap: 1rem;

    padding: 1rem 0;

    border-top: 1px solid rgba(109, 59, 71, 0.08);
}

.design-placeholder {
    width: 45px;
    height: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    background: rgba(184, 115, 131, 0.1);
}

.design-info {
    display: flex;

    flex-direction: column;

    flex: 1;
}

.design-info span {
    margin-top: 0.2rem;

    font-size: 0.8rem;

    opacity: 0.6;
}


/* Responsive */

@media (max-width: 1000px) {

    .stats {
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

    .stats {
        grid-template-columns: 1fr;
    }

    .dashboard-header {
        align-items: flex-start;

        gap: 1rem;
    }

}

</style>