<template>
    <div class="color-panel">

        <!-- Überschrift + Lupe -->
        <div class="color-header">

            <h3>FARBE</h3>

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


/*
|--------------------------------------------------------------------------
| Farbe auswählen
|--------------------------------------------------------------------------
*/

function selectColor(color) {

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
    */

    const search = searchTerm.value
        .toLowerCase()
        .trim()


    if (search) {

        result = result.filter(color => {

            return color.name
                ?.toLowerCase()
                .includes(search)

        })

    }


    /*
    |--------------------------------------------------------------------------
    | Standardfarben
    |--------------------------------------------------------------------------
    */

    if (selectedFilter.value) {

        const filter = selectedFilter.value.toLowerCase()


        result = result.filter(color => {

            return color.name
                ?.toLowerCase()
                .includes(filter)

        })

    }


    return result

})


/*
|--------------------------------------------------------------------------
| Farben aus Datenbank laden
|--------------------------------------------------------------------------
*/

onMounted(async () => {

    try {

        const response = await fetch('/colors')


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
| PANEL
|--------------------------------------------------------------------------
*/

.color-panel {

width: clamp(10rem, 16vw, 14rem);
    padding: clamp(1rem, 1.2vw, 1.5rem);

    background: #ffffff;

    border: 0.08vw solid #eeeeee;

    border-radius: 1vw;

    box-shadow:
        0 0.3vw 1.2vw rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.color-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 1rem;

}


.color-header h3 {

    margin: 0;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(
        0.85rem,
        1vw,
        1.1rem
    );

    font-weight: 400;

    letter-spacing: 0.08em;

}


/*
|--------------------------------------------------------------------------
| LUPE
|--------------------------------------------------------------------------
*/

.search-toggle {

    display: flex;

    align-items: center;

    justify-content: center;

    width: clamp(
        2rem,
        2.5vw,
        2.8rem
    );

    height: clamp(
        2rem,
        2.5vw,
        2.8rem
    );

    padding: 0;

    border: none;

    border-radius: 50%;

    background: transparent;

    cursor: pointer;

    font-size: clamp(
        0.9rem,
        1.2vw,
        1.2rem
    );

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;

}


.search-toggle:hover {

    background: #f3e5e8;

    transform: scale(1.08);

}


/*
|--------------------------------------------------------------------------
| SUCHFELD
|--------------------------------------------------------------------------
*/

.color-search {

    width: 100%;

    margin-bottom: 1rem;

}


.color-search input {

    width: 100%;

    padding:
        0.618em
        0.8em;

    border:
        0.08vw solid #dddddd;

    border-radius: 0.618em;

    background: #fafafa;

    color: #333333;

    font-size: clamp(
        0.75rem,
        0.9vw,
        1rem
    );

    outline: none;

    box-sizing: border-box;

}


.color-search input:focus {

    background: #ffffff;

    border-color: #7d1024;

    box-shadow:
        0 0 0 0.15vw rgba(
            125,
            16,
            36,
            0.1
        );

}


/*
|--------------------------------------------------------------------------
| STANDARD FARBEN
|--------------------------------------------------------------------------
*/

.standard-colors {

    display: flex;

    flex-wrap: wrap;

    gap: 0.4rem;

    margin-bottom: 1rem;

}


.standard-color {

    padding:
        0.35em
        0.7em;

    border:
        0.08vw solid #e4e4e4;

    border-radius: 100vw;

    background: #ffffff;

    color: #555555;

    font-size: clamp(
        0.65rem,
        0.75vw,
        0.8rem
    );

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease,
        transform 0.2s ease;

}


.standard-color:hover {

    background: #f8f0f2;

    border-color: #c9aab1;

    color: #7d1024;

    transform: translateY(-0.1rem);

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

    justify-content: space-between;

    gap: 0.5rem;

    margin-bottom: 0.8rem;

    padding:
        0.45rem
        0.7rem;

    border-radius: 0.618rem;

    background: #f8f0f2;

    color: #7d1024;

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

}


.filter-info button {

    padding: 0;

    border: none;

    background: transparent;

    color: #7d1024;

    font-size: 1rem;

    cursor: pointer;

}


/*
|--------------------------------------------------------------------------
| FARB-LISTE
|--------------------------------------------------------------------------
*/

.color-list {

    display: flex;

    flex-direction: column;

    gap: 0.618rem;

    max-height: clamp(
        12rem,
        25vw,
        24rem
    );

    overflow-y: auto;

    padding-right: 0.3rem;

    scrollbar-width: thin;

    scrollbar-color:
        #d8bcc2
        transparent;

}


/*
|--------------------------------------------------------------------------
| EINZELNE FARBE
|--------------------------------------------------------------------------
*/

.color-button {

    display: flex;

    align-items: center;

    gap: 0.8rem;

    width: 100%;

    min-height: clamp(
        2.3rem,
        3vw,
        3.2rem
    );

    padding:
        0.4rem
        0.6rem;

    /*
    WICHTIG:
    Der Button selbst bekommt KEINE Farbe.
    */

    border: none;

    border-radius: 0.618rem;

    background: transparent;

    color: #333333;

    cursor: pointer;

    text-align: left;

    appearance: none;

    -webkit-appearance: none;

    box-shadow: none;

    outline: none;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;

}


/*
|--------------------------------------------------------------------------
| FARBKREIS
|--------------------------------------------------------------------------
*/

.color-circle {

    display: block;

    width: clamp(
        1.8rem,
        2.5vw,
        2.8rem
    );

    height: clamp(
        1.8rem,
        2.5vw,
        2.8rem
    );

    flex-shrink: 0;

    border:
        0.1vw solid #dddddd;

    border-radius: 50%;

    box-sizing: border-box;

    box-shadow:
        0 0.15vw 0.5vw rgba(
            0,
            0,
            0,
            0.08
        );

}


/*
|--------------------------------------------------------------------------
| FARBNAME
|--------------------------------------------------------------------------
*/

.color-name {

    overflow: hidden;

    color: #333333;

    font-size: clamp(
        0.75rem,
        0.9vw,
        1rem
    );

    text-overflow: ellipsis;

    white-space: nowrap;

}


/*
|--------------------------------------------------------------------------
| HOVER
|--------------------------------------------------------------------------
*/

.color-button:hover {

    background: #f8f0f2;

    transform: translateX(0.25rem);

}


/*
|--------------------------------------------------------------------------
| FOCUS
|--------------------------------------------------------------------------
|
| Wichtig:
| Auch beim Anklicken darf der Button NICHT rot werden.
|
*/

.color-button:focus {

    outline: none;

    background: #f8f0f2;

}


.color-button:focus-visible {

    outline:
        0.1vw solid #7d1024;

    outline-offset: 0.1rem;

    background: #f8f0f2;

}


/*
|--------------------------------------------------------------------------
| KEINE ERGEBNISSE
|--------------------------------------------------------------------------
*/

.no-results {

    margin: 1rem 0;

    color: #999999;

    font-size: clamp(
        0.7rem,
        0.8vw,
        0.9rem
    );

    text-align: center;

}


/*
|--------------------------------------------------------------------------
| SCROLLBAR
|--------------------------------------------------------------------------
*/

.color-list::-webkit-scrollbar {

    width: 0.4rem;

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
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .color-panel {

        width: 90vw;

        margin:
            0 auto;

        padding: 1.618rem;

    }


    .color-list {

        max-height: 50vw;

    }

}

</style>