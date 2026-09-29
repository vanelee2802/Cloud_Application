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
/*Anknüfung zur Datenbank*/
import { ref, onMounted } from 'vue'

const emit = defineEmits(['color-selected'])
/*color aus der Datenbank holen*/
const colors = ref([])

function selectColor(color) {
    emit('color-selected', color)
}



onMounted(async () => {
    const response = await fetch('/colors')
    colors.value = await response.json()
})

/*statische color werte*/

/*const colors = [
    {
        name: 'Nude',
        value: '#F5D0C5',
        price: 5,
        image: '/images/colors/nude.png',
    },
    {
        name: 'Rosa',
        value: '#E8A0A8',
        price: 5,
        image: '/images/colors/rosa.png',
    },
    {
        name: 'Rot',
        value: '#C94C4C',
        price: 5,
        image: '/images/colors/rot.png',
    },
    {
        name: 'Pink',
        value: '#D96C9D',
        price: 5,
        image: '/images/colors/pink.png',
    },
    {
        name: 'Weiß',
        value: '#FFFFFF',
        price: 5,
        image: '/images/colors/weiss.png',
    },
    {
        name: 'Schwarz',
        value: '#222222',
        price: 5,
        image: '/images/colors/schwarz.png',
    },
]*/

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
    flex-direction: column;
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