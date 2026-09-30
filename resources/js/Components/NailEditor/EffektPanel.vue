<template>
    <div class="effekt-panel">

        <h3>EFFEKT</h3>

        <div
            v-for="effekt in effekte"
            :key="effekt.name"
        >
            <button @click="selectEffekt(effekt.name)">
                <img :src="effekt.icon" alt="">
                <span>{{ effekt.name }}</span>
            </button>
        </div>

    </div>
</template>

<script setup>

const emit = defineEmits(['effekt-selected'])

function selectEffekt(effekt) {
    emit('effekt-selected', effekt)
}

const effekte = [
    {
        name: 'Chrome',
        icon: '/images/icons/effekt.svg',
    },
    {
        name: 'Matt',
        icon: '/images/icons/effekt.svg',
    },
    {
        name: 'Glänzend',
        icon: '/images/icons/effekt.svg',
    },
]

</script>

<style scoped>

.effekt-panel {
    width: 220px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.effekt-panel h3 {
    font-size: 14px;
    margin-bottom: 15px;
}

.effekt-panel button {
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

.effekt-panel button img {
    width: 20px;
    height: 20px;
}

</style>