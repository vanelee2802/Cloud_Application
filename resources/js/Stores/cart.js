import { ref, computed, watch } from 'vue'

/**
 * Gemeinsamer Warenkorb für Leistungen und Designs.
 *
 * - Ein einziger Zustand für die ganze App (Modul-Ebene), egal wie oft useCart() aufgerufen wird.
 * - Wird in localStorage gespeichert und übersteht damit das Neuladen.
 * - Bleibt kompatibel zur alten API: addToCart(item), removeFromCart(index), cartItems, total.
 *
 * Aufbau einer Position:
 *   {
 *     id: string,               // wird automatisch vergeben
 *     type: 'service' | 'design',
 *     name: string,
 *     price: number,            // in Euro, nur zur Anzeige – der Server rechnet beim Checkout neu
 *     serviceItemId?: string,   // bei Designs: zu welcher Leistung im Warenkorb es gehört
 *     ...beliebige weitere Felder (serviceId, studioId, nails, designData …)
 *   }
 */

export const CART_STORAGE_KEY = 'mixmatch_cart_v1'

function isBrowser() {
    return typeof window !== 'undefined' && typeof window.localStorage !== 'undefined'
}

function isValidItem(item) {
    return item !== null
        && typeof item === 'object'
        && typeof item.id === 'string'
        && (item.type === 'service' || item.type === 'design')
}

function loadCart() {
    if (!isBrowser()) {
        return []
    }

    try {
        const raw = window.localStorage.getItem(CART_STORAGE_KEY)
        const parsed = raw ? JSON.parse(raw) : []

        return Array.isArray(parsed) ? parsed.filter(isValidItem) : []
    } catch {
        // Kaputte Daten im Speicher sollen die Seite nicht lahmlegen
        return []
    }
}

function saveCart(items) {
    if (!isBrowser()) {
        return
    }

    try {
        window.localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items))
    } catch {
        // z. B. privater Modus oder Speicher voll – Warenkorb funktioniert dann nur bis zum Neuladen
    }
}

function createId() {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID()
    }

    // Fallback, falls die Seite nicht über HTTPS läuft (randomUUID gibt es nur in sicheren Kontexten)
    return `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 10)}`
}

function toPrice(value) {
    const number = Number(value)

    return Number.isFinite(number) && number > 0 ? number : 0
}

// ---- Gemeinsamer Zustand ----

const cartItems = ref(loadCart())

// flush: 'sync' → sofort speichern, auch wenn direkt danach zum Checkout weitergeleitet wird
watch(cartItems, (items) => saveCart(items), { deep: true, flush: 'sync' })

// Änderungen aus anderen Tabs übernehmen
if (isBrowser()) {
    window.addEventListener('storage', (event) => {
        if (event.key === CART_STORAGE_KEY) {
            cartItems.value = loadCart()
        }
    })
}

// ---- Öffentliche API ----

export function useCart() {

    /**
     * Fügt eine Position hinzu und gibt ihre id zurück.
     * Ohne type wird 'design' angenommen (so hat der DesignEditor bisher hinzugefügt).
     */
    function addToCart(item) {
        const newItem = {
            ...item,
            id: typeof item.id === 'string' ? item.id : createId(),
            type: item.type === 'service' ? 'service' : 'design',
            price: toPrice(item.price),
            addedAt: item.addedAt ?? new Date().toISOString(),
        }

        cartItems.value.push(newItem)

        return newItem.id
    }

    /**
     * Entfernt eine Position – per Index (alte API) oder per id.
     * Wird eine Leistung entfernt, fliegen die zugehörigen Designs mit raus.
     */
    function removeFromCart(indexOrId) {
        const index = typeof indexOrId === 'number'
            ? indexOrId
            : cartItems.value.findIndex((item) => item.id === indexOrId)

        if (index < 0 || index >= cartItems.value.length) {
            return
        }

        const removed = cartItems.value[index]

        cartItems.value = cartItems.value.filter((item, i) => {
            if (i === index) {
                return false
            }

            return !(removed.type === 'service' && item.serviceItemId === removed.id)
        })
    }

    function updateItem(id, changes) {
        const item = cartItems.value.find((entry) => entry.id === id)

        if (!item) {
            return
        }

        Object.assign(item, changes, { id: item.id })

        if ('price' in changes) {
            item.price = toPrice(changes.price)
        }
    }

    function clearCart() {
        cartItems.value = []
    }

    const services = computed(() => cartItems.value.filter((item) => item.type === 'service'))

    const designs = computed(() => cartItems.value.filter((item) => item.type === 'design'))

    function designsForService(serviceItemId) {
        return designs.value.filter((item) => item.serviceItemId === serviceItemId)
    }

    const count = computed(() => cartItems.value.length)

    const isEmpty = computed(() => cartItems.value.length === 0)

    // In Cent rechnen, damit 19.9 + 0.1 auch wirklich 20 ergibt
    const total = computed(() => {
        const cents = cartItems.value.reduce(
            (sum, item) => sum + Math.round(toPrice(item.price) * 100),
            0
        )

        return cents / 100
    })

    return {
        cartItems,
        services,
        designs,
        designsForService,
        count,
        isEmpty,
        total,
        addToCart,
        removeFromCart,
        updateItem,
        clearCart,
    }
}