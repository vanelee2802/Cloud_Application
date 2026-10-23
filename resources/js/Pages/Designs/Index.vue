
<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import pagesLayout from '@/Layouts/pagesLayout.vue'

const designs = ref([])
const search = ref('')
const status = ref('')
const loading = ref(false)
const errorMessage = ref('')

const filteredDesigns = computed(() => designs.value)

async function loadDesigns() {
    loading.value = true
    errorMessage.value = ''

    try {
        const params = new URLSearchParams()

        if (search.value.trim()) {
            params.set('search', search.value.trim())
        }

        if (status.value) {
            params.set('status', status.value)
        }

        const query = params.toString()
        const response = await fetch(`/designs${query ? `?${query}` : ''}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            throw new Error('Die Designs konnten nicht geladen werden.')
        }

        designs.value = await response.json()
    } catch (error) {
        errorMessage.value = error.message
    } finally {
        loading.value = false
    }
}

async function deleteDesign(design) {
    if (!confirm(`Möchtest du "${design.name}" wirklich löschen?`)) {
        return
    }

    errorMessage.value = ''

    try {
        const response = await fetch(`/designs/${design.id}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') ?? '',
            },
        })

        if (!response.ok) {
            throw new Error('Das Design konnte nicht gelöscht werden.')
        }

        await loadDesigns()
    } catch (error) {
        errorMessage.value = error.message
    }
}

function formatPrice(price) {
    return Number(price ?? 0).toLocaleString('de-DE', {
        style: 'currency',
        currency: 'EUR',
    })
}

function formatStatus(value) {
    const labels = {
        pending: 'Ausstehend',
        accepted: 'Angenommen',
        rejected: 'Abgelehnt',
    }

    return labels[value] ?? value
}

onMounted(loadDesigns)
</script>

<template>
    <pagesLayout>
        <main class="designs-page">
            <header class="page-header">
                <div>
                    <h1>Meine Nageldesigns</h1>
                    <p>Hier findest du deine gespeicherten Designs.</p>
                </div>

                <a href="/DesignEditor" class="primary-button">
                    Neues Design erstellen
                </a>
            </header>

            <section class="filters">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Designnamen suchen"
                    aria-label="Designnamen suchen"
                    @input="loadDesigns"
                />

                <select v-model="status" aria-label="Nach Status filtern" @change="loadDesigns">
                    <option value="">Alle Status</option>
                    <option value="pending">Ausstehend</option>
                    <option value="accepted">Angenommen</option>
                    <option value="rejected">Abgelehnt</option>
                </select>
            </section>

            <p v-if="loading" role="status">Designs werden geladen …</p>
            <p v-if="errorMessage" class="error-message" role="alert">
                {{ errorMessage }}
            </p>

            <section v-if="!loading && filteredDesigns.length === 0" class="empty-state">
                <h2>Noch keine Designs gefunden</h2>
                <p>Erstelle dein erstes Nageldesign oder ändere deine Suche.</p>
            </section>

            <section v-else-if="!loading" class="design-grid">
                <article
                    v-for="design in filteredDesigns"
                    :key="design.id"
                    class="design-card"
                >
                    <div class="card-header">
                        <h2>{{ design.name }}</h2>
                        <span class="status">{{ formatStatus(design.status) }}</span>
                    </div>

                    <p class="price">{{ formatPrice(design.total_price) }}</p>

                    <p class="nail-count">
                        {{ design.nails?.length ?? 0 }}
                        {{ design.nails?.length === 1 ? 'Nagel' : 'Nägel' }}
                    </p>

                    <div v-if="design.nails?.length" class="nail-list">
                        <div
                            v-for="nail in design.nails"
                            :key="nail.id"
                            class="nail-row"
                        >
                            <span
                                class="color-swatch"
                                :style="{ backgroundColor: nail.color?.hex_code ?? '#eeeeee' }"
                            ></span>

                            <div class="nail-info">
                                <strong>{{ nail.color?.name ?? 'Keine Farbe' }}</strong>
                                <small>
                                    {{ nail.nail_shape?.name ?? 'Keine Form' }}
                                </small>
                                <small v-if="nail.design_elements?.length">
                                    {{ nail.design_elements.map(element => element.name).join(', ') }}
                                </small>
                                <small v-else>Kein Designelement</small>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="delete-button"
                        @click="deleteDesign(design)"
                    >
                        Design löschen
                    </button>
                </article>
            </section>
        </main>
    </pagesLayout>
</template>

<style scoped>

.designs-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    box-sizing: border-box;
    color: #333333;
    background-color: #ffffff;
    min-height: 80vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

h1 {
    margin: 0;
    font-size: 2rem;
}

.page-header p {
    opacity: 0.7;
}

.primary-button,
.delete-button {
    display: inline-block;
    border: 0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    cursor: pointer;
    text-decoration: none;
    font: inherit;
}

.primary-button {
    background: var(--text-color);
    color: var(--background-color, white);
}

.filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.filters input,
.filters select {
    min-width: 0;
    padding: 0.75rem;
    border: 1px solid #cccccc;
    border-radius: 8px;
    background: #ffffff;
    color: #333333;
}

.filters input {
    flex: 1;
}

.design-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.25rem;
}

.design-card,
.empty-state {
    padding: 1.25rem;
    border: 1px solid #dddddd;
    border-radius: 12px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
}

.card-header h2 {
    margin: 0;
    font-size: 1.2rem;
    overflow-wrap: anywhere;
}

.status {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    background: #eeeeee;
    color: #333333;
    font-size: 0.8rem;
    white-space: nowrap;
}

.price {
    font-size: 1.4rem;
    font-weight: 700;
}

.nail-count {
    opacity: 0.7;
}

.nail-list {
    display: grid;
    gap: 0.75rem;
    margin: 1rem 0;
}

.nail-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.color-swatch {
    flex: 0 0 28px;
    width: 28px;
    height: 28px;
    border: 1px solid #cccccc;
    border-radius: 50%;
}

.nail-info {
    display: grid;
    gap: 0.15rem;
}

.nail-info small {
    opacity: 0.7;
}

.delete-button {
    margin-top: 0.5rem;
    background: #8f2335;
    color: white;
}

.error-message {
    color: #b42318;
}

@media (max-width: 650px) {
    .designs-page {
        padding: 1rem;
    }

    .page-header,
    .filters {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
