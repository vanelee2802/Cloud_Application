<template>
    <div class="hand-container">

        <img
            src="/images/nail-editor/Hand.png"
            alt="Hand"
            class="hand-image"
        />

        <!-- Daumen -->
        <img
            src="/images/nail-editor/almond-right hand/thumb.png"
            alt="Daumen Nagel"
            class="nail nail-thumb"
        />

        <!-- Zeigefinger -->
        <img
            src="/images/nail-editor/almond-right hand/index.png"
            alt="Zeigefinger Nagel"
            class="nail nail-index"
        />

        <!-- Mittelfinger -->
        <img
            src="/images/nail-editor/almond-right hand/middle.png"
            alt="Mittelfinger Nagel"
            class="nail nail-middle"
        />

        <!-- Ringfinger -->
        <img
            src="/images/nail-editor/almond-right hand/ring.png"
            alt="Ringfinger Nagel"
            class="nail nail-ring"
        />

        <!-- Kleiner Finger -->
        <img
            src="/images/nail-editor/almond-right hand/pinky.png"
            alt="Kleiner Finger Nagel"
            class="nail nail-pinky"
        />

    </div>
</template>

<style scoped>
.hand-container {
    position: relative;
    width: 40%;
    height: 40%;
    margin: auto;
    display: flex;
    justify-content: center;
    align-items: center;
}

.hand-image {
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
};
</style>