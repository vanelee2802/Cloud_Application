<script setup>

import { ref, computed, onMounted } from 'vue'
import { buildLayers } from '@/Components/NailEditor/patternGenerators'

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
import DesignSavePanel from '@/Components/NailEditor/DesignSavePanel.vue'
import TemplateModal from '@/Components/NailEditor/TemplateModal.vue'

const props = defineProps({
    nailShapes: { type: Array, default: () => [] },
    colors: { type: Array, default: () => [] },
    designElements: { type: Array, default: () => [] },
    designTemplates: { type: Array, default: () => [] },
})

// =========================================
// KATEGORIEN UND NAGELAUSWAHL
// =========================================

const FINGERS = ['thumb', 'index', 'middle', 'ring', 'pinky']

const selectedCategories = ref([])
const selectedFingers = ref([])

const nailDesigns = ref({
    thumb:  { form: null, color: null, design: null, effekt: null },
    index:  { form: null, color: null, design: null, effekt: null },
    middle: { form: null, color: null, design: null, effekt: null },
    ring:   { form: null, color: null, design: null, effekt: null },
    pinky:  { form: null, color: null, design: null, effekt: null },
})

function toggleFinger(fingerKey) {
    if (selectedFingers.value.includes(fingerKey)) {
        selectedFingers.value = selectedFingers.value.filter(
            finger => finger !== fingerKey
        )
    } else {
        selectedFingers.value.push(fingerKey)
    }
}

/**
 * Auf welche Nägel wirkt eine Auswahl?
 * - Nägel ausgewählt → nur diese
 * - keiner ausgewählt → alle
 */
function targetFingers() {
    return selectedFingers.value.length > 0
        ? [...selectedFingers.value]
        : [...FINGERS]
}

function sameHex(a, b) {
    return Boolean(a && b) && a.toLowerCase() === b.toLowerCase()
}


// =========================================
// NAGELFORM + LÄNGE
// =========================================

// shape  = Form (id aus dem FormPanel = Ordnername)
// length = Länge: short | medium | long
const shape  = ref('almond')
const length = ref('medium')

const shapeNameMap = {
    almond: 'Almond',
    square: 'Square',
    oval: 'Round',
    coffin: 'Coffin',
    stiletto: 'Stiletto',
}

function getShapeId() {
    const shapeName = shapeNameMap[shape.value]

    if (!shapeName) {
        return null
    }

    const nailShape = props.nailShapes.find(
        item => item.name === shapeName
    )

    return nailShape?.id ?? null
}

const fingerPositionMap = {
    thumb: 1,
    index: 2,
    middle: 3,
    ring: 4,
    pinky: 5,
}

// ID des Musters auf einem Nagel (für das Speichern)
function getElementId(nailData) {
    return nailData?.design?.id ?? null
}



// Alle Nagelbilder vorladen, damit beim Umschalten nichts flackert
const FOLDERS = [
    ...['almond', 'square', 'oval', 'coffin', 'ballerina', 'squoval'].map(s => `${s}-right hand`),
    ...['almond', 'square', 'oval'].map(s => `${s}-short-right hand`),
    ...['almond', 'square', 'coffin', 'ballerina', 'oval'].map(s => `${s}-long-right hand`),
    'stiletto-right hand'
]

onMounted(() => {

    for (const folder of FOLDERS) {

        for (const finger of FINGERS) {

            const img = new Image()

            img.src = `/images/nail-editor/FormPanel/forms_right hand/${folder}/${finger}.png`
        }

    }

})


// =========================================
// CHECKOUT
// =========================================

const showCheckout = ref(false)


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
// FARBE
// Ziel der Farbauswahl:
//   colorTarget = null  → Grundfarbe des Nagels
//   colorTarget = 0, 1… → Farbschicht des Musters (z. B. Spitze)
// =========================================

const selectedColor = ref(null)

const colorTarget = ref(null)

const designPanel = ref(null)


function selectColor(color) {

    // Ziel ist eine Schicht: Farbe an das DesignPanel geben
    if (colorTarget.value !== null) {
        designPanel.value?.setLayerColor(colorTarget.value, color.hex_code)
        return
    }

    const fingers = targetFingers()

    // Haben alle Ziel-Nägel schon diese Farbe? → Zweiter Klick hebt sie auf
    const allHaveIt = fingers.every(finger =>
        sameHex(nailDesigns.value[finger].color?.hex_code, color.hex_code)
    )

    const newColor = allHaveIt ? null : color

    fingers.forEach(finger => {
        nailDesigns.value[finger].color = newColor
    })

    selectedColor.value = newColor
}


// Klick auf "● Spitze" im DesignPanel
function onLayerTarget(index) {

    colorTarget.value = index

    // Farbauswahl automatisch einblenden, damit man direkt färben kann
    if (index !== null && !selectedCategories.value.includes('Farbe')) {
        selectedCategories.value.push('Farbe')
    }

}


// Welche Farbe im ColorPanel markiert ist
const activeHex = computed(() => {

    if (colorTarget.value !== null) {
        return selectedDesign.value?.layers?.[colorTarget.value]?.color ?? null
    }

    // Farbe des ersten ausgewählten Nagels, sonst die zuletzt gewählte
    const firstFinger = selectedFingers.value[0]

    if (firstFinger) {
        return nailDesigns.value[firstFinger].color?.hex_code ?? null
    }

    return selectedColor.value?.hex_code ?? null

})

// Hinweis im ColorPanel, z. B. "FARBE · Spitze"
const targetLabel = computed(() =>
    colorTarget.value !== null
        ? selectedDesign.value?.layers?.[colorTarget.value]?.name ?? ''
        : ''
)


// =========================================
// DESIGN / MUSTER
// =========================================

// Zuletzt gewähltes Design (ganzes Objekt aus der Datenbank)
const selectedDesign = ref(null)

/**
 * Das DesignPanel schickt:
 * - ein Muster      → beim Auswählen und beim Umfärben einer Schicht
 * - null            → beim Abwählen (zweiter Klick)
 * Das Ergebnis geht auf die ausgewählten Nägel, sonst auf alle.
 */
function selectDesign(design) {

    targetFingers().forEach(finger => {
        nailDesigns.value[finger].design = design
    })

    selectedDesign.value = design
}

// Preis: Summe der Muster-Preise aller Nägel
const totalDesignPrice = computed(() => {
    return Object.values(nailDesigns.value).reduce((total, nail) => {
        return total + Number(nail.design?.price_per_nail ?? 0)
    }, 0)
})

function selectEffekt(effekt) {

    console.log('Ausgewählter Effekt:', effekt)

}

function selectVerziehrung(verziehrung) {

    console.log('Ausgewählte Verzierung:', verziehrung)

}


// =========================================
// VORLAGEN (vorgefertigte Designs)
// =========================================

const showTemplates    = ref(false)
const showSavePanel    = ref(false)
const activeTemplateId = ref(null)

function findColor(name) {
    return props.colors.find(color => color.name === name) ?? null
}

// Muster aus der Datenbank; generierte Muster bekommen ihre Farbschichten
function findDesign(name) {
    const design = props.designElements.find(element => element.name === name)

    if (!design) {
        return null
    }

    return design.generator
        ? { ...design, layers: buildLayers(design.generator, design.params ?? {}) }
        : design
}

// Was liegt laut Vorlage auf diesem Nagel? ("alle" + Finger-Angabe)
function templateNail(template, finger) {
    return {
        ...(template.nails?.alle ?? {}),
        ...(template.nails?.[finger] ?? {}),
    }
}

// Vorlagen mit Vorschau (Farbe / Musterbild je Nagel) und Preis fürs Pop-up
const templateList = computed(() =>
    props.designTemplates.map(template => {

        const nails = FINGERS.map(finger => {
            const spec   = templateNail(template, finger)
            const design = spec.design ? findDesign(spec.design) : null

            return {
                hex:   spec.color ? findColor(spec.color)?.hex_code ?? null : null,
                image: design?.image_path ?? null,
                price: Number(design?.price_per_nail ?? 0),
            }
        })

        return {
            ...template,
            preview: nails,
            price:   nails.reduce((sum, nail) => sum + nail.price, 0),
        }
    })
)

// Vorlage auf die Hand legen (alle Nägel) und Pop-up schließen
function applyTemplate(template) {
    FINGERS.forEach(finger => {
        const spec = templateNail(template, finger)

        nailDesigns.value[finger].color  = spec.color  ? findColor(spec.color)   : null
        nailDesigns.value[finger].design = spec.design ? findDesign(spec.design) : null
    })

    selectedFingers.value  = []
    selectedColor.value    = null
    selectedDesign.value   = null
    colorTarget.value      = null
    activeTemplateId.value = template.id
    designName.value       = template.name
    showTemplates.value    = false
}


// =========================================
// DESIGN SPEICHERN
// =========================================

const designName = ref('Mein Nageldesign')
const isSaving = ref(false)
const saveMessage = ref('')


async function saveDesign() {
    saveMessage.value = ''

    // Alle gestalteten Nägel berücksichtigen, nicht nur ausgewählte.
    const designedFingers = Object.keys(nailDesigns.value).filter(
        finger => nailDesigns.value[finger]?.color?.id
    )

    if (designedFingers.length === 0) {
        saveMessage.value = 'Bitte gestalte mindestens einen Nagel mit einer Farbe.'
        return
    }

    const nailShapeId = getShapeId()

    if (!nailShapeId) {
        saveMessage.value =
            'Diese Nagelform ist noch nicht in der Datenbank vorhanden.'
        return
    }

    const nails = []

    for (const finger of designedFingers) {
        const nailData = nailDesigns.value[finger]
        const position = fingerPositionMap[finger]

        if (!position) {
            saveMessage.value = `Die Nagelposition "${finger}" ist ungültig.`
            return
        }

        const elementId = getElementId(nailData)

        nails.push({
            nail_position: position,
            nail_shape_id: nailShapeId,
            color_id: nailData.color.id,
            element_ids: elementId ? [elementId] : [],
        })
    }

    isSaving.value = true

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content')

        const response = await fetch('/designs', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: JSON.stringify({
                name: designName.value,
                nails,
            }),
        })

        const result = await response.json()

        if (!response.ok) {
            if (response.status === 422 && result.errors) {
                saveMessage.value = Object.values(result.errors)
                    .flat()
                    .join(' ')
            } else if (response.status === 401 || response.status === 403) {
                saveMessage.value =
                    'Bitte melde dich an, um dein Design zu speichern.'
            } else {
                saveMessage.value =
                    result.message || 'Das Design konnte nicht gespeichert werden.'
            }

            return
        }

        saveMessage.value =
            `Design erfolgreich gespeichert! (ID: ${result.id})`
    } catch (error) {
        console.error('Fehler beim Speichern:', error)
        saveMessage.value =
            'Server nicht erreichbar. Bitte versuche es erneut.'
    } finally {
        isSaving.value = false
    }
}

</script>


<template>

    <pagesLayout>


        <!-- ========================================= -->
        <!-- HAUPTBEREICH -->
        <!-- ========================================= -->

        <main class="nail-designer">


            <!-- ======================================================= -->
            <!-- EDITOR (GRID)                                           -->
            <!--                                                         -->
            <!--  Kategorien | Form  | Hand    | Verzierung | Warenkorb   -->
            <!--  Kategorien | Farbe | Designs ──────────── | Warenkorb   -->
            <!-- ======================================================= -->

            <div class="editor-row">


                <!-- ========================================= -->
                <!-- SPALTE 1: KATEGORIEN + EFFEKT -->
                <!-- ========================================= -->

                <section class="categories area-kategorien">

                    <CategoryPanel
                        @category-selected="selectCategory"
                    />

                    <div
                        v-if="selectedCategories.includes('Effekte')"
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
                    v-if="selectedCategories.includes('Form')"
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
                            :selected-fingers="selectedFingers"
                            :nail-designs="nailDesigns"
                            :color="selectedColor?.hex_code"
                            :pattern="selectedDesign?.image_path"
                            :layers="selectedDesign?.layers ?? []"
                            @toggle-finger="toggleFinger"
                        />

                    </div>

                </section>


                <!-- ========================================= -->
                <!-- SPALTE 4: VERZIERUNG + MEIN DESIGN -->
                <!-- ========================================= -->

                                  <div class="design-area area-deko">

                    <CharmPanel
                        v-if="selectedCategories.includes('Verzierung')"
                        @verziehrung-selected="selectVerziehrung"
                    />

                    <!-- Immer ganz unten in der Spalte -->
                    <div class="save-slot">

                        <button
                            v-if="!showSavePanel"
                            type="button"
                            class="open-save-button"
                            @click="showSavePanel = true"
                        >
                            Design speichern
                        </button>

                        <DesignSavePanel
                            v-else
                            v-model="designName"
                            :nail-count="selectedFingers.length"
                            :price="totalDesignPrice"
                            :saving="isSaving"
                            :message="saveMessage"
                            @save="saveDesign"
                            @close="showSavePanel = false"
                        />

                    </div>

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
                        v-if="selectedCategories.includes('Farbe')"
                        class="color-section"
                    >

                        <ColorPanel
                            :colors="props.colors"
                            :active-hex="activeHex"
                            :target-label="targetLabel"
                            @color-selected="selectColor"
                        />

                    </div>

                    <div
                        v-if="selectedCategories.includes('Designs')"
                        class="design-section"
                    >

                        <DesignPanel
                            ref="designPanel"
                            :active-layer="colorTarget"
                            @layer-target="onLayerTarget"
                            @design-selected="selectDesign"
                            @open-templates="showTemplates = true"
                        />

                    </div>

                </div>


                <!-- ========================================= -->
                <!-- SPALTE 5: WARENKORB                        -->
                <!-- immer sichtbar, oben auf Höhe der Verzierung -->
                <!-- ========================================= -->

                             <!-- ========================================= -->
                <!-- SPALTE 5: WARENKORB + MEIN DESIGN          -->
                <!-- ========================================= -->

                <aside class="cart-area area-cart">

                    <CartPanel
                        :nail-designs="nailDesigns"
                        :design-price="totalDesignPrice"
                        @checkout="showCheckout = true"
                    />

                  

                </aside>
                


            </div>


        </main>


        <!-- ========================================= -->
        <!-- CHECKOUT -->
        <!-- ========================================= -->

        <Checkout
            v-if="showCheckout"
            @close="showCheckout = false"
        />


        <!-- ========================================= -->
        <!-- VORLAGEN ENTDECKEN (Pop-up) -->
        <!-- ========================================= -->

        <TemplateModal
            v-if="showTemplates"
            :templates="templateList"
            :active-template-id="activeTemplateId"
            @apply="applyTemplate"
            @close="showTemplates = false"
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

    /* aktuelle Höhe der unteren Row (0, solange Farbe/Designs zu sind) */
    --bottom-h: 0px;

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
        auto                                                  /* Verzierung + Mein Design */
        var(--cart-w);                                        /* Warenkorb */

    grid-template-rows:
        minmax(0, 1fr)
        var(--bottom-h);

    /* Warenkorb oben rechts, über beide Zeilen */
    grid-template-areas:
        "kategorien form   canvas deko   cart"
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
.area-cart {

    min-width: 0;
    min-height: 0;

}

.area-kategorien { grid-area: kategorien; }
.area-form       { grid-area: form; }
.area-canvas     { grid-area: canvas; }
.area-deko       { grid-area: deko; }
.area-bottom     { grid-area: bottom; }
.area-cart       { grid-area: cart; }


/* Spalten, die zu lang werden können, scrollen intern */
.area-form,
.area-deko,
.area-cart {

    overflow-y: auto;

    scrollbar-width: thin;
    scrollbar-color: #d9b3bc transparent;

}

.area-form::-webkit-scrollbar,
.area-deko::-webkit-scrollbar,
.area-cart::-webkit-scrollbar {
    width: 0.3rem;
}

.area-form::-webkit-scrollbar-thumb,
.area-deko::-webkit-scrollbar-thumb,
.area-cart::-webkit-scrollbar-thumb {

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
/* VERZIERUNG + MEIN DESIGN */
/* ========================================= */
.design-area {

    display: flex;

    flex-direction: column;

    gap: var(--gap);

    height: 100%;

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
/* WARENKORB (oben rechts, immer sichtbar)    */
/* ========================================= */

.cart-area {

    display: flex;

    flex-direction: column;

    /* Abstand zwischen Warenkorb und Mein Design */
    gap: var(--gap);

    align-self: start;

    /* Keine Höhenbegrenzung, keine Scrollbar */
    overflow: visible;

}

/* Warenkorb und Mein Design gleich breit wie die Spalte */
.cart-area > * {

    width: 100%;

    max-width: none;

    box-sizing: border-box;

}



/* ========================================= */
/* TABLET: Seite darf scrollen               */
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
            "deko       deko   cart";

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

    .area-deko,
    .area-cart {

        overflow: visible;

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
            "deko"
            "cart";

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
/* Speichern-Bereich ans untere Ende der Spalte schieben */
.save-slot {

    margin-top: auto;

}
/* ========================================= */
/* BUTTON "DESIGN SPEICHERN" (unter Verzierung) */
/* ========================================= */

.open-save-button {

    width: 100%;

    padding: 0.65rem 1rem;

    border: none;

    border-radius: 0.7rem;

    background: #7d1024;

    color: #ffffff;

    font: inherit;

    font-size: clamp(0.75rem, 0.85vw, 0.9rem);

    font-weight: 500;

    cursor: pointer;

    box-shadow: 0 0.3vw 1vw rgba(0, 0, 0, 0.12);

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;

}

.open-save-button:hover {

    background: #64101f;

    transform: translateY(-0.1rem);

}

.open-save-button:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 2px;

}
</style>