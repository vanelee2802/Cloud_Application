<template>
    <div class="booking-result">

        <template v-if="success">
            <h1>Vielen Dank!</h1>
            <p>
                Deine Zahlung ist eingegangen und dein Termin ist fest gebucht.
            </p>
            <p>
                Eine Bestätigung mit allen Details bekommst du per E-Mail.
            </p>
        </template>

        <template v-else>
            <h1>Zahlung abgebrochen</h1>
            <p>
                Dein Termin wurde nicht gebucht. Der reservierte Platz wird in Kürze wieder freigegeben.
            </p>
            <p>
                Du kannst jederzeit einen neuen Termin auswählen.
            </p>
        </template>

        <Link href="/" class="home-link">
            Zur Startseite
        </Link>

    </div>
</template>


<script setup>

import { Link } from '@inertiajs/vue3'

// Vom Server: true = Zahlung erfolgreich, false = abgebrochen
defineProps({
    success: { type: Boolean, default: false },
})

</script>


<style scoped>

.booking-result {
    max-width: 560px;
    margin: 80px auto;
    padding: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.booking-result h1 {
    margin-bottom: 20px;
}

.booking-result p {
    margin-bottom: 12px;
}

.home-link {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 24px;
    border-radius: 6px;
    background: #222;
    color: white;
    text-decoration: none;
}

</style>