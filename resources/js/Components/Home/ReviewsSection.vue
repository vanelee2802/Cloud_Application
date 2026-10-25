<script setup>
import { ref, computed, onMounted } from 'vue'

// Bewertungen von Kundinnen nach ihrem Termin (eigenes Formular, kein Google)

const props = defineProps({
    // null = alle Studios
    studioId: { type: Number, default: null },
    limit:    { type: Number, default: 6 },
})

const average = ref(null)
const count = ref(0)
const reviews = ref([])
const loading = ref(true)
const error = ref(false)

onMounted(async () => {
    const params = new URLSearchParams({ limit: String(props.limit) })

    if (props.studioId) {
        params.set('nail_studio_id', String(props.studioId))
    }

    try {
        const response = await fetch(`/reviews/public?${params}`, {
            headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
            throw new Error()
        }

        const data = await response.json()

        average.value = data.average
        count.value = data.count
        reviews.value = data.reviews
    } catch {
        error.value = true
    } finally {
        loading.value = false
    }
})

const averageLabel = computed(() =>
    average.value === null ? '' : average.value.toFixed(1).replace('.', ',')
)

const countLabel = computed(() =>
    count.value === 1 ? '1 Bewertung' : `${count.value} Bewertungen`
)

// Anteil der Sterne für die Anzeige des Durchschnitts, z. B. 4,6 → 92 %
const averagePercent = computed(() =>
    average.value === null ? 0 : Math.round((average.value / 5) * 100)
)
</script>

<template>
    <section class="reviews-section" aria-labelledby="reviews-title">
        <div class="reviews-container">

            <div class="reviews-heading">
                <h2 id="reviews-title">Das sagen unsere Kundinnen</h2>

                <div v-if="!loading && count > 0" class="average">
                    <span class="average-number">{{ averageLabel }}</span>

                    <span class="average-stars" :aria-label="`${averageLabel} von 5 Sternen`">
                        <span class="stars-base" aria-hidden="true">★★★★★</span>
                        <span
                            class="stars-fill"
                            :style="{ width: `${averagePercent}%` }"
                            aria-hidden="true"
                        >★★★★★</span>
                    </span>

                    <span class="average-count">{{ countLabel }}</span>
                </div>
            </div>

            <p v-if="loading" class="state">Bewertungen werden geladen …</p>

            <!-- Fehler oder noch keine Bewertungen: Bereich bleibt ruhig -->
            <p v-else-if="error || count === 0" class="state">
                Noch keine Bewertungen. Nach deinem Termin kannst du als Erste erzählen, wie es dir gefallen hat.
            </p>

            <div v-else class="review-grid">
                <article
                    v-for="review in reviews"
                    :key="review.id"
                    class="review-card"
                >
                    <div class="review-stars" :aria-label="`${review.rating} von 5 Sternen`">
                        <span
                            v-for="star in 5"
                            :key="star"
                            :class="{ filled: star <= review.rating }"
                            aria-hidden="true"
                        >★</span>
                    </div>

                    <p v-if="review.comment" class="review-text">{{ review.comment }}</p>

                    <p class="review-meta">
                        <strong>{{ review.author_name }}</strong>
                        <span v-if="review.studio && !studioId">, {{ review.studio }}</span>
                        <span v-if="review.date">, {{ review.date }}</span>
                    </p>
                </article>
            </div>

        </div>
    </section>
</template>

<style scoped>
.reviews-section {
    padding: 4rem 1.5rem;
    background: #ffffff;
    color: #3d2930;
}

.reviews-container {
    max-width: 1200px;
    margin: 0 auto;
}

.reviews-heading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2.5rem;
    text-align: center;
}

.reviews-heading h2 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2rem, 4vw, 2.6rem);
    font-weight: 500;
}

/* Durchschnitt */

.average {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 0.6rem 0.9rem;
}

.average-number {
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 2.6rem;
    line-height: 1;
}

.average-stars {
    position: relative;
    display: inline-block;
    font-size: 1.5rem;
    line-height: 1;
    letter-spacing: 0.1em;
}

.stars-base {
    color: #ead7dd;
}

.stars-fill {
    position: absolute;
    top: 0;
    left: 0;
    overflow: hidden;
    color: #c98a3d;
    white-space: nowrap;
}

.average-count {
    color: #8b7b80;
    font-size: 0.9rem;
}

/* Karten */

.review-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.25rem;
}

.review-card {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    padding: 1.5rem;
    border: 1px solid #f0e3e7;
    border-radius: 18px;
    background: #fffafb;
}

.review-stars {
    color: #ead7dd;
    font-size: 1.05rem;
    letter-spacing: 0.08em;
}

.review-stars .filled {
    color: #c98a3d;
}

.review-text {
    flex: 1;
    margin: 0;
    color: #5f4a51;
    font-size: 0.95rem;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.review-meta {
    margin: 0;
    color: #8b7b80;
    font-size: 0.82rem;
}

.review-meta strong {
    color: #754353;
}

.state {
    margin: 0 auto;
    max-width: 520px;
    color: #8b7b80;
    text-align: center;
    line-height: 1.7;
}

@media (max-width: 900px) {
    .review-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .reviews-section {
        padding: 3rem 1rem;
    }

    .review-grid {
        grid-template-columns: 1fr;
    }
}
</style>
