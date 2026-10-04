<script setup>
</script>

<template>

    <section class="dashboard-card">

        <!-- Überschrift -->
        <div class="card-header">

            <div>
                <h2>Aktuelle Buchungen</h2>
                <p>Die neuesten Termine</p>
            </div>

            <button class="secondary-button">
                Alle anzeigen
            </button>

        </div>


        <!-- Buchungen -->
        <div class="appointment-list">

            <!-- Buchung 1 -->
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


            <!-- Buchung 2 -->
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


            <!-- Buchung 3 -->
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

    </section>

</template>


<style scoped>

/* =========================================================
   AKTUELLE BUCHUNGEN
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
   BUTTON
   ========================================================= */

.secondary-button {
    padding: 0.5rem 0.8rem;

    border: none;
    border-radius: 7px;

    background: rgba(184, 115, 131, 0.08);

    color: var(--text-color);

    cursor: pointer;

    transition: 0.2s;
}

.secondary-button:hover {
    opacity: 0.8;
}


/* =========================================================
   BUCHUNGEN
   ========================================================= */

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


/* =========================================================
   DATUM
   ========================================================= */

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


/* =========================================================
   BUCHUNGSINFORMATIONEN
   ========================================================= */

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


/* =========================================================
   STATUS
   ========================================================= */

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


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {

    .appointment {
        align-items: flex-start;
    }

    .appointment .status {
        margin-left: auto;
    }

    .card-header {
        align-items: flex-start;

        gap: 1rem;
    }

}

</style>