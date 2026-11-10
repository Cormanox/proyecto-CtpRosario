/**
 * Controlador de Animaciones y Microinteracciones
 * Librerías: Anime.js v4 (https://animejs.com/) + Iconimate (https://iconimate.app/)
 * Proyecto: Portal Web Institucional CTP Rosario de Naranjo
 */

import { animate, stagger } from 'https://cdn.jsdelivr.net/npm/animejs/+esm';

// Verificar preferencia de reducción de movimiento
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', () => {
    if (prefersReducedMotion) {
        console.info('[CTP Rosario] prefers-reduced-motion activo. Animaciones complejas desactivadas.');
        return;
    }

    initHeroAndNewsStagger();
    initSpecialtiesScrollReveal();
    initKpiCounters();
    initIconimateInteractions();
    initFormShakeOnError();
});

/**
 * 1. Entrada Escalonada para Hero y Noticias
 */
function initHeroAndNewsStagger() {
    const heroElements = document.querySelectorAll('.hero h2, .hero p, .hero-buttons, .hero-content > *');
    if (heroElements.length > 0) {
        animate(heroElements, {
            opacity: [0, 1],
            translateY: [24, 0],
            delay: stagger(90, { start: 100 }),
            duration: 650,
            ease: 'outQuart'
        });
    }

    const newsCards = document.querySelectorAll('.news-card');
    if (newsCards.length > 0) {
        animate(newsCards, {
            opacity: [0, 1],
            translateY: [20, 0],
            delay: stagger(100, { start: 250 }),
            duration: 600,
            ease: 'outCubic'
        });
    }
}

/**
 * 2. Revelado en Scroll de Especialidades Técnicas
 */
function initSpecialtiesScrollReveal() {
    const specialtyCards = document.querySelectorAll('.specialty-card');
    if (specialtyCards.length === 0) return;

    // Inicializar estado oculto para animación suave
    specialtyCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(22px)';
    });

    const observer = new IntersectionObserver((entries, obs) => {
        const visibleCards = [];
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                visibleCards.push(entry.target);
                obs.unobserve(entry.target);
            }
        });

        if (visibleCards.length > 0) {
            animate(visibleCards, {
                opacity: [0, 1],
                translateY: [22, 0],
                delay: stagger(80),
                duration: 600,
                ease: 'outQuart'
            });
        }
    }, { threshold: 0.15 });

    specialtyCards.forEach(card => observer.observe(card));
}

/**
 * 3. Contadores Numéricos Animados en Dashboard KPI
 */
function initKpiCounters() {
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach(counter => {
        const text = counter.innerText.trim();
        const targetValue = parseInt(text, 10);

        if (!isNaN(targetValue) && targetValue > 0) {
            counter.innerText = '0';
            const obj = { val: 0 };
            animate(obj, {
                val: targetValue,
                duration: 900,
                ease: 'outExpo',
                onUpdate: () => {
                    counter.innerText = Math.round(obj.val);
                }
            });
        }
    });
}

/**
 * 4. Microinteracciones Iconimate en Hover, Focus y Toque
 */
function initIconimateInteractions() {
    const iconWrappers = document.querySelectorAll('[data-iconimate]');

    iconWrappers.forEach(el => {
        const iconName = el.getAttribute('data-iconimate') || '';

        const runAnimation = () => {
            switch (iconName) {
                case 'bell-ringing':
                    animate(el, {
                        rotate: [-14, 14, -8, 8, 0],
                        duration: 450,
                        ease: 'inOutSine'
                    });
                    break;

                case 'arrows-clockwise':
                    animate(el, {
                        rotate: [0, 360],
                        duration: 550,
                        ease: 'outCubic'
                    });
                    break;

                case 'sliders':
                case 'funnel':
                    animate(el, {
                        scale: [1, 1.18, 1],
                        duration: 320,
                        ease: 'outBack'
                    });
                    break;

                case 'download-simple':
                    animate(el, {
                        translateY: [0, 4, 0],
                        duration: 350,
                        ease: 'inOutQuad'
                    });
                    break;

                case 'trash':
                    animate(el, {
                        rotate: [-10, 10, 0],
                        duration: 300,
                        ease: 'inOutQuad'
                    });
                    break;

                default:
                    animate(el, {
                        scale: [1, 1.12, 1],
                        duration: 250,
                        ease: 'outQuad'
                    });
                    break;
            }
        };

        const target = el.closest('button, a, .specialty-card, .service-card, .stat-card') || el;
        target.addEventListener('mouseenter', runAnimation);
        target.addEventListener('focus', runAnimation);
        target.addEventListener('pointerdown', runAnimation);
    });
}

/**
 * 5. Microinteracción de Sacudida ante Errores de Validación
 */
function initFormShakeOnError() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('invalid', (e) => {
            const invalidField = e.target;
            invalidField.classList.add('shake-error');
            setTimeout(() => {
                invalidField.classList.remove('shake-error');
            }, 450);
        }, true);
    });
}

// Exportar helper para uso programático si es requerido
export { animate, stagger };

