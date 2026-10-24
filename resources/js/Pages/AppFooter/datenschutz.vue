
<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PagesLayout from '@/Layouts/pagesLayout.vue';

const page = usePage();

// Nutzt die von Inertia bereitgestellten Benutzerdaten,
// sofern auth.user.role in deinem Projekt geteilt wird.
const canEdit = computed(
    () => page.props.auth?.user?.role === 'owner'
);

// Vorläufige Spracheinstellung.
// Später durch die zentrale Sprache deiner Website ersetzen.
const language = ref('de');

const editingSection = ref(null);
const draft = ref({});
const overrides = ref({});

const translations = {
    de: {
        eyebrow: 'SCHÖNHEIT & PFLEGE',
        title: 'Datenschutzerklärung',
        intro: 'Deine Privatsphäre ist uns wichtig. Hier erfährst du, wie personenbezogene Daten verarbeitet werden.',
        back: 'Zurück zur Startseite',
        edit: 'Abschnitt bearbeiten',
        save: 'Änderungen übernehmen',
        cancel: 'Abbrechen',
        owner: 'Owner-Modus: Du kannst die Texte bearbeiten.',
        noticeTitle: 'Hinweis zur Vorlage',
        notice: 'Diese Datenschutzerklärung ist noch nicht fertig für die Veröffentlichung. Alle markierten Angaben müssen anhand der tatsächlichen Website, der verwendeten Dienste und der betrieblichen Abläufe geprüft und ergänzt werden.',
        sections: [
            {
                id: 'controller',
                title: '1. Verantwortliche Stelle',
                description: 'Verantwortliche Stelle im Sinne der Datenschutz-Grundverordnung (DSGVO).',
                fields: [
                    { key: 'name', label: 'Name des Studios / Verantwortlichen', value: '[Name des Nagelstudios / vollständiger Name]' },
                    { key: 'address', label: 'Anschrift', value: '[Straße, Hausnummer, PLZ und Ort]' },
                    { key: 'email', label: 'E-Mail-Adresse', value: '[Datenschutz-Kontakt per E-Mail]' },
                    { key: 'phone', label: 'Telefon', value: '[Telefonnummer, sofern angegeben]' },
                ],
            },
            {
                id: 'overview',
                title: '2. Allgemeine Hinweise zur Datenverarbeitung',
                description: 'Personenbezogene Daten werden nur verarbeitet, soweit hierfür eine Rechtsgrundlage besteht und dies für die jeweiligen Zwecke erforderlich ist.',
                fields: [
                    { key: 'principles', label: 'Weitere Hinweise und Besonderheiten', value: '[Individuelle Hinweise zur Verarbeitung ergänzen.]', multiline: true },
                ],
            },
            {
                id: 'hosting',
                title: '3. Hosting und Server-Protokolle',
                description: 'Beim Aufruf einer Website können technisch erforderliche Verbindungsdaten verarbeitet werden. Welche Daten anfallen und wie lange sie gespeichert werden, hängt vom tatsächlichen Hosting und der Serverkonfiguration ab.',
                fields: [
                    { key: 'provider', label: 'Hosting-Anbieter und Anschrift', value: '[Hosting-Anbieter und dessen Kontaktdaten]' },
                    { key: 'logs', label: 'Verarbeitete Serverdaten', value: '[Tatsächlich erfasste Daten, z. B. IP-Adresse, Zeitpunkt, angeforderte Seite, Browserinformationen]' , multiline: true },
                    { key: 'logPurpose', label: 'Zweck und Rechtsgrundlage', value: '[Zweck, passende Rechtsgrundlage und ggf. berechtigte Interessen konkret angeben.]', multiline: true },
                    { key: 'logRetention', label: 'Speicherdauer', value: '[Tatsächliche Speicherdauer oder Kriterien zur Festlegung nennen.]' },
                ],
            },
            {
                id: 'contact',
                title: '4. Kontaktaufnahme',
                description: 'Wenn du per E-Mail, Telefon oder über ein Kontaktformular Kontakt aufnimmst, können die übermittelten Angaben zur Bearbeitung deiner Anfrage verarbeitet werden.',
                fields: [
                    { key: 'contactChannels', label: 'Tatsächlich angebotene Kontaktwege', value: '[E-Mail, Telefon, Kontaktformular oder weitere tatsächlich angebotene Wege]' },
                    { key: 'contactPurpose', label: 'Zweck und Rechtsgrundlage', value: '[Zweck, passende Rechtsgrundlage und ggf. berechtigte Interessen konkret angeben.]', multiline: true },
                    { key: 'contactRetention', label: 'Speicherdauer', value: '[Aufbewahrungsdauer bzw. Kriterien angeben; gesetzliche Aufbewahrungspflichten berücksichtigen.]', multiline: true },
                ],
            },
            {
                id: 'appointments',
                title: '5. Terminbuchungen und Kundenverwaltung',
                description: 'Dieser Abschnitt ist nur auszufüllen, wenn die Website Terminbuchungen, Kundenkonten oder eine sonstige Kundenverwaltung anbietet.',
                fields: [
                    { key: 'bookingSystem', label: 'Buchungssystem / Anbieter', value: '[Tatsächlich verwendetes System oder „nicht vorhanden“ nach Prüfung]' },
                    { key: 'bookingData', label: 'Verarbeitete Daten und Zweck', value: '[Tatsächlich erhobene Angaben, Verarbeitungszwecke und Rechtsgrundlagen]', multiline: true },
                    { key: 'bookingRetention', label: 'Speicherdauer', value: '[Tatsächliche Speicherdauer und Kriterien angeben.]', multiline: true },
                ],
            },
            {
                id: 'cookies',
                title: '6. Cookies und ähnliche Technologien',
                description: 'Bitte prüfe, welche Cookies, Local-Storage-Einträge, Analyse- oder sonstigen Technologien deine Website tatsächlich einsetzt. Nicht erforderliche Zugriffe auf Informationen im Endgerät können eine Einwilligung nach den einschlägigen gesetzlichen Vorgaben erfordern.',
                fields: [
                    { key: 'technologies', label: 'Tatsächlich eingesetzte Technologien', value: '[Cookies, Local Storage, Analyse-, Marketing- oder Consent-Dienste prüfen und auflisten.]', multiline: true },
                    { key: 'consent', label: 'Einwilligung und Einstellungen', value: '[Beschreiben, welche Einwilligungen benötigt werden und wie sie erteilt bzw. widerrufen werden können.]', multiline: true },
                ],
            },
            {
                id: 'thirdParty',
                title: '7. Eingesetzte externe Dienste',
                description: 'Externe Dienste dürfen hier nicht pauschal angenommen werden. Nenne nur Anbieter, die tatsächlich eingebunden sind, und beschreibe deren Datenverarbeitung.',
                fields: [
                    { key: 'services', label: 'Dienste und Anbieter', value: '[Tatsächlich eingebundene Dienste, Anbieter, Zweck, Rechtsgrundlage und Datenkategorien auflisten.]', multiline: true },
                    { key: 'transfers', label: 'Drittlandübermittlungen', value: '[Prüfen, ob Daten außerhalb des EWR übermittelt werden; ggf. Zielland und Übermittlungsgrundlage angeben.]', multiline: true },
                ],
            },
            {
                id: 'recipients',
                title: '8. Empfänger und Auftragsverarbeitung',
                description: 'Soweit zutreffend, sind Empfänger oder Kategorien von Empfängern und die Rolle der eingesetzten Dienstleister zu beschreiben.',
                fields: [
                    { key: 'recipientList', label: 'Empfänger / Dienstleister', value: '[Tatsächliche Empfänger und Zwecke der Übermittlung angeben.]', multiline: true },
                    { key: 'processors', label: 'Auftragsverarbeitung', value: '[Prüfen, welche Anbieter als Auftragsverarbeiter tätig sind und ob erforderliche Vereinbarungen bestehen.]', multiline: true },
                ],
            },
            {
                id: 'retention',
                title: '9. Speicherdauer',
                description: 'Personenbezogene Daten sollen nur so lange gespeichert werden, wie es für den jeweiligen Zweck erforderlich ist oder gesetzliche Pflichten eine Speicherung verlangen.',
                fields: [
                    { key: 'retentionRules', label: 'Konkrete Speicher- und Löschfristen', value: '[Fristen je Verarbeitung festlegen; gesetzliche Aufbewahrungsfristen und Löschkonzept berücksichtigen.]', multiline: true },
                ],
            },
            {
                id: 'rights',
                title: '10. Deine Datenschutzrechte',
                description: 'Im Rahmen der gesetzlichen Voraussetzungen bestehen insbesondere folgende Rechte:',
                fields: [
                    { key: 'rightsText', label: 'Rechte und Kontakt zur Ausübung', value: 'Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit und – soweit anwendbar – Widerspruch. Eine erteilte Einwilligung kann mit Wirkung für die Zukunft widerrufen werden. Zur Ausübung deiner Rechte kannst du dich an die oben genannte verantwortliche Stelle wenden.', multiline: true },
                ],
            },
            {
                id: 'complaints',
                title: '11. Beschwerderecht bei einer Aufsichtsbehörde',
                description: 'Du hast das Recht, dich bei einer Datenschutz-Aufsichtsbehörde zu beschweren, wenn du der Ansicht bist, dass die Verarbeitung deiner personenbezogenen Daten gegen Datenschutzrecht verstößt.',
                fields: [
                    { key: 'authority', label: 'Zuständige Aufsichtsbehörde', value: '[Zuständige Datenschutzaufsichtsbehörde anhand des Sitzes und der konkreten Umstände prüfen.]', multiline: true },
                ],
            },
            {
                id: 'obligation',
                title: '12. Pflicht zur Bereitstellung von Daten',
                description: 'Soweit Daten für einen Vertrag, eine Anfrage oder eine Funktion benötigt werden, muss erläutert werden, ob ihre Bereitstellung gesetzlich oder vertraglich vorgeschrieben oder erforderlich ist und welche Folgen eine Nichtbereitstellung hat.',
                fields: [
                    { key: 'requiredData', label: 'Erforderliche Angaben und Folgen', value: '[Für die tatsächlichen Funktionen der Website konkret beschreiben; nicht zutreffende Teile entfernen.]', multiline: true },
                ],
            },
            {
                id: 'automation',
                title: '13. Automatisierte Entscheidungen',
                description: 'Falls eine automatisierte Entscheidungsfindung einschließlich Profiling im Sinne von Art. 22 DSGVO stattfindet, sind die gesetzlich erforderlichen Informationen zu ergänzen.',
                fields: [
                    { key: 'automatedDecision', label: 'Automatisierte Entscheidungen / Profiling', value: '[Prüfen, ob solche Verfahren tatsächlich eingesetzt werden, und die erforderlichen Angaben ergänzen.]', multiline: true },
                ],
            },
        ],
    },

    en: {
        eyebrow: 'BEAUTY & CARE',
        title: 'Privacy Policy',
        intro: 'Your privacy matters to us. This notice explains how personal data may be processed.',
        back: 'Back to homepage',
        edit: 'Edit section',
        save: 'Apply changes',
        cancel: 'Cancel',
        owner: 'Owner mode: You can edit the text.',
        noticeTitle: 'Template notice',
        notice: 'This privacy notice is not ready for publication. Verify and complete all marked information based on your actual website, services and business practices.',
        sections: [
            {
                id: 'controller',
                title: '1. Data Controller',
                description: 'The controller responsible for processing personal data under the GDPR.',
                fields: [
                    { key: 'name', label: 'Studio / controller name', value: '[Nail studio name / full legal name]' },
                    { key: 'address', label: 'Address', value: '[Street, postal code and city]' },
                    { key: 'email', label: 'Email address', value: '[Privacy contact email]' },
                    { key: 'phone', label: 'Telephone', value: '[Phone number, if provided]' },
                ],
            },
            {
                id: 'overview',
                title: '2. General Information',
                description: 'Personal data is processed only where there is an applicable legal basis and for the relevant purposes.',
                fields: [
                    { key: 'principles', label: 'Additional information', value: '[Add details specific to your processing activities.]', multiline: true },
                ],
            },
            {
                id: 'hosting',
                title: '3. Hosting and Server Logs',
                description: 'When a website is accessed, technical connection data may be processed. The actual data and retention period depend on your hosting and server configuration.',
                fields: [
                    { key: 'provider', label: 'Hosting provider and address', value: '[Hosting provider and contact details]' },
                    { key: 'logs', label: 'Server data processed', value: '[Verify actual data, e.g. IP address, timestamp, requested page and browser information]', multiline: true },
                    { key: 'logPurpose', label: 'Purpose and legal basis', value: '[Specify the purpose, applicable legal basis and any legitimate interests.]', multiline: true },
                    { key: 'logRetention', label: 'Retention period', value: '[Actual retention period or criteria used to determine it]' },
                ],
            },
            {
                id: 'contact',
                title: '4. Contact',
                description: 'If you contact us by email, phone or a contact form, the information you provide may be processed to handle your enquiry.',
                fields: [
                    { key: 'contactChannels', label: 'Contact channels actually offered', value: '[Email, phone, contact form or other available channels]' },
                    { key: 'contactPurpose', label: 'Purpose and legal basis', value: '[Specify the purpose, applicable legal basis and any legitimate interests.]', multiline: true },
                    { key: 'contactRetention', label: 'Retention period', value: '[Retention criteria; consider statutory retention obligations.]', multiline: true },
                ],
            },
            {
                id: 'appointments',
                title: '5. Appointments and Customer Management',
                description: 'Complete this section only if your website provides booking, customer accounts or customer management features.',
                fields: [
                    { key: 'bookingSystem', label: 'Booking system / provider', value: '[Actual system or “not used”, after verification]' },
                    { key: 'bookingData', label: 'Data and purposes', value: '[Actual data collected, purposes and legal bases]', multiline: true },
                    { key: 'bookingRetention', label: 'Retention period', value: '[Actual retention period and criteria]', multiline: true },
                ],
            },
            {
                id: 'cookies',
                title: '6. Cookies and Similar Technologies',
                description: 'Check which cookies, local storage entries, analytics or other technologies your website actually uses. Certain non-essential access to information on a device may require consent under applicable law.',
                fields: [
                    { key: 'technologies', label: 'Technologies actually used', value: '[Review and list cookies, local storage, analytics, marketing and consent tools.]', multiline: true },
                    { key: 'consent', label: 'Consent and settings', value: '[Explain applicable consent requirements and how consent can be withdrawn.]', multiline: true },
                ],
            },
            {
                id: 'thirdParty',
                title: '7. Third-Party Services',
                description: 'Do not assume third-party services are used. List only services actually integrated and describe their processing.',
                fields: [
                    { key: 'services', label: 'Services and providers', value: '[List actual providers, purposes, legal bases and data categories.]', multiline: true },
                    { key: 'transfers', label: 'International transfers', value: '[Check whether data is transferred outside the EEA and specify safeguards where applicable.]', multiline: true },
                ],
            },
            {
                id: 'recipients',
                title: '8. Recipients and Processors',
                description: 'Where applicable, identify recipients or categories of recipients and the role of service providers.',
                fields: [
                    { key: 'recipientList', label: 'Recipients / providers', value: '[Specify actual recipients and purposes of disclosure.]', multiline: true },
                    { key: 'processors', label: 'Data processors', value: '[Identify actual processors and verify required processing agreements.]', multiline: true },
                ],
            },
            {
                id: 'retention',
                title: '9. Data Retention',
                description: 'Personal data should be retained only as long as necessary for its purpose or as required by law.',
                fields: [
                    { key: 'retentionRules', label: 'Retention and deletion periods', value: '[Specify periods by processing activity and account for statutory retention obligations.]', multiline: true },
                ],
            },
            {
                id: 'rights',
                title: '10. Your Data Protection Rights',
                description: 'Subject to the applicable legal requirements, you may have the following rights:',
                fields: [
                    { key: 'rightsText', label: 'Rights and contact', value: 'Rights may include access, rectification, erasure, restriction of processing, data portability and, where applicable, objection. Consent may be withdrawn with future effect. Contact the controller listed above to exercise your rights.', multiline: true },
                ],
            },
            {
                id: 'complaints',
                title: '11. Right to Lodge a Complaint',
                description: 'You may lodge a complaint with a data protection supervisory authority if you believe that your personal data is being processed unlawfully.',
                fields: [
                    { key: 'authority', label: 'Competent supervisory authority', value: '[Identify the appropriate authority based on the controller and circumstances.]', multiline: true },
                ],
            },
            {
                id: 'obligation',
                title: '12. Requirement to Provide Data',
                description: 'Where data is needed for a contract, enquiry or website feature, explain whether it is required by law or contract and the consequences of not providing it.',
                fields: [
                    { key: 'requiredData', label: 'Required data and consequences', value: '[Describe the actual website functions and remove irrelevant statements.]', multiline: true },
                ],
            },
            {
                id: 'automation',
                title: '13. Automated Decisions',
                description: 'If automated decision-making, including profiling under Article 22 GDPR, takes place, provide the information required by law.',
                fields: [
                    { key: 'automatedDecision', label: 'Automated decisions / profiling', value: '[Verify whether such processes are actually used and add the required details.]', multiline: true },
                ],
            },
        ],
    },
};

const t = computed(() => translations[language.value]);

function fieldValue(sectionId, key, fallback) {
    return overrides.value[`${sectionId}.${key}`] ?? fallback;
}

function startEditing(section) {
    draft.value = {};

    for (const field of section.fields) {
        draft.value[field.key] = fieldValue(
            section.id,
            field.key,
            field.value
        );
    }

    editingSection.value = section.id;
}

function applyChanges(section) {
    for (const field of section.fields) {
        overrides.value[`${section.id}.${field.key}`] =
            draft.value[field.key];
    }

    editingSection.value = null;
}

function cancelEditing() {
    editingSection.value = null;
    draft.value = {};
}
</script>

<template>
    <pagesLayout>
    <Head :title="t.title" />

    <div class="privacy-page">
        <section class="privacy-hero">
            <span class="eyebrow">{{ t.eyebrow }}</span>
            <h1>{{ t.title }}</h1>
            <p>{{ t.intro }}</p>
            <div class="hero-line"></div>

            <div class="language-switch" aria-label="Language">
                <button
                    type="button"
                    :class="{ active: language === 'de' }"
                    :aria-pressed="language === 'de'"
                    @click="language = 'de'"
                >
                    DE
                </button>
                <button
                    type="button"
                    :class="{ active: language === 'en' }"
                    :aria-pressed="language === 'en'"
                    @click="language = 'en'"
                >
                    EN
                </button>
            </div>
        </section>

        <main class="privacy-container">
            <div v-if="canEdit" class="owner-notice">
                <span class="owner-dot"></span>
                {{ t.owner }}
            </div>

            <aside class="template-notice">
                <strong>{{ t.noticeTitle }}</strong>
                <p>{{ t.notice }}</p>
            </aside>

            <section
                v-for="section in t.sections"
                :key="section.id"
                class="info-card"
            >
                <div class="card-heading">
                    <div class="card-icon" aria-hidden="true">♡</div>
                    <h2>{{ section.title }}</h2>

                    <button
                        v-if="canEdit"
                        type="button"
                        class="edit-button"
                        :aria-label="`${t.edit}: ${section.title}`"
                        @click="startEditing(section)"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            width="18"
                            height="18"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
                        </svg>
                    </button>
                </div>

                <p class="section-description">
                    {{ section.description }}
                </p>

                <form
                    v-if="canEdit && editingSection === section.id"
                    class="edit-form"
                    @submit.prevent="applyChanges(section)"
                >
                    <div
                        v-for="field in section.fields"
                        :key="field.key"
                        class="form-field"
                    >
                        <label :for="`${section.id}-${field.key}`">
                            {{ field.label }}
                        </label>

                        <textarea
                            v-if="field.multiline"
                            :id="`${section.id}-${field.key}`"
                            v-model="draft[field.key]"
                            rows="4"
                        />

                        <input
                            v-else
                            :id="`${section.id}-${field.key}`"
                            v-model="draft[field.key]"
                            type="text"
                        />
                    </div>

                    <div class="edit-actions">
                        <button
                            type="button"
                            class="cancel-button"
                            @click="cancelEditing"
                        >
                            {{ t.cancel }}
                        </button>

                        <button type="submit" class="save-button">
                            {{ t.save }}
                        </button>
                    </div>
                </form>

                <div v-else class="section-content">
                    <div
                        v-for="field in section.fields"
                        :key="field.key"
                        class="content-field"
                    >
                        <h3>{{ field.label }}</h3>
                        <p class="preserve-lines">
                            {{ fieldValue(section.id, field.key, field.value) }}
                        </p>
                    </div>
                </div>
            </section>

            <div class="back-link">
                <Link href="/">
                    <span aria-hidden="true">←</span>
                    {{ t.back }}
                </Link>
            </div>
        </main>
    </div>
    </pagesLayout>
</template>

<style scoped>
.privacy-page {
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

.privacy-hero {
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

.privacy-hero h1 {
    margin: 16px 0;
    color: var(--wine-dark);
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2.2rem, 6vw, 3.8rem);
    font-weight: 500;
}

.privacy-hero > p {
    max-width: 620px;
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

.privacy-container {
    display: grid;
    gap: 22px;
    width: min(100% - 40px, 900px);
    margin: 0 auto;
}

.owner-notice {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border: 1px solid #ead5da;
    border-radius: 14px;
    background: var(--rose);
    color: var(--wine-dark);
    font-size: 0.92rem;
}

.owner-dot {
    flex: 0 0 8px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--wine);
}

.template-notice {
    padding: 20px 22px;
    border: 1px solid #ead5da;
    border-left: 4px solid var(--wine);
    border-radius: 12px;
    background: #fff;
    line-height: 1.7;
}

.template-notice strong {
    color: var(--wine-dark);
}

.template-notice p {
    margin: 8px 0 0;
    color: var(--muted);
}

.info-card {
    min-width: 0;
    padding: 30px;
    border: 1px solid #f0e2e6;
    border-radius: 18px;
    background: white;
    box-shadow: 0 8px 28px rgb(117 67 83 / 5%);
}

.card-heading {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
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
    min-width: 0;
    margin: 0;
    color: var(--wine-dark);
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.35rem;
    font-weight: 500;
}

.section-description {
    margin: 0 0 20px;
    color: var(--muted);
    font-size: 0.94rem;
    line-height: 1.8;
}

.content-field {
    margin-top: 18px;
}

.content-field:first-child {
    margin-top: 0;
}

.content-field h3 {
    margin: 0 0 7px;
    color: var(--wine-dark);
    font-size: 0.95rem;
    font-weight: 700;
}

.content-field p {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.85;
    overflow-wrap: anywhere;
}

.preserve-lines {
    white-space: pre-line;
}

.edit-button {
    display: grid;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    place-items: center;
    border: 1px solid #ead5da;
    border-radius: 11px;
    background: #fffafb;
    color: var(--wine);
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.edit-button:hover {
    background: var(--wine);
    color: white;
}

.edit-form {
    display: grid;
    gap: 18px;
}

.form-field {
    display: grid;
    gap: 8px;
}

.form-field label {
    color: var(--wine-dark);
    font-size: 0.9rem;
    font-weight: 600;
}

.form-field input,
.form-field textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    border: 1px solid #e4cbd2;
    border-radius: 10px;
    background: #fffafb;
    color: var(--text);
    font: inherit;
    line-height: 1.6;
}

.form-field textarea {
    resize: vertical;
}

.form-field input:focus,
.form-field textarea:focus {
    border-color: var(--wine);
    outline: 2px solid rgb(143 83 100 / 15%);
}

.edit-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 10px;
}

.edit-actions button {
    padding: 11px 15px;
    border-radius: 9px;
    font: inherit;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.save-button {
    border: 1px solid var(--wine);
    background: var(--wine);
    color: white;
}

.save-button:hover {
    background: var(--wine-dark);
}

.cancel-button {
    border: 1px solid #ead5da;
    background: white;
    color: var(--wine-dark);
}

.back-link {
    padding-top: 8px;
    text-align: center;
}

.back-link a {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: var(--wine);
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
}

.back-link a:hover {
    color: var(--wine-dark);
    text-decoration: underline;
}

@media (max-width: 640px) {
    .privacy-hero {
        padding: 54px 22px 46px;
    }

    .privacy-container {
        width: calc(100% - 32px);
        gap: 16px;
    }

    .info-card {
        padding: 23px;
        border-radius: 15px;
    }

    .card-heading {
        gap: 10px;
    }

    .card-heading h2 {
        font-size: 1.2rem;
    }
}
</style>
