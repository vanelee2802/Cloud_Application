<template>
    <div style="max-width: 700px; margin: 40px auto; font-family: monospace;">
        <h1>Terminseite (Entwurf)</h1>
        <pre>{{ JSON.stringify($props, null, 2) }}</pre>
    </div>
</template>

<script setup>
defineProps({
    appointment: Object,
    payUrl: String,
    alternatives: Array,
})
</script>