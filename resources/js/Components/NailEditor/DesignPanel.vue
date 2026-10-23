<template>
    <div class="design-panel">

        <!-- ========================================= -->
        <!-- KOPFZEILE: Überschrift + Lupe + Suchfeld  -->
        <!-- ========================================= -->

        <div class="design-header">

            <h3>DESIGNS</h3>

            <button
                type="button"
                class="search-toggle"
                :class="{ 'is-open': showSearch }"
                :aria-expanded="showSearch"
                aria-label="Designs durchsuchen"
                @click="toggleSearch"
            ></button>

            <div
                v-if="showSearch"
                class="design-search"
            >

                <input
                    ref="searchInput"
                    v-model="suche"
                    type="search"
                    placeholder="Design suchen"
                    aria-label="Design suchen"
                    @keydown.esc="closeSearch"
                >

                <button
                    v-if="suche"
                    type="button"
                    class="design-search__clear"
                    aria-label="Suche leeren"
                    @click="suche = ''"
                >
                    ×
                </button>

            </div>

        </div>


        <!-- ========================================= -->
        <!-- DESIGNS (WAAGERECHT)                      -->
        <!-- ========================================= -->

        <div
            v-if="gefilterteDesigns.length"
            class="design-list"
        >

            <button
                v-for="design in gefilterteDesigns"
                :key="design.name"
                type="button"
                class="design-item"
                :class="{ active: ausgewaehlt === design.name }"
                :aria-pressed="ausgewaehlt === design.name"
                :title="design.name"
                @click="selectDesign(design.name)"
            >

                <img
                    :src="design.icon"
                    alt=""
                >

                <span>
                    {{ design.name }}
                </span>

            </button>

        </div>

        <p
            v-else
            class="no-results"
        >
            Kein Design gefunden
        </p>

    </div>
</template>


<script setup>

import { ref, computed, nextTick } from 'vue'

const emit = defineEmits([
    'design-selected'
])


// =========================================
// AUSWAHL
// =========================================

const ausgewaehlt = ref(null)

function selectDesign(design) {

    ausgewaehlt.value = design

    emit('design-selected', design)

}


// =========================================
// SUCHE
// =========================================

const showSearch = ref(false)

const suche = ref('')

const searchInput = ref(null)


async function toggleSearch() {

    if (showSearch.value) {

        closeSearch()

        return

    }

    showSearch.value = true

    await nextTick()

    searchInput.value?.focus()

}


function closeSearch() {

    showSearch.value = false

    suche.value = ''

}


/*
| Groß-/Kleinschreibung und Umlaute egal
| ("ombre" findet "Ombré")
*/
function normalisieren(text) {

    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()

}


// =========================================
// DESIGNS
// tags = zusätzliche Suchbegriffe
// =========================================

const ICON_PATH = '/images/nail-editor/icon/DesignPanel'

const designs = [

    { name: 'French',  icon: `${ICON_PATH}/kategorie-french.svg`,  tags: ['klassisch', 'spitze', 'weiß', 'tip'] },
    { name: 'Muster',  icon: `${ICON_PATH}/kategorie-motive.svg`,  tags: ['streifen', 'pattern', 'linien'] },
    { name: 'Motive',  icon: `${ICON_PATH}/kategorie-muster.svg`,  tags: ['streifen', 'pattern', 'linien'] },


]


const gefilterteDesigns = computed(() => {

    const begriff = normalisieren(suche.value)

    if (!begriff) {
        return designs
    }

    return designs.filter((design) =>
        [design.name, ...design.tags]
            .some((text) => normalisieren(text).includes(begriff))
    )

})

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| DESIGN PANEL (WAAGERECHT, WIE COLOR-PANEL)
|--------------------------------------------------------------------------
|
|  DESIGNS 🔍 [Suche]
|  [▯]     [▯]     [▯]     [▯]   →
|  French  Glitter Muster  Ombré
*/

.design-panel {

    display: grid;

    grid-template-rows:
        auto
        minmax(0, 1fr);

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


/*
|--------------------------------------------------------------------------
| KOPFZEILE
|--------------------------------------------------------------------------
*/

.design-header {

    display: flex;

    align-items: center;

    gap: 0.3rem;

    min-width: 0;

    min-height: 2rem;

}

.design-header h3 {

    margin: 0;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(0.85rem, 1vw, 1.1rem);

    font-weight: 400;

    letter-spacing: 0.08em;

    white-space: nowrap;

}


/*
|--------------------------------------------------------------------------
| LUPE (gleiches Icon wie im Color-Panel)
|--------------------------------------------------------------------------
*/

.search-toggle {

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    width: 2rem;

    height: 2rem;

    padding: 0;

    border: none;

    border-radius: 50%;

    background: transparent;

    cursor: pointer;

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

.search-toggle:hover,
.search-toggle.is-open {

    background: #f3e5e8;

}

.search-toggle:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 1px;

}


/*
|--------------------------------------------------------------------------
| SUCHFELD (in derselben Zeile)
|--------------------------------------------------------------------------
*/

.design-search {

    position: relative;

    flex: 0 1 12rem;

    min-width: 6rem;

    margin-left: 0.4rem;

}

.design-search input {

    width: 100%;

    padding: 0.4em 1.8em 0.4em 0.8em;

    border: 1px solid #e6dadd;

    border-radius: 0.5rem;

    background: #fbf7f8;

    color: #333333;

    font: inherit;

    font-size: clamp(0.72rem, 0.8vw, 0.85rem);

    outline: none;

    box-sizing: border-box;

}

.design-search input::-webkit-search-cancel-button {

    -webkit-appearance: none;

}

.design-search input::placeholder {

    color: #a8999d;

}

.design-search input:focus {

    background: #ffffff;

    border-color: #7d1024;

}

.design-search__clear {

    position: absolute;

    right: 0.3rem;

    top: 50%;

    width: 1.2rem;

    height: 1.2rem;

    padding: 0;

    transform: translateY(-50%);

    border: none;

    background: transparent;

    color: #a8999d;

    font-size: 1rem;

    line-height: 1;

    cursor: pointer;

}

.design-search__clear:hover {

    color: #7d1024;

}


/*
|--------------------------------------------------------------------------
| DESIGN-LISTE (WAAGERECHT)
|--------------------------------------------------------------------------
*/

.design-list {

    display: flex;

    flex-direction: row;

    align-items: flex-start;

    gap: 0.4rem;

    min-width: 0;

    min-height: 0;

    padding: 0.2rem 0 0.4rem;

    overflow-x: auto;

    overflow-y: hidden;

    scroll-snap-type: x proximity;

    scrollbar-width: thin;

    scrollbar-color: #d8bcc2 transparent;

}

.design-list::-webkit-scrollbar {

    height: 0.3rem;

}

.design-list::-webkit-scrollbar-track {

    background: transparent;

}

.design-list::-webkit-scrollbar-thumb {

    background: #d8bcc2;

    border-radius: 1rem;

}

.design-list::-webkit-scrollbar-thumb:hover {

    background: #7d1024;

}


/*
|--------------------------------------------------------------------------
| EINZELNES DESIGN: Icon oben, Name darunter
|--------------------------------------------------------------------------
*/

.design-item {

    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 0.35rem;

    flex-shrink: 0;

    width: clamp(3.8rem, 4.6vw, 4.8rem);

    padding: 0.35rem 0.2rem;

    border: none;

    border-radius: 0.6rem;

    background: transparent;

    color: #333333;

    text-align: center;

    cursor: pointer;

    scroll-snap-align: start;

    transition: background-color 0.2s ease;

}

.design-item:hover {

    background: #f8f0f2;

    color: #7d1024;

}

.design-item:focus {

    outline: none;

}

.design-item:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 1px;

    background: #f8f0f2;

}

.design-item.active {

    background: #f8f0f2;

    color: #7d1024;

    box-shadow: inset 0 0 0 1px #7d1024;

}


/* Icon (hochkant 48 × 64) */
.design-item img {

    width: clamp(2rem, 2.4vw, 2.6rem);

    height: clamp(2.7rem, 3.2vw, 3.5rem);

    flex-shrink: 0;

    object-fit: contain;

    transition: transform 0.2s ease;

}

.design-item:hover img {

    transform: scale(1.06);

}


/* Name */
.design-item span {

    max-width: 100%;

    overflow: hidden;

    color: inherit;

    font-size: clamp(0.62rem, 0.68vw, 0.75rem);

    line-height: 1.2;

    text-overflow: ellipsis;

    white-space: nowrap;

}


/*
|--------------------------------------------------------------------------
| KEINE ERGEBNISSE
|--------------------------------------------------------------------------
*/

.no-results {

    align-self: center;

    margin: 0;

    color: #a8999d;

    font-size: clamp(0.7rem, 0.8vw, 0.85rem);

    text-align: center;

}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .design-panel {

        height: auto;

        padding: 1rem;

    }

}


/*
|--------------------------------------------------------------------------
| REDUCED MOTION
|--------------------------------------------------------------------------
*/

@media (prefers-reduced-motion: reduce) {

    .design-item,
    .design-item img,
    .search-toggle {

        transition: none;

    }

    .design-item:hover img {

        transform: none;

    }

}

</style>