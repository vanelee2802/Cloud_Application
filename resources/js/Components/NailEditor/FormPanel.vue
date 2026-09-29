<template>
    <div class="form-panel">

        <h3>FORM</h3>

        <div
            v-for="form in forms"
            :key="form.name"
        >
            <button @click="selectForm(form.name)">
                <img :src="form.icon" alt="">
                <span>{{ form.name }}</span>
            </button>
        </div>

    </div>
</template>

<script setup>

const emit = defineEmits(['form-selected'])

function selectForm(form) {
    emit('form-selected', form)
}

const forms = [
    {
        name: 'Almond',
        icon: '/images/icons/color.svg',
    },
    {
        name: 'Square',
        icon: '/images/icons/color.svg',
    },
    {
        name: 'Coffin',
        icon: '/images/icons/color.svg',
    },
    {
        name: 'Round',
        icon: '/images/icons/color.svg',
    },
]

</script>

<style scoped>

.form-panel {
    width: 220px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.form-panel h3 {
    font-size: 14px;
    margin-bottom: 15px;
}

.form-panel button {
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

.form-panel button img {
    width: 20px;
    height: 20px;
}

</style>