<template>
    <pagesLayout>
    <section class="studio-search-page">
        <div class="studio-container">
            <!-- Überschrift -->
            <header class="page-header">
                <p class="eyebrow">{{ t.eyebrow }}</p>
                <h1>{{ t.title }}</h1>
                <p class="subtitle">{{ t.subtitle }}</p>

                <!-- Sprache -->
                <div class="language-switch">
                    <button
                        v-for="lang in languages"
                        :key="lang.code"
                        type="button"
                        :class="{ active: language === lang.code }"
                        :aria-pressed="language === lang.code"
                        @click="language = lang.code"
                    >
                        {{ lang.label }}
                    </button>
                </div>
            </header>

            <!-- Owner-Verwaltung -->
            <section v-if="canManageStudios" class="owner-panel">
                <div class="owner-panel-top">
                    <div>
                        <p class="eyebrow">{{ t.ownerEyebrow }}</p>
                        <h2>{{ t.ownerTitle }}</h2>
                        <p class="owner-description">
                            {{ t.ownerDescription }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="primary-button"
                        @click="openCreateForm"
                    >
                        <span>＋</span>
                        {{ t.addStudio }}
                    </button>
                </div>

                <p class="demo-notice">
                    {{ t.demoNotice }}
                </p>

                <!-- Studio hinzufügen / bearbeiten -->
                <div v-if="showForm" class="studio-form-card">
                    <div class="form-heading">
                        <h3>
                            {{ editingId === null ? t.newStudio : t.editStudio }}
                        </h3>

                        <button
                            type="button"
                            class="close-button"
                            :aria-label="t.cancel"
                            @click="closeForm"
                        >
                            ×
                        </button>
                    </div>

                    <form class="studio-form" @submit.prevent="saveStudio">
                        <div class="form-grid">
                            <div class="form-field">
                                <label for="studio-name">{{ t.name }}</label>
                                <input
                                    id="studio-name"
                                    v-model.trim="draft.name"
                                    required
                                    maxlength="100"
                                    :placeholder="t.namePlaceholder"
                                />
                            </div>

                            <div class="form-field">
                                <label for="studio-city">{{ t.city }}</label>
                                <input
                                    id="studio-city"
                                    v-model.trim="draft.city"
                                    required
                                    maxlength="100"
                                    :placeholder="t.cityPlaceholder"
                                />
                            </div>

                            <div class="form-field">
                                <label for="studio-postcode">{{ t.postcode }}</label>
                                <input
                                    id="studio-postcode"
                                    v-model.trim="draft.postcode"
                                    maxlength="15"
                                    :placeholder="t.postcodePlaceholder"
                                />
                            </div>

                            <div class="form-field">
                                <label for="studio-rating">{{ t.rating }}</label>
                                <input
                                    id="studio-rating"
                                    v-model.number="draft.rating"
                                    type="number"
                                    min="0"
                                    max="5"
                                    step="0.1"
                                    required
                                />
                            </div>

                            <div class="form-field full-width">
                                <label for="studio-address">{{ t.address }}</label>
                                <input
                                    id="studio-address"
                                    v-model.trim="draft.address"
                                    maxlength="200"
                                    :placeholder="t.addressPlaceholder"
                                />
                            </div>

                            <div class="form-field">
                                <label for="studio-phone">{{ t.phone }}</label>
                                <input
                                    id="studio-phone"
                                    v-model.trim="draft.phone"
                                    type="tel"
                                    maxlength="40"
                                    :placeholder="t.phonePlaceholder"
                                />
                            </div>

                            <div class="form-field">
                                <label for="studio-email">{{ t.email }}</label>
                                <input
                                    id="studio-email"
                                    v-model.trim="draft.email"
                                    type="email"
                                    maxlength="150"
                                    :placeholder="t.emailPlaceholder"
                                />
                            </div>

                            <div class="form-field full-width">
                                <label for="studio-website">{{ t.website }}</label>
                                <input
                                    id="studio-website"
                                    v-model.trim="draft.website"
                                    type="url"
                                    maxlength="250"
                                    placeholder="https://..."
                                />
                            </div>

                            <div class="form-field full-width">
                                <label for="studio-description">
                                    {{ t.descriptionLabel }}
                                </label>
                                <textarea
                                    id="studio-description"
                                    v-model.trim="draft.description.de"
                                    rows="3"
                                    maxlength="600"
                                    :placeholder="t.descriptionPlaceholder"
                                ></textarea>
                            </div>

                            <div class="form-field full-width">
                                <label for="studio-description-en">
                                    {{ t.descriptionEnLabel }}
                                </label>
                                <textarea
                                    id="studio-description-en"
                                    v-model.trim="draft.description.en"
                                    rows="3"
                                    maxlength="600"
                                    :placeholder="t.descriptionEnPlaceholder"
                                ></textarea>
                            </div>
                        </div>

                        <label class="checkbox-row">
                            <input
                                v-model="draft.published"
                                type="checkbox"
                            />
                            <span>{{ t.visibleInSearch }}</span>
                        </label>

                        <div class="form-actions">
                            <button type="submit" class="primary-button">
                                {{ t.save }}
                            </button>

                            <button
                                type="button"
                                class="secondary-button"
                                @click="closeForm"
                            >
                                {{ t.cancel }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Owner-Liste -->
                <div class="owner-studio-list">
                    <h3>{{ t.manageTitle }}</h3>

                    <div
                        v-for="studio in studios"
                        :key="studio.id"
                        class="owner-studio-row"
                    >
                        <div class="owner-studio-info">
                            <strong>{{ studio.name }}</strong>
                            <span>
                                {{ studio.city }}
                                <template v-if="studio.postcode">
                                    · {{ studio.postcode }}
                                </template>
                            </span>
                            <span
                                class="status-pill"
                                :class="{ hidden: !studio.published }"
                            >
                                {{
                                    studio.published
                                        ? t.visibleStatus
                                        : t.hiddenStatus
                                }}
                            </span>
                        </div>

                        <div class="owner-actions">
                            <button
                                type="button"
                                class="small-button"
                                @click="togglePublished(studio)"
                            >
                                {{
                                    studio.published
                                        ? t.hide
                                        : t.show
                                }}
                            </button>

                            <button
                                type="button"
                                class="small-button"
                                @click="openEditForm(studio)"
                            >
                                {{ t.edit }}
                            </button>

                            <button
                                type="button"
                                class="small-button delete-button"
                                @click="deleteStudio(studio.id)"
                            >
                                {{ t.delete }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Suche -->
            <div class="search-area">
                <label class="search-box">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="21"
                        height="21"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-4-4" />
                    </svg>

                    <input
                        v-model="searchQuery"
                        type="search"
                        :placeholder="t.searchPlaceholder"
                        :aria-label="t.searchPlaceholder"
                    />

                    <button
                        v-if="searchQuery"
                        type="button"
                        class="clear-search"
                        :aria-label="t.clearSearch"
                        @click="searchQuery = ''"
                    >
                        ×
                    </button>
                </label>

                <div class="results-meta">
                    <span>{{ t.resultsLabel }}</span>
                    <strong>{{ filteredStudios.length }}</strong>
                </div>
            </div>

            <!-- Studios -->
            <div v-if="filteredStudios.length" class="studio-grid">
                <article
                    v-for="studio in filteredStudios"
                    :key="studio.id"
                    class="studio-card"
                >
                    <div class="studio-card-top">
                        <div class="studio-avatar">
                            {{ studioInitials(studio.name) }}
                        </div>

                        <span class="rating-badge">
                            <span class="star">★</span>
                            {{ studio.rating.toFixed(1) }}
                        </span>
                    </div>

                    <h2>{{ studio.name }}</h2>

                    <div class="studio-location">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path
                                d="M20 10c0 5-8 12-8 12S4 15 4 10
                                a8 8 0 1 1 16 0Z"
                            />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>

                        <span>
                            {{ studio.city }}
                            <template v-if="studio.postcode">
                                · {{ studio.postcode }}
                            </template>
                        </span>
                    </div>

                    <p v-if="localizedDescription(studio)" class="studio-description">
                        {{ localizedDescription(studio) }}
                    </p>

                    <div class="studio-card-footer">
                        <button
                            type="button"
                            class="select-button"
                            @click="selectStudio(studio)"
                        >
                            {{ t.selectStudio }}
                            <span aria-hidden="true">→</span>
                        </button>

                        <a
                            v-if="safeWebsite(studio.website)"
                            :href="safeWebsite(studio.website)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="website-link"
                            :aria-label="`${t.website}: ${studio.name}`"
                        >
                            ↗
                        </a>
                    </div>
                </article>
            </div>

            <!-- Keine Ergebnisse -->
            <div v-else class="empty-state">
                <div class="empty-icon">⌕</div>
                <h2>{{ t.noResults }}</h2>
                <p>{{ t.noResultsDescription }}</p>

                <button
                    type="button"
                    class="secondary-button"
                    @click="searchQuery = ''"
                >
                    {{ t.resetSearch }}
                </button>
            </div>

            <!-- Auswahl-Dialog -->
            <div
                v-if="selectedStudio"
                class="modal-backdrop"
                @click.self="selectedStudio = null"
            >
                <section
                    class="studio-modal"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="'selected-studio-title'"
                >
                    <button
                        type="button"
                        class="modal-close"
                        :aria-label="t.close"
                        @click="selectedStudio = null"
                    >
                        ×
                    </button>

                    <div class="studio-avatar modal-avatar">
                        {{ studioInitials(selectedStudio.name) }}
                    </div>

                    <p class="eyebrow">{{ t.selectedEyebrow }}</p>
                    <h2 id="selected-studio-title">
                        {{ selectedStudio.name }}
                    </h2>

                    <div class="modal-rating">
                        <span class="star">★</span>
                        {{ selectedStudio.rating.toFixed(1) }}
                    </div>

                    <p class="modal-location">
                        {{ selectedStudio.address || selectedStudio.city }}
                        <template v-if="selectedStudio.postcode">
                            · {{ selectedStudio.postcode }}
                        </template>
                    </p>

                    <p
                        v-if="localizedDescription(selectedStudio)"
                        class="modal-description"
                    >
                        {{ localizedDescription(selectedStudio) }}
                    </p>

                    <div class="modal-actions">
                        <a
                            v-if="safeWebsite(selectedStudio.website)"
                            :href="safeWebsite(selectedStudio.website)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="primary-button"
                        >
                            {{ t.visitWebsite }} ↗
                        </a>

                        <a
                            v-if="selectedStudio.email"
                            :href="`mailto:${selectedStudio.email}`"
                            class="secondary-button"
                        >
                            {{ t.emailStudio }}
                        </a>

                        <a
                            v-if="selectedStudio.phone"
                            :href="`tel:${selectedStudio.phone.replace(/[^\d+]/g, '')}`"
                            class="secondary-button"
                        >
                            {{ t.callStudio }}
                        </a>
                    </div>

                    <p class="modal-note">
                        {{ t.selectionNote }}
                    </p>
                </section>
            </div>

            <footer class="studio-footer">
                <span>✿</span>
                <p>{{ t.footer }}</p>
            </footer>
        </div>
    </section>
</pagesLayout>
</template>

<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue'
import { computed, reactive, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

/*
|--------------------------------------------------------------------------
| App-Owner-Berechtigung
|--------------------------------------------------------------------------
| Der Prototyp zeigt die Verwaltung nur, wenn die Inertia-Props eine
| passende Rolle enthalten.
|
| Unterstützte Rollen in diesem Beispiel:
| owner, app_owner, admin
|
| Passe die Rollen an dein tatsächliches Berechtigungsmodell an.
| Diese Prüfung schützt keine Daten und ersetzt keine Backend-Autorisierung.
*/

const canManageStudios = computed(() => {
    const role = page.props.auth?.user?.role

    return ['owner', 'app_owner', 'admin'].includes(role)
})

/*
|--------------------------------------------------------------------------
| Sprache
|--------------------------------------------------------------------------
*/

const languages = [
    { code: 'de', label: 'DE' },
    { code: 'en', label: 'EN' },
]

const language = ref('de')
const searchQuery = ref('')
const selectedStudio = ref(null)

/*
|--------------------------------------------------------------------------
| Statische Demo-Daten
|--------------------------------------------------------------------------
| Die Einträge sind Beispieldaten und keine geprüften Geschäftseinträge.
| Später können sie durch eine Laravel-API ersetzt werden.
*/

const studios = reactive([
    {
        id: 1,
        name: 'Nail Studio Hannover',
        city: 'Hannover',
        postcode: '30159',
        address: '',
        rating: 4.8,
        phone: '',
        email: '',
        website: '',
        description: {
            de: 'Ein Beispielstudio für den Suchseiten-Prototyp.',
            en: 'A sample studio for this search page prototype.',
        },
        published: true,
    },
    {
        id: 2,
        name: 'Beauty Nails',
        city: 'Hannover',
        postcode: '30161',
        address: '',
        rating: 4.6,
        phone: '',
        email: '',
        website: '',
        description: {
            de: 'Beispiel für ein weiteres Nagelstudio.',
            en: 'An example of another nail studio.',
        },
        published: true,
    },
    {
        id: 3,
        name: 'Nail Lounge',
        city: 'Hannover',
        postcode: '30169',
        address: '',
        rating: 4.7,
        phone: '',
        email: '',
        website: '',
        description: {
            de: 'Platzhalter für einen weiteren Studioeintrag.',
            en: 'Placeholder for another studio listing.',
        },
        published: true,
    },
])

/*
|--------------------------------------------------------------------------
| Übersetzungen
|--------------------------------------------------------------------------
*/

const translations = {
    de: {
        eyebrow: 'Dein nächster Beauty-Termin',
        title: 'Wähle dein Nagelstudio',
        subtitle: 'Finde dein passendes Nagelstudio in deiner Nähe.',
        ownerEyebrow: 'Verwaltung',
        ownerTitle: 'Studios verwalten',
        ownerDescription:
            'Füge Studios hinzu, bearbeite Angaben oder blende Einträge aus der Suche aus.',
        addStudio: 'Studio hinzufügen',
        demoNotice:
            'Frontend-Prototyp: Änderungen werden nicht dauerhaft gespeichert. Die Studios sind Beispieldaten.',
        newStudio: 'Neues Studio',
        editStudio: 'Studio bearbeiten',
        name: 'Studio-Name',
        namePlaceholder: 'z. B. Beauty Nails',
        city: 'Ort',
        cityPlaceholder: 'z. B. Hannover',
        postcode: 'Postleitzahl',
        postcodePlaceholder: 'z. B. 30159',
        rating: 'Bewertung',
        address: 'Adresse',
        addressPlaceholder: 'Straße und Hausnummer',
        phone: 'Telefon',
        phonePlaceholder: '+49 ...',
        email: 'E-Mail',
        emailPlaceholder: 'studio@example.de',
        website: 'Website',
        descriptionLabel: 'Beschreibung (Deutsch)',
        descriptionPlaceholder: 'Kurze Beschreibung des Studios',
        descriptionEnLabel: 'Beschreibung (Englisch)',
        descriptionEnPlaceholder: 'Optional: English description',
        visibleInSearch: 'In der öffentlichen Suche anzeigen',
        save: 'Speichern',
        cancel: 'Abbrechen',
        manageTitle: 'Alle Studios',
        visibleStatus: 'Sichtbar',
        hiddenStatus: 'Ausgeblendet',
        hide: 'Ausblenden',
        show: 'Anzeigen',
        edit: 'Bearbeiten',
        delete: 'Löschen',
        searchPlaceholder: 'Studio, Ort oder PLZ suchen...',
        clearSearch: 'Suche leeren',
        resultsLabel: 'Gefundene Studios',
        selectStudio: 'Studio auswählen',
        noResults: 'Keine Studios gefunden',
        noResultsDescription:
            'Versuche einen anderen Suchbegriff oder entferne die Suche.',
        resetSearch: 'Suche zurücksetzen',
        selectedEyebrow: 'Dein ausgewähltes Studio',
        visitWebsite: 'Website besuchen',
        emailStudio: 'E-Mail schreiben',
        callStudio: 'Anrufen',
        selectionNote:
            'Die Auswahl ist momentan eine Vorschau. Eine echte Buchung oder dauerhafte Studiozuordnung ist noch nicht verbunden.',
        close: 'Schließen',
        footer: 'Finde den passenden Ort für dein nächstes Nageldesign.',
    },

    en: {
        eyebrow: 'Your next beauty appointment',
        title: 'Choose your nail studio',
        subtitle: 'Find a nail studio that suits you nearby.',
        ownerEyebrow: 'Management',
        ownerTitle: 'Manage Studios',
        ownerDescription:
            'Add studios, edit their details or hide listings from search results.',
        addStudio: 'Add Studio',
        demoNotice:
            'Frontend prototype: changes are not permanently saved. Studios are sample data.',
        newStudio: 'New Studio',
        editStudio: 'Edit Studio',
        name: 'Studio Name',
        namePlaceholder: 'e.g. Beauty Nails',
        city: 'City',
        cityPlaceholder: 'e.g. Hanover',
        postcode: 'Postal Code',
        postcodePlaceholder: 'e.g. 30159',
        rating: 'Rating',
        address: 'Address',
        addressPlaceholder: 'Street and number',
        phone: 'Phone',
        phonePlaceholder: '+49 ...',
        email: 'Email',
        emailPlaceholder: 'studio@example.com',
        website: 'Website',
        descriptionLabel: 'Description (German)',
        descriptionPlaceholder: 'Short studio description',
        descriptionEnLabel: 'Description (English)',
        descriptionEnPlaceholder: 'Optional English description',
        visibleInSearch: 'Show in public search',
        save: 'Save',
        cancel: 'Cancel',
        manageTitle: 'All Studios',
        visibleStatus: 'Visible',
        hiddenStatus: 'Hidden',
        hide: 'Hide',
        show: 'Show',
        edit: 'Edit',
        delete: 'Delete',
        searchPlaceholder: 'Search by studio, city or postal code...',
        clearSearch: 'Clear search',
        resultsLabel: 'Studios found',
        selectStudio: 'Select studio',
        noResults: 'No studios found',
        noResultsDescription:
            'Try another search term or clear your search.',
        resetSearch: 'Reset search',
        selectedEyebrow: 'Your selected studio',
        visitWebsite: 'Visit website',
        emailStudio: 'Send email',
        callStudio: 'Call studio',
        selectionNote:
            'This selection is currently a preview. Real booking and persistent studio assignment are not connected yet.',
        close: 'Close',
        footer: 'Find the right place for your next nail design.',
    },
}

const t = computed(() => translations[language.value] ?? translations.de)

/*
|--------------------------------------------------------------------------
| Suche
|--------------------------------------------------------------------------
| Sucht unabhängig von Groß-/Kleinschreibung nach Name, Ort und PLZ.
*/

const filteredStudios = computed(() => {
    const query = searchQuery.value.trim().toLocaleLowerCase()

    return studios.filter(studio => {
        if (!studio.published && !canManageStudios.value) {
            return false
        }

        if (!query) return true

        const searchableText = [
            studio.name,
            studio.city,
            studio.postcode,
            studio.address,
        ]
            .filter(Boolean)
            .join(' ')
            .toLocaleLowerCase()

        return searchableText.includes(query)
    })
})

function localizedDescription(studio) {
    return (
        studio.description?.[language.value] ||
        studio.description?.de ||
        ''
    )
}

function studioInitials(name) {
    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase()
}

function safeWebsite(value) {
    if (!value) return ''

    try {
        const url = new URL(value)

        if (url.protocol !== 'https:' && url.protocol !== 'http:') {
            return ''
        }

        return url.href
    } catch {
        return ''
    }
}

/*
|--------------------------------------------------------------------------
| Studio auswählen
|--------------------------------------------------------------------------
| Aktuell öffnet sich ein Detail-Dialog.
| Später kann hier die Studio-ID an die Terminbuchung übergeben werden.
*/

function selectStudio(studio) {
    selectedStudio.value = studio
}

/*
|--------------------------------------------------------------------------
| Owner-Editor
|--------------------------------------------------------------------------
*/

const showForm = ref(false)
const editingId = ref(null)

let nextId = Math.max(0, ...studios.map(studio => studio.id)) + 1

function emptyDraft() {
    return {
        name: '',
        city: '',
        postcode: '',
        address: '',
        rating: 5,
        phone: '',
        email: '',
        website: '',
        description: {
            de: '',
            en: '',
        },
        published: true,
    }
}

const draft = reactive(emptyDraft())

function resetDraft() {
    Object.assign(draft, emptyDraft())
    draft.description = { de: '', en: '' }
}

function openCreateForm() {
    if (!canManageStudios.value) return

    editingId.value = null
    resetDraft()
    showForm.value = true
}

function openEditForm(studio) {
    if (!canManageStudios.value) return

    editingId.value = studio.id

    Object.assign(draft, {
        name: studio.name,
        city: studio.city,
        postcode: studio.postcode,
        address: studio.address,
        rating: studio.rating,
        phone: studio.phone,
        email: studio.email,
        website: studio.website,
        description: {
            de: studio.description?.de ?? '',
            en: studio.description?.en ?? '',
        },
        published: studio.published,
    })

    showForm.value = true
}

function closeForm() {
    showForm.value = false
    editingId.value = null
    resetDraft()
}

function saveStudio() {
    if (!canManageStudios.value) return

    const name = draft.name.trim()
    const city = draft.city.trim()
    const rating = Number(draft.rating)

    if (
        !name ||
        !city ||
        !Number.isFinite(rating) ||
        rating < 0 ||
        rating > 5
    ) {
        return
    }

    const data = {
        name,
        city,
        postcode: draft.postcode.trim(),
        address: draft.address.trim(),
        rating,
        phone: draft.phone.trim(),
        email: draft.email.trim(),
        website: safeWebsite(draft.website.trim()),
        description: {
            de: draft.description.de.trim(),
            en: draft.description.en.trim(),
        },
        published: draft.published,
    }

    if (editingId.value !== null) {
        const existing = studios.find(
            studio => studio.id === editingId.value
        )

        if (existing) {
            Object.assign(existing, data)
        }
    } else {
        studios.push({
            id: nextId++,
            ...data,
        })
    }

    closeForm()
}

function togglePublished(studio) {
    if (!canManageStudios.value) return

    studio.published = !studio.published

    if (
        selectedStudio.value?.id === studio.id &&
        !studio.published
    ) {
        selectedStudio.value = null
    }
}

function deleteStudio(id) {
    if (!canManageStudios.value) return

    const index = studios.findIndex(studio => studio.id === id)

    if (index !== -1) {
        studios.splice(index, 1)
    }

    if (selectedStudio.value?.id === id) {
        selectedStudio.value = null
    }

    if (editingId.value === id) {
        closeForm()
    }
}
</script>

<style scoped>
.studio-search-page {
    min-height: 100vh;
    padding: 70px 22px 80px;
    color: #3f3035;
    background: #fff;
    font-family: inherit;
}

.studio-container {
    max-width: 940px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 34px;
    text-align: center;
}

.eyebrow {
    margin: 0 0 12px;
    color: #9b6373;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.19em;
    text-transform: uppercase;
}

.page-header h1 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(30px, 4vw, 38px);
    font-weight: 500;
    line-height: 1.3;
}

.subtitle {
    margin: 12px 0 20px;
    color: #8d7880;
    font-size: 14px;
    line-height: 1.7;
}

.language-switch {
    display: inline-flex;
    gap: 4px;
    padding: 4px;
    background: #f8eff1;
    border: 1px solid #efdee2;
    border-radius: 999px;
}

.language-switch button {
    min-width: 48px;
    padding: 7px 13px;
    color: #805b65;
    background: transparent;
    border: 0;
    border-radius: 999px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 700;
}

.language-switch button.active {
    color: #fff;
    background: #8f5364;
}

/* Owner */

.owner-panel {
    margin-bottom: 30px;
    padding: 23px;
    background: #fffafb;
    border: 1px solid #ead2d9;
    border-radius: 16px;
}

.owner-panel-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.owner-panel h2,
.form-heading h3,
.owner-studio-list h3 {
    margin: 0 0 8px;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 23px;
    font-weight: 500;
}

.owner-description {
    margin: 0;
    color: #826b71;
    font-size: 13px;
    line-height: 1.7;
}

.demo-notice {
    margin: 17px 0 0;
    padding: 11px 13px;
    color: #805b65;
    background: #f8ecef;
    border-radius: 9px;
    font-size: 12px;
    line-height: 1.7;
}

.primary-button,
.secondary-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 15px;
    border: 1px solid #8f5364;
    border-radius: 9px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.primary-button {
    color: #fff;
    background: #8f5364;
}

.primary-button:hover {
    background: #754353;
}

.secondary-button {
    color: #754353;
    background: #fff;
    border-color: #e5cbd2;
}

.secondary-button:hover {
    background: #fbf0f3;
}

/* Form */

.studio-form-card {
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #efdee2;
    border-radius: 13px;
}

.form-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 17px;
}

.close-button {
    width: 32px;
    height: 32px;
    color: #754353;
    background: #f8ecef;
    border: 0;
    border-radius: 8px;
    cursor: pointer;
    font-size: 22px;
}

.studio-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.form-field {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 7px;
}

.form-field.full-width {
    grid-column: 1 / -1;
}

.form-field label {
    color: #754353;
    font-size: 12px;
    font-weight: 700;
}

.form-field input,
.form-field textarea {
    width: 100%;
    min-width: 0;
    padding: 11px 12px;
    color: #403138;
    background: #fff;
    border: 1px solid #dfd4d7;
    border-radius: 8px;
    outline: none;
    font: inherit;
    font-size: 13px;
}

.form-field textarea {
    resize: vertical;
    line-height: 1.7;
}

.form-field input:focus,
.form-field textarea:focus {
    border-color: #8f5364;
    box-shadow: 0 0 0 3px rgba(143, 83, 100, 0.09);
}

.checkbox-row {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #725f64;
    font-size: 13px;
    cursor: pointer;
}

.checkbox-row input {
    accent-color: #8f5364;
}

.form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}

/* Owner-Liste */

.owner-studio-list {
    margin-top: 24px;
}

.owner-studio-list h3 {
    font-size: 20px;
}

.owner-studio-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 13px 0;
    border-top: 1px solid #efdee2;
}

.owner-studio-info {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 12px;
}

.owner-studio-info strong {
    color: #754353;
    font-size: 13px;
}

.owner-studio-info > span:not(.status-pill) {
    color: #8b767d;
    font-size: 12px;
}

.status-pill {
    padding: 4px 7px;
    color: #754353;
    background: #f4e5e9;
    border-radius: 6px;
    font-size: 10px;
}

.status-pill.hidden {
    color: #786a6e;
    background: #eee8ea;
}

.owner-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.small-button {
    padding: 7px 9px;
    color: #754353;
    background: white;
    border: 1px solid #e6cfd5;
    border-radius: 7px;
    cursor: pointer;
    font-size: 11px;
}

.small-button:hover {
    background: #f8ecef;
}

.delete-button {
    color: #a13d50;
}

/* Suche */

.search-area {
    margin-bottom: 28px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    min-height: 56px;
    padding: 0 17px;
    color: #8f5364;
    background: #fff;
    border: 1px solid #ded8da;
    border-radius: 9px;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.search-box:focus-within {
    border-color: #9b6373;
    box-shadow: 0 0 0 3px rgba(143, 83, 100, 0.08);
}

.search-box input {
    width: 100%;
    min-width: 0;
    padding: 15px 0;
    color: #403138;
    background: transparent;
    border: 0;
    outline: 0;
    font: inherit;
    font-size: 14px;
}

.search-box input::placeholder {
    color: #8b7a81;
}

.search-box input::-webkit-search-cancel-button {
    display: none;
}

.clear-search {
    width: 27px;
    height: 27px;
    flex: 0 0 27px;
    color: #805b65;
    background: #f8ecef;
    border: 0;
    border-radius: 50%;
    cursor: pointer;
    font-size: 19px;
}

.results-meta {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 12px;
    color: #8b767d;
    font-size: 12px;
}

.results-meta strong {
    color: #754353;
}

/* Studio-Karten */

.studio-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 23px;
}

.studio-card {
    display: flex;
    min-width: 0;
    min-height: 230px;
    flex-direction: column;
    padding: 25px;
    background: #fff;
    border: 1px solid #ded8da;
    border-radius: 13px;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;
}

.studio-card:hover {
    border-color: #d4b5bf;
    box-shadow: 0 8px 24px rgba(117, 67, 83, 0.08);
    transform: translateY(-2px);
}

.studio-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 15px;
}

.studio-avatar {
    display: grid;
    width: 45px;
    height: 45px;
    flex: 0 0 45px;
    place-items: center;
    color: #754353;
    background: #f5e7eb;
    border: 1px solid #eddae0;
    border-radius: 50%;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 15px;
}

.rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 9px;
    color: #62454e;
    background: #fbf5f6;
    border: 1px solid #f0e1e4;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.star {
    color: #c28a45;
    font-size: 17px;
}

.studio-card h2 {
    margin: 0 0 13px;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 25px;
    font-weight: 500;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.studio-location {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #65555a;
    font-size: 13px;
}

.studio-location svg {
    flex: 0 0 auto;
    color: #8f5364;
}

.studio-description {
    margin: 13px 0 18px;
    color: #8b767d;
    font-size: 12px;
    line-height: 1.7;
}

.studio-card-footer {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: auto;
    padding-top: 22px;
}

.select-button {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    min-height: 42px;
    padding: 10px 14px;
    color: #754353;
    background: #f8ecef;
    border: 1px solid #f0dfe3;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition:
        color 0.2s ease,
        background 0.2s ease;
}

.select-button:hover {
    color: white;
    background: #8f5364;
    border-color: #8f5364;
}

.select-button span {
    font-size: 17px;
}

.website-link {
    display: grid;
    width: 40px;
    height: 40px;
    margin-left: auto;
    place-items: center;
    color: #754353;
    background: white;
    border: 1px solid #ead7dd;
    border-radius: 8px;
    text-decoration: none;
}

.website-link:hover {
    background: #f8ecef;
}

/* Keine Ergebnisse */

.empty-state {
    padding: 55px 20px;
    text-align: center;
    background: #fffdfd;
    border: 1px solid #eee3e6;
    border-radius: 13px;
}

.empty-icon {
    margin-bottom: 12px;
    color: #b17b8a;
    font-size: 42px;
}

.empty-state h2 {
    margin: 0 0 10px;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 25px;
    font-weight: 500;
}

.empty-state p {
    margin: 0 0 20px;
    color: #8b767d;
    font-size: 13px;
    line-height: 1.7;
}

/* Studio-Dialog */

.modal-backdrop {
    position: fixed;
    z-index: 1000;
    inset: 0;
    display: grid;
    overflow-y: auto;
    padding: 20px;
    place-items: center;
    background: rgba(48, 30, 36, 0.42);
    backdrop-filter: blur(4px);
}

.studio-modal {
    position: relative;
    width: 100%;
    max-width: 460px;
    padding: 34px 28px;
    text-align: center;
    background: #fffdfd;
    border: 1px solid #f0e1e4;
    border-radius: 20px;
    box-shadow: 0 20px 70px rgba(48, 30, 36, 0.2);
}

.modal-close {
    position: absolute;
    top: 13px;
    right: 13px;
    display: grid;
    width: 35px;
    height: 35px;
    place-items: center;
    color: #754353;
    background: #f8ecef;
    border: 0;
    border-radius: 50%;
    cursor: pointer;
    font-size: 24px;
}

.modal-avatar {
    width: 60px;
    height: 60px;
    margin: 0 auto 20px;
    font-size: 19px;
}

.studio-modal h2 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 29px;
    font-weight: 500;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.modal-rating {
    margin-top: 14px;
    color: #62454e;
    font-size: 15px;
}

.modal-location,
.modal-description {
    color: #806b72;
    font-size: 13px;
    line-height: 1.8;
}

.modal-description {
    margin-top: 18px;
}

.modal-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 9px;
    margin-top: 22px;
}

.modal-note {
    margin: 20px 0 0;
    color: #9b858c;
    font-size: 11px;
    line-height: 1.7;
}

/* Footer */

.studio-footer {
    padding: 45px 15px 0;
    text-align: center;
}

.studio-footer span {
    color: #ad7887;
    font-size: 24px;
}

.studio-footer p {
    color: #967b83;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 14px;
    font-style: italic;
}

button:focus-visible,
a:focus-visible {
    outline: 3px solid rgba(143, 83, 100, 0.35);
    outline-offset: 3px;
}

/* Responsive */

@media (max-width: 680px) {
    .studio-search-page {
        padding: 35px 16px 55px;
    }

    .page-header {
        margin-bottom: 26px;
    }

    .studio-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .studio-card {
        min-height: 210px;
        padding: 21px;
    }

    .studio-card h2 {
        font-size: 23px;
    }

    .owner-panel-top {
        align-items: flex-start;
        flex-direction: column;
    }

    .owner-studio-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .owner-actions {
        width: 100%;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-field.full-width {
        grid-column: auto;
    }

    .studio-form-card {
        padding: 15px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
