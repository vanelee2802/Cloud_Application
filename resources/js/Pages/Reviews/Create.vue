<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import pagesLayout from '@/Layouts/pagesLayout.vue'

const props = defineProps({
    appointment:     { type: Object,  required: true },
    defaultName:     { type: String,  default: '' },
    alreadyReviewed: { type: Boolean, default: false },
    submitUrl:       { type: String,  default: null },
})

const form = useForm({
    rating:      0,
    comment:     '',
    author_name: props.defaultName,
})

const STARS = [1, 2, 3, 4, 5]

const ratingLabels = {
    1: 'Nicht zufrieden',
    2: 'Geht so',
    3: 'Okay',
    4: 'Gut',
    5: 'Sehr gut',
}

function submit() {
    if (!props.submitUrl || form.rating === 0) {
        return
    }

    form.post(props.submitUrl, { preserveScroll: true })
}
</script>

<template>
    <Head title="Termin bewerten" />

    <pagesLayout>
        <main class="review-page">
            <section class="review-card">

                <!-- Schon bewertet -->
                <template v-if="alreadyReviewed">
                    <span class="thanks-icon" aria-hidden="true">♡</span>
                    <h1>Danke für deine Bewertung!</h1>
                    <p class="lead">
                        Deine Bewertung für {{ appointment.service }} bei {{ appointment.studio }}
                        ist gespeichert. Wir freuen uns, dich bald wiederzusehen.
                    </p>
                    <a href="/" class="home-link">Zur Startseite</a>
                </template>

                <!-- Formular -->
                <template v-else>
                    <h1>Wie war dein Termin?</h1>
                    <p class="lead">
                        {{ appointment.service }} bei {{ appointment.studio }}, {{ appointment.date }}
                    </p>

                    <form @submit.prevent="submit">

                        <fieldset class="stars-field">
                            <legend>Deine Bewertung</legend>

                            <div class="stars">
                                <label
                                    v-for="star in STARS"
                                    :key="star"
                                    class="star"
                                    :class="{ filled: star <= form.rating }"
                                >
                                    <input
                                        v-model="form.rating"
                                        type="radio"
                                        name="rating"
                                        :value="star"
                                        class="star-input"
                                    >
                                    <span aria-hidden="true">★</span>
                                    <span class="sr-only">{{ star }} von 5 Sternen</span>
                                </label>
                            </div>

                            <p class="rating-label" aria-live="polite">
                                {{ form.rating ? ratingLabels[form.rating] : 'Tippe auf einen Stern' }}
                            </p>

                            <small v-if="form.errors.rating" class="error">{{ form.errors.rating }}</small>
                        </fieldset>

                        <label class="field">
                            <span>Was hat dir gefallen? (optional)</span>
                            <textarea
                                v-model="form.comment"
                                rows="4"
                                maxlength="1000"
                                placeholder="Erzähl anderen Kundinnen von deinem Besuch"
                            />
                            <small v-if="form.errors.comment" class="error">{{ form.errors.comment }}</small>
                        </label>

                        <label class="field">
                            <span>Dein Name, so wie er angezeigt wird</span>
                            <input
                                v-model.trim="form.author_name"
                                type="text"
                                maxlength="60"
                                required
                                autocomplete="given-name"
                            >
                            <small class="hint">Zum Beispiel nur dein Vorname.</small>
                            <small v-if="form.errors.author_name" class="error">{{ form.errors.author_name }}</small>
                        </label>

                        <button
                            type="submit"
                            class="submit-button"
                            :disabled="form.processing || form.rating === 0 || !form.author_name"
                        >
                            {{ form.processing ? 'Wird gesendet …' : 'Bewertung absenden' }}
                        </button>

                    </form>
                </template>

            </section>
        </main>
    </pagesLayout>
</template>

<style scoped>
.review-page {
    display: flex;
    justify-content: center;
    padding: 2rem 1rem 4rem;
    background: #fffafb;
    color: #3d2930;
}

.review-card {
    width: 100%;
    max-width: 520px;
    padding: 2rem 1.75rem;
    border: 1px solid #f0e3e7;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 5px 20px rgba(80, 35, 48, 0.05);
    text-align: center;
}

h1 {
    margin: 0 0 0.5rem;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(1.6rem, 4vw, 2rem);
    font-weight: 500;
}

.lead {
    margin: 0 0 1.75rem;
    color: #8b7b80;
    line-height: 1.6;
}

form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    text-align: left;
}

/* Sterne */

.stars-field {
    margin: 0;
    padding: 0;
    border: none;
    text-align: center;
}

.stars-field legend {
    margin: 0 auto 0.5rem;
    color: #754353;
    font-size: 0.9rem;
    font-weight: 600;
}

.stars {
    display: flex;
    justify-content: center;
    gap: 0.35rem;
}

.star {
    position: relative;
    color: #ead7dd;
    font-size: 2.4rem;
    line-height: 1;
    cursor: pointer;
    transition: color 0.15s ease, transform 0.15s ease;
}

.star.filled {
    color: #c98a3d;
}

.star:hover {
    transform: scale(1.08);
}

.star-input {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
}

.star:has(.star-input:focus-visible) {
    outline: 2px solid #8f5364;
    outline-offset: 2px;
    border-radius: 6px;
}

.rating-label {
    margin: 0.4rem 0 0;
    color: #8b7b80;
    font-size: 0.85rem;
}

/* Felder */

.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    color: #754353;
    font-size: 0.9rem;
    font-weight: 600;
}

.field textarea,
.field input {
    padding: 0.7rem 0.85rem;
    border: 1px solid #ead7dd;
    border-radius: 10px;
    color: #3d2930;
    font: inherit;
    font-weight: 400;
}

.field textarea:focus,
.field input:focus {
    outline: 2px solid #8f5364;
    outline-offset: 1px;
}

.hint {
    color: #8b7b80;
    font-weight: 400;
}

.error {
    color: #9b2c3c;
    font-weight: 400;
}

.submit-button {
    padding: 0.85rem 1rem;
    border: none;
    border-radius: 10px;
    background: #8f5364;
    color: #ffffff;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
}

.submit-button:hover:not(:disabled) {
    background: #754353;
}

.submit-button:disabled {
    opacity: 0.55;
    cursor: default;
}

.submit-button:focus-visible,
.home-link:focus-visible {
    outline: 2px solid #8f5364;
    outline-offset: 2px;
}

/* Danke */

.thanks-icon {
    display: block;
    margin-bottom: 0.5rem;
    color: #8f5364;
    font-size: 2rem;
}

.home-link {
    display: inline-block;
    padding: 0.7rem 1.4rem;
    border-radius: 10px;
    background: #8f5364;
    color: #ffffff;
    text-decoration: none;
    font-weight: 600;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
    white-space: nowrap;
}

@media (prefers-reduced-motion: reduce) {
    .star {
        transition: none;
    }

    .star:hover {
        transform: none;
    }
}
</style>
