<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    receipts: {
        type: Array,
        default: () => [],
    },
    // 'customer' = eigene Rechnungen, 'studio' = Rechnungen des Studios
    mode: {
        type: String,
        default: 'customer',
    },
})

const isStudio = computed(() => props.mode === 'studio')

/*
|--------------------------------------------------------------------------
| Formatierung
|--------------------------------------------------------------------------
*/

const euroFormat = new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
})

function euro(cents) {
    return euroFormat.format((cents ?? 0) / 100)
}

function formatDate(date) {
    if (!date) {
        return '—'
    }

    return new Date(date).toLocaleDateString('de-DE')
}

function monthKey(date) {
    // "2026-10-24T16:41:17.000000Z" → "2026-10"
    return String(date).slice(0, 7)
}

function monthLabel(key) {
    const [year, month] = key.split('-')

    return new Date(Number(year), Number(month) - 1, 1).toLocaleDateString('de-DE', {
        month: 'long',
        year: 'numeric',
    })
}

/*
|--------------------------------------------------------------------------
| Monatsfilter (nur Studio-Ansicht)
|--------------------------------------------------------------------------
*/

const selectedMonth = ref('all')

const months = computed(() => {
    const keys = new Set(props.receipts.map((r) => monthKey(r.issued_at)))

    return [...keys].sort().reverse()
})

const filteredReceipts = computed(() => {
    if (selectedMonth.value === 'all') {
        return props.receipts
    }

    return props.receipts.filter(
        (r) => monthKey(r.issued_at) === selectedMonth.value
    )
})

const totals = computed(() => {
    return filteredReceipts.value.reduce(
        (sum, r) => ({
            net: sum.net + (r.net_cents ?? 0),
            vat: sum.vat + (r.vat_cents ?? 0),
            gross: sum.gross + (r.gross_cents ?? 0),
        }),
        { net: 0, vat: 0, gross: 0 }
    )
})

/*
|--------------------------------------------------------------------------
| CSV-Export für Buchhaltung / Steuerberatung (nur Studio-Ansicht)
|--------------------------------------------------------------------------
*/

function csvValue(value) {
    const text = String(value ?? '')

    return /[;"\n]/.test(text) ? `"${text.replace(/"/g, '""')}"` : text
}

function centsForCsv(cents) {
    // Deutsches Zahlenformat ohne Währungszeichen, z. B. 21,01
    return ((cents ?? 0) / 100).toFixed(2).replace('.', ',')
}

function exportCsv() {
    const header = [
        'Rechnungsnummer',
        'Rechnungsdatum',
        'Leistungsdatum',
        'Studio',
        'Kundin',
        'Netto',
        'USt',
        'Brutto',
    ]

    const rows = filteredReceipts.value.map((r) => [
        r.number,
        formatDate(r.issued_at),
        formatDate(r.service_date),
        r.studio_name,
        r.customer_name,
        centsForCsv(r.net_cents),
        centsForCsv(r.vat_cents),
        centsForCsv(r.gross_cents),
    ])

    const csv = [header, ...rows]
        .map((row) => row.map(csvValue).join(';'))
        .join('\r\n')

    // BOM, damit Excel Umlaute richtig anzeigt
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8' })
    const url = URL.createObjectURL(blob)

    const link = document.createElement('a')
    link.href = url
    link.download = selectedMonth.value === 'all'
        ? 'Rechnungen.csv'
        : `Rechnungen-${selectedMonth.value}.csv`
    link.click()

    URL.revokeObjectURL(url)
}
</script>

<template>
    <Head :title="isStudio ? 'Rechnungen des Studios' : 'Meine Rechnungen'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="page-title">
                {{ isStudio ? 'Rechnungen des Studios' : 'Meine Rechnungen' }}
            </h2>
        </template>

        <div class="page">
            <section class="card">

                <div class="card-header">
                    <div>
                        <h3>
                            {{ isStudio ? 'Alle Rechnungen' : 'Deine Rechnungen' }}
                        </h3>
                        <p>
                            {{
                                isStudio
                                    ? 'Rechnungen aus bezahlten Terminen – zur Ablage für Buchhaltung und Steuer.'
                                    : 'Hier findest du alle Rechnungen zu deinen bezahlten Terminen.'
                            }}
                        </p>
                    </div>

                    <!-- Filter und Export nur für das Studio -->
                    <div v-if="isStudio && receipts.length" class="toolbar">
                        <select v-model="selectedMonth" class="select" aria-label="Monat auswählen">
                            <option value="all">Alle Monate</option>
                            <option v-for="m in months" :key="m" :value="m">
                                {{ monthLabel(m) }}
                            </option>
                        </select>

                        <button type="button" class="secondary-button" @click="exportCsv">
                            CSV exportieren
                        </button>
                    </div>
                </div>

                <!-- Summen für den gewählten Zeitraum (Studio) -->
                <div v-if="isStudio && filteredReceipts.length" class="totals">
                    <div class="total-box">
                        <span>Netto</span>
                        <strong>{{ euro(totals.net) }}</strong>
                    </div>
                    <div class="total-box">
                        <span>Umsatzsteuer</span>
                        <strong>{{ euro(totals.vat) }}</strong>
                    </div>
                    <div class="total-box highlight">
                        <span>Brutto</span>
                        <strong>{{ euro(totals.gross) }}</strong>
                    </div>
                </div>

                <!-- Liste -->
                <div v-if="filteredReceipts.length" class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rechnungsnr.</th>
                                <th>Rechnungsdatum</th>
                                <th>Termin</th>
                                <th>Studio</th>
                                <th v-if="isStudio">Kundin</th>
                                <th class="right">Betrag</th>
                                <th class="right">PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in filteredReceipts" :key="r.id">
                                <td class="number">{{ r.number }}</td>
                                <td>{{ formatDate(r.issued_at) }}</td>
                                <td>{{ formatDate(r.service_date) }}</td>
                                <td>{{ r.studio_name }}</td>
                                <td v-if="isStudio">{{ r.customer_name }}</td>
                                <td class="right">{{ euro(r.gross_cents) }}</td>
                                <td class="right">
                                    <!-- Normaler Link, damit der Browser die PDF herunterlädt -->
                                    <a
                                        :href="`/rechnungen/${r.id}/pdf`"
                                        class="download-link"
                                    >
                                        Herunterladen
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Leerer Zustand -->
                <div v-else class="empty-state">
                    <div class="empty-icon">♡</div>
                    <strong>Noch keine Rechnungen</strong>
                    <span>
                        {{
                            isStudio
                                ? 'Sobald eine Kundin einen Termin bezahlt, erscheint die Rechnung hier.'
                                : 'Nach deiner ersten Zahlung findest du die Rechnung hier und in deinem Postfach.'
                        }}
                    </span>
                </div>

            </section>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.page-title {
    margin: 0;
    color: #4f3540;
    font-size: 1.25rem;
    font-weight: 600;
}

.page {
    max-width: 72rem;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.card {
    background-color: white;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow:
        0 4px 12px rgba(80, 35, 48, 0.06),
        0 8px 20px rgba(80, 35, 48, 0.04);
}

.card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.card-header h3 {
    margin: 0;
    color: #4f3540;
    font-size: 1.15rem;
    font-weight: 700;
}

.card-header p {
    margin: 0.35rem 0 0;
    color: #8a7a80;
    font-size: 0.8rem;
    line-height: 1.5;
}

.toolbar {
    display: flex;
    gap: 0.6rem;
    flex-shrink: 0;
}

.select {
    padding: 0.55rem 2rem 0.55rem 0.75rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 8px;
    background-color: #fdfbfc;
    color: #4f3540;
    font-size: 0.85rem;
}

.secondary-button {
    padding: 0.55rem 0.9rem;
    border: 1px solid #8f5364;
    border-radius: 8px;
    background-color: white;
    color: #8f5364;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}

.secondary-button:hover {
    background-color: #f5e7eb;
}

.totals {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.total-box {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.8rem 1rem;
    border: 1px solid rgba(143, 83, 100, 0.1);
    border-radius: 10px;
    background-color: #fdfbfc;
}

.total-box span {
    color: #8a7a80;
    font-size: 0.75rem;
}

.total-box strong {
    color: #4f3540;
    font-size: 1.05rem;
}

.total-box.highlight {
    background-color: #f5e7eb;
}

.table-wrap {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.table th {
    padding: 0.7rem 0.6rem;
    border-bottom: 1px solid rgba(143, 83, 100, 0.2);
    color: #8a7a80;
    font-size: 0.75rem;
    font-weight: 600;
    text-align: left;
    white-space: nowrap;
}

.table td {
    padding: 0.75rem 0.6rem;
    border-bottom: 1px solid rgba(143, 83, 100, 0.08);
    color: #4f3540;
    white-space: nowrap;
}

.table .right {
    text-align: right;
}

.number {
    font-weight: 600;
}

.download-link {
    color: #8f5364;
    font-weight: 600;
    text-decoration: none;
}

.download-link:hover {
    text-decoration: underline;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    min-height: 180px;
    text-align: center;
}

.empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    margin-bottom: 0.5rem;
    border-radius: 50%;
    background-color: #f5e7eb;
    color: #8f5364;
    font-size: 1.2rem;
}

.empty-state strong {
    color: #4f3540;
    font-size: 0.95rem;
}

.empty-state span {
    max-width: 320px;
    color: #8a7a80;
    font-size: 0.8rem;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
    }

    .toolbar {
        width: 100%;
        flex-direction: column;
    }

    .totals {
        grid-template-columns: 1fr;
    }
}
</style>