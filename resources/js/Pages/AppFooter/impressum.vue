
<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import pagesLayout from '@/Layouts/pagesLayout.vue';

// Vorläufige Sprache. Später kann sie durch vue-i18n ersetzt werden.
const currentLanguage = ref('de');

// Vorläufige Owner-Erkennung.
// Später wird sie mit deiner tatsächlichen Authentifizierung verbunden.
const page = usePage();
const canEdit = computed(() => page.props.auth?.user?.role === 'owner');

const editingSection = ref(null);
const editedContent = ref({});
const savedContent = ref({});

const translations = {
    de: {
        eyebrow: 'SCHÖNHEIT & PFLEGE',
        title: 'Impressum',
        intro: 'Transparenz und Vertrauen gehören für uns genauso zur Schönheitspflege wie eine liebevolle Behandlung.',
        back: 'Zurück zur Startseite',
        edit: 'Abschnitt bearbeiten',
        save: 'Übernehmen',
        cancel: 'Abbrechen',
        sections: {
            company: {
                title: 'Angaben gemäß § 5 DDG',
                fields: {
                    studioName: 'Name deines Nagelstudios',
                    name: 'Vorname Nachname',
                    street: 'Straße und Hausnummer',
                    city: 'PLZ Ort',
                    country: 'Deutschland',
                },
            },
            contact: {
                title: 'Kontakt',
                fields: {
                    phone: '[Telefonnummer]',
                    email: '[E-Mail-Adresse]',
                },
            },
            business: {
                title: 'Angaben zum Unternehmen',
                fields: {
                    owner: '[Vorname Nachname]',
                    legalForm: '[Rechtsform, z. B. Einzelunternehmen]',
                    vat: '[USt-IdNr., falls vorhanden]',
                    register: '[Registerangaben, falls zutreffend]',
                },
            },
            responsible: {
                title: 'Verantwortlich für den Inhalt',
                fields: {
                    name: '[Vorname Nachname]',
                    address: '[Straße und Hausnummer, PLZ Ort]',
                },
            },
            dispute: {
                title: 'Verbraucherstreitbeilegung',
                text: '[Hier die zutreffende Information zur Verbraucherstreitbeilegung ergänzen, sofern erforderlich.]',
            },
            legal: {
                title: 'Rechtliche Hinweise',
                fields: {
                    contentLiability: 'Haftung für Inhalte',
                    contentText: 'Wir bemühen uns, die Inhalte dieser Website aktuell und richtig zu halten. Für die Richtigkeit, Vollständigkeit und Aktualität der bereitgestellten Inhalte übernehmen wir im Rahmen der gesetzlichen Vorschriften keine Gewähr.',
                    linkLiability: 'Haftung für externe Links',
                    linkText: 'Diese Website kann Links zu externen Websites Dritter enthalten. Auf deren Inhalte haben wir keinen Einfluss. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber verantwortlich.',
                    copyright: 'Urheberrecht',
                    copyrightText: 'Die auf dieser Website veröffentlichten Inhalte und Werke unterliegen dem jeweils geltenden Urheberrecht.',
                },
            },
        },
    },

    en: {
        eyebrow: 'BEAUTY & CARE',
        title: 'Legal Notice',
        intro: 'Transparency and trust are just as important to us as thoughtful beauty care.',
        back: 'Back to homepage',
        edit: 'Edit section',
        save: 'Apply',
        cancel: 'Cancel',
        sections: {
            company: {
                title: 'Business Information',
                fields: {
                    studioName: 'Your nail studio name',
                    name: 'Full name',
                    street: 'Street and house number',
                    city: 'Postal code and city',
                    country: 'Country',
                },
            },
            contact: {
                title: 'Contact',
                fields: {
                    phone: '[Phone number]',
                    email: '[Email address]',
                },
            },
            business: {
                title: 'Company Details',
                fields: {
                    owner: '[Full name]',
                    legalForm: '[Legal form]',
                    vat: '[VAT identification number, if applicable]',
                    register: '[Company registration details, if applicable]',
                },
            },
            responsible: {
                title: 'Responsible for Content',
                fields: {
                    name: '[Full name]',
                    address: '[Street, postal code and city]',
                },
            },
            dispute: {
                title: 'Consumer Dispute Resolution',
                text: '[Add the applicable consumer dispute resolution information, if required.]',
            },
            legal: {
                title: 'Legal Information',
                fields: {
                    contentLiability: 'Liability for Content',
                    contentText: 'We endeavour to keep the information on this website accurate and up to date, subject to applicable law.',
                    linkLiability: 'Liability for External Links',
                    linkText: 'This website may contain links to external websites. We have no control over their content; responsibility rests with their respective operators.',
                    copyright: 'Copyright',
                    copyrightText: 'The content and works published on this website are subject to applicable copyright law.',
                },
            },
        },
    },
};

const t = computed(() => translations[currentLanguage.value]);

function getSection(id) {
    return t.value.sections[id];
}

function getValue(sectionId, key, fallback) {
    const saved = savedContent.value[`${sectionId}.${key}`];
    return saved ?? fallback;
}

function startEditing(sectionId) {
    const section = getSection(sectionId);
    editingSection.value = sectionId;

    editedContent.value = {};

    if (section.fields) {
        Object.entries(section.fields).forEach(([key, value]) => {
            editedContent.value[key] = getValue(sectionId, key, value);
        });
    } else if (section.text) {
        editedContent.value.text = getValue(sectionId, 'text', section.text);
    }
}

function applyChanges(sectionId) {
    Object.entries(editedContent.value).forEach(([key, value]) => {
        savedContent.value[`${sectionId}.${key}`] = value;
    });

    editingSection.value = null;
}

function cancelEditing() {
    editingSection.value = null;
    editedContent.value = {};
}
</script>

<template>
    <pagesLayout>
    <Head :title="t.title" />

    <div class="impressum-page">
        <section class="impressum-hero">
            <span class="eyebrow">{{ t.eyebrow }}</span>
            <h1>{{ t.title }}</h1>
            <p>{{ t.intro }}</p>
            <div class="hero-line"></div>

            <!-- Temporäre Sprachwahl für die Vorbereitung -->
            <div class="language-switch">
                <button
                    type="button"
                    :class="{ active: currentLanguage === 'de' }"
                    @click="currentLanguage = 'de'"
                >
                    DE
                </button>
                <button
                    type="button"
                    :class="{ active: currentLanguage === 'en' }"
                    @click="currentLanguage = 'en'"
                >
                    EN
                </button>
            </div>
        </section>

        <main class="impressum-container">
            <section class="info-card">
                <div class="card-heading">
                    <span class="card-icon">♡</span>
                    <h2>{{ getSection('company').title }}</h2>

                    <button
                        v-if="canEdit"
                        class="edit-button"
                        type="button"
                        :aria-label="t.edit"
                        @click="startEditing('company')"
                    >✎</button>
                </div>

                <form
                    v-if="canEdit && editingSection === 'company'"
                    @submit.prevent="applyChanges('company')"
                >
                    <label v-for="(value, key) in getSection('company').fields" :key="key">
                        {{ value }}
                        <input v-model="editedContent[key]" />
                    </label>
                    <div class="actions">
                        <button type="submit" class="save-button">{{ t.save }}</button>
                        <button type="button" class="cancel-button" @click="cancelEditing">{{ t.cancel }}</button>
                    </div>
                </form>

                <template v-else>
                    <p class="studio-name">
                        {{ getValue('company', 'studioName', getSection('company').fields.studioName) }}
                    </p>
                    <p>
                        {{ getValue('company', 'name', getSection('company').fields.name) }}<br />
                        {{ getValue('company', 'street', getSection('company').fields.street) }}<br />
                        {{ getValue('company', 'city', getSection('company').fields.city) }}<br />
                        {{ getValue('company', 'country', getSection('company').fields.country) }}
                    </p>
                </template>
            </section>

            <section class="info-card">
                <div class="card-heading">
                    <span class="card-icon">✉</span>
                    <h2>{{ getSection('contact').title }}</h2>
                    <button
                        v-if="canEdit"
                        class="edit-button"
                        type="button"
                        :aria-label="t.edit"
                        @click="startEditing('contact')"
                    >✎</button>
                </div>

                <form
                    v-if="canEdit && editingSection === 'contact'"
                    @submit.prevent="applyChanges('contact')"
                >
                    <label v-for="(value, key) in getSection('contact').fields" :key="key">
                        {{ key === 'phone' ? 'Telefon / Phone' : 'E-Mail' }}
                        <input v-model="editedContent[key]" />
                    </label>
                    <div class="actions">
                        <button type="submit" class="save-button">{{ t.save }}</button>
                        <button type="button" class="cancel-button" @click="cancelEditing">{{ t.cancel }}</button>
                    </div>
                </form>

                <template v-else>
                    <p>Telefon: {{ getValue('contact', 'phone', getSection('contact').fields.phone) }}</p>
                    <p>E-Mail: {{ getValue('contact', 'email', getSection('contact').fields.email) }}</p>
                </template>
            </section>

            <section class="info-card">
                <div class="card-heading">
                    <span class="card-icon">✧</span>
                    <h2>{{ getSection('business').title }}</h2>
                    <button
                        v-if="canEdit"
                        class="edit-button"
                        type="button"
                        :aria-label="t.edit"
                        @click="startEditing('business')"
                    >✎</button>
                </div>

                <form
                    v-if="canEdit && editingSection === 'business'"
                    @submit.prevent="applyChanges('business')"
                >
                    <label v-for="(value, key) in getSection('business').fields" :key="key">
                        {{ value }}
                        <input v-model="editedContent[key]" />
                    </label>
                    <div class="actions">
                        <button type="submit" class="save-button">{{ t.save }}</button>
                        <button type="button" class="cancel-button" @click="cancelEditing">{{ t.cancel }}</button>
                    </div>
                </form>

                <template v-else>
                    <p v-for="(value, key) in getSection('business').fields" :key="key">
                        {{ value }}: {{ getValue('business', key, value) }}
                    </p>
                </template>
            </section>

            <section class="info-card">
                <div class="card-heading">
                    <span class="card-icon">❀</span>
                    <h2>{{ getSection('responsible').title }}</h2>
                    <button
                        v-if="canEdit"
                        class="edit-button"
                        type="button"
                        :aria-label="t.edit"
                        @click="startEditing('responsible')"
                    >✎</button>
                </div>

                <form
                    v-if="canEdit && editingSection === 'responsible'"
                    @submit.prevent="applyChanges('responsible')"
                >
                    <label v-for="(value, key) in getSection('responsible').fields" :key="key">
                        {{ value }}
                        <textarea v-model="editedContent[key]" rows="3"></textarea>
                    </label>
                    <div class="actions">
                        <button type="submit" class="save-button">{{ t.save }}</button>
                        <button type="button" class="cancel-button" @click="cancelEditing">{{ t.cancel }}</button>
                    </div>
                </form>

                <template v-else>
                    <p>{{ getValue('responsible', 'name', getSection('responsible').fields.name) }}</p>
                    <p>{{ getValue('responsible', 'address', getSection('responsible').fields.address) }}</p>
                </template>
            </section>

            <section class="info-card">
                <div class="card-heading">
                    <span class="card-icon">♡</span>
                    <h2>{{ getSection('dispute').title }}</h2>
                    <button
                        v-if="canEdit"
                        class="edit-button"
                        type="button"
                        :aria-label="t.edit"
                        @click="startEditing('dispute')"
                    >✎</button>
                </div>

                <form
                    v-if="canEdit && editingSection === 'dispute'"
                    @submit.prevent="applyChanges('dispute')"
                >
                    <textarea v-model="editedContent.text" rows="4"></textarea>
                    <div class="actions">
                        <button type="submit" class="save-button">{{ t.save }}</button>
                        <button type="button" class="cancel-button" @click="cancelEditing">{{ t.cancel }}</button>
                    </div>
                </form>

                <p v-else>
                    {{ getValue('dispute', 'text', getSection('dispute').text) }}
                </p>
            </section>

            <section class="info-card full-width">
                <div class="card-heading">
                    <span class="card-icon">✧</span>
                    <h2>{{ getSection('legal').title }}</h2>
                    <button
                        v-if="canEdit"
                        class="edit-button"
                        type="button"
                        :aria-label="t.edit"
                        @click="startEditing('legal')"
                    >✎</button>
                </div>

                <form
                    v-if="canEdit && editingSection === 'legal'"
                    @submit.prevent="applyChanges('legal')"
                >
                    <label>{{ getSection('legal').fields.contentLiability }}
                        <textarea v-model="editedContent.contentText" rows="4"></textarea>
                    </label>
                    <label>{{ getSection('legal').fields.linkLiability }}
                        <textarea v-model="editedContent.linkText" rows="4"></textarea>
                    </label>
                    <label>{{ getSection('legal').fields.copyright }}
                        <textarea v-model="editedContent.copyrightText" rows="4"></textarea>
                    </label>
                    <div class="actions">
                        <button type="submit" class="save-button">{{ t.save }}</button>
                        <button type="button" class="cancel-button" @click="cancelEditing">{{ t.cancel }}</button>
                    </div>
                </form>

                <template v-else>
                    <h3>{{ getSection('legal').fields.contentLiability }}</h3>
                    <p>{{ getValue('legal', 'contentText', getSection('legal').fields.contentText) }}</p>
                    <h3>{{ getSection('legal').fields.linkLiability }}</h3>
                    <p>{{ getValue('legal', 'linkText', getSection('legal').fields.linkText) }}</p>
                    <h3>{{ getSection('legal').fields.copyright }}</h3>
                    <p>{{ getValue('legal', 'copyrightText', getSection('legal').fields.copyrightText) }}</p>
                </template>
            </section>

            <div class="back-link">
                <Link href="/">{{ t.back }}</Link>
            </div>
        </main>
    </div>
</pagesLayout>
</template>

<style scoped>
.impressum-page {
    --rose: #f8eff1;
    --wine: #8f5364;
    --wine-dark: #754353;
    --text: #514047;
    --muted: #806f76;

    min-height: 100vh;
    padding-bottom: 70px;
    background: #fffafb;
    color: var(--text);
    font-family: inherit;
}

.impressum-hero {
    padding: 76px 24px 64px;
    text-align: center;
    background: linear-gradient(180deg, #f6e9ed 0%, #fffafb 100%);
}

.eyebrow {
    color: var(--wine);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.2em;
}

.impressum-hero h1 {
    margin: 16px 0;
    color: var(--wine-dark);
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 500;
}

.impressum-hero p {
    max-width: 570px;
    margin: 0 auto;
    color: var(--muted);
    line-height: 1.8;
}

.hero-line {
    width: 56px;
    height: 2px;
    margin: 28px auto 0;
    background: var(--wine);
}

.language-switch {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 24px;
}

.language-switch button {
    padding: 6px 12px;
    border: 1px solid #ead5da;
    border-radius: 8px;
    background: white;
    color: var(--wine);
    cursor: pointer;
}

.language-switch button.active {
    background: var(--wine);
    color: white;
}

.impressum-container {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
    width: min(100% - 40px, 960px);
    margin: 0 auto;
}

.info-card {
    min-width: 0;
    padding: 30px;
    border: 1px solid #f0e2e6;
    border-radius: 18px;
    background: white;
    box-shadow: 0 8px 28px rgb(117 67 83 / 5%);
}

.full-width,
.back-link {
    grid-column: 1 / -1;
}

.card-heading {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
}

.card-icon {
    display: grid;
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    place-items: center;
    border-radius: 50%;
    background: var(--rose);
    color: var(--wine);
    font-size: 1.4rem;
}

.card-heading h2 {
    flex: 1;
    margin: 0;
    color: var(--wine-dark);
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.35rem;
    font-weight: 500;
}

.info-card p {
    margin: 0 0 12px;
    font-size: 0.96rem;
    line-height: 1.9;
    overflow-wrap: anywhere;
}

.studio-name {
    color: var(--wine);
    font-weight: 700;
}

.info-card h3 {
    margin: 24px 0 10px;
    color: var(--wine-dark);
}

.edit-button {
    display: grid;
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    place-items: center;
    border: 1px solid #ead5da;
    border-radius: 10px;
    background: #fffafb;
    color: var(--wine);
    cursor: pointer;
}

.edit-button:hover {
    background: var(--wine);
    color: white;
}

.info-card form,
.info-card label {
    display: grid;
    gap: 8px;
}

.info-card form {
    gap: 16px;
}

.info-card label {
    color: var(--wine-dark);
    font-size: 0.9rem;
    font-weight: 600;
}

.info-card input,
.info-card textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 12px;
    border: 1px solid #e4cbd2;
    border-radius: 10px;
    background: #fffafb;
    color: var(--text);
    font: inherit;
    font-weight: 400;
}

.info-card textarea {
    resize: vertical;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.actions button {
    padding: 10px 14px;
    border-radius: 9px;
    font: inherit;
    cursor: pointer;
}

.save-button {
    border: 1px solid var(--wine);
    background: var(--wine);
    color: white;
}

.cancel-button {
    border: 1px solid #ead5da;
    background: white;
    color: var(--wine-dark);
}

.back-link {
    padding-top: 10px;
    text-align: center;
}

.back-link a {
    color: var(--wine);
    font-weight: 600;
    text-decoration: none;
}

.back-link a:hover {
    color: var(--wine-dark);
    text-decoration: underline;
}

@media (max-width: 640px) {
    .impressum-hero {
        padding: 54px 22px 46px;
    }

    .impressum-container {
        grid-template-columns: 1fr;
        width: calc(100% - 32px);
        gap: 16px;
    }

    .info-card {
        padding: 24px;
    }

    .full-width,
    .back-link {
        grid-column: auto;
    }
}
</style>
