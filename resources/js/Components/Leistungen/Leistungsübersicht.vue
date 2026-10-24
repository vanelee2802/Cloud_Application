
<script setup>
// Statische Preisübersicht – Beispielpreise
</script>

<template>
    <section class="price-overview" id="preise">
        <div class="price-container">

            <!-- Überschrift -->
            <div class="price-heading">
                <span class="eyebrow">UNSERE PREISE</span>

                <h2>Unsere Leistungen & Preise</h2>

                <p>
                    Entdecke unsere Behandlungen und finde
                    deinen perfekten Look.
                </p>
            </div>

            <!-- Preiskategorien -->
            <div class="price-grid">

                <!-- Neumodellage -->
                <article class="price-card">
                    <div class="card-heading">
                        <h3>Neumodellage</h3>
                        <span class="card-number">01</span>
                    </div>

                    <p class="card-description">
                        Dein neues Nageldesign, ganz nach deinen Wünschen.
                    </p>

                    <div class="price-row">
                        <span>Natur</span>
                        <strong>45 €</strong>
                    </div>

                    <div class="price-row">
                        <span>Mit Farbe</span>
                        <strong>50 €</strong>
                    </div>

                    <div class="price-row">
                        <span>Mit French</span>
                        <strong>55 €</strong>
                    </div>
                </article>

                <!-- Auffüllen -->
                <article class="price-card">
                    <div class="card-heading">
                        <h3>Auffüllen</h3>
                        <span class="card-number">02</span>
                    </div>

                    <p class="card-description">
                        Für gepflegte Nägel und einen frischen Look.
                    </p>

                    <div class="price-row">
                        <span>Natur</span>
                        <strong>35 €</strong>
                    </div>

                    <div class="price-row">
                        <span>Mit Farbe</span>
                        <strong>40 €</strong>
                    </div>

                    <div class="price-row">
                        <span>Mit French</span>
                        <strong>45 €</strong>
                    </div>
                </article>

                <!-- Extras -->
                <article class="price-card">
                    <div class="card-heading">
                        <h3>Extras & Nail Art</h3>
                        <span class="card-number">03</span>
                    </div>

                    <p class="card-description">
                        Kleine Details für dein individuelles Design.
                    </p>

                    <div class="price-row">
                        <span>Nail Art</span>
                        <strong>ab 5 €</strong>
                    </div>

                    <div class="price-row">
                        <span>Chrome-Effekt</span>
                        <strong>ab 5 €</strong>
                    </div>

                    <div class="price-row">
                        <span>Nagelreparatur</span>
                        <strong>ab 3 €</strong>
                    </div>
                </article>

            </div>

            <!-- Hinweis -->
            <div class="price-note">
                <span class="note-icon">♡</span>

                <p>
                    Alle Preise dienen zunächst als Beispiel.
                    Aufwendige Designs und Sonderwünsche können
                    je nach Aufwand zusätzlich berechnet werden.
                </p>
            </div>

        </div>
    </section>
</template>

<style scoped>
.price-overview {
    padding: 4rem 1.5rem;
    background: #fffafb;
    color: #3d2930;
}

.price-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Überschrift */

.price-heading {
    text-align: center;
    max-width: 700px;
    margin: 0 auto 3rem;
}

.eyebrow {
    display: inline-block;
    margin-bottom: 0.8rem;
    color: #8f5364;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.18em;
}

.price-heading h2 {
    margin: 0 0 1rem;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 500;
}

.price-heading p {
    margin: 0;
    color: #8b7b80;
    line-height: 1.8;
}

/* Karten */

.price-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
}

.price-card {
    padding: 1.75rem;
    border: 1px solid #f0e3e7;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 5px 20px rgba(80, 35, 48, 0.04);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.price-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(80, 35, 48, 0.09);
}

/* Kartenüberschrift */

.card-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0e3e7;
}

.card-heading h3 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.25rem;
    font-weight: 500;
}

.card-number {
    color: #d8b7c1;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.4rem;
}

/* Beschreibung */

.card-description {
    min-height: 3.2rem;
    margin: 1rem 0 1.5rem;
    color: #8b7b80;
    font-size: 0.9rem;
    line-height: 1.7;
}

/* Preise */

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem 0;
    border-bottom: 1px solid #f6edf0;
    font-size: 0.92rem;
}

.price-row:last-child {
    border-bottom: none;
}

.price-row span {
    color: #746269;
}

.price-row strong {
    color: #8f5364;
    white-space: nowrap;
    font-size: 1rem;
}

/* Hinweis */

.price-note {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    max-width: 850px;
    margin: 2rem auto 0;
    padding: 1.2rem 1.5rem;
    border: 1px solid #f0e3e7;
    border-radius: 12px;
    background: #fff6f8;
}

.note-icon {
    color: #8f5364;
    font-size: 1.3rem;
}

.price-note p {
    margin: 0;
    color: #806c73;
    font-size: 0.85rem;
    line-height: 1.7;
}

/* Responsive */

@media (max-width: 900px) {
    .price-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .price-overview {
        padding: 3rem 1rem;
    }

    .price-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .price-card {
        padding: 1.4rem;
    }

    .card-description {
        min-height: 0;
    }

    .price-note {
        padding: 1rem;
    }
}
</style>
