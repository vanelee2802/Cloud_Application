import { ref, computed } from 'vue'

const cartItems = ref([])

export function useCart() {

    function addToCart(item) {
        cartItems.value.push(item)
    }

    function removeFromCart(index) {
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