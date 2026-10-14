<script setup>

import { ref, computed, onMounted } from 'vue'

import pagesLayout from '@/Layouts/pagesLayout.vue'

import NailHand from '@/Components/NailEditor/NailHand.vue'
import CategoryPanel from '@/Components/NailEditor/CategoryPanel.vue'
import FormPanel from '@/Components/NailEditor/FormPanel.vue'
import ColorPanel from '@/Components/NailEditor/ColorPanel.vue'
import DesignPanel from '@/Components/NailEditor/DesignPanel.vue'
import EffektPanel from '@/Components/NailEditor/EffektPanel.vue'
import CartPanel from '@/Components/NailEditor/CartPanel.vue'
import Checkout from '@/Pages/Checkout.vue'
import CharmPanel from '@/Components/NailEditor/CharmPanel.vue'

import { useCart } from '@/Stores/cart.js'
import NailSelectionPanel from '@/Components/NailEditor/NailSelectionPanel.vue'

const props = defineProps({
    nailShapes: {
        type: Array,
        default: () => [],
    },
    colors: {
        type: Array,
        default: () => [],
    },
})

// =========================================
// KATEGORIEN
// =========================================

const selectedCategories = ref([])


// =========================================
// NAGELFORM + LÄNGE
// =========================================

// shape  = Form (id aus dem FormPanel = Ordnername)
// length = Länge: short | medium | long
const shape  = ref('almond')
const length = ref('medium')


// Alle Nagelbilder vorladen, damit beim Umschalten nichts flackert
const FOLDERS = [
    ...['almond', 'square', 'oval', 'coffin', 'ballerina', 'squoval'].map(s => `${s}-right hand`),
    ...['almond', 'square', 'oval'].map(s => `${s}-short-right hand`),
    ...['almond', 'square', 'coffin', 'ballerina', 'oval'].map(s => `${s}-long-right hand`),
    'stiletto-right hand'
]

const FINGERS = ['thumb', 'index', 'middle', 'ring', 'pinky']

onMounted(() => {

    for (const folder of FOLDERS) {

        for (const finger of FINGERS) {

            const img = new Image()

            img.src = `/images/nail-editor/${folder}/${finger}.png`

        }

    }

})


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
// UNTERE ROW: Farbe und/oder Designs offen?
// =========================================

const showBottomRow = computed(() =>
    selectedCategories.value.includes('Farbe') ||
    selectedCategories.value.includes('Designs')
)


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
// AUSWAHL-EVENTS
// =========================================

function selectColor(color) {

    console.log('Ausgewählte Farbe:', color)

}

function selectDesign(design) {

    console.log('Ausgewähltes Design:', design)

}

function selectEffekt(effekt) {

    console.log('Ausgewählter Effekt:', effekt)

}

function selectVerziehrung(verziehrung) {

    console.log('Ausgewählte Verzierung:', verziehrung)

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
        <!-- HAUPTBEREICH -->
        <!-- ========================================= -->

        <main class="nail-designer">


            <!-- ===================================================== -->
            <!-- EDITOR (GRID)                                         -->
            <!--                                                       -->
            <!--  Kategorien | Form  | Hand    | Verzierung | Korb-Panel -->
            <!--  Kategorien | Farbe | Designs ──────────── | Korb-Button-->
            <!-- ===================================================== -->

            <div class="editor-row">


                <!-- ========================================= -->
                <!-- SPALTE 1: KATEGORIEN + EFFEKT -->
                <!-- ========================================= -->

                <section class="categories area-kategorien">

                    <CategoryPanel
                        @category-selected="selectCategory"
                    />

                    <div
                        v-if="
                            selectedCategories.includes('Effekte')
                        "
                        class="nail-selection-wrapper"
                    >

                        <EffektPanel
                            @effekt-selected="selectEffekt"
                        />

                    </div>

                </section>


                <!-- ========================================= -->
                <!-- SPALTE 2: FORM + LÄNGE -->
                <!-- ========================================= -->

                <div
                    v-if="
                        selectedCategories.includes('Form')
                    "
                    class="form-effekt-area area-form"
                >

                    <FormPanel
                        :selected="shape"
                        :selected-length="length"
                        @form-selected="shape = $event"
                        @length-selected="length = $event"
                    />

                </div>


                <!-- ========================================= -->
                <!-- SPALTE 3: NAGEL CANVAS -->
                <!-- ========================================= -->

                <section class="nail-canvas area-canvas">

                    <div class="nail-canvas-inner">

                        <NailHand
                            :shape="shape"
                            :length="length"
                        />

                    </div>

                </section>


                <!-- ========================================= -->
                <!-- SPALTE 4: VERZIERUNG -->
                <!-- ========================================= -->

                <div
                    v-if="
                        selectedCategories.includes('Verzierung')
                    "
                    class="design-area area-deko"
                >

                    <CharmPanel
                        @verziehrung-selected="
                            selectVerziehrung
                        "
                    />


                </div>

                

                
                <!-- ========================================= -->
                <!-- UNTERE ROW: FARBE + DESIGNS -->
                <!-- reicht bis zum Ende der Verzierung -->
                <!-- ========================================= -->

                <div
                    v-if="showBottomRow"
                    class="bottom-row area-bottom"
                >

                    <div
                        v-if="
                            selectedCategories.includes('Farbe')
                        "
                        class="color-section"
                    >

                        <ColorPanel
                            :colors="props.colors"
                            @color-selected="selectColor"
                        />

                    </div>

                    <div
                        v-if="
                            selectedCategories.includes('Designs')
                        "
                        class="design-section"
                    >

                        <DesignPanel
                            @design-selected="selectDesign"
                        />

                    </div>

                </div>


                <!-- ========================================= -->
                <!-- SPALTE 5 OBEN: WARENKORB-PANEL -->
                <!-- (neben Verzierung) -->
                <!-- ========================================= -->

                <Transition name="cart">

                    <div
                        v-if="showCart"
                        class="cart-dropdown area-cartpanel"
                    >

                        <CartPanel
                            @checkout="showCheckout = true"
                        />

                    </div>

                </Transition>


                <!-- ========================================= -->
                <!-- SPALTE 5 UNTEN: WARENKORB-BUTTON -->
                <!-- ========================================= -->

                <div class="cart-cell area-cart">

                    <button
                        type="button"
                        class="cart-toggle"
                        :aria-expanded="showCart"
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

    /*
    | Höhe von allem, was ÜBER dem Editor liegt
    | (Navigation aus pagesLayout + dessen Padding).
    | Wenn unten eine Scrollbar erscheint: Wert erhöhen.
    */
    --header-h: 80px;

    --gap: 20px;

    /* Höhe der unteren Row, wenn Farbe/Designs offen sind */
    --farbe-h: 10rem;

    /* Höhe der unteren Row, wenn nur der Warenkorb-Button da ist */
    --cart-row-h: 2.8rem;

    /* Breite der Warenkorb-Spalte */
    --cart-w: clamp(13rem, 15vw, 16rem);

    /* Seitenverhältnis der Hand inkl. Freiraum für lange Nägel */
    --hand-ratio: 1080 / 1186;

    position: relative;

    width: 100%;

    height: calc(100vh - var(--header-h));
    height: calc(100dvh - var(--header-h));

    box-sizing: border-box;

    /* Damit die Hand-Spalte ihre Breite aus der Höhe berechnen kann */
    container-type: size;

}


/* ========================================= */
/* EDITOR GRID */
/* ========================================= */

.editor-row {

    /* aktuelle Höhe der unteren Row */
    --bottom-h: var(--cart-row-h);

    /* Höhe, die der Hand zur Verfügung steht */
    --canvas-h: calc(100cqh - var(--bottom-h) - var(--gap));

    display: grid;

    /*
    | Die Hand-Spalte ist genau so breit wie das Bild bei voller Höhe.
    | Ist der Bildschirm zu schmal, schrumpft sie (minmax 0).
    */
    grid-template-columns:
        auto                                                  /* Kategorien + Effekt */
        auto                                                  /* Form */
        minmax(0, calc(var(--canvas-h) * var(--hand-ratio))) /* Hand */
        auto                                                  /* Verzierung */
        var(--cart-w);                                        /* Warenkorb */

    grid-template-rows:
        minmax(0, 1fr)
        var(--bottom-h);

    grid-template-areas:
        "kategorien form   canvas deko   cartpanel"
        "kategorien bottom bottom bottom cart";

    /* Spalten nicht strecken, sondern als Block zentrieren */
    justify-content: center;

    gap: var(--gap);

    width: 100%;
    height: 100%;

    padding:
        0
        clamp(0.25rem, 0.5vw, 0.75rem);

    box-sizing: border-box;

}

/* Untere Row bekommt volle Höhe, wenn Farbe oder Designs offen sind */
.editor-row:has(.area-bottom) {

    --bottom-h: var(--farbe-h);

}


/* ========================================= */
/* BEREICHE */
/* ========================================= */

/*
| min-height: 0 ist entscheidend: Ohne das wachsen die Panels
| über den Bildschirm hinaus, statt intern zu scrollen.
*/

.area-kategorien,
.area-form,
.area-canvas,
.area-deko,
.area-bottom,
.area-cartpanel,
.area-cart {

    min-width: 0;
    min-height: 0;

}

.area-kategorien { grid-area: kategorien; }
.area-form       { grid-area: form; }
.area-canvas     { grid-area: canvas; }
.area-deko       { grid-area: deko; }
.area-bottom     { grid-area: bottom; }
.area-cartpanel  { grid-area: cartpanel; }
.area-cart       { grid-area: cart; }


/* Spalten, die zu lang werden können, scrollen intern */
.area-form,
.area-deko {

    overflow-y: auto;

    scrollbar-width: thin;
    scrollbar-color: #d9b3bc transparent;

}

.area-form::-webkit-scrollbar,
.area-deko::-webkit-scrollbar {
    width: 0.3rem;
}

.area-form::-webkit-scrollbar-thumb,
.area-deko::-webkit-scrollbar-thumb {

    background: #d9b3bc;

    border-radius: 1rem;

}


/* ========================================= */
/* KATEGORIEN */
/* ========================================= */

.categories {

    display: flex;

    flex-direction: column;

    gap: var(--gap);

}


/* ========================================= */
/* EFFEKT: füllt die Spalte bis zur          */
/* Unterkante der unteren Row                */
/* ========================================= */

.nail-selection-wrapper {

    position: relative;

    display: flex;

    flex: 1;

    /* darf schrumpfen, aber nicht kleiner als ~4 Einträge */
    min-height: 14rem;

}

/* EffektPanel selbst: volle Höhe */
.nail-selection-wrapper > * {

    height: 100%;

}

/* Liste nimmt den Rest ein und scrollt intern */
.nail-selection-wrapper :deep(.effekt-liste) {

    flex: 1;

    min-height: 0;

    max-height: none;

}


/* ========================================= */
/* FORM */
/* ========================================= */

.form-effekt-area {

    display: flex;

    flex-direction: column;

    gap: var(--gap);

}


/* ========================================= */
/* NAGEL CANVAS */
/* ========================================= */

.nail-canvas {

    display: flex;

    align-items: flex-start;

    justify-content: center;

    container-type: size;

}

.nail-canvas-inner {

    width: min(
        100cqw,
        100cqh * var(--hand-ratio)
    );

    aspect-ratio: var(--hand-ratio);

}


/* ========================================= */
/* VERZIERUNG */
/* ========================================= */

.design-area {

    display: flex;

    flex-direction: column;

    gap: var(--gap);

}


/* ========================================= */
/* UNTERE ROW: FARBE + DESIGNS NEBENEINANDER */
/* ========================================= */

.bottom-row {

    display: flex;

    gap: var(--gap);

    height: 100%;

    /*
    | Inhalt der Row zählt NICHT für die Spaltenbreite.
    | Die Row übernimmt nur die Breite der Spalten darüber.
    */
    contain: inline-size;

    overflow: hidden;

}

/*
| Farbe bekommt etwas mehr Platz als Designs.
| Ist nur eins von beiden offen, nimmt es die ganze Breite.
*/
.color-section {

    flex: 1.3 1 0;

}

.design-section {

    flex: 1 1 0;

}

.color-section,
.design-section {

    display: flex;

    min-width: 0;

    overflow: hidden;

}

/* Panels füllen ihren Bereich komplett aus */
.color-section > *,
.design-section > * {

    flex: 1;

    width: 100%;
    max-width: none;

    height: 100%;

    box-sizing: border-box;

}


/* ========================================= */
/* WARENKORB-PANEL (oben, neben Verzierung) */
/* ========================================= */

.cart-dropdown {

    align-self: start;

    max-height: 100%;

    overflow-y: auto;

    scrollbar-width: thin;
    scrollbar-color: #d9b3bc transparent;

}

.cart-dropdown > * {

    width: 100%;

    max-width: none;

    box-sizing: border-box;

}

/* Kurzes Einblenden */
.cart-enter-active,
.cart-leave-active {

    transition:
        opacity 0.15s ease,
        transform 0.15s ease;

}

.cart-enter-from,
.cart-leave-to {

    opacity: 0;

    transform: translateY(0.4rem);

}


/* ========================================= */
/* WARENKORB-ZELLE (unten rechts) */
/* ========================================= */

.cart-cell {

    display: flex;

    align-items: flex-end;

}


/* ========================================= */
/* WARENKORB BUTTON */
/* ========================================= */

.cart-toggle {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 0.5rem;

    /* so breit wie das Panel darüber */
    width: 100%;

    min-height: var(--cart-row-h);

    padding: 0.65rem 1rem;

    border: none;

    border-radius: 0.7rem;

    background: #7d1024;

    color: #ffffff;

    white-space: nowrap;

    cursor: pointer;

    box-shadow: 0 0.3vw 1vw rgba(0, 0, 0, 0.15);

    transition:
        background-color 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;

}

.cart-toggle:hover {

    background: #64101f;

    transform: translateY(-0.1rem);

    box-shadow: 0 0.5vw 1.2vw rgba(125, 16, 36, 0.2);

}

.cart-toggle:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 2px;

}


/* ========================================= */
/* WARENKORB ICON / TEXT / ANZAHL */
/* ========================================= */

.cart-icon {

    font-size: 1.1rem;

    line-height: 1;

}

.cart-label {

    font-size: clamp(0.7rem, 0.8vw, 0.9rem);

    font-weight: 500;

}

.cart-badge {

    display: flex;

    align-items: center;

    justify-content: center;

    min-width: 1.35rem;

    height: 1.35rem;

    padding: 0 0.25rem;

    border-radius: 50%;

    background: #ffffff;

    color: #7d1024;

    font-size: 0.65rem;

    font-weight: 600;

}


/* ========================================= */
/* TABLET: Seite darf scrollen,              */
/* Warenkorb schwebt unten rechts            */
/* ========================================= */

@media (max-width: 1200px) {

    .nail-designer {

        height: auto;

        /* Wichtig: ohne feste Höhe darf kein Size-Container aktiv sein */
        container-type: normal;

    }

    .editor-row {

        grid-template-columns:
            auto
            auto
            minmax(0, 1fr);

        grid-template-rows: auto;

        grid-template-areas:
            "kategorien form   canvas"
            "bottom     bottom bottom"
            "deko       deko   deko";

        justify-content: normal;

        height: auto;

    }

    .nail-canvas {

        height: 60vh;

        align-items: center;

    }

    .bottom-row {

        height: 11rem;

    }

    /* Effekt wieder normale Höhe */
    .nail-selection-wrapper {

        flex: none;

        min-height: 0;

    }

    .nail-selection-wrapper > * {

        height: auto;

    }

    .nail-selection-wrapper :deep(.effekt-liste) {

        max-height: 22rem;

    }

    /* Warenkorb schwebend */
    .cart-cell {

        position: fixed;

        right: 1rem;

        bottom: 1rem;

        z-index: 1000;

    }

    .cart-toggle {

        width: auto;

    }

    .cart-dropdown {

        position: fixed;

        right: 1rem;

        bottom: 4.8rem;

        z-index: 999;

        width: min(18rem, calc(100vw - 2rem));

        max-height: calc(100vh - 7rem);

    }

}


/* ========================================= */
/* MOBILE */
/* ========================================= */

@media (max-width: 768px) {

    .editor-row {

        grid-template-columns: minmax(0, 1fr);

        grid-template-areas:
            "canvas"
            "kategorien"
            "form"
            "bottom"
            "deko";

        padding: 0;

    }

    .nail-canvas {

        height: 50vh;

    }

    .area-form,
    .area-deko {

        overflow: visible;

    }

    /* Farbe und Designs untereinander */
    .bottom-row {

        flex-direction: column;

        height: auto;

    }

    .color-section,
    .design-section {

        flex: none;

        height: 11rem;

    }

}


/* ========================================= */
/* REDUCED MOTION */
/* ========================================= */

@media (prefers-reduced-motion: reduce) {

    .cart-toggle,
    .cart-enter-active,
    .cart-leave-active {

        transition: none;

    }

    .cart-toggle:hover {

        transform: none;

    }

}

</style>