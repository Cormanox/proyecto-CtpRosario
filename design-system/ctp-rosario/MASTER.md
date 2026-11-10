# Design System Master File — CTP Rosario

> **LÓGICA HIERÁRQUICA:** Al construir o modificar una página específica, revisa primero `design-system/ctp-rosario/pages/[page-name].md`.
> Si ese archivo existe, sus reglas **tienen prioridad** sobre este archivo Master.
> Si no existe, sigue estrictamente las especificaciones descritas a continuación.

---

**Proyecto:** CTP Rosario (Colegio Técnico Profesional Rosario de Naranjo)  
**Versión:** 2.0 (Autogestión CMS & UI/UX Pro Max)  
**Dominio:** Portal Institucional y CMS Educativo Técnico  
**Restricción Crítica:** Preservación estricta de la paleta cromática original del colegio.

---

## 1. Identidad Cromática Institucional

| Rol | Hex | CSS Variable | Uso en Interfaz |
|---|---|---|---|
| **Primary (Borgoña Institucional)** | `#871a2f` | `--color-primary` | Cabeceras, barra de navegación, títulos principales, acentos de marca |
| **Primary Dark (Borgoña Profundo)** | `#5c1120` | `--color-primary-dark` | Topbars, sombras de profundidad, contraste alto |
| **Primary Light** | `#a3223b` | `--color-primary-light` | Estados interactivos, bordes destacados |
| **Secondary (Rojo Carmesí)** | `#9c1932` | `--color-secondary` | Botones de acción principal (CTA), enlaces destacados |
| **Secondary Hover** | `#731325` | `--color-secondary-hover` | Estado hover de botones primarios |
| **Accent / Dorado Institucional** | `#ffd700` | `--color-gold` | Insignias, iconos conmemorativos, detalles de excelencia |
| **Dorado Oscuro** | `#d97706` | `--color-gold-dark` | Texto legible sobre fondos claros |
| **Nav Text** | `#e8e8e6` | `--color-nav-text` | Tipografía en header y footer sobre fondo borgoña |
| **Fondo de Página** | `#f8f9fa` | `--color-bg-page` | Fondo global limpio institucional |
| **Superficie / Tarjetas** | `#ffffff` | `--color-bg-surface` | Fondo de cards, modales, formularios y tablas |
| **Superficie Hover** | `#f1f5f9` | `--color-bg-surface-hover` | Hover en filas de tablas e items de menú |
| **Superficie Muted** | `#f8f9fc` | `--color-bg-muted` | Paneles secundarios, barras de filtros |
| **Borde Estándar** | `#e2e8f0` | `--color-border` | Divisores, contorno de inputs y tarjetas |
| **Borde Enfoque (Focus)** | `#9c1932` | `--color-border-focus` | Anillo accesible `:focus-visible` |
| **Texto Principal** | `#1e293b` | `--color-text-main` | Slate 900 (Contraste 11.2:1 con fondo blanco) |
| **Texto Secundario (Muted)** | `#475569` | `--color-text-muted` | Slate 600 (Contraste 5.6:1 con fondo blanco) |
| **Texto Inverso** | `#ffffff` | `--color-text-inverse` | Texto sobre fondos oscuros o primarios |

### Badges Semánticos de Estado y Roles (WCAG AAA >= 7.0:1)
- **Activo / Aceptada:** Fondo `#ecfdf5`, Texto `#065f46`, Borde `#a7f3d0`
- **Inactivo / Rechazada:** Fondo `#fef2f2`, Texto `#991b1b`, Borde `#fecaca`
- **Pendiente / Warning:** Fondo `#fffbeb`, Texto `#92400e`, Borde `#fde68a`
- **En Revisión / Info:** Fondo `#eff6ff`, Texto `#1e40af`, Borde `#bfdbfe`
- **Rol Admin:** Fondo `#fdf2f4`, Texto `#871a2f`, Borde `#fbcfe8`
- **Rol Editor:** Fondo `#fffbeb`, Texto `#92400e`, Borde `#fde68a`
- **Rol Usuario:** Fondo `#f1f5f9`, Texto `#334155`, Borde `#cbd5e1`

---

## 2. Tipografía y Jerarquía Fluida

- **Fuente de Lectura & UI:** `Plus Jakarta Sans`, sans-serif
- **Fuente de Cifras, Código & Badges:** `JetBrains Mono`, monospace
- **Escala Fluida:**
  - `H1 (Hero Principal):` `clamp(2.0rem, 5vw, 3.25rem)` — `line-height: 1.15`
  - `H2 (Títulos de Sección):` `clamp(1.5rem, 3.5vw, 2.25rem)` — `line-height: 1.25`
  - `H3 (Títulos de Tarjeta):` `clamp(1.15rem, 2vw, 1.45rem)` — `line-height: 1.3`
  - `Párrafo / Body:` `0.95rem` a `1.05rem` — `line-height: 1.6`
  - `Texto Pequeño / Metadatos:` `0.85rem` — `line-height: 1.4`

```css
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
```

---

## 3. Espaciado y Ritmo Visual (Sistema 4/8dp)

| Token | Valor | Uso Recomendado |
|---|---|---|
| `--space-xs` | `4px` | Espacio entre iconos pequeños y etiquetas |
| `--space-sm` | `8px` | Gaps en botones, chips y controles |
| `--space-md` | `16px` | Padding interno de inputs, espaciado estándar |
| `--space-lg` | `24px` | Padding de tarjetas, gaps de grid |
| `--space-xl` | `32px` | Separación entre módulos y secciones intermedias |
| `--space-2xl` | `48px` | Márgenes verticales de sección |
| `--space-3xl` | `64px` | Padding vertical del Hero y pie de página |

---

## 4. Elevaciones y Sombras Bento

| Nivel | Sombra | Uso |
|---|---|---|
| `--shadow-xs` | `0 1px 2px 0 rgba(0, 0, 0, 0.05)` | Botones planos, inputs en reposo |
| `--shadow-sm` | `0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.06)` | Tarjetas informativas en reposo |
| `--shadow-md` | `0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.04)` | Tarjetas destacadas, dropdowns |
| `--shadow-lg` | `0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.03)` | Modales, tarjetas en hover |
| `--shadow-xl` | `0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05)` | Diálogos de confirmación, previews |

---

## 5. Reglas de Accesibilidad y Usabilidad (UI/UX Pro Max)

1. **Contraste WCAG AA Garantizado:** Todo texto regular debe alcanzar un ratio ≥ 4.5:1 respecto a su fondo.
2. **Área Táctil Mínima:** Todo control interactivo (botones, inputs, selectores, enlaces de icono) debe tener un área táctil mínima de `44px x 44px`.
3. **Foco Visible (`:focus-visible`):** Ningún elemento interactivo debe tener `outline: none` sin un reemplazo visible. Se utiliza un anillo de `2px solid var(--color-border-focus)` con `outline-offset: 2px`.
4. **Iconos Contextuales:**
   - Si el icono es decorativo y acompaña un texto visible: `aria-hidden="true"`.
   - Si el icono es un botón de acción en solitario: `aria-label="Descripción de la acción"`.
5. **No Emojis como Iconos Estructurales:** Prohibido el uso de emojis (📁, ✏️, 🗑️) para controles del sistema; utilizar exclusivamente SVGs inline estandarizados Iconimate.
6. **Preferencia de Movimiento Reducido (`prefers-reduced-motion`):** Respetar la configuración del sistema para usuarios con sensibilidad al movimiento desactivando transiciones pesadas o acelerándolas a 0ms.
7. **Tablas Responsivas sin Desbordamiento:** Envoltura obligatoria con `.table-responsive` (`overflow-x: auto`) y celdas con `white-space: normal` para evitar roturas de diseño en móviles (`375px`).
