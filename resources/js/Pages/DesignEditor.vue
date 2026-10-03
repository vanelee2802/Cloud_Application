<script setup>

import { ref, computed } from 'vue'

import pagesLayout from '@/Layouts/pagesLayout.vue'

import NailCanvas from '@/Components/NailEditor/NailCanvas.vue'
import CategoryPanel from '@/Components/NailEditor/CategoryPanel.vue'
import FormPanel from '@/Components/NailEditor/FormPanel.vue'
import ColorPanel from '@/Components/NailEditor/ColorPanel.vue'
import DesignPanel from '@/Components/NailEditor/DesignPanel.vue'
import EffektPanel from '@/Components/NailEditor/EffektPanel.vue'
import NailSelectionPanel from '@/Components/NailEditor/NailSelectionPanel.vue'
import CartPanel from '@/Components/NailEditor/CartPanel.vue'
import Checkout from '@/Pages/Checkout.vue'
import CharmPanel from '@/Components/NailEditor/CharmPanel.vue'

import { useCart } from '@/Stores/cart.js'


// =========================================
// KATEGORIEN
// =========================================

const selectedCategories = ref([])


// =========================================
// NÄGEL AUSWÄHLEN
// =========================================

const showNailSelection = ref(false)


// =========================================
// CHECKOUT
// =========================================

const showCheckout = ref(false)


// =========================================
// WARENKORB
// =========================================

const showCart = ref(false)

const {
    cartItems
} = useCart()


const cartItemCount = computed(() => {

    return cartItems.value.length

})


// =========================================
// KATEGORIE AUSWÄHLEN
// =========================================

function selectCategory(category) {

    if (selectedCategories.value.includes(category)) {

        selectedCategories.value =
            selectedCategories.value.filter(
                item => item !== category
            )

    } else {

        selectedCategories.value.push(category)

    }

}


// =========================================
// FARBE AUSWÄHLEN
// =========================================

function selectColor(color) {

    console.log(
        'Ausgewählte Farbe:',
        color
    )

}


// =========================================
// VERZIERUNG AUSWÄHLEN
// =========================================

function selectVerziehrung(verziehrung) {

    console.log(
        'Ausgewählte Verzierung:',
        verziehrung
    )

}


// =========================================
// WARENKORB ÖFFNEN / SCHLIESSEN
// =========================================

function toggleCart() {

    showCart.value = !showCart.value

}

</script>


<template>

    <pagesLayout>


        <!-- ========================================= -->
        <!-- WARENKORB BUTTON -->
        <!-- ========================================= -->

        <button
            type="button"
            class="cart-toggle"
            @click="toggleCart"
        >

            <span class="cart-icon">
                🛒
            </span>

            <span class="cart-label">
                Warenkorb
            </span>

            <span
                v-if="cartItemCount > 0"
                class="cart-badge"
            >
                {{ cartItemCount }}
            </span>

        </button>


        <!-- ========================================= -->
        <!-- WARENKORB -->
        <!-- ========================================= -->

        <div
            v-if="showCart"
            class="cart-dropdown"
        >

            <CartPanel
                @checkout="showCheckout = true"
            />

        </div>


        <!-- ========================================= -->
        <!-- HAUPTBEREICH -->
        <!-- ========================================= -->

        <main class="nail-designer">


            <!-- ========================================= -->
            <!-- EDITOR -->
            <!-- ========================================= -->

            <div class="editor-row">


                <!-- ========================================= -->
                <!-- LINKER BEREICH -->
                <!-- ========================================= -->

                <div class="left-editor">


                    <!-- ========================================= -->
                    <!-- KATEGORIEN / FORM / EFFEKT -->
                    <!-- ========================================= -->

                    <div class="category-layout">


                        <!-- ========================================= -->
                        <!-- KATEGORIEN -->
                        <!-- ========================================= -->

                        <section class="categories">


                            <!-- ========================================= -->
                            <!-- CATEGORY PANEL -->
                            <!-- ========================================= -->

                            <CategoryPanel
                                @category-selected="selectCategory"
                            />


                            <!-- ========================================= -->
                            <!-- NÄGEL AUSWÄHLEN -->
                            <!-- ========================================= -->

                            <div class="nail-selection-wrapper">


                                <!-- ========================================= -->
                                <!-- PFEIL -->
                                <!-- ========================================= -->

                                <button
                                    type="button"
                                    class="nail-selection-toggle"
                                    @click="
                                        showNailSelection =
                                            !showNailSelection
                                    "
                                    :aria-expanded="
                                        showNailSelection
                                    "
                                    aria-label="Nägel auswählen ein- oder ausblenden"
                                >

                                    <span
                                        :class="[
                                            'arrow',
                                            {
                                                'arrow-open':
                                                    showNailSelection
                                            }
                                        ]"
                                    >
                                        ›
                                    </span>

                                </button>


                                <!-- ========================================= -->
                                <!-- NAIL SELECTION PANEL -->
                                <!-- ========================================= -->

                                <div
                                    v-if="showNailSelection"
                                    class="nail-selection-container"
                                >

                                    <NailSelectionPanel />

                                </div>


                            </div>


                        </section>


                        <!-- ========================================= -->
                        <!-- FORM + EFFEKT -->
                        <!-- ========================================= -->

                        <div class="form-effekt-area">


                            <!-- ========================================= -->
                            <!-- FORM -->
                            <!-- ========================================= -->

                            <FormPanel
                                v-if="
                                    selectedCategories.includes('Form')
                                "
                            />


                            <!-- ========================================= -->
                            <!-- EFFEKT -->
                            <!-- ========================================= -->

                            <EffektPanel
                                v-if="
                                    selectedCategories.includes('Effekte')
                                "
                            />


                        </div>


                    </div>


                </div>


                <!-- ========================================= -->
                <!-- NAGEL CANVAS -->
                <!-- ========================================= -->

                <section class="nail-canvas">

                    <NailCanvas />

                </section>


                <!-- ========================================= -->
                <!-- DESIGN + VERZIERUNG -->
                <!-- ========================================= -->

                <div class="design-area">


                    <!-- ========================================= -->
                    <!-- DESIGNS -->
                    <!-- ========================================= -->

                    <DesignPanel
                        v-if="
                            selectedCategories.includes('Designs')
                        "
                    />


                    <!-- ========================================= -->
                    <!-- VERZIERUNG -->
                    <!-- ========================================= -->

                    <CharmPanel
                        v-if="
                            selectedCategories.includes('Verzierung')
                        "
                        @verziehrung-selected="
                            selectVerziehrung
                        "
                    />


                </div>


                <!-- ========================================= -->
                <!-- FARBE -->
                <!-- ========================================= -->

                <div
                    v-if="
                        selectedCategories.includes('Farbe')
                    "
                    class="color-section"
                >

                    <ColorPanel
                        @color-selected="selectColor"
                    />

                </div>


            </div>


        </main>


        <!-- ========================================= -->
        <!-- CHECKOUT -->
        <!-- ========================================= -->

        <Checkout
            v-if="showCheckout"
            @close="showCheckout = false"
        />


    </pagesLayout>

</template>


<style scoped>


/* ========================================= */
/* HAUPTBEREICH */
/* ========================================= */

.nail-designer {

    position: relative;

    display: flex;

    flex-direction: column;

    width: 100%;

    min-height: 100%;

    gap: 20px;

}


/* ========================================= */
/* WARENKORB BUTTON */
/* ========================================= */

.cart-toggle {

    position: fixed;

    right: clamp(
        1rem,
        2vw,
        2.5rem
    );

    bottom: clamp(
        1rem,
        2vw,
        2rem
    );

    z-index: 1000;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 0.5rem;

    min-height: 2.8rem;

    padding:
        0.65rem
        1rem;

    border: none;

    border-radius: 0.7rem;

    background: #7d1024;

    color: #ffffff;

    cursor: pointer;

    box-shadow:
        0
        0.3vw
        1vw
        rgba(0, 0, 0, 0.15);

    transition:
        background-color 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;

}


.cart-toggle:hover {

    background: #64101f;

    transform: translateY(-0.1rem);

    box-shadow:
        0
        0.5vw
        1.2vw
        rgba(125, 16, 36, 0.2);

}


/* ========================================= */
/* WARENKORB ICON */
/* ========================================= */

.cart-icon {

    font-size: 1.1rem;

    line-height: 1;

}


/* ========================================= */
/* WARENKORB TEXT */
/* ========================================= */

.cart-label {

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

    font-weight: 500;

}


/* ========================================= */
/* WARENKORB ANZAHL */
/* ========================================= */

.cart-badge {

    display: flex;

    align-items: center;

    justify-content: center;

    min-width: 1.35rem;

    height: 1.35rem;

    padding:
        0
        0.25rem;

    border-radius: 50%;

    background: #ffffff;

    color: #7d1024;

    font-size: 0.65rem;

    font-weight: 600;

}


/* ========================================= */
/* WARENKORB DROPDOWN */
/* ========================================= */

.cart-dropdown {

    position: fixed;

    right: clamp(
        1rem,
        2vw,
        2.5rem
    );

    bottom: clamp(
        4.8rem,
        6vw,
        6rem
    );

    z-index: 999;

}


/* ========================================= */
/* EDITOR ROW */
/* ========================================= */

.editor-row {

    display: flex;

    align-items: flex-start;

    width: 100%;

    gap: 20px;

}


/* ========================================= */
/* LINKER BEREICH */
/* ========================================= */

.left-editor {

    display: flex;

    flex-direction: column;

    width: fit-content;

    gap: 20px;

    flex-shrink: 0;

    margin-left: clamp(
        0.25rem,
        0.5vw,
        0.75rem
    );

}


/* ========================================= */
/* KATEGORIEN-BEREICH */
/* ========================================= */

.category-layout {

    display: grid;

    grid-template-columns:
        fit-content(100%)
        fit-content(100%);

    grid-template-rows:
        auto;

    align-items: start;

    column-gap: 20px;

}


/* ========================================= */
/* KATEGORIEN */
/* ========================================= */

.categories {

    grid-column: 1;

    grid-row: 1;

    display: flex;

    flex-direction: column;

    width: fit-content;

    align-self: start;

}


/* ========================================= */
/* NÄGEL AUSWÄHLEN */
/* ========================================= */

.nail-selection-wrapper {

    position: relative;

    width: fit-content;

    margin-top: 20px;

}


/* ========================================= */
/* NÄGEL PANEL */
/* ========================================= */

.nail-selection-container {

    width: fit-content;

}


/* ========================================= */
/* PFEIL BUTTON */
/* ========================================= */

.nail-selection-toggle {

    position: absolute;

    top: 50%;

    right: -1.2rem;

    z-index: 10;

    display: flex;

    align-items: center;

    justify-content: center;

    width: 1.5rem;

    height: 3rem;

    padding: 0;

    border: none;

    border-radius:
        0
        0.5rem
        0.5rem
        0;

    background: #ffffff;

    color: #7d1024;

    cursor: pointer;

    box-shadow:
        0.2vw
        0
        0.5vw
        rgba(0, 0, 0, 0.08);

    transform: translateY(-50%);

    transition:
        background-color 0.2s ease,
        color 0.2s ease;

}


.nail-selection-toggle:hover {

    background: #f8f0f2;

    color: #64101f;

}


/* ========================================= */
/* PFEIL */
/* ========================================= */

.arrow {

    display: block;

    font-size: 1.5rem;

    line-height: 1;

    transition:
        transform 0.25s ease;

}


.arrow-open {

    transform: rotate(180deg);

}


/* ========================================= */
/* FORM + EFFEKT */
/* ========================================= */

.form-effekt-area {

    grid-column: 2;

    grid-row: 1;

    display: flex;

    flex-direction: column;

    width: fit-content;

    gap: 20px;

    align-self: start;

}


/* ========================================= */
/* NAGEL CANVAS */
/* ========================================= */

.nail-canvas {

    width: 35vw;

    aspect-ratio: 1080 / 1056;

    flex-shrink: 0;

}


/* ========================================= */
/* DESIGN + VERZIERUNG */
/* ========================================= */

.design-area {

    display: flex;

    flex-direction: column;

    width: fit-content;

    gap: 20px;

    flex-shrink: 0;

    align-self: flex-start;

}


/* ========================================= */
/* FARBE */
/* ========================================= */

.color-section {

    display: flex;

    gap: 10px;

    flex-shrink: 0;

}


/* ========================================= */
/* TABLET */
/* ========================================= */

@media (max-width: 1200px) {

    .editor-row {

        flex-wrap: wrap;

    }


    .nail-canvas {

        width: 50vw;

    }


    .color-section {

        width: 50vw;

        margin-left: 0;

    }

}


/* ========================================= */
/* MOBILE */
/* ========================================= */

@media (max-width: 768px) {

    .cart-toggle {

        right: 1rem;

        bottom: 1rem;

    }


    .cart-dropdown {

        right: 1rem;

        bottom: 4.8rem;

        max-width:
            calc(100vw - 2rem);

    }


    .editor-row {

        flex-direction: column;

    }


    .left-editor {

        margin-left: 0;

    }


    .nail-canvas {

        width: 90vw;

    }


    .category-layout {

        display: flex;

        flex-direction: column;

        gap: 20px;

    }


    .categories {

        width: fit-content;

    }


    .form-effekt-area {

        width: fit-content;

    }


    .design-area {

        width: fit-content;

    }


    .color-section {

        width: 90vw;

        margin-left: 0;

    }


    .nail-selection-toggle {

        right: -1rem;

    }

}

</style>