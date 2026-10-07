<script setup>
import { ref, reactive } from 'vue'

const props = defineProps({
    nailShapes: {
        type: Array,
        default: () => [],
    },
    colors: {
        type: Array,
        default: () => [],
    },
    designElements: {
        type: Array,
        default: () => [],
    },
})

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
import { useCart } from '@/Stores/cart.js'

// Kategorien
const selectedCategories = ref([])
const showCheckout = ref(false)
const { addToCart } = useCart()

// Aktuell ausgewählter Nagel
const selectedNail = ref(null)

// Zustand jedes einzelnen Nagels
const nailDesigns = reactive({
    'Daumen': {
        form: null,
        color: null,
        design: null,
        effekt: null,
    },
    'Zeigefinger': {
        form: null,
        color: null,
        design: null,
        effekt: null,
    },
    'Mittelfinger': {
        form: null,
        color: null,
        design: null,
        effekt: null,
    },
    'Ringfinger': {
        form: null,
        color: null,
        design: null,
        effekt: null,
    },
    'Kleiner Finger': {
        form: null,
        color: null,
        design: null,
        effekt: null,
    },
})

// Kategorie auswählen
function selectCategory(category) {
    if (selectedCategories.value.includes(category)) {
        selectedCategories.value = selectedCategories.value.filter(
            item => item !== category
        )
    } else {
        selectedCategories.value.push(category)
    }
}

// Nagel auswählen
function selectNail(nail) {
    selectedNail.value = nail

    console.log('Ausgewählter Nagel:', nail)
}

// Form auswählen
function selectForm(form) {
    if (!selectedNail.value) {
        return
    }

    nailDesigns[selectedNail.value].form = form

    console.log(
        'Form ausgewählt:',
        selectedNail.value,
        form
    )
}

// Farbe auswählen
function selectColor(color) {
    if (!selectedNail.value) {
        return
    }

    nailDesigns[selectedNail.value].color = color

    console.log(
        'Farbe ausgewählt:',
        selectedNail.value,
        color
    )
}

// Design auswählen
function selectDesign(design) {
    if (!selectedNail.value) {
        return
    }

    nailDesigns[selectedNail.value].design = design

    console.log(
        'Design ausgewählt:',
        selectedNail.value,
        design
    )
}

// Effekt auswählen
function selectEffekt(effekt) {
    if (!selectedNail.value) {
        return
    }

    nailDesigns[selectedNail.value].effekt = effekt

    console.log(
        'Effekt ausgewählt:',
        selectedNail.value,
        effekt
    )
}

// Design in den Warenkorb legen
function addDesignToCart() {
    let price = 0

    Object.values(nailDesigns).forEach(nail => {
        // Farbe
        if (nail.color) {
            price += nail.color.price ?? 0
        }

        // Design
        if (nail.design) {
            price += nail.design.price ?? 0
        }

        // Effekt
        if (nail.effekt) {
            price += nail.effekt.price ?? 0
        }
    })

    addToCart({
        name: 'Nageldesign',
        price: price,
        image: '/images/nail-editor/Hand.png',
        nailDesigns: JSON.parse(JSON.stringify(nailDesigns)),
    })

    console.log('Design zum Warenkorb hinzugefügt:', nailDesigns)
    console.log('Gesamtpreis:', price)
}

// Linke Hand
function selectLeftHand() {
    console.log('Linke Hand ausgewählt')
}
</script>

<template>
    <pagesLayout>
        <main class="nail-designer">
            <div class="editor-row">

                <div class="left-editor">

                    <div class="category-row">
                        <section class="categories">
                            <CategoryPanel
                                @category-selected="selectCategory"
                            />
                        </section>

                        <FormPanel
                            v-if="selectedCategories.includes('Form')"
                            :nail-shapes="props.nailShapes"
                            @form-selected="selectForm"
                        />
                    </div>

                    <ColorPanel
                            v-if="selectedCategories.includes('Farbe')"
                            :colors="props.colors"
                            @color-selected="selectColor"
                    />

                    <NailSelectionPanel
                        @nail-selected="selectNail"
                        @left-hand-selected="selectLeftHand"
                    />

                </div>

                <section class="nail-canvas">
                    <NailCanvas
                        :nail-designs="nailDesigns"
                        :selected-nail="selectedNail"
                    />
                </section>

                <div class="design-area">

                    <DesignPanel
                        v-if="selectedCategories.includes('Designs')"
                        :design-elements="props.designElements"
                        @design-selected="selectDesign"
                    />

                    <EffektPanel
                        v-if="selectedCategories.includes('Effekte')"
                        @effekt-selected="selectEffekt"
                    />

            </div>

                <div class="Warenkorb">
                    <button
                    class="add-to-cart-button"
                    @click="addDesignToCart"
                >
                    Design zum Warenkorb hinzufügen
                    </button>

                <CartPanel @checkout="showCheckout = true" />
                </div>

            </div>
        </main>

        <Checkout
            v-if="showCheckout"
            @close="showCheckout = false"
        />
    </pagesLayout>
</template>

<style scoped>
.nail-designer {
    display: flex;
    flex-direction: column;
    width: 100%;
    min-height: 100%;
    gap: 20px;
}

.editor-row {
    display: flex;
    align-items: flex-start;
    width: 100%;
    gap: 20px;
}

.left-editor {
    display: flex;
    flex-direction: column;
    width: fit-content;
    gap: 20px;
    flex-shrink: 0;
}

.category-row {
    display: flex;
    align-items: flex-start;
    width: fit-content;
    gap: 20px;
}

.categories {
    width: fit-content;
    flex-shrink: 0;
}

.nail-canvas {
    width: 35vw;
    aspect-ratio: 1080 / 1056;
    flex-shrink: 0;
}

.design-area {
    display: flex;
    flex-direction: column;
    width: fit-content;
    gap: 20px;
    flex-shrink: 0;
}

@media (max-width: 1200px) {
    .editor-row {
        flex-wrap: wrap;
    }

    .nail-canvas {
        width: 50vw;
    }
}

@media (max-width: 768px) {
    .editor-row {
        flex-direction: column;
    }

    .nail-canvas {
        width: 90vw;
    }

    .category-row {
        flex-direction: column;
    }
}
</style>