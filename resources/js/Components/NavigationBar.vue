<script setup>
import NavLink from '@/Components/NavLink.vue'
import CartIcon from '@/Components/CartIcon.vue'
import Checkout from '@/Pages/Checkout.vue'
import { usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

const page = usePage()

const roleName = {
    customer: 'Kunde',
    employee: 'Mitarbeiter',
    admin: 'Schichtleitung',
    owner: 'Studioinhaberin',
}

const user = computed(() => page.props.auth?.user ?? null)
const role = computed(() => user.value?.role)

const isOwner = computed(() => role.value === 'owner')
const isLead = computed(() => ['admin', 'owner'].includes(role.value))
const isStaff = computed(() =>
    ['employee', 'admin', 'owner'].includes(role.value)
)

const menuOpen = ref(false)
const menuRef = ref(null)

// Warenkorb-Symbol öffnet das bestehende Checkout-Fenster
const showCheckout = ref(false)

function toggleMenu() {
    menuOpen.value = !menuOpen.value
}

function closeMenu() {
    menuOpen.value = false
}

function handleOutsideClick(event) {
    if (menuRef.value && !menuRef.value.contains(event.target)) {
        closeMenu()
    }
}

function handleEscape(event) {
    if (event.key === 'Escape') closeMenu()
}

onMounted(() => {
    document.addEventListener('click', handleOutsideClick)
    document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleOutsideClick)
    document.removeEventListener('keydown', handleEscape)
})
</script>

<template>
    <nav class="navbar">
        <div class="navbar-inner">

            <!-- Öffentliche Links -->
            <ul class="nav-links">
                <li>
                    <NavLink href="/" :active="route().current('welcome')">
                        Home
                    </NavLink>
                </li>

                <li>
                    <NavLink
                        href="/Leistungen"
                        :active="route().current('Leistungen')"
                    >
                        Leistungen
                    </NavLink>
                </li>

                <li>
                    <NavLink
                        href="/DesignEditor"
                        :active="route().current('DesignEditor')"
                    >
                        Design Editor
                    </NavLink>
                </li>

            

                <li>
                    <NavLink
                        href="/StudioSearch"
                        :active="route().current('StudioSearch')"
                    >
                        Studio-Suche
                    </NavLink>
                </li>
            </ul>

            <!-- Mein Bereich: direkt neben Studio-Suche -->
            <div
                v-if="user"
                ref="menuRef"
                class="menu-wrapper"
            >
                <button
                    type="button"
                    class="menu-trigger"
                    :class="{ 'menu-trigger-active': menuOpen }"
                    :aria-expanded="menuOpen"
                    aria-haspopup="true"
                    @click="toggleMenu"
                >
                    <span>Mein Bereich</span>

                    <svg
                        class="chevron"
                        :class="{ rotated: menuOpen }"
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>

                <Transition name="dropdown">
                    <div v-if="menuOpen" class="mega-menu">

                        <!-- Mein Studio -->
                        <section class="menu-section">
                            <h2 class="menu-heading">Mein Studio</h2>

                            <div class="menu-grid">
                                <NavLink
                                    v-if="isLead"
                                    href="/StudioDashboard"
                                    :active="route().current('StudioDashboard')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="3" width="7" height="7" rx="1" />
                                        <rect x="14" y="3" width="7" height="7" rx="1" />
                                        <rect x="3" y="14" width="7" height="7" rx="1" />
                                        <rect x="14" y="14" width="7" height="7" rx="1" />
                                    </svg>
                                    <span>Dashboard</span>
                                </NavLink>

                                <NavLink
                                    href="/meine-designs"
                                    :active="route().current('designs.page')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="3" width="18" height="18" rx="3" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <path d="m21 15-5-5L5 21" />
                                    </svg>
                                    <span>Meine Designs</span>
                                </NavLink>

                                <NavLink
                                    v-if="isStaff"
                                    href="/Studioverwaltung"
                                    :active="route().current('Studioverwaltung')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 21h18" />
                                        <path d="M5 21V7l8-4v18" />
                                        <path d="M13 10h6v11" />
                                        <path d="M8 9v.01M8 13v.01M8 17v.01" />
                                    </svg>
                                    <span>Studioverwaltung</span>
                                </NavLink>

                                <NavLink
                                    href="/rechnungen"
                                    :active="route().current('receipts.index')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z" />
                                        <path d="M9 8h6M9 12h6" />
                                    </svg>
                                    <span>Meine Rechnungen</span>
                                </NavLink>

                                <NavLink
                                    v-if="isLead"
                                    href="/studio/rechnungen"
                                    :active="route().current('receipts.studio')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <path d="M3 10h18M7 15h4" />
                                    </svg>
                                    <span>Studio-Rechnungen</span>
                                </NavLink>
                            </div>
                        </section>

                        <div class="menu-divider"></div>

                        <!-- App-Verwaltung -->
                        <section v-if="isStaff || isOwner" class="menu-section">
                            <h2 class="menu-heading">App-Verwaltung</h2>

                            <div class="menu-grid">
                                <NavLink
                                    v-if="isStaff"
                                    href="/Studioverwaltung"
                                    :active="route().current('Studioverwaltung')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 21h18" />
                                        <path d="M5 21V7l8-4v18" />
                                        <path d="M13 10h6v11" />
                                        <path d="M8 9v.01M8 13v.01M8 17v.01" />
                                    </svg>
                                    <span>Studios verwalten</span>
                                </NavLink>

                                <!-- Preisverwaltung: nur für die Studioinhaberin -->
                                <NavLink
                                    v-if="isOwner"
                                    href="/admin/preise"
                                    :active="route().current('admin.prices.index')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 2v20" />
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                    </svg>
                                    <span>Preisverwaltung</span>
                                </NavLink>

                                <!-- NEU: Bewertungen verwalten – nur für die Studioinhaberin -->
                                <NavLink
                                    v-if="isOwner"
                                    href="/admin/bewertungen"
                                    :active="route().current('admin.reviews.page')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z" />
                                    </svg>
                                    <span>Bewertungen</span>
                                </NavLink>

                                <NavLink
                                    v-if="isOwner"
                                    href="/admin/users"
                                    :active="route().current('admin.users.index')"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="10" cy="7" r="4" />
                                        <path d="M20 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    <span>Benutzerverwaltung</span>
                                </NavLink>

                                <a
                                    href="/settings"
                                    class="mega-menu-link"
                                    @click="closeMenu"
                                >
                                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="3" />
                                        <path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z" />
                                    </svg>
                                    <span>Einstellungen</span>
                                </a>
                            </div>
                        </section>
                    </div>
                </Transition>
            </div>

            <!-- Benutzerinformationen bleiben rechts -->
            <div v-if="user" class="navbar-right">
                <CartIcon @open="showCheckout = true" />

                <img
                    v-if="user.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                    class="profile-image"
                />

                <div class="user-info">
                    <span class="user-name">{{ user.name }}</span>
                    <span class="user-role">
                        {{ roleName[user.role] || user.role }}
                    </span>
                </div>

                <a
                    href="/settings"
                    class="settings-button"
                    title="Einstellungen"
                    aria-label="Einstellungen"
                >
                    ⚙
                </a>

                <button
                    type="button"
                    class="logout-button"
                    @click="router.post('/logout')"
                >
                    Logout
                </button>
            </div>

            <!-- Gäste können auch buchen, daher Warenkorb auch hier -->
            <div v-else class="navbar-right">
                <CartIcon @open="showCheckout = true" />

                <a href="/auth/google" class="login-button">
                    Mit Google anmelden
                </a>
            </div>
        </div>

        <!-- Checkout-Fenster über der ganzen Seite -->
        <Teleport to="body">
            <Checkout
                v-if="showCheckout"
                @close="showCheckout = false"
            />
        </Teleport>
    </nav>
</template>

<style scoped>
.navbar {
    position: relative;
    z-index: 100;
    margin-bottom: 3rem;
    background: #8f5364;
    border-bottom: 1px solid #754353;
    box-shadow:
        0 2px 8px rgba(80, 35, 48, 0.12),
        0 8px 24px rgba(80, 35, 48, 0.15);
}

.navbar-inner {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    min-height: 68px;
    padding: 0.5rem 1.25rem;
}

/* Öffentliche Navigation */

.nav-links {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.15rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.nav-links li {
    display: flex;
    align-items: center;
}

.nav-links :deep(a) {
    display: inline-flex;
    align-items: center;
    padding: 0.65rem 0.75rem;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    white-space: nowrap;
    transition: background-color 0.2s ease;
}

.nav-links :deep(a:hover) {
    background: rgba(255, 255, 255, 0.15);
}

.nav-links :deep(a.active) {
    background: rgba(255, 255, 255, 0.2);
}

/* Mein-Bereich-Dropdown */

.menu-wrapper {
    position: relative;
    flex: 0 0 auto;
}

.menu-trigger {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 0.85rem;
    color: white;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    font: inherit;
    font-size: 0.9rem;
    font-weight: 600;
    white-space: nowrap;
    transition: background-color 0.2s ease;
}

.menu-trigger:hover,
.menu-trigger-active {
    background: rgba(255, 255, 255, 0.15);
}

.chevron {
    transition: transform 0.2s ease;
}

.chevron.rotated {
    transform: rotate(180deg);
}

/* Dropdown */

.mega-menu {
    position: absolute;
    top: calc(100% + 14px);
    left: 0;
    width: 680px;
    max-width: calc(100vw - 24px);
    max-height: min(75vh, 650px);
    overflow-x: hidden;
    overflow-y: auto;
    box-sizing: border-box;
    padding: 28px 24px 22px;
    color: #171417;
    background: #fffdfd;
    border: 1px solid #e7dfe2;
    border-top: 4px solid #8f5364;
    border-radius: 0 0 24px 24px;
    box-shadow: 0 12px 30px rgba(55, 25, 35, 0.15);
}

.menu-heading {
    margin: 0 0 14px;
    color: #686166;
    font-size: 0.9rem;
    font-weight: 700;
    letter-spacing: 0.025em;
    text-transform: uppercase;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 3px 20px;
}

.mega-menu :deep(.mega-menu-link) {
    display: flex;
    min-width: 0;
    min-height: 48px;
    align-items: center;
    gap: 13px;
    padding: 9px 2px;
    color: #171417;
    background: transparent;
    border-radius: 8px;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    line-height: 1.35;
    overflow-wrap: anywhere;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.mega-menu :deep(.mega-menu-link:hover),
.mega-menu :deep(.mega-menu-link.active) {
    color: #754353;
    background: #f8eff2;
}

.menu-icon {
    flex: 0 0 25px;
    width: 25px;
    height: 25px;
    color: #9b5369;
}

.menu-divider {
    height: 1px;
    margin: 20px -24px;
    background: #eee7e9;
}

/* Benutzerbereich rechts */

.navbar-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.7rem;
    margin-left: auto;
    flex-shrink: 0;
}

/* Warenkorb-Symbol auf dem dunklen Hintergrund: weiß statt dunkelrosa */
.navbar-right :deep(.cart-icon) {
    color: white;
}

.navbar-right :deep(.cart-icon:hover) {
    background: rgba(255, 255, 255, 0.15);
}

.navbar-right :deep(.cart-badge) {
    border-color: #8f5364;
    background: white;
    color: #8f5364;
}

.profile-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.8);
    border-radius: 50%;
}

.user-info {
    display: flex;
    min-width: 0;
    flex-direction: column;
}

.user-name {
    max-width: 160px;
    overflow: hidden;
    color: white;
    font-size: 0.9rem;
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-role {
    color: rgba(255, 255, 255, 0.78);
    font-size: 0.75rem;
}

.settings-button {
    display: grid;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    place-items: center;
    color: white;
    border-radius: 50%;
    text-decoration: none;
    font-size: 1.3rem;
    transition: background-color 0.2s ease, transform 0.3s ease;
}

.settings-button:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: rotate(30deg);
}

.logout-button,
.login-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.7rem 1.35rem;
    color: #8f5364;
    background: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    cursor: pointer;
    font: inherit;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.logout-button:hover,
.login-button:hover {
    color: white;
    background: #754353;
}

/* Animation */

.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.16s ease, transform 0.16s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

.navbar button:focus-visible,
.navbar :deep(a:focus-visible) {
    outline: 3px solid #f2d9e1;
    outline-offset: 3px;
}

/* Kleinere Bildschirme */

@media (max-width: 1100px) {
    .navbar-inner {
        flex-wrap: wrap;
        padding: 0.6rem 1rem;
    }

    .navbar-right {
        margin-left: auto;
    }

    .mega-menu {
        width: min(680px, calc(100vw - 24px));
    }
}

@media (max-width: 650px) {
    .navbar-inner {
        gap: 0.4rem;
    }

    .nav-links {
        width: 100%;
    }

    .nav-links :deep(a) {
        padding: 0.5rem 0.6rem;
        font-size: 0.82rem;
    }

    .navbar-right {
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .user-name {
        max-width: 130px;
    }

    .menu-wrapper {
        position: static;
    }

    .mega-menu {
        position: fixed;
        top: 120px;
        right: 12px;
        left: 12px;
        width: auto;
        max-width: none;
        max-height: calc(100dvh - 135px);
        padding: 20px 16px;
        border-radius: 0 0 18px 18px;
    }

    .menu-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 2px;
    }

    .mega-menu :deep(.mega-menu-link) {
        width: 100%;
        box-sizing: border-box;
        gap: 10px;
        padding: 11px 2px;
        font-size: 0.92rem;
    }

    .menu-icon {
        flex-basis: 22px;
        width: 22px;
        height: 22px;
    }

    .menu-divider {
        margin: 16px -16px;
    }

    .logout-button,
    .login-button {
        padding: 0.6rem 0.9rem;
    }
}
</style>