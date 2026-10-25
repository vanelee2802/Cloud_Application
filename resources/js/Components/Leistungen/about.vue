
<script setup>
</script>

<template>
    <section class="studio-intro">
        <div class="intro-container">

            <!-- Textbereich -->
            <div class="intro-content">
                <span class="eyebrow">
                    WILLKOMMEN IN UNSEREM STUDIO
                </span>

                <h1>
                    Schönheit beginnt
                    <span>bis in die Fingerspitzen.</span>
                </h1>

                <p class="intro-lead">
                    Dein Wohlfühlort für gepflegte Nägel,
                    kreative Designs und kleine Auszeiten vom Alltag.
                </p>

                <p class="intro-description">
                    Wir lieben es, individuelle Nageldesigns zu kreieren,
                    die deine Persönlichkeit unterstreichen. Ob natürlich,
                    elegant oder auffällig – bei uns stehen deine Wünsche,
                    eine sorgfältige Arbeit und dein Wohlbefinden im Mittelpunkt.
                </p>

                <div class="intro-values">
                    <div class="value-item">
                        <span class="value-icon">♡</span>
                        <div>
                            <h3>Mit Liebe zum Detail</h3>
                            <p>Individuelle Designs und sorgfältige Arbeit.</p>
                        </div>
                    </div>

                    <div class="value-item">
                        <span class="value-icon">✧</span>
                        <div>
                            <h3>Dein persönlicher Stil</h3>
                            <p>Von natürlich und zeitlos bis kreativ und modern.</p>
                        </div>
                    </div>

                    <div class="value-item">
                        <span class="value-icon">❀</span>
                        <div>
                            <h3>Zeit für dich</h3>
                            <p>Eine kleine Auszeit, in der du im Mittelpunkt stehst.</p>
                        </div>
                    </div>
                </div>

                <a href="/Leistungen" class="intro-button">
                    Unsere Leistungen entdecken
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <!-- Bildbereich -->
            <div class="intro-visual">
                <div class="image-frame">
                    <img
                        src="/images/studio-nails.jpg"
                        alt="Elegantes Nageldesign in zarten Rosé-Tönen"
                        class="intro-image"
                    />

                    <div class="image-caption">
                        <span>Deine Schönheit.</span>
                        <strong>Dein Stil.</strong>
                    </div>
                </div>

                <div class="decorative-circle"></div>
            </div>

        </div>
    </section>
</template>

<style scoped>
.studio-intro {
    padding: 5rem 1.5rem;
    background: #fffafb;
    color: #3d2930;
    overflow: hidden;
}

.intro-container {
    position: relative;
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    align-items: center;
    gap: 4rem;

    max-width: 1200px;
    margin: 0 auto;
}

/* Text */

.intro-content {
    position: relative;
    z-index: 1;
}

.eyebrow {
    display: inline-block;
    margin-bottom: 1.25rem;

    color: #8f5364;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.16em;
}

.intro-content h1 {
    margin: 0 0 1.5rem;

    color: #754353;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: clamp(2.5rem, 4.5vw, 4rem);
    font-weight: 500;
    line-height: 1.13;
}

.intro-content h1 span {
    display: block;
    color: #b17b8c;
    font-style: italic;
}

.intro-lead {
    margin: 0 0 1rem;

    color: #634650;
    font-size: 1.1rem;
    line-height: 1.8;
}

.intro-description {
    margin: 0;

    color: #8b7b80;
    font-size: 0.95rem;
    line-height: 1.9;
}

/* Werte */

.intro-values {
    display: grid;
    gap: 1.35rem;
    margin: 2rem 0;
}

.value-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.value-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    width: 42px;
    height: 42px;
    border-radius: 50%;

    background: #f7e9ee;
    color: #8f5364;
    font-size: 1.35rem;
}

.value-item h3 {
    margin: 0 0 0.3rem;

    color: #754353;
    font-size: 0.95rem;
    font-weight: 600;
}

.value-item p {
    margin: 0;

    color: #8b7b80;
    font-size: 0.88rem;
    line-height: 1.6;
}

/* Button */

.intro-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;

    padding: 0.9rem 1.4rem;
    border: 1px solid #8f5364;
    border-radius: 9px;

    background: #8f5364;
    color: white;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;

    transition:
        background 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.intro-button:hover {
    background: #754353;
    box-shadow: 0 6px 18px rgba(80, 35, 48, 0.15);
    transform: translateY(-2px);
}

.intro-button span {
    font-size: 1.15rem;
}

/* Bild */

.intro-visual {
    position: relative;
    padding: 1rem 1rem 1rem 0;
}

.image-frame {
    position: relative;
    z-index: 1;

    height: 560px;
    overflow: hidden;
    border-radius: 180px 180px 18px 18px;

    background: #f7e9ee;
    box-shadow: 0 18px 50px rgba(80, 35, 48, 0.12);
}

.intro-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-caption {
    position: absolute;
    right: 1rem;
    bottom: 1rem;
    left: 1rem;

    display: flex;
    flex-direction: column;
    gap: 0.2rem;

    padding: 1.2rem 1.4rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 12px;

    background: rgba(255, 250, 251, 0.91);
    color: #754353;
    backdrop-filter: blur(10px);
}

.image-caption span {
    font-size: 0.85rem;
    color: #8b7b80;
}

.image-caption strong {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.4rem;
    font-weight: 500;
}

.decorative-circle {
    position: absolute;
    top: -1rem;
    right: -1rem;

    width: 180px;
    height: 180px;
    border: 1px solid #e8cbd4;
    border-radius: 50%;
}

/* Responsive */

@media (max-width: 850px) {
    .studio-intro {
        padding: 3.5rem 1.25rem;
    }

    .intro-container {
        grid-template-columns: 1fr;
        gap: 3rem;
    }

    .intro-visual {
        width: min(100%, 500px);
        margin: 0 auto;
    }

    .image-frame {
        height: 480px;
    }

    .decorative-circle {
        right: -0.25rem;
    }
}

@media (max-width: 480px) {
    .intro-content h1 {
        font-size: 2.5rem;
    }

    .image-frame {
        height: 390px;
        border-radius: 130px 130px 16px 16px;
    }

    .intro-button {
        width: 100%;
        padding: 0.9rem 1rem;
    }
}
</style>
