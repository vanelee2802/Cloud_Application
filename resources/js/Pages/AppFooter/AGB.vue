
<template>
    <pagesLayout>
    <div class="agb-page">
        <div class="agb-container">
            <!-- Kopfbereich -->
            <header class="agb-header">
                <div class="header-decoration">✦</div>

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

            <!-- Hinweis für den Owner -->
            <div v-if="canEdit" class="owner-notice">
                <span class="owner-notice-icon">✎</span>
                <div>
                    <strong>{{ t.ownerModeTitle }}</strong>
                    <p>{{ t.ownerModeDescription }}</p>
                </div>
            </div>

            <!-- AGB-Abschnitte -->
            <main class="agb-content">
                <article
                    v-for="(section, index) in t.sections"
                    :key="`${language}-${section.id}`"
                    class="agb-card"
                >
                    <div class="section-number">
                        {{ String(index + 1).padStart(2, '0') }}
                    </div>

                    <div class="section-body">
                        <div class="section-heading">
                            <h2>{{ section.title }}</h2>

                            <button
                                v-if="canEdit"
                                type="button"
                                class="edit-button"
                                :aria-label="`${t.editButton}: ${section.title}`"
                                :title="t.editButton"
                                @click="startEditing(section)"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="17"
                                    height="17"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M12 20h9" />
                                    <path
                                        d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Bearbeitungsmodus -->
                        <div
                            v-if="editingSection === section.id"
                            class="editor"
                        >
                            <label
                                class="editor-label"
                                :for="`editor-${section.id}`"
                            >
                                {{ t.editingLabel }}
                            </label>

                            <textarea
                                :id="`editor-${section.id}`"
                                v-model="draft"
                                class="edit-textarea"
                                rows="9"
                            ></textarea>

                            <div class="editor-actions">
                                <button
                                    type="button"
                                    class="save-button"
                                    @click="applyChanges"
                                >
                                    {{ t.saveButton }}
                                </button>

                                <button
                                    type="button"
                                    class="cancel-button"
                                    @click="cancelEditing"
                                >
                                    {{ t.cancelButton }}
                                </button>

                                <button
                                    v-if="hasOverride(section.id)"
                                    type="button"
                                    class="reset-button"
                                    @click="resetSection(section)"
                                >
                                    {{ t.resetButton }}
                                </button>
                            </div>

                            <p class="editor-hint">
                                {{ t.localSaveHint }}
                            </p>
                        </div>

                        <!-- Normale Ansicht -->
                        <p
                            v-else
                            class="section-text"
                        >{{ sectionText(section) }}</p>
                    </div>
                </article>
            </main>

            <!-- Fußzeile -->
            <footer class="agb-footer">
                <div class="footer-flower">✿</div>
                <p>{{ t.footerText }}</p>
            </footer>
        </div>
    </div>
</pagesLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import pagesLayout from '@/Layouts/pagesLayout.vue'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Owner-Berechtigung
|--------------------------------------------------------------------------
| Voraussetzung: Laravel/Inertia stellt auth.user.role bereit.
| Die Prüfung im Frontend dient nur der Darstellung.
| Später müssen Änderungen auch serverseitig autorisiert werden.
*/

const canEdit = computed(() => {
    return page.props.auth?.user?.role === 'owner'
})

/*
|--------------------------------------------------------------------------
| Verfügbare Sprachen
|--------------------------------------------------------------------------
| Weitere Sprachen können später ergänzt werden, z. B.:
| { code: 'fr', label: 'FR' }
*/

const availableLanguages = [
    { code: 'de', label: 'DE' },
    { code: 'en', label: 'EN' },
]

const language = ref('de')

/*
|--------------------------------------------------------------------------
| Übersetzungen und Standardtexte
|--------------------------------------------------------------------------
| Jede Sprache hat dieselben Abschnitts-IDs.
| Dadurch lassen sich Texte später einfach aus einer API oder Datenbank laden.
*/

const translations = {
    de: {
        eyebrow: 'Gut zu wissen',
        pageTitle: 'Allgemeine Geschäftsbedingungen',
        description:
            'Transparenz, Vertrauen und schöne Nägel – hier findest du unsere allgemeinen Geschäftsbedingungen.',
        ownerModeTitle: 'Owner-Bearbeitungsmodus',
        ownerModeDescription:
            'Du kannst die Texte bearbeiten. Änderungen gelten momentan nur in dieser geöffneten Ansicht und werden nicht dauerhaft gespeichert.',
        editButton: 'Text bearbeiten',
        editingLabel: 'Abschnittstext bearbeiten',
        saveButton: 'Übernehmen',
        cancelButton: 'Abbrechen',
        resetButton: 'Standardtext wiederherstellen',
        localSaveHint:
            'Hinweis: Die Änderung wird nur lokal im aktuellen Frontend übernommen. Beim Neuladen der Seite geht sie verloren.',
        footerText:
            'Vielen Dank für dein Vertrauen und deinen Besuch.',
        sections: [
            {
                id: 'scope',
                title: '1. Geltungsbereich',
                text: `Diese Allgemeinen Geschäftsbedingungen gelten für die angebotenen Dienstleistungen des Nagelstudios.

Mit der Vereinbarung eines Termins erkennen Kundinnen und Kunden diese Bedingungen an.

Abweichende Vereinbarungen können individuell getroffen werden.`,
            },
            {
                id: 'services',
                title: '2. Leistungen',
                text: `Das Nagelstudio bietet kosmetische Nageldienstleistungen an. Art und Umfang der Behandlung werden vor Beginn mit der Kundin oder dem Kunden abgestimmt.

Das Ergebnis kann abhängig von der natürlichen Beschaffenheit der Nägel, dem Nagelzustand und der gewählten Gestaltung variieren.

Referenzbilder dienen als Orientierung. Eine vollkommen identische Umsetzung kann nicht in jedem Fall garantiert werden.`,
            },
            {
                id: 'appointments',
                title: '3. Terminvereinbarung',
                text: `Termine können über die angebotenen Kontakt- und Buchungsmöglichkeiten vereinbart werden.

Ein Termin gilt als vereinbart, sobald er vom Nagelstudio bestätigt wurde.

Kundinnen und Kunden werden gebeten, pünktlich zum vereinbarten Termin zu erscheinen.`,
            },
            {
                id: 'prices',
                title: '4. Preise und Zahlung',
                text: `Es gelten die zum Zeitpunkt der Buchung kommunizierten Preise.

Zusätzliche Leistungen oder nachträgliche Änderungen des gewünschten Designs können den Preis verändern. Solche Änderungen werden möglichst vor der Durchführung besprochen.

Die Zahlung erfolgt nach der Behandlung über die vom Nagelstudio angebotenen Zahlungsmöglichkeiten.`,
            },
            {
                id: 'cancellation',
                title: '5. Terminabsagen und Verspätungen',
                text: `Sollte ein Termin nicht wahrgenommen werden können, wird um eine möglichst frühzeitige Absage gebeten.

Bei Verspätungen kann sich die Behandlungszeit verkürzen. Ist eine ordnungsgemäße Durchführung dadurch nicht mehr möglich, kann ein neuer Termin vereinbart werden.

Etwaige Ausfallgebühren oder besondere Stornierungsbedingungen müssen vorab transparent mitgeteilt werden und rechtlich zulässig sein.`,
            },
            {
                id: 'nailHealth',
                title: '6. Nagelgesundheit und Behandlung',
                text: `Kundinnen und Kunden sollen vor der Behandlung auf bekannte Allergien, Unverträglichkeiten, Verletzungen oder sonstige relevante Beschwerden im Nagelbereich hinweisen.

Bei Anzeichen einer möglichen Infektion, offenen Wunden oder anderen Umständen, die eine Behandlung ungeeignet erscheinen lassen, kann das Nagelstudio die Behandlung ablehnen oder abbrechen.

Die Behandlung dient kosmetischen Zwecken und ersetzt keine medizinische Beratung oder Behandlung.`,
            },
            {
                id: 'photoPublication',
                title: '7. Veröffentlichung von Nageldesigns und Fotos',
                text: `Das Nagelstudio möchte angefertigte Nageldesigns fotografieren und die Aufnahmen zu Werbe- und Präsentationszwecken verwenden. Dazu gehören beispielsweise die Website des Studios, soziale Medien wie Instagram oder TikTok sowie ein digitales oder gedrucktes Portfolio.

Die Veröffentlichung soll sich nach Möglichkeit auf das Nageldesign beschränken. Werden auf einem Foto darüber hinaus Hände, Tätowierungen, Schmuck oder andere Merkmale sichtbar, durch die eine Person identifizierbar sein kann, wird die Veröffentlichung nur auf Grundlage einer geeigneten Rechtsgrundlage vorgenommen.

Soweit eine Einwilligung erforderlich ist, wird diese gesondert und freiwillig eingeholt. Die Ablehnung einer solchen Einwilligung hat keinen Einfluss auf die Behandlung oder den vereinbarten Preis.

Eine erteilte Einwilligung kann mit Wirkung für die Zukunft widerrufen werden. Gesetzliche Rechte der abgebildeten Personen bleiben unberührt.`,
            },
            {
                id: 'complaints',
                title: '8. Beanstandungen',
                text: `Sollte es Fragen oder Beanstandungen zum Behandlungsergebnis geben, wird darum gebeten, das Nagelstudio möglichst zeitnah zu kontaktieren.

Gemeinsam soll eine angemessene Lösung gefunden werden. Gesetzliche Rechte der Kundinnen und Kunden bleiben unberührt.`,
            },
            {
                id: 'vouchers',
                title: '9. Gutscheine',
                text: `Sofern Gutscheine angeboten werden, gelten die bei deren Erwerb mitgeteilten Bedingungen.

Gesetzliche Verjährungs- und Verbraucherschutzvorschriften bleiben unberührt.`,
            },
            {
                id: 'liability',
                title: '10. Haftung',
                text: `Das Nagelstudio haftet nach den gesetzlichen Vorschriften.

Eine Haftungsbeschränkung gilt nicht bei Vorsatz, grober Fahrlässigkeit, Verletzung von Leben, Körper oder Gesundheit sowie in sonstigen Fällen, in denen eine Haftungsbeschränkung gesetzlich unzulässig ist.`,
            },
            {
                id: 'withdrawal',
                title: '11. Widerrufsrecht bei Fernabsatzverträgen',
                text: `Bei online oder außerhalb der Geschäftsräume geschlossenen Verträgen können gesetzliche Verbraucherrechte, einschließlich eines Widerrufsrechts, bestehen.

Ob ein Widerrufsrecht besteht und ob Ausnahmen gelten, hängt vom konkreten Vertrag und den gesetzlichen Voraussetzungen ab. Maßgeblich sind die gesetzlichen Bestimmungen und die im Einzelfall bereitgestellten Informationen.`,
            },
            {
                id: 'final',
                title: '12. Schlussbestimmungen',
                text: `Es gilt das Recht der Bundesrepublik Deutschland, soweit dem keine zwingenden gesetzlichen Vorschriften entgegenstehen.

Sollten einzelne Bestimmungen unwirksam sein, bleiben die übrigen Bestimmungen im Rahmen der gesetzlichen Vorschriften unberührt.`,
            },
        ],
    },

    en: {
        eyebrow: 'Good to know',
        pageTitle: 'Terms and Conditions',
        description:
            'Transparency, trust and beautiful nails – here you can find our terms and conditions.',
        ownerModeTitle: 'Owner editing mode',
        ownerModeDescription:
            'You can edit the text. For now, changes only apply to this open view and are not permanently saved.',
        editButton: 'Edit text',
        editingLabel: 'Edit section text',
        saveButton: 'Apply',
        cancelButton: 'Cancel',
        resetButton: 'Restore default text',
        localSaveHint:
            'Note: Changes are only applied locally in the current frontend. They will be lost when the page is reloaded.',
        footerText: 'Thank you for your trust and your visit.',
        sections: [
            {
                id: 'scope',
                title: '1. Scope',
                text: `These Terms and Conditions apply to the services offered by the nail studio.

By booking an appointment, clients acknowledge these terms.

Individual arrangements may be agreed separately.`,
            },
            {
                id: 'services',
                title: '2. Services',
                text: `The studio offers cosmetic nail services. The type and scope of the treatment are discussed with the client before the treatment begins.

Results may vary depending on the natural condition of the nails, their current state and the selected design.

Reference images are intended as inspiration. An identical result cannot always be guaranteed.`,
            },
            {
                id: 'appointments',
                title: '3. Appointments',
                text: `Appointments can be booked through the contact and booking options provided by the studio.

An appointment is considered confirmed once the studio has accepted it.

Clients are asked to arrive on time for their appointment.`,
            },
            {
                id: 'prices',
                title: '4. Prices and Payment',
                text: `The prices communicated at the time of booking apply.

Additional services or changes to the requested design may affect the final price. Such changes should be discussed before the work is carried out whenever possible.

Payment is due after the treatment using the payment methods offered by the studio.`,
            },
            {
                id: 'cancellation',
                title: '5. Cancellations and Late Arrivals',
                text: `If a client cannot attend an appointment, the studio asks for as much advance notice as possible.

Late arrivals may reduce the available treatment time. If the treatment can no longer be carried out properly, a new appointment may be arranged.

Any cancellation fees or special cancellation terms must be communicated transparently in advance and must comply with applicable law.`,
            },
            {
                id: 'nailHealth',
                title: '6. Nail Health and Treatment',
                text: `Clients should inform the studio before treatment about known allergies, intolerances, injuries or other relevant conditions affecting the nail area.

If there are signs of a possible infection, open wounds or other circumstances that make treatment unsuitable, the studio may refuse or discontinue the treatment.

Treatments are cosmetic and do not replace medical advice or medical care.`,
            },
            {
                id: 'photoPublication',
                title: '7. Publication of Nail Designs and Photos',
                text: `The nail studio may wish to photograph completed nail designs and use the images for promotional and presentation purposes. This may include the studio website, social media platforms such as Instagram or TikTok, and a digital or printed portfolio.

Where possible, published images will focus on the nail design itself. If a photo also shows hands, tattoos, jewellery or other features that may make a person identifiable, publication will take place only on an appropriate legal basis.

Where consent is required, it will be obtained separately and voluntarily. Refusing consent will not affect the treatment or the agreed price.

Consent may be withdrawn with effect for the future. The legal rights of the people depicted remain unaffected.`,
            },
            {
                id: 'complaints',
                title: '8. Complaints',
                text: `If clients have questions or concerns about the treatment result, they are asked to contact the studio as soon as reasonably possible.

The studio will seek an appropriate solution together with the client. Clients' statutory rights remain unaffected.`,
            },
            {
                id: 'vouchers',
                title: '9. Vouchers',
                text: `If vouchers are offered, the terms communicated at the time of purchase apply.

Statutory limitation periods and consumer protection rights remain unaffected.`,
            },
            {
                id: 'liability',
                title: '10. Liability',
                text: `The studio is liable in accordance with applicable law.

No limitation of liability applies in cases of intent, gross negligence, injury to life, body or health, or any other circumstances in which a limitation of liability is prohibited by law.`,
            },
            {
                id: 'withdrawal',
                title: '11. Right of Withdrawal for Distance Contracts',
                text: `For contracts concluded online or outside business premises, statutory consumer rights, including a right of withdrawal, may apply.

Whether a right of withdrawal exists and whether an exception applies depends on the specific contract and statutory requirements. Applicable law and the information provided in the individual case are decisive.`,
            },
            {
                id: 'final',
                title: '12. Final Provisions',
                text: `The laws of the Federal Republic of Germany apply, insofar as mandatory statutory provisions do not provide otherwise.

If any provision is invalid, the remaining provisions remain unaffected to the extent permitted by law.`,
            },
        ],
    },
}

/*
|--------------------------------------------------------------------------
| Lokale Owner-Änderungen
|--------------------------------------------------------------------------
| Schlüssel: Sprache + Abschnitts-ID
| Beispiel: de.photoPublication
|
| Später kann dieses Objekt durch Daten aus Laravel ersetzt werden.
*/

const overrides = ref({})

const editingSection = ref(null)
const draft = ref('')

const t = computed(() => {
    return translations[language.value] ?? translations.de
})

function changeLanguage(code) {
    if (!translations[code]) return

    cancelEditing()
    language.value = code
}

function overrideKey(section) {
    return `${language.value}.${section.id}`
}

function hasOverride(sectionId) {
    return Object.prototype.hasOwnProperty.call(
        overrides.value,
        `${language.value}.${sectionId}`,
    )
}

function sectionText(section) {
    const key = overrideKey(section)

    if (Object.prototype.hasOwnProperty.call(overrides.value, key)) {
        return overrides.value[key]
    }

    return section.text
}

function startEditing(section) {
    if (!canEdit.value) return

    editingSection.value = section.id
    draft.value = sectionText(section)
}

function applyChanges() {
    if (!canEdit.value || editingSection.value === null) return

    const key = `${language.value}.${editingSection.value}`

    overrides.value = {
        ...overrides.value,
        [key]: draft.value,
    }

    editingSection.value = null
    draft.value = ''
}

function cancelEditing() {
    editingSection.value = null
    draft.value = ''
}

function resetSection(section) {
    if (!canEdit.value) return

    const key = overrideKey(section)
    const updated = { ...overrides.value }

    delete updated[key]

    overrides.value = updated
    cancelEditing()
}
</script>

<style scoped>
.agb-page {
    min-height: 100vh;
    padding: 56px 20px 70px;
    background:
        radial-gradient(
            circle at top left,
            rgba(229, 193, 201, 0.22),
            transparent 34%
        ),
        #fbf7f7;
    color: #4c353b;
    font-family: inherit;
}

.agb-container {
    width: 100%;
    max-width: 940px;
    margin: 0 auto;
}

/* Kopfbereich */

.agb-header {
    position: relative;
    margin-bottom: 42px;
    padding: 46px 30px 36px;
    overflow: hidden;
    text-align: center;
    background: #fffdfd;
    border: 1px solid #f0e1e4;
    border-radius: 24px;
    box-shadow: 0 12px 36px rgba(117, 67, 83, 0.07);
}

.header-decoration {
    margin-bottom: 12px;
    color: #a66e7e;
    font-size: 30px;
}

.eyebrow {
    margin: 0 0 12px;
    color: #9a6272;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}

.agb-header h1 {
    margin: 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(30px, 5vw, 43px);
    font-weight: 500;
    line-height: 1.2;
}

.header-description {
    max-width: 580px;
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

/* Sprachauswahl */

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
    transition:
        background 0.2s ease,
        color 0.2s ease,
        box-shadow 0.2s ease;
}

.language-switch button:hover {
    color: #754353;
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

.owner-notice-icon {
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
    margin: 0 0 5px;
    font-size: 14px;
}

.owner-notice p {
    margin: 0;
    color: #80636c;
    font-size: 13px;
    line-height: 1.6;
}

/* Inhaltskarten */

.agb-content {
    display: flex;
    flex-direction: column;
    gap: 17px;
}

.agb-card {
    display: grid;
    grid-template-columns: 48px minmax(0, 1fr);
    gap: 20px;
    padding: 28px 30px;
    background: #fffdfd;
    border: 1px solid #f0e4e6;
    border-radius: 18px;
    box-shadow: 0 5px 18px rgba(117, 67, 83, 0.035);
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.agb-card:hover {
    border-color: #e7cbd3;
    box-shadow: 0 8px 23px rgba(117, 67, 83, 0.065);
}

.section-number {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    color: #8f5364;
    background: #f8ecef;
    border: 1px solid #f0dfe3;
    border-radius: 13px;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 14px;
}

.section-body {
    min-width: 0;
}

.section-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 13px;
}

.section-heading h2 {
    margin: 5px 0 0;
    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 21px;
    font-weight: 500;
    line-height: 1.4;
}

.section-text {
    margin: 0;
    color: #725f64;
    font-size: 14px;
    line-height: 1.95;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

/* Bearbeiten-Button */

.edit-button {
    display: grid;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    place-items: center;
    color: #8f5364;
    background: #fbf0f3;
    border: 1px solid #efdce1;
    border-radius: 11px;
    cursor: pointer;
    transition:
        color 0.2s ease,
        background 0.2s ease,
        transform 0.2s ease;
}

.edit-button:hover {
    color: #fff;
    background: #8f5364;
    transform: translateY(-1px);
}

/* Editor */

.editor {
    margin-top: 12px;
}

.editor-label {
    display: block;
    margin-bottom: 8px;
    color: #754353;
    font-size: 12px;
    font-weight: 700;
}

.edit-textarea {
    display: block;
    width: 100%;
    min-height: 190px;
    padding: 14px;
    color: #4c353b;
    background: #fffafa;
    border: 1px solid #dfbdc7;
    border-radius: 12px;
    outline: none;
    font: inherit;
    font-size: 14px;
    line-height: 1.8;
    resize: vertical;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.edit-textarea:focus {
    border-color: #8f5364;
    box-shadow: 0 0 0 3px rgba(143, 83, 100, 0.1);
}

.editor-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 12px;
}

.editor-actions button {
    padding: 10px 15px;
    border-radius: 9px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.save-button {
    color: #fff;
    background: #8f5364;
    border: 1px solid #8f5364;
}

.save-button:hover {
    background: #754353;
}

.cancel-button {
    color: #754353;
    background: #fff;
    border: 1px solid #e6cbd2;
}

.cancel-button:hover {
    background: #f8ecef;
}

.reset-button {
    color: #8a5363;
    background: #f8ecef;
    border: 1px solid #efdce1;
}

.reset-button:hover {
    background: #f0dce2;
}

.editor-hint {
    margin: 12px 0 0;
    color: #927780;
    font-size: 12px;
    line-height: 1.6;
}

/* Fußzeile */

.agb-footer {
    padding: 38px 15px 0;
    text-align: center;
}

.footer-flower {
    margin-bottom: 10px;
    color: #ad7887;
    font-size: 25px;
}

.agb-footer p {
    margin: 0;
    color: #967b83;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 15px;
    font-style: italic;
}

/* Tastaturbedienung */

button:focus-visible {
    outline: 3px solid rgba(143, 83, 100, 0.35);
    outline-offset: 3px;
}

/* Responsive Design */

@media (max-width: 640px) {
    .agb-page {
        padding: 24px 12px 40px;
    }

    .agb-header {
        margin-bottom: 24px;
        padding: 34px 18px 27px;
        border-radius: 19px;
    }

    .agb-header h1 {
        font-size: 30px;
    }

    .header-description {
        font-size: 14px;
    }

    .agb-content {
        gap: 12px;
    }

    .agb-card {
        grid-template-columns: 1fr;
        gap: 13px;
        padding: 20px 18px;
        border-radius: 15px;
    }

    .section-number {
        width: 35px;
        height: 35px;
        border-radius: 10px;
        font-size: 12px;
    }

    .section-heading {
        gap: 10px;
    }

    .section-heading h2 {
        margin-top: 2px;
        font-size: 19px;
    }

    .section-text {
        font-size: 13px;
        line-height: 1.85;
    }

    .owner-notice {
        padding: 14px;
    }

    .editor-actions button {
        flex-grow: 1;
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
