
<script setup>
const currentYear = new Date().getFullYear()

const company = {
  name: 'Nagelstudio MixMatch',
  description: 'Nageldesign und Nailart für kreative Hände. Wir bieten individuelle Designs, hochwertige Produkte und professionelle Beratung.',
  email: 'nails@test.de',
}

const legalLinks = [
  { label: 'Impressum', href: '/impressum' },
  { label: 'Datenschutz', href: '/datenschutz' },
  { label: 'AGB', href: '/AGB' },
]

const navigationLinks = [
  { label: 'Startseite', href: '/' },
  { label: 'Leistungen', href: 'Leistungen' },
  { label: 'Kontakt', href: '/kontakt' },
]
</script>

<template>
  <footer class="site-footer">
    <div class="footer-container">
      <!-- Unternehmensinformationen -->
      <div class="footer-brand">
        <a href="/" class="footer-logo">
          {{ company.name }}
        </a>

        <p>{{ company.description }}</p>

        <a :href="`mailto:${company.email}`">
          {{ company.email }}
        </a>
      </div>

      <!-- Navigation -->
      <nav class="footer-nav" aria-label="Footer Navigation">
        <h2>Navigation</h2>

        <ul>
          <li v-for="link in navigationLinks" :key="link.href">
            <a :href="link.href">{{ link.label }}</a>
          </li>
        </ul>
      </nav>

      <!-- Rechtliches -->
      <nav class="footer-legal" aria-label="Rechtliche Informationen">
        <h2>Rechtliches</h2>

        <ul>
          <li v-for="link in legalLinks" :key="link.href">
            <a :href="link.href">{{ link.label }}</a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom">
      <p>
        &copy; {{ currentYear }} {{ company.name }}.
        Alle Rechte vorbehalten.
      </p>
    </div>
  </footer>
</template>


<style scoped>
.site-footer {
    background: #fffafb;
    color: #3d2930;

    margin-top: 3rem;
    border-top: 1px solid #f0e3e7;

    box-shadow: 0 -4px 20px rgba(80, 35, 48, 0.04);
}

/* =========================
   Hauptbereich
   ========================= */

.footer-container {
    width: min(1200px, calc(100% - 3rem));
    margin: 0 auto;
    padding: 2.75rem 0;

    display: grid;
    grid-template-columns: 1.6fr 1fr 1fr;
    gap: 3rem;
}

/* =========================
   Studio / Logo
   ========================= */

.footer-logo {
    display: inline-block;

    color: #8f5364;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.45rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    font-weight: 500;

    text-decoration: none;
    transition: color 0.2s ease;
}

.footer-brand p {
    max-width: 320px;
    margin: 0.9rem 0 1rem;

    color: #8b7b80;
    font-size: 0.92rem;
    line-height: 1.8;
}

/* =========================
   Überschriften
   ========================= */

.footer-container h2 {
    margin: 0 0 1.2rem;

    color: #8f5364;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

/* =========================
   Listen
   ========================= */

.footer-container ul {
    list-style: none;
    padding: 0;
    margin: 0;

    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

/* =========================
   Links
   ========================= */

.footer-brand > a:not(.footer-logo),
.footer-nav a,
.footer-legal a {
    color: #746269;
    font-size: 0.92rem;
    text-decoration: none;

    transition:
        color 0.2s ease,
        padding-left 0.2s ease;
}

.footer-brand > a:not(.footer-logo):hover,
.footer-nav a:hover,
.footer-legal a:hover {
    color: #8f5364;
    text-decoration: none;
    padding-left: 3px;
}

.footer-logo:hover {
    color: #754353;
}

/* =========================
   Copyright-Bereich
   ========================= */

.footer-bottom {
    width: min(1200px, calc(100% - 3rem));
    margin: 0 auto;
    padding: 1.25rem 0;

    border-top: 1px solid #f0e3e7;

    color: #9b8b90;
    font-size: 0.82rem;
    text-align: center;
}

.footer-bottom p {
    margin: 0;
}

/* =========================
   Responsive Design
   ========================= */

@media (max-width: 700px) {
    .footer-container {
        width: calc(100% - 2.5rem);
        grid-template-columns: 1fr 1fr;
        gap: 2rem 1.25rem;
        padding: 2.25rem 0;
    }

    .footer-brand {
        grid-column: 1 / -1;
    }

    .footer-bottom {
        width: calc(100% - 2.5rem);
        line-height: 1.7;
    }
}

@media (max-width: 380px) {
    .footer-container {
        grid-template-columns: 1fr;
    }

    .footer-brand {
        grid-column: auto;
    }
}
</style>

