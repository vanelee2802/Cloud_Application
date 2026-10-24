<template>

    <div class="form-panel">

        <h3>FORM</h3>

        <div
            v-for="form in forms"
            :key="form.name"
        >

            <button
                type="button"
                @click="selectForm(form.name)"
                :title="form.name"
            >

                <img
                    :src="form.icon"
                    :alt="form.name"
                >

            </button>

        </div>

    </div>

</template>


<script setup>

const emit = defineEmits([
    'form-selected'
])


function selectForm(form) {

    emit(
        'form-selected',
        form
    )

}


const forms = [

    {
        name: 'Almond',
        icon: 'images/nail-editor/shape_icons/mandel.svg'
    },

    {
        name: 'Square',
        icon: 'images/nail-editor/shape_icons/quadratisch.svg'
    },

    {
        name: 'Coffin',
        icon: 'images/nail-editor/shape_icons/stiletto.svg'
    },

    {
        name: 'Round',
        icon: 'images/nail-editor/shape_icons/oval.svg'
    },

    {
        name: 'Edge',
        icon: 'images/nail-editor/shape_icons/edge.svg'
    },

    {
        name: 'Squoval',
        icon: 'images/nail-editor/shape_icons/squoval.svg'
    },

    {
        name: 'lipstick',
        icon: 'images/nail-editor/shape_icons/lipstick.svg'
    },

    {
        name: 'Flare',
        icon: 'images/nail-editor/shape_icons/flare.svg'
    }

]

</script>


<style scoped>

.form-panel {

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


.form-panel h3 {

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


/* =========================
   FORM BUTTON
   ========================= */

.form-panel button {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    padding:
        0.4rem;

    margin-bottom: 0.2rem;

    border: none;

    border-radius: 0.5rem;

    background: transparent;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;

}


/* =========================
   HOVER
   ========================= */

.form-panel button:hover {

    background: #f8f0f2;

    transform: translateX(0.15rem);

}


/* =========================
   SVG
   ========================= */

.form-panel button img {

    display: block;

    width: clamp(
        5rem,
        7vw,
        7rem
    );

    height: clamp(
        4rem,
        6vw,
        6rem
    );

    object-fit: contain;

}


/* =========================
   MOBILE
   ========================= */

@media (max-width: 700px) {

    .form-panel {

        width: 75vw;

        max-width: 12rem;

        padding: 1rem;

    }

    .form-panel button img {

        width: 6rem;

        height: 5rem;

    }

}

</style>