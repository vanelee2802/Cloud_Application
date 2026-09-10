```vue
<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const nailShapes = page.props.nailShapes
const colors = page.props.colors

const selectedNail = ref(1)

const nails = [1, 2, 3, 4, 5]

const nailColors = ref({
    1: '#f5d0d8',
    2: '#f5d0d8',
    3: '#f5d0d8',
    4: '#f5d0d8',
    5: '#f5d0d8',

})

const nailShapesPerNail = ref({
    1: null,
    2: null,
    3: null,
    4: null,
    5: null,
})

function selectNail(nail) {
    selectedNail.value = nail
}

function selectColor(color) {
    nailColors.value = {
        ...nailColors.value,
        [selectedNail.value]: color.hex_code,
    }
}

function selectShape(shape) {
    nailShapesPerNail.value = {
        ...nailShapesPerNail.value,
        [selectedNail.value]: shape.id,
    }
}

function getShapeClass(nail) {
    const shapeId = nailShapesPerNail.value[nail]

    const shape = nailShapes.find(shape => shape.id === shapeId)

    return shape ? shape.name.toLowerCase() : 'default'
}
</script>

<template>
    <div class="editor">
        <h1>Nail Design Editor</h1>

        <div class="editor-content">

            <!-- Linke Seite: Nägel -->
            <div class="hand">
                <h2>Deine Nägel</h2>

                <div class="nails">
                    <button
                        v-for="nail in nails"
                        :key="nail"
                        @click="selectNail(nail)"
                        :class="[
                            'nail',
                            getShapeClass(nail),
                            { selected: selectedNail === nail }
                        ]"
                        :style="{ backgroundColor: nailColors[nail] }"
                    >
                        <span>💅</span>
                        <small>{{ nail }}</small>
                    </button>
                </div>
            </div>

            <!-- Rechte Seite: Einstellungen -->
            <div class="settings">
                <h2>Nagel {{ selectedNail }}</h2>

                <p>
                    Dieser Nagel ist aktuell ausgewählt.
                </p>

                <h3>Nagelformen aus der Datenbank:</h3>

                <div class="shapes">
                        <button
                            v-for="shape in nailShapes"
                            :key="shape.id"
                            class="shape-button"
                            :class="{
                                selected: nailShapesPerNail[selectedNail] === shape.id
                            }"
                            @click="selectShape(shape)"
                        >
                            {{ shape.name }}
                        </button>
                </div>

                <h3>Farben:</h3>

                    <div class="colors">
                        <button
                            v-for="color in colors"
                            :key="color.id"
                            class="color-button"
                            @click="selectColor(color)"
                        >
                            <span
                                class="color-preview"
                                :style="{ backgroundColor: color.hex_code }"
                            ></span>

                            {{ color.name }}
                        </button>
                    </div>

                <p>
                    Hier werden später die Einstellungen
                    für Form, Farbe und Design angezeigt.
                </p>
            </div>

        </div>
    </div>
</template>

<style scoped>
.editor {
    padding: 40px;
    max-width: 1000px;
    margin: 0 auto;
}

.editor h1 {
    margin-bottom: 30px;
}

.editor-content {
    display: flex;
    gap: 60px;
}

.hand {
    flex: 1;
}

.settings {
    flex: 1;
    padding: 30px;
    border: 1px solid #ddd;
    border-radius: 10px;
}

.nails {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.nail {
    width: 80px;
    height: 120px;
    border: 2px solid #ddd;
    border-radius: 40px 40px 25px 25px;
    cursor: pointer;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    font-size: 30px;
}

.nail.default {
    border-radius: 40px 40px 25px 25px;
}

.nail.almond {
    border-radius: 50% 50% 30% 30%;
    transform: scaleX(0.85);
}

.nail.square {
    border-radius: 10px;
}

.nail.coffin {
    border-radius: 20px 20px 5px 5px;
    transform: scaleX(0.9);
}

.nail.round {
    border-radius: 40px;
}

.nail.stiletto {
    border-radius: 50% 50% 5px 5px;
    clip-path: polygon(25% 0, 75% 0, 100% 100%, 0 100%);
}

.nail small {
    font-size: 14px;
    margin-top: 10px;
}

.nail.selected {
    border: 4px solid #000;
}

.colors {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.color-button {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    text-align: left;
}

.color-preview {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid #999;
}

.shapes {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.shape-button {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: white;
    cursor: pointer;
}

.shape-button.selected {
    border: 2px solid #000;
    font-weight: bold;
}
</style>
```
