<template>

    <div class="form-panel">


        <!-- ========================================= -->
        <!-- LÄNGE -->
        <!-- ========================================= -->

        <h3>LÄNGE</h3>

        <div
            class="length-group"
            role="group"
            aria-label="Nagellänge"
        >

            <button
                v-for="length in lengths"
                :key="length.id"
                type="button"
                class="length-button"
                :class="{ active: length.id === selectedLength }"
                :aria-pressed="length.id === selectedLength"
                @click="selectLength(length.id)"
            >

                {{ length.name }}

            </button>

        </div>


        <!-- ========================================= -->
        <!-- FORM (nur Formen, die es in dieser Länge gibt) -->
        <!-- ========================================= -->

        <h3>FORM</h3>

        <div
            v-for="form in visibleForms"
            :key="form.id"
        >

            <button
                type="button"
                class="form-button"
                :class="{ active: form.id === selected }"
                :aria-pressed="form.id === selected"
                :title="form.name"
                @click="selectForm(form.id)"
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

import { computed } from 'vue'

// selected       = id der gewählten Form    (kommt von der DesignEditor-Page)
// selectedLength = id der gewählten Länge   (kommt von der DesignEditor-Page)
const props = defineProps({
    selected: {
        type: String,
        default: 'almond'
    },
    selectedLength: {
        type: String,
        default: 'medium'
    }
})

const emit = defineEmits([
    'form-selected',
    'length-selected'
])


/*
|--------------------------------------------------------------------------
| PFADE
|--------------------------------------------------------------------------
|
| Icons der Formen (Auswahl-Buttons):
|   public/images/nail-editor/icon/FormPanel/
|
| Nagelbilder für die Hand (werden in NailHand.vue geladen, nicht hier):
|   public/images/nail-editor/FormPanel/forms_right hand/<Ordner>/<finger>.png
|
|   id      = Ordnername
|             short  -> "<id>-short-right hand"
|             medium -> "<id>-right hand"
|             long   -> "<id>-long-right hand"
|             (Stiletto: immer "stiletto-right hand")
|   lengths = in welchen Längen es Nagelbilder für diese Form gibt
*/

const ICON_PATH = '/images/nail-editor/icon/FormPanel'

const forms = [

    {
        id: 'almond',
        name: 'Almond',
        icon: `${ICON_PATH}/mandel.svg`,
        lengths: ['short', 'medium', 'long']
    },

    {
        id: 'square',
        name: 'Square',
        icon: `${ICON_PATH}/quadratisch.svg`,
        lengths: ['short', 'medium', 'long']
    },

    {
        id: 'oval',
        name: 'Round',
        icon: `${ICON_PATH}/oval.svg`,
        lengths: ['short', 'medium', 'long']
    },

    {
        id: 'coffin',
        name: 'Coffin',
        icon: `${ICON_PATH}/coffin.svg`,
        lengths: ['medium', 'long']
    },

    {
        id: 'ballerina',
        name: 'Ballerina',
        icon: `${ICON_PATH}/ballerina.svg`,
        lengths: ['medium', 'long']
    },

    {
        id: 'squoval',
        name: 'Squoval',
        icon: `${ICON_PATH}/squoval.svg`,
        lengths: ['medium']
    },

    {
        id: 'stiletto',
        name: 'Stiletto',
        icon: `${ICON_PATH}/stiletto.svg`,
        lengths: ['long']
    }

]


const lengths = [

    { id: 'short',  name: 'Kurz' },

    { id: 'medium', name: 'Mittel' },

    { id: 'long',   name: 'Lang' }

]


// Nur die Formen anzeigen, die es in der gewählten Länge gibt
const visibleForms = computed(() => {

    return forms.filter(form =>
        form.lengths.includes(props.selectedLength)
    )

})


// Form, auf die automatisch gewechselt wird, wenn die aktuelle
// Form in der neu gewählten Länge nicht existiert.
const defaultFormForLength = {
    short:  'almond',
    medium: 'almond',
    long:   'almond'
}


function selectForm(id) {

    emit(
        'form-selected',
        id
    )

}


function selectLength(id) {

    emit(
        'length-selected',
        id
    )

    // Gibt es die aktuelle Form in der neuen Länge nicht
    // (z. B. Stiletto in "Kurz"), wird auf die Standard-Form gewechselt.
    const current = forms.find(form => form.id === props.selected)

    if (!current || !current.lengths.includes(id)) {

        emit(
            'form-selected',
            defaultFormForLength[id]
        )

    }

}

</script>


<style scoped>

.form-panel {

    width: clamp(
        9rem,
        13vw,
        12rem
    );

    height: 100%;

    max-height: 100%;

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

    overflow-y: auto;

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
   LÄNGE
   ========================= */

.length-group {

    display: flex;

    gap: 0.25rem;

    margin-bottom: 1.2rem;

}


.length-button {

    flex: 1;

    padding:
        0.4rem
        0.2rem;

    border:
        0.08rem
        solid
        #e6d6da;

    border-radius: 0.5rem;

    background: #ffffff;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(
        0.65rem,
        0.75vw,
        0.8rem
    );

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        color 0.2s ease;

}


.length-button:hover {

    background: #f8f0f2;

}


.length-button.active {

    background: #7d1024;

    border-color: #7d1024;

    color: #ffffff;

}


/* =========================
   FORM BUTTON
   ========================= */

.form-button {

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


.form-button:hover {

    background: #f8f0f2;

    transform: translateX(0.15rem);

}


.form-button.active {

    background: #f8f0f2;

    box-shadow: inset 0 0 0 0.12rem #7d1024;

}


/* =========================
   TASTATUR-FOKUS
   ========================= */

.form-panel button:focus-visible {

    outline: 0.15rem solid #7d1024;

    outline-offset: 0.1rem;

}


/* =========================
   SVG
   ========================= */

.form-button img {

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

    .form-button img {

        width: 6rem;

        height: 5rem;

    }

}

</style>