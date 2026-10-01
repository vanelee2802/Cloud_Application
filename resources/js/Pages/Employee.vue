<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue'
</script>

<template>
    <pagesLayout>

        <div class="employee-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1>Mitarbeiterbereich</h1>
                    <p>
                        Übersicht über Kundenanfragen, Termine und Mitarbeiter
                    </p>
                </div>

                <div class="employee-date">
                    Montag, 05. Oktober 2026
                </div>
            </div>


            <!-- Statistik -->
            <div class="stats-grid">

                <div class="stat-card">
                    <span class="stat-title">Kundenanfragen</span>
                    <span class="stat-number">5</span>
                    <span class="stat-description">
                        offene Anfragen
                    </span>
                </div>

                <div class="stat-card">
                    <span class="stat-title">Kommende Termine</span>
                    <span class="stat-number">8</span>
                    <span class="stat-description">
                        für diese Woche
                    </span>
                </div>

                <div class="stat-card">
                    <span class="stat-title">Urlaubsanträge</span>
                    <span class="stat-number">2</span>
                    <span class="stat-description">
                        warten auf Bearbeitung
                    </span>
                </div>

                <div class="stat-card">
                    <span class="stat-title">Mitarbeiter</span>
                    <span class="stat-number">4</span>
                    <span class="stat-description">
                        im Studio
                    </span>
                </div>

            </div>


            <!-- Kundenanfragen + kommende Termine -->
            <div class="two-column-layout">

                <!-- Kundenanfragen -->
                <section class="dashboard-section">

                    <div class="section-header">
                        <div>
                            <h2>Kundenanfragen</h2>
                            <p>Neue Anfragen von Kunden</p>
                        </div>

                        <button class="secondary-button">
                            Alle anzeigen
                        </button>
                    </div>

                    <div class="request-list">

                        <div class="request-card">
                            <div class="request-info">
                                <h3>Anna Müller</h3>
                                <p>Nail Design</p>
                                <span>05.10.2026 · 10:00 Uhr</span>
                            </div>

                            <div class="request-actions">
                                <button class="accept-button">
                                    Annehmen
                                </button>

                                <button class="decline-button">
                                    Ablehnen
                                </button>
                            </div>
                        </div>


                        <div class="request-card">
                            <div class="request-info">
                                <h3>Lisa Schmidt</h3>
                                <p>Gelmodellage</p>
                                <span>06.10.2026 · 12:30 Uhr</span>
                            </div>

                            <div class="request-actions">
                                <button class="accept-button">
                                    Annehmen
                                </button>

                                <button class="decline-button">
                                    Ablehnen
                                </button>
                            </div>
                        </div>


                        <div class="request-card">
                            <div class="request-info">
                                <h3>Julia Weber</h3>
                                <p>Maniküre</p>
                                <span>07.10.2026 · 15:00 Uhr</span>
                            </div>

                            <div class="request-actions">
                                <button class="accept-button">
                                    Annehmen
                                </button>

                                <button class="decline-button">
                                    Ablehnen
                                </button>
                            </div>
                        </div>

                    </div>

                </section>


                <!-- Kommende Termine -->
                <section class="dashboard-section">

                    <div class="section-header">
                        <div>
                            <h2>Kommende Termine</h2>
                            <p>Die nächsten Termine im Studio</p>
                        </div>

                        <button class="secondary-button">
                            Kalender
                        </button>
                    </div>

                    <div class="appointment-list">

                        <div class="appointment-card">
                            <div class="appointment-time">
                                <strong>10:00</strong>
                                <span>05.10.</span>
                            </div>

                            <div class="appointment-info">
                                <h3>Anna Müller</h3>
                                <p>Nail Design</p>
                            </div>

                            <span class="status-badge confirmed">
                                Bestätigt
                            </span>
                        </div>


                        <div class="appointment-card">
                            <div class="appointment-time">
                                <strong>12:30</strong>
                                <span>05.10.</span>
                            </div>

                            <div class="appointment-info">
                                <h3>Lisa Schmidt</h3>
                                <p>Gelmodellage</p>
                            </div>

                            <span class="status-badge confirmed">
                                Bestätigt
                            </span>
                        </div>


                        <div class="appointment-card">
                            <div class="appointment-time">
                                <strong>15:00</strong>
                                <span>06.10.</span>
                            </div>

                            <div class="appointment-info">
                                <h3>Julia Weber</h3>
                                <p>Maniküre</p>
                            </div>

                            <span class="status-badge pending">
                                Ausstehend
                            </span>
                        </div>


                        <div class="appointment-card">
                            <div class="appointment-time">
                                <strong>16:30</strong>
                                <span>06.10.</span>
                            </div>

                            <div class="appointment-info">
                                <h3>Laura Fischer</h3>
                                <p>Gelmodellage</p>
                            </div>

                            <span class="status-badge confirmed">
                                Bestätigt
                            </span>
                        </div>

                    </div>

                </section>

            </div>


            <!-- Mitarbeiterschichten -->
            <section class="dashboard-section">

                <div class="section-header">
                    <div>
                        <h2>Mitarbeiterschichten & Verfügbarkeit</h2>
                        <p>Übersicht über die Arbeitszeiten der Mitarbeiter</p>
                    </div>

                    <button class="secondary-button">
                        Schichten verwalten
                    </button>
                </div>


                <div class="shift-table-wrapper">

                    <table class="shift-table">

                        <thead>
                            <tr>
                                <th>Mitarbeiter</th>
                                <th>Montag</th>
                                <th>Dienstag</th>
                                <th>Mittwoch</th>
                                <th>Donnerstag</th>
                                <th>Freitag</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr>
                                <td class="employee-name">
                                    Vanessa
                                </td>

                                <td>09:00 – 17:00</td>
                                <td>09:00 – 17:00</td>
                                <td class="day-off">Frei</td>
                                <td>12:00 – 20:00</td>
                                <td>09:00 – 17:00</td>
                            </tr>


                            <tr>
                                <td class="employee-name">
                                    Lisa
                                </td>

                                <td>12:00 – 20:00</td>
                                <td class="day-off">Frei</td>
                                <td>09:00 – 17:00</td>
                                <td>09:00 – 17:00</td>
                                <td>12:00 – 20:00</td>
                            </tr>


                            <tr>
                                <td class="employee-name">
                                    Julia
                                </td>

                                <td class="day-off">Frei</td>
                                <td>09:00 – 17:00</td>
                                <td>12:00 – 20:00</td>
                                <td>09:00 – 17:00</td>
                                <td class="day-off">Frei</td>
                            </tr>


                            <tr>
                                <td class="employee-name">
                                    Laura
                                </td>

                                <td>09:00 – 17:00</td>
                                <td>12:00 – 20:00</td>
                                <td>09:00 – 17:00</td>
                                <td class="day-off">Frei</td>
                                <td>09:00 – 17:00</td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </section>


            <!-- Verfügbarkeit -->
            <section class="dashboard-section">

                <div class="section-header">
                    <div>
                        <h2>Aktuelle Verfügbarkeit</h2>
                        <p>Wer ist aktuell im Studio verfügbar?</p>
                    </div>
                </div>


                <div class="availability-grid">

                    <div class="availability-card">
                        <div class="availability-person">
                            <div class="avatar">V</div>

                            <div>
                                <h3>Vanessa</h3>
                                <p>Nageldesignerin</p>
                            </div>
                        </div>

                        <span class="availability-status available">
                            Verfügbar
                        </span>
                    </div>


                    <div class="availability-card">
                        <div class="availability-person">
                            <div class="avatar">L</div>

                            <div>
                                <h3>Lisa</h3>
                                <p>Nageldesignerin</p>
                            </div>
                        </div>

                        <span class="availability-status busy">
                            Beschäftigt
                        </span>
                    </div>


                    <div class="availability-card">
                        <div class="availability-person">
                            <div class="avatar">J</div>

                            <div>
                                <h3>Julia</h3>
                                <p>Nageldesignerin</p>
                            </div>
                        </div>

                        <span class="availability-status unavailable">
                            Nicht verfügbar
                        </span>
                    </div>


                    <div class="availability-card">
                        <div class="availability-person">
                            <div class="avatar">L</div>

                            <div>
                                <h3>Laura</h3>
                                <p>Nageldesignerin</p>
                            </div>
                        </div>

                        <span class="availability-status vacation">
                            Urlaub
                        </span>
                    </div>

                </div>

            </section>


            <!-- Urlaubsanträge -->
            <section class="dashboard-section">

                <div class="section-header">
                    <div>
                        <h2>Urlaubsanträge</h2>
                        <p>Offene Urlaubsanträge der Mitarbeiter</p>
                    </div>

                    <button class="secondary-button">
                        Alle Anträge
                    </button>
                </div>


                <div class="vacation-list">

                    <div class="vacation-card">

                        <div class="vacation-info">
                            <h3>Vanessa</h3>
                            <p>10.10.2026 – 14.10.2026</p>
                            <span>5 Urlaubstage</span>
                        </div>

                        <span class="status-badge pending">
                            Ausstehend
                        </span>

                        <div class="vacation-actions">
                            <button class="accept-button">
                                Genehmigen
                            </button>

                            <button class="decline-button">
                                Ablehnen
                            </button>
                        </div>

                    </div>


                    <div class="vacation-card">

                        <div class="vacation-info">
                            <h3>Lisa</h3>
                            <p>20.10.2026 – 22.10.2026</p>
                            <span>3 Urlaubstage</span>
                        </div>

                        <span class="status-badge pending">
                            Ausstehend
                        </span>

                        <div class="vacation-actions">
                            <button class="accept-button">
                                Genehmigen
                            </button>

                            <button class="decline-button">
                                Ablehnen
                            </button>
                        </div>

                    </div>

                </div>

            </section>


            <!-- Weitere Funktionen -->
            <section class="dashboard-section">

                <div class="section-header">
                    <div>
                        <h2>Weitere Bereiche</h2>
                        <p>Schneller Zugriff auf Mitarbeiterfunktionen</p>
                    </div>
                </div>

                <div class="quick-actions">

                    <button class="quick-action">
                        <strong>Mein Kalender</strong>
                        <span>Termine und Schichten</span>
                    </button>

                    <button class="quick-action">
                        <strong>Kunden</strong>
                        <span>Kundenübersicht</span>
                    </button>

                    <button class="quick-action">
                        <strong>Meine Termine</strong>
                        <span>Persönliche Termine</span>
                    </button>

                    <button class="quick-action">
                        <strong>Nachrichten</strong>
                        <span>Benachrichtigungen</span>
                    </button>

                </div>

            </section>

        </div>

    </pagesLayout>
</template>


<style scoped>

.employee-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem 4rem;
}


/* Header */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0 0 0.4rem;
    color: var(--text-color);
    font-size: 2.2rem;
}

.page-header p {
    margin: 0;
    color: #777;
}

.employee-date {
    color: #777;
    font-size: 0.95rem;
}


/* Statistik */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    padding: 1.5rem;
    border-radius: 12px;
    background: var(--background-color);
    box-shadow: 0 4px 15px rgba(109, 59, 71, 0.08);
}

.stat-title {
    display: block;
    font-size: 0.9rem;
    color: #777;
    margin-bottom: 0.5rem;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-color);
}

.stat-description {
    font-size: 0.8rem;
    color: #999;
}


/* Layout */

.two-column-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.dashboard-section {
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 12px;
    background: var(--background-color);
    box-shadow: 0 4px 15px rgba(109, 59, 71, 0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.section-header h2 {
    margin: 0 0 0.3rem;
    color: var(--text-color);
    font-size: 1.3rem;
}

.section-header p {
    margin: 0;
    color: #888;
    font-size: 0.9rem;
}


/* Buttons */

.secondary-button {
    padding: 0.55rem 1rem;
    border: 1px solid #ddd;
    border-radius: 7px;
    background: white;
    color: var(--text-color);
    cursor: pointer;
}

.accept-button,
.decline-button {
    padding: 0.5rem 0.8rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
}

.accept-button {
    background: var(--text-color);
    color: white;
}

.decline-button {
    background: #f1eeee;
    color: #777;
}


/* Kundenanfragen */

.request-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.request-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 9px;
}

.request-info h3 {
    margin: 0 0 0.25rem;
    font-size: 1rem;
    color: var(--text-color);
}

.request-info p {
    margin: 0 0 0.25rem;
    color: #777;
}

.request-info span {
    color: #999;
    font-size: 0.8rem;
}

.request-actions {
    display: flex;
    gap: 0.5rem;
}


/* Termine */

.appointment-list {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.appointment-card {
    display: grid;
    grid-template-columns: 65px 1fr auto;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 9px;
}

.appointment-time strong {
    display: block;
    color: var(--text-color);
}

.appointment-time span {
    font-size: 0.75rem;
    color: #999;
}

.appointment-info h3 {
    margin: 0 0 0.2rem;
    font-size: 0.95rem;
}

.appointment-info p {
    margin: 0;
    color: #777;
    font-size: 0.85rem;
}


/* Status */

.status-badge,
.availability-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.7rem;
    border-radius: 20px;
    font-size: 0.75rem;
}

.confirmed {
    background: #e9f4ec;
    color: #39734a;
}

.pending {
    background: #f7f0df;
    color: #8a6d2f;
}


/* Schichten */

.shift-table-wrapper {
    overflow-x: auto;
}

.shift-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

.shift-table th {
    padding: 0.9rem;
    text-align: left;
    color: #777;
    font-size: 0.8rem;
    font-weight: 600;
    border-bottom: 1px solid #eee;
}

.shift-table td {
    padding: 1rem 0.9rem;
    border-bottom: 1px solid #eee;
    font-size: 0.85rem;
    color: #666;
}

.shift-table .employee-name {
    font-weight: 600;
    color: var(--text-color);
}

.shift-table .day-off {
    color: #aaa;
    font-style: italic;
}


/* Verfügbarkeit */

.availability-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.availability-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 9px;
}

.availability-person {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--text-color);
    color: white;
    font-weight: 600;
}

.availability-person h3 {
    margin: 0 0 0.15rem;
    font-size: 0.9rem;
}

.availability-person p {
    margin: 0;
    color: #999;
    font-size: 0.75rem;
}

.available {
    background: #e9f4ec;
    color: #39734a;
}

.busy {
    background: #f7f0df;
    color: #8a6d2f;
}

.unavailable {
    background: #f1eeee;
    color: #777;
}

.vacation {
    background: #eee8f4;
    color: #665276;
}


/* Urlaub */

.vacation-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.vacation-card {
    display: grid;
    grid-template-columns: 1fr auto auto;
    align-items: center;
    gap: 1.5rem;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 9px;
}

.vacation-info h3 {
    margin: 0 0 0.25rem;
    color: var(--text-color);
}

.vacation-info p {
    margin: 0 0 0.2rem;
    color: #777;
}

.vacation-info span {
    color: #999;
    font-size: 0.8rem;
}

.vacation-actions {
    display: flex;
    gap: 0.5rem;
}


/* Weitere Bereiche */

.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.4rem;
    padding: 1.2rem;
    border: 1px solid #eee;
    border-radius: 9px;
    background: white;
    color: var(--text-color);
    text-align: left;
    cursor: pointer;
}

.quick-action strong {
    font-size: 0.95rem;
}

.quick-action span {
    color: #888;
    font-size: 0.8rem;
}


/* Responsive */

@media (max-width: 1100px) {

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .availability-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
}


@media (max-width: 800px) {

    .employee-page {
        padding: 0 1rem 3rem;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .two-column-layout {
        grid-template-columns: 1fr;
    }

    .request-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .vacation-card {
        grid-template-columns: 1fr;
    }

    .availability-grid {
        grid-template-columns: 1fr;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }
}


@media (max-width: 550px) {

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .section-header {
        flex-direction: column;
    }

    .appointment-card {
        grid-template-columns: 55px 1fr;
    }

    .appointment-card .status-badge {
        grid-column: 2;
        justify-self: start;
    }
}

</style>