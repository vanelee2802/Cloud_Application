<template>

    <section class="dashboard-card">

        <!-- Überschrift -->
        <div class="card-header">

            <div>
                <h2>Schnellzugriff</h2>
                <p>Häufig verwendete Funktionen</p>
            </div>

        </div>


        <!-- Aktionen -->
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

    </section>

</template>


<style scoped>

/* =========================================================
   SCHNELLZUGRIFF
   ========================================================= */

.dashboard-card {
    padding: 1.5rem;

    background: white;

    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);

    box-sizing: border-box;
}


/* =========================================================
   CARD HEADER
   ========================================================= */

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


/* =========================================================
   AKTIONEN
   ========================================================= */

.quick-actions {
    display: flex;

    flex-direction: column;

    gap: 0.7rem;
}


/* =========================================================
   BUTTON
   ========================================================= */

.action-button {
    padding: 0.9rem;

    border: none;
    border-radius: 7px;

    background: rgba(184, 115, 131, 0.08);

    color: var(--text-color);

    text-align: left;

    cursor: pointer;

    transition: 0.2s;
}

.action-button:hover {
    opacity: 0.8;
}

</style>