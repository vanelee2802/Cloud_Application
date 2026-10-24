<template>
    <pagesLayout>
    <div class="contact-page">
        <div class="contact-container">
            <!-- Kopfbereich -->
            <header class="contact-header">
                <div class="header-flower">✿</div>

                <p class="eyebrow">{{ t.eyebrow }}</p>
                <h1>{{ t.pageTitle }}</h1>

                <p class="header-description">
                    {{ t.description }}
                </p>

                <div class="header-line"></div>

                <!-- Sprachauswahl -->
                <div class="language-switch">
                    <button
                        v-for="lang in availableLanguages"
                        :key="lang.code"
                        type="button"
                        :class="{ active: language === lang.code }"
                        :aria-pressed="language === lang.code"
                        @click="changeLanguage(lang.code)"
                    >
                        {{ lang.label }}
                    </button>
                </div>
            </header>

            <!-- Owner-Hinweis -->
            <div v-if="canEdit" class="owner-notice">
                <span class="owner-icon">✎</span>
                <div>
                    <strong>{{ t.ownerModeTitle }}</strong>
                    <p>{{ t.ownerModeDescription }}</p>
                </div>
            </div>

            <!-- Kontaktkarten -->
            <section class="contact-grid">
                <!-- Kontaktinformationen -->
                <article class="contact-card information-card">
                    <div class="card-heading">
                        <div>
                            <p class="card-eyebrow">{{ t.informationEyebrow }}</p>
                            <h2>{{ t.informationTitle }}</h2>
                        </div>

                        <span class="heading-icon">♡</span>
                    </div>

                    <p class="information-description">
                        {{ contactText('intro', t.informationDescription) }}
                    </p>

                    <button
                        v-if="canEdit"
                        type="button"
                        class="edit-button intro-edit"
                        @click="openTextEditor(
                            'intro',
                            t.informationDescription,
                            t.informationTitle
                        )"
                    >
                        <span>✎</span> {{ t.editButton }}
                    </button>

                    <div
                        v-if="editingTextKey === 'intro'"
                        class="inline-editor"
                    >
                        <textarea
                            v-model="textDraft"
                            class="edit-textarea"
                            rows="4"
                        ></textarea>

                        <div class="editor-actions">
                            <button
                                type="button"
                                class="primary-button"
                                @click="saveTextEdit"
                            >
                                {{ t.saveButton }}
                            </button>
                            <button
                                type="button"
                                class="secondary-button"
                                @click="cancelTextEdit"
                            >
                                {{ t.cancelButton }}
                            </button>
                        </div>
                    </div>

                    <!-- E-Mail -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="21"
                                height="21"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2"
                                />
                                <path d="m3 7 9 6 9-6" />
                            </svg>
                        </div>

                        <div class="contact-item-content">
                            <span class="item-label">{{ t.emailLabel }}</span>

                            <template v-if="editingField === 'email'">
                                <input
                                    v-model="fieldDraft"
                                    type="email"
                                    class="edit-input"
                                    :placeholder="t.emailPlaceholder"
                                />
                            </template>

                            <template v-else>
                                <a
                                    v-if="contact.email"
                                    :href="`mailto:${contact.email}`"
                                    class="contact-value"
                                >
                                    {{ contact.email }}
                                </a>
                                <span v-else class="empty-value">
                                    {{ t.notProvided }}
                                </span>
                            </template>

                            <button
                                v-if="canEdit && editingField !== 'email'"
                                type="button"
                                class="edit-button"
                                @click="openFieldEditor('email')"
                            >
                                ✎ {{ t.editButton }}
                            </button>

                            <div
                                v-if="editingField === 'email'"
                                class="small-editor-actions"
                            >
                                <button
                                    type="button"
                                    @click="saveFieldEdit"
                                >
                                    {{ t.saveButton }}
                                </button>
                                <button
                                    type="button"
                                    @click="cancelFieldEdit"
                                >
                                    {{ t.cancelButton }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Telefon -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="21"
                                height="21"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2
                                    19.79 19.79 0 0 1-8.63-3.07
                                    19.5 19.5 0 0 1-6-6
                                    A19.79 19.79 0 0 1 2.12 3.18
                                    2 2 0 0 1 4.11 1h3
                                    a2 2 0 0 1 2 1.72
                                    c.12.96.36 1.9.7 2.8
                                    a2 2 0 0 1-.45 2.11L8.09 8.91
                                    a16 16 0 0 0 6 6l1.27-1.27
                                    a2 2 0 0 1 2.11-.45
                                    c.9.34 1.84.58 2.8.7
                                    A2 2 0 0 1 22 16.92Z"
                                />
                            </svg>
                        </div>

                        <div class="contact-item-content">
                            <span class="item-label">{{ t.phoneLabel }}</span>

                            <template v-if="editingField === 'phone'">
                                <input
                                    v-model="fieldDraft"
                                    type="tel"
                                    class="edit-input"
                                    :placeholder="t.phonePlaceholder"
                                />
                            </template>

                            <template v-else>
                                <a
                                    v-if="contact.phone"
                                    :href="`tel:${contact.phone.replace(/[^\d+]/g, '')}`"
                                    class="contact-value"
                                >
                                    {{ contact.phone }}
                                </a>
                                <span v-else class="empty-value">
                                    {{ t.notProvided }}
                                </span>
                            </template>

                            <button
                                v-if="canEdit && editingField !== 'phone'"
                                type="button"
                                class="edit-button"
                                @click="openFieldEditor('phone')"
                            >
                                ✎ {{ t.editButton }}
                            </button>

                            <div
                                v-if="editingField === 'phone'"
                                class="small-editor-actions"
                            >
                                <button
                                    type="button"
                                    @click="saveFieldEdit"
                                >
                                    {{ t.saveButton }}
                                </button>
                                <button
                                    type="button"
                                    @click="cancelFieldEdit"
                                >
                                    {{ t.cancelButton }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="21"
                                height="21"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
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
                        </div>

                        <div class="contact-item-content">
                            <span class="item-label">{{ t.addressLabel }}</span>

                            <template v-if="editingField === 'address'">
                                <textarea
                                    v-model="fieldDraft"
                                    class="edit-textarea address-textarea"
                                    rows="3"
                                    :placeholder="t.addressPlaceholder"
                                ></textarea>
                            </template>

                            <template v-else>
                                <p
                                    v-if="contact.address"
                                    class="contact-value address-value"
                                >{{ contact.address }}</p>
                                <span v-else class="empty-value">
                                    {{ t.notProvided }}
                                </span>
                            </template>

                            <button
                                v-if="canEdit && editingField !== 'address'"
                                type="button"
                                class="edit-button"
                                @click="openFieldEditor('address')"
                            >
                                ✎ {{ t.editButton }}
                            </button>

                            <div
                                v-if="editingField === 'address'"
                                class="small-editor-actions"
                            >
                                <button
                                    type="button"
                                    @click="saveFieldEdit"
                                >
                                    {{ t.saveButton }}
                                </button>
                                <button
                                    type="button"
                                    @click="cancelFieldEdit"
                                >
                                    {{ t.cancelButton }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Öffnungszeiten -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="21"
                                height="21"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 2" />
                            </svg>
                        </div>

                        <div class="contact-item-content">
                            <span class="item-label">
                                {{ t.openingHoursLabel }}
                            </span>

                            <template v-if="editingField === 'openingHours'">
                                <textarea
                                    v-model="fieldDraft"
                                    class="edit-textarea address-textarea"
                                    rows="4"
                                    :placeholder="t.openingHoursPlaceholder"
                                ></textarea>
                            </template>

                            <template v-else>
                                <p
                                    v-if="contact.openingHours"
                                    class="contact-value address-value"
                                >{{ contact.openingHours }}</p>
                                <span v-else class="empty-value">
                                    {{ t.byAppointment }}
                                </span>
                            </template>

                            <button
                                v-if="
                                    canEdit &&
                                    editingField !== 'openingHours'
                                "
                                type="button"
                                class="edit-button"
                                @click="openFieldEditor('openingHours')"
                            >
                                ✎ {{ t.editButton }}
                            </button>

                            <div
                                v-if="editingField === 'openingHours'"
                                class="small-editor-actions"
                            >
                                <button
                                    type="button"
                                    @click="saveFieldEdit"
                                >
                                    {{ t.saveButton }}
                                </button>
                                <button
                                    type="button"
                                    @click="cancelFieldEdit"
                                >
                                    {{ t.cancelButton }}
                                </button>
                            </div>
                        </div>
                    </div>
                </article>

<!-- Social Media -->
<article class="contact-card social-card">
    <div class="card-heading">
        <div>
            <p class="card-eyebrow">{{ t.socialEyebrow }}</p>
            <h2>{{ t.socialTitle }}</h2>
        </div>
        <span class="heading-icon">♡</span>
    </div>

    <p class="form-description">
        {{ t.socialDescription }}
    </p>

    <div class="social-list">
        <div
            v-for="social in socialLinks"
            :key="social.id"
            v-show="social.visible"
            class="social-item"
        >
            <div class="social-icon" :class="`social-${social.id}`">
                <span>{{ social.symbol }}</span>
            </div>

            <div class="social-content">
                <strong>{{ social.label }}</strong>

                <template v-if="editingSocial === social.id">
                    <input
                        v-model="socialDraft"
                        type="url"
                        class="edit-input"
                        :placeholder="t.socialUrlPlaceholder"
                    />

                    <div class="small-editor-actions">
                        <button type="button" @click="saveSocial(social)">
                            {{ t.saveButton }}
                        </button>
                        <button type="button" @click="cancelSocialEdit">
                            {{ t.cancelButton }}
                        </button>
                    </div>
                </template>

                <a
                    v-else-if="social.url"
                    :href="social.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="social-link"
                >
                    {{ social.display || t.visitProfile }}
                    <span aria-hidden="true">↗</span>
                </a>

                <span v-else class="empty-value">
                    {{ t.notProvided }}
                </span>

                <button
                    v-if="canEdit && editingSocial !== social.id"
                    type="button"
                    class="edit-button"
                    @click="editSocial(social)"
                >
                    ✎ {{ t.editButton }}
                </button>

                <button
                    v-if="canEdit"
                    type="button"
                    class="social-visibility"
                    @click="social.visible = !social.visible"
                >
                    {{ social.visible ? t.hideSocial : t.showSocial }}
                </button>
            </div>
        </div>
    </div>
</article>

                <!-- Kontaktformular -->
                <article class="contact-card form-card">
                    <div class="card-heading">
                        <div>
                            <p class="card-eyebrow">{{ t.formEyebrow }}</p>
                            <h2>{{ t.formTitle }}</h2>
                        </div>

                        <span class="heading-icon">♡</span>
                    </div>

                    <p class="form-description">
                        {{ t.formDescription }}
                    </p>

                    <form
                        class="contact-form"
                        @submit.prevent="submitForm"
                    >
                        <div class="form-group">
                            <label for="contact-name">
                                {{ t.nameLabel }}
                            </label>
                            <input
                                id="contact-name"
                                v-model.trim="form.name"
                                type="text"
                                autocomplete="name"
                                :placeholder="t.namePlaceholder"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label for="contact-email">
                                {{ t.emailLabel }}
                            </label>
                            <input
                                id="contact-email"
                                v-model.trim="form.email"
                                type="email"
                                autocomplete="email"
                                :placeholder="t.emailPlaceholder"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label for="contact-subject">
                                {{ t.subjectLabel }}
                            </label>
                            <input
                                id="contact-subject"
                                v-model.trim="form.subject"
                                type="text"
                                :placeholder="t.subjectPlaceholder"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label for="contact-message">
                                {{ t.messageLabel }}
                            </label>
                            <textarea
                                id="contact-message"
                                v-model.trim="form.message"
                                rows="5"
                                :placeholder="t.messagePlaceholder"
                                required
                            ></textarea>
                        </div>

                        <p class="privacy-note">
                            {{ t.privacyNote }}
                        </p>

                        <button
                            type="submit"
                            class="submit-button"
                            :disabled="formSubmitted"
                        >
                            <span>{{ formSubmitted ? t.formSubmitted : t.submitButton }}</span>
                            <span aria-hidden="true">↗</span>
                        </button>

                        <p
                            v-if="formSubmitted"
                            class="form-feedback"
                            role="status"
                        >
                            {{ t.demoSuccess }}
                        </p>
                    </form>
                </article>
            </section>

            <!-- Abschließender Hinweis -->
            <footer class="contact-footer">
                <span class="footer-flower">✿</span>
                <p>{{ t.footerText }}</p>
            </footer>
        </div>
    </div>
</pagesLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import pagesLayout from '@/Layouts/pagesLayout.vue'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Owner-Berechtigung
|--------------------------------------------------------------------------
| Erwartet auth.user.role === 'owner' in den Inertia-Props.
| Diese Prüfung steuert nur die Darstellung im Frontend.
*/

const socialLinks = reactive([
    {
        id: 'instagram',
        label: 'Instagram',
        symbol: '◎',
        url: '',
        display: '',
        visible: true,
    },
    {
        id: 'tiktok',
        label: 'TikTok',
        symbol: '♪',
        url: '',
        display: '',
        visible: true,
    },
    {
        id: 'facebook',
        label: 'Facebook',
        symbol: 'f',
        url: '',
        display: '',
        visible: true,
    },
    {
        id: 'whatsapp',
        label: 'WhatsApp',
        symbol: '◔',
        url: '',
        display: '',
        visible: true,
    },
])

const editingSocial = ref(null)
const socialDraft = ref('')

function editSocial(social) {
    if (!canEdit.value) return

    editingSocial.value = social.id
    socialDraft.value = social.url
}

function saveSocial(social) {
    if (!canEdit.value) return

    const value = socialDraft.value.trim()

    if (
        value &&
        !/^https?:\/\/\S+$/i.test(value)
    ) {
        return
    }

    social.url = value
    social.display = value
        ? value.replace(/^https?:\/\//i, '').replace(/\/$/, '')
        : ''

    cancelSocialEdit()
}

function cancelSocialEdit() {
    editingSocial.value = null
    socialDraft.value = ''
}

const canEdit = computed(() => {
    return page.props.auth?.user?.role === 'owner'
})

/*
|--------------------------------------------------------------------------
| Sprachen
|--------------------------------------------------------------------------
| Neue Sprachen können hier ergänzt werden.
| Die Kontaktfelder sind derzeit gemeinsam für beide Sprachen.
*/

const availableLanguages = [
    { code: 'de', label: 'DE' },
    { code: 'en', label: 'EN' },
]

const language = ref('de')

/*
|--------------------------------------------------------------------------
| Kontaktangaben
|--------------------------------------------------------------------------
| Bitte trage deine echten Kontaktdaten hier ein.
| Die Werte sind nur lokale Beispieldaten und werden nicht gespeichert.
*/

const contact = reactive({
    email: '',
    phone: '',
    address: '',
    openingHours: '',
})

/*
|--------------------------------------------------------------------------
| Übersetzungen
|--------------------------------------------------------------------------
*/

const translations = {
    de: {
        eyebrow: 'Wir freuen uns auf dich',
        pageTitle: 'Kontakt',
        description:
            'Du hast Fragen oder möchtest einen Termin vereinbaren? Wir freuen uns, von dir zu hören.',
        ownerModeTitle: 'Owner-Bearbeitungsmodus',
        ownerModeDescription:
            'Du kannst Kontaktangaben und Texte bearbeiten. Änderungen sind vorerst nur lokal und nicht dauerhaft gespeichert.',
        editButton: 'Bearbeiten',
        saveButton: 'Übernehmen',
        cancelButton: 'Abbrechen',
        notProvided: 'Noch nicht eingetragen',
        byAppointment: 'Termine nach Vereinbarung',
        informationEyebrow: 'Sag Hallo',
        informationTitle: 'Unsere Kontaktdaten',
        informationDescription:
            'Ob Fragen, Terminwünsche oder Inspiration für dein nächstes Nageldesign – melde dich gerne bei uns.',
        emailLabel: 'E-Mail',
        emailPlaceholder: 'deine@email.de',
        phoneLabel: 'Telefon',
        phonePlaceholder: '+49 ...',
        addressLabel: 'Adresse',
        addressPlaceholder: 'Straße, Hausnummer, PLZ und Ort',
        openingHoursLabel: 'Öffnungszeiten',
        openingHoursPlaceholder: 'z. B. Mo–Fr: 09:00–18:00 Uhr',
        formEyebrow: 'Schreib uns',
        formTitle: 'Nachricht senden',
        formDescription:
            'Fülle das Formular aus. Wir freuen uns auf deine Nachricht.',
        nameLabel: 'Dein Name',
        namePlaceholder: 'Vor- und Nachname',
        subjectLabel: 'Betreff',
        subjectPlaceholder: 'Worum geht es?',
        messageLabel: 'Deine Nachricht',
        messagePlaceholder: 'Schreibe hier deine Nachricht ...',
        privacyNote:
            'Bitte sende über dieses Demo-Formular keine sensiblen persönlichen Daten. Die Nachricht wird noch nicht übertragen oder gespeichert.',
        submitButton: 'Nachricht vorbereiten',
        formSubmitted: 'Formular ausgefüllt',
        demoSuccess:
            'Dies ist eine Frontend-Demo. Es wurde keine Nachricht versendet. Ein Versand muss später mit dem Laravel-Backend verbunden werden.',
        footerText:
            'Wir freuen uns darauf, dich in unserem Nagelstudio willkommen zu heißen.',
    },

    en: {
        eyebrow: 'We would love to hear from you',
        pageTitle: 'Contact',
        description:
            'Have a question or want to book an appointment? We look forward to hearing from you.',
        ownerModeTitle: 'Owner editing mode',
        ownerModeDescription:
            'You can edit contact details and text. For now, changes are local and are not permanently saved.',
        editButton: 'Edit',
        saveButton: 'Apply',
        cancelButton: 'Cancel',
        notProvided: 'Not provided yet',
        byAppointment: 'By appointment',
        informationEyebrow: 'Say hello',
        informationTitle: 'Our Contact Details',
        informationDescription:
            'Questions, appointment requests or inspiration for your next nail design – feel free to contact us.',
        emailLabel: 'Email',
        emailPlaceholder: 'your@email.com',
        phoneLabel: 'Phone',
        phonePlaceholder: '+49 ...',
        addressLabel: 'Address',
        addressPlaceholder: 'Street, number, postal code and city',
        openingHoursLabel: 'Opening Hours',
        openingHoursPlaceholder: 'e.g. Mon–Fri: 9:00 AM–6:00 PM',
        formEyebrow: 'Get in touch',
        formTitle: 'Send a Message',
        formDescription:
            'Fill out the form. We look forward to your message.',
        nameLabel: 'Your Name',
        namePlaceholder: 'First and last name',
        subjectLabel: 'Subject',
        subjectPlaceholder: 'What is your message about?',
        messageLabel: 'Your Message',
        messagePlaceholder: 'Write your message here ...',
        privacyNote:
            'Please do not submit sensitive personal information through this demo form. Messages are not transmitted or stored yet.',
        submitButton: 'Prepare Message',
        formSubmitted: 'Form completed',
        demoSuccess:
            'This is a frontend demo. No message has been sent. Sending must be connected to the Laravel backend later.',
        footerText:
            'We look forward to welcoming you to our nail studio.',
    },
}

const t = computed(() => {
    return translations[language.value] ?? translations.de
})

function changeLanguage(code) {
    if (!translations[code]) return

    cancelTextEdit()
    cancelFieldEdit()
    language.value = code
}

/*
|--------------------------------------------------------------------------
| Lokale Textänderungen
|--------------------------------------------------------------------------
| Textschlüssel enthalten die Sprache, damit Übersetzungen separat
| bearbeitet werden können.
*/

const textOverrides = ref({})
const editingTextKey = ref(null)
const textDraft = ref('')

function contactText(key, defaultText) {
    const overrideKey = `${language.value}.${key}`

    if (Object.prototype.hasOwnProperty.call(textOverrides.value, overrideKey)) {
        return textOverrides.value[overrideKey]
    }

    return defaultText
}

function openTextEditor(key, defaultText, label) {
    if (!canEdit.value) return

    editingTextKey.value = key
    textDraft.value = contactText(key, defaultText)
    fieldLabel.value = label
}

const fieldLabel = ref('')

function saveTextEdit() {
    if (!canEdit.value || !editingTextKey.value) return

    const key = `${language.value}.${editingTextKey.value}`

    textOverrides.value = {
        ...textOverrides.value,
        [key]: textDraft.value,
    }

    cancelTextEdit()
}

function cancelTextEdit() {
    editingTextKey.value = null
    textDraft.value = ''
    fieldLabel.value = ''
}

/*
|--------------------------------------------------------------------------
| Bearbeitung der Kontaktdaten
|--------------------------------------------------------------------------
| E-Mail, Telefon, Adresse und Öffnungszeiten sind vorerst sprachübergreifend.
*/

const editingField = ref(null)
const fieldDraft = ref('')

function openFieldEditor(key) {
    if (!canEdit.value) return

    editingField.value = key
    fieldDraft.value = contact[key] ?? ''
}

function saveFieldEdit() {
    if (!canEdit.value || !editingField.value) return

    contact[editingField.value] = fieldDraft.value.trim()

    cancelFieldEdit()
}

function cancelFieldEdit() {
    editingField.value = null
    fieldDraft.value = ''
}

/*
|--------------------------------------------------------------------------
| Kontaktformular
|--------------------------------------------------------------------------
| Nur Frontend-Demo: keine HTTP-Anfrage und kein E-Mail-Versand.
*/

const form = reactive({
    name: '',
    email: '',
    subject: '',
    message: '',
})

const formSubmitted = ref(false)

function submitForm() {
    formSubmitted.value = true
}
</script>

<style scoped>
.contact-page {
    min-height: 100vh;
    padding: 56px 20px 70px;
    color: #4c353b;
    background:
        radial-gradient(
            circle at top left,
            rgba(229, 193, 201, 0.22),
            transparent 34%
        ),
        #fbf7f7;
    font-family: inherit;
}

.contact-container {
    width: 100%;
    max-width: 1080px;
    margin: 0 auto;
}

/* Kopfbereich */

.contact-header {
    margin-bottom: 35px;
    padding: 44px 28px 34px;
    text-align: center;
    background: #fffdfd;
    border: 1px solid #f0e1e4;
    border-radius: 24px;
    box-shadow: 0 12px 36px rgba(117, 67, 83, 0.07);
}

.header-flower {
    margin-bottom: 12px;
    color: #a66e7e;
    font-size: 31px;
}

.eyebrow,
.card-eyebrow {
    margin: 0 0 11px;
    color: #9a6272;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.19em;
    text-transform: uppercase;
}

.contact-header h1 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(34px, 5vw, 46px);
    font-weight: 500;
    line-height: 1.2;
}

.header-description {
    max-width: 600px;
    margin: 18px auto 0;
    color: #826b71;
    font-size: 15px;
    line-height: 1.8;
}

.header-line {
    width: 58px;
    height: 2px;
    margin: 25px auto;
    background: #b47c8b;
}

/* Sprachumschalter */

.language-switch {
    display: inline-flex;
    gap: 5px;
    padding: 5px;
    background: #f7edef;
    border: 1px solid #f0dfe3;
    border-radius: 999px;
}

.language-switch button {
    min-width: 58px;
    padding: 9px 16px;
    color: #805b65;
    background: transparent;
    border: none;
    border-radius: 999px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
}

.language-switch button.active {
    color: #fff;
    background: #8f5364;
    box-shadow: 0 3px 9px rgba(117, 67, 83, 0.18);
}

/* Owner-Hinweis */

.owner-notice {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 24px;
    padding: 17px 20px;
    color: #754353;
    background: #f6e9ed;
    border: 1px solid #ead2d9;
    border-radius: 14px;
}

.owner-icon {
    display: grid;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    place-items: center;
    background: #fff8fa;
    border-radius: 50%;
    font-size: 19px;
}

.owner-notice strong {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
}

.owner-notice p {
    margin: 0;
    color: #80636c;
    font-size: 13px;
    line-height: 1.6;
}

/* Kartenlayout */

.contact-grid {
    display: grid;
    grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr);
    align-items: start;
    gap: 22px;
}

.contact-card {
    min-width: 0;
    padding: 30px;
    background: #fffdfd;
    border: 1px solid #f0e4e6;
    border-radius: 20px;
    box-shadow: 0 7px 24px rgba(117, 67, 83, 0.045);
}

.card-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.card-heading h2 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 25px;
    font-weight: 500;
    line-height: 1.35;
}

.card-eyebrow {
    margin-bottom: 8px;
}

.heading-icon {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    place-items: center;
    color: #8f5364;
    background: #f8ecef;
    border-radius: 14px;
    font-size: 23px;
}

.information-description,
.form-description {
    margin: 0 0 23px;
    color: #826b71;
    font-size: 14px;
    line-height: 1.85;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.intro-edit {
    margin-top: -10px;
    margin-bottom: 18px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 19px 0;
    border-top: 1px solid #f2e7e9;
}

.contact-icon {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    place-items: center;
    color: #8f5364;
    background: #f9eff1;
    border-radius: 13px;
}

.contact-item-content {
    min-width: 0;
    flex: 1;
}

.item-label {
    display: block;
    margin: 1px 0 6px;
    color: #987b84;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.contact-value {
    display: inline-block;
    max-width: 100%;
    color: #754353;
    font-size: 14px;
    line-height: 1.7;
    text-decoration: none;
    overflow-wrap: anywhere;
}

a.contact-value:hover {
    color: #a36d7d;
    text-decoration: underline;
}

.address-value {
    margin: 0;
    white-space: pre-line;
}

.empty-value {
    color: #a18b92;
    font-size: 13px;
    font-style: italic;
}

/* Bearbeiten-Elemente */

.edit-button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 7px;
    padding: 5px 8px;
    color: #8f5364;
    background: #fbf0f3;
    border: 1px solid #efdce1;
    border-radius: 8px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 700;
}

.edit-button:hover {
    color: #fff;
    background: #8f5364;
}

.inline-editor {
    margin: 0 0 20px;
}

.edit-input,
.edit-textarea {
    display: block;
    width: 100%;
    padding: 11px 12px;
    color: #4c353b;
    background: #fffafa;
    border: 1px solid #dfbdc7;
    border-radius: 10px;
    outline: none;
    font: inherit;
    font-size: 13px;
}

.edit-textarea {
    line-height: 1.7;
    resize: vertical;
}

.address-textarea {
    margin-top: 4px;
}

.edit-input:focus,
.edit-textarea:focus {
    border-color: #8f5364;
    box-shadow: 0 0 0 3px rgba(143, 83, 100, 0.1);
}

.editor-actions,
.small-editor-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 9px;
}

.editor-actions button,
.small-editor-actions button {
    padding: 8px 11px;
    color: #754353;
    background: #f8ecef;
    border: 1px solid #efdce1;
    border-radius: 8px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 700;
}

.editor-actions .primary-button {
    color: #fff;
    background: #8f5364;
    border-color: #8f5364;
}

.editor-actions .primary-button:hover {
    background: #754353;
}

.editor-actions .secondary-button {
    background: #fff;
}

/* Kontaktformular */

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    color: #754353;
    font-size: 12px;
    font-weight: 700;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 13px 14px;
    color: #4c353b;
    background: #fffafa;
    border: 1px solid #ead8dd;
    border-radius: 11px;
    outline: none;
    font: inherit;
    font-size: 13px;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #b29ca3;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #a56c7c;
    box-shadow: 0 0 0 3px rgba(143, 83, 100, 0.09);
}

.form-group textarea {
    min-height: 135px;
    line-height: 1.7;
    resize: vertical;
}

.privacy-note {
    margin: 0;
    color: #987b84;
    font-size: 11px;
    line-height: 1.7;
}

.submit-button {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    width: 100%;
    padding: 15px 18px;
    color: #fff;
    background: #8f5364;
    border: 1px solid #8f5364;
    border-radius: 12px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    transition:
        background 0.2s ease,
        transform 0.2s ease;
}

.submit-button:hover:not(:disabled) {
    background: #754353;
    transform: translateY(-1px);
}

.submit-button:disabled {
    cursor: default;
    opacity: 0.7;
}

.submit-button span:last-child {
    font-size: 19px;
}

.form-feedback {
    margin: 0;
    padding: 12px;
    color: #754353;
    background: #f8ecef;
    border: 1px solid #efdce1;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.7;
}

/* Fußzeile */

.contact-footer {
    padding: 38px 15px 0;
    text-align: center;
}

.footer-flower {
    display: block;
    margin-bottom: 10px;
    color: #ad7887;
    font-size: 25px;
}

.contact-footer p {
    margin: 0;
    color: #967b83;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 15px;
    font-style: italic;
    line-height: 1.7;
}

button:focus-visible {
    outline: 3px solid rgba(143, 83, 100, 0.35);
    outline-offset: 3px;
}

/* Tablet und Smartphone */

@media (max-width: 800px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 560px) {
    .contact-page {
        padding: 24px 12px 40px;
    }

    .contact-header {
        margin-bottom: 22px;
        padding: 32px 17px 26px;
        border-radius: 19px;
    }

    .contact-header h1 {
        font-size: 34px;
    }

    .header-description {
        font-size: 14px;
    }

    .contact-card {
        padding: 22px 18px;
        border-radius: 16px;
    }

    .card-heading h2 {
        font-size: 23px;
    }

    .contact-item {
        gap: 11px;
    }

    .contact-icon {
        width: 37px;
        height: 37px;
        flex-basis: 37px;
    }

    .owner-notice {
        padding: 14px;
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
