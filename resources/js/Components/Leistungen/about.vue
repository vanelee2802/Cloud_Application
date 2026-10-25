<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const slides = [
    {
        src: 'https://images.unsplash.com/photo-1519014816548-bf5fe059798b?auto=format&fit=crop&w=1000&q=80',
        alt: 'Zarte rosafarbene Maniküre in Nahaufnahme',
        title: 'Zartes Rosé',
        subtitle: 'Natürlich & elegant',
    },
    {
        src: 'https://images.unsplash.com/photo-1754799670410-b282791342c3?auto=format&fit=crop&w=1000&q=80',
        alt: 'Hände mit weißen und rosa Nägeln mit kleinen Herzchen',
        title: 'Little Hearts',
        subtitle: 'Verspielt & romantisch',
    },
    {
        src: 'https://images.unsplash.com/photo-1754799670312-8e7da8e40ad7?auto=format&fit=crop&w=1000&q=80',
        alt: 'Kunstvolles Nageldesign in Navy, Gold und Weiß',
        title: 'Navy & Gold',
        subtitle: 'Für besondere Momente',
    },
    {
        src: 'https://images.unsplash.com/photo-1571290274554-6a2eaa771e5f?auto=format&fit=crop&w=1000&q=80',
        alt: 'Gepflegte Hände mit frischer Maniküre',
        title: 'Classic Care',
        subtitle: 'Zeitlos gepflegt',
    },
    {
        src: 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=1000&q=80',
        alt: 'Hand mit schwarzen Nägeln im Schildpatt-Muster und goldenem Ring',
        title: 'Tortoise Look',
        subtitle: 'Modern & ausdrucksstark',
    },
]

const currentIndex = ref(0)
const isPaused = ref(false)
const autoplayDelay = 5000

let autoplayTimer = null
let touchStartX = 0

function nextSlide() {
    currentIndex.value = (currentIndex.value + 1) % slides.length
}

function prevSlide() {
    currentIndex.value = (currentIndex.value - 1 + slides.length) % slides.length
}

function goToSlide(index) {
    currentIndex.value = index
    restartAutoplay()
}

function startAutoplay() {
    stopAutoplay()

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion) return

    autoplayTimer = setInterval(() => {
        if (!isPaused.value) {
            nextSlide()
        }
    }, autoplayDelay)
}

function stopAutoplay() {
    if (autoplayTimer) {
        clearInterval(autoplayTimer)
        autoplayTimer = null
    }
}

function restartAutoplay() {
    startAutoplay()
}

function handleNext() {
    nextSlide()
    restartAutoplay()
}

function handlePrev() {
    prevSlide()
    restartAutoplay()
}

function onTouchStart(event) {
    touchStartX = event.changedTouches[0].clientX
}

function onTouchEnd(event) {
    const distance = event.changedTouches[0].clientX - touchStartX

    if (Math.abs(distance) < 50) return

    if (distance < 0) {
        handleNext()
    } else {
        handlePrev()
    }
}

onMounted(() => {
    startAutoplay()
})

onBeforeUnmount(() => {
    stopAutoplay()
})
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

                <a href="#preise" class="intro-button">
                    Unsere Leistungen entdecken
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <!-- Bildbereich mit Karussell -->
            <div class="intro-visual">
                <div
                    class="image-frame"
                    role="region"
                    aria-roledescription="Karussell"
                    aria-label="Unsere Nageldesigns"
                    @mouseenter="isPaused = true"
                    @mouseleave="isPaused = false"
                    @focusin="isPaused = true"
                    @focusout="isPaused = false"
                    @touchstart.passive="onTouchStart"
                    @touchend.passive="onTouchEnd"
                >
                    <!-- Slides -->
                    <div
                        v-for="(slide, index) in slides"
                        :key="slide.src"
                        class="carousel-slide"
                        :class="{ active: index === currentIndex }"
                        role="group"
                        aria-roledescription="Folie"
                        :aria-label="`${index + 1} von ${slides.length}`"
                        :aria-hidden="index !== currentIndex"
                    >
                        <img
                            :src="slide.src"
                            :alt="slide.alt"
                            class="intro-image"
                            :loading="index === 0 ? 'eager' : 'lazy'"
                        />
                    </div>

                    <!-- Pfeile -->
                    <button
                        type="button"
                        class="carousel-arrow carousel-arrow--prev"
                        aria-label="Vorheriges Design"
                        @click="handlePrev"
                    >
                        ‹
                    </button>

                    <button
                        type="button"
                        class="carousel-arrow carousel-arrow--next"
                        aria-label="Nächstes Design"
                        @click="handleNext"
                    >
                        ›
                    </button>

                    <!-- Bildunterschrift + Punkte -->
                    <div class="image-caption">
                        <div class="caption-text" aria-live="polite">
                            <span>{{ slides[currentIndex].subtitle }}</span>
                            <strong>{{ slides[currentIndex].title }}</strong>
                        </div>

                        <div class="carousel-dots">
                            <button
                                v-for="(slide, index) in slides"
                                :key="`dot-${slide.src}`"
                                type="button"
                                class="carousel-dot"
                                :class="{ active: index === currentIndex }"
                                :aria-label="`Design ${index + 1} anzeigen`"
                                :aria-current="index === currentIndex"
                                @click="goToSlide(index)"
                            ></button>
                        </div>
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

/* Bild / Karussell */

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

.carousel-slide {
    position: absolute;
    inset: 0;

    opacity: 0;
    transform: scale(1.04);
    transition:
        opacity 0.9s ease,
        transform 6s ease;
}

.carousel-slide.active {
    opacity: 1;
    transform: scale(1);
}

.intro-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Pfeile */

.carousel-arrow {
    position: absolute;
    top: 50%;
    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 42px;
    height: 42px;
    padding: 0 0 3px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 50%;

    background: rgba(255, 250, 251, 0.85);
    color: #754353;
    font-size: 1.6rem;
    line-height: 1;
    cursor: pointer;
    backdrop-filter: blur(8px);

    opacity: 0;
    transform: translateY(-50%);
    transition:
        opacity 0.25s ease,
        background 0.2s ease;
}

.carousel-arrow--prev {
    left: 1rem;
}

.carousel-arrow--next {
    right: 1rem;
}

.image-frame:hover .carousel-arrow,
.carousel-arrow:focus-visible {
    opacity: 1;
}

.carousel-arrow:hover {
    background: #ffffff;
}

.carousel-arrow:focus-visible,
.carousel-dot:focus-visible {
    outline: 2px solid #8f5364;
    outline-offset: 2px;
}

/* Bildunterschrift */

.image-caption {
    position: absolute;
    right: 1rem;
    bottom: 1rem;
    left: 1rem;
    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;

    padding: 1.2rem 1.4rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 12px;

    background: rgba(255, 250, 251, 0.91);
    color: #754353;
    backdrop-filter: blur(10px);
}

.caption-text {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    min-width: 0;
}

.caption-text span {
    font-size: 0.85rem;
    color: #8b7b80;
}

.caption-text strong {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.4rem;
    font-weight: 500;
}

/* Punkte */

.carousel-dots {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-shrink: 0;
}

.carousel-dot {
    width: 8px;
    height: 8px;
    padding: 0;
    border: none;
    border-radius: 999px;

    background: #e3c6cf;
    cursor: pointer;

    transition:
        width 0.3s ease,
        background 0.3s ease;
}

.carousel-dot:hover {
    background: #b17b8c;
}

.carousel-dot.active {
    width: 22px;
    background: #8f5364;
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

    /* Auf Touch-Geräten Pfeile immer sichtbar */
    .carousel-arrow {
        opacity: 1;
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

    .image-caption {
        padding: 1rem 1.1rem;
    }

    .caption-text strong {
        font-size: 1.15rem;
    }

    .carousel-arrow {
        width: 36px;
        height: 36px;
        font-size: 1.4rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .carousel-slide {
        transform: none;
        transition: opacity 0.3s ease;
    }

    .carousel-slide.active {
        transform: none;
    }
}
</style>