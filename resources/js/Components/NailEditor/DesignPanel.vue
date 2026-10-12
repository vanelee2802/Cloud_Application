<template>
    <div class="design-panel">

        <!-- ========================================= -->
        <!-- KOPFZEILE                                 -->
        <!-- Ebene 1: DESIGNS 🔍                       -->
        <!-- Ebene 2: ← MUSTER 🔍                      -->
        <!-- ========================================= -->

        <div class="design-header">

            <button
                v-if="aktiveKategorie"
                type="button"
                class="back-button"
                aria-label="Zurück zu den Kategorien"
                @click="zurueck"
            ></button>

            <h3>
                {{ aktiveKategorie ? aktiveKategorie.toUpperCase() : 'DESIGNS' }}
            </h3>

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
                    :placeholder="aktiveKategorie ? `In ${aktiveKategorie} suchen` : 'Alle Designs durchsuchen'"
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
        <!-- LADEN / FEHLER                            -->
        <!-- ========================================= -->

        <p
            v-if="laden"
            class="status"
        >
            Designs werden geladen …
        </p>

        <p
            v-else-if="fehler"
            class="status"
        >
            Designs konnten nicht geladen werden.
            <button
                type="button"
                class="retry"
                @click="ladeDesigns"
            >
                Erneut versuchen
            </button>
        </p>


        <!-- ========================================= -->
        <!-- EBENE 1: KATEGORIEN                       -->
        <!-- (nur ohne aktive Suche)                   -->
        <!-- ========================================= -->

        <div
            v-else-if="zeigeKategorien"
            class="design-list"
        >

            <button
                v-for="kategorie in kategorien"
                :key="kategorie.name"
                type="button"
                class="design-item"
                :title="kategorie.name"
                @click="oeffneKategorie(kategorie.name)"
            >

                <span class="press-on-wrap">
                    <span
                        class="press-on"
                        :style="{ backgroundImage: `url('${kategorie.vorschau}')` }"
                    ></span>
                   
                </span>

                <span class="label">
                    {{ kategorie.name }}
                </span>

            </button>

        </div>


        <!-- ========================================= -->
        <!-- EBENE 2 / SUCHERGEBNISSE: DESIGNS         -->
        <!-- ========================================= -->

        <div
            v-else-if="sichtbareDesigns.length"
            class="design-list"
        >

            <button
                v-for="design in sichtbareDesigns"
                :key="design.id"
                type="button"
                class="design-item"
                :class="{ active: ausgewaehltId === design.id }"
                :aria-pressed="ausgewaehltId === design.id"
                :title="`${design.name} · ${formatPreis(design.price_per_nail)}`"
                @click="selectDesign(design)"
            >

                <span class="press-on-wrap">
                    <span
                        class="press-on"
                        :style="{ backgroundImage: `url('${design.image_path}')` }"
                    ></span>
                </span>

                <span class="label">
                    {{ design.name }}
                </span>

            </button>

        </div>

        <p
            v-else
            class="status"
        >
            Kein Design gefunden
        </p>

    </div>
</template>


<script setup>

import { ref, computed, nextTick, onMounted } from 'vue'

const emit = defineEmits([
    'design-selected'
])


// =========================================
// DESIGNS AUS DER DATENBANK LADEN
// =========================================

const designs = ref([])
const laden   = ref(true)
const fehler  = ref(false)


async function ladeDesigns() {

    laden.value  = true
    fehler.value = false

    try {

        const response = await fetch('/design-elements?patterns=1', {
            headers: { Accept: 'application/json' },
        })

        if (!response.ok) {
            throw new Error(response.status)
        }

        designs.value = await response.json()

    } catch (error) {

        console.error('Designs laden fehlgeschlagen:', error)

        fehler.value = true

    } finally {

        laden.value = false

    }

}

onMounted(ladeDesigns)


// =========================================
// KATEGORIEN (Ebene 1)
// =========================================

// Feste Reihenfolge für bekannte Kategorien, neue kommen alphabetisch dahinter
const REIHENFOLGE = ['French', 'Muster', 'Motive', 'Nail Art', 'Ombre', 'Glitzer']

const kategorien = computed(() => {

    const gruppen = {}

    for (const design of designs.value) {

        if (!gruppen[design.category]) {
            gruppen[design.category] = []
        }

        gruppen[design.category].push(design)

    }

    return Object.entries(gruppen)
        .map(([name, liste]) => ({
            name,
            anzahl:  liste.length,
            vorschau: liste[0].image_path,
        }))
        .sort((a, b) => {

            const ia = REIHENFOLGE.indexOf(a.name)
            const ib = REIHENFOLGE.indexOf(b.name)

            if (ia !== -1 && ib !== -1) return ia - ib
            if (ia !== -1) return -1
            if (ib !== -1) return 1

            return a.name.localeCompare(b.name, 'de')

        })

})


// =========================================
// NAVIGATION ZWISCHEN DEN EBENEN
// =========================================

const aktiveKategorie = ref(null)

function oeffneKategorie(name) {

    aktiveKategorie.value = name

    suche.value = ''

}

function zurueck() {

    aktiveKategorie.value = null

    suche.value = ''

}


// =========================================
// SUCHE
// Ebene 1: über ALLE Designs
// Ebene 2: nur in der aktuellen Kategorie
// =========================================

const showSearch  = ref(false)
const suche       = ref('')
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


// Groß-/Kleinschreibung und Akzente egal ("ombre" findet "Ombré")
function normalisieren(text) {

    return String(text ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()

}

function passtZurSuche(design, begriff) {

    const texte = [
        design.name,
        design.category,
        ...(design.tags ?? []).map((tag) => tag.name),
    ]

    return texte.some((text) => normalisieren(text).includes(begriff))

}


// Kategorien nur zeigen, wenn keine Kategorie offen ist UND nicht gesucht wird
const zeigeKategorien = computed(() =>
    !aktiveKategorie.value && !normalisieren(suche.value)
)

const sichtbareDesigns = computed(() => {

    const begriff = normalisieren(suche.value)

    let liste = designs.value

    if (aktiveKategorie.value) {
        liste = liste.filter((design) => design.category === aktiveKategorie.value)
    }

    if (begriff) {
        liste = liste.filter((design) => passtZurSuche(design, begriff))
    }

    return liste

})


// =========================================
// AUSWAHL
// =========================================

const ausgewaehltId = ref(null)

function selectDesign(design) {

    ausgewaehltId.value = design.id

    // Ganzes Objekt weitergeben: id, name, image_path, price_per_nail, …
    emit('design-selected', design)

}


function formatPreis(preis) {

    return Number(preis).toLocaleString('de-DE', {
        style: 'currency',
        currency: 'EUR',
    }) + ' / Nagel'

}

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| DESIGN PANEL
|--------------------------------------------------------------------------
*/

.design-panel {

    --bordeaux: #7d1024;

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

    color: var(--bordeaux);

    font-family: Georgia, serif;

    font-size: clamp(0.85rem, 1vw, 1.1rem);

    font-weight: 400;

    letter-spacing: 0.08em;

    white-space: nowrap;

}


/*
|--------------------------------------------------------------------------
| ICON-BUTTONS (Zurück + Lupe)
|--------------------------------------------------------------------------
*/

.back-button,
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

.back-button::before,
.search-toggle::before {

    content: "";

    width: 1rem;

    height: 1rem;

    background: var(--bordeaux);

}

.back-button::before {

    -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M15 5l-7 7 7 7'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M15 5l-7 7 7 7'/%3E%3C/svg%3E") center / contain no-repeat;

}

.search-toggle::before {

    -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-4-4'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-4-4'/%3E%3C/svg%3E") center / contain no-repeat;

}

.back-button {

    margin-left: -0.5rem;

}

.back-button:hover,
.search-toggle:hover,
.search-toggle.is-open {

    background: #f3e5e8;

}

.back-button:focus-visible,
.search-toggle:focus-visible {

    outline: 2px solid var(--bordeaux);

    outline-offset: 1px;

}


/*
|--------------------------------------------------------------------------
| SUCHFELD
|--------------------------------------------------------------------------
*/

.design-search {

    position: relative;

    flex: 0 1 13rem;

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

    border-color: var(--bordeaux);

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

    color: var(--bordeaux);

}


/*
|--------------------------------------------------------------------------
| LISTE (WAAGERECHT)
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

.design-list::-webkit-scrollbar-thumb {

    background: #d8bcc2;

    border-radius: 1rem;

}


/*
|--------------------------------------------------------------------------
| KACHEL (Kategorie oder Design)
|--------------------------------------------------------------------------
*/

.design-item {

    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 0.4rem;

    flex-shrink: 0;

    width: clamp(3.8rem, 4.6vw, 4.8rem);

    padding: 0.4rem 0.2rem;

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

    color: var(--bordeaux);

}

.design-item:focus {

    outline: none;

}

.design-item:focus-visible {

    outline: 2px solid var(--bordeaux);

    outline-offset: 1px;

    background: #f8f0f2;

}

.design-item.active {

    background: #f8f0f2;

    color: var(--bordeaux);

    box-shadow: inset 0 0 0 1px var(--bordeaux);

}

.label {

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
| PRESS-ON-VORSCHAU
|--------------------------------------------------------------------------
| Das Musterbild wird live mit einer Mandel-Maske zugeschnitten.
| Darüber: Volumen (Rand dunkler) und Glanzlicht.
| Der Schatten sitzt auf dem Wrapper, weil die Maske ihn sonst abschneidet.
*/

.press-on-wrap {

    position: relative;

    display: block;

    width: clamp(2rem, 2.4vw, 2.6rem);

    height: clamp(3rem, 3.6vw, 3.9rem);

    filter: drop-shadow(0 2px 2.5px rgba(0, 0, 0, 0.2));

    transition: transform 0.2s ease;

}

.design-item:hover .press-on-wrap {

    transform: translateY(-2px) scale(1.04);

}

.press-on {

    position: absolute;

    inset: 0;

    /* Nude als Grundfarbe für transparente PNG-Muster */
    background-color: #f3dcd0;

    background-size: cover;

    background-position: center top;

    -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 160'%3E%3Cpath d='M50 2C66 6 92 36 92 74V136Q92 158 50 158Q8 158 8 136V74C8 36 34 6 50 2Z'/%3E%3C/svg%3E") center / 100% 100% no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 160'%3E%3Cpath d='M50 2C66 6 92 36 92 74V136Q92 158 50 158Q8 158 8 136V74C8 36 34 6 50 2Z'/%3E%3C/svg%3E") center / 100% 100% no-repeat;

}

/* Volumen: Rand leicht abgedunkelt */
.press-on::before {

    content: "";

    position: absolute;

    inset: 0;

    background: radial-gradient(
        ellipse 70% 65% at 45% 42%,
        transparent 55%,
        rgba(0, 0, 0, 0.22) 100%
    );

}

/* Glanzlicht */
.press-on::after {

    content: "";

    position: absolute;

    inset: 0;

    background:
        radial-gradient(ellipse 6% 10% at 64% 16%, rgba(255, 255, 255, 0.85), transparent 100%),
        linear-gradient(100deg, transparent 18%, rgba(255, 255, 255, 0.55) 28%, transparent 40%);

}



/*
|--------------------------------------------------------------------------
| STATUS (Laden, Fehler, keine Treffer)
|--------------------------------------------------------------------------
*/

.status {

    align-self: center;

    margin: 0;

    color: #a8999d;

    font-size: clamp(0.7rem, 0.8vw, 0.85rem);

    text-align: center;

}

.retry {

    margin-left: 0.4rem;

    padding: 0;

    border: none;

    background: none;

    color: var(--bordeaux);

    font: inherit;

    text-decoration: underline;

    cursor: pointer;

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
    .press-on-wrap,
    .back-button,
    .search-toggle {

        transition: none;

    }

    .design-item:hover .press-on-wrap {

        transform: none;

    }

}

</style>