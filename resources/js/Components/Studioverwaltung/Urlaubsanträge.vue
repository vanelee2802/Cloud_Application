<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const vacationRequests = computed(() => {
    return page.props.vacationRequests ?? []
})

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
</script>

<template>
    <section class="dashboard-section">

        <div class="section-header">

            <div>
                <h2>
                    Urlaubsanträge
                </h2>
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

.vacation-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.vacation-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid rgba(143, 83, 100, 0.1);
    border-radius: 10px;
    background-color: #fdf9fa;
}

.vacation-info {
    flex: 1;
    min-width: 0;
}

.vacation-info h3 {
    margin: 0;
    color: #4f3540;
    font-size: 0.9rem;
    font-weight: 700;
}

.vacation-info p {
    margin: 0.3rem 0;
    color: #6f5961;
    font-size: 0.8rem;
}

.vacation-info span {
    color: #8a7a80;
    font-size: 0.75rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge.pending {
    background-color: #f5e7eb;
    color: #8f5364;
}

.vacation-actions {
    display: flex;
    gap: 0.5rem;
}

.accept-button,
.decline-button {
    padding: 0.5rem 0.75rem;
    border-radius: 7px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
}

.accept-button {
    border: 1px solid rgba(83, 126, 91, 0.2);
    background-color: #f2f8f3;
    color: #52765a;
}

.decline-button {
    border: 1px solid rgba(143, 83, 100, 0.2);
    background-color: #fdf5f6;
    color: #8f5364;
}

.accept-button:hover {
    background-color: #e8f3ea;
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

    .section-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .secondary-button {
        width: 100%;
    }

    .vacation-card {
        align-items: flex-start;
        flex-direction: column;
    }

    .vacation-actions {
        width: 100%;
    }

    .accept-button,
    .decline-button {
        flex: 1;
    }
}

@media (max-width: 480px) {
    .dashboard-section {
        padding: 1rem;
    }

    .vacation-card {
        padding: 0.85rem;
    }
}
</style>