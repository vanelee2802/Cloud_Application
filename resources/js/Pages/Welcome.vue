<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue'
import StudioSelector from '@/Components/StudioSelector.vue'

defineProps({
    studios: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <pagesLayout>
        <StudioSelector :studios="studios" />
    </pagesLayout>
</template>