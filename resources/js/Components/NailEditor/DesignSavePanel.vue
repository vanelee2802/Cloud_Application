<template>
    <div class="save-panel">

        <!-- ========================================= -->
        <!-- ÜBERSCHRIFT                               -->
        <!-- ========================================= -->

        <h3>MEIN DESIGN</h3>


        <!-- ========================================= -->
        <!-- ÜBERSICHT                                 -->
        <!-- ========================================= -->

        <div class="summary">

            <div class="summary-row">
                <span class="summary-label">Ausgewählte Nägel</span>
                <span class="summary-value">{{ nailCount || 'Alle' }}</span>
            </div>

            <div class="summary-row">
                <span class="summary-label">Designpreis</span>
                <span class="summary-value price">{{ euro(price) }}</span>
            </div>

        </div>


        <!-- ========================================= -->
        <!-- NAME + SPEICHERN                          -->
        <!-- ========================================= -->

        <label
            for="design-name"
            class="field-label"
        >
            Name des Designs
        </label>

        <input
            id="design-name"
            :value="modelValue"
            type="text"
            maxlength="255"
            class="name-input"
            placeholder="z. B. Herbst French"
            @input="emit('update:modelValue', $event.target.value)"
            @keydown.enter.prevent="emit('save')"
        />

        <button
            type="button"
            class="save-button"
            :disabled="saving"
            @click="emit('save')"
        >
            {{ saving ? 'Speichert …' : 'Design speichern' }}
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

/*
|--------------------------------------------------------------------------
| DESIGN SPEICHERN
|--------------------------------------------------------------------------
| Zeigt Anzahl der Nägel + Preis und speichert das Design.
| Die eigentliche Speicher-Logik liegt im DesignEditor (saveDesign).
|
| Nutzung:
|   <DesignSavePanel
|       v-model="designName"
|       :nail-count="selectedFingers.length"
|       :price="totalDesignPrice"
|       :saving="isSaving"
|       :message="saveMessage"
|       @save="saveDesign"
|   />
*/

defineProps({
    modelValue: { type: String,  default: '' },     // Name des Designs
    nailCount:  { type: Number,  default: 0 },      // 0 = alle Nägel
    price:      { type: Number,  default: 0 },      // Designpreis in Euro
    saving:     { type: Boolean, default: false },
    message:    { type: String,  default: '' },
})

const emit = defineEmits(['update:modelValue', 'save'])

function euro(value) {

    return Number(value ?? 0).toLocaleString('de-DE', {
        style: 'currency',
        currency: 'EUR',
    })

}

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| PANEL (gleicher Stil wie Farbe, Designs, Verzierung)
|--------------------------------------------------------------------------
*/

.save-panel {

    display: flex;

    flex-direction: column;

    gap: 0.55rem;

    width: 100%;

    padding: 0.9rem 1.2rem 1.1rem;

    background: #ffffff;

    border: 1px solid #eeeeee;

    border-radius: 1rem;

    box-shadow: 0 0.3vw 1.2vw rgba(0, 0, 0, 0.06);

    box-sizing: border-box;

}

.save-panel h3 {

    margin: 0 0 0.2rem;

    color: #7d1024;

    font-family: Georgia, serif;

    font-size: clamp(0.85rem, 1vw, 1.1rem);

    font-weight: 400;

    letter-spacing: 0.08em;

}


/*
|--------------------------------------------------------------------------
| ÜBERSICHT
|--------------------------------------------------------------------------
*/

.summary {

    display: flex;

    flex-direction: column;

    gap: 0.3rem;

    padding-bottom: 0.6rem;

    border-bottom: 1px solid #f0e6e9;

}

.summary-row {

    display: flex;

    align-items: baseline;

    justify-content: space-between;

    gap: 0.5rem;

}

.summary-label {

    color: #8a7a80;

    font-size: clamp(0.68rem, 0.75vw, 0.8rem);

}

.summary-value {

    color: #333333;

    font-size: clamp(0.75rem, 0.85vw, 0.9rem);

    font-weight: 600;

}

.summary-value.price {

    color: #7d1024;

}


/*
|--------------------------------------------------------------------------
| NAME + BUTTON
|--------------------------------------------------------------------------
*/

.field-label {

    margin-top: 0.2rem;

    color: #8a7a80;

    font-size: clamp(0.68rem, 0.75vw, 0.8rem);

}

.name-input {

    width: 100%;

    padding: 0.5em 0.8em;

    border: 1px solid #e6dadd;

    border-radius: 0.5rem;

    background: #fbf7f8;

    color: #333333;

    font: inherit;

    font-size: clamp(0.75rem, 0.85vw, 0.9rem);

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

    padding: 0.6rem 1rem;

    border: none;

    border-radius: 0.6rem;

    background: #7d1024;

    color: #ffffff;

    font: inherit;

    font-size: clamp(0.75rem, 0.85vw, 0.9rem);

    font-weight: 500;

    letter-spacing: 0.03em;

    cursor: pointer;

    transition:
        background-color 0.2s ease,
        transform 0.2s ease;

}

.save-button:hover {

    background: #64101f;

    transform: translateY(-0.1rem);

}

.save-button:focus-visible {

    outline: 2px solid #7d1024;

    outline-offset: 2px;

}

.save-button:disabled {

    opacity: 0.6;

    cursor: not-allowed;

    transform: none;

}

.message {

    margin: 0;

    color: #555555;

    font-size: clamp(0.68rem, 0.75vw, 0.8rem);

    line-height: 1.4;

}


/*
|--------------------------------------------------------------------------
| REDUCED MOTION
|--------------------------------------------------------------------------
*/

@media (prefers-reduced-motion: reduce) {

    .save-button {

        transition: none;

    }

    .save-button:hover {

        transform: none;

    }

}

</style>