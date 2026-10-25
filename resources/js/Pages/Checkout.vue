<template>
    <div class="checkout-overlay">

        <div class="checkout-window">

            <!-- X zum Schließen -->
            <button
                class="close-button"
                aria-label="Schließen"
                @click="close"
            >
                ×
            </button>

            <h1>Checkout</h1>

            <div class="checkout-content">

                <!-- WARENKORB -->
                <div class="cart-section">

                    <h2>Dein Warenkorb</h2>

                    <p v-if="cartItems.length === 0">
                        Dein Warenkorb ist leer.
                    </p>

                    <div
                        v-for="item in cartItems"
                        :key="item.id"
                        class="cart-item"
                    >
                        <img
                            v-if="item.image"
                            :src="item.image"
                            :alt="item.name"
                            class="cart-item-image"
                        >

                        <div class="cart-item-info">
                            <span>{{ item.name }}</span>
                            <span>{{ euro(item.price) }}</span>
                        </div>

                        <button
                            type="button"
                            class="remove-button"
                            :aria-label="`${item.name} entfernen`"
                            @click="removeFromCart(item.id)"
                        >
                            Entfernen
                        </button>

                    </div>

                    <div class="cart-total">
                        <span>Gesamt</span>
                        <span>{{ euro(total) }}</span>
                    </div>

                </div>


                <!-- TERMIN -->
                <div class="appointment">

                    <h2>Wähle deinen Termin</h2>

                    <!-- Ohne Leistung im Warenkorb → Leistung direkt hier auswählen -->
                    <template v-if="!bookedService">
                        <p v-if="unassignedDesign">
                            Wähle eine Leistung zu deinem Design „{{ unassignedDesign.name }}“:
                        </p>
                        <p v-else>
                            Wähle zuerst eine Leistung aus, damit wir die passenden Uhrzeiten finden.
                        </p>

                        <label v-if="pickerStudios.length > 1" class="picker-studio">
                            <span>Studio</span>
                            <select
                                :value="pickerStudioId"
                                @change="loadPicker(Number($event.target.value))"
                            >
                                <option v-for="studio in pickerStudios" :key="studio.id" :value="studio.id">
                                    {{ studio.name }}
                                </option>
                            </select>
                        </label>

                        <p v-if="pickerLoading" class="picker-state">Leistungen werden geladen …</p>

                        <p v-else-if="pickerError" class="picker-state">{{ pickerError }}</p>

                        <div v-else class="service-picker">
                            <button
                                v-for="service in bookablePickerServices"
                                :key="service.id"
                                type="button"
                                class="service-option"
                                @click="chooseService(service)"
                            >
                                <span class="option-name">{{ service.name }}</span>
                                <span class="option-meta">
                                    {{ service.duration_minutes }} Min., {{ euro(service.price) }}
                                </span>
                            </button>
                        </div>

                        <button
                            type="button"
                            class="text-link"
                            @click="goToServices"
                        >
                            {{ onServicesPage ? 'Zurück zu den Leistungskarten' : 'Alle Leistungen mit Beschreibung ansehen' }}
                        </button>
                    </template>

                    <template v-else>

                        <p class="booked-service">
                            {{ bookedService.name }}
                            <template v-if="bookedService.durationMinutes">
                                ({{ bookedService.durationMinutes }} Min.)
                            </template>
                            <template v-if="bookedDesign">
                                <br>mit Design „{{ bookedDesign.name }}“
                            </template>
                        </p>

                        <!-- NEU: Studio auch bei bereits gewählter Leistung auswählbar -->
                        <label v-if="switchStudios.length > 1" class="picker-studio">
                            <span>Studio</span>
                            <select
                                :value="studioId"
                                :disabled="switchLoading"
                                @change="changeStudio($event)"
                            >
                                <option v-for="studio in switchStudios" :key="studio.id" :value="studio.id">
                                    {{ studio.name }}
                                </option>
                            </select>
                        </label>

                        <p v-if="switchLoading" class="picker-state">Studio wird gewechselt …</p>
                        <p v-else-if="switchError" class="picker-state">{{ switchError }}</p>

                        <label for="date">
                            Datum
                        </label>

                        <!-- :min verhindert vergangene Tage -->
                        <input
                            id="date"
                            type="date"
                            :min="today"
                            v-model="selectedDate"
                        >

                        <label for="time">
                            Uhrzeit
                        </label>

                        <!-- Uhrzeiten kommen vom Server, ausgebuchte sind gesperrt -->
                        <select
                            id="time"
                            v-model="selectedTime"
                            :disabled="!selectedDate"
                        >
                            <option value="">
                                Uhrzeit auswählen
                            </option>

                            <option
                                v-for="slot in times"
                                :key="slot.time"
                                :value="slot.time"
                                :disabled="slot.free === 0"
                            >
                                {{ slot.time }}{{ slot.free === 0 ? ' – ausgebucht' : '' }}
                            </option>
                        </select>

                        <!-- Reservierung mit Countdown -->
                        <p v-if="hold">
                            Diese Uhrzeit ist für dich reserviert – noch {{ countdown }} Min.
                        </p>

                        <!-- Endbetrag vom Server (mit den Preisen dieses Studios) -->
                        <p v-if="hold && serverTotal !== null" class="server-total">
                            Zu zahlen: {{ euro(serverTotal) }}
                        </p>

                        <p v-if="selectedDate && selectedTime">
                            Gewählter Termin:
                            {{ selectedDate }} um {{ selectedTime }}
                        </p>

                        <!-- Nur für Gäste – Kontaktdaten -->
                        <template v-if="!user">
                            <label for="customer-name">
                                Name
                            </label>
                            <input
                                id="customer-name"
                                v-model.trim="customerName"
                                autocomplete="name"
                            >

                            <label for="customer-email">
                                E-Mail (für Bestätigung und Quittung)
                            </label>
                            <input
                                id="customer-email"
                                type="email"
                                v-model.trim="customerEmail"
                                autocomplete="email"
                            >
                        </template>

                        <!-- ZAHLUNG -->
                        <button
                            class="payment-button"
                            :disabled="!canSubmit"
                            @click="submitBooking"
                        >
                            {{ loading ? 'Einen Moment …' : buttonLabel }}
                        </button>

                    </template>

                </div>

            </div>

        </div>

    </div>
</template>

<script setup>

import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useCart } from '@/Stores/cart'
import { usePage, router } from '@inertiajs/vue3'

const emit = defineEmits(['close'])

// Preise im deutschen Format, z. B. 45,00 €
const euroFormat = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' })

function euro(value) {
    return euroFormat.format(Number(value ?? 0))
}

const {
    cartItems,
    services,
    total,
    addToCart,
    updateItem,
    removeFromCart,
    clearCart,
} = useCart()

// Studio und Leistung kommen aus dem Warenkorb
const bookedService = computed(() => services.value[0] ?? null)
const studioId = computed(() => bookedService.value?.studioId ?? null)
const serviceId = computed(() => bookedService.value?.serviceId ?? null)

// Gespeichertes Design, das zu dieser Leistung gehört (aus dem Design-Editor)
const bookedDesign = computed(() =>
    cartItems.value.find(item =>
        item.type === 'design'
        && item.designId
        && item.serviceItemId === bookedService.value?.id
    ) ?? null
)

const selectedDate = ref('')
const selectedTime = ref('')


// ---------- Leistung direkt im Checkout auswählen ----------
// (z. B. wenn die Kundin im Design-Editor angefangen hat und noch keine Leistung gewählt hat)

// Gespeichertes Design, das noch zu keiner Leistung gehört
const unassignedDesign = computed(() =>
    cartItems.value.find(item =>
        item.type === 'design' && item.designId && !item.serviceItemId
    ) ?? null
)

const pickerStudios = ref([])
const pickerStudioId = ref(null)
const pickerServices = ref([])
const pickerLoading = ref(false)
const pickerError = ref('')

const bookablePickerServices = computed(() =>
    pickerServices.value.filter(service => service.bookable)
)

async function loadPicker(studio = null) {
    pickerLoading.value = true
    pickerError.value = ''

    try {
        const url = studio ? `/services/catalog?nail_studio_id=${studio}` : '/services/catalog'
        const response = await fetch(url, { headers: { Accept: 'application/json' } })

        if (!response.ok) {
            throw new Error()
        }

        const data = await response.json()

        pickerStudios.value = data.studios
        pickerStudioId.value = data.studio_id
        pickerServices.value = data.services

        if (bookablePickerServices.value.length === 0) {
            pickerError.value = 'Für dieses Studio sind noch keine buchbaren Leistungen eingetragen.'
        }
    } catch {
        pickerError.value = 'Die Leistungen konnten nicht geladen werden. Bitte versuche es erneut.'
    } finally {
        pickerLoading.value = false
    }
}

function chooseService(service) {
    const serviceItemId = addToCart({
        type: 'service',
        serviceId: service.id,
        studioId: service.nail_studio_id,
        name: service.name,
        price: service.price,
        durationMinutes: service.duration_minutes,
    })

    // Gespeichertes Design ohne Leistung an die gewählte Leistung hängen
    cartItems.value
        .filter(item => item.type === 'design' && item.designId && !item.serviceItemId)
        .forEach(item => updateItem(item.id, { serviceItemId }))
}

// Leistungen laden, sobald keine Leistung im Warenkorb ist
watch(bookedService, (service) => {
    if (!service && pickerServices.value.length === 0 && !pickerLoading.value) {
        loadPicker()
    }
}, { immediate: true })


// ---------- NEU: Studio wechseln, wenn die Leistung schon im Warenkorb ist ----------
// (z. B. über „Buchen“ auf der Leistungsseite)

const switchStudios = ref([])
const switchLoading = ref(false)
const switchError = ref('')

// Liste aller Studios einmal laden
async function loadSwitchStudios() {
    if (switchStudios.value.length > 0) return

    try {
        const response = await fetch('/services/catalog', { headers: { Accept: 'application/json' } })

        if (!response.ok) return

        const data = await response.json()
        switchStudios.value = data.studios ?? []
    } catch {
        // Ohne Liste bleibt die Auswahl einfach ausgeblendet
    }
}

watch(bookedService, (service) => {
    if (service) {
        loadSwitchStudios()
    }
}, { immediate: true })

// Dieselbe Leistung im neu gewählten Studio suchen und im Warenkorb austauschen
async function changeStudio(event) {
    const newStudioId = Number(event.target.value)
    const service = bookedService.value

    if (!service || newStudioId === studioId.value) return

    switchLoading.value = true
    switchError.value = ''

    try {
        const response = await fetch(`/services/catalog?nail_studio_id=${newStudioId}`, {
            headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
            throw new Error()
        }

        const data = await response.json()

        // Wie im Backend: Leistung muss buchbar sein UND genau zu diesem Studio gehören
        const match = (data.services ?? []).find(item =>
            item.bookable
            && Number(item.nail_studio_id) === newStudioId
            && item.name.trim().toLowerCase() === service.name.trim().toLowerCase()
        )

        if (!match) {
            switchError.value = `„${service.name}“ wird in diesem Studio nicht angeboten.`
            event.target.value = String(studioId.value)
            return
        }

        // Gleicher Warenkorb-Eintrag → verknüpftes Design bleibt erhalten
        updateItem(service.id, {
            serviceId: match.id,
            studioId: newStudioId,
            price: match.price,
            durationMinutes: match.duration_minutes,
        })
    } catch {
        switchError.value = 'Das Studio konnte nicht gewechselt werden. Bitte versuche es erneut.'
        event.target.value = String(studioId.value)
    } finally {
        switchLoading.value = false
    }
}


// ---------- Freie Uhrzeiten ----------

// Freie Plätze pro Uhrzeit vom Server, z. B. [{ time: '14:00', free: 3 }]
const times = ref([])

// Heute als YYYY-MM-DD, damit keine vergangenen Tage wählbar sind
const today = new Date().toLocaleDateString('sv-SE')

async function loadTimes(date) {
    await releaseHold()

    selectedTime.value = ''
    times.value = []

    if (!date || !serviceId.value) return

    const response = await fetch(`/appointments/availability?nail_studio_id=${studioId.value}&date=${date}&service_id=${serviceId.value}`, {
        headers: { Accept: 'application/json' },
    })

    if (!response.ok) {
        alert('Die freien Uhrzeiten konnten nicht geladen werden.')
        return
    }

    const data = await response.json()
    times.value = data.times
}

// Sobald ein Datum gewählt wird: alten Platz freigeben, freie Uhrzeiten laden
watch(selectedDate, loadTimes)

// Leistung ODER Studio geändert → Uhrzeiten neu laden bzw. zurücksetzen
watch([serviceId, studioId], () => {
    switchError.value = ''

    if (!serviceId.value) {
        releaseHold()
        selectedDate.value = ''
        return
    }

    loadTimes(selectedDate.value)
})


// ---------- Platz reservieren und freigeben ----------

// Aktuelle Reservierung: { id, hold_token, expires_in, total_price } oder null
const hold = ref(null)

// Betrag, den der Server für diesen Termin berechnet hat
const serverTotal = computed(() =>
    hold.value?.total_price !== undefined && hold.value?.total_price !== null
        ? Number(hold.value.total_price)
        : null
)

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content')
}

// Aktuelle Reservierung auf dem Server freigeben.
// keepalive = true: Anfrage läuft auch weiter, wenn die Seite gerade geschlossen wird
async function releaseHold(keepalive = false) {
    stopCountdown()

    if (!hold.value) return

    const { id, hold_token } = hold.value
    hold.value = null

    await fetch(`/appointments/${id}/hold`, {
        method: 'DELETE',
        keepalive: keepalive,
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Hold-Token': hold_token,
        },
    })
}

// Sobald eine Uhrzeit gewählt wird: alten Platz freigeben, neuen reservieren
watch(selectedTime, async (time) => {
    await releaseHold()

    if (!time || !serviceId.value) return

    const response = await fetch('/appointments/hold', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({
            nail_studio_id: studioId.value,
            service_id: serviceId.value,
            design_id: bookedDesign.value?.designId ?? null,
            date: selectedDate.value,
            time: time,
        }),
    })

    const data = await response.json()

    if (!response.ok) {
        alert(data.message ?? 'Diese Uhrzeit konnte nicht reserviert werden.')
        selectedTime.value = ''
        return
    }

    hold.value = data
    startCountdown(data.expires_in)
})


// ---------- Countdown ----------

const secondsLeft = ref(0)
let timer = null

function startCountdown(seconds) {
    stopCountdown()

    const endsAt = Date.now() + seconds * 1000

    const tick = () => {
        secondsLeft.value = Math.max(0, Math.round((endsAt - Date.now()) / 1000))

        if (secondsLeft.value === 0) {
            stopCountdown()
            hold.value = null       // Server gibt den Platz selbst frei (releaseExpired)
            selectedTime.value = ''
            alert('Deine Reservierung ist abgelaufen. Bitte wähle die Uhrzeit erneut.')
        }
    }

    tick()
    timer = setInterval(tick, 1000)
}

function stopCountdown() {
    clearInterval(timer)
    timer = null
}

// Sekunden als "2:05"
const countdown = computed(() => {
    const minutes = Math.floor(secondsLeft.value / 60)
    const seconds = String(secondsLeft.value % 60).padStart(2, '0')
    return `${minutes}:${seconds}`
})


// ---------- Termin abschicken ----------

// Eingeloggte Kundin oder null (Gast)
const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)

// Nur für Gäste – Kontaktdaten für Bestätigung und Zahlung
const customerName = ref('')
const customerEmail = ref('')

// Läuft gerade eine Anfrage? Verhindert Doppelklicks auf den Button
const loading = ref(false)

const isToday = computed(() => selectedDate.value === today)

const buttonLabel = computed(() =>
    isToday.value ? 'Termin anfragen' : 'Jetzt bezahlen'
)

// Button nur klickbar, wenn reserviert und (bei Gästen) Name + E-Mail ausgefüllt
const canSubmit = computed(() =>
    hold.value
    && !loading.value
    && !switchLoading.value
    && (user.value || (customerName.value && customerEmail.value))
)

async function submitBooking() {
    if (!canSubmit.value) return

    loading.value = true

    const response = await fetch(`/appointments/${hold.value.id}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Hold-Token': hold.value.hold_token,
        },
        // Eingeloggt nichts mitschicken, als Gast Name + E-Mail
        body: JSON.stringify(
            user.value
                ? {}
                : { customer_name: customerName.value, customer_email: customerEmail.value }
        ),
    })

    const data = await response.json()
    loading.value = false

    // Eingaben fehlen oder sind falsch (z. B. E-Mail) → Reservierung bleibt, nochmal versuchen
    if (response.status === 422) {
        alert(data.message ?? 'Bitte prüfe deine Angaben.')
        return
    }

    if (!response.ok) {
        alert(data.message ?? 'Der Termin konnte nicht abgeschickt werden.')
        // Reservierung ist ungültig → Auswahl zurücksetzen
        stopCountdown()
        hold.value = null
        selectedTime.value = ''
        return
    }

    // Der Platz gehört jetzt zum Termin → NICHT mehr freigeben
    stopCountdown()
    hold.value = null

    // Termin ist angelegt → Warenkorb leeren, damit nicht doppelt gebucht wird
    clearCart()

    // Späterer Tag: direkt zur Zahlung
    if (data.checkout_url) {
        window.location.href = data.checkout_url
        return
    }

    // Heute: Anfrage an die Studioleitung
    alert('Deine Anfrage wurde gesendet! Da der Termin heute ist, prüft das Studio kurz die Verfügbarkeit. Wir melden uns per E-Mail. Bezahlt wird erst nach der Bestätigung.')
    emit('close')
}


// ---------- Zu den Leistungen ----------

// Sind wir schon auf der Leistungsseite? Dann nur Fenster schließen und zu den Karten scrollen
const onServicesPage = computed(() =>
    window.location.pathname.toLowerCase() === '/leistungen'
)

function goToServices() {
    if (onServicesPage.value) {
        close()
        document.getElementById('preise')?.scrollIntoView({ behavior: 'smooth' })
        return
    }

    close()
    router.visit('/Leistungen')
}


// ---------- Checkout verlassen ----------

// × geklickt: erst freigeben, dann schließen
function close() {
    releaseHold(true)
    emit('close')
}

// Tab geschlossen oder Seite neu geladen
function onPageHide() {
    releaseHold(true)
}

onMounted(() => {
    window.addEventListener('pagehide', onPageHide)
})

// Komponente wird entfernt (z. B. Wechsel auf eine andere Seite der App)
onBeforeUnmount(() => {
    window.removeEventListener('pagehide', onPageHide)
    releaseHold(true)
})

</script>

<style scoped>

.checkout-overlay {
    position: fixed;

    top: 0;
    left: 0;

    width: 100%;
    height: 100%;

    display: flex;
    justify-content: center;
    align-items: center;

    background: rgba(0, 0, 0, 0.35);

    z-index: 1000;
}


.checkout-window {
    position: relative;

    width: 80%;
    max-width: 900px;

    max-height: 85vh;
    overflow-y: auto;

    padding: 40px;

    background: white;

    border-radius: 12px;

    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
}


/* X */

.close-button {
    position: absolute;

    top: 15px;
    left: 15px;

    width: 35px;
    height: 35px;

    border: none;

    background: transparent;

    font-size: 28px;
    line-height: 1;

    cursor: pointer;
}


.checkout-window h1 {
    margin-bottom: 30px;

    text-align: center;
}


.checkout-content {
    display: flex;

    gap: 40px;

    margin-top: 30px;
}


.cart-section,
.appointment {
    flex: 1;

    padding: 20px;

    background: #f9f9f9;

    border-radius: 8px;
}


.cart-section h2,
.appointment h2 {
    margin-bottom: 20px;
}


/* WARENKORB */

.cart-item {
    display: flex;

    align-items: center;

    gap: 12px;

    padding: 10px 0;

    border-bottom: 1px solid #eee;
}


.cart-item-image {
    width: 50px;
    height: 50px;

    object-fit: cover;

    border-radius: 6px;
}


.cart-item-info {
    display: flex;

    flex: 1;

    flex-direction: column;

    gap: 4px;
}


.remove-button {
    padding: 0;

    border: none;

    background: none;

    color: #9b2c3c;

    font: inherit;
    font-size: 0.8rem;

    text-decoration: underline;

    cursor: pointer;
}


.cart-total {
    display: flex;

    justify-content: space-between;

    margin-top: 20px;
    padding-top: 15px;

    border-top: 1px solid #ddd;

    font-weight: bold;
}


/* TERMIN */

.appointment {
    display: flex;

    flex-direction: column;

    gap: 10px;
}


.booked-service {
    margin: 0 0 6px;

    color: #8f5364;

    font-weight: 600;
}


.server-total {
    margin: 0;

    font-weight: 700;
}


.picker-studio {
    display: flex;

    flex-direction: column;

    gap: 4px;

    font-size: 0.85rem;
}


.picker-state {
    color: #8b7b80;
}


.service-picker {
    display: flex;

    flex-direction: column;

    gap: 8px;
}


.service-option {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 12px;

    padding: 12px 14px;

    border: 1px solid #ead7dd;

    border-radius: 8px;

    background: white;

    color: #3d2930;

    font: inherit;

    text-align: left;

    cursor: pointer;
}


.service-option:hover {
    border-color: #8f5364;

    background: #fff6f8;
}


.service-option:focus-visible,
.text-link:focus-visible {
    outline: 2px solid #8f5364;

    outline-offset: 2px;
}


.option-name {
    font-weight: 600;
}


.option-meta {
    flex-shrink: 0;

    color: #8f5364;

    font-size: 0.85rem;
}


.text-link {
    align-self: flex-start;

    padding: 0;

    border: none;

    background: none;

    color: #8f5364;

    font: inherit;
    font-size: 0.85rem;

    text-decoration: underline;

    cursor: pointer;
}


.services-link {
    align-self: flex-start;

    padding: 10px 16px;

    border: none;

    border-radius: 6px;

    font: inherit;

    cursor: pointer;

    background: #8f5364;

    color: white;

    text-decoration: none;
}


input,
select {
    padding: 10px;
}


/* ZAHLUNG */

.payment-button {
    width: 100%;

    margin-top: 20px;

    padding: 12px;

    border: none;

    border-radius: 6px;

    background: #8f5364;

    color: white;

    cursor: pointer;

    font: inherit;
    font-size: 15px;
    font-weight: 600;
}

.payment-button:hover:not(:disabled) {
    background: #754353;
}

/* Noch nicht alles ausgefüllt */
.payment-button:disabled {
    background: #e6d6db;

    color: #8b7b80;

    cursor: not-allowed;
}

.payment-button:focus-visible {
    outline: 2px solid #8f5364;
    outline-offset: 2px;
}


@media (max-width: 700px) {
    .checkout-window {
        width: 100%;
        padding: 50px 20px 20px;
    }

    .checkout-content {
        flex-direction: column;
        gap: 20px;
    }
}

</style>