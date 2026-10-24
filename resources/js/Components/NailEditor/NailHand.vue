<template>
    <!--
    | hand-stage = Hand + fester Freiraum oben für lange Nägel.
    -->
    <div class="hand-stage">

        <div
            class="hand-container"
            :class="{ 'is-tall': isTall }"
        >

            <img
                src="/images/nail-editor/nail-editor/Hand.png"
                alt="Hand"
                class="hand-image"
            />

            <!--
            | Pro Finger ein Wrapper. Position, Größe und Drehung
            | sitzen auf dem Wrapper, alle Schichten darin liegen
            | deckungsgleich übereinander.
            -->
            <span
                v-for="finger in fingers"
                :key="finger.key"
                :class="['nail', `nail-${finger.key}`, {
                    'nail-selected': selectedFingers.includes(finger.key)
                }]"
                role="button"
                tabindex="0"
                :aria-pressed="selectedFingers.includes(finger.key)"
                @click="toggleFinger(finger.key)"
                @keydown.enter.prevent="toggleFinger(finger.key)"
                @keydown.space.prevent="toggleFinger(finger.key)"
            >

                <!-- 1. Nagelbild als Basis -->
                <img
                    :src="nailSrc(finger.key)"
                    :alt="finger.alt"
                    class="nail-base"
                />

                <!-- 2. Grundfarbe -->
                <span
                    v-if="color"
                    class="nail-layer"
                    :style="colorStyle(finger.key)"
                ></span>

                <!-- 3. Muster -->
                <span
                    v-if="pattern"
                    class="nail-layer nail-pattern"
                    :style="patternStyle(finger.key)"
                ></span>

                <!-- 4. Umfärbbare Farbschichten -->
                <span
                    v-for="(layer, index) in layers"
                    :key="`${finger.key}-layer-${index}`"
                    class="nail-layer"
                    :style="layerStyle(finger.key, layer)"
                ></span>

                <!-- 5. Wölbung -->
                <img
                    v-if="hasPaint"
                    :src="nailSrc(finger.key)"
                    alt=""
                    aria-hidden="true"
                    class="nail-shine"
                />

                <!-- 6. Glanz -->
                <span
                    v-if="hasPaint"
                    class="nail-layer nail-gloss"
                    :style="maskStyle(finger.key)"
                ></span>
            </span>

        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    shape: { type: String, default: 'almond' },
    length: { type: String, default: 'medium' },
    selectedFingers: { type: Array, default: () => [] },
    color: { type: String, default: null },
    pattern: { type: String, default: null },
    layers: { type: Array, default: () => [] },
})

const emit = defineEmits(['toggle-finger'])

function toggleFinger(fingerKey) {
    emit('toggle-finger', fingerKey)
}

// Ist überhaupt etwas auf dem Nagel? Dann Wölbung + Glanz zeigen
const hasPaint = computed(() =>
    Boolean(props.color || props.pattern || props.layers.length)
)

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

    return `/images/nail-editor/FormPanel/forms_right hand/${folder}`
})

function nailSrc(finger) {

    return `${basePath.value}/${finger}.png`

}

// Maske: das Nagelbild selbst schneidet jede Schicht zu
function maskStyle(finger) {

    const mask = `url("${nailSrc(finger)}")`

    return {
        WebkitMaskImage: mask,
        maskImage:       mask,
    }

}

// Farb-Schicht
function colorStyle(finger) {

    return {
        ...maskStyle(finger),
        backgroundColor: props.color,
    }

}

// Muster-Schicht
function patternStyle(finger) {

    return {
        ...maskStyle(finger),
        backgroundImage: `url("${props.pattern}")`,
    }

}
// Farbschicht: Form aus der Schicht-Maske UND Nagelform, Farbe frei wählbar
function layerStyle(finger, layer) {

    const masks = `url("${layer.mask}"), url("${nailSrc(finger)}")`

    return {
        backgroundColor: layer.color,

        WebkitMaskImage:     masks,
        maskImage:           masks,

        WebkitMaskSize:      'cover, 100% 100%',
        maskSize:            'cover, 100% 100%',

        WebkitMaskPosition:  'center top, center',
        maskPosition:        'center top, center',

        WebkitMaskRepeat:    'no-repeat',
        maskRepeat:          'no-repeat',

        // nur dort sichtbar, wo BEIDE Masken etwas zeigen
        WebkitMaskComposite: 'source-in',
        maskComposite:       'intersect',
    }

}
// Lange Nägel / Stiletto haben eine höhere Leinwand (1080 x 1556)
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


/* =========================================
   NAGEL-WRAPPER
   ========================================= */

.nail {
    position: absolute;
    display: block;
}

.nail {
    cursor: pointer;
    transition: filter 0.2s ease, outline 0.2s ease;
}

.nail-selected {
    filter: drop-shadow(0 0 6px #d63384);
    outline: 2px solid #d63384;
    outline-offset: 2px;
}

/* 1. Nagel: gibt dem Wrapper seine Höhe */
.nail-base {
    display: block;
    width: 100%;
    height: auto;
}

/* 2. + 3. Farbe und Muster: exakt auf den Nagel zugeschnitten */
.nail-layer {
    position: absolute;
    inset: 0;
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
   ========================================= */

.is-tall .nail-index  { top: -2.34%; }

.is-tall .nail-middle { top: -11.38%; }

.is-tall .nail-ring   { top: -2.63%; }

.is-tall .nail-pinky  { top: 17.91%; }

</style>