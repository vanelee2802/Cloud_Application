<script setup>
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'

const language = ref('de')
const theme = ref('light')
const contrast = ref('normal')
const accent = ref('rose')

const showDeleteDialog = ref(false)
const deleteScope = ref('personal')
const confirmationText = ref('')

const accents = [
    { label: 'Altrosa', value: 'rose', color: '#8f5364' },
    { label: 'Beere', value: 'berry', color: '#754353' },
    { label: 'Rosé', value: 'pink', color: '#b17b8c' },
    { label: 'Mauve', value: 'mauve', color: '#98717e' },
]

const selectedAccent = computed(
    () => accents.find(item => item.value === accent.value) ?? accents[0]
)

const pageClasses = computed(() => ({
    'settings-dark': theme.value === 'dark',
    'settings-high-contrast': contrast.value === 'high',
}))

function saveAppearance() {
    alert(
        'Deine Auswahl wurde für diese Vorschau übernommen. ' +
        'Dauerhaftes Speichern ist noch nicht eingerichtet.'
    )
}

function openDeleteDialog() {
    confirmationText.value = ''
    showDeleteDialog.value = true
}

function confirmDelete() {
    if (confirmationText.value !== 'LÖSCHEN') return

    alert(
        'Die Löschfunktion ist noch nicht mit dem Backend verbunden. ' +
        'Es wurden keine Daten gelöscht.'
    )
}

function closeDeleteDialog() {
    showDeleteDialog.value = false
    confirmationText.value = ''
}
</script>

<template>
    <Head title="Einstellungen" />

    <main
        :class="pageClasses"
        class="settings-page"
        :style="{ '--accent': selectedAccent.color }"
    >
        <div class="settings-container">
            <!-- Kopfbereich -->

            <a href="javascript:history.back()" class="back-button">
    <span aria-hidden="true">←</span>
    Zurück
</a>
            <header class="settings-header">
                <span class="settings-eyebrow">
                    DEIN MIXMATCH
                </span>

                <h1>
                    Deine <span>Einstellungen.</span>
                </h1>

                <p>
                    Gestalte dein Erlebnis so individuell wie dein Studio.
                </p>
            </header>

            <!-- Darstellung -->
            <section class="settings-section">
                <div class="section-heading">
                    <span class="section-icon">✧</span>

                    <div>
                        <h2>Darstellung</h2>
                        <p>Dein Stil, deine Farben, deine Ansicht.</p>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <h3>Sprache</h3>
                        <p>In welcher Sprache möchtest du MixMatch nutzen?</p>
                    </div>

                    <select v-model="language" class="settings-select">
                        <option value="de">Deutsch</option>
                        <option value="en">English</option>
                        <option value="tr">Türkçe</option>
                    </select>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <h3>Farbschema</h3>
                        <p>Wähle die passende Atmosphäre.</p>
                    </div>

                    <div class="choice-group">
                        <button
                            v-for="item in [
                                { value: 'light', label: 'Hell', icon: '☀' },
                                { value: 'dark', label: 'Dunkel', icon: '☾' },
                                { value: 'system', label: 'System', icon: '◐' },
                            ]"
                            :key="item.value"
                            type="button"
                            class="choice-button"
                            :class="{ selected: theme === item.value }"
                            :aria-pressed="theme === item.value"
                            @click="theme = item.value"
                        >
                            <span>{{ item.icon }}</span>
                            {{ item.label }}
                        </button>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <h3>Kontrast</h3>
                        <p>Für eine angenehme und gut lesbare Ansicht.</p>
                    </div>

                    <div class="choice-group">
                        <button
                            type="button"
                            class="choice-button"
                            :class="{ selected: contrast === 'normal' }"
                            :aria-pressed="contrast === 'normal'"
                            @click="contrast = 'normal'"
                        >
                            Normal
                        </button>

                        <button
                            type="button"
                            class="choice-button"
                            :class="{ selected: contrast === 'high' }"
                            :aria-pressed="contrast === 'high'"
                            @click="contrast = 'high'"
                        >
                            Hoch
                        </button>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <h3>Akzentfarbe</h3>
                        <p>Ein Hauch Farbe für deine Oberfläche.</p>
                    </div>

                    <div class="color-options">
                        <button
                            v-for="item in accents"
                            :key="item.value"
                            type="button"
                            class="color-choice"
                            :aria-label="item.label"
                            :aria-pressed="accent === item.value"
                            @click="accent = item.value"
                        >
                            <span
                                class="color-dot"
                                :class="{ active: accent === item.value }"
                                :style="{ '--swatch': item.color }"
                            >
                                <span v-if="accent === item.value">✓</span>
                            </span>

                            <small>{{ item.label }}</small>
                        </button>
                    </div>
                </div>

                <div class="settings-actions">
                    <button
                        type="button"
                        class="primary-button"
                        @click="saveAppearance"
                    >
                        Einstellungen übernehmen
                        <span>→</span>
                    </button>

                    <p class="helper-text">
                        Änderungen werden vorerst nicht dauerhaft gespeichert.
                    </p>
                </div>
            </section>

            <!-- Datenschutz -->
            <section class="settings-section">
                <div class="section-heading">
                    <span class="section-icon">♡</span>

                    <div>
                        <h2>Datenschutz</h2>
                        <p>Deine Verantwortung für sensible Kundendaten.</p>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <h3>Kundenverwaltung</h3>
                        <p>
                            Verwalte einzelne Kundenkonten über die
                            Benutzerverwaltung.
                        </p>
                    </div>

                    <a href="/admin/users" class="secondary-button">
                        Zur Übersicht →
                    </a>
                </div>

                <div class="danger-panel">
                    <span class="danger-eyebrow">
                        DATENSCHUTZ & KONTROLLE
                    </span>

                    <h3>Kundendaten löschen</h3>

                    <p class="danger-description">
                        Du möchtest personenbezogene Kundendaten entfernen?
                        Wähle zunächst aus, welche Art der Datenbereinigung
                        du vorbereiten möchtest.
                    </p>

                    <div class="delete-options">
                        <label class="delete-option">
                            <input
                                v-model="deleteScope"
                                type="radio"
                                value="personal"
                                name="deleteScope"
                            />

                            <span>
                                <strong>
                                    Personenbezogene Daten anonymisieren
                                </strong>
                                <small>
                                    Persönliche Angaben anonymisieren, soweit
                                    zulässig, und notwendige Nachweise erhalten.
                                </small>
                            </span>
                        </label>

                        <label class="delete-option">
                            <input
                                v-model="deleteScope"
                                type="radio"
                                value="all"
                                name="deleteScope"
                            />

                            <span>
                                <strong>
                                    Alle löschbaren Kundendaten
                                </strong>
                                <small>
                                    Daten entfernen, die nicht mehr benötigt
                                    werden und keiner Aufbewahrungspflicht
                                    unterliegen.
                                </small>
                            </span>
                        </label>
                    </div>

                    <button
                        type="button"
                        class="danger-button"
                        @click="openDeleteDialog"
                    >
                        Löschvorgang vorbereiten
                        <span>→</span>
                    </button>

                    <p class="danger-note">
                        Es werden keine Daten automatisch gelöscht.
                        Termine, Designs und erforderliche Rechnungsnachweise
                        müssen bei einer späteren Umsetzung berücksichtigt werden.
                    </p>
                </div>
            </section>

            <!-- Weitere Einstellungen -->
            <section class="settings-section">
                <div class="section-heading">
                    <span class="section-icon">✧</span>

                    <div>
                        <h2>Konto & Sicherheit</h2>
                        <p>Weitere persönliche Einstellungen.</p>
                    </div>
                </div>

                <div class="setting-row">
                    <div class="setting-copy">
                        <h3>Kontoeinstellungen</h3>
                        <p>
                            Weitere Sicherheits- und Kontofunktionen
                            können hier später ergänzt werden.
                        </p>
                    </div>

                    <span class="coming-soon">Demnächst</span>
                </div>
            </section>

            <footer class="settings-footer">
                <span>Mit Liebe zum Detail gestaltet.</span>
                <span>MixMatch ♡</span>
            </footer>
        </div>

        <!-- Bestätigungsdialog -->
        <div
            v-if="showDeleteDialog"
            class="dialog-backdrop"
            @click.self="closeDeleteDialog"
        >
            <section
                class="delete-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-title"
            >
                <span class="dialog-icon">!</span>

                <span class="settings-eyebrow">
                    BITTE BESTÄTIGEN
                </span>

                <h2 id="delete-title">Bist du dir sicher?</h2>

                <p>
                    Ausgewählt:
                    <strong>
                        {{
                            deleteScope === 'personal'
                                ? 'Personenbezogene Daten anonymisieren'
                                : 'Alle löschbaren Kundendaten'
                        }}
                    </strong>
                </p>

                <p>
                    Dies ist momentan nur eine Vorschau. Es werden keine
                    Daten gelöscht.
                </p>

                <label for="delete-confirmation" class="confirm-label">
                    Tippe <strong>LÖSCHEN</strong> zur Bestätigung.
                </label>

                <input
                    id="delete-confirmation"
                    v-model="confirmationText"
                    class="settings-select confirmation-input"
                    placeholder="LÖSCHEN"
                    autocomplete="off"
                />

                <div class="dialog-actions">
                    <button
                        type="button"
                        class="secondary-button"
                        @click="closeDeleteDialog"
                    >
                        Abbrechen
                    </button>

                    <button
                        type="button"
                        class="danger-button"
                        :disabled="confirmationText !== 'LÖSCHEN'"
                        @click="confirmDelete"
                    >
                        Bestätigen
                    </button>
                </div>
            </section>
        </div>
    </main>
</template>

<style scoped>

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 2rem;
    padding: 0.7rem 1.1rem;

    border: 1px solid #e8cbd4;
    border-radius: 9px;

    background: #fffafb;
    color: #8f5364;

    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;

    transition:
        background 0.2s ease,
        transform 0.2s ease;
}

.back-button:hover {
    background: #f7e9ee;
    transform: translateX(-3px);
}

.back-button span {
    font-size: 1.2rem;
}

.settings-dark .back-button {
    background: #35262d;
    border-color: #60424e;
    color: #f1cbd7;
}

.settings-dark .back-button:hover {
    background: #49313c;
}
.settings-page {
    --page-bg: #fffafb;
    --card-bg: #ffffff;
    --text-main: #3d2930;
    --text-heading: #754353;
    --text-muted: #8b7b80;
    --border-color: #f0dfe5;
    --soft-pink: #f7e9ee;
    --accent: #8f5364;

    min-height: 100vh;
    padding: 4rem 1.5rem;
    background: var(--page-bg);
    color: var(--text-main);
    transition: background 0.2s ease, color 0.2s ease;
}

.settings-container {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
}

.settings-header {
    margin-bottom: 3rem;
}

.settings-eyebrow {
    display: inline-block;
    margin-bottom: 0.9rem;
    color: #8f5364;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.16em;
}

.settings-header h1 {
    margin: 0;
    color: var(--text-heading);
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2.4rem, 5vw, 3.7rem);
    font-weight: 500;
    line-height: 1.15;
}

.settings-header h1 span {
    color: #b17b8c;
    font-style: italic;
}

.settings-header > p {
    margin-top: 1rem;
    color: var(--text-muted);
    font-size: 1rem;
    line-height: 1.8;
}

.settings-section {
    margin-bottom: 1.6rem;
    padding: 2rem;
    border: 1px solid var(--border-color);
    border-radius: 16px;
    background: var(--card-bg);
    box-shadow: 0 10px 35px rgba(80, 35, 48, 0.045);
}

.section-heading {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.6rem;
}

.section-icon {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: var(--soft-pink);
    color: var(--accent);
    font-size: 1.5rem;
}

.section-heading h2 {
    margin: 0;
    color: var(--text-heading);
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.5rem;
    font-weight: 500;
}

.section-heading p {
    margin-top: 0.3rem;
    color: var(--text-muted);
    font-size: 0.87rem;
    line-height: 1.6;
}

.setting-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-top: 1px solid var(--border-color);
}

.setting-copy {
    min-width: 0;
}

.setting-copy h3,
.danger-panel h3 {
    margin: 0 0 0.4rem;
    color: var(--text-heading);
    font-size: 0.96rem;
    font-weight: 600;
}

.setting-copy p {
    max-width: 490px;
    margin: 0;
    color: var(--text-muted);
    font-size: 0.87rem;
    line-height: 1.7;
}

.settings-select {
    width: 100%;
    max-width: 230px;
    padding: 0.75rem 0.9rem;
    border: 1px solid #e8cbd4;
    border-radius: 9px;
    background: var(--page-bg);
    color: var(--text-main);
    font: inherit;
    font-size: 0.9rem;
}

.choice-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.choice-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    min-height: 42px;
    padding: 0.65rem 0.9rem;
    border: 1px solid var(--border-color);
    border-radius: 9px;
    background: var(--card-bg);
    color: var(--text-main);
    font-size: 0.85rem;
    cursor: pointer;
    transition: 0.2s ease;
}

.choice-button:hover {
    border-color: #cda2b0;
}

.choice-button.selected {
    border-color: var(--accent);
    background: var(--soft-pink);
    color: var(--text-heading);
}

.color-options {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.color-choice {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    border: 0;
    background: transparent;
    color: var(--text-main);
    cursor: pointer;
}

.color-dot {
    display: flex;
    width: 34px;
    height: 34px;
    align-items: center;
    justify-content: center;
    border: 3px solid var(--card-bg);
    border-radius: 50%;
    outline: 1px solid transparent;
    background: var(--swatch);
    color: white;
}

.color-dot.active {
    outline: 2px solid var(--text-heading);
    outline-offset: 2px;
}

.color-choice small {
    color: var(--text-muted);
    font-size: 0.72rem;
}

.settings-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
    padding-top: 1rem;
}

.primary-button,
.secondary-button,
.danger-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    min-height: 44px;
    padding: 0.8rem 1.15rem;
    border: 1px solid transparent;
    border-radius: 9px;
    font-size: 0.88rem;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.2s ease;
}

.primary-button {
    background: var(--accent);
    color: white;
}

.primary-button:hover {
    background: #754353;
    transform: translateY(-1px);
}

.secondary-button {
    border-color: #dcbac5;
    background: var(--card-bg);
    color: var(--text-heading);
}

.secondary-button:hover {
    background: var(--soft-pink);
}

.helper-text {
    color: var(--text-muted);
    font-size: 0.75rem;
    line-height: 1.5;
}

.danger-panel {
    padding: 1.5rem;
    border: 1px solid #e8cbd4;
    border-radius: 12px;
    background: #fffafb;
}

.danger-eyebrow {
    display: block;
    margin-bottom: 0.8rem;
    color: #8f5364;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.13em;
}

.danger-panel h3 {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.5rem;
    font-weight: 500;
}

.danger-description,
.danger-note {
    color: #8b7b80;
    font-size: 0.87rem;
    line-height: 1.8;
}

.delete-options {
    display: grid;
    gap: 0.75rem;
    margin: 1.5rem 0;
}

.delete-option {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    padding: 1rem;
    border: 1px solid #f0dfe5;
    border-radius: 9px;
    background: #ffffff;
    cursor: pointer;
}

.delete-option input {
    margin-top: 0.3rem;
    accent-color: #8f5364;
}

.delete-option strong,
.delete-option small {
    display: block;
}

.delete-option strong {
    color: #754353;
    font-size: 0.87rem;
}

.delete-option small {
    margin-top: 0.35rem;
    color: #8b7b80;
    font-size: 0.8rem;
    line-height: 1.6;
}

.danger-button {
    border-color: #d7aab7;
    background: #8f5364;
    color: white;
}

.danger-button:hover:not(:disabled) {
    background: #754353;
}

.danger-button:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.danger-note {
    margin: 1rem 0 0;
    font-size: 0.76rem;
}

.coming-soon {
    flex-shrink: 0;
    padding: 0.4rem 0.7rem;
    border-radius: 20px;
    background: var(--soft-pink);
    color: var(--text-heading);
    font-size: 0.72rem;
}

.settings-footer {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem 0;
    color: var(--text-muted);
    font-size: 0.8rem;
}

/* Dialog */

.dialog-backdrop {
    position: fixed;
    z-index: 100;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 1rem;
    background: rgba(45, 25, 32, 0.55);
    backdrop-filter: blur(5px);
}

.delete-dialog {
    width: 100%;
    max-width: 470px;
    padding: 2rem;
    border: 1px solid var(--border-color);
    border-radius: 18px;
    background: #fffafb;
    color: #3d2930;
    box-shadow: 0 25px 80px rgba(45, 25, 32, 0.25);
}

.dialog-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    margin-bottom: 1.2rem;
    border-radius: 50%;
    background: #f7e9ee;
    color: #8f5364;
    font-size: 1.4rem;
    font-weight: 700;
}

.delete-dialog h2 {
    margin: 0 0 1rem;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.8rem;
    font-weight: 500;
}

.delete-dialog p {
    margin: 0.75rem 0;
    color: #8b7b80;
    font-size: 0.9rem;
    line-height: 1.8;
}

.confirm-label {
    display: block;
    margin-top: 1.4rem;
    color: #754353;
    font-size: 0.85rem;
}

.confirmation-input {
    max-width: none;
    margin-top: 0.6rem;
}

.dialog-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.7rem;
    margin-top: 1.5rem;
}

/* Dunkler Modus */

.settings-page.settings-dark {
    --page-bg: #261c21;
    --card-bg: #35262d;
    --text-main: #fff5f7;
    --text-heading: #f1cbd7;
    --text-muted: #d0b5bf;
    --border-color: #60424e;
    --soft-pink: #49313c;
}

.settings-dark .danger-panel,
.settings-dark .delete-option {
    background: #35262d;
    border-color: #77505f;
}

.settings-dark .delete-option strong,
.settings-dark .danger-panel h3 {
    color: #f1cbd7;
}

.settings-dark .delete-dialog {
    background: #35262d;
    color: #fff5f7;
}

.settings-dark .delete-dialog h2,
.settings-dark .confirm-label {
    color: #f1cbd7;
}

/* Hoher Kontrast */

.settings-page.settings-high-contrast {
    --text-main: #24151b;
    --text-heading: #4e1f31;
    --text-muted: #59444b;
    --border-color: #9e7382;
}

.settings-page.settings-high-contrast .settings-section {
    border-width: 2px;
}

.settings-page.settings-high-contrast .setting-copy p,
.settings-page.settings-high-contrast .danger-description,
.settings-page.settings-high-contrast .danger-note {
    color: var(--text-main);
}

.settings-page :focus-visible {
    outline: 3px solid var(--accent);
    outline-offset: 3px;
}

@media (max-width: 700px) {
    .settings-page {
        padding: 3rem 1rem;
    }

    .settings-header {
        margin-bottom: 2rem;
    }

    .settings-section {
        padding: 1.25rem;
        border-radius: 13px;
    }

    .setting-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 1rem;
    }

    .settings-select {
        max-width: none;
    }

    .choice-group {
        width: 100%;
    }

    .choice-button {
        flex: 1;
    }

    .settings-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .primary-button,
    .secondary-button,
    .danger-button {
        width: 100%;
    }

    .settings-footer {
        flex-direction: column;
        text-align: center;
    }

    .delete-dialog {
        padding: 1.4rem;
    }

    .dialog-actions {
        flex-direction: column-reverse;
    }
}
</style>
