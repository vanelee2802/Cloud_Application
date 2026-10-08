<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

defineProps({
    employees: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    name: '',
    email: '',
})

function addEmployee() {
    form.post('/employees', {
        onSuccess: () => {
            form.reset()
        },
    })
}

function removeEmployee(employeeId) {
    if (!confirm('Möchtest du diesen Mitarbeiter wirklich entfernen?')) {
        return
    }

    form.delete(`/employees/${employeeId}`)
}
</script>

<template>
    <Head title="Mitarbeiterverwaltung" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mitarbeiterverwaltung
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="mb-4 text-lg font-semibold">
                            Mitarbeiter
                        </h3>
                            <form
                            @submit.prevent="addEmployee"
                            class="mb-6 rounded border p-4"
                            >
                            <h4 class="mb-3 font-semibold">
                                Mitarbeiter hinzufügen
                            </h4>

                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Name"
                                class="mb-3 w-full rounded border p-2"
                            />

                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="E-Mail"
                                class="mb-3 w-full rounded border p-2"
                            />

                            <button
                                type="submit"
                                class="rounded bg-gray-800 px-4 py-2 text-white"
                            >
                                Mitarbeiter hinzufügen
                            </button>
                            </form>

                        <p v-if="employees.length === 0">
                            Keine Mitarbeiter vorhanden.
                        </p>

                        <div v-else>
                            <div
                                v-for="employee in employees"
                                :key="employee.id"
                                class="mb-3 rounded border p-4"
                            >
                                <p class="font-semibold">
                                    {{ employee.name }}
                                </p>

                                <p class="text-gray-600">
                                    {{ employee.email }}
                                </p>

                                <p class="text-sm text-gray-500">
                                    Rolle: {{ employee.role }}
                                </p>
                                <button
                                    type="button"
                                    @click="removeEmployee(employee.id)"
                                    class="mt-3 rounded bg-red-600 px-4 py-2 text-white"
                                >
                                    Mitarbeiter entfernen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>