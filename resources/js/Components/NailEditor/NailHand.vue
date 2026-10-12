<template>
    <!--
    | hand-stage = Hand + fester Freiraum oben für lange Nägel.
    | Der Freiraum ist IMMER da, damit die Hand beim Umschalten
    | der Länge nicht springt.
    -->
    <div class="hand-stage">

        <div
            class="hand-container"
            :class="{ 'is-tall': isTall }"
        >

            <img
                src="/images/nail-editor/Hand.png"
                alt="Hand"
                class="hand-image"
            />

            <img
                v-for="finger in fingers"
                :key="finger.key"
                :src="`${basePath}/${finger.key}.png`"
                :alt="finger.alt"
                :class="['nail', `nail-${finger.key}`]"
            />

        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue'

// shape:  almond | square | oval | coffin | squoval | stiletto
// length: short | medium | long
const props = defineProps({
    shape:  { type: String, default: 'almond' },
    length: { type: String, default: 'medium' },
})

// Formen, die nur EINEN Bildsatz haben (unabhängig von der Länge).
// Stiletto gibt es nur lang -> Ordner "stiletto-right hand"
const SINGLE_SET = ['stiletto']

// Ordnernamen:
//   almond  + short  -> almond-short-right hand
//   almond  + medium -> almond-right hand
//   stiletto         -> stiletto-right hand
const basePath = computed(() => {

    let folder

    if (SINGLE_SET.includes(props.shape) || props.length === 'medium') {

        folder = `${props.shape}-right hand`

    } else {

        folder = `${props.shape}-${props.length}-right hand`

    }

    return `/images/nail-editor/${folder}`

})

// Lange Nägel / Stiletto haben eine höhere Leinwand (1080 x 1556).
// Dafür gelten unten andere "top"-Werte (Klasse .is-tall).
const isTall = computed(() =>
    props.length === 'long' || props.shape === 'stiletto'
)

const fingers = [
    { key: 'thumb',  alt: 'Daumen Nagel' },
    { key: 'index',  alt: 'Zeigefinger Nagel' },
    { key: 'middle', alt: 'Mittelfinger Nagel' },
    { key: 'ring',   alt: 'Ringfinger Nagel' },
    { key: 'pinky',  alt: 'Kleiner Finger Nagel' },
]
</script>

<style scoped>

/* =========================================
   STAGE
   Füllt die Breite des Elternelements.
   padding-top in % bezieht sich auf die BREITE
   -> der Freiraum skaliert immer mit der Hand mit.
   Gesamtverhältnis: 1080 / 1186
   ========================================= */

.hand-stage {
    width: 100%;
    padding-top: 12%;
    box-sizing: border-box;
}


/* =========================================
   HAND
   ========================================= */

.hand-container {
    position: relative;
    width: 100%;
    aspect-ratio: 1080 / 1056;
    display: block;
}

.hand-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.nail {
    position: absolute;
}

/* Daumen */
.nail-thumb {
    width: 21.05%;
    left: 5.19%;
    top: 40.34%;
    transform: rotate(-0.67deg);
}

/* Zeigefinger */
.nail-index {
    width: 22.31%;
    left: 24.54%;
    top: 2.94%;
    transform: rotate(0deg);
}

/* Mittelfinger */
.nail-middle {
    width: 24.44%;
    left: 37.5%;
    top: -5.59%;
    transform: rotate(0deg);
}

/* Ringfinger */
.nail-ring {
    width: 23.52%;
    left: 51.30%;
    top: 2.94%;
    transform: rotate(0deg);
}

/* Kleiner Finger */
.nail-pinky {
    width: 16.91%;
    left: 69.87%;
    top: 21.91%;
    transform: rotate(4.14deg);
}


/* =========================================
   LANG / STILETTO (höhere Leinwand)
   Breite, left und Rotation bleiben gleich,
   nur "top" ist nach oben verschoben.
   ========================================= */

.is-tall .nail-index  { top: -2.34%; }

.is-tall .nail-middle { top: -11.38%; }

.is-tall .nail-ring   { top: -2.63%; }

.is-tall .nail-pinky  { top: 17.91%; }

</style>