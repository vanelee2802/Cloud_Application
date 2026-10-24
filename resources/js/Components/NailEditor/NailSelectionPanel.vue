<template>

    <div class="nail-selection-panel">

        <h3>NÄGEL AUSWÄHLEN</h3>

        <div class="nail-list">

            <div
                v-for="nail in nails"
                :key="nail.name"
            >

                <button
                    type="button"
                    @click="selectNail(nail.name)"
                >

                    <span>
                        {{ nail.name }}
                    </span>

                </button>

            </div>

            <button
                type="button"
                class="left-hand-button"
                @click="selectLeftHand"
            >
                Linke Hand bearbeiten
            </button>

        </div>

    </div>

</template>


<script setup>

const emit = defineEmits([
    'nail-selected',
    'left-hand-selected'
])


function selectNail(nail) {

    emit(
        'nail-selected',
        nail
    )

}


function selectLeftHand() {

    emit(
        'left-hand-selected'
    )

}


const nails = [

    {
        name: 'Daumen'
    },

    {
        name: 'Zeigefinger'
    },

    {
        name: 'Mittelfinger'
    },

    {
        name: 'Ringfinger'
    },

    {
        name: 'Kleiner Finger'
    }

]

</script>


<style scoped>

.nail-selection-panel {

    width: clamp(
        9rem,
        13vw,
        12rem
    );

    padding: clamp(
        0.8rem,
        1vw,
        1.2rem
    );

    background: #ffffff;

    border:
        0.08vw
        solid
        #eeeeee;

    border-radius: 0.9rem;

    box-shadow:
        0
        0.3vw
        1.2vw
        rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

}


.nail-selection-panel h3 {

    margin:
        0
        0
        0.8rem
        0;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(
        0.8rem,
        0.9vw,
        1rem
    );

    font-weight: 400;

    letter-spacing: 0.08em;

}


.nail-list {

    display: flex;

    flex-direction: column;

    gap: 0.2rem;

}


.nail-list button {

    display: flex;

    align-items: center;

    gap: 0.618rem;

    width: 100%;

    padding:
        0.45rem
        0.35rem;

    border: none;

    border-radius: 0.5rem;

    background: transparent;

    color: #333;

    text-align: left;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;

}


.nail-list button:hover {

    background: #f8f0f2;

    color: #7d1024;

    transform: translateX(0.15rem);

}


.nail-list button span {

    overflow: hidden;

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

    text-overflow: ellipsis;

    white-space: nowrap;

}


.left-hand-button {

    margin-top: 0.6rem;

    border-top:
        1px solid
        #eeeeee !important;

    border-radius: 0 !important;

    padding-top: 0.8rem !important;

}


@media (max-width: 700px) {

    .nail-selection-panel {

        width: 75vw;

        max-width: 12rem;

        padding: 1rem;

    }

}

</style>