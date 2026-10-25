<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'

const options = [
    {
        value: 'anonymize',
        title: 'Personenbezogene Daten anonymisieren',
        text: 'Persönliche Angaben anonymisieren, soweit zulässig, und notwendige Nachweise erhalten.',
    },
    {
        value: 'delete',
        title: 'Alle löschbaren Kundendaten',
        text: 'Daten entfernen, die nicht mehr benötigt werden und keiner Aufbewahrungspflicht unterliegen.',
    },
]

const mode = ref('anonymize')
const preview = ref(null)
const result = ref(null)
const confirmation = ref('')
const password = ref('')
const loading = ref(false)
const errors = ref({})
const errorMessage = ref('')

const total = computed(() => {
    if (!preview.value) return 0
    return preview.value.users_deleted
        + preview.value.users_anonymized
        + preview.value.guest_appointments
})

const canRun = computed(() =>
    confirmation.value.trim() === 'LÖSCHEN'
    && password.value.length > 0
    && !loading.value
)

// Bei Wechsel der Option alles zurücksetzen
watch(mode, () => {
    resetForm()
    result.value = null
})

function resetForm() {
    preview.value = null
    confirmation.value = ''
    password.value = ''
    errors.value = {}
    errorMessage.value = ''
}

function handleError(error) {
    const status = error.response?.status

    if (status === 422) {
        errors.value = error.response.data.errors ?? {}
        return
    }
    if (status === 403) {
        errorMessage.value = 'Du hast keine Berechtigung für diese Funktion.'
        return
    }
    if (status === 419) {
        errorMessage.value = 'Deine Sitzung ist abgelaufen. Bitte lade die Seite neu.'
        return
    }
    errorMessage.value = 'Es ist ein Fehler aufgetreten. Bitte versuche es später erneut.'
}

async function loadPreview() {
    resetForm()
    result.value = null
    loading.value = true

    try {
        const { data } = await axios.post('/admin/datenschutz/vorschau', {
            mode: mode.value,
        })
        preview.value = data
    } catch (error) {
        handleError(error)
    } finally {
        loading.value = false
    }
}

async function runCleanup() {
    if (!canRun.value) return

    errors.value = {}
    errorMessage.value = ''
    loading.value = true

    try {
        const { data } = await axios.post('/admin/datenschutz/ausfuehren', {
            mode: mode.value,
            confirmation: confirmation.value.trim(),
            password: password.value,
        })
        result.value = data
        resetForm()
    } catch (error) {
        handleError(error)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <section class="cleanup-card">
        <p class="eyebrow">Datenschutz &amp; Kontrolle</p>
        <h2 class="title">Kundendaten löschen</h2>
        <p class="intro">
            Du möchtest personenbezogene Kundendaten entfernen? Wähle zunächst aus,
            welche Art der Datenbereinigung du vorbereiten möchtest.
        </p>

        <!-- Auswahl -->
        <div class="options">
            <label
                v-for="option in options"
                :key="option.value"
                class="option"
                :class="{ 'option--active': mode === option.value }"
            >
                <input
                    v-model="mode"
                    type="radio"
                    name="cleanup-mode"
                    :value="option.value"
                    :disabled="loading"
                >
                <span>
                    <span class="option-title">{{ option.title }}</span>
                    <span class="option-text">{{ option.text }}</span>
                </span>
            </label>
        </div>

        <!-- Schritt 1: Vorschau anfordern -->
        <button
            v-if="!preview"
            type="button"
            class="btn btn-primary"
            :disabled="loading"
            @click="loadPreview"
        >
            {{ loading ? 'Wird geprüft …' : 'Löschvorgang vorbereiten →' }}
        </button>

        <!-- Allgemeine Fehlermeldung -->
        <p v-if="errorMessage" class="alert alert-error">{{ errorMessage }}</p>

        <!-- Schritt 2: Vorschau anzeigen -->
        <div v-if="preview" class="preview">
            <template v-if="total === 0">
                <p class="preview-title">Nichts zu bereinigen</p>
                <p class="preview-text">
                    Aktuell gibt es keine Kundendaten, die nach diesen Regeln bereinigt werden können.
                </p>
                <button type="button" class="btn btn-secondary" @click="resetForm">
                    Zurück
                </button>
            </template>

            <template v-else>
                <p class="preview-title">Vorschau – es wurde noch nichts geändert</p>

                <ul class="preview-list">
                    <li v-if="mode === 'delete'">
                        <strong>{{ preview.users_deleted }}</strong>
                        Kundenkonten werden vollständig gelöscht (inkl. Designs und Terminen).
                    </li>
                    <li>
                        <strong>{{ preview.users_anonymized }}</strong>
                        Kundenkonten werden anonymisiert<span v-if="mode === 'delete'">,
                        weil noch Nachweise aufbewahrt werden müssen</span>.
                    </li>
                    <li>
                        <strong>{{ preview.guest_appointments }}</strong>
                        <template v-if="mode === 'delete'">
                            alte Gast-Termine ohne Nachweis werden gelöscht.
                        </template>
                        <template v-else>
                            alte Gast-Termine: Name und E-Mail werden entfernt.
                        </template>
                    </li>
                </ul>

                <p class="preview-hint">
                    Betroffen sind nur Kundinnen, die ausschließlich in deinen Studios gebucht
                    und seit mindestens 24 Monaten keinen Termin hatten. Abgeschlossene Termine
                    innerhalb der Aufbewahrungsfrist bleiben als Nachweis erhalten.
                </p>

                <!-- Schritt 3: Bestätigung -->
                <div class="confirm">
                    <label class="field">
                        <span class="field-label">
                            Gib zur Bestätigung <strong>LÖSCHEN</strong> ein
                        </span>
                        <input
                            v-model="confirmation"
                            type="text"
                            class="input"
                            autocomplete="off"
                            :disabled="loading"
                        >
                        <span v-if="errors.confirmation" class="field-error">
                            {{ errors.confirmation[0] }}
                        </span>
                    </label>

                    <label class="field">
                        <span class="field-label">Dein Passwort</span>
                        <input
                            v-model="password"
                            type="password"
                            class="input"
                            autocomplete="current-password"
                            :disabled="loading"
                            @keyup.enter="runCleanup"
                        >
                        <span v-if="errors.password" class="field-error">
                            {{ errors.password[0] }}
                        </span>
                    </label>

                    <div class="actions">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            :disabled="loading"
                            @click="resetForm"
                        >
                            Abbrechen
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger"
                            :disabled="!canRun"
                            @click="runCleanup"
                        >
                            {{ loading ? 'Wird ausgeführt …' : 'Jetzt endgültig ausführen' }}
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Ergebnis -->
        <div v-if="result" class="alert alert-success">
            <strong>Fertig.</strong>
            <span v-if="result.mode === 'delete'">
                {{ result.users_deleted }} Konten gelöscht,
            </span>
            {{ result.users_anonymized }} Konten anonymisiert,
            {{ result.guest_appointments }} Gast-Termine bereinigt.
        </div>

        <p class="footnote">
            Es werden keine Daten automatisch gelöscht. Termine, Designs und erforderliche
            Rechnungsnachweise werden bei der Bereinigung berücksichtigt.
        </p>
    </section>
</template>

<style scoped>
.cleanup-card {
    background: #fdf8f9;
    border: 1px solid #f0dfe4;
    border-radius: 14px;
    padding: 28px;
    color: #5a4a50;
}

.eyebrow {
    font-size: 11px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #7a4a5c;
    margin: 0 0 14px;
}

.title {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 26px;
    font-weight: 400;
    color: #5e3346;
    margin: 0 0 10px;
}

.intro {
    font-size: 14px;
    margin: 0 0 22px;
}

.options {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.option {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    background: #fff;
    border: 1px solid #f0dfe4;
    border-radius: 10px;
    padding: 18px;
    cursor: pointer;
    transition: border-color 0.15s;
}

.option--active {
    border-color: #c9a0ae;
}

.option input {
    margin-top: 3px;
    accent-color: #8e5468;
    width: 16px;
    height: 16px;
}

.option-title {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #4a2e3a;
    margin-bottom: 6px;
}

.option-text {
    display: block;
    font-size: 13px;
}

.btn {
    border: none;
    border-radius: 8px;
    padding: 13px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary {
    background: #8e5468;
    color: #fff;
}

.btn-secondary {
    background: #fff;
    color: #5e3346;
    border: 1px solid #e3cbd3;
}

.btn-danger {
    background: #a63d4f;
    color: #fff;
}

.preview {
    background: #fff;
    border: 1px solid #e3cbd3;
    border-radius: 10px;
    padding: 20px;
    margin-top: 4px;
}

.preview-title {
    font-weight: 600;
    color: #4a2e3a;
    margin: 0 0 12px;
}

.preview-text,
.preview-hint {
    font-size: 13px;
    margin: 0 0 16px;
}

.preview-list {
    margin: 0 0 14px;
    padding-left: 18px;
    font-size: 14px;
    line-height: 1.7;
}

.preview-list strong {
    color: #5e3346;
}

.confirm {
    border-top: 1px solid #f0dfe4;
    padding-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-label {
    font-size: 13px;
}

.input {
    border: 1px solid #e3cbd3;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 14px;
    max-width: 320px;
}

.input:focus {
    outline: 2px solid #c9a0ae;
    outline-offset: 1px;
}

.field-error {
    font-size: 12px;
    color: #a63d4f;
}

.actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.alert {
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 14px;
    margin-top: 16px;
}

.alert-error {
    background: #fbeaec;
    color: #8a2e3e;
}

.alert-success {
    background: #edf7ef;
    color: #2f6b3c;
}

.footnote {
    font-size: 12px;
    color: #7d6c72;
    margin: 20px 0 0;
}
</style>