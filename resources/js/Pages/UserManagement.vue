<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
})

const selectedRole = ref('all')

const filteredUsers = computed(() => {
    if (selectedRole.value === 'all') {
        return props.users
    }

    return props.users.filter(
        (user) => user.role === selectedRole.value
    )
})

function formatDate(date) {
    if (!date) {
        return '—'
    }

    return new Date(date).toLocaleDateString('de-DE')
}
</script>

<template>
    <Head title="Benutzerverwaltung" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Benutzerverwaltung
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="mb-4 text-lg font-semibold">
                            Alle Benutzer
                        </h3>

                        <label for="role-filter" class="mb-2 block font-medium">
                            Nach Rolle filtern
                        </label>

                        <select
                            id="role-filter"
                            v-model="selectedRole"
                            class="mb-6 rounded border p-2"
                        >
                            <option value="all">Alle Rollen</option>
                            <option value="customer">Kunden</option>
                            <option value="employee">Mitarbeiter</option>
                            <option value="admin">Admins</option>
                        </select>

                        <p v-if="filteredUsers.length === 0">
                            Keine Benutzer für diese Auswahl gefunden.
                        </p>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="p-3">Name</th>
                                        <th class="p-3">E-Mail</th>
                                        <th class="p-3">Rolle</th>
                                        <th class="p-3">Studio-ID</th>
                                        <th class="p-3">Registriert am</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr
                                        v-for="user in filteredUsers"
                                        :key="user.id"
                                        class="border-b"
                                    >
                                        <td class="p-3">{{ user.name }}</td>
                                        <td class="p-3">{{ user.email }}</td>
                                        <td class="p-3">{{ user.role }}</td>
                                        <td class="p-3">
                                            {{ user.nail_studio_id ?? '—' }}
                                        </td>
                                        <td class="p-3">
                                            {{ formatDate(user.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p class="mt-4 text-sm text-gray-600">
                            Angezeigte Benutzer: {{ filteredUsers.length }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
