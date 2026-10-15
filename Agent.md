# AGENTS.md — Portal Web Institucional del CTP Rosario

## 1. Información General del Proyecto
- **Proyecto:** Desarrollo del Portal Web Institucional del CTP Rosario de Naranjo[cite: 1].
- **Nombre Corto:** Portal Web CTP Rosario de Naranjo[cite: 1].
- **Institución:** CTP Rosario de Naranjo[cite: 1].
- **Área / Subárea:** Desarrollo Web / Diseño de software – T.I (Nivel: 11°)[cite: 1].
- **Docente Responsable:** Ronald A. Zamora Acosta[cite: 1].
- **Año:** 2026[cite: 1].

---

## 2. Rol y Directrices del Agente
Eres un asistente de ingeniería de software e instructor técnico guiando el desarrollo del **Portal Web Institucional del CTP Rosario**[cite: 1].
- **Objetivo Principal:** Apoyar en el análisis, diseño, programación frontend/backend, modelado de bases de datos, seguridad, pruebas y documentación para entregar una solución web completa, responsive, accesible y segura[cite: 1].
- **Enfoque de Calidad:** Tratar el software como un **producto tecnológico institucional**, estructurado limpiamente para futuras ampliaciones y manteniendo buenas prácticas de código[cite: 1].
- **Privacidad y Datos Sensibles:** Utilizar exclusivamente datos de prueba o ficticios durante el desarrollo académico[cite: 1]. No incluir información personal real de estudiantes, encargados o funcionarios sin previa autorización formal[cite: 1].

---

## 3. Alcance y Arquitectura del Sistema

### 3.1. Área Pública (Visitantes y Comunidad)[cite: 1]
- **Inicio:** Presentación general del colegio, accesos directos y avisos destacados[cite: 1].
- **Información Institucional:** Historia, misión, visión, valores y medios de contacto[cite: 1].
- **Oferta Educativa:** Detalle de especialidades técnicas y opciones curriculares[cite: 1].
- **Noticias y Comunicados:** Publicación cronológica de información institucional[cite: 1].
- **Actividades:** Calendario o listado de eventos y fechas importantes[cite: 1].
- **Galería:** Fotografías de eventos e infraestructura[cite: 1].
- **Formularios Públicos:**
  - **Consultas:** Formulario de contacto y recepción de mensajes[cite: 1].
  - **Pre-matrícula:** Formulario para recopilar información de aspirantes[cite: 1].
- **Ubicación y Contacto:** Medios de contacto, mapa y ubicación física[cite: 1].

### 3.2. Área Administrativa (Gestión con Autenticación)[cite: 1]
- **Autenticación y Roles:** Inicio de sesión seguro con control de acceso por roles/permisos[cite: 1].
- **Gestión de Contenidos:** CRUD (crear, leer, actualizar, eliminar) para noticias, comunicados, actividades, especialidades, galería e información general[cite: 1].
- **Bandeja de Formularios:**
  - Consulta y administración de solicitudes de pre-matrícula[cite: 1].
  - Consulta y gestión de mensajes de consulta recibidos[cite: 1].
- **Gestión de Usuarios:** Administración de cuentas con permisos de edición[cite: 1].

---

## 4. Requerimientos del Sistema

### 4.1. Requerimientos Funcionales (RF)
- `RF-01` **Página principal:** Presentar información clave y enlaces organizados[cite: 1].
- `RF-02` **Navegación:** Menú principal claro, intuitivo y responsivo[cite: 1].
- `RF-03` **Información institucional:** Mostrar identidad del centro educativo[cite: 1].
- `RF-04` **Oferta educativa:** Desplegar especialidades y programas formativos[cite: 1].
- `RF-05` **Noticias y comunicados:** Publicación y actualización de novedades[cite: 1].
- `RF-06` **Actividades:** Visualización de eventos y fechas cívicas/académicas[cite: 1].
- `RF-07` **Galería:** Muestra organizada de contenido multimedia/fotográfico[cite: 1].
- `RF-08` **Formulario de consultas:** Envío de inquietudes por parte del usuario[cite: 1].
- `RF-09` **Pre-matrícula:** Recolección ordenada de datos de interesados[cite: 1].
- `RF-10` **Almacenamiento:** Persistencia de formularios y contenidos en base de datos[cite: 1].
- `RF-11` **Administración:** Operaciones de gestión de contenido para usuarios autorizados[cite: 1].
- `RF-12` **Autenticación:** Login seguro con protección de rutas administrativas[cite: 1].
- `RF-13` **Roles:** Niveles de acceso diferenciados para operadores y administradores[cite: 1].
- `RF-14` **Consulta de información:** Visualización de registros de consultas y pre-matrículas recibidas[cite: 1].
- `RF-15` **Validación:** Validación estricta tanto en cliente como en servidor[cite: 1].

### 4.2. Requerimientos No Funcionales (RNF)
- `RNF-01` **Usabilidad:** Experiencia intuitiva, legible y accesible[cite: 1].
- `RNF-02` **Diseño Responsive:** Adaptabilidad fluida a móviles, tabletas y computadoras de escritorio[cite: 1].
- `RNF-03` **Compatibilidad:** Correcto despliegue y funcionalidad en los principales navegadores web[cite: 1].
- `RNF-04` **Seguridad:** Encriptación de contraseñas, sanitización contra inyecciones SQL/XSS y protección de rutas privadas[cite: 1].
- `RNF-05` **Rendimiento:** Tiempos de carga reducidos, compresión de activos y código optimizado[cite: 1].
- `RNF-06` **Mantenibilidad:** Código modular, estructurado, limpio y documentado[cite: 1].
- `RNF-07` **Accesibilidad:** Estructuras semánticas HTML5, contrastes legibles y soporte accesible[cite: 1].
- `RNF-08` **Integridad de datos:** Normalización y restricciones de integridad en base de datos[cite: 1].

---

## 5. Módulos del Sistema
1. **Módulo 1: Inicio** (Home y portada institucional)[cite: 1].
2. **Módulo 2: Información Institucional** (Historia, visión, misión, valores)[cite: 1].
3. **Módulo 3: Oferta Educativa** (Especialidades técnicas)[cite: 1].
4. **Módulo 4: Noticias y Comunicados** (Blog / Tablero informativo)[cite: 1].
5. **Módulo 5: Actividades** (Eventos y agenda)[cite: 1].
6. **Módulo 6: Galería** (Álbumes y fotografías)[cite: 1].
7. **Módulo 7: Pre-matrícula** (Recepción y procesamiento de solicitudes)[cite: 1].
8. **Módulo 8: Consultas** (Bandeja de contacto)[cite: 1].
9. **Módulo 9: Administración** (Panel de control/CMS)[cite: 1].
10. **Módulo 10: Usuarios y Permisos** (Control de acceso y credenciales)[cite: 1].
11. **Módulo 11: Base de Datos** (Esquema relacional y migraciones/scripts)[cite: 1].
12. **Módulo 12: Seguridad** (Autenticación, validación dual cliente-servidor y control de sesiones)[cite: 1].

---

## 6. Stack Tecnológico Aceptado
- **Frontend:** HTML5 semántico, CSS3 (Responsive/Flexbox/Grid/Frameworks si aplica), JavaScript estándar[cite: 1].
- **Backend:** Conforme a las herramientas vistas en el curso (Node.js, PHP, Python, etc.) implementando una arquitectura ordenada (MVC o API REST)[cite: 1].
- **Base de Datos:** Gestor de bases de datos relacional (MySQL / MariaDB / PostgreSQL) con script DDL/DML estructurado[cite: 1].
- **Herramientas de Apoyo:** Git para control de versiones, editores de código, herramientas de prototipado/diseño y suites de pruebas[cite: 1].

---

## 7. Fases de Desarrollo y Entregables

| Etapa[cite: 1] | Foco de Trabajo[cite: 1] | Entregables Clave Asociados[cite: 1] |
|---|---|---|
| **Etapa 1: Análisis**[cite: 1] | Identificación del problema, usuarios, necesidades y alcance[cite: 1]. | **Entregable 1:** Documento de análisis (requerimientos y objetivos)[cite: 1]. |
| **Etapa 2: Diseño**[cite: 1] | Mapa de sitio, wireframes, diagramas de BD y arquitectura[cite: 1]. | **Entregable 2:** Diseño UI/UX y modelo entidad-relación[cite: 1]. |
| **Etapa 3: Desarrollo**[cite: 1] | Codificación frontend, backend, BD y panel administrativo[cite: 1]. | **Entregable 3:** Script de Base de Datos con datos de prueba[cite: 1]. |
| **Etapa 4: Integración**[cite: 1] | Enlace de módulos en un único sistema unificado y funcional[cite: 1]. | **Entregable 4:** Portal Web público funcional[cite: 1].<br>**Entregable 5:** Panel Administrativo funcional[cite: 1]. |
| **Etapa 5: Pruebas**[cite: 1] | Ejecución de casos de prueba, corrección de bugs y seguridad[cite: 1]. | **Entregable 6:** Documento de pruebas, resultados y correcciones[cite: 1]. |
| **Etapa 6: Documentación**[cite: 1] | Manuales y especificación técnica[cite: 1]. | **Entregable 7:** Documentación técnica[cite: 1].<br>**Entregable 8:** Manual de usuario[cite: 1]. |
| **Etapa 7: Presentación**[cite: 1] | Demostración funcional y defensa técnica[cite: 1]. | **Entregable 9:** Presentación y defensa técnica final[cite: 1]. |

---

## 8. Criterios de Evaluación y Calidad

### Distribución de Calificación (Total 100%)[cite: 1]
- **Análisis y definición del proyecto:** 10%[cite: 1]
- **Diseño de interfaz y experiencia de usuario (UX/UI):** 10%[cite: 1]
- **Desarrollo frontend:** 15%[cite: 1]
- **Desarrollo backend:** 15%[cite: 1]
- **Base de datos:** 10%[cite: 1]
- **Funcionalidad e integración:** 15%[cite: 1]
- **Seguridad y validación:** 10%[cite: 1]
- **Pruebas y corrección de errores:** 5%[cite: 1]
- **Documentación:** 5%[cite: 1]
- **Presentación y defensa:** 5%[cite: 1]

### Estándar de Desempeño
- **Excelente:** Sistema totalmente funcional, interfaz profesional, responsive, BD optimizada con integridad referencial, seguridad implementada contra vulnerabilidades comunes, pruebas completas y documentación exhaustiva[cite: 1].