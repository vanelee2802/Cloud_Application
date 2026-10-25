
<script setup>
import { ref, computed } from 'vue'

const categories = [
    { label: 'Alle', value: 'all' },
    { label: 'Neumodellage', value: 'new' },
    { label: 'Auffüllen', value: 'refill' },
    { label: 'Extras', value: 'extras' },
]

const selectedCategory = ref('all')

// Beispielpreise – durch deine echten Preise ersetzen
const services = ref([
    {
        id: 1,
        name: 'Neumodellage Natur',
        description: 'Neues Nagelset mit natürlichem Look.',
        price: 45,
        duration: '90 Min.',
        category: 'new',
        popular: true,
    },
    {
        id: 2,
        name: 'Neumodellage mit Farbe',
        description: 'Neues Set mit einer Farbe deiner Wahl.',
        price: 50,
        duration: '100 Min.',
        category: 'new',
        popular: false,
    },
    {
        id: 3,
        name: 'Auffüllen Natur',
        description: 'Auffüllen und Formen der bestehenden Modellage.',
        price: 35,
        duration: '60 Min.',
        category: 'refill',
        popular: false,
    },
    {
        id: 4,
        name: 'Auffüllen mit Farbe',
        description: 'Auffüllen inklusive Farblack.',
        price: 40,
        duration: '75 Min.',
        category: 'refill',
        popular: true,
    },
    {
        id: 5,
        name: 'French Design',
        description: 'Klassische French-Spitzen für einen eleganten Look.',
        price: 8,
        duration: '15 Min. extra',
        category: 'extras',
        popular: false,
    },
    {
        id: 6,
        name: 'Nail Art',
        description: 'Individuelle Verzierungen nach Wunsch.',
        price: 5,
        priceLabel: 'ab 5 €',
        duration: 'Je nach Aufwand',
        category: 'extras',
        popular: false,
    },
])

const filteredServices = computed(() => {
    if (selectedCategory.value === 'all') {
        return services.value
    }

    return services.value.filter(
        service => service.category === selectedCategory.value
    )
})

const formatPrice = (price) =>
    new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 2,
    }).format(price)
</script>

<template>
    <section class="price-section" id="preise">
        <div class="price-container">

            <!-- Überschrift -->
            <div class="price-heading">
                <span class="eyebrow">UNSERE PREISE</span>

                <h1>Schönheit bis in die Fingerspitzen</h1>

                <p>
                    Entdecke unsere Leistungen und finde
                    die passende Behandlung für dich.
                </p>
            </div>

            <!-- Kategorie-Filter -->
            <div class="category-filter">
                <button
                    v-for="category in categories"
                    :key="category.value"
                    type="button"
                    :class="[
                        'filter-button',
                        {
                            active:
                                selectedCategory === category.value
                        }
                    ]"
                    :aria-pressed="
                        selectedCategory === category.value
                    "
                    @click="selectedCategory = category.value"
                >
                    {{ category.label }}
                </button>
            </div>

            <!-- Preiskarten -->
            <div class="price-grid">
                <article
                    v-for="service in filteredServices"
                    :key="service.id"
                    class="price-card"
                >
                    <span
                        v-if="service.popular"
                        class="popular-label"
                    >
                        Beliebt
                    </span>

                    <div class="card-content">
                        <h2>{{ service.name }}</h2>

                        <p class="service-description">
                            {{ service.description }}
                        </p>

                        <div class="service-meta">
                            <span>◷ {{ service.duration }}</span>
                        </div>
                    </div>

                    <div class="card-footer">
                        <span class="price">
                            {{ service.priceLabel || formatPrice(service.price) }}
                        </span>
                    </div>
                </article>
            </div>

            <!-- Hinweis -->
            <div class="price-note">
                <p>
                    <strong>Hinweis:</strong>
                    Die angegebenen Preise sind Beispielwerte.
                    Individuelle Designs und Extras können je nach
                    Aufwand variieren.
                </p>
            </div>

        </div>
    </section>
</template>

<style scoped>
.price-section {
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
    margin: 0 auto 2.5rem;
}

.eyebrow {
    display: inline-block;
    margin-bottom: 0.8rem;

    color: #8f5364;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.18em;
}

.price-heading h1 {
    margin: 0 0 1rem;

    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 500;
    line-height: 1.2;
}

.price-heading p {
    margin: 0;
    color: #8b7b80;
    line-height: 1.8;
}

/* Filter */

.category-filter {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.65rem;
    margin-bottom: 2rem;
}

.filter-button {
    padding: 0.7rem 1.2rem;

    border: 1px solid #ead8de;
    border-radius: 999px;

    background: white;
    color: #754353;

    font: inherit;
    font-size: 0.9rem;
    cursor: pointer;

    transition: all 0.2s ease;
}

.filter-button:hover {
    border-color: #8f5364;
    background: #fbf1f4;
}

.filter-button.active {
    border-color: #8f5364;
    background: #8f5364;
    color: white;
}

/* Karten-Grid */

.price-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.25rem;
}

.price-card {
    position: relative;
    display: flex;
    flex-direction: column;

    min-width: 0;
    padding: 1.5rem;

    border: 1px solid #f0e3e7;
    border-radius: 18px;
    background: white;

    box-shadow: 0 5px 20px rgba(80, 35, 48, 0.04);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;
}

.price-card:hover {
    transform: translateY(-4px);
    border-color: #dfbdc8;
    box-shadow: 0 12px 28px rgba(80, 35, 48, 0.09);
}

.card-content {
    flex: 1;
}

.price-card h2 {
    margin: 0 0 0.75rem;
    padding-right: 0.5rem;

    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.2rem;
    font-weight: 500;
}

.service-description {
    min-height: 3.2rem;
    margin: 0 0 1.25rem;

    color: #8b7b80;
    font-size: 0.9rem;
    line-height: 1.7;
}

.service-meta {
    color: #9b858d;
    font-size: 0.82rem;
}

.card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-top: 1.4rem;
    padding-top: 1rem;

    border-top: 1px solid #f2e8eb;
}

.price {
    color: #8f5364;
    font-size: 1.5rem;
    font-weight: 700;
}

.popular-label {
    position: absolute;
    top: 1rem;
    right: 1rem;

    padding: 0.3rem 0.6rem;
    border-radius: 999px;

    background: #f7e9ee;
    color: #8f5364;

    font-size: 0.7rem;
    font-weight: 700;
}

/* Preishinweis */

.price-note {
    max-width: 760px;
    margin: 2rem auto 0;
    padding: 1rem 1.25rem;

    border: 1px solid #f0e3e7;
    border-radius: 12px;
    background: #fff6f8;

    color: #806c73;
    font-size: 0.85rem;
    line-height: 1.7;
}

.price-note p {
    margin: 0;
}

.price-note strong {
    color: #754353;
}

/* Responsive */

@media (max-width: 900px) {
    .price-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 580px) {
    .price-section {
        padding: 3rem 1rem;
    }

    .price-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .price-card {
        padding: 1.25rem;
    }

    .service-description {
        min-height: 0;
    }

    .category-filter {
        gap: 0.5rem;
    }

    .filter-button {
        padding: 0.6rem 0.9rem;
        font-size: 0.82rem;
    }
}
</style>
