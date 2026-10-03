<template>
    <div class="category-panel">

        <h3>KATEGORIEN</h3>

        <div
            v-for="category in categories"
            :key="category.name"
        >
            <button @click="selectCategory(category.name)">
                <img :src="category.icon" alt="">
                <span>{{ category.name }}</span>
            </button>
        </div>

    </div>
</template>


<script setup>

const emit = defineEmits(['category-selected'])

function selectCategory(category) {
    emit('category-selected', category)
}

const categories = [
    {
        name: 'Form',
        icon: '/images/nail-editor/icon/nail_icon.svg',
    },
    {
        name: 'Farbe',
        icon: '/images/nail-editor/icon/NailPolish_icon.svg',
    },
    {
        name: 'Designs',
        icon: '/images/nail-editor/icon/NailDesign_icon.svg',
    },
    {
        name: 'Effekte',
        icon: '/images/nail-editor/icon/NailEffect_icon.svg',
    },
    {
        name: 'Verzierung',
        icon: '/images/nail-editor/icon/NailVerzierung_icon.svg',
    },
]

</script>



<style scoped>
.category-panel { 
 
    width: clamp( 
        160px, 
        18vw, 
        240px 
    ); 
 
    /* Abstand wird jetzt im DesignEditor geregelt */ 
    margin-left: 0; 
 
    padding: clamp( 
        0.7rem, 
        1vw, 
        1.4rem 
    ); 
 
    background: #ffffff; 
 
    border: 0.08vw solid #eeeeee; 
 
    border-radius: 1vw; 
 
    box-shadow: 
        0 
        0.3vw 
        1.2vw 
        rgba(0, 0, 0, 0.06); 
 
    box-sizing: border-box; 
} 
 
 
/* ========================= 
   Überschrift 
   ========================= */ 
 
.category-panel h3 { 
 
    margin: 
        0 
        0 
        clamp( 
            1rem, 
            1.618vw, 
            2rem 
        ) 
        0; 
 
    font-size: clamp( 
        0.85rem, 
        1.1vw, 
        1.2rem 
    ); 
 
    font-weight: 500; 
 
    letter-spacing: 0.08em; 
 
    text-transform: uppercase; 
 
    color: #7d1024; 
} 
 
 
/* ========================= 
   Kategorie-Buttons 
   ========================= */ 
 
.category-panel button { 
 
    display: flex; 
 
    align-items: center; 
 
    width: 100%; 
 
    padding: clamp( 
        0.5rem, 
        0.618vw, 
        0.8rem 
    ); 
 
    margin-bottom: clamp( 
        0.3rem, 
        0.618vw, 
        0.8rem 
    ); 
 
    gap: clamp( 
        0.6rem, 
        1vw, 
        1.2rem 
    ); 
 
    border: none; 
 
    border-radius: 0.618vw; 
 
    background: transparent; 
 
    color: #222222; 
 
    font-size: clamp( 
        0.9rem, 
        1.2vw, 
        1.3rem 
    ); 
 
    font-weight: 400; 
 
    text-align: left; 
 
    cursor: pointer; 
 
    transition: 
        background-color 0.2s ease, 
        color 0.2s ease, 
        transform 0.2s ease, 
        box-shadow 0.2s ease; 
} 
 
 
/* ========================= 
   Icons 
   ========================= */ 
 
.category-panel button img { 
 
    width: clamp( 
        30px, 
        3vw, 
        48px 
    ); 
 
    height: clamp( 
        30px, 
        3vw, 
        48px 
    ); 
 
    object-fit: contain; 
 
    flex-shrink: 0; 
 
    transition: 
        transform 0.2s ease; 
} 
 
 
/* ========================= 
   Hover 
   ========================= */ 
 
.category-panel button:hover { 
 
    background: #f8f0f2; 
 
    color: #7d1024; 
 
    transform: translateX(0.382vw); 
 
    box-shadow: 
        0 
        0.2vw 
        0.8vw 
        rgba(125, 16, 36, 0.08); 
} 
 
 
.category-panel button:hover img { 
 
    transform: scale(1.08); 
} 
 
 
/* ========================= 
   Aktive Kategorie 
   ========================= */ 
 
.category-panel button.active { 
 
    background: #f3e5e8; 
 
    color: #7d1024; 
 
    font-weight: 600; 
 
    box-shadow: 
        inset 
        0 
        0 
        0 
        0.08vw 
        #7d1024; 
} 
 
 
/* ========================= 
   Letzter Button 
   ========================= */ 
 
.category-panel button:last-child { 
 
    margin-bottom: 0; 
 
} 
 
 
/* ========================= 
   Kleine Bildschirme 
   ========================= */ 
 
@media (max-width: 700px) { 
 
    .category-panel { 
 
        width: 90vw; 
 
        margin-left: 0; 
 
        margin-right: 0; 
 
        padding: 1.618rem; 
 
    } 
 
 
    .category-panel button { 
 
        font-size: 1rem; 
 
        gap: 1rem; 
 
    } 
 
 
    .category-panel button img { 
 
        width: 36px; 
 
        height: 36px; 
 
    } 
 
} 
 
</style>