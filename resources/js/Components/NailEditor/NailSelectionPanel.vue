<template>
    <div class="nail-selection-panel">

        <h3>NÄGEL AUSWÄHLEN</h3>

        <div
            v-for="nail in nails"
            :key="nail.name"
        >
            <button @click="selectNail(nail.name)">
                <span>{{ nail.name }}</span>
            </button>
        </div>

        <button
            class="left-hand-button"
            @click="selectLeftHand"
        >
            Linke Hand bearbeiten
        </button>

    </div>
</template>

<script setup>

const emit = defineEmits([
    'nail-selected',
    'left-hand-selected'
])

function selectNail(nail) {
    emit('nail-selected', nail)
}

function selectLeftHand() {
    emit('left-hand-selected')
}

const nails = [
    {
        name: 'Daumen',
    },
    {
        name: 'Zeigefinger',
    },
    {
        name: 'Mittelfinger',
    },
    {
        name: 'Ringfinger',
    },
    {
        name: 'Kleiner Finger',
    },
]

</script>

<style scoped>

.nail-selection-panel {
    width: 220px;
    padding: 20px;
    background: white;
    border-radius: 8px;
}

.nail-selection-panel h3 {
    font-size: 14px;
    margin-bottom: 15px;
}

.nail-selection-panel button {
    display: flex;
    align-items: center;

    width: 100%;
    padding: 12px;

    border: none;
    background: transparent;

    text-align: left;
    cursor: pointer;
}

.left-hand-button {
    margin-top: 15px;
    border-top: 1px solid #ddd !important;
}

</style>