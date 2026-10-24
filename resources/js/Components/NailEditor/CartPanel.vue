<template>

    <div class="cart-panel">

        <!-- ========================================= -->
        <!-- ÜBERSCHRIFT -->
        <!-- ========================================= -->

        <div class="cart-header">

            <h3>WARENKORB</h3>

            <span class="cart-count">
                {{ cartItems.length }}
            </span>

        </div>


        <!-- ========================================= -->
        <!-- WARENKORB-INHALT -->
        <!-- ========================================= -->

        <div class="cart-content">

            <!-- Warenkorb leer -->

            <p
                v-if="cartItems.length === 0"
                class="empty-cart"
            >
                Dein Warenkorb ist leer.
            </p>


            <!-- Artikel -->

            <div
                v-for="(item, index) in cartItems"
                :key="index"
                class="cart-item"
            >

                <!-- Bild -->

                <div
                    v-if="item.image"
                    class="cart-item-image-wrapper"
                >

                    <img
                        :src="item.image"
                        :alt="item.name"
                        class="cart-item-image"
                    >

                </div>


                <!-- Informationen -->

                <div class="cart-item-info">

                    <span class="cart-item-name">
                        {{ item.name }}
                    </span>

                    <span class="cart-item-price">
                        {{ item.price.toFixed(2) }} €
                    </span>

                </div>


                <!-- Entfernen -->

                <button
                    type="button"
                    class="remove-button"
                    @click="removeFromCart(index)"
                    aria-label="Artikel entfernen"
                >
                    ×
                </button>

            </div>

        </div>


        <!-- ========================================= -->
        <!-- GESAMT -->
        <!-- ========================================= -->

        <div class="cart-total">

            <span class="total-label">
                Gesamt
            </span>

            <span class="total-price">
                {{ total.toFixed(2) }} €
            </span>

        </div>


        <!-- ========================================= -->
        <!-- CHECKOUT -->
        <!-- ========================================= -->

        <button
            type="button"
            class="checkout-button"
            @click="emit('checkout')"
        >
            Termin buchen
        </button>


    </div>

</template>


<script setup>

import { useCart } from '@/Stores/cart.js'


const emit = defineEmits(['checkout'])


const {
    cartItems,
    total,
    removeFromCart
} = useCart()

</script>


<style scoped>

/* ========================================= */
/* HAUPTPANEL */
/* ========================================= */

.cart-panel {

    width: clamp(10rem, 15vw, 14rem);

    padding: clamp(0.8rem, 1vw, 1.2rem);

    background: #ffffff;

    border: 0.08vw solid #eeeeee;

    border-radius: 0.9rem;

    box-shadow:
        0 0.3vw 1.2vw rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

}


/* ========================================= */
/* HEADER */
/* ========================================= */

.cart-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 0.8rem;

}


.cart-header h3 {

    margin: 0;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(
        0.8rem,
        0.9vw,
        1rem
    );

    font-weight: 400;

    letter-spacing: 0.08em;

}


/* ========================================= */
/* ANZAHL ARTIKEL */
/* ========================================= */

.cart-count {

    display: flex;

    align-items: center;

    justify-content: center;

    min-width: 1.5rem;

    height: 1.5rem;

    padding: 0 0.35rem;

    background: #f3e5e8;

    color: #7d1024;

    border-radius: 50%;

    font-size: 0.7rem;

    box-sizing: border-box;

}


/* ========================================= */
/* INHALT */
/* ========================================= */

.cart-content {

    display: flex;

    flex-direction: column;

    max-height: clamp(
        8rem,
        18vw,
        16rem
    );

    overflow-y: auto;

    padding-right: 0.2rem;

    scrollbar-width: thin;

    scrollbar-color:
        #d8bcc2
        transparent;

}


/* ========================================= */
/* LEERER WARENKORB */
/* ========================================= */

.empty-cart {

    margin: 1.5rem 0;

    color: #999;

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

    line-height: 1.5;

    text-align: center;

}


/* ========================================= */
/* WARENKORB-ARTIKEL */
/* ========================================= */

.cart-item {

    display: flex;

    align-items: center;

    gap: 0.6rem;

    padding: 0.55rem 0.25rem;

    border-bottom:
        0.08vw solid #eeeeee;

}


/* ========================================= */
/* BILD */
/* ========================================= */

.cart-item-image-wrapper {

    width: clamp(
        2rem,
        2.5vw,
        2.8rem
    );

    height: clamp(
        2rem,
        2.5vw,
        2.8rem
    );

    flex-shrink: 0;

    overflow: hidden;

    border-radius: 0.5rem;

    background: #f8f0f2;

}


.cart-item-image {

    width: 100%;

    height: 100%;

    object-fit: cover;

}


/* ========================================= */
/* ARTIKEL-INFOS */
/* ========================================= */

.cart-item-info {

    display: flex;

    flex-direction: column;

    flex: 1;

    min-width: 0;

    gap: 0.2rem;

}


.cart-item-name {

    overflow: hidden;

    color: #333;

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

    font-weight: 500;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.cart-item-price {

    color: #7d1024;

    font-size: clamp(
        0.65rem,
        0.75vw,
        0.85rem
    );

}


/* ========================================= */
/* ENTFERNEN-BUTTON */
/* ========================================= */

.remove-button {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 1.6rem;

    height: 1.6rem;

    padding: 0;

    border: none;

    border-radius: 50%;

    background: transparent;

    color: #999;

    font-size: 1.2rem;

    line-height: 1;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;

}


.remove-button:hover {

    background: #f3e5e8;

    color: #7d1024;

    transform: scale(1.08);

}


/* ========================================= */
/* GESAMT */
/* ========================================= */

.cart-total {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: 0.8rem;

    padding-top: 0.8rem;

    border-top:
        0.08vw solid #dddddd;

}


.total-label {

    color: #555;

    font-size: clamp(
        0.75rem,
        0.85vw,
        0.95rem
    );

}


.total-price {

    color: #7d1024;

    font-size: clamp(
        0.85rem,
        1vw,
        1.1rem
    );

    font-weight: 600;

}


/* ========================================= */
/* CHECKOUT-BUTTON */
/* ========================================= */

.checkout-button {

    width: 100%;

    margin-top: 1rem;

    padding: clamp(
        0.6rem,
        0.8vw,
        0.8rem
    );

    border: none;

    border-radius: 0.6rem;

    background: #7d1024;

    color: #ffffff;

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

    font-weight: 500;

    letter-spacing: 0.03em;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;

}


.checkout-button:hover {

    background: #64101f;

    transform: translateY(-0.1rem);

    box-shadow:
        0 0.3vw 0.8vw
        rgba(125, 16, 36, 0.18);

}


/* ========================================= */
/* SCROLLBAR */
/* ========================================= */

.cart-content::-webkit-scrollbar {

    width: 0.3rem;

}


.cart-content::-webkit-scrollbar-track {

    background: transparent;

}


.cart-content::-webkit-scrollbar-thumb {

    background: #d8bcc2;

    border-radius: 1rem;

}


.cart-content::-webkit-scrollbar-thumb:hover {

    background: #7d1024;

}


/* ========================================= */
/* MOBILE */
/* ========================================= */

@media (max-width: 700px) {

    .cart-panel {

        width: 75vw;

        max-width: 14rem;

        padding: 1rem;

    }


    .cart-content {

        max-height: 50vw;

    }

}

</style>