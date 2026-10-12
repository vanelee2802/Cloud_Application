<template>
    <div class="color-panel">

        <!-- Überschrift + Lupe -->
        <div class="color-header">

                      <h3>
                FARBE<span v-if="targetLabel" class="target-label"> · {{ targetLabel }}</span>
            </h3>
            <button
                type="button"
                class="search-toggle"
                @click="showSearch = !showSearch"
                :aria-label="showSearch ? 'Suche schließen' : 'Farben suchen'"
            >
                🔍
            </button>

        </div>


        <!-- Suchfeld -->
        <div v-if="showSearch" class="color-search">

            <input
                v-model="searchTerm"
                type="text"
                placeholder="Farbe suchen..."
            />

        </div>


        <!-- Standardfarben -->
        <div class="standard-colors">

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'rot' }"
                @click="toggleFilter('rot')"
            >
                Rot
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'blau' }"
                @click="toggleFilter('blau')"
            >
                Blau
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'grün' }"
                @click="toggleFilter('grün')"
            >
                Grün
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'gelb' }"
                @click="toggleFilter('gelb')"
            >
                Gelb
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'schwarz' }"
                @click="toggleFilter('schwarz')"
            >
                Schwarz
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'weiß' }"
                @click="toggleFilter('weiß')"
            >
                Weiß
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'rosa' }"
                @click="toggleFilter('rosa')"
            >
                Rosa
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'lila' }"
                @click="toggleFilter('lila')"
            >
                Lila
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'orange' }"
                @click="toggleFilter('orange')"
            >
                Orange
            </button>

            <button
                type="button"
                class="standard-color"
                :class="{ active: selectedFilter === 'braun' }"
                @click="toggleFilter('braun')"
            >
                Braun
            </button>

        </div>


        <!-- Aktiver Filter -->
        <div
            v-if="selectedFilter || searchTerm"
            class="filter-info"
        >

            <span>
                {{ selectedFilter || searchTerm }}
            </span>

            <button
                type="button"
                @click="clearFilter"
            >
                ×
            </button>

        </div>


        <!-- Farbliste -->
        <div class="color-list">

                     <button
                v-for="color in filteredColors"
                :key="color.id ?? color.name"
                type="button"
                class="color-button"
                                :class="{ active: isActive(color) }"
                :aria-pressed="isActive(color)"
                :title="color.name"
                @click="selectColor(color)"
            >

                <!-- NUR dieser Kreis bekommt die Farbe -->
                <span
                    class="color-circle"
                    :style="{ backgroundColor: color.hex_code }"
                ></span>

                <span class="color-name">
                    {{ color.name }}
                </span>

            </button>


            <!-- Keine Ergebnisse -->
            <p
                v-if="filteredColors.length === 0"
                class="no-results"
            >
                Keine Farben gefunden.
            </p>

        </div>

    </div>
</template>


<script setup>

import { ref, computed, onMounted } from 'vue'


const emit = defineEmits(['color-selected'])


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const colors = ref([])

const searchTerm = ref('')

const selectedFilter = ref(null)

const showSearch = ref(false)


/// Vom Editor gesteuert:
// activeHex   = welche Farbe markiert ist
// targetLabel = wofür gerade gefärbt wird (z. B. "Spitze"), leer = Grundfarbe
const props = defineProps({
    activeHex:   { type: String, default: null },
    targetLabel: { type: String, default: '' },
})

function isActive(color) {

    return Boolean(props.activeHex) &&
        color.hex_code?.toLowerCase() === props.activeHex.toLowerCase()

}

// Der Editor entscheidet, was mit der Farbe passiert
function selectColor(color) {

    emit('color-selected', color)

}

function colorKey(color) {

    return color.id ?? color.name

}

function selectColor(color) {

    // Zweiter Klick auf dieselbe Farbe: Auswahl aufheben
    if (selectedKey.value === colorKey(color)) {

        selectedKey.value = null

        emit('color-selected', null)

        return

    }

    selectedKey.value = colorKey(color)

    emit('color-selected', color)

}


/*
|--------------------------------------------------------------------------
| Standardfarben Filter
|--------------------------------------------------------------------------
*/

function toggleFilter(filter) {

    if (selectedFilter.value === filter) {

        selectedFilter.value = null

    } else {

        selectedFilter.value = filter

    }

}


/*
|--------------------------------------------------------------------------
| Filter zurücksetzen
|--------------------------------------------------------------------------
*/

function clearFilter() {

    selectedFilter.value = null

    searchTerm.value = ''

}


/*
|--------------------------------------------------------------------------
| Farben filtern
|--------------------------------------------------------------------------
*/

const filteredColors = computed(() => {

    let result = colors.value


    /*
    |--------------------------------------------------------------------------
    | Suchfeld
    |--------------------------------------------------------------------------
    |
    | Sucht im Namen UND in der Kategorie.
    | So findet "blau" auch "Petrol" oder "Nachtblau".
    |
    */

    const search = searchTerm.value
        .toLowerCase()
        .trim()


    if (search) {

        result = result.filter(color => {

            const name = color.name?.toLowerCase() ?? ''

            const category = color.category?.toLowerCase() ?? ''

            return name.includes(search) || category.includes(search)

        })

    }


    /*
    |--------------------------------------------------------------------------
    | Standardfarben
    |--------------------------------------------------------------------------
    |
    | Filtert über das Feld "category" statt über den Namen.
    | So erscheint z. B. "Bordeaux" beim Filter "Rot".
    |
    */

    if (selectedFilter.value) {

        const filter = selectedFilter.value.toLowerCase()


        result = result.filter(color => {

            return color.category?.toLowerCase() === filter

        })

    }


    return result

})


/*
|--------------------------------------------------------------------------
| Farben laden (Mockup: aus public/colors.json)
|--------------------------------------------------------------------------
*/

onMounted(async () => {

    try {

        const response = await fetch('/colors.json')


        if (!response.ok) {

            throw new Error(
                'Farben konnten nicht geladen werden.'
            )

        }


        colors.value = await response.json()

    } catch (error) {

        console.error(
            'Fehler beim Laden der Farben:',
            error
        )

    }

})

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| PANEL (WAAGERECHT FÜR DIE UNTERE ROW)
|--------------------------------------------------------------------------
|
|  FARBE 🔍 [Suche]  Rot Blau Grün …          Filter: Rot ×
|  ●    ●    ●    ●    ●    ●    ●    ●    ●    ●   →
|
*/

.color-panel {

    display: grid;

    grid-template-columns:
        auto              /* Überschrift + Lupe */
        auto              /* Suchfeld (0, wenn zu) */
        minmax(0, 1fr)    /* Filter-Chips */
        auto;             /* Filter-Info */

    grid-template-rows:
        auto
        minmax(0, 1fr);

    grid-template-areas:
        "title search chips info"
        "list  list   list  list";

    align-items: center;

    column-gap: 1rem;
    row-gap: 0.6rem;

    width: 100%;
    height: 100%;

    padding: 0.9rem 1.2rem;

    background: #ffffff;

    border: 1px solid #eeeeee;

    border-radius: 1rem;

    box-shadow: 0 0.3vw 1.2vw rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

    overflow: hidden;

}

.color-header    { grid-area: title; }
.color-search    { grid-area: search; }
.standard-colors { grid-area: chips; }
.filter-info     { grid-area: info; }
.color-list,
.no-results      { grid-area: list; }


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.color-header {

    display: flex;

    align-items: center;

    gap: 0.3rem;

    margin: 0;

}

.color-header h3 {

    margin: 0;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(0.85rem, 1vw, 1.1rem);

    font-weight: 400;

    letter-spacing: 0.08em;

}


/*
|--------------------------------------------------------------------------
| LUPE
|--------------------------------------------------------------------------
| Das Emoji im Template wird ausgeblendet und durch ein
| Linien-Icon in Bordeaux ersetzt (passt zu den anderen Icons).
*/

.search-toggle {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 2rem;

    height: 2rem;

    padding: 0;

    border: none;

    border-radius: 50%;

    background: transparent;

    cursor: pointer;

    font-size: 0;

    transition: background-color 0.2s ease;

}

.search-toggle::before {

    content: "";

    width: 1rem;

    height: 1rem;

    background: #7d1024;

    -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-4-4'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-4-4'/%3E%3C/svg%3E") center / contain no-repeat;

}

.search-toggle:hover {

    background: #f3e5e8;

}

.search-toggle:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 1px;

}


/*
|--------------------------------------------------------------------------
| SUCHFELD (erscheint in derselben Zeile)
|--------------------------------------------------------------------------
*/

.color-search {

    width: clamp(9rem, 12vw, 13rem);

    margin: 0;

}

.color-search input {

    width: 100%;

    padding: 0.4em 0.8em;

    border: 1px solid #e6dadd;

    border-radius: 0.5rem;

    background: #fbf7f8;

    color: #333333;

    font-size: clamp(0.72rem, 0.8vw, 0.85rem);

    outline: none;

    box-sizing: border-box;

}

.color-search input:focus {

    background: #ffffff;

    border-color: #7d1024;

}


/*
|--------------------------------------------------------------------------
| FILTER-CHIPS (eine Zeile, scrollbar wenn zu viele)
|--------------------------------------------------------------------------
*/

.standard-colors {

    display: flex;

    flex-wrap: nowrap;

    gap: 0.35rem;

    margin: 0;

    min-width: 0;

    overflow-x: auto;

    scrollbar-width: none;

}

.standard-colors::-webkit-scrollbar {

    display: none;

}

.standard-color {

    flex-shrink: 0;

    padding: 0.3em 0.75em;

    border: 1px solid #e4e4e4;

    border-radius: 100vw;

    background: #ffffff;

    color: #555555;

    font-size: clamp(0.65rem, 0.72vw, 0.78rem);

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease;

}

.standard-color:hover {

    background: #f8f0f2;

    border-color: #c9aab1;

    color: #7d1024;

}

.standard-color.active {

    background: #f3e5e8;

    border-color: #7d1024;

    color: #7d1024;

    font-weight: 600;

}


/*
|--------------------------------------------------------------------------
| FILTER INFO
|--------------------------------------------------------------------------
*/

.filter-info {

    display: flex;

    align-items: center;

    gap: 0.5rem;

    margin: 0;

    padding: 0.3rem 0.7rem;

    border-radius: 100vw;

    background: #f8f0f2;

    color: #7d1024;

    font-size: clamp(0.68rem, 0.75vw, 0.8rem);

    white-space: nowrap;

}

.filter-info button {

    padding: 0;

    border: none;

    background: transparent;

    color: #7d1024;

    font-size: 1rem;

    line-height: 1;

    cursor: pointer;

}


/*
|--------------------------------------------------------------------------
| FARB-LISTE (WAAGERECHT)
|--------------------------------------------------------------------------
*/

.color-list {

    display: flex;

    flex-direction: row;

    align-items: flex-start;

    gap: 0.4rem;

    height: 100%;

    min-width: 0;

    padding: 0.2rem 0 0.4rem;

    overflow-x: auto;

    overflow-y: hidden;

    scroll-snap-type: x proximity;

    scrollbar-width: thin;

    scrollbar-color: #d8bcc2 transparent;

}

.color-list::-webkit-scrollbar {

    height: 0.3rem;

}

.color-list::-webkit-scrollbar-track {

    background: transparent;

}

.color-list::-webkit-scrollbar-thumb {

    background: #d8bcc2;

    border-radius: 1rem;

}

.color-list::-webkit-scrollbar-thumb:hover {

    background: #7d1024;

}


/*
|--------------------------------------------------------------------------
| EINZELNE FARBE: Kreis oben, Name darunter
|--------------------------------------------------------------------------
*/

.color-button {

    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 0.35rem;

    flex-shrink: 0;

    width: clamp(3.6rem, 4.4vw, 4.6rem);

    padding: 0.35rem 0.2rem;

    /* Der Button selbst bekommt KEINE Farbe */
    border: none;

    border-radius: 0.6rem;

    background: transparent;

    color: #333333;

    cursor: pointer;

    text-align: center;

    appearance: none;

    -webkit-appearance: none;

    box-shadow: none;

    outline: none;

    scroll-snap-align: start;

    transition: background-color 0.2s ease;

}


/*
|--------------------------------------------------------------------------
| FARBKREIS
|--------------------------------------------------------------------------
*/

.color-circle {

    display: flex;

    width: clamp(2.2rem, 2.7vw, 2.8rem);

    height: clamp(2.2rem, 2.7vw, 2.8rem);

    flex-shrink: 0;

    border: 1px solid #dddddd;

    border-radius: 50%;

    box-sizing: border-box;

    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;

}


/*
|--------------------------------------------------------------------------
| FARBNAME
|--------------------------------------------------------------------------
*/

.color-name {

    max-width: 100%;

    overflow: hidden;

    color: #555555;

    font-size: clamp(0.62rem, 0.68vw, 0.75rem);

    line-height: 1.2;

    text-overflow: ellipsis;

    white-space: nowrap;

}


/*
|--------------------------------------------------------------------------
| HOVER / FOCUS
|--------------------------------------------------------------------------
| Wichtig: Auch beim Anklicken darf der Button NICHT rot werden.
*/

.color-button:hover {

    background: #f8f0f2;

}

.color-button:hover .color-circle {

    transform: scale(1.08);

    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);

}

.color-button:focus {

    outline: none;

}

.color-button:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 1px;

    background: #f8f0f2;

}



/*
|--------------------------------------------------------------------------
| KEINE ERGEBNISSE
|--------------------------------------------------------------------------
*/

.no-results {

    align-self: center;

    margin: 0;

    color: #999999;

    font-size: clamp(0.7rem, 0.8vw, 0.9rem);

    text-align: center;

}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .color-panel {

        grid-template-columns: auto minmax(0, 1fr);

        grid-template-areas:
            "title  info"
            "search search"
            "chips  chips"
            "list   list";

        grid-template-rows: auto;

        height: auto;

        padding: 1rem;

    }

    .color-search {

        width: 100%;

    }

    .filter-info {

        justify-self: end;

    }

}


/*
|--------------------------------------------------------------------------
| REDUCED MOTION
|--------------------------------------------------------------------------
*/

@media (prefers-reduced-motion: reduce) {

    .color-circle,
    .color-button,
    .search-toggle,
    .standard-color {

        transition: none;

    }

    .color-button:hover .color-circle {

        transform: none;

    }

}

/*
|--------------------------------------------------------------------------
| AUSGEWÄHLTE FARBE (gleicher Stil wie Form + Designs)
|--------------------------------------------------------------------------
*/

.color-panel .color-button.active {

    background: #f8f0f2;

    box-shadow: inset 0 0 0 2px #7d1024;

}

.color-panel .color-button.active .color-name {

    color: #7d1024;

}

/* Ziel der Farbauswahl, z. B. "· Spitze" */
.target-label {

    font-family: system-ui, sans-serif;

    font-size: 0.8em;

    letter-spacing: 0.02em;

    color: #555555;

}

</style>
