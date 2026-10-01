<script setup>
import NavLink from '@/Components/NavLink.vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
    <nav class="navbar">

        <ul class="nav-links">

            <!-- Home: für alle -->
            <li>
                <NavLink href="/" :active="route().current('welcome')">
                    Home
                </NavLink>
            </li>

            <!-- Design Editor: für alle -->
            <li>
                <NavLink href="/DesignEditor" :active="route().current('DesignEditor')">
                    Design Editor
                </NavLink>
            </li>

            <li>
                <NavLink href="/StudioDashboard" :active="route().current('StudioDashboard')">
                    Studio Dashboard
                </NavLink>
            </li>

            <!-- Studio Dashboard: nur für Employee und Admin -->
           <!-- <li
                v-if="
                    page.props.auth?.user &&
                    (
                        page.props.auth.user.role === 'employee' ||
                        page.props.auth.user.role === 'admin'
                    )
                "
            >
                <NavLink
                    href="/studio-dashboard"
                    :active="route().current('studio.dashboard')"
                >
                    Studio Dashboard
                </NavLink>
            </li>-->

            <!-- Login: für alle -->
            <li class="login-item">

                <!-- Benutzername, wenn angemeldet, erstmal nur platzhalter-->
                 <span class="user-name">
        Vanessa Oetken
    </span>
                <a href="/auth/google" class="login-button">
                    Login
                </a>
            </li>

        </ul>

    </nav>
</template>

<style lang="css" scoped>
.navbar {
    background-color: var(--background-color);

    margin-bottom: 3rem;

    border-bottom: 1px var(--background-color) solid;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);
}

.nav-links {
    display: flex;
    justify-content: left;
    gap: 1rem;

    margin-left: 1rem;
    padding: 1rem;
    color: var(--text-color);
}

.login-button {
    margin-right: 2rem;
    padding: 0.6rem 1.5rem;

    border: none;
    border-radius: 6px;

    background-color: var(--text-color);
    color: white;

    cursor: pointer;
}

.login-item {
    margin-left: auto;

    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-name {
    font-weight: 500;
    color: var(--text-color);
}
</style>