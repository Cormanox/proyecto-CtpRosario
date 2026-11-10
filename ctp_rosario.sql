-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 10-11-2026 a las 02:55:45
-- Versión del servidor: 8.0.46
-- Versión de PHP: 8.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ctp_rosario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sitio`
--

CREATE TABLE `configuracion_sitio` (
  `clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `categoria` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_sitio`
--

INSERT INTO `configuracion_sitio` (`clave`, `valor`, `categoria`, `descripcion`, `actualizado_en`) VALUES
('colegio_nombre', 'CTP Rosario', 'general', 'Nombre abreviado institucional', '2026-11-09 20:37:22'),
('colegio_nombre_largo', 'Colegio Técnico Profesional Rosario', 'general', 'Nombre oficial completo', '2026-11-09 20:37:22'),
('correo_oficial', 'ctp.elrosariodenaranjo@mep.go.cr', 'contacto', 'Correo electrónico institucional', '2026-11-09 20:37:22'),
('direccion_oficial', 'Naranjo, 100 metros sur de la Escuela el Rosario.', 'contacto', 'Ubicación física del colegio', '2026-11-09 20:37:22'),
('hero_cta_matricula', 'Solicitar Pre-Matrícula', 'hero', 'Texto del botón principal de acción', '2026-11-09 20:37:22'),
('hero_cta_oferta', 'Conocer Especialidades', 'hero', 'Texto del botón secundario', '2026-11-09 20:37:22'),
('hero_subtitulo', 'Innovación, educación técnica de calidad y oportunidades para construir tu futuro profesional.', 'hero', 'Subtítulo descriptivo del banner', '2026-11-09 20:37:22'),
('hero_titulo', 'Bienvenidos al Colegio Técnico Profesional Rosario', 'hero', 'Título principal del banner de bienvenida', '2026-11-09 20:37:22'),
('horario_atencion', 'Lunes a Viernes: 7:00 AM - 4:30 PM', 'contacto', 'Horario de recepción y oficinas', '2026-11-09 20:37:22'),
('mision', 'Entregar a la sociedad costarricense, un ser integral donde se una el desarrollo intelectual con el desarrollo físico, espiritual y personal, logrando en ese ser, gracias al proceso de enseñanza-aprendizaje una actitud crítica, de urbanidad y disciplina. Formando así hombres y mujeres que conozcan y practiquen los principios democráticos, familiares y éticos del Estado costarricense.\r\nEste proceso conlleva una acción conjunta entre Docentes, Padres de Familia y Alumnos, como un medio para asegurar el bienestar estudiantil, la institucionalidad y la formación integral del educando.', 'institucional', 'Misión oficial del colegio', '2026-11-09 20:37:22'),
('periodo_lectivo_actual', '2027', 'matricula', 'Año o período lectivo vigente', '2026-11-09 20:37:22'),
('prematricula_abierta', '1', 'matricula', 'Estado del período de pre-matrícula (1 = Abierta, 0 = Cerrada)', '2026-11-09 20:37:22'),
('prematricula_mensaje_cierre', 'El período oficial de recepción de solicitudes de pre-matrícula se encuentra temporalmente cerrado. Manténgase atento a nuestros comunicados oficiales para la apertura de nuevas convocatorias.', 'matricula', 'Mensaje visible cuando la pre-matrícula está inactiva', '2026-11-09 20:37:22'),
('red_facebook', 'https://www.facebook.com/ctprosariodenaranjo/?locale=es_LA', 'redes', 'URL oficial de Facebook', '2026-11-09 20:37:22'),
('red_instagram', 'https://www.instagram.com/ctprosariodenaranjooficial/?hl=es', 'redes', 'URL oficial de Instagram', '2026-11-09 20:37:22'),
('red_youtube', 'https://youtube.com/@ctprosario', 'redes', 'Canal oficial de YouTube', '2026-11-09 20:37:22'),
('resena_historica', 'Fundado con el propósito de brindar educación técnica diversificada de calidad en Rosario de Naranjo, el CTP Rosario ha formado generaciones de técnicos altamente capacitados para incorporarse con éxito al sector productivo nacional y a la educación superior.', 'institucional', 'Reseña histórica del centro educativo', '2026-11-09 20:37:22'),
('telefono_oficial', '+506 2101-4017', 'contacto', 'Teléfono principal de atención', '2026-11-09 20:37:22'),
('vision', 'Proyectar al Colegio Técnico Profesional Rosario de Naranjo como una institución que promueva valores en el ámbito ético, académico, cultural, deportivo y tecnológico; para lo cual es fundamental formar un ciudadano integral que contribuya con el mejoramiento de la calidad de vida de la comunidad en general, así como de su entorno natural y social.', 'institucional', 'Visión oficial del colegio', '2026-11-09 20:37:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `id` int NOT NULL,
  `nombre_remitente` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo_remitente` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asunto` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `leido` tinyint(1) DEFAULT '0' COMMENT '0: No leído, 1: Leído',
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`id`, `nombre_remitente`, `correo_remitente`, `telefono`, `asunto`, `mensaje`, `leido`, `fecha_envio`) VALUES
(1, 'Alejandro Morales', 'alejandro@ejemplo.com', '8765-4321', 'Consulta sobre horarios de transporte estudiantil', 'Buenas tardes, quisiera consultar si para el curso lectivo 2026 cuentan con servicio de buseta desde el centro de Rosario.', 0, '2026-10-12 19:11:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id` int NOT NULL,
  `nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_corta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `perfil_egresado` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'graduation-cap',
  `orden` int NOT NULL DEFAULT '0',
  `activa` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: Activa, 0: Inactiva',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id`, `nombre`, `slug`, `descripcion_corta`, `perfil_egresado`, `icono`, `orden`, `activa`, `creado_en`) VALUES
(1, 'Informática en Desarrollo de Software', 'desarrollo-de-software', 'Aprende diseño, programación y mantenimiento de aplicaciones web, móviles y bases de datos con los estándares y lenguajes modernos de la industria.', 'Programación web (HTML, CSS, JS, PHP, Python)\nGestión de Bases de Datos relacionales (MySQL, SQL)\nMetodologías ágiles y proyectos colaborativos', 'code', 1, 1, '2026-11-09 20:09:59'),
(2, 'Contabilidad y Finanzas', 'contabilidad-y-finanzas', 'Domina los principios contables, gestión tributaria, auditoría básica, nómina y uso de software contable especializado para empresas públicas y privadas.', 'Ciclo contable completo y estados financieros\nLegislación tributaria y laboral vigente\nSistemas automatizados de gestión contable', 'calculator', 2, 1, '2026-11-09 20:09:59'),
(3, 'Electrónica Industrial', 'electronica-industrial', 'Capacitación en automatización, control de motores, sistemas embebidos, robótica, sensores y mantenimiento preventivo de líneas industriales.', 'Controladores lógicos programables (PLC)\nCircuitos digitales y electrónica de potencia\nDiagnóstico y reparación de equipo electrónico', 'microchip', 3, 1, '2026-11-09 20:09:59'),
(4, 'Secretariado Ejecutivo', 'secretariado-ejecutivo', 'Desarrolla habilidades en administración de oficinas modernas, comunicación corporativa, servicio al cliente bilingüe y redacción comercial ejecutiva.', 'Gestión documental y ofimática avanzada\nProtocolo empresarial y relaciones públicas\nManejo de agendas y organización de eventos', 'briefcase', 4, 1, '2026-11-09 20:09:59'),
(5, 'Mecánica de Precisión', 'mecanica-de-precision', 'Formación en mecanizado con tornos, fresadoras, diseño asistido por computadora (CAD/CAM) y control numérico computarizado (CNC).', 'Mecanizado convencional y CNC\nMetrología y control de calidad dimensional\nInterpretación y elaboración de planos técnicos', 'gears', 5, 1, '2026-11-09 20:09:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int NOT NULL,
  `titulo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagen_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('borrador','publicado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'publicado',
  `usuario_id` int NOT NULL,
  `fecha_publicacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `slug`, `contenido`, `imagen_url`, `estado`, `usuario_id`, `fecha_publicacion`) VALUES
(1, 'Inauguración del Ciclo Lectivo 2026', 'inauguracion-ciclo-lectivo-2026', 'Damos una cordial bienvenida a toda la comunidad estudiantil, cuerpo docente y familias a este nuevo año académico lleno de aprendizaje técnico y superación.', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&auto=format&fit=crop&q=80', 'publicado', 1, '2026-10-12 19:04:39'),
(2, 'Apertura del Proceso de Pre-Matrícula', 'apertura-proceso-pre-matricula', 'Ya se encuentra habilitado el formulario de pre-matrícula en línea para el ingreso a nuestras especialidades técnicas. ¡Asegura tu cupo con anticipación!', 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=600&auto=format&fit=crop&q=80', 'publicado', 1, '2026-10-12 19:04:39'),
(3, 'Feria Vocacional y Demostración de Proyectos', 'feria-vocacional-y-demostraciones', 'Los estudiantes de los últimos niveles presentarán sus proyectos de innovación tecnológica en las áreas de desarrollo de software, contabilidad y electrónica.', 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=600&auto=format&fit=crop&q=80', 'publicado', 1, '2026-10-12 19:04:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prematriculas`
--

CREATE TABLE `prematriculas` (
  `id` int NOT NULL,
  `cedula_estudiante` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_estudiante` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `primer_apellido` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `segundo_apellido` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `especialidad_interes` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_encargado` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono_contacto` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo_contacto` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_solicitud` enum('pendiente','en_revision','aceptada','rechazada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prematriculas`
--

INSERT INTO `prematriculas` (`id`, `cedula_estudiante`, `nombre_estudiante`, `primer_apellido`, `segundo_apellido`, `fecha_nacimiento`, `especialidad_interes`, `nombre_encargado`, `telefono_contacto`, `correo_contacto`, `estado_solicitud`, `observaciones`, `fecha_registro`) VALUES
(1, '1-1890-0432', 'Sofía', 'Castro', 'Vargas', '2008-04-15', 'Informática en Desarrollo de Software', 'María Elena Vargas', '8899-7766', 'maria.vargas@ejemplo.com', 'aceptada', 'Estudiante con promedio de honor e interés en programación web.', '2026-10-12 19:11:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('admin','editor','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `estado` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: Activo, 0: Inactivo',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol`, `estado`, `creado_en`) VALUES
(1, 'Administrador CTP', 'admin@ctprosario.ed.cr', '$2y$10$zWEjrG2Wg3QE66iFmqs3deWulAx4LyxY5lZGWPCvm1UE8ZGiiI3mO', 'admin', 1, '2026-10-07 22:22:38');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion_sitio`
--
ALTER TABLE `configuracion_sitio`
  ADD PRIMARY KEY (`clave`),
  ADD KEY `idx_config_categoria` (`categoria`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_consultas_leido` (`leido`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_especialidades_slug` (`slug`),
  ADD KEY `idx_especialidades_activa_orden` (`activa`,`orden`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_noticias_estado` (`estado`);

--
-- Indices de la tabla `prematriculas`
--
ALTER TABLE `prematriculas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prematriculas_cedula` (`cedula_estudiante`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_rol` (`rol`),
  ADD KEY `idx_usuarios_estado` (`estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `prematriculas`
--
ALTER TABLE `prematriculas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
