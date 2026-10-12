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
                src="/images/nail-editor/Hand.png"
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
                :class="['nail', `nail-${finger.key}`]"
            >

                <!-- 1. Nagel (bestimmt die Größe) -->
                <img
                    :src="nailSrc(finger.key)"
                    :alt="finger.alt"
                    class="nail-base"
                />

                <template v-if="pattern">

                    <!-- 2. Muster, mit dem Nagel als Maske zugeschnitten -->
                    <span
                        class="nail-pattern"
                        :style="patternStyle(finger.key)"
                    ></span>

                    <!-- 3. Glanz und Wölbung vom Original-Nagel -->
                    <img
                        :src="nailSrc(finger.key)"
                        alt=""
                        aria-hidden="true"
                        class="nail-shine"
                    />

                </template>

            </span>

        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue'

// shape:   almond | square | oval | coffin | squoval | stiletto
// length:  short | medium | long
// pattern: Bildpfad des gewählten Musters (oder null)
const props = defineProps({
    shape:   { type: String, default: 'almond' },
    length:  { type: String, default: 'medium' },
    pattern: { type: String, default: null },
})

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

    return `/images/nail-editor/${folder}`

})

function nailSrc(finger) {

    return `${basePath.value}/${finger}.png`

}

// Muster-Ebene: Muster als Hintergrund, Nagelbild als Maske
function patternStyle(finger) {

    const mask = `url("${nailSrc(finger)}")`

    return {
        backgroundImage: `url("${props.pattern}")`,
        WebkitMaskImage: mask,
        maskImage:       mask,
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

/* 1. Nagel: gibt dem Wrapper seine Höhe */
.nail-base {
    display: block;
    width: 100%;
    height: auto;
}

/* 2. Muster: exakt auf den Nagel zugeschnitten */
.nail-pattern {
    position: absolute;
    inset: 0;

    background-size: cover;
    background-position: center top;
    background-repeat: no-repeat;

    -webkit-mask-size: 100% 100%;
            mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
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