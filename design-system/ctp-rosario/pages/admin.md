# Page Override: Panel Administrativo (Admin & CMS) — CTP Rosario

> Hereda de `design-system/ctp-rosario/MASTER.md`. Aplica a `admin/*.php`.

---

## 1. Arquitectura del Panel Administrativo
- **Barra de Navegación Superior (`.admin-navbar`):**
  - Fondo: Borgoña institucional `--color-primary` (`#871a2f`) con borde inferior `--color-primary-dark` (`#5c1120`).
  - Logotipo del colegio con acento dorado `#ffd700`.
  - Enlaces de navegación con iconos Iconimate y detección de página activa:
    - *Dashboard* (`admin/index.php`)
    - *Especialidades CMS* (`admin/especialidades.php`)
    - *Configuración Web* (`admin/configuracion.php`)
    - *Solicitudes & Consultas* (`admin/solicitudes.php`)
    - *Noticias* (`admin/noticias.php`)
    - *Usuarios & Permisos* (`admin/usuarios.php`)
  - Indicador de usuario en sesión con badge de rol (`admin`, `editor`, `user`), acceso al portal público y botón de cierre de sesión seguro.

## 2. Componentes Clave del CMS
- **Tarjetas KPI con Contadores Animados:**
  - Valores numéricos de Pre-Matrículas, Consultas no leídas, Especialidades activas, Noticias y Usuarios.
  - Cifras animadas progresivamente al cargar con Anime.js v4.
- **Barra de Filtros Combinables (`.filter-bar`):**
  - Selector de especialidad, selector de estado de solicitud, rango de fechas y buscador en tiempo real.
  - Botón de Exportación CSV con icono `download-simple`.
- **Tablas de Datos Responsivas (`.table-responsive > .data-table`):**
  - Encabezados claros, padding generoso, hover en filas y badges semánticos con texto e icono para lectura rápida.
  - Cero desbordamiento horizontal en pantallas pequeñas.
- **Formularios de Configuración con Switchers iOS:**
  - Interruptores interactivos para activar/desactivar especialidades y abrir/cerrar pre-matrícula con retroalimentación instantánea.

