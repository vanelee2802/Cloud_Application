<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, nextTick } from 'vue'

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    // Studios der Inhaberin (für die Auswahl beim Bearbeiten)
    studios: {
        type: Array,
        default: () => [],
    },
})

/*
|--------------------------------------------------------------------------
| Rollen
|--------------------------------------------------------------------------
*/

const roleLabels = {
    owner: 'Inhaberin',
    admin: 'Schichtleitung',
    employee: 'Mitarbeiter',
    customer: 'Kunde',
}

// Rollen, die die Inhaberin vergeben darf (owner nur durch IT)
const assignableRoles = ['customer', 'employee', 'admin']

function roleLabel(role) {
    return roleLabels[role] ?? role
}

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const selectedRole = ref('all')

const filteredUsers = computed(() => {
    if (selectedRole.value === 'all') {
        return props.users
    }

    return props.users.filter(
        (user) => user.role === selectedRole.value
    )
})

/*
|--------------------------------------------------------------------------
| Bearbeiten
|--------------------------------------------------------------------------
*/

const editingUserId = ref(null)
const editingIsOwner = ref(false)
const editSection = ref(null)

const editForm = useForm({
    name: '',
    email: '',
    role: 'customer',
    nail_studio_id: '',
})

// Studio-Auswahl nur für Mitarbeiter und Schichtleitung
const needsStudio = computed(() => {
    return ['employee', 'admin'].includes(editForm.role)
})

async function startEditing(user) {
    editingUserId.value = user.id
    editingIsOwner.value = user.role === 'owner'

    editForm.name = user.name
    editForm.email = user.email
    editForm.role = user.role
    editForm.nail_studio_id = user.nail_studio_id ?? ''
    editForm.clearErrors()

    // Formular steht unter der Tabelle – dorthin scrollen
    await nextTick()
    editSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function onRoleChange() {
    if (!needsStudio.value) {
        editForm.nail_studio_id = ''
        return
    }

    // Nur ein Studio vorhanden: direkt vorauswählen
    if (!editForm.nail_studio_id && props.studios.length === 1) {
        editForm.nail_studio_id = props.studios[0].id
    }
}

function cancelEditing() {
    editingUserId.value = null
    editingIsOwner.value = false
    editForm.reset()
    editForm.clearErrors()
}

function saveUser() {
    editForm
        .transform((data) => {
            // Bei der Inhaberin selbst nur Name und E-Mail ändern
            if (editingIsOwner.value) {
                return { name: data.name, email: data.email }
            }

            return {
                ...data,
                nail_studio_id: needsStudio.value ? data.nail_studio_id : null,
            }
        })
        .patch(`/admin/users/${editingUserId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                cancelEditing()
            },
        })
}

/*
|--------------------------------------------------------------------------
| Hilfsfunktionen
|--------------------------------------------------------------------------
*/

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
                            <option value="admin">Schichtleitung</option>
                            <option value="owner">Inhaberin</option>
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
                                        <th class="p-3">Studio</th>
                                        <th class="p-3">Registriert am</th>
                                        <th class="p-3">Aktionen</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr
                                        v-for="user in filteredUsers"
                                        :key="user.id"
                                        class="border-b"
                                        :class="{ 'bg-indigo-50': user.id === editingUserId }"
                                    >
                                        <td class="p-3">{{ user.name }}</td>
                                        <td class="p-3">{{ user.email }}</td>
                                        <td class="p-3">{{ roleLabel(user.role) }}</td>
                                        <td class="p-3">
                                            {{ user.nail_studio?.name ?? '—' }}
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
                            ref="editSection"
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

                                <!-- Rolle: bei der Inhaberin nicht änderbar -->
                                <div v-if="!editingIsOwner">
                                    <label for="edit-role" class="mb-1 block font-medium">
                                        Rolle
                                    </label>
                                    <select
                                        id="edit-role"
                                        v-model="editForm.role"
                                        @change="onRoleChange"
                                        class="w-full rounded border p-2"
                                    >
                                        <option
                                            v-for="role in assignableRoles"
                                            :key="role"
                                            :value="role"
                                        >
                                            {{ roleLabel(role) }}
                                        </option>
                                    </select>
                                    <p v-if="editForm.errors.role" class="mt-1 text-sm text-red-600">
                                        {{ editForm.errors.role }}
                                    </p>
                                </div>

                                <p v-else class="text-sm text-gray-600">
                                    Die Rolle der Inhaberin kann nur von der IT geändert werden.
                                </p>

                                <!-- Studio: nur bei Mitarbeiter und Schichtleitung -->
                                <div v-if="!editingIsOwner && needsStudio">
                                    <label for="edit-studio" class="mb-1 block font-medium">
                                        Studio
                                    </label>
                                    <select
                                        id="edit-studio"
                                        v-model="editForm.nail_studio_id"
                                        class="w-full rounded border p-2"
                                    >
                                        <option value="" disabled>
                                            Studio auswählen
                                        </option>
                                        <option
                                            v-for="studio in studios"
                                            :key="studio.id"
                                            :value="studio.id"
                                        >
                                            {{ studio.name }}
                                        </option>
                                    </select>
                                    <p v-if="editForm.errors.nail_studio_id" class="mt-1 text-sm text-red-600">
                                        {{ editForm.errors.nail_studio_id }}
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