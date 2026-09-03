<script setup> 
import NavLink from '@/Components/NavLink.vue'  
</script>

<template> 
    <nav class="navbar">
        <ul class="nav-links">
            <li>
                <NavLink href="/" :active="route().current('welcome')">Home</NavLink>
            </li>
            <li>
                <NavLink href="/DesignEditor" :active="route().current('DesignEditor')">Design Editor</NavLink>
            </li>
            <li>
                <NavLink href="/Appointments" :active="route().current('Appointments')">Appointments</NavLink>
            </li>
        </ul>
    </nav>
</template>

<style lang="css" scoped>
.navbar {
    background-color: var(--background-color);

    margin-bottom: 3rem;


    border-bottom: 1px var(--background-color) solid;

    box-shadow:
        0 2px 8px rgba(184, 115, 131, 0.04),
        0 8px 24px rgba(109, 59, 71, 0.06);

}

.nav-links {
   display: flex;
   justify-content: left;
   gap: 1rem;
   
  
   margin-left: 1rem ;
   padding: 1rem;
   color: var(--text-color);
}
</style>