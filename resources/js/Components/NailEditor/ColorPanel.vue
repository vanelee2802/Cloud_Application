<template>
    <div class="color-panel">

        <h3>FARBE</h3>

        <div class="color-list">

            <button
                v-for="color in colors"
                :key="color.name"
                @click="selectColor(color)"
                class="color-button"
                :style="{ backgroundColor: color.hex_code }"
            >
            </button>

        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    colors: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['color-selected'])

function selectColor(color) {
    emit('color-selected', color)
}

const colors = computed(() => props.colors)

</script>

<style scoped>

.color-panel {
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.color-panel h3 {
    font-size: 14px;
    margin-bottom: 15px;
}

.color-list {
    display: flex;
    flex-direction: row;
    gap: 12px;
    flex-wrap: nowrap;
}

.color-button {
    flex-shrink: 0;

    width: 35px;
    height: 35px;

    border: none;
    border-radius: 50%;

    cursor: pointer;
}

</style>