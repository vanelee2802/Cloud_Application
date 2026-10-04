<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Daten aus dem Backend
|--------------------------------------------------------------------------
*/

const employees = computed(() => {
    return page.props.employees ?? []
})

const currentUser = computed(() => {
    return page.props.auth?.user ?? null
})

const isAdmin = computed(() => {
    return currentUser.value?.role === 'admin'
})

/*
|--------------------------------------------------------------------------
| Mitarbeiter hinzufügen
|--------------------------------------------------------------------------
*/

const showEmployeeModal = ref(false)

const newEmployee = ref({
    name: '',
    email: '',
})

const employeeError = ref('')
const employeeLoading = ref(false)

function openEmployeeModal() {
    employeeError.value = ''

    newEmployee.value = {
        name: '',
        email: '',
    }

    showEmployeeModal.value = true
}

function closeEmployeeModal() {
    if (employeeLoading.value) {
        return
    }

    showEmployeeModal.value = false
}

function addEmployee() {
    employeeError.value = ''

    if (!newEmployee.value.name.trim()) {
        employeeError.value = 'Bitte gib einen Namen ein.'
        return
    }

    if (!newEmployee.value.email.trim()) {
        employeeError.value = 'Bitte gib eine E-Mail-Adresse ein.'
        return
    }

    employeeLoading.value = true

    router.post(
        '/employees',
        {
            name: newEmployee.value.name,
            email: newEmployee.value.email,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showEmployeeModal.value = false

                newEmployee.value = {
                    name: '',
                    email: '',
                }
            },

            onError: (errors) => {
                employeeError.value =
                    errors.email ||
                    errors.name ||
                    'Der Mitarbeiter konnte nicht hinzugefügt werden.'
            },

            onFinish: () => {
                employeeLoading.value = false
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Mitarbeiter entfernen
|--------------------------------------------------------------------------
*/

function removeEmployee(employee) {
    if (!employee?.id) {
        return
    }

    const confirmed = window.confirm(
        `Möchtest du ${employee.name} wirklich aus dem Studio entfernen?`
    )

    if (!confirmed) {
        return
    }

    router.delete(`/employees/${employee.id}`, {
        preserveScroll: true,
    })
}

/*
|--------------------------------------------------------------------------
| Mitarbeiter-Hilfsfunktionen
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

function getEmployeeStatus(employee) {
    return employee?.status ?? 'available'
}

function getStatusLabel(status) {
    const labels = {
        available: 'Verfügbar',
        busy: 'Beschäftigt',
        unavailable: 'Nicht verfügbar',
        vacation: 'Urlaub',
    }

    return labels[status] ?? 'Verfügbar'
}

function getStatusClass(status) {
    return `status-${status}`
}
</script>

<template>
    <section class="dashboard-section employee-management">

        <div class="section-header">

            <div>
                <h2>Mitarbeiter verwalten</h2>

                <p>
                    Mitarbeiter deines Studios hinzufügen oder
                    entfernen.
                </p>
            </div>

            <button
                v-if="isAdmin"
                class="primary-button"
                type="button"
                @click="openEmployeeModal"
            >
                <span class="button-plus">+</span>

                Mitarbeiter hinzufügen
            </button>

        </div>

        <div
            v-if="employees.length"
            class="employee-grid"
        >

            <article
                v-for="employee in employees"
                :key="employee.id"
                class="employee-card"
            >

                <div class="employee-card-top">

                    <div class="employee-avatar">
                        {{ getInitials(employee.name) }}
                    </div>

                    <span
                        class="employee-status"
                        :class="
                            getStatusClass(
                                getEmployeeStatus(employee)
                            )
                        "
                    >
                        <span class="status-dot"></span>

                        {{
                            getStatusLabel(
                                getEmployeeStatus(employee)
                            )
                        }}
                    </span>

                </div>

                <div class="employee-card-content">

                    <h3>
                        {{ employee.name }}
                    </h3>

                    <p class="employee-role">
                        Nageldesigner/in
                    </p>

                    <p class="employee-email">
                        {{ employee.email }}
                    </p>

                </div>

                <div
                    v-if="isAdmin"
                    class="employee-card-actions"
                >
                    <button
                        class="remove-button"
                        type="button"
                        @click="removeEmployee(employee)"
                    >
                        Entfernen
                    </button>
                </div>

            </article>

        </div>

        <div
            v-else
            class="empty-state"
        >
            <div class="empty-icon">
                ♡
            </div>

            <strong>
                Keine Mitarbeiter vorhanden
            </strong>

            <span>
                Aktuell sind keine Mitarbeiter für dieses
                Studio eingetragen.
            </span>
        </div>

    </section>

    <!-- ========================================================= -->
    <!-- MITARBEITER HINZUFÜGEN MODAL -->
    <!-- ========================================================= -->

    <div
        v-if="showEmployeeModal"
        class="modal-overlay"
        @click.self="closeEmployeeModal"
    >

        <div class="employee-modal">

            <div class="modal-header">

                <div>
                    <h2>
                        Mitarbeiter hinzufügen
                    </h2>

                    <p>
                        Füge einen neuen Mitarbeiter zu deinem
                        Studio hinzu.
                    </p>
                </div>

                <button
                    class="modal-close"
                    type="button"
                    :disabled="employeeLoading"
                    @click="closeEmployeeModal"
                >
                    ×
                </button>

            </div>

            <form
                class="employee-form"
                @submit.prevent="addEmployee"
            >

                <div class="form-group">

                    <label for="employee-name">
                        Name
                    </label>

                    <input
                        id="employee-name"
                        v-model="newEmployee.name"
                        type="text"
                        placeholder="z. B. Anna Müller"
                        :disabled="employeeLoading"
                    >

                </div>

                <div class="form-group">

                    <label for="employee-email">
                        E-Mail-Adresse
                    </label>

                    <input
                        id="employee-email"
                        v-model="newEmployee.email"
                        type="email"
                        placeholder="z. B. anna@example.de"
                        :disabled="employeeLoading"
                    >

                </div>

                <div
                    v-if="employeeError"
                    class="employee-error"
                >
                    {{ employeeError }}
                </div>

                <div class="modal-actions">

                    <button
                        class="cancel-button"
                        type="button"
                        :disabled="employeeLoading"
                        @click="closeEmployeeModal"
                    >
                        Abbrechen
                    </button>

                    <button
                        class="primary-button"
                        type="submit"
                        :disabled="employeeLoading"
                    >
                        {{
                            employeeLoading
                                ? 'Wird hinzugefügt...'
                                : 'Mitarbeiter hinzufügen'
                        }}
                    </button>

                </div>

            </form>

        </div>

    </div>
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
    margin-bottom: 1.5rem;
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
    line-height: 1.5;
}

.primary-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.65rem 1rem;
    border: 1px solid #8f5364;
    border-radius: 8px;
    background-color: #8f5364;
    color: white;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        opacity 0.2s ease;
}

.primary-button:hover {
    background-color: #754354;
    border-color: #754354;
}

.primary-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.button-plus {
    font-size: 1.1rem;
    line-height: 1;
}

.employee-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}

.employee-card {
    display: flex;
    flex-direction: column;
    padding: 1rem;
    border: 1px solid rgba(143, 83, 100, 0.1);
    border-radius: 10px;
    background-color: #fdfbfc;
}

.employee-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.employee-avatar {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f5e7eb;
    color: #8f5364;
    font-size: 0.95rem;
    font-weight: 700;
}

.employee-status {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.6rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: currentColor;
}

.status-available {
    background-color: #e8f4ec;
    color: #477052;
}

.status-busy {
    background-color: #f8eee1;
    color: #8a6742;
}

.status-unavailable {
    background-color: #f5e7e7;
    color: #8a5050;
}

.status-vacation {
    background-color: #eee9f5;
    color: #6d5a82;
}

.employee-card-content {
    margin-top: 1rem;
}

.employee-card-content h3 {
    margin: 0;
    color: #4f3540;
    font-size: 1rem;
    font-weight: 700;
}

.employee-role {
    margin: 0.25rem 0 0;
    color: #8f5364;
    font-size: 0.8rem;
    font-weight: 600;
}

.employee-email {
    margin: 0.35rem 0 0;
    color: #8a7a80;
    font-size: 0.8rem;
    word-break: break-word;
}

.employee-card-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(143, 83, 100, 0.08);
}

.remove-button {
    padding: 0.5rem 0.75rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 7px;
    background-color: white;
    color: #8f5364;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.remove-button:hover {
    background-color: #f5e7eb;
    border-color: rgba(143, 83, 100, 0.3);
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

/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background-color: rgba(40, 25, 30, 0.45);
}

.employee-modal {
    width: 100%;
    max-width: 480px;
    background-color: white;
    border-radius: 14px;
    box-shadow: 0 20px 50px rgba(40, 25, 30, 0.2);
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem;
    border-bottom: 1px solid rgba(143, 83, 100, 0.1);
}

.modal-header h2 {
    margin: 0;
    color: #4f3540;
    font-size: 1.15rem;
    font-weight: 700;
}

.modal-header p {
    margin: 0.35rem 0 0;
    color: #8a7a80;
    font-size: 0.8rem;
    line-height: 1.5;
}

.modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    border: none;
    border-radius: 7px;
    background-color: #fdf9fa;
    color: #8f5364;
    font-size: 1.4rem;
    line-height: 1;
    cursor: pointer;
}

.modal-close:hover {
    background-color: #f5e7eb;
}

.modal-close:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.employee-form {
    padding: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 1rem;
}

.form-group label {
    color: #4f3540;
    font-size: 0.8rem;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.7rem 0.8rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 8px;
    outline: none;
    background-color: #fdfbfc;
    color: #4f3540;
    font-family: inherit;
    font-size: 0.85rem;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.form-group input:focus {
    border-color: #8f5364;
    box-shadow: 0 0 0 3px rgba(143, 83, 100, 0.08);
}

.form-group input::placeholder {
    color: #b0a2a7;
}

.form-group input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.employee-error {
    margin-bottom: 1rem;
    padding: 0.7rem 0.8rem;
    border: 1px solid rgba(160, 70, 70, 0.2);
    border-radius: 8px;
    background-color: #fdf2f2;
    color: #8a5050;
    font-size: 0.8rem;
    line-height: 1.4;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    margin-top: 1.5rem;
}

.cancel-button {
    padding: 0.65rem 1rem;
    border: 1px solid rgba(143, 83, 100, 0.2);
    border-radius: 8px;
    background-color: white;
    color: #8f5364;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}

.cancel-button:hover {
    background-color: #f5e7eb;
}

.cancel-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 900px) {
    .employee-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-section {
        padding: 1.25rem;
    }

    .section-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .primary-button {
        width: 100%;
    }
}

@media (max-width: 600px) {
    .employee-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-section {
        padding: 1rem;
    }

    .employee-card-top {
        align-items: flex-start;
    }

    .modal-header {
        padding: 1.25rem;
    }

    .employee-form {
        padding: 1.25rem;
    }

    .modal-actions {
        flex-direction: column-reverse;
    }

    .modal-actions button {
        width: 100%;
    }
}
</style>