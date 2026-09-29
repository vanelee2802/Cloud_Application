<template>
    <div class="category-panel">

        <h3>KATEGORIEN</h3>

        <div
            v-for="category in categories"
            :key="category.name"
        >
            <button @click="selectCategory(category.name)">
                <img :src="category.icon" alt="">
                <span>{{ category.name }}</span>
            </button>
        </div>

    </div>
</template>

<script setup>

const emit = defineEmits(['category-selected'])

function selectCategory(category) {
    emit('category-selected', category)
}

const categories = [
    {
        name: 'Form',
        icon: '/images/icons/color.svg',
    },
    {
        name: 'Farbe',
        icon: '/images/icons/color.svg',
    },
    {
        name: 'Designs',
        icon: '/images/icons/nailart.svg',
    },
    {
        name: 'Effekte',
        icon: '/images/icons/effekt.svg',
    },
    {
        name: 'Verzierung',
        icon: '/images/icons/sticker.svg',
    },
]
</script>

<style scoped>

.category-panel {
    width: 220px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.category-panel h3 {
    font-size: 14px;
    margin-bottom: 15px;
}

.category-panel button {
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

.category-panel button img {
    width: 20px;
    height: 20px;
}

</style>