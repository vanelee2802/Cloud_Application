<script setup>
import NavigationBar from '@/Components/NavigationBar.vue';

</script>
<template>
    <header>
        <NavigationBar/>
    </header>

    <main>
        <slot /> 
    </main>

    <footer>
        <p class="text-center text-gray-500 text-xs">
            &copy;2024 MixMatch. All rights reserved.
        </p>
    </footer>
</template>

