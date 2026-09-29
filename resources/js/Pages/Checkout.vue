<template>
    <div class="checkout-page">

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

                <input
                    id="date"
                    type="date"
                    v-model="selectedDate"
                >

                <label for="time">
                    Uhrzeit
                </label>

                <select
                    id="time"
                    v-model="selectedTime"
                >
                    <option value="">
                        Uhrzeit auswählen
                    </option>

                    <option>10:00</option>
                    <option>11:00</option>
                    <option>12:00</option>
                    <option>13:00</option>
                    <option>14:00</option>
                    <option>15:00</option>
                    <option>16:00</option>
                    <option>17:00</option>
                </select>

                <p v-if="selectedDate && selectedTime">
                    Gewählter Termin:
                    {{ selectedDate }} um {{ selectedTime }}
                </p>

                <!-- ZAHLUNG -->
                <button
                    class="payment-button"
                    @click="startPayment"
                >
                    Weiter zur Zahlung
                </button>

            </div>

        </div>

    </div>
</template>


<script setup>

import { ref } from 'vue'
import { useCart } from '@/Stores/cart.js'

const {
    cartItems,
    total
} = useCart()

const selectedDate = ref('')
const selectedTime = ref('')


async function startPayment() {

    const response = await fetch('/api/stripe/checkout', {
        method: 'POST',

        headers: {
            'Content-Type': 'application/json',
        },

        body: JSON.stringify({
            payment_id: 1
        }),
    })

    const data = await response.json()

    console.log('Stripe Antwort:', data)
}

</script>


<style scoped>

.checkout-page {
    padding: 40px;
}

.checkout-page h1 {
    margin-bottom: 30px;
}

.checkout-content {
    display: flex;
    gap: 40px;
    margin-top: 30px;
}

.cart-section,
.appointment {
    width: 350px;
    padding: 20px;
    background: white;
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