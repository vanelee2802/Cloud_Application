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

function isSelected(nail) {
    return props.selectedNail === nail.name
}
</script>

<template>
    <div class="hand-container">

        <img
            src="/images/nail-editor/Hand.png"
            alt="Hand"
            class="hand-image"
        />

        <div
            v-for="nail in nails"
            :key="nail.name"
            class="nail"
            :class="[nail.className, { selected: isSelected(nail) }]"
        >
            <img
                :src="nail.image"
                :alt="`${nail.name} Nagel`"
                class="nail-image"
            />

            <div
                v-if="nailColor(nail)"
                class="nail-color"
                :style="{
                    backgroundColor: nailColor(nail),
                    maskImage: `url('${nail.image}')`,
                    WebkitMaskImage: `url('${nail.image}')`
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

/* Farbebene */
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

/* Ausgewählten Nagel markieren */
.nail.selected .nail-image {
    filter:
        drop-shadow(0 0 2px black)
        drop-shadow(0 0 2px black);
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
</style>