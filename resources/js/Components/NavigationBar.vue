<script setup>
import NavLink from '@/Components/NavLink.vue'
import { usePage, router } from '@inertiajs/vue3'

const page = usePage()

const roleName = {
    customer: 'Kunde',
    employee: 'Mitarbeiter',
    admin: 'Admin',
}
</script>

<template>
    <nav class="navbar">

        <ul class="nav-links">

            <!-- Home: für alle -->
            <li>
                <NavLink
                    href="/"
                    :active="route().current('welcome')"
                >
                    Home
                </NavLink>
            </li>

            <!-- Design Editor: für alle -->
            <li>
                <NavLink
                    href="/DesignEditor"
                    :active="route().current('DesignEditor')"
                >
                    Design Editor
                </NavLink>
            </li>

            <!-- Studio Dashboard: für Admin -->
            <li v-if="page.props.auth?.user?.role === 'admin'">
                <NavLink
                    href="/StudioDashboard"
                    :active="route().current('StudioDashboard')"
                >
                    Studio Dashboard
                </NavLink>
            </li>

            <!-- Employee: für Employee und Admin -->
            <li
                v-if="
                    page.props.auth?.user &&
                    (
                        page.props.auth.user.role === 'employee' ||
                        page.props.auth.user.role === 'admin'
                    )
                "
            >
                <NavLink
                    href="/Employee"
                    :active="route().current('Employee')"
                >
                    Employee
                </NavLink>
            </li>

            <!-- Benutzerbereich -->
            <li
                v-if="page.props.auth?.user"
                class="user-area"
            >

                <!-- Google Profilbild -->
                <img
                    v-if="page.props.auth.user.avatar"
                    :src="page.props.auth.user.avatar"
                    :alt="page.props.auth.user.name"
                    class="profile-image"
                >

                <!-- Name + Rolle -->
                <div class="user-info">
                    <span class="user-name">
                        {{ page.props.auth.user.name }}
                    </span>

                    <span class="user-role">
                        {{ roleName[page.props.auth.user.role] }}
                    </span>
                </div>

                <!-- Einstellungen -->
                <a
                    href="/settings"
                    class="settings-button"
                    title="Einstellungen"
                >
                    ⚙
                </a>

                <!-- Logout -->
                <button
                    class="login-button"
                    @click="router.post('/logout')"
                >
                    Logout
                </button>

            </li>

            <!-- Nicht eingeloggt -->
            <li
                v-else
                class="login-item"
            >
                <a
                    href="/auth/google"
                    class="login-button"
                >
                    Mit Google anmelden
                </a>
            </li>

        </ul>

    </nav>
</template>

<style lang="css" scoped>
.navbar {
    background-color: #8f5364;

    margin-bottom: 3rem;

    border-bottom: 1px solid #754353;

    box-shadow:
        0 2px 8px rgba(80, 35, 48, 0.12),
        0 8px 24px rgba(80, 35, 48, 0.15);
}

.nav-links {
    display: flex;
    align-items: center;
    justify-content: left;
    gap: 0.5rem;

    margin-left: 1rem;
    padding: 0.8rem 1rem;

    color: white;
}

/* =========================
   Navigation
   ========================= */

.nav-links li {
    display: flex;
    align-items: center;
}

/* Hover für die Navigations-Reiter */
.nav-links :deep(a) {
    color: white;
    text-decoration: none;

    padding: 0.65rem 1rem;
    border-radius: 8px;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease,
        color 0.2s ease;
}

.nav-links :deep(a:hover) {
    background-color: rgba(255, 255, 255, 0.15);

    transform: translateY(-1px);
}

/* Aktiver Reiter */
.nav-links :deep(a.active) {
    background-color: rgba(255, 255, 255, 0.2);
}

/* =========================
   Benutzerbereich
   ========================= */

.login-item {
    margin-left: auto;
}

.user-area {
    margin-left: auto;

    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* =========================
   Google Profilbild
   ========================= */

.profile-image {
    width: 42px;
    height: 42px;

    border-radius: 50%;

    object-fit: cover;

    border: 2px solid rgba(255, 255, 255, 0.8);
}

/* =========================
   Name + Rolle
   ========================= */

.user-info {
    display: flex;
    flex-direction: column;
    justify-content: center;

    min-width: 100px;
}

.user-name {
    font-weight: 600;
    color: white;
}

.user-role {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.75);
}

/* =========================
   Zahnrad
   ========================= */

.settings-button {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    border-radius: 50%;

    text-decoration: none;
    font-size: 1.3rem;

    color: white;

    transition:
        background-color 0.2s ease,
        transform 0.3s ease;
}

.settings-button:hover {
    background-color: rgba(255, 255, 255, 0.15);

    transform: rotate(30deg);
}

/* =========================
   Login / Logout Button
   ========================= */

.login-button {
    margin-right: 1rem;

    padding: 0.6rem 1.5rem;

    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 8px;

    background-color: white;
    color: #8f5364;

    font-weight: 600;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.login-button:hover {
    background-color: #754353;
    color: white;

    transform: translateY(-2px);

    box-shadow:
        0 4px 10px rgba(50, 20, 30, 0.2);
}

.login-button:active {
    transform: translateY(0);
}

/* =========================
   Nicht eingeloggt
   ========================= */

.login-item .login-button {
    margin-right: 1rem;
}
</style>