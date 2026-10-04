<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Daten aus dem Backend
|--------------------------------------------------------------------------
*/

const employees = computed(() => {
    return page.props.employees ?? []
})

/*
|--------------------------------------------------------------------------
| Hilfsfunktionen
|--------------------------------------------------------------------------
*/

function getInitials(name) {
    if (!name) {
        return '?'
    }

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

            <button
                class="secondary-button"
                type="button"
            >
                Schichten verwalten
            </button>

        </div>

        <div class="shift-table-wrapper">

            <table class="shift-table">

                <thead>
                    <tr>
                        <th>
                            Mitarbeiter
                        </th>

                        <th>
                            Montag
                        </th>

                        <th>
                            Dienstag
                        </th>

                        <th>
                            Mittwoch
                        </th>

                        <th>
                            Donnerstag
                        </th>

                        <th>
                            Freitag
                        </th>
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

                                <span>
                                    {{ employee.name }}
                                </span>

                            </div>

                        </td>

                        <td>
                            {{
                                employee.shifts?.monday ||
                                '09:00 – 17:00'
                            }}
                        </td>

                        <td>
                            {{
                                employee.shifts?.tuesday ||
                                '09:00 – 17:00'
                            }}
                        </td>

                        <td>
                            {{
                                employee.shifts?.wednesday ||
                                '09:00 – 17:00'
                            }}
                        </td>

                        <td>
                            {{
                                employee.shifts?.thursday ||
                                '09:00 – 17:00'
                            }}
                        </td>

                        <td>
                            {{
                                employee.shifts?.friday ||
                                '09:00 – 17:00'
                            }}
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        <div
            v-if="!employees.length"
            class="empty-state"
        >
            <div class="empty-icon">
                ♧
            </div>

            <strong>
                Keine Mitarbeiter vorhanden
            </strong>

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

.shift-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.shift-table {
    width: 100%;
    min-width: 700px;
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

    .section-header {
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