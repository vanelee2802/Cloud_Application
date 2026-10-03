<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue'
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Daten aus dem Backend
|--------------------------------------------------------------------------
|
| Erwartet werden später:
|
| employees
| customerRequests
| appointments
| vacationRequests
|
| Falls diese Props noch nicht vorhanden sind, werden leere Arrays benutzt.
|
*/

const employees = computed(() => page.props.employees ?? [])
const customerRequests = computed(() => page.props.customerRequests ?? [])
const appointments = computed(() => page.props.appointments ?? [])
const vacationRequests = computed(() => page.props.vacationRequests ?? [])

const currentUser = computed(() => page.props.auth?.user ?? null)

const isAdmin = computed(() => currentUser.value?.role === 'admin')

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

function formatDate(date) {
    if (!date) {
        return ''
    }

    const value = new Date(date)

    if (Number.isNaN(value.getTime())) {
        return date
    }

    return value.toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

function formatTime(time) {
    if (!time) {
        return ''
    }

    return String(time).substring(0, 5)
}

function formatAppointmentDate(appointment) {
    if (appointment?.date) {
        return formatDate(appointment.date)
    }

    return ''
}

function formatAppointmentTime(appointment) {
    if (appointment?.time) {
        return formatTime(appointment.time)
    }

    return ''
}

/*
|--------------------------------------------------------------------------
| Statistiken
|--------------------------------------------------------------------------
*/

const employeeCount = computed(() => employees.value.length)

const requestCount = computed(() => customerRequests.value.length)

const appointmentCount = computed(() => appointments.value.length)

const vacationCount = computed(() => vacationRequests.value.length)
</script>

<template>
    <pagesLayout>
        <div class="employee-page">

            <!-- ========================================================= -->
            <!-- HEADER -->
            <!-- ========================================================= -->

            <header class="page-header">
                <div class="header-content">
                    <span class="eyebrow">
                        Nagelsutdio Name · STUDIO
                    </span>

                    <h1>Mitarbeiterbereich</h1>

                  
                </div>

               
            </header>


            <!-- ========================================================= -->
            <!-- STATISTIK -->
            <!-- ========================================================= -->

            <section class="stats-grid">

                <article class="stat-card">
                    <div class="stat-icon">
                        ♡
                    </div>

                    <div class="stat-content">
                        <span class="stat-title">
                            Kundenanfragen
                        </span>

                        <strong class="stat-number">
                            {{ requestCount }}
                        </strong>

                        <span class="stat-description">
                            offene Anfragen
                        </span>
                    </div>
                </article>


                <article class="stat-card">
                    <div class="stat-icon">
                        ◷
                    </div>

                    <div class="stat-content">
                        <span class="stat-title">
                            Kommende Termine
                        </span>

                        <strong class="stat-number">
                            {{ appointmentCount }}
                        </strong>

                        <span class="stat-description">
                            anstehende Termine
                        </span>
                    </div>
                </article>


                <article class="stat-card">
                    <div class="stat-icon">
                        ✦
                    </div>

                    <div class="stat-content">
                        <span class="stat-title">
                            Urlaubsanträge
                        </span>

                        <strong class="stat-number">
                            {{ vacationCount }}
                        </strong>

                        <span class="stat-description">
                            warten auf Bearbeitung
                        </span>
                    </div>
                </article>


                <article class="stat-card">
                    <div class="stat-icon">
                        ♧
                    </div>

                    <div class="stat-content">
                        <span class="stat-title">
                            Mitarbeiter
                        </span>

                        <strong class="stat-number">
                            {{ employeeCount }}
                        </strong>

                        <span class="stat-description">
                            im Studio
                        </span>
                    </div>
                </article>

            </section>


            <!-- ========================================================= -->
            <!-- ANFRAGEN + TERMINE -->
            <!-- ========================================================= -->

            <div class="two-column-layout">

                <!-- Kundenanfragen -->

                <section class="dashboard-section">

                    <div class="section-header">

                        <div>
                           

                            <h2>Kundenanfragen</h2>

                            
                        </div>

                        <button class="secondary-button">
                            Alle anzeigen
                        </button>

                    </div>


                    <div
                        v-if="customerRequests.length"
                        class="request-list"
                    >

                        <div
                            v-for="request in customerRequests"
                            :key="request.id"
                            class="request-card"
                        >

                            <div class="request-avatar">
                                {{
                                    getInitials(
                                        request.customer?.name ||
                                        request.user?.name ||
                                        request.name
                                    )
                                }}
                            </div>

                            <div class="request-info">

                                <h3>
                                    {{
                                        request.customer?.name ||
                                        request.user?.name ||
                                        request.name ||
                                        'Kunde'
                                    }}
                                </h3>

                                <p>
                                    {{
                                        request.service?.name ||
                                        request.service_name ||
                                        'Termin'
                                    }}
                                </p>

                                <span>
                                    {{
                                        formatAppointmentDate(request)
                                    }}

                                    <template
                                        v-if="formatAppointmentTime(request)"
                                    >
                                        ·
                                        {{ formatAppointmentTime(request) }}
                                        Uhr
                                    </template>
                                </span>

                            </div>


                            <div class="request-actions">

                                <button
                                    class="accept-button"
                                    type="button"
                                >
                                    Annehmen
                                </button>

                                <button
                                    class="decline-button"
                                    type="button"
                                >
                                    Ablehnen
                                </button>

                            </div>

                        </div>

                    </div>


                    <div
                        v-else
                        class="empty-state"
                    >
                        <div class="empty-icon">
                            ✓
                        </div>

                        <strong>
                            Keine offenen Anfragen
                        </strong>

                        <span>
                            Aktuell warten keine Kundenanfragen auf
                            Bearbeitung.
                        </span>
                    </div>

                </section>


                <!-- Kommende Termine -->

                <section class="dashboard-section">

                    <div class="section-header">

                        <div>
                            

                            <h2>Kommende Termine</h2>

                           
                        </div>

                        <button class="secondary-button">
                            Kalender
                        </button>

                    </div>


                    <div
                        v-if="appointments.length"
                        class="appointment-list"
                    >

                        <div
                            v-for="appointment in appointments"
                            :key="appointment.id"
                            class="appointment-card"
                        >

                            <div class="appointment-time">

                                <strong>
                                    {{ formatAppointmentTime(appointment) }}
                                </strong>

                                <span>
                                    {{
                                        formatAppointmentDate(appointment)
                                    }}
                                </span>

                            </div>


                            <div class="appointment-info">

                                <h3>
                                    {{
                                        appointment.customer?.name ||
                                        appointment.user?.name ||
                                        appointment.customer_name ||
                                        'Kunde'
                                    }}
                                </h3>

                                <p>
                                    {{
                                        appointment.service?.name ||
                                        appointment.service_name ||
                                        'Termin'
                                    }}
                                </p>

                            </div>


                            <span
                                class="status-badge"
                                :class="
                                    appointment.status === 'confirmed'
                                        ? 'confirmed'
                                        : 'pending'
                                "
                            >
                                {{
                                    appointment.status === 'confirmed'
                                        ? 'Bestätigt'
                                        : 'Ausstehend'
                                }}
                            </span>

                        </div>

                    </div>


                    <div
                        v-else
                        class="empty-state"
                    >
                        <div class="empty-icon">
                            ◷
                        </div>

                        <strong>
                            Keine kommenden Termine
                        </strong>

                        <span>
                            Für das Studio sind aktuell keine Termine
                            eingetragen.
                        </span>
                    </div>

                </section>

            </div>


            <!-- ========================================================= -->
            <!-- MITARBEITER VERWALTEN -->
            <!-- ========================================================= -->

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
                                {{
                                    getInitials(employee.name)
                                }}
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


                        <div class="employee-card-footer">

                            <span class="employee-label">
                                Mitarbeiter
                            </span>

                            <button
                                v-if="
                                    isAdmin &&
                                    employee.id !== currentUser?.id
                                "
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
                    class="empty-state employee-empty"
                >

                    <div class="empty-icon">
                        ♧
                    </div>

                    <strong>
                        Noch keine Mitarbeiter
                    </strong>

                    <span>
                        Füge den ersten Mitarbeiter für dein Studio hinzu.
                    </span>

                  

                </div>

            </section>


            <!-- ========================================================= -->
            <!-- SCHICHTEN -->
            <!-- ========================================================= -->

            <section class="dashboard-section">

                <div class="section-header">

                    <div>
                       
                        <h2>Mitarbeiterschichten</h2>

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
                                <th>Mitarbeiter</th>
                                <th>Montag</th>
                                <th>Dienstag</th>
                                <th>Mittwoch</th>
                                <th>Donnerstag</th>
                                <th>Freitag</th>
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
                                            {{
                                                getInitials(employee.name)
                                            }}
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

            </section>


            <!-- ========================================================= -->
            <!-- VERFÜGBARKEIT -->
            <!-- ========================================================= -->

            <section class="dashboard-section">

                <div class="section-header">

                    <div>
                        
                    

                        <h2>Aktuelle Verfügbarkeit</h2>

                        <p>
                            Wer ist aktuell im Studio verfügbar?
                        </p>
                    </div>

                </div>


                <div
                    v-if="employees.length"
                    class="availability-grid"
                >

                    <div
                        v-for="employee in employees"
                        :key="`availability-${employee.id}`"
                        class="availability-card"
                    >

                        <div class="availability-person">

                            <div class="avatar">
                                {{
                                    getInitials(employee.name)
                                }}
                            </div>

                            <div>

                                <h3>
                                    {{ employee.name }}
                                </h3>

                                <p>
                                    Nageldesigner/in
                                </p>

                            </div>

                        </div>


                        <span
                            class="availability-status"
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

                </div>


                <div
                    v-else
                    class="empty-state"
                >
                    <div class="empty-icon">
                        ◉
                    </div>

                    <strong>
                        Keine Mitarbeiter vorhanden
                    </strong>

                    <span>
                        Sobald Mitarbeiter hinzugefügt wurden, erscheint
                        hier ihre aktuelle Verfügbarkeit.
                    </span>
                </div>

            </section>


            <!-- ========================================================= -->
            <!-- URLAUBSANTRÄGE -->
            <!-- ========================================================= -->

            <section class="dashboard-section">

                <div class="section-header">

                    <div>
                       

                        <h2>Urlaubsanträge</h2>

                        
                    </div>

                    <button
                        class="secondary-button"
                        type="button"
                    >
                        Alle Anträge
                    </button>

                </div>


                <div
                    v-if="vacationRequests.length"
                    class="vacation-list"
                >

                    <div
                        v-for="vacation in vacationRequests"
                        :key="vacation.id"
                        class="vacation-card"
                    >

                        <div class="vacation-info">

                            <h3>
                                {{
                                    vacation.employee?.name ||
                                    vacation.user?.name ||
                                    vacation.name ||
                                    'Mitarbeiter'
                                }}
                            </h3>

                            <p>
                                {{ formatDate(vacation.start_date) }}
                                –
                                {{ formatDate(vacation.end_date) }}
                            </p>

                            <span>
                                {{
                                    vacation.days ||
                                    'Urlaubstage'
                                }}
                            </span>

                        </div>


                        <span class="status-badge pending">
                            Ausstehend
                        </span>


                        <div class="vacation-actions">

                            <button
                                class="accept-button"
                                type="button"
                            >
                                Genehmigen
                            </button>

                            <button
                                class="decline-button"
                                type="button"
                            >
                                Ablehnen
                            </button>

                        </div>

                    </div>

                </div>


                <div
                    v-else
                    class="empty-state"
                >
                    <div class="empty-icon">
                        ✓
                    </div>

                    <strong>
                        Keine offenen Urlaubsanträge
                    </strong>

                    <span>
                        Aktuell liegen keine Anträge zur Bearbeitung vor.
                    </span>
                </div>

            </section>


            <!-- ========================================================= -->
            <!-- WEITERE BEREICHE -->
            <!-- ========================================================= -->

            <section class="dashboard-section">

                <div class="section-header">

                    <div>
                      

                        <h2>Weitere Bereiche</h2>

                       
                    </div>

                </div>


                <div class="quick-actions">

                    <button
                        class="quick-action"
                        type="button"
                    >
                        <span class="quick-icon">
                            ◷
                        </span>

                        <strong>
                            Mein Kalender
                        </strong>

                        <span>
                            Termine und Schichten
                        </span>
                    </button>


                    <button
                        class="quick-action"
                        type="button"
                    >
                        <span class="quick-icon">
                            ♡
                        </span>

                        <strong>
                            Kunden
                        </strong>

                        <span>
                            Kundenübersicht
                        </span>
                    </button>


                    <button
                        class="quick-action"
                        type="button"
                    >
                        <span class="quick-icon">
                            ✓
                        </span>

                        <strong>
                            Meine Termine
                        </strong>

                        <span>
                            Persönliche Termine
                        </span>
                    </button>


                    <button
                        class="quick-action"
                        type="button"
                    >
                        <span class="quick-icon">
                            ✉
                        </span>

                        <strong>
                            Nachrichten
                        </strong>

                        <span>
                            Benachrichtigungen
                        </span>
                    </button>

                </div>

            </section>

        </div>


        <!-- ============================================================= -->
        <!-- MITARBEITER HINZUFÜGEN MODAL -->
        <!-- ============================================================= -->

        <div
            v-if="showEmployeeModal"
            class="modal-overlay"
            @click.self="closeEmployeeModal"
        >

            <div class="employee-modal">

                <button
                    class="modal-close"
                    type="button"
                    @click="closeEmployeeModal"
                >
                    ×
                </button>


                <div class="modal-header">

                   
                    <h2>
                        Mitarbeiter hinzufügen
                    </h2>

                  

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
                            placeholder="z. B. Lisa Schmidt"
                            autocomplete="name"
                        >

                    </div>


                    <div class="form-group">

                        <label for="employee-email">
                            Google-E-Mail-Adresse
                        </label>

                        <input
                            id="employee-email"
                            v-model="newEmployee.email"
                            type="email"
                            placeholder="z. B. lisa@gmail.com"
                            autocomplete="email"
                        >

                        <small>
                            Der Mitarbeiter kann sich anschließend mit
                            diesem Google-Konto anmelden.
                        </small>

                    </div>


                    <div
                        v-if="employeeError"
                        class="form-error"
                    >
                        {{ employeeError }}
                    </div>


                    <div class="modal-actions">

                        <button
                            class="secondary-button"
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
                                    ? 'Wird hinzugefügt …'
                                    : 'Mitarbeiter hinzufügen'
                            }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </pagesLayout>
</template>


<style scoped>

/* =========================================================
   EMPLOYEE DASHBOARD
   ========================================================= */

.employee-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    box-sizing: border-box;
}


/* =========================================================
   HEADER
   ========================================================= */

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0;

    color: var(--text-color);
    font-size: 2rem;
    font-weight: 700;
}

.page-header p {
    margin: 0.4rem 0 0;

    color: var(--text-color);
    opacity: 0.6;
}




/* =========================================================
   STATISTIK
   ========================================================= */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;

    margin-bottom: 1.5rem;
}

.stat-card {
    padding: 1.4rem;

    background: white;
    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);

    box-sizing: border-box;

    transition: 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);

    box-shadow:
        0 4px 12px rgba(184, 115, 131, 0.06),
        0 10px 28px rgba(109, 59, 71, 0.08);
}

.stat-title {
    display: block;

    margin-bottom: 0.7rem;

    color: var(--text-color);
    font-size: 0.9rem;
    opacity: 0.7;
}

.stat-card strong {
    display: block;

    margin-bottom: 0.4rem;

    color: var(--text-color);
    font-size: 1.8rem;
}

.stat-description {
    color: var(--text-color);
    font-size: 0.8rem;
    opacity: 0.55;
}


/* =========================================================
   HAUPTBEREICH
   ========================================================= */

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;

    margin-bottom: 1.5rem;
}


/* =========================================================
   KARTEN
   ========================================================= */

.dashboard-card {
    padding: 1.5rem;

    background: white;
    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);

    box-sizing: border-box;
}


/* =========================================================
   CARD HEADER
   ========================================================= */

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 1.5rem;
}

.card-header h2 {
    margin: 0;

    color: var(--text-color);
    font-size: 1.2rem;
    font-weight: 600;
}

.card-header p {
    margin: 0.3rem 0 0;

    color: var(--text-color);
    font-size: 0.85rem;
    opacity: 0.6;
}


/* =========================================================
   BUTTONS
   ========================================================= */

.primary-button,
.secondary-button,
.action-button,
.accept-button,
.decline-button,
.remove-button {
    border: none;
    border-radius: 7px;

    font-family: inherit;
    cursor: pointer;

    transition: 0.2s;
}

.primary-button {
    padding: 0.5rem 0.9rem;

    background: var(--text-color);
    color: white;

    font-size: 0.8rem;
}

.secondary-button {
    padding: 0.5rem 0.8rem;

    background: rgba(184, 115, 131, 0.08);
    color: var(--text-color);

    font-size: 0.8rem;
}

.primary-button:hover,
.secondary-button:hover,
.action-button:hover {
    opacity: 0.8;
}


/* =========================================================
   BUCHUNGSANFRAGEN
   ========================================================= */

.request-list {
    display: flex;
    flex-direction: column;
}

.request-card {
    display: flex;
    align-items: center;

    gap: 1rem;

    padding: 1rem 0;

    border-top: 1px solid rgba(109, 59, 71, 0.08);
}

.request-card:first-child {
    border-top: none;
}

.request-avatar {
    width: 42px;
    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 50%;

    background: rgba(184, 115, 131, 0.1);
    color: var(--text-color);

    font-size: 0.75rem;
    font-weight: 600;
}

.request-info {
    display: flex;
    flex-direction: column;

    flex: 1;
}

.request-info h3 {
    margin: 0;

    color: var(--text-color);
    font-size: 0.85rem;
    font-weight: 600;
}

.request-info p {
    margin: 0.2rem 0 0;

    color: var(--text-color);
    font-size: 0.8rem;
    opacity: 0.6;
}

.request-info span {
    margin-top: 0.15rem;

    color: var(--text-color);
    font-size: 0.7rem;
    opacity: 0.45;
}

.request-actions {
    display: flex;
    gap: 0.4rem;
}


/* =========================================================
   TERMINE
   ========================================================= */

.appointment-list {
    display: flex;
    flex-direction: column;
}

.appointment {
    display: flex;
    align-items: center;

    gap: 1rem;

    padding: 1rem 0;

    border-top: 1px solid rgba(109, 59, 71, 0.08);
}

.appointment:first-child {
    border-top: none;
}

.appointment-date {
    width: 45px;

    flex-shrink: 0;

    text-align: center;
}

.appointment-date strong {
    display: block;

    color: var(--text-color);
    font-size: 1.2rem;
}

.appointment-date span {
    color: var(--text-color);
    font-size: 0.7rem;
    opacity: 0.5;
}

.appointment-info {
    display: flex;
    flex-direction: column;

    flex: 1;
}

.appointment-info strong {
    color: var(--text-color);
    font-size: 0.85rem;
}

.appointment-info span {
    margin-top: 0.2rem;

    color: var(--text-color);
    font-size: 0.8rem;
    opacity: 0.6;
}


/* =========================================================
   STATUS
   ========================================================= */

.status {
    padding: 0.35rem 0.7rem;

    border-radius: 20px;

    font-size: 0.75rem;

    white-space: nowrap;
}

.status.pending {
    background: #fff3cd;
    color: #7c641d;
}

.status.confirmed {
    background: #e2f4e8;
    color: #397052;
}

.status.completed {
    background: rgba(109, 59, 71, 0.08);
    color: var(--text-color);
}


/* =========================================================
   SCHNELLZUGRIFF
   ========================================================= */

.quick-actions {
    display: flex;
    flex-direction: column;

    gap: 0.7rem;
}

.action-button {
    width: 100%;

    padding: 0.9rem;

    background: rgba(184, 115, 131, 0.08);
    color: var(--text-color);

    text-align: left;
    font-size: 0.85rem;
}


/* =========================================================
   MITARBEITER VERWALTEN
   ========================================================= */

.employee-management {
    margin-bottom: 1.5rem;
}

.employee-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.employee-card {
    padding: 1.2rem;

    background: white;
    border-radius: 10px;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);

    transition: 0.2s;
}

.employee-card:hover {
    transform: translateY(-2px);
}

.employee-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 1rem;
}

.employee-avatar {
    width: 45px;
    height: 45px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: rgba(184, 115, 131, 0.1);
    color: var(--text-color);

    font-size: 0.75rem;
    font-weight: 600;
}

.employee-card h3 {
    margin: 0;

    color: var(--text-color);
    font-size: 0.9rem;
}

.employee-role {
    margin: 0.25rem 0 0;

    color: var(--text-color);
    font-size: 0.75rem;
    opacity: 0.6;
}

.employee-email {
    margin-top: 0.7rem;

    color: var(--text-color);
    font-size: 0.7rem;
    opacity: 0.5;

    overflow-wrap: anywhere;
}

.employee-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-top: 1rem;
    padding-top: 0.8rem;

    border-top: 1px solid rgba(109, 59, 71, 0.08);
}


/* =========================================================
   MITARBEITER STATUS
   ========================================================= */

.employee-status {
    padding: 0.3rem 0.6rem;

    border-radius: 20px;

    font-size: 0.7rem;
}

.employee-status.available {
    background: #e2f4e8;
    color: #397052;
}

.employee-status.busy {
    background: #f6e6eb;
    color: #8d5264;
}

.employee-status.vacation {
    background: #eee5e8;
    color: var(--text-color);
}


/* =========================================================
   VERFÜGBARKEIT
   ========================================================= */

.availability-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.8rem;
}

.availability-card {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0.9rem;

    background: rgba(184, 115, 131, 0.035);

    border-radius: 8px;

    border: 1px solid rgba(109, 59, 71, 0.05);
}

.availability-person {
    display: flex;
    align-items: center;

    gap: 0.65rem;
}

.avatar {
    width: 36px;
    height: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: rgba(184, 115, 131, 0.1);
    color: var(--text-color);

    font-size: 0.65rem;
    font-weight: 600;
}

.availability-person h3 {
    margin: 0;

    color: var(--text-color);
    font-size: 0.78rem;
}

.availability-person p {
    margin: 0.2rem 0 0;

    color: var(--text-color);
    font-size: 0.7rem;
    opacity: 0.5;
}

.availability-status {
    padding: 0.3rem 0.55rem;

    border-radius: 20px;

    font-size: 0.65rem;
}

.availability-status.available {
    background: #e2f4e8;
    color: #397052;
}

.availability-status.busy {
    background: #f6e6eb;
    color: #8d5264;
}


/* =========================================================
   SCHICHTEN
   ========================================================= */

.shift-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.shift-table {
    width: 100%;
    border-collapse: collapse;
}

.shift-table th {
    padding: 0.7rem;

    color: var(--text-color);
    font-size: 0.7rem;
    font-weight: 500;

    text-align: left;
    opacity: 0.5;

    border-bottom: 1px solid rgba(109, 59, 71, 0.08);
}

.shift-table td {
    padding: 0.8rem 0.7rem;

    color: var(--text-color);
    font-size: 0.75rem;

    border-bottom: 1px solid rgba(109, 59, 71, 0.06);
}

.shift-table tr:last-child td {
    border-bottom: none;
}

.table-employee {
    display: flex;
    align-items: center;

    gap: 0.6rem;
}

.table-avatar {
    width: 30px;
    height: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: rgba(184, 115, 131, 0.1);
    color: var(--text-color);

    font-size: 0.6rem;
    font-weight: 600;
}


/* =========================================================
   URLAUB
   ========================================================= */

.vacation-list {
    display: flex;
    flex-direction: column;
}

.vacation-card {
    display: flex;
    align-items: center;

    gap: 1rem;

    padding: 1rem 0;

    border-top: 1px solid rgba(109, 59, 71, 0.08);
}

.vacation-card:first-child {
    border-top: none;
}

.vacation-info {
    display: flex;
    flex-direction: column;

    flex: 1;
}

.vacation-info strong {
    color: var(--text-color);
    font-size: 0.85rem;
}

.vacation-info p {
    margin: 0.2rem 0 0;

    color: var(--text-color);
    font-size: 0.75rem;
    opacity: 0.6;
}

.vacation-info span {
    margin-top: 0.2rem;

    color: var(--text-color);
    font-size: 0.7rem;
    opacity: 0.45;
}

.vacation-actions {
    display: flex;
    gap: 0.4rem;
}


/* =========================================================
   LEERE BEREICHE
   ========================================================= */

.empty-state {
    padding: 2rem;

    text-align: center;

    color: var(--text-color);

    background: rgba(184, 115, 131, 0.035);
    border-radius: 8px;
}

.empty-state strong {
    display: block;

    margin-bottom: 0.35rem;

    font-size: 0.9rem;
}

.empty-state span {
    font-size: 0.75rem;
    opacity: 0.55;
}


/* =========================================================
   MODAL
   ========================================================= */

.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 1rem;

    background: rgba(70, 40, 50, 0.25);
    backdrop-filter: blur(3px);
}

.employee-modal {
    position: relative;

    width: min(500px, 100%);

    padding: 1.8rem;

    background: white;
    border-radius: 12px;

    box-shadow:
        0 8px 24px rgba(109, 59, 71, 0.12);
}

.modal-close {
    position: absolute;

    top: 1rem;
    right: 1rem;

    width: 30px;
    height: 30px;

    border: none;
    border-radius: 50%;

    background: rgba(184, 115, 131, 0.08);
    color: var(--text-color);

    cursor: pointer;
}

.modal-header h2 {
    margin: 0;

    color: var(--text-color);
    font-size: 1.25rem;
}

.modal-header p {
    margin: 0.35rem 0 1.5rem;

    color: var(--text-color);
    font-size: 0.8rem;
    opacity: 0.6;
}

.employee-form {
    display: flex;
    flex-direction: column;

    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;

    gap: 0.4rem;
}

.form-group label {
    color: var(--text-color);
    font-size: 0.75rem;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;

    padding: 0.7rem 0.8rem;

    border: 1px solid rgba(109, 59, 71, 0.12);
    border-radius: 7px;

    outline: none;

    font-family: inherit;
    font-size: 0.8rem;

    background: white;
    color: var(--text-color);
}

.form-group input:focus {
    border-color: rgba(184, 115, 131, 0.6);

    box-shadow:
        0 0 0 3px rgba(184, 115, 131, 0.08);
}

.form-group small {
    color: var(--text-color);
    font-size: 0.7rem;
    opacity: 0.5;
}

.form-error {
    padding: 0.7rem;

    border-radius: 7px;

    background: #f6e6eb;
    color: #8d5264;

    font-size: 0.75rem;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;

    gap: 0.5rem;

    margin-top: 0.5rem;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1000px) {

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .employee-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .availability-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}


@media (max-width: 700px) {

    .employee-page {
        padding: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .employee-grid {
        grid-template-columns: 1fr;
    }

    .availability-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        align-items: flex-start;

        gap: 1rem;
    }

    .page-header h1 {
        font-size: 1.7rem;
    }

    .employee-date {
        font-size: 0.75rem;
    }

    .appointment {
        align-items: flex-start;
    }

    .appointment .status {
        margin-left: auto;
    }

    .request-card {
        align-items: flex-start;
    }

    .request-actions {
        flex-direction: column;
    }

    .vacation-card {
        align-items: flex-start;
        flex-direction: column;
    }

    .vacation-actions {
        width: 100%;
    }

    .vacation-actions button {
        flex: 1;
    }

    .card-header {
        align-items: flex-start;
        gap: 1rem;
    }

    .modal-actions {
        flex-direction: column-reverse;
    }

    .modal-actions button {
        width: 100%;
    }
}

</style>