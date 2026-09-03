<script setup>
import pagesLayout from '@/Layouts/pagesLayout.vue' 
import "../../css/Welcome.css"
</script> 

<template>
    <pagesLayout>

       <!-- HERO -->
        <section class="hero" style="text-align: center;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Willkommen bei MixMatch</h1>
            <p style="font-size: 1.25rem; margin-bottom: 2rem;">
                Entdecke die Welt der Nageldesigns und buche direkt deinen Termin!
            </p>
           <img
                style="width: 100%; height: auto; max-height: 500px; object-fit: cover;"
                src="/images/banner.png"
                alt="MixMatch"
            > 
            
            <h1>
            About
             </h1>
             <p>
                Mit Mix and Match kannst du dein individuelles Nageldesign ganz einfach konfigurieren und anschließend direkt einen Termin bei einem passenden Nagelstudio anfragen.<br>
                 Durch die transparente Preisgestaltung weißt du bereits vor der Buchung, welche Kosten für dein persönliches Design entstehen.
             </p>
        </section>
       
        <!-- Inhaltsverzeichnis -->
        <nav aria-label="Inhaltsverzeichnis">
        <h2>Inhaltsverzeichnis</h2>

            <ol >
                <li>
                    <a href="#projekt">Unser Projekt</a>
                </li>

                <li>
                    <a href="#motivation">Unsere Motivation</a>
                </li>

                <li>
                    <a href="#ziel">Unser Ziel</a>
                </li>

                <li>
                    <a href="#konzept">Unser Konzept</a>
                </li>

                <li>
                    <a href="#technologien">Technologien</a>
                </li>

                <li>
                    <a href="#architektur">Architektur</a>
                </li>

                <li>
                    <a href="#entscheidungen">Technologische Entscheidungen</a>
                </li>

                <li>
                    <a href="#features">Features</a>
                </li>

                <li>
                    <a href="#seminar">Application in the Cloud</a>
                </li>
            </ol>
        </nav>


        <!-- Unser Projekt -->
        <section id="projekt">
            <img
                src="https://placehold.co/600x400/F6EEE8/6D3B47?text=Our+Project"
                alt="Unser Projekt"
            >

            <h2>Unser Projekt</h2>

            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            </p>

            <a href="#top">Nach oben</a>
        </section>


        <!-- Motivation -->
        <section id="motivation">
            <img
                src="https://placehold.co/600x400/E9D7D1/6D3B47?text=Motivation"
                alt="Unsere Motivation"
            >

            <h2>Unsere Motivation</h2>

            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            </p>

            <a href="#top">Nach oben</a>
        </section>


        <!-- Ziel -->
        <section id="ziel">
            <img
                src="https://placehold.co/600x400/D9A58F/6D3B47?text=Our+Goal"
                alt="Unser Ziel"
            >

            <h2>Unser Ziel</h2>

            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            </p>

            <a href="#top">Nach oben</a>
        </section>


        <!-- Konzept -->
        <section id="konzept">
            <h2>Unser Konzept</h2>

            <img
                src="https://placehold.co/800x450/B07D7D/F6EEE8?text=Concept"
                alt="MixMatch Konzept"
            >

            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            </p>

            <a href="#top">Nach oben</a>
        </section>


        <!-- Technologien -->
        <section id="technologien">
            <h2>Technologien</h2>

            <div>
                <article>
                    <img
                        src="https://placehold.co/400x250/6D3B47/F6EEE8?text=Frontend"
                        alt="Frontend"
                    >

                    <h3>Frontend</h3>

                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    </p>
                </article>

                <article>
                    <img
                        src="https://placehold.co/400x250/B07D7D/F6EEE8?text=Backend"
                        alt="Backend"
                    >

                    <h3>Backend</h3>

                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    </p>
                </article>

                <article>
                    <img
                        src="https://placehold.co/400x250/D9A58F/6D3B47?text=Database"
                        alt="Datenbank"
                    >

                    <h3>Datenbank</h3>

                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    </p>
                </article>
            </div>

            <a href="#top">Nach oben</a>
        </section>


        <!-- Architektur -->
        <section id="architektur">
            <h2>Architektur</h2>

            <img
                src="https://placehold.co/1000x400/E9D7D1/6D3B47?text=Frontend+%E2%86%92+Backend+%E2%86%92+Database"
                alt="Systemarchitektur"
            >

            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            </p>

            <a href="#top">Nach oben</a>
        </section>


        <!-- Entscheidungen -->
        <section id="entscheidungen">
            <h2>Technologische Entscheidungen</h2>

            <article>
                <h3>Warum Laravel?</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </p>
            </article>

            <article>
                <h3>Warum Vue?</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </p>
            </article>

            <article>
                <h3>Warum Cloud?</h3>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </p>
            </article>

            <a href="#top">Nach oben</a>
        </section>


        <!-- Features -->
        <section id="features">
            <h2>Features</h2>

            <img
                src="https://placehold.co/800x400/F6EEE8/6D3B47?text=Features"
                alt="MixMatch Features"
            >

            <ul>
                <li>Feature 1 – Lorem ipsum dolor sit amet.</li>
                <li>Feature 2 – Lorem ipsum dolor sit amet.</li>
                <li>Feature 3 – Lorem ipsum dolor sit amet.</li>
                <li>Feature 4 – Lorem ipsum dolor sit amet.</li>
            </ul>

            <a href="#top">Nach oben</a>
        </section>


        <!-- Seminar -->
        <section id="seminar">
            <h2>Application in the Cloud</h2>

            <img
                src="https://placehold.co/1000x400/6D3B47/F6EEE8?text=Application+in+the+Cloud"
                alt="Application in the Cloud"
            >

            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            </p>

            <a href="#top">Nach oben</a>
        </section>

    </pagesLayout>
</template>


