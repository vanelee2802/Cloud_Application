<template>
    <div class="save-panel">

        <!-- Kopf: Titel + Schließen -->
        <div class="header-row">

            <h3>DESIGN SPEICHERN</h3>

            <button
                type="button"
                class="close-button"
                aria-label="Schließen"
                @click="emit('close')"
            >
                ×
            </button>

        </div>

        <!-- Übersicht -->
        <div class="summary">

            <span>
                {{ nailCount ? `${nailCount} ${nailCount === 1 ? 'Nagel' : 'Nägel'} ausgewählt` : 'Alle Nägel' }}
            </span>

            <strong>{{ euro(price) }}</strong>

        </div>

        <!-- Name + Speichern -->
        <input
            ref="nameInput"
            :value="modelValue"
            type="text"
            maxlength="255"
            class="name-input"
            placeholder="Name des Designs"
            aria-label="Name des Designs"
            @input="emit('update:modelValue', $event.target.value)"
            @keydown.enter.prevent="emit('save')"
            @keydown.esc="emit('close')"
        />

        <button
            type="button"
            class="save-button"
            :disabled="saving"
            @click="emit('save')"
        >
            {{ saving ? 'Speichert …' : 'Speichern' }}
        </button>

        <p
            v-if="message"
            class="message"
            role="status"
        >
            {{ message }}
        </p>

    </div>
</template>


<script setup>

import { ref, onMounted } from 'vue'

/*
|--------------------------------------------------------------------------
| DESIGN SPEICHERN
|--------------------------------------------------------------------------
| Erscheint erst nach Klick auf "Design speichern" unter der Verzierung.
|
| Events:
|   save   → Design speichern
|   close  → Panel wieder schließen
*/

defineProps({
    modelValue: { type: String,  default: '' },     // Name des Designs
    nailCount:  { type: Number,  default: 0 },      // 0 = alle Nägel
    price:      { type: Number,  default: 0 },      // Designpreis in Euro
    saving:     { type: Boolean, default: false },
    message:    { type: String,  default: '' },
})

const emit = defineEmits(['update:modelValue', 'save', 'close'])

const nameInput = ref(null)

// Beim Öffnen direkt ins Namensfeld springen
onMounted(() => {
    nameInput.value?.focus()
    nameInput.value?.select()
})

function euro(value) {

    return Number(value ?? 0).toLocaleString('de-DE', {
        style: 'currency',
        currency: 'EUR',
    })

}

</script>


<style scoped>

.save-panel {

    display: flex;

    flex-direction: column;

    gap: 0.5rem;

    width: 100%;

    padding: 0.8rem 1rem 0.9rem;

    background: #ffffff;

    border: 1px solid #eeeeee;

    border-radius: 1rem;

    box-shadow: 0 0.3vw 1.2vw rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

}

.header-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 0.5rem;

}

.header-row h3 {

    margin: 0;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(0.8rem, 0.9vw, 1rem);

    font-weight: 400;

    letter-spacing: 0.08em;

}

.close-button {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 1.7rem;

    height: 1.7rem;

    flex-shrink: 0;

    padding: 0;

    border: none;

    border-radius: 50%;

    background: #fdf9fa;

    color: #7d1024;

    font-size: 1.2rem;

    line-height: 1;

    cursor: pointer;

}

.close-button:hover {

    background: #f3e5e8;

}

.close-button:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 1px;

}

.summary {

    display: flex;

    align-items: baseline;

    justify-content: space-between;

    gap: 0.5rem;

    color: #8a7a80;

    font-size: clamp(0.68rem, 0.75vw, 0.8rem);

}

.summary strong {

    color: #7d1024;

    font-size: clamp(0.78rem, 0.88vw, 0.92rem);

}

.name-input {

    width: 100%;

    padding: 0.45em 0.7em;

    border: 1px solid #e6dadd;

    border-radius: 0.5rem;

    background: #fbf7f8;

    color: #333333;

    font: inherit;

    font-size: clamp(0.72rem, 0.8vw, 0.85rem);

    outline: none;

    box-sizing: border-box;

}

.name-input::placeholder {

    color: #b0a2a7;

}

.name-input:focus {

    background: #ffffff;

    border-color: #7d1024;

}

.save-button {

    width: 100%;

    padding: 0.5rem 0.8rem;

    border: none;

    border-radius: 0.55rem;

    background: #7d1024;

    color: #ffffff;

    font: inherit;

    font-size: clamp(0.72rem, 0.8vw, 0.85rem);

    font-weight: 500;

    cursor: pointer;

    transition: background-color 0.2s ease;

}

.save-button:hover {

    background: #64101f;

}

.save-button:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 2px;

}

.save-button:disabled {

    opacity: 0.6;

    cursor: not-allowed;

}

.message {

    margin: 0;

    color: #555555;

    font-size: clamp(0.65rem, 0.72vw, 0.75rem);

    line-height: 1.35;

}

@media (prefers-reduced-motion: reduce) {

    .save-button {

        transition: none;

    }

}

</style>