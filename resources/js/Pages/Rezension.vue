
<template>
    <pagesLayout>
    <div class="reviews-page">
        <div class="reviews-container">
            <!-- Header -->
            <header class="reviews-header">
                <div class="header-flower">♡</div>
                <p class="eyebrow">{{ t.eyebrow }}</p>
                <h1>{{ t.title }}</h1>
                <p class="header-description">{{ t.description }}</p>

                <div class="header-line"></div>

                <div class="language-switch">
                    <button
                        v-for="lang in availableLanguages"
                        :key="lang.code"
                        type="button"
                        :class="{ active: language === lang.code }"
                        :aria-pressed="language === lang.code"
                        @click="language = lang.code"
                    >
                        {{ lang.label }}
                    </button>
                </div>
            </header>

            <!-- Owner-Bereich -->
            <section v-if="canEdit" class="owner-panel">
                <div class="owner-panel-heading">
                    <div>
                        <p class="eyebrow">{{ t.ownerEyebrow }}</p>
                        <h2>{{ t.ownerTitle }}</h2>
                        <p class="owner-description">
                            {{ t.ownerDescription }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="primary-button"
                        @click="openCreateForm"
                    >
                        <span>＋</span>
                        {{ t.addReview }}
                    </button>
                </div>

                <div class="owner-stats">
                    <div class="owner-stat">
                        <span>{{ t.totalReviews }}</span>
                        <strong>{{ reviews.length }}</strong>
                    </div>

                    <div class="owner-stat">
                        <span>{{ t.publishedReviews }}</span>
                        <strong>{{ publishedReviews.length }}</strong>
                    </div>

                    <div class="owner-stat">
                        <span>{{ t.hiddenReviews }}</span>
                        <strong>
                            {{ reviews.length - publishedReviews.length }}
                        </strong>
                    </div>
                </div>

                <p class="demo-notice">
                    {{ t.demoNotice }}
                </p>

                <!-- Review-Editor -->
                <div v-if="showEditor" class="review-editor">
                    <div class="editor-heading">
                        <h3>
                            {{ editingId === null ? t.newReview : t.editReview }}
                        </h3>

                        <button
                            type="button"
                            class="icon-button"
                            :aria-label="t.cancel"
                            @click="closeEditor"
                        >
                            ×
                        </button>
                    </div>

                    <form class="editor-form" @submit.prevent="saveReview">
                        <div class="form-row">
                            <div class="form-field">
                                <label for="review-name">
                                    {{ t.nameLabel }}
                                </label>
                                <input
                                    id="review-name"
                                    v-model.trim="draft.name"
                                    type="text"
                                    maxlength="80"
                                    required
                                    :placeholder="t.namePlaceholder"
                                />
                            </div>

                            <div class="form-field">
                                <label for="review-rating">
                                    {{ t.ratingLabel }}
                                </label>
                                <select
                                    id="review-rating"
                                    v-model.number="draft.rating"
                                    required
                                >
                                    <option :value="5">★★★★★ (5)</option>
                                    <option :value="4">★★★★☆ (4)</option>
                                    <option :value="3">★★★☆☆ (3)</option>
                                    <option :value="2">★★☆☆☆ (2)</option>
                                    <option :value="1">★☆☆☆☆ (1)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="review-date">
                                {{ t.dateLabel }}
                            </label>
                            <input
                                id="review-date"
                                v-model="draft.date"
                                type="date"
                                required
                            />
                        </div>

                        <div class="form-field">
                            <label for="review-text-de">
                                {{ t.germanTextLabel }}
                            </label>
                            <textarea
                                id="review-text-de"
                                v-model.trim="draft.text.de"
                                rows="4"
                                maxlength="1500"
                                required
                                :placeholder="t.germanTextPlaceholder"
                            ></textarea>
                        </div>

                        <div class="form-field">
                            <label for="review-text-en">
                                {{ t.englishTextLabel }}
                            </label>
                            <textarea
                                id="review-text-en"
                                v-model.trim="draft.text.en"
                                rows="4"
                                maxlength="1500"
                                :placeholder="t.englishTextPlaceholder"
                            ></textarea>
                        </div>

                        <div class="form-field">
                            <label for="review-source">
                                {{ t.sourceLabel }}
                            </label>
                            <select id="review-source" v-model="draft.source">
                                <option value="website">{{ t.websiteSource }}</option>
                                <option value="google">Google</option>
                                <option value="other">{{ t.otherSource }}</option>
                            </select>
                        </div>

                        <label class="checkbox-row">
                            <input
                                v-model="draft.verified"
                                type="checkbox"
                            />
                            <span>{{ t.verifiedLabel }}</span>
                        </label>

                        <label class="checkbox-row">
                            <input
                                v-model="draft.published"
                                type="checkbox"
                            />
                            <span>{{ t.publishedLabel }}</span>
                        </label>

                        <div class="editor-actions">
                            <button type="submit" class="primary-button">
                                {{ t.save }}
                            </button>

                            <button
                                type="button"
                                class="secondary-button"
                                @click="closeEditor"
                            >
                                {{ t.cancel }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Verwaltungsübersicht -->
                <div class="manage-list">
                    <h3>{{ t.manageTitle }}</h3>

                    <div
                        v-for="review in reviews"
                        :key="review.id"
                        class="manage-row"
                    >
                        <div class="manage-review-info">
                            <strong>{{ review.name }}</strong>

                            <span class="manage-stars">
                                {{ starString(review.rating) }}
                            </span>

                            <span class="manage-status">
                                {{
                                    review.published
                                        ? t.statusPublished
                                        : t.statusHidden
                                }}
                            </span>
                        </div>

                        <div class="manage-actions">
                            <button
                                type="button"
                                class="small-button"
                                @click="togglePublished(review)"
                            >
                                {{
                                    review.published
                                        ? t.hide
                                        : t.publish
                                }}
                            </button>

                            <button
                                type="button"
                                class="small-button"
                                @click="openEditForm(review)"
                            >
                                {{ t.edit }}
                            </button>

                            <button
                                type="button"
                                class="small-button delete-button"
                                @click="deleteReview(review.id)"
                            >
                                {{ t.delete }}
                            </button>
                        </div>
                    </div>

                    <p v-if="reviews.length === 0" class="empty-state">
                        {{ t.noReviews }}
                    </p>
                </div>
            </section>

            <!-- Bewertungsübersicht -->
            <section class="rating-overview">
                <div class="rating-score">
                    <strong>{{ averageRating.toFixed(1).replace('.', ',') }}</strong>

                    <div
                        class="stars overview-stars"
                        :aria-label="`${averageRating} ${t.outOfFive}`"
                    >
                        {{ starString(Math.round(averageRating)) }}
                    </div>

                    <span>{{ t.averageRating }}</span>
                    <small>
                        {{ publishedReviews.length }} {{ t.reviewCountLabel }}
                    </small>
                </div>

                <div class="rating-breakdown">
                    <div
                        v-for="rating in [5, 4, 3, 2, 1]"
                        :key="rating"
                        class="rating-bar-row"
                    >
                        <span>{{ rating }} ★</span>

                        <div class="rating-bar">
                            <div
                                class="rating-bar-fill"
                                :style="{
                                    width: ratingPercentage(rating) + '%'
                                }"
                            ></div>
                        </div>

                        <span class="rating-count">
                            {{ countForRating(rating) }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Filter und Sortierung -->
            <section class="reviews-section">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ t.reviewsEyebrow }}</p>
                        <h2>{{ t.reviewsTitle }}</h2>
                    </div>

                    <span class="review-count-badge">
                        {{ filteredReviews.length }}
                    </span>
                </div>

                <div class="review-toolbar">
                    <div class="filter-buttons">
                        <button
                            v-for="filter in filters"
                            :key="filter.value"
                            type="button"
                            :class="{
                                active: activeFilter === filter.value
                            }"
                            @click="activeFilter = filter.value"
                        >
                            {{ filter.label }}
                        </button>
                    </div>

                    <select
                        v-model="sortOrder"
                        class="sort-select"
                        :aria-label="t.sortLabel"
                    >
                        <option value="newest">{{ t.newest }}</option>
                        <option value="highest">{{ t.highest }}</option>
                        <option value="lowest">{{ t.lowest }}</option>
                    </select>
                </div>

                <!-- Rezensionen -->
                <div v-if="filteredReviews.length" class="reviews-grid">
                    <article
                        v-for="review in filteredReviews"
                        :key="review.id"
                        class="review-card"
                    >
                        <div class="review-card-top">
                            <div class="stars">
                                {{ starString(review.rating) }}
                            </div>

                            <span
                                v-if="review.verified"
                                class="verified-badge"
                            >
                                ✓ {{ t.verified }}
                            </span>
                        </div>

                        <p class="review-quote">“</p>

                        <p class="review-text">
                            {{ localizedReviewText(review) }}
                        </p>

                        <div class="review-card-footer">
                            <div class="review-avatar">
                                {{ initials(review.name) }}
                            </div>

                            <div class="review-author">
                                <strong>{{ review.name }}</strong>
                                <span>{{ formatDate(review.date) }}</span>
                            </div>

                            <span
                                v-if="review.source === 'google'"
                                class="source-label"
                            >
                                Google
                            </span>
                        </div>
                    </article>
                </div>

                <div v-else class="empty-reviews">
                    <div class="empty-heart">♡</div>
                    <h3>{{ t.emptyTitle }}</h3>
                    <p>{{ t.emptyDescription }}</p>
                </div>
            </section>

            <!-- Call to action -->
            <section class="review-cta">
                <div class="cta-heart">♡</div>
                <p class="eyebrow">{{ t.ctaEyebrow }}</p>
                <h2>{{ t.ctaTitle }}</h2>
                <p>{{ t.ctaDescription }}</p>

                <a
                    v-if="googleReviewUrl"
                    :href="googleReviewUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="cta-button"
                >
                    {{ t.googleButton }}
                    <span>↗</span>
                </a>

                <p v-else class="cta-note">
                    {{ t.googleSetupHint }}
                </p>
            </section>

            <footer class="reviews-footer">
                <span>✿</span>
                <p>{{ t.footer }}</p>
            </footer>
        </div>
    </div>
</pagesLayout>
</template>

<script setup>


import { computed, reactive, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PagesLayout from '@/Layouts/pagesLayout.vue'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Owner-Berechtigung
|--------------------------------------------------------------------------
| Erwartet auth.user.role === 'owner'.
| Die Prüfung ist nur für die UI. Backend-Aktionen müssen später ebenfalls
| serverseitig autorisiert werden.
*/

const canEdit = computed(
    () => page.props.auth?.user?.role === 'owner'
)

/*
|--------------------------------------------------------------------------
| Konfiguration
|--------------------------------------------------------------------------
| Später hier die echte URL deines Google-Bewertungsprofils eintragen.
*/

const googleReviewUrl = ''

const availableLanguages = [
    { code: 'de', label: 'DE' },
    { code: 'en', label: 'EN' },
]

const language = ref('de')
const activeFilter = ref('all')
const sortOrder = ref('newest')

/*
|--------------------------------------------------------------------------
| Beispielrezensionen
|--------------------------------------------------------------------------
| Diese Einträge sind nur Demo-Daten.
| IDs sind stabil und können später durch Datenbank-IDs ersetzt werden.
*/

const reviews = reactive([
    {
        id: 1,
        name: 'Sabrina K.',
        rating: 5,
        date: '2026-09-18',
        text: {
            de: 'Ich bin jedes Mal absolut begeistert! Die Atmosphäre ist so angenehm und meine Nägel sehen immer wunderschön aus.',
            en: 'I am delighted every time! The atmosphere is lovely and my nails always look beautiful.',
        },
        source: 'website',
        verified: false,
        published: true,
    },
    {
        id: 2,
        name: 'Laura M.',
        rating: 5,
        date: '2026-09-02',
        text: {
            de: 'Super schönes Studio, tolle Beratung und unglaublich genaue Arbeit. Ich komme immer wieder gerne!',
            en: 'A beautiful studio, great advice and incredibly precise work. I will definitely come back!',
        },
        source: 'website',
        verified: false,
        published: true,
    },
    {
        id: 3,
        name: 'Julia T.',
        rating: 5,
        date: '2026-08-21',
        text: {
            de: 'Wunderschöne Designs, sehr professionell und ein richtig tolles Team. Ich fühle mich jedes Mal wohl.',
            en: 'Beautiful designs, very professional service and a lovely team. I feel welcome every time.',
        },
        source: 'google',
        verified: false,
        published: true,
    },
    {
        id: 4,
        name: 'Melanie R.',
        rating: 5,
        date: '2026-08-04',
        text: {
            de: 'Meine Nägel halten so lange und sehen einfach traumhaft aus. Ich liebe die kreativen Designs und die Atmosphäre!',
            en: 'My nails last so long and look amazing. I love the creative designs and the atmosphere!',
        },
        source: 'website',
        verified: false,
        published: true,
    },
    {
        id: 5,
        name: 'Natalie S.',
        rating: 4,
        date: '2026-07-16',
        text: {
            de: 'Sehr sauberes Studio, super freundlich und eine tolle Auswahl an Farben und Designs.',
            en: 'A very clean studio, lovely service and a great selection of colours and designs.',
        },
        source: 'website',
        verified: false,
        published: true,
    },
    {
        id: 6,
        name: 'Anna L.',
        rating: 5,
        date: '2026-06-28',
        text: {
            de: 'Hier wird mit so viel Liebe zum Detail gearbeitet. Es wird ganz individuell auf meine Wünsche eingegangen.',
            en: 'There is so much attention to detail. My preferences are always taken into account.',
        },
        source: 'google',
        verified: false,
        published: true,
    },
])

/*
|--------------------------------------------------------------------------
| Übersetzungen der Oberfläche
|--------------------------------------------------------------------------
*/

const translations = {
    de: {
        eyebrow: 'Kundenstimmen',
        title: 'Das sagen unsere Kundinnen',
        description:
            'Schöne Nägel und zufriedene Kundinnen sind unsere größte Motivation.',
        ownerEyebrow: 'Verwaltung',
        ownerTitle: 'Rezensionen verwalten',
        ownerDescription:
            'Füge Rezensionen hinzu, bearbeite sie oder blende sie aus.',
        addReview: 'Rezension hinzufügen',
        totalReviews: 'Gesamt',
        publishedReviews: 'Veröffentlicht',
        hiddenReviews: 'Ausgeblendet',
        demoNotice:
            'Prototyp: Änderungen werden nur lokal gespeichert und gehen beim Neuladen verloren. Die angezeigten Rezensionen sind Beispieldaten.',
        newReview: 'Neue Rezension',
        editReview: 'Rezension bearbeiten',
        nameLabel: 'Anzeigename',
        namePlaceholder: 'z. B. Kundin A.',
        ratingLabel: 'Sternebewertung',
        dateLabel: 'Datum',
        germanTextLabel: 'Bewertungstext (Deutsch)',
        englishTextLabel: 'Bewertungstext (Englisch)',
        germanTextPlaceholder: 'Deutschen Text eingeben ...',
        englishTextPlaceholder: 'Optional: englische Übersetzung ...',
        sourceLabel: 'Quelle',
        websiteSource: 'Website',
        otherSource: 'Sonstige',
        verifiedLabel: 'Als verifiziert markieren',
        publishedLabel: 'Veröffentlichen',
        save: 'Speichern',
        cancel: 'Abbrechen',
        manageTitle: 'Alle Einträge',
        statusPublished: 'Veröffentlicht',
        statusHidden: 'Ausgeblendet',
        hide: 'Ausblenden',
        publish: 'Veröffentlichen',
        edit: 'Bearbeiten',
        delete: 'Löschen',
        noReviews: 'Noch keine Rezensionen vorhanden.',
        outOfFive: 'von 5 Sternen',
        averageRating: 'Durchschnittsbewertung',
        reviewCountLabel: 'Rezensionen',
        reviewsEyebrow: 'Eure Erfahrungen',
        reviewsTitle: 'Alle Rezensionen',
        allFilter: 'Alle',
        newest: 'Neueste zuerst',
        highest: 'Beste Bewertung',
        lowest: 'Niedrigste Bewertung',
        sortLabel: 'Rezensionen sortieren',
        verified: 'Verifiziert',
        emptyTitle: 'Noch keine passenden Rezensionen',
        emptyDescription:
            'Für diesen Filter wurden keine veröffentlichten Bewertungen gefunden.',
        ctaEyebrow: 'Deine Meinung zählt',
        ctaTitle: 'Warst du bei uns?',
        ctaDescription:
            'Wenn du uns besucht hast, freuen wir uns über dein ehrliches Feedback.',
        googleButton: 'Auf Google bewerten',
        googleSetupHint:
            'Die Verlinkung zu deinem Google-Bewertungsprofil kannst du später ergänzen.',
        footer: 'Vielen Dank für euer Vertrauen.',
        formError: 'Bitte überprüfe deine Eingaben.',
    },

    en: {
        eyebrow: 'Customer Reviews',
        title: 'What our customers say',
        description:
            'Beautiful nails and happy clients are our greatest motivation.',
        ownerEyebrow: 'Management',
        ownerTitle: 'Manage Reviews',
        ownerDescription:
            'Add reviews, edit their details or hide them from public view.',
        addReview: 'Add Review',
        totalReviews: 'Total',
        publishedReviews: 'Published',
        hiddenReviews: 'Hidden',
        demoNotice:
            'Prototype: changes are only stored locally and are lost when the page reloads. The displayed reviews are sample data.',
        newReview: 'New Review',
        editReview: 'Edit Review',
        nameLabel: 'Display Name',
        namePlaceholder: 'e.g. Customer A',
        ratingLabel: 'Star Rating',
        dateLabel: 'Date',
        germanTextLabel: 'Review Text (German)',
        englishTextLabel: 'Review Text (English)',
        germanTextPlaceholder: 'Enter German text ...',
        englishTextPlaceholder: 'Optional: English translation ...',
        sourceLabel: 'Source',
        websiteSource: 'Website',
        otherSource: 'Other',
        verifiedLabel: 'Mark as verified',
        publishedLabel: 'Publish review',
        save: 'Save',
        cancel: 'Cancel',
        manageTitle: 'All Entries',
        statusPublished: 'Published',
        statusHidden: 'Hidden',
        hide: 'Hide',
        publish: 'Publish',
        edit: 'Edit',
        delete: 'Delete',
        noReviews: 'No reviews yet.',
        outOfFive: 'out of 5 stars',
        averageRating: 'Average Rating',
        reviewCountLabel: 'reviews',
        reviewsEyebrow: 'Your Experiences',
        reviewsTitle: 'All Reviews',
        allFilter: 'All',
        newest: 'Newest first',
        highest: 'Highest rated',
        lowest: 'Lowest rated',
        sortLabel: 'Sort reviews',
        verified: 'Verified',
        emptyTitle: 'No matching reviews yet',
        emptyDescription:
            'No published reviews were found for this filter.',
        ctaEyebrow: 'Your opinion matters',
        ctaTitle: 'Have you visited us?',
        ctaDescription:
            'If you have visited us, we would love to hear your honest feedback.',
        googleButton: 'Review us on Google',
        googleSetupHint:
            'You can add a link to your Google review profile later.',
        footer: 'Thank you for your trust.',
        formError: 'Please check your entries.',
    },
}

const t = computed(() => translations[language.value])

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const filters = computed(() => [
    {
        value: 'all',
        label: `${t.value.allFilter} (${publishedReviews.value.length})`,
    },
    { value: 5, label: '★★★★★' },
    { value: 4, label: '★★★★' },
    { value: 3, label: '★★★' },
    { value: 2, label: '★★' },
    { value: 1, label: '★' },
])

const publishedReviews = computed(() =>
    reviews.filter(review => review.published)
)

const filteredReviews = computed(() => {
    let result = [...publishedReviews.value]

    if (activeFilter.value !== 'all') {
        result = result.filter(
            review => review.rating === Number(activeFilter.value)
        )
    }

    if (sortOrder.value === 'newest') {
        result.sort((a, b) => b.date.localeCompare(a.date))
    } else if (sortOrder.value === 'highest') {
        result.sort(
            (a, b) => b.rating - a.rating ||
                b.date.localeCompare(a.date)
        )
    } else if (sortOrder.value === 'lowest') {
        result.sort(
            (a, b) => a.rating - b.rating ||
                b.date.localeCompare(a.date)
        )
    }

    return result
})

const averageRating = computed(() => {
    if (!publishedReviews.value.length) return 0

    const total = publishedReviews.value.reduce(
        (sum, review) => sum + review.rating,
        0
    )

    return total / publishedReviews.value.length
})

function countForRating(rating) {
    return publishedReviews.value.filter(
        review => review.rating === rating
    ).length
}

function ratingPercentage(rating) {
    if (!publishedReviews.value.length) return 0

    return (
        countForRating(rating) /
        publishedReviews.value.length *
        100
    )
}

/*
|--------------------------------------------------------------------------
| Formatierung
|--------------------------------------------------------------------------
*/

function starString(rating) {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating)
}

function initials(name) {
    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map(part => part.replace(/[^a-zA-ZÀ-ÿ]/g, '').charAt(0))
        .join('')
        .toUpperCase()
}

function localizedReviewText(review) {
    return (
        review.text?.[language.value] ||
        review.text?.de ||
        ''
    )
}

function formatDate(date) {
    if (!date) return ''

    const parsed = new Date(`${date}T12:00:00`)

    if (Number.isNaN(parsed.getTime())) return date

    return new Intl.DateTimeFormat(
        language.value === 'de' ? 'de-DE' : 'en-GB',
        {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }
    ).format(parsed)
}

/*
|--------------------------------------------------------------------------
| Owner-Editor
|--------------------------------------------------------------------------
*/

const showEditor = ref(false)
const editingId = ref(null)
let nextId = Math.max(0, ...reviews.map(review => review.id)) + 1

function emptyDraft() {
    return {
        name: '',
        rating: 5,
        date: new Date().toISOString().slice(0, 10),
        text: {
            de: '',
            en: '',
        },
        source: 'website',
        verified: false,
        published: false,
    }
}

const draft = reactive(emptyDraft())

function resetDraft() {
    Object.assign(draft, emptyDraft())
    draft.text = { de: '', en: '' }
}

function openCreateForm() {
    if (!canEdit.value) return

    editingId.value = null
    resetDraft()
    showEditor.value = true
}

function openEditForm(review) {
    if (!canEdit.value) return

    editingId.value = review.id

    Object.assign(draft, {
        name: review.name,
        rating: review.rating,
        date: review.date,
        text: {
            de: review.text?.de ?? '',
            en: review.text?.en ?? '',
        },
        source: review.source,
        verified: review.verified,
        published: review.published,
    })

    showEditor.value = true
}

function closeEditor() {
    showEditor.value = false
    editingId.value = null
    resetDraft()
}

function saveReview() {
    if (!canEdit.value) return

    const name = draft.name.trim()
    const germanText = draft.text.de.trim()

    if (
        !name ||
        !germanText ||
        !draft.date ||
        !Number.isInteger(draft.rating) ||
        draft.rating < 1 ||
        draft.rating > 5
    ) {
        return
    }

    const reviewData = {
        name,
        rating: draft.rating,
        date: draft.date,
        text: {
            de: germanText,
            en: draft.text.en.trim(),
        },
        source: draft.source,
        verified: draft.verified,
        published: draft.published,
    }

    if (editingId.value !== null) {
        const index = reviews.findIndex(
            review => review.id === editingId.value
        )

        if (index !== -1) {
            Object.assign(reviews[index], reviewData)
        }
    } else {
        reviews.unshift({
            id: nextId++,
            ...reviewData,
        })
    }

    closeEditor()
}

function togglePublished(review) {
    if (!canEdit.value) return

    review.published = !review.published
}

function deleteReview(id) {
    if (!canEdit.value) return

    const index = reviews.findIndex(review => review.id === id)

    if (index !== -1) {
        reviews.splice(index, 1)
    }

    if (editingId.value === id) {
        closeEditor()
    }
}
</script>

<style scoped>
.reviews-page {
    min-height: 100vh;
    padding: 54px 20px 70px;
    color: #4c353b;
    background:
        radial-gradient(
            circle at top left,
            rgba(229, 193, 201, 0.22),
            transparent 34%
        ),
        #fbf7f7;
    font-family: inherit;
}

.reviews-container {
    max-width: 1120px;
    margin: 0 auto;
}

.reviews-header {
    margin-bottom: 28px;
    padding: 44px 25px 34px;
    text-align: center;
    background: #fffdfd;
    border: 1px solid #f0e1e4;
    border-radius: 24px;
    box-shadow: 0 12px 36px rgba(117, 67, 83, 0.06);
}

.header-flower {
    margin-bottom: 10px;
    color: #ad7887;
    font-size: 32px;
}

.eyebrow {
    margin: 0 0 10px;
    color: #9a6272;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.19em;
    text-transform: uppercase;
}

.reviews-header h1,
.section-heading h2,
.review-cta h2 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(30px, 4vw, 43px);
    font-weight: 500;
    line-height: 1.25;
}

.header-description {
    max-width: 610px;
    margin: 17px auto 0;
    color: #826b71;
    font-size: 15px;
    line-height: 1.8;
}

.header-line {
    width: 58px;
    height: 2px;
    margin: 25px auto;
    background: #b47c8b;
}

.language-switch {
    display: inline-flex;
    gap: 5px;
    padding: 5px;
    background: #f7edef;
    border: 1px solid #f0dfe3;
    border-radius: 999px;
}

.language-switch button {
    min-width: 58px;
    padding: 9px 16px;
    color: #805b65;
    background: transparent;
    border: 0;
    border-radius: 999px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
}

.language-switch button.active {
    color: white;
    background: #8f5364;
}

/* Owner */

.owner-panel {
    margin-bottom: 28px;
    padding: 25px;
    background: #fffdfd;
    border: 1px solid #ead2d9;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(117, 67, 83, 0.04);
}

.owner-panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.owner-panel-heading h2,
.manage-list h3,
.editor-heading h3 {
    margin: 0 0 8px;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 24px;
    font-weight: 500;
}

.owner-description {
    margin: 0;
    color: #826b71;
    font-size: 13px;
    line-height: 1.7;
}

.primary-button,
.secondary-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 17px;
    border: 1px solid #8f5364;
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
}

.primary-button {
    color: #fff;
    background: #8f5364;
}

.primary-button:hover {
    background: #754353;
}

.secondary-button {
    color: #754353;
    background: white;
    border-color: #e6cbd2;
}

.owner-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-top: 22px;
}

.owner-stat {
    display: flex;
    flex-direction: column;
    gap: 7px;
    padding: 15px;
    background: #fbf3f5;
    border-radius: 12px;
}

.owner-stat span {
    color: #896d76;
    font-size: 12px;
}

.owner-stat strong {
    color: #754353;
    font-size: 24px;
}

.demo-notice {
    margin: 16px 0 0;
    padding: 12px 14px;
    color: #80636c;
    background: #f8ecef;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.7;
}

/* Editor */

.review-editor {
    margin-top: 22px;
    padding: 22px;
    background: #fffafa;
    border: 1px solid #ead2d9;
    border-radius: 14px;
}

.editor-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.editor-heading h3 {
    margin-bottom: 0;
}

.icon-button {
    width: 34px;
    height: 34px;
    color: #754353;
    background: #f8ecef;
    border: 0;
    border-radius: 9px;
    cursor: pointer;
    font-size: 24px;
}

.editor-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.form-field label {
    color: #754353;
    font-size: 12px;
    font-weight: 700;
}

.form-field input,
.form-field select,
.form-field textarea {
    width: 100%;
    padding: 12px 13px;
    color: #4c353b;
    background: white;
    border: 1px solid #ead8dd;
    border-radius: 9px;
    outline: none;
    font: inherit;
    font-size: 13px;
}

.form-field textarea {
    resize: vertical;
    line-height: 1.7;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    border-color: #a56c7c;
    box-shadow: 0 0 0 3px rgba(143, 83, 100, 0.09);
}

.checkbox-row {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #725f64;
    font-size: 13px;
    cursor: pointer;
}

.checkbox-row input {
    accent-color: #8f5364;
}

.editor-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}

/* Verwaltungsübersicht */

.manage-list {
    margin-top: 24px;
}

.manage-list h3 {
    font-size: 21px;
}

.manage-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 15px 0;
    border-top: 1px solid #f0e1e4;
}

.manage-review-info {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.manage-review-info strong {
    color: #754353;
    font-size: 13px;
}

.manage-stars {
    color: #b97882;
    letter-spacing: 1px;
}

.manage-status {
    padding: 4px 8px;
    color: #805b65;
    background: #f8ecef;
    border-radius: 6px;
    font-size: 10px;
}

.manage-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.small-button {
    padding: 7px 9px;
    color: #754353;
    background: white;
    border: 1px solid #e8d2d9;
    border-radius: 7px;
    cursor: pointer;
    font-size: 11px;
}

.small-button:hover {
    background: #f8ecef;
}

.delete-button {
    color: #a13d50;
}

/* Bewertungsübersicht */

.rating-overview {
    display: grid;
    grid-template-columns: 0.8fr 1.2fr;
    gap: 30px;
    margin-bottom: 44px;
    padding: 27px 32px;
    background: #fffdfd;
    border: 1px solid #f0e1e4;
    border-radius: 20px;
    box-shadow: 0 7px 24px rgba(117, 67, 83, 0.04);
}

.rating-score {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px;
    text-align: center;
    border-right: 1px solid #f0e1e4;
}

.rating-score > strong {
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 56px;
    font-weight: 500;
    line-height: 1.1;
}

.stars {
    color: #bd8188;
    letter-spacing: 3px;
    font-size: 17px;
}

.overview-stars {
    margin: 9px 0;
}

.rating-score > span {
    color: #725f64;
    font-size: 12px;
}

.rating-score > small {
    margin-top: 5px;
    color: #9b8089;
    font-size: 11px;
}

.rating-breakdown {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 12px;
}

.rating-bar-row {
    display: grid;
    grid-template-columns: 43px minmax(0, 1fr) 28px;
    align-items: center;
    gap: 10px;
    color: #80636c;
    font-size: 12px;
}

.rating-bar {
    height: 8px;
    overflow: hidden;
    background: #f2e5e8;
    border-radius: 999px;
}

.rating-bar-fill {
    height: 100%;
    background: #b97882;
    border-radius: 999px;
    transition: width 0.25s ease;
}

.rating-count {
    text-align: right;
}

/* Rezensionen */

.reviews-section {
    margin-bottom: 44px;
}

.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 22px;
}

.section-heading h2 {
    font-size: clamp(27px, 4vw, 34px);
}

.review-count-badge {
    display: grid;
    min-width: 40px;
    height: 40px;
    padding: 0 10px;
    place-items: center;
    color: #754353;
    background: #f8ecef;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
}

.review-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 22px;
}

.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.filter-buttons button {
    padding: 9px 13px;
    color: #805b65;
    background: #fffdfd;
    border: 1px solid #ead8dd;
    border-radius: 999px;
    cursor: pointer;
    font-size: 11px;
    transition: background 0.2s ease;
}

.filter-buttons button.active,
.filter-buttons button:hover {
    color: #fff;
    background: #8f5364;
    border-color: #8f5364;
}

.sort-select {
    max-width: 100%;
    padding: 10px 12px;
    color: #754353;
    background: #fffdfd;
    border: 1px solid #ead8dd;
    border-radius: 10px;
    font: inherit;
    font-size: 12px;
}

.reviews-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}

.review-card {
    display: flex;
    min-width: 0;
    flex-direction: column;
    padding: 23px;
    background: #fffdfd;
    border: 1px solid #f0e1e4;
    border-radius: 17px;
    box-shadow: 0 5px 18px rgba(117, 67, 83, 0.035);
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.review-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(117, 67, 83, 0.08);
}

.review-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

.verified-badge {
    padding: 5px 7px;
    color: #805b65;
    background: #f8ecef;
    border-radius: 7px;
    font-size: 10px;
}

.review-quote {
    height: 24px;
    margin: 12px 0 0;
    color: #c28a96;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 43px;
    line-height: 1;
}

.review-text {
    flex: 1;
    margin: 9px 0 24px;
    color: #725f64;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 15px;
    font-style: italic;
    line-height: 1.8;
    overflow-wrap: anywhere;
}

.review-card-footer {
    display: flex;
    align-items: center;
    gap: 11px;
    padding-top: 16px;
    border-top: 1px solid #f2e7e9;
}

.review-avatar {
    display: grid;
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    place-items: center;
    color: #754353;
    background: #f2dfe3;
    border-radius: 50%;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 15px;
}

.review-author {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 4px;
}

.review-author strong {
    color: #754353;
    font-size: 12px;
}

.review-author span {
    color: #9b8089;
    font-size: 10px;
}

.source-label {
    margin-left: auto;
    color: #987b84;
    font-size: 10px;
}

.empty-reviews,
.empty-state {
    padding: 36px 20px;
    text-align: center;
    color: #826b71;
}

.empty-reviews {
    background: #fffdfd;
    border: 1px solid #f0e1e4;
    border-radius: 18px;
}

.empty-heart {
    color: #b97882;
    font-size: 34px;
}

.empty-reviews h3 {
    margin: 12px 0 7px;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 23px;
    font-weight: 500;
}

.empty-reviews p {
    margin: 0;
    font-size: 13px;
    line-height: 1.7;
}

/* Google CTA */

.review-cta {
    padding: 36px 25px;
    text-align: center;
    background: #f6e9ed;
    border: 1px solid #ead2d9;
    border-radius: 22px;
}

.cta-heart {
    margin-bottom: 10px;
    color: #a66e7e;
    font-size: 31px;
}

.review-cta h2 {
    font-size: clamp(27px, 4vw, 36px);
}

.review-cta > p:not(.eyebrow):not(.cta-note) {
    max-width: 540px;
    margin: 14px auto 22px;
    color: #80636c;
    font-size: 14px;
    line-height: 1.8;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    padding: 13px 20px;
    color: white;
    background: #8f5364;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.cta-button:hover {
    background: #754353;
}

.cta-note {
    margin: 18px 0 0;
    color: #896d76;
    font-size: 12px;
}

.reviews-footer {
    padding: 35px 15px 0;
    text-align: center;
}

.reviews-footer span {
    color: #ad7887;
    font-size: 25px;
}

.reviews-footer p {
    color: #967b83;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 14px;
    font-style: italic;
}

/* Responsive */

button:focus-visible,
a:focus-visible,
select:focus-visible {
    outline: 3px solid rgba(143, 83, 100, 0.35);
    outline-offset: 3px;
}

@media (max-width: 900px) {
    .reviews-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 650px) {
    .reviews-page {
        padding: 24px 12px 40px;
    }

    .reviews-header {
        padding: 32px 17px 26px;
        border-radius: 19px;
    }

    .owner-panel {
        padding: 18px;
    }

    .owner-panel-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .owner-stats {
        gap: 7px;
    }

    .owner-stat {
        padding: 11px 9px;
    }

    .owner-stat span {
        font-size: 10px;
    }

    .owner-stat strong {
        font-size: 21px;
    }

    .manage-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .manage-actions {
        width: 100%;
    }

    .rating-overview {
        grid-template-columns: 1fr;
        gap: 22px;
        padding: 23px;
    }

    .rating-score {
        padding-bottom: 22px;
        border-right: 0;
        border-bottom: 1px solid #f0e1e4;
    }

    .reviews-grid {
        grid-template-columns: 1fr;
    }

    .review-card {
        padding: 21px;
    }

    .review-toolbar {
        align-items: stretch;
        flex-direction: column;
    }

    .sort-select {
        width: 100%;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .review-editor {
        padding: 16px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
