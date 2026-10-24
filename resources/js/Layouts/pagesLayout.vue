
<script setup>
import NavigationBar from '@/Components/NavigationBar.vue'
import Footer from '@/Components/Footer.vue'
</script>

<template>
    <div class="page-layout">
        <header>
            <NavigationBar />
        </header>

        <main class="page-content">
            <slot />
        </main>

        <Footer />
    </div>
</template>

<style scoped>
.page-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.page-content {
    flex: 1;
}
</style>
