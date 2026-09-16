<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue' 
import "../../css/NailEditor.css"

</script> 

<template>
    <pagesLayout> 
        <main class="nail-designer">

        <!-- LINKER BEREICH -->
        <aside class="left-sidebar">

            <section class="categories">
                <CategoryMenu />
            </section>

            <section class="nail-selection">
                <NailSelector />
            </section>

        </aside>


        <!-- MITTLERER BEREICH -->
        <section class="designer-area">

            <section class="shape-selection">
                <ShapeSelector />
            </section>

            <section class="nail-canvas">
                <NailCanvas />
            </section>

            <section class="bottom-options">

                <section class="colors">
                    <ColorSelector />
                </section>

                <section class="designs">
                    <DesignSelector />
                </section>

            </section>

        </section>


        <!-- RECHTER BEREICH -->
        <aside class="design-summary">
            <DesignSummary />
        </aside>

    </main>
    </pagesLayout>
</template>
