<template>
    <div class="checkout-overlay">

        <div class="checkout-window">

            <!-- X zum Schließen -->
            <button
                class="close-button"
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
                        v-for="(item, index) in cartItems"
                        :key="index"
                        class="cart-item"
                    >
                        <img
                            :src="item.image"
                            :alt="item.name"
                            class="cart-item-image"
                        >

                        <div class="cart-item-info">
                            <span>{{ item.name }}</span>
                            <span>{{ item.price.toFixed(2) }} €</span>
                        </div>

                    </div>

                    <div class="cart-total">
                        <span>Gesamt</span>
                        <span>{{ total.toFixed(2) }} €</span>
                    </div>

                </div>


                <!-- TERMIN -->
                <div class="appointment">

                    <h2>Wähle deinen Termin</h2>

                    <label for="date">
                        Datum
                    </label>

                    <!-- GEÄNDERT: :min verhindert vergangene Tage -->
                    <input
                        id="date"
                        type="date"
                        :min="today"
                        v-model="selectedDate"
                    >

                    <label for="time">
                        Uhrzeit
                    </label>

                    <!-- GEÄNDERT: Uhrzeiten kommen vom Server, ausgebuchte sind gesperrt -->
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

                    <p v-if="hold">
    Diese Uhrzeit ist für dich reserviert.
</p>

                    <p v-if="selectedDate && selectedTime">
                        Gewählter Termin:
                        {{ selectedDate }} um {{ selectedTime }}
                    </p>


                    <!-- ZAHLUNG -->
                    <button class="payment-button" @click="createAppointment">
                        Termin anfragen
                    </button>

                </div>

            </div>

        </div>

    </div>
</template>


<script setup>

import { ref, watch, onMounted, onBeforeUnmount } from 'vue'   // GEÄNDERT (Schritt 18)
import { useCart } from '@/Stores/cart.js'


const emit = defineEmits(['close'])

const {
    cartItems,
    total
} = useCart()

const selectedDate = ref('')
const selectedTime = ref('')


// ---------- Schritt 15: freie Uhrzeiten ----------

// Freie Plätze pro Uhrzeit vom Server, z. B. [{ time: '14:00', free: 3 }]
const times = ref([])

// Heute als YYYY-MM-DD, damit keine vergangenen Tage wählbar sind
const today = new Date().toLocaleDateString('sv-SE')

// Sobald ein Datum gewählt wird: alten Platz freigeben, freie Uhrzeiten laden
watch(selectedDate, async (date) => {
    await releaseHold()

    selectedTime.value = ''
    times.value = []

    if (!date) return

    const response = await fetch(`/appointments/availability?nail_studio_id=1&date=${date}`, {
        headers: { Accept: 'application/json' },
    })

    if (!response.ok) {
        alert('Die freien Uhrzeiten konnten nicht geladen werden.')
        return
    }

    const data = await response.json()
    times.value = data.times
})


// ---------- Schritt 16 bis 18: Platz reservieren und freigeben ----------

// Aktuelle Reservierung: { id, hold_token, expires_in } oder null
const hold = ref(null)

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content')
}

// Aktuelle Reservierung auf dem Server freigeben.
// keepalive = true: Anfrage läuft auch weiter, wenn die Seite gerade geschlossen wird
async function releaseHold(keepalive = false) {     // GEÄNDERT (Schritt 18)
    if (!hold.value) return

    const { id, hold_token } = hold.value
    hold.value = null

    await fetch(`/appointments/${id}/hold`, {
        method: 'DELETE',
        keepalive: keepalive,                        // NEU (Schritt 18)
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

    if (!time) return

    const response = await fetch('/appointments/hold', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({
            nail_studio_id: 1,
            service_id: 1,
            design_id: null,
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
})

// ---------- NEU (Schritt 18): Checkout verlassen ----------

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

// ---------- ENDE NEU ----------


/**
 * Termin anfragen
 *
 * Der Termin wird zunächst mit dem Status "requested"
 * im Backend angelegt.
 */
async function createAppointment() {

    if (!selectedDate.value || !selectedTime.value) {
        alert('Bitte wähle zuerst einen Termin aus.')
        return
    }

    const response = await fetch('/appointments', {
        method: 'POST',

        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content'),
        },

        body: JSON.stringify({
            nail_studio_id: 1,
            service_id: 1,
            design_id: null,
            date: selectedDate.value,
            time: selectedTime.value,
        }),
    })

    const data = await response.json()

    console.log('Termin:', data)

    if (response.ok) {
        alert('Deine Terminanfrage wurde erfolgreich erstellt.')
    } else {
        alert('Der Termin konnte nicht erstellt werden.')
    }
}


/**
 * Stripe-Zahlung
 *
 * Diese Funktion bleibt bestehen.
 * Sie wird später aufgerufen, nachdem der
 * Termin vom Admin bestätigt wurde.
 */
async function startPayment() {

    const response = await fetch('/api/stripe/checkout', {
        method: 'POST',

        headers: {
            'Content-Type': 'application/json',
        },

        body: JSON.stringify({
            payment_id: 6,
        }),
    })

    const data = await response.json()

    console.log('Stripe Antwort:', data)

    if (data.checkout_url) {
        window.location.href = data.checkout_url
    }
}

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

    flex-direction: column;

    gap: 4px;
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

    cursor: pointer;

    font-size: 14px;
}

</style>