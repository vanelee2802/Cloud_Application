<script setup> 
import NavLink from '@/Components/NavLink.vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
    <nav class="navbar">

        <ul class="nav-links">
            <li>
                <NavLink href="/" :active="route().current('welcome')">
                    Home
                </NavLink>
            </li>

            <li>
                <NavLink href="/DesignEditor" :active="route().current('DesignEditor')">
                    Design Editor
                </NavLink>
            </li>

            <li>
                <NavLink href="/Appointments" :active="route().current('Appointments')">
                    Appointments
                </NavLink>
            </li>

            
            <li class="login-item">
                <template v-if="page.props.auth.user">
                    <span class="user-name">
                        {{ page.props.auth.user.name }}
                    </span>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="logout-button"
                    >
                        Logout
                    </Link>
                </template>

                <a v-else href="/auth/google" class="login-button">
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
}

.user-name {
    margin-right: 1rem;
}

.logout-button {
    margin-right: 2rem;
    padding: 0.6rem 1.5rem;
    border: none;
    border-radius: 6px;
    background-color: var(--text-color);
    color: white;
    cursor: pointer;
}

.logout-button:hover {
    opacity: 0.85;
}
</style>