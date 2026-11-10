# Page Override: Oferta Académica (Especialidades) — CTP Rosario

> Hereda de `design-system/ctp-rosario/MASTER.md`. Aplica a `public/oferta.php`.

---

## 1. Composición y Elementos
- **Encabezado de Página (`.page-header`):**
  - Fondo: Borgoña institucional con patrón suave.
  - Título y descripción de la oferta educativa técnica diversificada.
- **Barra de Búsqueda y Filtro Rápido:**
  - Input de búsqueda instantánea por nombre o contenido de la especialidad con icono `magnifying-glass`.
  - Contador dinámico de especialidades activas encontradas.
- **Catálogo de Tarjetas Especialidad (`.specialty-card`):**
  - Carga dinámica desde la base de datos (`especialidades WHERE activa = 1 ORDER BY orden ASC`).
  - Cabecera de tarjeta: Icono Iconimate en contenedor circular suave (`#fdf2f4`, color `#871a2f`).
  - Nombre de la especialidad H3 con contraste legible.
  - Descripción formativa y lista de competencias / perfil de salida con iconos `check-circle`.
  - Botón de Acción Directa: *"Pre-matricularme en esta especialidad"* con enlace canónico a `public/prematricula.php?especialidad=<slug>`.
- **Beneficios Institucionales:**
  - Tarjeta bento con información de pasantías empresariales, convenios y continuidad universitaria.

## 2. Micro-interacciones
- Revelado de tarjetas con scroll suave e `IntersectionObserver` animado con Anime.js v4.
- Filtrado en vivo sin recarga de página: las tarjetas no coincidentes se desvanecen suavemente.

