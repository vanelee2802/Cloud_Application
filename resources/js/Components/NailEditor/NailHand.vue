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
            | Klick auf einen Nagel = auswählen / abwählen.
            | Jeder Nagel hat seine EIGENE Farbe und sein EIGENES Muster.
            -->
            <span
                v-for="finger in fingers"
                :key="finger.key"
                :class="['nail', `nail-${finger.key}`, {
                    'nail-selected': selectedFingers.includes(finger.key)
                }]"
                role="button"
                tabindex="0"
                :aria-label="finger.alt"
                :aria-pressed="selectedFingers.includes(finger.key)"
                @click="toggleFinger(finger.key)"
                @keydown.enter.prevent="toggleFinger(finger.key)"
                @keydown.space.prevent="toggleFinger(finger.key)"
            >

                <!-- 1. Nagel (bestimmt die Größe) -->
                <img
                    :src="nailSrc(finger.key)"
                    :alt="finger.alt"
                    class="nail-base"
                />

                <!-- 2. Farbe, mit dem Nagel als Maske zugeschnitten -->
                <span
                    v-if="fingerColor(finger.key)"
                    class="nail-layer"
                    :style="colorStyle(finger.key)"
                ></span>

                <!-- 3. Muster, mit dem Nagel als Maske zugeschnitten -->
                <span
                    v-if="fingerPattern(finger.key)"
                    class="nail-layer nail-pattern"
                    :style="patternStyle(finger.key)"
                ></span>

                <!-- 3b. Umfärbbare Farbschichten (z. B. French-Spitze, Karo-Linien) -->
                <span
                    v-for="(layer, index) in fingerLayers(finger.key)"
                    :key="`${finger.key}-layer-${index}`"
                    class="nail-layer"
                    :style="layerStyle(finger.key, layer)"
                ></span>

                <!-- 4. Wölbung vom Original-Nagel -->
                <img
                    v-if="hasPaint(finger.key)"
                    :src="nailSrc(finger.key)"
                    alt=""
                    aria-hidden="true"
                    class="nail-shine"
                />

                <!-- 5. Glanz (Standard-Finish, später durch Effekte ersetzbar) -->
                <span
                    v-if="hasPaint(finger.key)"
                    class="nail-layer nail-gloss"
                    :style="maskStyle(finger.key)"
                ></span>
            </span>

        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue'

// shape:   almond | square | oval | coffin | squoval | stiletto
// length:  short | medium | long
const props = defineProps({
    shape:   { type: String, default: 'almond' },
    length:  { type: String, default: 'medium' },

    // Nagelauswahl (Daniel): welche Nägel gerade ausgewählt sind
    selectedFingers: { type: Array, default: () => [] },

    /*
    | Farbe + Muster PRO NAGEL, z. B.
    | { thumb: { color: {...}, design: {...} }, index: {...}, ... }
    */
    nailDesigns: { type: Object, default: null },

    // Eine Farbe / ein Muster für alle Nägel –
    // wird nur genutzt, wenn nailDesigns nicht übergeben wird
    color:   { type: String, default: null },        // Hex-Code, z. B. "#B01C2E"
    pattern: { type: String, default: null },        // Bildpfad eines deckenden Musters
    layers:  { type: Array,  default: () => [] },    // [{ mask, color }, …] umfärbbares Muster
})

const emit = defineEmits(['toggle-finger'])

function toggleFinger(fingerKey) {
    emit('toggle-finger', fingerKey)
}


// =========================================
// WAS LIEGT AUF WELCHEM NAGEL?
// =========================================

const perNail = computed(() => Boolean(props.nailDesigns))

function fingerColor(finger) {

    return perNail.value
        ? props.nailDesigns[finger]?.color?.hex_code ?? null
        : props.color

}

function fingerPattern(finger) {

    return perNail.value
        ? props.nailDesigns[finger]?.design?.image_path ?? null
        : props.pattern

}

function fingerLayers(finger) {

    return perNail.value
        ? props.nailDesigns[finger]?.design?.layers ?? []
        : props.layers

}

// Ist auf DIESEM Nagel etwas? Dann Wölbung + Glanz zeigen
function hasPaint(finger) {

    return Boolean(
        fingerColor(finger) ||
        fingerPattern(finger) ||
        fingerLayers(finger).length
    )

}


// =========================================
// BILDPFADE
// =========================================

// Formen, die nur EINEN Bildsatz haben (unabhängig von der Länge)
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


// =========================================
// MASKING (unverändert aus der funktionierenden Version)
// =========================================

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
        backgroundColor: fingerColor(finger),
    }

}

// Muster-Schicht
function patternStyle(finger) {

    return {
        ...maskStyle(finger),
        backgroundImage: `url("${fingerPattern(finger)}")`,
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


/* =========================================
   NAGELAUSWAHL (Daniel)
   ========================================= */

.nail {
    cursor: pointer;
    transition: filter 0.2s ease, outline 0.2s ease;
}

.nail:focus {
    outline: none;
}

.nail:focus-visible {
    outline: 2px solid #d63384;
    outline-offset: 2px;
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

    -webkit-mask-size: 100% 100%;
            mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
}

.nail-pattern {
    background-size: cover;
    background-position: center top;
    background-repeat: no-repeat;
}

/* 3. Glanz + Wölbung: graue Kopie des Nagels, weich eingeblendet */
.nail-shine {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;

    filter: grayscale(1) contrast(1.5);
    mix-blend-mode: soft-light;
    opacity: 0.9;

    pointer-events: none;
}

/* 5. Glanz: wie echter Lack, schmal, scharf, seitlich versetzt */
.nail-gloss {
    mix-blend-mode: screen;
    pointer-events: none;
}

/* Lichtstreifen: läuft oben und unten weich aus */
.nail-gloss::before {
    content: "";
    position: absolute;
    inset: 0;

    background: linear-gradient(
        90deg,
        transparent 22%,
        rgba(255, 255, 255, 0.08) 26%,
        rgba(255, 255, 255, 0.55) 31%,
        rgba(255, 255, 255, 0.60) 35%,
        rgba(255, 255, 255, 0.12) 40%,
        transparent 46%
    );

    -webkit-mask-image: linear-gradient(180deg, transparent 4%, #000 18%, #000 55%, transparent 88%);
            mask-image: linear-gradient(180deg, transparent 4%, #000 18%, #000 55%, transparent 88%);
}

/* Glanzpunkt an der Spitze + Randreflex rechts + Schimmer am Nagelbett */
.nail-gloss::after {
    content: "";
    position: absolute;
    inset: 0;

    background:
        radial-gradient(ellipse 6% 5% at 66% 12%, rgba(255, 255, 255, 0.9), transparent),
        linear-gradient(90deg, transparent 84%, rgba(255, 255, 255, 0.22) 90%, transparent 96%),
        radial-gradient(ellipse 40% 7% at 50% 96%, rgba(255, 255, 255, 0.25), transparent);
}


/* =========================================
   POSITIONEN
   ========================================= */

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