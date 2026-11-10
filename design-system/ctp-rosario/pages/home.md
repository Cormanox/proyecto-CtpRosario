# Page Override: Inicio (Home) — CTP Rosario

> Hereda de `design-system/ctp-rosario/MASTER.md`. Las reglas aquí especificadas aplican exclusivamente a `public/index.php`.

---

## 1. Composición y Secciones
- **Hero Institucional:**
  - Fondo: Degradado sutil con base `--color-primary` (`#871a2f`) a `--color-primary-dark` (`#5c1120`).
  - Título Principal con tipografía fluida: `clamp(2rem, 5vw, 3.25rem)`.
  - Botones de Doble Llamada a la Acción (CTA):
    - Primario: Pre-Matrícula (`.btn-primary`, fondo `--color-secondary` `#9c1932`, texto blanco).
    - Secundario: Conocer Especialidades (`.btn-secondary`, borde blanco/dorado, texto inverso).
- **Banner de Estado Convocatoria:**
  - Muestra si la pre-matrícula está actualmente activa con enlace directo o el aviso de cierre oficial.
- **Sección Noticias y Comunicados:**
  - Grid responsivo de 3 columnas (`repeat(auto-fit, minmax(300px, 1fr))`).
  - Tarjetas con imagen ratio 16:9, fecha formateada con icono SVG, título H3 y resumen truncado.
  - Elevación `--shadow-sm` con transición suave a `--shadow-md` en hover (`transform: translateY(-3px)`).
- **Sección Misión, Visión y Reseña Institucional:**
  - Grid de 3 tarjetas destacadas con iconos institucionales Iconimate (`shield-check`, `graduation-cap`, `newspaper`).
- **Servicios Digitales Bento:**
  - Acceso directo a Pre-Matrícula Digital, Oferta de Especialidades y Formulario de Contacto.

## 2. Micro-interacciones
- Entrada escalonada de elementos Hero y tarjetas de noticias mediante Anime.js v4 (`stagger(80)`).
- Iconos interactivos con animación al `:hover` y `:focus-visible`.

