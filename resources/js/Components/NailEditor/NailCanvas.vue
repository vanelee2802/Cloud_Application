<script setup>
const props = defineProps({
    nailDesigns: {
        type: Object,
        required: true,
    },
    selectedNail: {
        type: String,
        default: null,
    },
})

const nails = [
    {
        name: 'Daumen',
        className: 'nail-thumb',
        image: '/images/nail-editor/almond-right hand/thumb.png',
    },
    {
        name: 'Zeigefinger',
        className: 'nail-index',
        image: '/images/nail-editor/almond-right hand/index.png',
    },
    {
        name: 'Mittelfinger',
        className: 'nail-middle',
        image: '/images/nail-editor/almond-right hand/middle.png',
    },
    {
        name: 'Ringfinger',
        className: 'nail-ring',
        image: '/images/nail-editor/almond-right hand/ring.png',
    },
    {
        name: 'Kleiner Finger',
        className: 'nail-pinky',
        image: '/images/nail-editor/almond-right hand/pinky.png',
    },
]

function nailColor(nail) {
    return props.nailDesigns[nail.name]?.color?.value ?? null
}

function nailImage(nail) {
    return props.nailDesigns[nail.name]?.form?.image ?? nail.image
}

function isSelected(nail) {
    return props.selectedNail === nail.name
}
</script>

<template>
    <div class="hand-container">

        <!-- Hand -->
        <img
            src="/images/nail-editor/Hand.png"
            alt="Hand"
            class="hand-image"
        />

        <!-- Nägel -->
        <div
            v-for="nail in nails"
            :key="nail.name"
            class="nail"
            :class="[nail.className, { selected: isSelected(nail) }]"
        >

            <!-- Original-Nagel -->
            <img
                :src="nailImage(nail)"
                :alt="`${nail.name} Nagel`"
                class="nail-image"
            />

            <!-- Farbe -->
            <div
                v-if="nailColor(nail)"
                class="nail-color"
                :style="{
                    backgroundColor: nailColor(nail),
                    maskImage: `url('${nailImage(nail)}')`,
                    WebkitMaskImage: `url('${nailImage(nail)}')`
                }"
            ></div>

        </div>

    </div>
</template>

<style scoped>
.hand-container {
    position: relative;
    width: 40vw;
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
    pointer-events: none;
}

.nail-image {
    display: block;
    width: 100%;
    height: auto;
}

/* Farbe */
.nail-color {
    position: absolute;
    inset: 0;

    mask-size: 100% 100%;
    mask-repeat: no-repeat;
    mask-position: center;

    -webkit-mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
    -webkit-mask-position: center;

    opacity: 0.75;
    pointer-events: none;
}

/* Ausgewählter Nagel */
.nail.selected .nail-image {
    filter:
        drop-shadow(0 0 2px black)
        drop-shadow(0 0 2px black);
}

/* Daumen */
.nail-thumb {
    width: 15%;
    left: 12%;
    top: 39%;
    transform: rotate(-0.67deg);
}

/* Zeigefinger */
.nail-index {
    width: 14%;
    left: 29%;
    top: 2%;
    transform: rotate(0deg);
}

/* Mittelfinger */
.nail-middle {
    width: 14%;
    left: 42%;
    top: -4%;
    transform: rotate(0deg);
}

/* Ringfinger */
.nail-ring {
    width: 14%;
    left: 55%;
    top: 0%;
    transform: rotate(0deg);
}

/* Kleiner Finger */
.nail-pinky {
    width: 11%;
    left: 70%;
    top: 18%;
    transform: rotate(4.14deg);
}
</style>