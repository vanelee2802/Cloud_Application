<template>
    <div class="effekt-panel">

        <h3>EFFEKT</h3>

        <!-- Suche -->
        <div class="effekt-suche">

            <svg
                class="effekt-suche__icon"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7" />
                <path d="M20 20 L16 16" />
            </svg>

            <input
                v-model="suche"
                type="search"
                placeholder="Effekt suchen"
                aria-label="Effekt suchen"
            >

            <button
                v-if="suche"
                type="button"
                class="effekt-suche__clear"
                aria-label="Suche leeren"
                @click="suche = ''"
            >
                ×
            </button>

        </div>

        <!-- Liste -->
        <div class="effekt-liste">

            <button
                v-for="effekt in gefilterteEffekte"
                :key="effekt.name"
                type="button"
                class="effekt-item"
                :class="{ 'is-active': ausgewaehlt === effekt.name }"
                :aria-pressed="ausgewaehlt === effekt.name"
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

            <p
                v-if="gefilterteEffekte.length === 0"
                class="effekt-leer"
            >
                Kein Effekt gefunden
            </p>

        </div>

    </div>
</template>


<script setup>

import { ref, computed } from 'vue'

const emit = defineEmits([
    'effekt-selected'
])

const suche = ref('')
const ausgewaehlt = ref(null)


function selectEffekt(effekt) {

    ausgewaehlt.value = effekt

    emit(
        'effekt-selected',
        effekt
    )

}


/*
| Suchbegriffe normalisieren: Groß-/Kleinschreibung und Umlaute egal
| ("chamaleon" findet "Chamäleon", "glanzend" findet "Glänzend")
*/
function normalisieren(text) {

    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()

}


const ICON_PATH = '/images/nail-editor/effects_icon'

/*
| tags = zusätzliche Suchbegriffe, die nicht im Namen stehen
*/
const effekte = [

    { name: 'Glänzend',      icon: `${ICON_PATH}/glanz.svg`,      tags: ['gloss', 'glanz', 'hochglanz'] },
    { name: 'Matt',          icon: `${ICON_PATH}/matt.svg`,       tags: ['matte'] },
    { name: 'Satin',         icon: `${ICON_PATH}/satin.svg`,      tags: ['seidenmatt', 'seide'] },
    { name: 'Metallic',      icon: `${ICON_PATH}/metallic.svg`,   tags: ['metall', 'silber', 'gold'] },
    { name: 'Chrome',        icon: `${ICON_PATH}/chrome.svg`,     tags: ['spiegel', 'mirror', 'chrom'] },
    { name: 'Perlmutt',      icon: `${ICON_PATH}/perlmutt.svg`,   tags: ['pearl', 'perle', 'glazed'] },
    { name: 'Holo / Aurora', icon: `${ICON_PATH}/holo.svg`,       tags: ['holographic', 'regenbogen', 'rainbow'] },
    { name: 'Chamäleon',     icon: `${ICON_PATH}/chamaeleon.svg`, tags: ['duochrome', 'farbwechsel'] },
    { name: 'Cat-Eye',       icon: `${ICON_PATH}/cat-eye.svg`,    tags: ['magnet', 'magnetic', 'katzenauge'] },
    { name: 'Velvet',        icon: `${ICON_PATH}/velvet.svg`,     tags: ['samt'] },
    { name: 'Jelly',         icon: `${ICON_PATH}/jelly.svg`,      tags: ['transparent', 'glas'] },
    { name: 'Shimmer',       icon: `${ICON_PATH}/shimmer.svg`,    tags: ['schimmer'] },
    { name: 'Sugar',         icon: `${ICON_PATH}/sugar.svg`,      tags: ['zucker', 'sand', 'struktur'] },
    { name: 'Neon',          icon: `${ICON_PATH}/neon.svg`,       tags: ['leuchtend', 'grell'] },

]


const gefilterteEffekte = computed(() => {

    const begriff = normalisieren(suche.value)

    if (!begriff) {
        return effekte
    }

    return effekte.filter((effekt) =>
        [effekt.name, ...effekt.tags]
            .some((text) => normalisieren(text).includes(begriff))
    )

})

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| EFFEKT PANEL
|--------------------------------------------------------------------------
*/

.effekt-panel {

    display: flex;
    flex-direction: column;

    width: clamp(9rem, 13vw, 12rem);

    padding: clamp(0.8rem, 1vw, 1.2rem);

    background: #ffffff;

    border: 1px solid #eeeeee;
    border-radius: 0.9rem;

    box-shadow: 0 0.3vw 1.2vw rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| ÜBERSCHRIFT
|--------------------------------------------------------------------------
*/

.effekt-panel {

    display: flex;
    flex-direction: column;

    width: clamp(160px, 18vw, 240px);

    padding: clamp(0.7rem, 1vw, 1.4rem);

    background: #ffffff;

    border: 1px solid #eeeeee;
    border-radius: 1vw;

    box-shadow: 0 0.3vw 1.2vw rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| SUCHE
|--------------------------------------------------------------------------
*/

.effekt-suche {

    position: relative;

    margin-bottom: 0.6rem;

}

.effekt-suche input {

    width: 100%;

    padding: 0.45rem 1.6rem 0.45rem 1.8rem;

    border: 1px solid #e6dadd;
    border-radius: 0.5rem;

    background: #fbf7f8;

    color: #333333;

    font: inherit;
    font-size: clamp(0.7rem, 0.8vw, 0.85rem);

    box-sizing: border-box;

    transition: border-color 0.2s ease, background-color 0.2s ease;

}

/* Browser-eigenes "x" im Suchfeld ausblenden, wir haben ein eigenes */
.effekt-suche input::-webkit-search-cancel-button {
    -webkit-appearance: none;
}

.effekt-suche input::placeholder {
    color: #a8999d;
}

.effekt-suche input:focus {

    outline: none;

    border-color: #7d1024;

    background: #ffffff;

}

.effekt-suche__icon {

    position: absolute;
    left: 0.55rem;
    top: 50%;

    width: 0.85rem;
    height: 0.85rem;

    transform: translateY(-50%);

    fill: none;
    stroke: #a8999d;
    stroke-width: 2.2;
    stroke-linecap: round;

    pointer-events: none;

}

.effekt-suche__clear {

    position: absolute;
    right: 0.3rem;
    top: 50%;

    width: 1.2rem;
    height: 1.2rem;

    padding: 0;

    transform: translateY(-50%);

    border: none;
    border-radius: 50%;

    background: transparent;

    color: #a8999d;

    font-size: 1rem;
    line-height: 1;

    cursor: pointer;

}

.effekt-suche__clear:hover {
    color: #7d1024;
}


/*
|--------------------------------------------------------------------------
| LISTE MIT SCROLLBAR
|--------------------------------------------------------------------------
*/

.effekt-liste {

    max-height: 22rem;

    overflow-y: auto;

    /* Platz, damit die Scrollbar nicht an den Buttons klebt */
    padding-right: 0.35rem;
    margin-right: -0.35rem;

    /* Firefox */
    scrollbar-width: thin;
    scrollbar-color: #d9b3bc transparent;

}

/* Chrome, Edge, Safari */
.effekt-liste::-webkit-scrollbar {
    width: 0.3rem;
}

.effekt-liste::-webkit-scrollbar-track {
    background: transparent;
}

.effekt-liste::-webkit-scrollbar-thumb {

    background: #d9b3bc;

    border-radius: 1rem;

}

.effekt-liste::-webkit-scrollbar-thumb:hover {
    background: #b8808e;
}


/*
|--------------------------------------------------------------------------
| BUTTON
|--------------------------------------------------------------------------
*/

.effekt-item {

    display: flex;
    align-items: center;
    gap: 0.618rem;

    width: 100%;

    padding: 0.4rem 0.35rem;

    border: 1px solid transparent;
    border-radius: 0.5rem;

    background: transparent;

    color: #333333;

    text-align: left;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        transform 0.2s ease;

}

.effekt-item:hover {

    background: #f8f0f2;

    color: #7d1024;

    transform: translateX(0.15rem);

}

.effekt-item:focus-visible {

    outline: 2px solid #7d1024;
    outline-offset: 1px;

}

.effekt-item.is-active {

    background: #f8f0f2;

    border-color: #7d1024;

    color: #7d1024;

}


/*
|--------------------------------------------------------------------------
| ICON (Icons sind hochkant 48 × 64)
|--------------------------------------------------------------------------
*/

.effekt-item img {

    width: clamp(1.7rem, 2.3vw, 2.5rem);
    height: clamp(2.2rem, 3vw, 3.3rem);

    flex-shrink: 0;

    object-fit: contain;

}


/*
|--------------------------------------------------------------------------
| TEXT
|--------------------------------------------------------------------------
*/

.effekt-item span {

    font-size: clamp(0.7rem, 0.8vw, 0.9rem);

    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;

}

.effekt-leer {

    margin: 0.8rem 0;

    color: #a8999d;

    font-size: 0.8rem;

    text-align: center;

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

    .effekt-liste {
        max-height: 50vh;
    }

}


/*
|--------------------------------------------------------------------------
| REDUCED MOTION
|--------------------------------------------------------------------------
*/

@media (prefers-reduced-motion: reduce) {

    .effekt-item {
        transition: none;
    }

    .effekt-item:hover {
        transform: none;
    }

}

</style>