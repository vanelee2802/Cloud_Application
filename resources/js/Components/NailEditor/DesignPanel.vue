<template>
    <div class="design-panel">

        <h3>DESIGNS</h3>

        <div
            v-for="design in designs"
            :key="design.name"
        >
            <button @click="selectDesign(design)">
                <img :src="design.icon" alt="">
                <span>{{ design.name }}</span>
            </button>
        </div>

    </div>
</template>

<script setup>

import { computed } from 'vue'

const props = defineProps({
    designElements: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['design-selected'])

function selectDesign(design) {
    emit('design-selected', design)
}

const designs = computed(() => props.designElements.map(design => ({
    ...design,
    price: design.price_per_nail,
    icon: '/images/icons/nailart.png',
})))

</script>

<style scoped>

.design-panel {
    width: 220px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.design-panel h3 {
    font-size: 14px;
    margin-bottom: 15px;
}

.design-panel button {
    display: flex;
    align-items: center;
    gap: 12px;

    width: 100%;
    padding: 12px;

    border: none;
    background: transparent;

    text-align: left;
    cursor: pointer;
}

.design-panel button img {
    width: 20px;
    height: 20px;
}

</style>