<template>
    <Teleport to="body">

        <div
            class="modal-overlay"
            @click.self="emit('close')"
        >

            <div
                class="modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="template-modal-title"
            >

                <!-- ========================================= -->
                <!-- KOPF                                      -->
                <!-- ========================================= -->

                <header class="modal-header">

                    <div>
                        <h2 id="template-modal-title">
                            VORLAGEN ENTDECKEN
                        </h2>
                        <p>
                            Wähle ein fertiges Design und passe es danach nach Lust und Laune an.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="close-button"
                        aria-label="Schließen"
                        @click="emit('close')"
                    >
                        ×
                    </button>

                </header>


                <!-- ========================================= -->
                <!-- SUCHE + TAGS                              -->
                <!-- ========================================= -->

                <div class="filter-bar">

                    <div class="search-field">
                        <input
                            ref="searchInput"
                            v-model="suche"
                            type="search"
                            placeholder="Suchen, z. B. herbst, gold, french …"
                            aria-label="Vorlagen suchen"
                        >
                    </div>

                    <div
                        v-if="alleTags.length"
                        class="tag-chips"
                    >

                        <button
                            v-for="tag in alleTags"
                            :key="tag"
                            type="button"
                            class="tag-chip"
                            :class="{ active: aktiverTag === tag }"
                            :aria-pressed="aktiverTag === tag"
                            @click="toggleTag(tag)"
                        >
                            {{ tag }}
                        </button>

                    </div>

                </div>


                <!-- ========================================= -->
                <!-- KARTEN                                    -->
                <!-- ========================================= -->

                <div
                    v-if="gefiltert.length"
                    class="card-grid"
                >

                    <article
                        v-for="template in gefiltert"
                        :key="template.id"
                        class="card"
                        :class="{ active: activeTemplateId === template.id }"
                    >

                        <!-- Vorschau: 5 Nägel wie an einer Hand -->
                        <div class="card-preview">
                            <span
                                v-for="(nail, index) in template.preview"
                                :key="index"
                                class="preview-nail"
                                :class="`preview-nail-${index}`"
                                :style="nailStyle(nail)"
                            ></span>
                        </div>

                        <div class="card-body">

                            <h3>{{ template.name }}</h3>

                            <p
                                v-if="template.description"
                                class="card-description"
                            >
                                {{ template.description }}
                            </p>

                            <div
                                v-if="tagNamen(template).length"
                                class="card-tags"
                            >
                                <span
                                    v-for="tag in tagNamen(template)"
                                    :key="tag"
                                    class="card-tag"
                                >
                                    {{ tag }}
                                </span>
                            </div>

                        </div>

                        <footer class="card-footer">

                            <span class="card-price">
                                {{ template.price > 0 ? euro(template.price) : 'ohne Aufpreis' }}
                            </span>

                            <button
                                type="button"
                                class="apply-button"
                                @click="emit('apply', template)"
                            >
                                Übernehmen
                            </button>

                        </footer>

                    </article>

                </div>

                <div
                    v-else
                    class="empty-state"
                >
                    <strong>Keine Vorlage gefunden</strong>
                    <button
                        type="button"
                        class="reset-link"
                        @click="zuruecksetzen"
                    >
                        Filter zurücksetzen
                    </button>
                </div>

            </div>

        </div>

    </Teleport>
</template>


<script setup>

import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

/*
|--------------------------------------------------------------------------
| POP-UP "VORLAGEN ENTDECKEN"
|--------------------------------------------------------------------------
| templates: [{ id, name, description, tags: [{ name }],
|               preview: [{ hex, image }, … 5×], price }]
|
| Events:
|   apply  → Vorlage übernehmen (Editor legt sie auf die Hand)
|   close  → Fenster schließen
*/

const props = defineProps({
    templates:        { type: Array,  default: () => [] },
    activeTemplateId: { type: Number, default: null },
})

const emit = defineEmits(['apply', 'close'])


// =========================================
// SUCHE + TAGS
// =========================================

const suche       = ref('')
const aktiverTag  = ref(null)
const searchInput = ref(null)

function normalisieren(text) {

    return String(text ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()

}

function tagNamen(template) {

    return (template.tags ?? []).map((tag) => tag.name)

}

// Alle Tags, sortiert nach Häufigkeit
const alleTags = computed(() => {

    const anzahl = new Map()

    for (const template of props.templates) {
        for (const name of tagNamen(template)) {
            anzahl.set(name, (anzahl.get(name) ?? 0) + 1)
        }
    }

    return [...anzahl.entries()]
        .sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0], 'de'))
        .map(([name]) => name)

})

function toggleTag(tag) {

    aktiverTag.value = aktiverTag.value === tag ? null : tag

}

function zuruecksetzen() {

    suche.value = ''

    aktiverTag.value = null

}

const gefiltert = computed(() => {

    const begriff = normalisieren(suche.value)

    return props.templates.filter((template) => {

        const tags = tagNamen(template)

        if (aktiverTag.value && !tags.includes(aktiverTag.value)) {
            return false
        }

        if (!begriff) {
            return true
        }

        return [template.name, template.description, ...tags]
            .some((text) => normalisieren(text).includes(begriff))

    })

})


// =========================================
// DARSTELLUNG
// =========================================

function nailStyle(nail) {

    return {
        backgroundColor: nail?.hex || '#f3dcd0',
        backgroundImage: nail?.image ? `url('${nail.image}')` : 'none',
    }

}

function euro(value) {

    return Number(value ?? 0).toLocaleString('de-DE', {
        style: 'currency',
        currency: 'EUR',
    })

}


// =========================================
// TASTATUR: Esc schließt, Seite scrollt nicht mit
// =========================================

function onKeydown(event) {

    if (event.key === 'Escape') {
        emit('close')
    }

}

onMounted(() => {

    document.addEventListener('keydown', onKeydown)

    document.body.style.overflow = 'hidden'

    searchInput.value?.focus()

})

onBeforeUnmount(() => {

    document.removeEventListener('keydown', onKeydown)

    document.body.style.overflow = ''

})

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| OVERLAY + FENSTER
|--------------------------------------------------------------------------
*/

.modal-overlay {

    position: fixed;

    inset: 0;

    z-index: 2000;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 1.5rem;

    background: rgba(40, 25, 30, 0.45);

}

.modal {

    display: flex;

    flex-direction: column;

    gap: 1.1rem;

    width: min(64rem, 100%);

    max-height: calc(100vh - 3rem);

    padding: 1.5rem 1.75rem;

    background: #ffffff;

    border-radius: 1.1rem;

    box-shadow: 0 20px 50px rgba(40, 25, 30, 0.25);

    box-sizing: border-box;

    overflow: hidden;

}


/*
|--------------------------------------------------------------------------
| KOPF
|--------------------------------------------------------------------------
*/

.modal-header {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 1rem;

}

.modal-header h2 {

    margin: 0;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(1rem, 1.3vw, 1.35rem);

    font-weight: 400;

    letter-spacing: 0.08em;

}

.modal-header p {

    margin: 0.3rem 0 0;

    color: #8a7a80;

    font-size: 0.85rem;

}

.close-button {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 2.2rem;

    height: 2.2rem;

    flex-shrink: 0;

    border: none;

    border-radius: 50%;

    background: #fdf9fa;

    color: #7d1024;

    font-size: 1.5rem;

    line-height: 1;

    cursor: pointer;

}

.close-button:hover {

    background: #f3e5e8;

}

.close-button:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 2px;

}


/*
|--------------------------------------------------------------------------
| SUCHE + TAGS
|--------------------------------------------------------------------------
*/

.filter-bar {

    display: flex;

    flex-direction: column;

    gap: 0.6rem;

}

.search-field input {

    width: 100%;

    padding: 0.6em 0.9em;

    border: 1px solid #e6dadd;

    border-radius: 0.6rem;

    background: #fbf7f8;

    color: #333333;

    font: inherit;

    font-size: 0.9rem;

    outline: none;

    box-sizing: border-box;

}

.search-field input:focus {

    background: #ffffff;

    border-color: #7d1024;

}

.tag-chips {

    display: flex;

    flex-wrap: wrap;

    gap: 0.35rem;

}

.tag-chip {

    padding: 0.3em 0.8em;

    border: 1px solid #e4e4e4;

    border-radius: 100vw;

    background: #ffffff;

    color: #555555;

    font-size: 0.78rem;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease;

}

.tag-chip:hover {

    background: #f8f0f2;

    border-color: #c9aab1;

    color: #7d1024;

}

.tag-chip.active {

    background: #f3e5e8;

    border-color: #7d1024;

    color: #7d1024;

    font-weight: 600;

}


/*
|--------------------------------------------------------------------------
| KARTEN
|--------------------------------------------------------------------------
*/

.card-grid {

    display: grid;

    grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));

    gap: 1rem;

    min-height: 0;

    padding: 0.2rem 0.3rem 0.4rem 0.1rem;

    overflow-y: auto;

    scrollbar-width: thin;

    scrollbar-color: #d8bcc2 transparent;

}

.card {

    display: flex;

    flex-direction: column;

    border: 1px solid rgba(143, 83, 100, 0.12);

    border-radius: 0.9rem;

    background: #fdfbfc;

    overflow: hidden;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;

}

.card:hover {

    border-color: rgba(143, 83, 100, 0.3);

    box-shadow: 0 6px 16px rgba(80, 35, 48, 0.08);

    transform: translateY(-2px);

}

.card.active {

    border-color: #7d1024;

    box-shadow: inset 0 0 0 1px #7d1024;

}


/* Vorschau: 5 Nägel, Mittelfinger am höchsten */
.card-preview {

    display: flex;

    align-items: flex-end;

    justify-content: center;

    gap: 0.45rem;

    height: 6.5rem;

    padding: 0.9rem 0 0.8rem;

    background: linear-gradient(180deg, #f8f0f2, #fdfbfc);

}

.preview-nail {

    display: block;

    width: 1.6rem;

    border: 1px solid rgba(0, 0, 0, 0.1);

    border-radius: 50% 50% 42% 42% / 62% 62% 38% 38%;

    background-size: cover;

    background-position: center top;

    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);

}

.preview-nail-0 { height: 2.6rem; width: 1.5rem; }   /* Daumen */
.preview-nail-1 { height: 3.6rem; }                   /* Zeigefinger */
.preview-nail-2 { height: 4.1rem; }                   /* Mittelfinger */
.preview-nail-3 { height: 3.7rem; }                   /* Ringfinger */
.preview-nail-4 { height: 3rem; width: 1.3rem; }     /* Kleiner Finger */


.card-body {

    display: flex;

    flex-direction: column;

    gap: 0.35rem;

    flex: 1;

    padding: 0.8rem 1rem 0.4rem;

}

.card-body h3 {

    margin: 0;

    color: #4f3540;

    font-size: 0.98rem;

    font-weight: 700;

}

.card-description {

    margin: 0;

    color: #8a7a80;

    font-size: 0.8rem;

    line-height: 1.4;

}

.card-tags {

    display: flex;

    flex-wrap: wrap;

    gap: 0.25rem;

    margin-top: 0.15rem;

}

.card-tag {

    padding: 0.12em 0.55em;

    border-radius: 100vw;

    background: #f3e5e8;

    color: #754354;

    font-size: 0.7rem;

}

.card-footer {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 0.6rem;

    padding: 0.7rem 1rem 0.9rem;

}

.card-price {

    color: #7d1024;

    font-size: 0.85rem;

    font-weight: 600;

}

.apply-button {

    padding: 0.5rem 0.95rem;

    border: none;

    border-radius: 0.55rem;

    background: #7d1024;

    color: #ffffff;

    font: inherit;

    font-size: 0.8rem;

    font-weight: 600;

    cursor: pointer;

    transition: background-color 0.2s ease;

}

.apply-button:hover {

    background: #64101f;

}

.apply-button:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 2px;

}


/*
|--------------------------------------------------------------------------
| KEINE TREFFER
|--------------------------------------------------------------------------
*/

.empty-state {

    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 0.4rem;

    padding: 2.5rem 0;

    color: #8a7a80;

}

.empty-state strong {

    color: #4f3540;

}

.reset-link {

    padding: 0;

    border: none;

    background: none;

    color: #7d1024;

    font: inherit;

    text-decoration: underline;

    cursor: pointer;

}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 600px) {

    .modal-overlay {

        padding: 0.75rem;

    }

    .modal {

        padding: 1.1rem;

        max-height: calc(100vh - 1.5rem);

    }

    .card-grid {

        grid-template-columns: 1fr;

    }

}


/*
|--------------------------------------------------------------------------
| REDUCED MOTION
|--------------------------------------------------------------------------
*/

@media (prefers-reduced-motion: reduce) {

    .card,
    .tag-chip,
    .apply-button {

        transition: none;

    }

    .card:hover {

        transform: none;

    }

}

</style>