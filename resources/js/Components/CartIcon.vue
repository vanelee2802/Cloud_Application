<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useCart } from '@/Stores/cart'

// Mit href → Link zu einer Seite. Ohne href → Button, der "open" auslöst (z. B. Checkout-Fenster öffnen)
const props = defineProps({
    href: {
        type: String,
        default: null,
    },
})

const emit = defineEmits(['open'])

const { count } = useCart()

const badgeText = computed(() => (count.value > 9 ? '9+' : String(count.value)))

const label = computed(() => {
    if (count.value === 0) {
        return 'Warenkorb, leer'
    }

    return count.value === 1
        ? 'Warenkorb, 1 Position'
        : `Warenkorb, ${count.value} Positionen`
})
</script>

<template>
    <component
        :is="props.href ? Link : 'button'"
        :href="props.href ?? undefined"
        :type="props.href ? undefined : 'button'"
        class="cart-icon"
        :aria-label="label"
        :title="label"
        @click="!props.href && emit('open')"
    >
        <svg
            viewBox="0 0 24 24"
            width="22"
            height="22"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M6 7h12l-1.2 11.2a2 2 0 0 1-2 1.8H9.2a2 2 0 0 1-2-1.8L6 7Z" />
            <path d="M9 7V6a3 3 0 0 1 6 0v1" />
        </svg>

        <span v-if="count > 0" class="cart-badge" aria-hidden="true">
            {{ badgeText }}
        </span>
    </component>
</template>

<style scoped>
.cart-icon {
    position: relative;
    display: inline-flex;
    flex: 0 0 40px;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #754353;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.cart-icon:hover {
    background-color: #fff0f3;
}

.cart-icon:focus-visible {
    outline: 2px solid #8f5364;
    outline-offset: 2px;
}

.cart-badge {
    position: absolute;
    top: 2px;
    right: 0;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border: 2px solid #ffffff;
    border-radius: 999px;
    background: #8f5364;
    color: #ffffff;
    font-size: 0.68rem;
    font-weight: 700;
    line-height: 14px;
    text-align: center;
    box-sizing: border-box;
}
</style>