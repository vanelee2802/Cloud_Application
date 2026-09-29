<template>

    <div class="verziehrung-panel">

        <h3>VERZIERUNG</h3>

      <div class="verziehrung-options">

    <div
        v-for="verziehrung in verziehrungen"
        :key="verziehrung.name"
    >
        <button @click="selectVerziehrung(verziehrung.name)">

            <img
                :src="verziehrung.icon"
                alt=""
            >

            <span>{{ verziehrung.name }}</span>

        </button>
    </div>

</div>
    </div>

</template>


<script setup>

const emit = defineEmits(['verziehrung-selected'])


function selectVerziehrung(verziehrung) {

    emit('verziehrung-selected', verziehrung)

}


const verziehrungen = [

    {
        name: 'Strasssteine',
        icon: '/images/icons/verziehrung.svg',
    },

    {
        name: 'Perlen',
        icon: '/images/icons/verziehrung.svg',
    },

    {
        name: 'Glitzersteine',
        icon: '/images/icons/verziehrung.svg',
    },

    {
        name: 'Sticker',
        icon: '/images/icons/verziehrung.svg',
    },

    {
        name: 'Metallic',
        icon: '/images/icons/verziehrung.svg',
    },

]

</script>


<style scoped>

.verziehrung-panel {
    width: 440px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.verziehrung-panel h3 {
    font-size: 14px;
    margin-bottom: 15px;
}

.verziehrung-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px 15px;
}

.verziehrung-options button {
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

.verziehrung-options button img {
    width: 20px;
    height: 20px;
}
</style>