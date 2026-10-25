<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { useCart } from '@/Stores/cart'
import Checkout from '@/Pages/Checkout.vue'

// Leistungen kommen aus der Datenbank (services), gepflegt in der Preisverwaltung

const studios = ref([])
const studioId = ref(null)
const services = ref([])
const activeCategory = ref('Alle')
const loading = ref(true)
const error = ref('')

async function loadServices(id = null) {
    loading.value = true
    error.value = ''

    try {
        const url = id ? `/services/catalog?nail_studio_id=${id}` : '/services/catalog'
        const response = await fetch(url, { headers: { Accept: 'application/json' } })

        if (!response.ok) {
            throw new Error()
        }

        const data = await response.json()

        studios.value = data.studios
        studioId.value = data.studio_id
        services.value = data.services

        // Gewählte Kategorie gibt es im neuen Studio evtl. nicht
        if (!categories.value.includes(activeCategory.value)) {
            activeCategory.value = 'Alle'
        }
    } catch {
        error.value = 'Die Leistungen konnten nicht geladen werden. Lade die Seite neu, um es noch einmal zu versuchen.'
    } finally {
        loading.value = false
    }
}

onMounted(() => loadServices())

function changeStudio(event) {
    loadServices(Number(event.target.value))
}

// Filter-Buttons: „Alle“ + Kategorien in der Reihenfolge der Leistungen
const categories = computed(() => {
    const list = []

    for (const service of services.value) {
        if (service.category && !list.includes(service.category)) {
            list.push(service.category)
        }
    }

    return ['Alle', ...list]
})

const visibleServices = computed(() =>
    activeCategory.value === 'Alle'
        ? services.value
        : services.value.filter(service => service.category === activeCategory.value)
)

const euro = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' })
const euroShort = new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
})

function priceLabel(service) {
    return service.price_is_from
        ? `ab ${euroShort.format(service.price)}`
        : euro.format(service.price)
}

// =========================================
// BUCHEN
// =========================================

const { services: cartServices, addToCart, removeFromCart } = useCart()

// Leistung, für die gerade das Fenster „Design gestalten?“ offen ist
const bookingService = ref(null)
const showCheckout = ref(false)
const firstDialogButton = ref(null)

async function openBooking(service) {
    bookingService.value = service
    await nextTick()
    firstDialogButton.value?.focus()
}

function closeBooking() {
    bookingService.value = null
}

// Liegt schon eine andere Leistung im Warenkorb? Die wird ersetzt (ein Termin = eine Leistung)
const replacedService = computed(() =>
    cartServices.value.find(item => item.serviceId !== bookingService.value?.id) ?? null
)

function putServiceInCart(service) {
    for (const item of [...cartServices.value]) {
        removeFromCart(item.id)
    }

    return addToCart({
        type: 'service',
        serviceId: service.id,
        studioId: service.nail_studio_id,
        name: service.name,
        price: service.price,
        durationMinutes: service.duration_minutes,
    })
}

// Nein → Leistung in den Warenkorb und direkt zum Checkout
function bookWithoutDesign() {
    putServiceInCart(bookingService.value)
    closeBooking()
    showCheckout.value = true
}

// Ja → Leistung in den Warenkorb und weiter zum Design-Editor
function bookWithDesign() {
    putServiceInCart(bookingService.value)
    closeBooking()
    router.visit('/DesignEditor')
}

function durationLabel(service) {
    if (!service.duration_minutes) {
        return 'Je nach Aufwand'
    }

    return service.is_addon
        ? `${service.duration_minutes} Min. extra`
        : `${service.duration_minutes} Min.`
}
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

            <!-- Studio-Auswahl nur, wenn es mehrere Studios gibt -->
            <div v-if="studios.length > 1" class="studio-select">
                <label for="studio">Studio</label>
                <select id="studio" :value="studioId" @change="changeStudio">
                    <option v-for="studio in studios" :key="studio.id" :value="studio.id">
                        {{ studio.name }}
                    </option>
                </select>
            </div>

            <p v-if="loading" class="state">Leistungen werden geladen …</p>

            <p v-else-if="error" class="state state-error">{{ error }}</p>

            <p v-else-if="services.length === 0" class="state">
                Für dieses Studio sind noch keine Leistungen eingetragen.
            </p>

            <template v-else>
                <!-- Kategorie-Filter -->
                <div class="category-filter">
                    <button
                        v-for="category in categories"
                        :key="category"
                        type="button"
                        :class="['filter-button', { active: activeCategory === category }]"
                        :aria-pressed="activeCategory === category"
                        @click="activeCategory = category"
                    >
                        {{ category }}
                    </button>
                </div>

                <!-- Preiskarten -->
                <div class="price-grid">
                    <article
                        v-for="service in visibleServices"
                        :key="service.id"
                        class="price-card"
                    >
                        <span
                            v-if="service.is_popular"
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
                                <span>◷ {{ durationLabel(service) }}</span>
                            </div>
                        </div>

                        <div class="card-footer">
                            <span class="price">
                                {{ priceLabel(service) }}
                            </span>

                            <button
                                v-if="service.bookable"
                                type="button"
                                class="book-button"
                                @click="openBooking(service)"
                            >
                                Buchen
                            </button>
                        </div>
                    </article>
                </div>
            </template>

            <!-- Hinweis -->
            <div class="price-note">
                <p>
                    <strong>Hinweis:</strong>
                    Individuelle Designs und Extras können je nach
                    Aufwand variieren.
                </p>
            </div>

        </div>

        <Teleport to="body">
            <!-- Fenster: Möchtest du dazu ein Design gestalten? -->
            <div
                v-if="bookingService"
                class="booking-overlay"
                @click.self="closeBooking"
                @keydown.esc="closeBooking"
            >
                <div
                    class="booking-dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="booking-title"
                >
                    <button
                        type="button"
                        class="dialog-close"
                        aria-label="Schließen"
                        @click="closeBooking"
                    >
                        ×
                    </button>

                    <h3 id="booking-title">Möchtest du dazu ein Design gestalten?</h3>

                    <p class="dialog-service">
                        {{ bookingService.name }} für {{ priceLabel(bookingService) }},
                        Dauer {{ durationLabel(bookingService) }}
                    </p>

                    <p class="dialog-text">
                        Im Design-Editor gestaltest du Farbe und Muster für jeden Nagel.
                        Danach buchst du Leistung und Design zusammen.
                    </p>

                    <p v-if="replacedService" class="dialog-hint">
                        „{{ replacedService.name }}“ in deinem Warenkorb wird durch diese Leistung ersetzt.
                    </p>

                    <div class="dialog-actions">
                        <button
                            ref="firstDialogButton"
                            type="button"
                            class="dialog-primary"
                            @click="bookWithDesign"
                        >
                            Ja, Design gestalten
                        </button>

                        <button
                            type="button"
                            class="dialog-secondary"
                            @click="bookWithoutDesign"
                        >
                            Nein, direkt buchen
                        </button>
                    </div>
                </div>
            </div>

            <Checkout
                v-if="showCheckout"
                @close="showCheckout = false"
            />
        </Teleport>
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

/* Studio */

.studio-select {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.5rem;
    color: #754353;
    font-size: 0.9rem;
}

.studio-select select {
    padding: 0.5rem 0.9rem;
    border: 1px solid #ead8de;
    border-radius: 999px;
    background: white;
    color: #3d2930;
    font: inherit;
}

/* Zustände */

.state {
    padding: 2rem 0;
    color: #8b7b80;
    text-align: center;
}

.state-error {
    color: #9b2c3c;
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
    padding-right: 4.5rem;

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
    gap: 1rem;

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

.book-button {
    padding: 0.55rem 1.2rem;
    border: none;
    border-radius: 999px;
    background: #8f5364;
    color: #ffffff;
    font: inherit;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.book-button:hover {
    background: #754353;
}

.book-button:focus-visible,
.dialog-primary:focus-visible,
.dialog-secondary:focus-visible,
.dialog-close:focus-visible {
    outline: 2px solid #8f5364;
    outline-offset: 2px;
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

/* Fenster „Design gestalten?“ */

.booking-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(61, 41, 48, 0.4);
}

.booking-dialog {
    position: relative;
    width: 100%;
    max-width: 440px;
    padding: 2rem 1.75rem 1.75rem;
    border-radius: 18px;
    background: #ffffff;
    color: #3d2930;
    box-shadow: 0 20px 50px rgba(61, 41, 48, 0.25);
}

.booking-dialog h3 {
    margin: 0 1.5rem 0.75rem 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.35rem;
    font-weight: 500;
}

.dialog-close {
    position: absolute;
    top: 0.75rem;
    right: 0.9rem;
    width: 2rem;
    height: 2rem;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #8b7b80;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
}

.dialog-service {
    margin: 0 0 0.75rem;
    color: #8f5364;
    font-weight: 600;
}

.dialog-text {
    margin: 0;
    color: #806c73;
    font-size: 0.9rem;
    line-height: 1.6;
}

.dialog-hint {
    margin: 1rem 0 0;
    padding: 0.7rem 0.9rem;
    border-radius: 10px;
    background: #fff6f8;
    color: #806c73;
    font-size: 0.85rem;
}

.dialog-actions {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-top: 1.5rem;
}

.dialog-primary,
.dialog-secondary {
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
}

.dialog-primary {
    border: none;
    background: #8f5364;
    color: #ffffff;
}

.dialog-primary:hover {
    background: #754353;
}

.dialog-secondary {
    border: 1px solid #ead7dd;
    background: #ffffff;
    color: #754353;
}

.dialog-secondary:hover {
    background: #fff0f3;
}

.price {
    color: #8f5364;
    font-size: 1.5rem;
    font-weight: 600;
}

/* Zustände */

.state {
    padding: 2rem 0;
    color: #8b7b80;
    text-align: center;
}

.state-error {
    color: #9b2c3c;
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

@media (max-width: 900px) {
    .service-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .service-overview {
        padding: 3rem 1rem;
    }

    .service-grid {
        grid-template-columns: 1fr;
    }
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