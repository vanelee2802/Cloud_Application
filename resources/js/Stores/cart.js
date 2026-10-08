import { ref, computed } from 'vue'

const cartItems = ref([])

async function loadCart() {
    const response = await fetch('/cart-items')

    if (!response.ok) {
        throw new Error('Warenkorb konnte nicht geladen werden.')
    }

    const items = await response.json()

    cartItems.value = items.map(item => ({
        id: item.id,
        name: item.design?.name ?? 'Nageldesign',
        price: Number(item.price),
        image: '/images/nail-editor/Hand.png',
    }))
}

export function useCart() {

    loadCart()

    function addToCart(item) {
        cartItems.value.push(item)
    }

    async function removeFromCart(index) {
        const item = cartItems.value[index]

    if (!item) {
        return
    }

    const response = await fetch(`/cart-items/${item.id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content'),
            'Accept': 'application/json',
        },
    })

    if (!response.ok) {
        throw new Error('Warenkorb-Eintrag konnte nicht gelöscht werden.')
    }

    cartItems.value.splice(index, 1)
    }

    const total = computed(() => {
        return cartItems.value.reduce(
            (sum, item) => sum + item.price,
            0
        )
    })

    return {
        cartItems,
        addToCart,
        removeFromCart,
        total
    }
}