<template>
    <div class="cart-panel">

        <h3>WARENKORB</h3>

        <div class="cart-content">

            <p v-if="cartItems.length === 0">
                Dein Warenkorb ist leer.
            </p>

            <div
                v-for="(item, index) in cartItems"
                :key="index"
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
                    <span>{{ item.price.toFixed(2) }} €</span>
                </div>

                <button
                    class="remove-button"
                    @click="removeFromCart(index)"
                >
                    ×
                </button>
            </div>

        </div>

        <div class="cart-total">
            <span>Gesamt</span>
            <span>{{ total.toFixed(2) }} €</span>
        </div>

        <button class="checkout-button">
            Zum Warenkorb
        </button>

    </div>
</template>

<script setup>
import { useCart } from '@/Stores/cart.js'

const {
    cartItems,
    total,
    removeFromCart
} = useCart()
</script>

<style scoped>

.cart-panel {
    width: 220px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.cart-panel h3 {
    font-size: 14px;
    margin-bottom: 20px;
}

.cart-content {
    min-height: 100px;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.cart-item-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
}

.cart-item-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    font-size: 12px;
}

.remove-button {
    border: none;
    background: transparent;
    font-size: 18px;
    cursor: pointer;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    padding-top: 15px;
    margin-top: 15px;
    border-top: 1px solid #ddd;
    font-size: 14px;
}

.checkout-button {
    width: 100%;
    margin-top: 20px;
    padding: 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

</style>