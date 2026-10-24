<template>
    <div class="effekt-panel">

        <h3>EFFEKT</h3>

        <div
            v-for="effekt in effekte"
            :key="effekt.name"
        >
            <button
                type="button"
                @click="selectEffekt(effekt.name)"
            >
                <img
                    :src="effekt.icon"
                    alt=""
                >

                <span>
                    {{ effekt.name }}
                </span>
            </button>
        </div>

    </div>
</template>


<script setup>

const emit = defineEmits([
    'effekt-selected'
])


function selectEffekt(effekt) {

    emit(
        'effekt-selected',
        effekt
    )

}


const effekte = [

    {
        name: 'Chrome',
        icon: '/images/icons/effekt.svg',
    },

    {
        name: 'Matt',
        icon: '/images/icons/effekt.svg',
    },

    {
        name: 'Glänzend',
        icon: '/images/icons/effekt.svg',
    },

]

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| EFFEKT PANEL
|--------------------------------------------------------------------------
*/

.effekt-panel {

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
        0.08vw solid #eeeeee;

    border-radius: 0.9rem;

    box-shadow:
        0 0.3vw 1.2vw
        rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| ÜBERSCHRIFT
|--------------------------------------------------------------------------
*/

.effekt-panel h3 {

    margin: 0 0 0.8rem 0;

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


/*
|--------------------------------------------------------------------------
| BUTTON
|--------------------------------------------------------------------------
*/

.effekt-panel button {

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

    color: #333333;

    text-align: left;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;

}


.effekt-panel button:hover {

    background: #f8f0f2;

    color: #7d1024;

    transform: translateX(0.15rem);

}


/*
|--------------------------------------------------------------------------
| ICON
|--------------------------------------------------------------------------
*/

.effekt-panel button img {

    width: clamp(
        1.7rem,
        2.3vw,
        2.5rem
    );

    height: clamp(
        1.7rem,
        2.3vw,
        2.5rem
    );

    flex-shrink: 0;

    object-fit: contain;

}


/*
|--------------------------------------------------------------------------
| TEXT
|--------------------------------------------------------------------------
*/

.effekt-panel button span {

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .effekt-panel {

        width: 75vw;

        max-width: 12rem;

        padding: 1rem;

    }

}

</style>