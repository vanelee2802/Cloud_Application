<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
})

const selectedRole = ref('all')

const editingUserId = ref(null)

const editForm = useForm({
    name: '',
    email: '',
})

function startEditing(user) {
    editingUserId.value = user.id
    editForm.name = user.name
    editForm.email = user.email
    editForm.clearErrors()
}

function cancelEditing() {
    editingUserId.value = null
    editForm.reset()
    editForm.clearErrors()
}

function saveUser() {
    editForm.patch(`/admin/users/${editingUserId.value}`, {
        onSuccess: () => {
            cancelEditing()
        },
    })
}

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
                                        <th class="p-3">Aktionen</th>
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
                                        <td class="p-3">
                                            <button
                                                type="button"
                                                @click="startEditing(user)"
                                                class="rounded bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700"
                                            >
                                                Bearbeiten
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p class="mt-4 text-sm text-gray-600">
                            Angezeigte Benutzer: {{ filteredUsers.length }}
                        </p>
                                                <div
                            v-if="editingUserId !== null"
                            class="mt-8 rounded-lg border p-6"
                        >
                            <h3 class="mb-4 text-lg font-semibold">
                                Benutzer bearbeiten
                            </h3>

                            <form @submit.prevent="saveUser" class="space-y-4">
                                <div>
                                    <label for="edit-name" class="mb-1 block font-medium">
                                        Name
                                    </label>
                                    <input
                                        id="edit-name"
                                        v-model="editForm.name"
                                        type="text"
                                        required
                                        class="w-full rounded border p-2"
                                    />
                                    <p v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ editForm.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label for="edit-email" class="mb-1 block font-medium">
                                        E-Mail
                                    </label>
                                    <input
                                        id="edit-email"
                                        v-model="editForm.email"
                                        type="email"
                                        required
                                        class="w-full rounded border p-2"
                                    />
                                    <p v-if="editForm.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ editForm.errors.email }}
                                    </p>
                                </div>

                                <div class="flex gap-3">
                                    <button
                                        type="submit"
                                        :disabled="editForm.processing"
                                        class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                                    >
                                        Speichern
                                    </button>

                                    <button
                                        type="button"
                                        @click="cancelEditing"
                                        class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300"
                                    >
                                        Abbrechen
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
