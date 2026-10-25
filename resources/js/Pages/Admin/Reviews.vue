<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
    studios: { type: Array, default: () => [] },
})

// '' = alle eigenen Studios
const studioId = ref('')
const filter = ref('all')     // all | visible | hidden
const reviews = ref([])
const loading = ref(false)
const message = ref('')
const busyId = ref(null)

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

async function load() {
    loading.value = true
    message.value = ''

    const params = studioId.value ? `?nail_studio_id=${studioId.value}` : ''

    try {
        const response = await fetch(`/admin/bewertungen/liste${params}`, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
            throw new Error()
        }

        reviews.value = (await response.json()).reviews
    } catch {
        message.value = 'Die Bewertungen konnten nicht geladen werden.'
    } finally {
        loading.value = false
    }
}

onMounted(load)
watch(studioId, load)

const visibleList = computed(() => {
    if (filter.value === 'visible') return reviews.value.filter(review => !review.is_hidden)
    if (filter.value === 'hidden') return reviews.value.filter(review => review.is_hidden)

    return reviews.value
})

const hiddenCount = computed(() => reviews.value.filter(review => review.is_hidden).length)

const average = computed(() => {
    const shown = reviews.value.filter(review => !review.is_hidden)

    if (shown.length === 0) return null

    const sum = shown.reduce((total, review) => total + review.rating, 0)

    return (sum / shown.length).toFixed(1).replace('.', ',')
})

async function toggle(review) {
    busyId.value = review.id
    message.value = ''

    try {
        const response = await fetch(`/admin/bewertungen/${review.id}`, {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ is_hidden: !review.is_hidden }),
        })

        if (!response.ok) {
            throw new Error()
        }

        const data = await response.json()
        review.is_hidden = data.is_hidden
        message.value = data.is_hidden
            ? 'Bewertung ausgeblendet. Sie erscheint nicht mehr auf der Startseite und zählt nicht zum Durchschnitt.'
            : 'Bewertung wird wieder angezeigt.'
    } catch {
        message.value = 'Die Änderung konnte nicht gespeichert werden.'
    } finally {
        busyId.value = null
    }
}
</script>

<template>
    <Head title="Bewertungen" />

    <AuthenticatedLayout>

        <template #header>
            <h2 class="page-title">Bewertungen</h2>
        </template>

        <div class="page">

            <section v-if="!studios.length" class="card empty-state">
                <strong>Noch kein Studio zugeordnet</strong>
                <span>Sobald dir ein Studio gehört, siehst du hier seine Bewertungen.</span>
            </section>

            <section v-else class="card">

                <div class="card-header">
                    <div>
                        <h3>Bewertungen deiner Kundinnen</h3>
                        <p>
                            Ausgeblendete Bewertungen erscheinen nicht auf der Startseite
                            und zählen nicht zum Durchschnitt. Du kannst sie jederzeit wieder anzeigen.
                        </p>
                    </div>

                    <label class="studio-select">
                        <span>Studio</span>
                        <select v-model="studioId">
                            <option value="">Alle meine Studios</option>
                            <option v-for="studio in studios" :key="studio.id" :value="studio.id">
                                {{ studio.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="summary">
                    <span v-if="average">Durchschnitt (sichtbar): <strong>{{ average }} ★</strong></span>
                    <span>{{ reviews.length }} insgesamt, {{ hiddenCount }} ausgeblendet</span>
                </div>

                <div class="filters" role="group" aria-label="Bewertungen filtern">
                    <button
                        v-for="option in [
                            { key: 'all', label: 'Alle' },
                            { key: 'visible', label: 'Sichtbar' },
                            { key: 'hidden', label: 'Ausgeblendet' },
                        ]"
                        :key="option.key"
                        type="button"
                        class="filter"
                        :class="{ active: filter === option.key }"
                        :aria-pressed="filter === option.key"
                        @click="filter = option.key"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <p v-if="message" class="message" role="status">{{ message }}</p>

                <p v-if="loading" class="empty-row">Bewertungen werden geladen …</p>

                <p v-else-if="visibleList.length === 0" class="empty-row">
                    {{ reviews.length ? 'Keine Bewertungen in dieser Ansicht.' : 'Noch keine Bewertungen.' }}
                </p>

                <ul v-else class="review-list">
                    <li
                        v-for="review in visibleList"
                        :key="review.id"
                        class="review"
                        :class="{ hidden: review.is_hidden }"
                    >
                        <div class="review-main">
                            <div class="review-top">
                                <span class="stars" :aria-label="`${review.rating} von 5 Sternen`">
                                    <span
                                        v-for="star in 5"
                                        :key="star"
                                        :class="{ filled: star <= review.rating }"
                                        aria-hidden="true"
                                    >★</span>
                                </span>
                                <span v-if="review.is_hidden" class="hidden-badge">ausgeblendet</span>
                            </div>

                            <p v-if="review.comment" class="comment">{{ review.comment }}</p>
                            <p v-else class="comment muted">Ohne Text</p>

                            <p class="meta">
                                {{ review.author_name }}
                                <template v-if="review.service">, {{ review.service }}</template>
                                <template v-if="!studioId && review.studio">, {{ review.studio }}</template>
                                <template v-if="review.date">, {{ review.date }}</template>
                            </p>
                        </div>

                        <button
                            type="button"
                            class="toggle-button"
                            :disabled="busyId === review.id"
                            @click="toggle(review)"
                        >
                            {{ review.is_hidden ? 'Wieder anzeigen' : 'Ausblenden' }}
                        </button>
                    </li>
                </ul>

            </section>

        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.page-title {
    margin: 0;
    color: #4f3540;
    font-size: 1.25rem;
    font-weight: 600;
}

.page {
    max-width: 64rem;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.card {
    padding: 1.5rem;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 12px;
    background: #ffffff;
    box-shadow:
        0 4px 12px rgba(80, 35, 48, 0.06),
        0 8px 20px rgba(80, 35, 48, 0.04);
}

.card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.card-header h3 {
    margin: 0;
    color: #4f3540;
    font-size: 1.15rem;
    font-weight: 700;
}

.card-header p {
    max-width: 34rem;
    margin: 0.35rem 0 0;
    color: #8a7a80;
    font-size: 0.8rem;
    line-height: 1.5;
}

.studio-select {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex-shrink: 0;
    color: #8a7a80;
    font-size: 0.75rem;
}

.studio-select select {
    min-width: 13rem;
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    border: 1px solid rgba(143, 83, 100, 0.25);
    border-radius: 8px;
    background-color: #fdfbfc;
    color: #4f3540;
    font-size: 0.9rem;
}

.summary {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem 1.5rem;
    margin-bottom: 1rem;
    color: #8a7a80;
    font-size: 0.85rem;
}

.summary strong {
    color: #4f3540;
}

.filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 1rem;
    padding-bottom: 0.9rem;
    border-bottom: 1px solid rgba(143, 83, 100, 0.12);
}

.filter {
    padding: 0.45rem 0.85rem;
    border: 1px solid #e4e4e4;
    border-radius: 100vw;
    background: #ffffff;
    color: #555555;
    font-size: 0.82rem;
    cursor: pointer;
}

.filter:hover {
    background: #f8f0f2;
    border-color: #c9aab1;
    color: #7d1024;
}

.filter.active {
    background: #7d1024;
    border-color: #7d1024;
    color: #ffffff;
}

.message {
    margin: 0 0 1rem;
    padding: 0.6rem 0.8rem;
    border-radius: 8px;
    background: #f8f0f2;
    color: #4f3540;
    font-size: 0.85rem;
}

.review-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.review {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem 1rem;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 10px;
    background: #fdfbfc;
}

.review.hidden {
    background: #f6f3f4;
}

.review.hidden .comment,
.review.hidden .stars {
    opacity: 0.55;
}

.review-main {
    min-width: 0;
}

.review-top {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.stars {
    color: #ead7dd;
    letter-spacing: 0.06em;
}

.stars .filled {
    color: #c98a3d;
}

.hidden-badge {
    padding: 0.05rem 0.5rem;
    border-radius: 100vw;
    background: #e9e1e3;
    color: #6f5f64;
    font-size: 0.7rem;
    font-weight: 600;
}

.comment {
    margin: 0.4rem 0;
    color: #4f3540;
    font-size: 0.9rem;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

.muted {
    color: #a8999d;
    font-style: italic;
}

.meta {
    margin: 0;
    color: #8a7a80;
    font-size: 0.75rem;
}

.toggle-button {
    flex-shrink: 0;
    padding: 0.45rem 0.85rem;
    border: 1px solid rgba(143, 83, 100, 0.25);
    border-radius: 7px;
    background: #ffffff;
    color: #7d1024;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
}

.toggle-button:hover:not(:disabled) {
    background: #f5e7eb;
}

.toggle-button:disabled {
    opacity: 0.6;
    cursor: default;
}

.filter:focus-visible,
.toggle-button:focus-visible {
    outline: 2px solid #7d1024;
    outline-offset: 2px;
}

.empty-row {
    margin: 1.5rem 0;
    color: #8a7a80;
    text-align: center;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding: 3rem 1.5rem;
    color: #8a7a80;
    text-align: center;
}

.empty-state strong {
    color: #4f3540;
}

@media (max-width: 768px) {
    .card-header,
    .review {
        flex-direction: column;
    }

    .studio-select,
    .studio-select select {
        width: 100%;
    }
}
</style>
