<!--
|--------------------------------------------------------------------------
| PATTERN SETTINGS – Schieberegler für generierte Muster
|--------------------------------------------------------------------------
|
| Zeigt für ein generiertes Muster (french, streifen, punkte, karo)
| die passenden Regler an. Welche Regler es gibt, steht in
| patternGenerators.js – hier muss nichts angepasst werden, wenn dort
| ein neuer Generator dazukommt.
|
| Benutzung:
|
|   <PatternSettings
|       v-model="params"
|       generator="french"
|       :defaults="{ tiefe: 26, bogen: 13 }"
|       @close="showSettings = false"
|   />
|
| v-model    = aktuelle Einstellungen, z. B. { tiefe: 30, bogen: 10, schraeg: 0 }
| generator  = Art des Musters
| defaults   = Startwerte aus der Datenbank (für "Zurücksetzen")
-->

<template>
    <div
        v-if="generatorDef"
        class="pattern-settings"
        role="dialog"
        :aria-label="`${generatorDef.label} anpassen`"
        @keydown.esc="emit('close')"
    >

        <!-- Kopfzeile -->
        <div class="settings-header">

            <h4>
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0" />
                    <circle cx="16" cy="6" r="2" />
                    <circle cx="10" cy="12" r="2" />
                    <circle cx="18" cy="18" r="2" />
                </svg>
                {{ generatorDef.label }} anpassen
            </h4>

            <button
                type="button"
                class="close-button"
                aria-label="Einstellungen schließen"
                @click="emit('close')"
            >
                ×
            </button>

        </div>


        <!-- Regler -->
        <div class="settings-body">

            <label
                v-for="param in generatorDef.params"
                :key="param.key"
                class="slider-row"
            >

                <span class="slider-label">
                    {{ param.label }}
                    <output>{{ formatValue(param) }}</output>
                </span>

                <input
                    type="range"
                    :min="param.min"
                    :max="param.max"
                    :step="param.step"
                    :value="currentValue(param)"
                    :style="{ '--fill': fillPercent(param) }"
                    @input="setValue(param.key, $event.target.value)"
                >

            </label>

        </div>


        <!-- Fußzeile -->
        <div class="settings-footer">

            <button
                type="button"
                class="text-button"
                :disabled="!isChanged"
                @click="reset"
            >
                Zurücksetzen
            </button>

            <button
                type="button"
                class="done-button"
                @click="emit('close')"
            >
                Fertig
            </button>

        </div>

    </div>
</template>


<script setup>

import { computed } from 'vue'
import { GENERATORS, defaultParams } from './patternGenerators'

const props = defineProps({
    generator:  { type: String, required: true },
    modelValue: { type: Object, default: () => ({}) },
    defaults:   { type: Object, default: () => ({}) },
})

const emit = defineEmits([
    'update:modelValue',
    'close',
])


// Beschreibung des Generators (Regler, Name, …)
const generatorDef = computed(() => GENERATORS[props.generator] ?? null)

// Startwerte: Generator-Standard, überschrieben mit den Werten aus der Datenbank
const startValues = computed(() => ({
    ...defaultParams(props.generator),
    ...props.defaults,
}))


function currentValue(param) {

    return props.modelValue[param.key] ?? startValues.value[param.key] ?? param.default

}

function setValue(key, value) {

    emit('update:modelValue', {
        ...startValues.value,
        ...props.modelValue,
        [key]: Number(value),
    })

}

function reset() {

    emit('update:modelValue', { ...startValues.value })

}

// Wurde etwas gegenüber den Startwerten geändert?
const isChanged = computed(() =>
    (generatorDef.value?.params ?? []).some(
        (param) => currentValue(param) !== startValues.value[param.key]
    )
)


// Anzeige neben dem Regler, z. B. "26 %" oder "45°"
function formatValue(param) {

    const value = currentValue(param)

    if (!param.unit) return value
    if (param.unit === '°') return `${value}°`

    return `${value} ${param.unit}`

}

// Wie weit der Regler gefüllt ist (für die farbige Spur)
function fillPercent(param) {

    const value = currentValue(param)

    return `${((value - param.min) / (param.max - param.min)) * 100}%`

}

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| KARTE
|--------------------------------------------------------------------------
*/

.pattern-settings {

    --bordeaux: #7d1024;
    --soft: #f8f0f2;
    --line: #eee4e7;

    width: min(19rem, calc(100vw - 2rem));

    background: #ffffff;

    border: 1px solid var(--line);

    border-radius: 0.9rem;

    box-shadow:
        0 12px 32px rgba(80, 35, 48, 0.14),
        0 2px 6px rgba(80, 35, 48, 0.06);

    box-sizing: border-box;

    overflow: hidden;

}


/*
|--------------------------------------------------------------------------
| KOPF
|--------------------------------------------------------------------------
*/

.settings-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0.75rem 0.9rem 0.6rem;

    border-bottom: 1px solid var(--line);

}

.settings-header h4 {

    display: flex;

    align-items: center;

    gap: 0.45rem;

    margin: 0;

    color: var(--bordeaux);

    font-family: Georgia, serif;

    font-size: 0.95rem;

    font-weight: 400;

    letter-spacing: 0.04em;

}

.settings-header svg {

    width: 16px;

    height: 16px;

    fill: none;

    stroke: currentColor;

    stroke-width: 1.8;

    stroke-linecap: round;

}

.close-button {

    width: 1.8rem;

    height: 1.8rem;

    padding: 0;

    border: none;

    border-radius: 50%;

    background: transparent;

    color: #a8999d;

    font-size: 1.2rem;

    line-height: 1;

    cursor: pointer;

}

.close-button:hover {

    background: var(--soft);

    color: var(--bordeaux);

}


/*
|--------------------------------------------------------------------------
| REGLER
|--------------------------------------------------------------------------
*/

.settings-body {

    display: flex;

    flex-direction: column;

    gap: 0.85rem;

    padding: 0.85rem 0.9rem;

}

.slider-row {

    display: flex;

    flex-direction: column;

    gap: 0.35rem;

}

.slider-label {

    display: flex;

    align-items: baseline;

    justify-content: space-between;

    color: #4f3540;

    font-size: 0.78rem;

}

.slider-label output {

    min-width: 3rem;

    color: var(--bordeaux);

    font-size: 0.75rem;

    font-weight: 600;

    font-variant-numeric: tabular-nums;

    text-align: right;

}


/* Schieberegler: dünne Spur, gefüllter Teil in Bordeaux */
input[type="range"] {

    width: 100%;

    height: 1.2rem;

    margin: 0;

    background: transparent;

    cursor: pointer;

    -webkit-appearance: none;

    appearance: none;

}

input[type="range"]::-webkit-slider-runnable-track {

    height: 4px;

    border-radius: 4px;

    background: linear-gradient(
        to right,
        var(--bordeaux) 0,
        var(--bordeaux) var(--fill),
        var(--line) var(--fill)
    );

}

input[type="range"]::-moz-range-track {

    height: 4px;

    border-radius: 4px;

    background: var(--line);

}

input[type="range"]::-moz-range-progress {

    height: 4px;

    border-radius: 4px;

    background: var(--bordeaux);

}

input[type="range"]::-webkit-slider-thumb {

    width: 16px;

    height: 16px;

    margin-top: -6px;

    border: 2px solid var(--bordeaux);

    border-radius: 50%;

    background: #ffffff;

    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);

    -webkit-appearance: none;

    appearance: none;

    transition: transform 0.15s ease;

}

input[type="range"]::-moz-range-thumb {

    width: 12px;

    height: 12px;

    border: 2px solid var(--bordeaux);

    border-radius: 50%;

    background: #ffffff;

}

input[type="range"]:hover::-webkit-slider-thumb {

    transform: scale(1.15);

}

input[type="range"]:focus-visible {

    outline: 2px solid var(--bordeaux);

    outline-offset: 2px;

    border-radius: 4px;

}


/*
|--------------------------------------------------------------------------
| FUSS
|--------------------------------------------------------------------------
*/

.settings-footer {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0.6rem 0.9rem 0.8rem;

    border-top: 1px solid var(--line);

}

.text-button {

    padding: 0;

    border: none;

    background: none;

    color: var(--bordeaux);

    font: inherit;

    font-size: 0.78rem;

    text-decoration: underline;

    text-underline-offset: 2px;

    cursor: pointer;

}

.text-button:disabled {

    color: #c4b6ba;

    text-decoration: none;

    cursor: default;

}

.done-button {

    padding: 0.4rem 0.9rem;

    border: none;

    border-radius: 0.5rem;

    background: var(--bordeaux);

    color: #ffffff;

    font: inherit;

    font-size: 0.78rem;

    font-weight: 600;

    cursor: pointer;

}

.done-button:hover {

    background: #64101f;

}


@media (prefers-reduced-motion: reduce) {

    input[type="range"]::-webkit-slider-thumb {

        transition: none;

    }

}

</style>