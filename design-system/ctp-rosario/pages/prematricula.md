# Page Override: Pre-Matrícula — CTP Rosario

> Hereda de `design-system/ctp-rosario/MASTER.md`. Aplica a `public/prematricula.php`.

---

## 1. Composición y Estados de Apertura/Cierre
- **Verificación de Apertura del Período Lectivo:**
  - **Estado Cerrado (`prematricula_abierta = 0`):**
    - Despliega tarjeta de alerta accesible (`role="alert"`) con icono `archive` o `shield-check`.
    - Presenta el mensaje personalizado configurado por el administrador (`prematricula_mensaje_cierre`) y el período lectivo.
    - Oculta o deshabilita los controles de envío del formulario para evitar registros fuera de fecha.
    - Ofrece enlaces alternativos: Conocer la Oferta Académica (`public/oferta.php`) o Consultar a la Institución (`public/contacto.php`).
  - **Estado Abierto (`prematricula_abierta = 1`):**
    - Despliega el formulario completo dividido en bloques semánticos:
      1. *Datos del Estudiante*: Cédula, Nombre, Apellidos, Fecha de Nacimiento.
      2. *Especialidad Técnica de Interés*: Dropdown poblado dinámicamente con solo las especialidades con `activa = 1`.
      3. *Datos del Encargado Legal*: Nombre completo, Teléfono, Correo Electrónico.
      4. *Observaciones o Condición Especial*.
- **Preselección Inteligente:**
  - Si la URL incluye `?especialidad=<slug>` o nombre, el formulario selecciona automáticamente la especialidad y muestra un resumen interactivo con badge y descripción.

## 2. Accesibilidad y Validación
- Validación en tiempo real y foco accesible en el primer campo inválido con sacudida visual suave (`x: [-6, 6, -4, 4, 0]`).
- Tokens CSRF obligatorios en el formulario con protección de doble envío.
- Confirmación de éxito con número de expediente generado y persistido en BD.

