<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    studios: {
        type: Array,
        default: () => [],
    },
})

const searchTerm = ref('')
const selectedStudio = ref(null)

const studios = computed(() => props.studios)

const filteredStudios = computed(() => {
    const search = searchTerm.value.toLowerCase().trim()

    if (!search) {
        return studios.value
    }

    return studios.value.filter(studio =>
        studio.name.toLowerCase().includes(search) ||
        studio.address.toLowerCase().includes(search)
    )
})

</script>

<template>
    <section class="studio-selector">
        <div class="studio-container">

            <h2>Wähle dein Nagelstudio</h2>

            <div class="search-container">
                <input
                    v-model="searchTerm"
                    type="text"
                    placeholder="Studio, Ort oder PLZ suchen..."
                />
            </div>

            <div class="studio-content">
                <div
                    v-for="studio in filteredStudios"
                    :key="studio.id"
                    class="studio-card"
                >
                    <h3>{{ studio.name }}</h3>

                        <p>📍 {{ studio.address }}</p>
                        <p>🕐 {{ studio.opening_hours }}</p>

                        <button @click="selectedStudio = studio">
                        Studio auswählen
                        </button>
                </div>
            </div>

            <p v-if="selectedStudio">
                Ausgewählt: {{ selectedStudio.name }}
            </p>

        </div>
    </section>
</template>

<style scoped>
.studio-selector {
    width: 100%;
    padding: 3rem 2rem;
    box-sizing: border-box;
}

.studio-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.studio-selector h2 {
    margin-bottom: 1.5rem;
    text-align: center;
}

.studio-content {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;

    display: grid;
    grid-template-columns: repeat(2, 350px);
    justify-content: center;
    gap: 1.5rem;

    overflow-y: auto;
    max-height: 600px;

    padding: 0.5rem;
    box-sizing: border-box;
}
.studio-card {
    width: 350px;
    min-height: 230px;
    padding: 1.5rem;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: white;
    box-sizing: border-box;
}

.studio-card h3 {
    margin-bottom: 1rem;
}

.studio-card p {
    margin: 0.5rem 0;
}

.studio-card button {
    margin-top: 1rem;
    padding: 0.7rem 1.2rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.search-container {
    max-width: 600px;
    margin: 0 auto 2rem;
}

.search-container input {
    width: 100%;
    padding: 0.9rem 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
}

.search-container input:focus {
    outline: none;
    border-color: #999;
}
</style>