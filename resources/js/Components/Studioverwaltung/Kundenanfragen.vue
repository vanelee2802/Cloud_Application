<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

/*
|--------------------------------------------------------------------------
| Daten aus dem Backend
|--------------------------------------------------------------------------
*/

const customerRequests = computed(() => {
    return page.props.customerRequests ?? []
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

function formatAppointmentDate(request) {
    if (request?.date) {
        return formatDate(request.date)
    }

    return ''
}

function formatAppointmentTime(request) {
    if (request?.time) {
        return formatTime(request.time)
    }

    return ''
}
</script>

<template>
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
                        {{ formatAppointmentDate(request) }}

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

.section-header h2 {
    margin: 0;
    color: #4f3540;
    font-size: 1.2rem;
    font-weight: 700;
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

.request-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.request-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid rgba(143, 83, 100, 0.1);
    border-radius: 10px;
    background-color: #fdfbfc;
}

.request-avatar {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f5e7eb;
    color: #8f5364;
    font-size: 0.9rem;
    font-weight: 700;
}

.request-info {
    flex: 1;
    min-width: 0;
}

.request-info h3 {
    margin: 0 0 0.2rem;
    color: #4f3540;
    font-size: 0.95rem;
    font-weight: 700;
}

.request-info p {
    margin: 0 0 0.2rem;
    color: #6f5961;
    font-size: 0.85rem;
}

.request-info span {
    color: #8a7a80;
    font-size: 0.75rem;
}

.request-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.accept-button,
.decline-button {
    padding: 0.55rem 0.8rem;
    border-radius: 7px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.accept-button {
    border: 1px solid #8f5364;
    background-color: #8f5364;
    color: white;
}

.accept-button:hover {
    background-color: #754354;
}

.decline-button {
    border: 1px solid rgba(143, 83, 100, 0.2);
    background-color: white;
    color: #8f5364;
}

.decline-button:hover {
    background-color: #f5e7eb;
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

    .request-card {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .request-info {
        min-width: calc(100% - 60px);
    }

    .request-actions {
        width: 100%;
        margin-left: 60px;
    }
}

@media (max-width: 480px) {
    .dashboard-section {
        padding: 1rem;
    }

    .section-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .request-card {
        gap: 0.75rem;
    }

    .request-avatar {
        width: 40px;
        height: 40px;
    }

    .request-actions {
        margin-left: 0;
        flex-direction: column;
        align-items: stretch;
    }

    .accept-button,
    .decline-button {
        width: 100%;
    }
}
</style>