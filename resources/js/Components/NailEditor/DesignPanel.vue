<template>
    <div class="design-panel">

        <h3>DESIGNS</h3>

        <div
            v-for="design in designs"
            :key="design.name"
        >
            <button @click="selectDesign(design.name)">
                <img :src="design.icon" alt="">
                <span>{{ design.name }}</span>
            </button>
        </div>

    </div>
</template>

<script setup>

const emit = defineEmits(['design-selected'])

function selectDesign(design) {
    emit('design-selected', design)
}

const designs = [
    {
        name: 'French',
        price: 2,
        icon: '/images/icons/nailart.svg',
    },
    {
        name: 'Glitter',
        price: 1,
        icon: '/images/icons/nailart.svg',
    },
    {
        name: 'Muster',
        price: 0,
        icon: '/images/icons/nailart.svg',
    },
    {
        name: 'Ombré',
        price: 2.5,
        icon: '/images/icons/nailart.svg',
    },
]

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