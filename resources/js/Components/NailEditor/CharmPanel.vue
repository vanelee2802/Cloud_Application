<template>

    <div class="verziehrung-panel">

        <h3>VERZIERUNG</h3>

        <div class="verziehrung-list">

            <div
                v-for="verziehrung in verziehrungen"
                :key="verziehrung.name"
            >

                <button
                    type="button"
                    @click="selectVerziehrung(verziehrung.name)"
                >

                    <img
                        :src="verziehrung.icon"
                        alt=""
                    >

                    <span>
                        {{ verziehrung.name }}
                    </span>

                </button>

            </div>


            <p
                v-if="verziehrungen.length === 0"
                class="no-verziehrungen"
            >
                Keine Verzierungen vorhanden.
            </p>

        </div>

    </div>

</template>


<script setup>

const emit = defineEmits([
    'verziehrung-selected'
])


function selectVerziehrung(verziehrung) {

    emit(
        'verziehrung-selected',
        verziehrung
    )

}


const verziehrungen = [

    {
        name: 'Strasssteine',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Perlen',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Glitzersteine',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Sticker',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Metallic',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Herzen',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Sterne',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Blumen',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Schleifen',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Perlmutt',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Gold',
        icon: '/images/icons/verziehrung.svg'
    },

    {
        name: 'Silber',
        icon: '/images/icons/verziehrung.svg'
    }

]

</script>


<style scoped>

.verziehrung-panel {

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


.verziehrung-panel h3 {

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


.verziehrung-list {

    display: flex;

    flex-direction: column;

    gap: 0.2rem;

    max-height: clamp(
        12rem,
        24vw,
        22rem
    );

    overflow-y: auto;

    padding-right: 0.2rem;

    scrollbar-width: thin;

    scrollbar-color:
        #d8bcc2
        transparent;

}


.verziehrung-list button {

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


.verziehrung-list button:hover {

    background: #f8f0f2;

    color: #7d1024;

    transform: translateX(0.15rem);

}


.verziehrung-list button img {

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


.verziehrung-list button span {

    overflow: hidden;

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

    text-overflow: ellipsis;

    white-space: nowrap;

}


.no-verziehrungen {

    margin: 1rem 0;

    color: #999;

    font-size: 0.75rem;

    text-align: center;

}


.verziehrung-list::-webkit-scrollbar {

    width: 0.3rem;

}


.verziehrung-list::-webkit-scrollbar-track {

    background: transparent;

}


.verziehrung-list::-webkit-scrollbar-thumb {

    background: #d8bcc2;

    border-radius: 1rem;

}


.verziehrung-list::-webkit-scrollbar-thumb:hover {

    background: #7d1024;

}


@media (max-width: 700px) {

    .verziehrung-panel {

        width: 75vw;

        max-width: 12rem;

        padding: 1rem;

    }

    .verziehrung-list {

        max-height: 50vw;

    }

}

</style>