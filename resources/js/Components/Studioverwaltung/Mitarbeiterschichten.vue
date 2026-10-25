<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Daten aus dem Backend
|--------------------------------------------------------------------------
| employees: [{ id, name, shifts: { monday: { start, end }, … } }]
| Fehlender Tag = frei
*/

const employees = computed(() => page.props.employees ?? [])

const currentUser = computed(() => page.props.auth?.user ?? null)

// Bearbeiten dürfen Schichtleitung und Inhaberin
const canEdit = computed(() =>
    ['admin', 'owner'].includes(currentUser.value?.role)
)

const DAYS = [
    { key: 'monday',    label: 'Montag' },
    { key: 'tuesday',   label: 'Dienstag' },
    { key: 'wednesday', label: 'Mittwoch' },
    { key: 'thursday',  label: 'Donnerstag' },
    { key: 'friday',    label: 'Freitag' },
    { key: 'saturday',  label: 'Samstag' },
]


/*
|--------------------------------------------------------------------------
| Bearbeiten
|--------------------------------------------------------------------------
| draft[employeeId][day] = { off, start, end }
*/

const editing = ref(false)
const draft   = ref({})
const saving  = ref({})   // employeeId → true
const errors  = ref({})   // employeeId → { 'shifts.monday.end': '…' }
const saved   = ref({})   // employeeId → true (kurz nach dem Speichern)

function startEditing() {

    const result = {}

    for (const employee of employees.value) {

        result[employee.id] = {}

        for (const day of DAYS) {

            const shift = employee.shifts?.[day.key]

            result[employee.id][day.key] = {
                off:   !shift,
                start: shift?.start ?? '09:00',
                end:   shift?.end ?? '17:00',
            }
        }
    }

    draft.value  = result
    errors.value = {}
    editing.value = true

}

function stopEditing() {

    editing.value = false

}

// Ganze Woche auf die Zeiten von Montag setzen
function copyMondayToAll(employeeId) {

    const monday = draft.value[employeeId].monday

    for (const day of DAYS) {
        draft.value[employeeId][day.key] = { ...monday }
    }

}

function save(employee) {

    const shifts = {}

    for (const day of DAYS) {

        const entry = draft.value[employee.id][day.key]

        shifts[day.key] = entry.off
            ? null
            : { start: entry.start, end: entry.end }
    }

    saving.value[employee.id] = true
    errors.value[employee.id] = {}

    router.put(`/employees/${employee.id}/shifts`, { shifts }, {
        preserveScroll: true,
        preserveState:  true,
        only: ['employees', 'flash'],

        onSuccess: () => {
            saved.value[employee.id] = true

            setTimeout(() => {
                saved.value[employee.id] = false
            }, 1800)
        },

        onError: (formErrors) => {
            errors.value[employee.id] = formErrors
        },

        onFinish: () => {
            saving.value[employee.id] = false
        },
    })

}

function fieldError(employeeId, dayKey) {

    const list = errors.value[employeeId] ?? {}

    return list[`shifts.${dayKey}.end`] || list[`shifts.${dayKey}.start`] || ''

}


/*
|--------------------------------------------------------------------------
| Anzeige
|--------------------------------------------------------------------------
*/

function shiftLabel(employee, dayKey) {

    const shift = employee.shifts?.[dayKey]

    return shift ? `${shift.start} – ${shift.end}` : 'frei'

}

function weeklyHours(employee) {

    let minutes = 0

    for (const day of DAYS) {

        const shift = employee.shifts?.[day.key]

        if (!shift) continue

        const [sh, sm] = shift.start.split(':').map(Number)
        const [eh, em] = shift.end.split(':').map(Number)

        minutes += (eh * 60 + em) - (sh * 60 + sm)
    }

    const hours = minutes / 60

    return hours.toLocaleString('de-DE', { maximumFractionDigits: 1 }) + ' Std.'

}

function getInitials(name) {

    if (!name) return '?'

    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('')

}
</script>

<template>
    <section class="dashboard-section">

        <div class="section-header">

            <div>
                <span class="section-eyebrow">
                    ARBEITSZEITEN
                </span>

                <h2>
                    Mitarbeiterschichten
                </h2>

                <p>
                    Übersicht über die Arbeitszeiten des Teams
                </p>
            </div>

            <template v-if="canEdit && employees.length">

                <button
                    v-if="!editing"
                    class="secondary-button"
                    type="button"
                    @click="startEditing"
                >
                    Schichten verwalten
                </button>

                <button
                    v-else
                    class="secondary-button"
                    type="button"
                    @click="stopEditing"
                >
                    Fertig
                </button>

            </template>

        </div>


        <!-- ========================================= -->
        <!-- ANSICHT                                   -->
        <!-- ========================================= -->

        <div
            v-if="employees.length && !editing"
            class="shift-table-wrapper"
        >

            <table class="shift-table">

                <thead>
                    <tr>
                        <th>Mitarbeiter</th>
                        <th v-for="day in DAYS" :key="day.key">{{ day.label }}</th>
                        <th class="right">Woche</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="employee in employees"
                        :key="`shift-${employee.id}`"
                    >

                        <td class="employee-name">
                            <div class="table-employee">
                                <div class="table-avatar">
                                    {{ getInitials(employee.name) }}
                                </div>
                                <span>{{ employee.name }}</span>
                            </div>
                        </td>

                        <td
                            v-for="day in DAYS"
                            :key="day.key"
                            :class="{ off: !employee.shifts?.[day.key] }"
                        >
                            {{ shiftLabel(employee, day.key) }}
                        </td>

                        <td class="right hours">
                            {{ weeklyHours(employee) }}
                        </td>

                    </tr>
                </tbody>

            </table>

        </div>


        <!-- ========================================= -->
        <!-- BEARBEITEN                                -->
        <!-- ========================================= -->

        <div
            v-if="employees.length && editing"
            class="edit-list"
        >

            <article
                v-for="employee in employees"
                :key="`edit-${employee.id}`"
                class="edit-card"
            >

                <header class="edit-card-header">

                    <div class="table-employee">
                        <div class="table-avatar">
                            {{ getInitials(employee.name) }}
                        </div>
                        <strong>{{ employee.name }}</strong>
                    </div>

                    <button
                        type="button"
                        class="link-button"
                        @click="copyMondayToAll(employee.id)"
                    >
                        Montag für alle Tage übernehmen
                    </button>

                </header>

                <div class="day-grid">

                    <div
                        v-for="day in DAYS"
                        :key="day.key"
                        class="day-cell"
                        :class="{ off: draft[employee.id][day.key].off }"
                    >

                        <div class="day-head">
                            <span>{{ day.label }}</span>

                            <label class="off-toggle">
                                <input
                                    v-model="draft[employee.id][day.key].off"
                                    type="checkbox"
                                >
                                frei
                            </label>
                        </div>

                        <div
                            v-if="!draft[employee.id][day.key].off"
                            class="time-row"
                        >
                            <input
                                v-model="draft[employee.id][day.key].start"
                                type="time"
                                step="900"
                                :aria-label="`${day.label} Beginn`"
                            >
                            <span>–</span>
                            <input
                                v-model="draft[employee.id][day.key].end"
                                type="time"
                                step="900"
                                :aria-label="`${day.label} Ende`"
                            >
                        </div>

                        <small
                            v-if="fieldError(employee.id, day.key)"
                            class="field-error"
                        >
                            {{ fieldError(employee.id, day.key) }}
                        </small>

                    </div>

                </div>

                <footer class="edit-card-footer">

                    <span
                        v-if="saved[employee.id]"
                        class="saved"
                    >
                        ✓ Gespeichert
                    </span>

                    <button
                        type="button"
                        class="primary-button"
                        :disabled="saving[employee.id]"
                        @click="save(employee)"
                    >
                        {{ saving[employee.id] ? 'Speichert …' : 'Speichern' }}
                    </button>

                </footer>

            </article>

        </div>


        <!-- ========================================= -->
        <!-- LEER                                      -->
        <!-- ========================================= -->

        <div
            v-if="!employees.length"
            class="empty-state"
        >
            <div class="empty-icon">♧</div>

            <strong>Keine Mitarbeiter vorhanden</strong>

            <span>
                Es sind aktuell keine Mitarbeiter
                für die Schichtübersicht eingetragen.
            </span>
        </div>

    </section>
</template>

<style scoped>
.dashboard-section {
    background-color: white;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow:
        0 4px 12px rgba(80, 35, 48, 0.06),
        0 8px 20px rgba(80, 35, 48, 0.04);
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.section-eyebrow {
    display: block;
    margin-bottom: 0.25rem;
    color: #8f5364;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
}

.section-header h2 {
    margin: 0;
    color: #4f3540;
    font-size: 1.2rem;
    font-weight: 700;
}

.section-header p {
    margin: 0.35rem 0 0;
    color: #8a7a80;
    font-size: 0.8rem;
}

.secondary-button {
    padding: 0.6rem 1rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 8px;
    background-color: #fdf9fa;
    color: #8f5364;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.secondary-button:hover {
    background-color: #f5e7eb;
    border-color: rgba(143, 83, 100, 0.3);
}

.primary-button {
    padding: 0.55rem 1.1rem;
    border: none;
    border-radius: 8px;
    background-color: #8f5364;
    color: white;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
}

.primary-button:hover {
    background-color: #754354;
}

.primary-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}


/* Ansicht */

.shift-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.shift-table {
    width: 100%;
    min-width: 780px;
    border-collapse: collapse;
}

.shift-table th {
    padding: 0.7rem;
    color: #6f5961;
    font-size: 0.7rem;
    font-weight: 500;
    text-align: left;
    opacity: 0.7;
    border-bottom: 1px solid rgba(109, 59, 71, 0.08);
}

.shift-table td {
    padding: 0.8rem 0.7rem;
    color: #4f3540;
    font-size: 0.75rem;
    border-bottom: 1px solid rgba(109, 59, 71, 0.06);
    white-space: nowrap;
}

.shift-table td.off {
    color: #b8a8ae;
    font-style: italic;
}

.shift-table .right {
    text-align: right;
}

.shift-table .hours {
    font-weight: 600;
    color: #8f5364;
}

.shift-table tr:last-child td {
    border-bottom: none;
}

.shift-table tbody tr:hover {
    background-color: #fdf9fa;
}

.table-employee {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    min-width: 150px;
}

.table-avatar {
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f5e7eb;
    color: #8f5364;
    font-size: 0.7rem;
    font-weight: 700;
}

.employee-name span {
    color: #4f3540;
    font-weight: 600;
}


/* Bearbeiten */

.edit-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.edit-card {
    padding: 1rem;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 10px;
    background: #fdfbfc;
}

.edit-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.8rem;
    color: #4f3540;
}

.link-button {
    padding: 0;
    border: none;
    background: none;
    color: #8f5364;
    font-size: 0.75rem;
    text-decoration: underline;
    cursor: pointer;
}

.day-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(9.5rem, 1fr));
    gap: 0.6rem;
}

.day-cell {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    padding: 0.6rem;
    border: 1px solid rgba(143, 83, 100, 0.12);
    border-radius: 8px;
    background: #ffffff;
}

.day-cell.off {
    background: #f8f4f5;
}

.day-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #4f3540;
    font-size: 0.78rem;
    font-weight: 600;
}

.off-toggle {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #8a7a80;
    font-size: 0.7rem;
    font-weight: 400;
    cursor: pointer;
}

.time-row {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    color: #8a7a80;
}

.time-row input {
    width: 100%;
    min-width: 0;
    padding: 0.3rem 0.35rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 6px;
    color: #4f3540;
    font: inherit;
    font-size: 0.75rem;
}

.time-row input:focus {
    outline: none;
    border-color: #8f5364;
}

.field-error {
    color: #a04646;
    font-size: 0.68rem;
    line-height: 1.3;
}

.edit-card-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.8rem;
    margin-top: 0.8rem;
}

.saved {
    color: #477052;
    font-size: 0.78rem;
}


/* Leer */

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
    max-width: 280px;
    color: #8a7a80;
    font-size: 0.8rem;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .dashboard-section {
        padding: 1.25rem;
    }

    .section-header,
    .edit-card-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .secondary-button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .dashboard-section {
        padding: 1rem;
    }
}
</style>
