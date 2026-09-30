<script setup>
import { ref } from 'vue'

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

// Kategorien
const selectedCategories = ref([])
const showCheckout = ref(false)

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

// Farbe auswählen
function selectColor(color) {
    console.log('Ausgewählte Farbe:', color)
}
</script>


<template>

    <!-- LAYOUT + NAVBAR -->
    <pagesLayout>

        <main class="nail-designer">

            <!-- ================================= -->
            <!-- EDITOR -->
            <!-- ================================= -->

            <div class="editor-row">

                <!-- ================================= -->
                <!-- LINKER BEREICH -->
                <!-- ================================= -->

                <div class="left-editor">

                    <!-- KATEGORIEN + FORM -->
                    <div class="category-row">

                        <!-- KATEGORIEN -->
                        <section class="categories">

                            <CategoryPanel
                                @category-selected="selectCategory"
                            />

                        </section>


                        <!-- FORM -->
                        <FormPanel
                            v-if="selectedCategories.includes('Form')"
                        />

                    </div>


                    <!-- FARBE -->
                    <ColorPanel
                        v-if="selectedCategories.includes('Farbe')"
                        @color-selected="selectColor"
                    />


                    <!-- NÄGEL AUSWÄHLEN -->
                    <NailSelectionPanel />

                </div>


                <!-- ================================= -->
                <!-- HAND / NAIL CANVAS -->
                <!-- ================================= -->

                <section class="nail-canvas">

                    <NailCanvas />

                </section>


                <!-- ================================= -->
                <!-- DESIGNS / EFFEKTE -->
                <!-- ================================= -->

                <div class="design-area">

                    <!-- DESIGNS -->
                    <DesignPanel
                        v-if="selectedCategories.includes('Designs')"
                    />


                    <!-- EFFEKTE -->
                    <EffektPanel
                        v-if="selectedCategories.includes('Effekte')"
                    />

                </div>


                <!-- ================================= -->
                <!-- WARENKORB -->
                <!-- ================================= -->

                <div class="Warenkorb">

                    <CartPanel @checkout="showCheckout = true" />

                </div>

            </div>

        </main>


        <!-- CHECKOUT POPUP -->
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
    display: flex;
    flex-direction: column;

    width: 100%;
    min-height: 100%;

    gap: 20px;
}


/* ========================================= */
/* EDITOR */
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
}


/* ========================================= */
/* KATEGORIEN + FORM */
/* ========================================= */

.category-row {
    display: flex;
    align-items: flex-start;

    width: fit-content;

    gap: 20px;
}


/* ========================================= */
/* KATEGORIEN */
/* ========================================= */

.categories {
    width: fit-content;

    flex-shrink: 0;
}


/* ========================================= */
/* HAND / CANVAS */
/* ========================================= */

.nail-canvas {
    width: 35vw;

    aspect-ratio: 1080 / 1056;

    flex-shrink: 0;
}


/* ========================================= */
/* DESIGNS / EFFEKTE */
/* ========================================= */

.design-area {
    display: flex;
    flex-direction: column;

    width: fit-content;

    gap: 20px;

    flex-shrink: 0;
}


/* ========================================= */
/* RESPONSIVE */
/* ========================================= */

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