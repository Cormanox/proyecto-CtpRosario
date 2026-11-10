-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 10, 2026 at 02:54 AM
-- Server version: 8.0.46
-- PHP Version: 8.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ctp_rosario`
--

-- --------------------------------------------------------

--
-- Table structure for table `consultas`
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

-- --------------------------------------------------------

--
-- Table structure for table `noticias`
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

-- --------------------------------------------------------

--
-- Table structure for table `prematriculas`
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

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
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
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol`, `estado`, `creado_en`) VALUES
(1, 'Administrador CTP', 'admin@ctprosario.ed.cr', '2y10$w0M89vS3w2/7bY8AAn994e4a0Z/a0YQ.O/0MvQe2O1V4.L99Z5q0a', 'admin', 1, '2026-10-07 22:22:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_consultas_leido` (`leido`);

--
-- Indexes for table `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_noticias_estado` (`estado`);

--
-- Indexes for table `prematriculas`
--
ALTER TABLE `prematriculas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prematriculas_cedula` (`cedula_estudiante`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_rol` (`rol`),
  ADD KEY `idx_usuarios_estado` (`estado`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prematriculas`
--
ALTER TABLE `prematriculas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Table structure for table `especialidades`
--

CREATE TABLE IF NOT EXISTS `especialidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_corta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `perfil_egresado` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'graduation-cap',
  `orden` int NOT NULL DEFAULT '0',
  `activa` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: Activa, 0: Inactiva',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_especialidades_slug` (`slug`),
  KEY `idx_especialidades_activa_orden` (`activa`,`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `especialidades`
--

INSERT INTO `especialidades` (`id`, `nombre`, `slug`, `descripcion_corta`, `perfil_egresado`, `icono`, `orden`, `activa`) VALUES
(1, 'Informática en Desarrollo de Software', 'desarrollo-de-software', 'Aprende diseño, programación y mantenimiento de aplicaciones web, móviles y bases de datos con los estándares y lenguajes modernos de la industria.', 'Programación web (HTML, CSS, JS, PHP, Python)\nGestión de Bases de Datos relacionales (MySQL, SQL)\nMetodologías ágiles y proyectos colaborativos', 'code', 1, 1),
(2, 'Contabilidad y Finanzas', 'contabilidad-y-finanzas', 'Domina los principios contables, gestión tributaria, auditoría básica, nómina y uso de software contable especializado para empresas públicas y privadas.', 'Ciclo contable completo y estados financieros\nLegislación tributaria y laboral vigente\nSistemas automatizados de gestión contable', 'calculator', 2, 1),
(3, 'Electrónica Industrial', 'electronica-industrial', 'Capacitación en automatización, control de motores, sistemas embebidos, robótica, sensores y mantenimiento preventivo de líneas industriales.', 'Controladores lógicos programables (PLC)\nCircuitos digitales y electrónica de potencia\nDiagnóstico y reparación de equipo electrónico', 'microchip', 3, 1),
(4, 'Secretariado Ejecutivo', 'secretariado-ejecutivo', 'Desarrolla habilidades en administración de oficinas modernas, comunicación corporativa, servicio al cliente bilingüe y redacción comercial ejecutiva.', 'Gestión documental y ofimática avanzada\nProtocolo empresarial y relaciones públicas\nManejo de agendas y organización de eventos', 'briefcase', 4, 1),
(5, 'Mecánica de Precisión', 'mecanica-de-precision', 'Formación en mecanizado con tornos, fresadoras, diseño asistido por computadora (CAD/CAM) y control numérico computarizado (CNC).', 'Mecanizado convencional y CNC\nMetrología y control de calidad dimensional\nInterpretación y elaboración de planos técnicos', 'gears', 5, 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- --------------------------------------------------------

--
-- Table structure for table `configuracion_sitio`
--

CREATE TABLE IF NOT EXISTS `configuracion_sitio` (
  `clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `categoria` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`clave`),
  KEY `idx_config_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuracion_sitio`
--

INSERT INTO `configuracion_sitio` (`clave`, `valor`, `categoria`, `descripcion`) VALUES
('colegio_nombre', 'CTP Rosario', 'general', 'Nombre abreviado institucional'),
('colegio_nombre_largo', 'Colegio Técnico Profesional Rosario', 'general', 'Nombre oficial completo'),
('hero_titulo', 'Bienvenidos al Colegio Técnico Profesional Rosario', 'hero', 'Título principal del banner de bienvenida'),
('hero_subtitulo', 'Innovación, educación técnica de calidad y oportunidades para construir tu futuro profesional.', 'hero', 'Subtítulo descriptivo del banner'),
('hero_cta_matricula', 'Solicitar Pre-Matrícula', 'hero', 'Texto del botón principal de acción'),
('hero_cta_oferta', 'Conocer Especialidades', 'hero', 'Texto del botón secundario'),
('mision', 'Formar profesionales técnicos competentes, éticos e innovadores, capaces de liderar el desarrollo productivo y tecnológico de la comunidad y del país.', 'institucional', 'Misión oficial del colegio'),
('vision', 'Ser la institución educativa técnico-profesional líder de la región de Occidente, reconocida por la excelencia académica, pertinencia formativa y sólida vinculación empresarial.', 'institucional', 'Visión oficial del colegio'),
('resena_historica', 'Fundado con el propósito de brindar educación técnica diversificada de calidad en Rosario de Naranjo, el CTP Rosario ha formado generaciones de técnicos altamente capacitados para incorporarse con éxito al sector productivo nacional y a la educación superior.', 'institucional', 'Reseña histórica del centro educativo'),
('prematricula_abierta', '1', 'matricula', 'Estado del período de pre-matrícula (1 = Abierta, 0 = Cerrada)'),
('prematricula_mensaje_cierre', 'El período oficial de recepción de solicitudes de pre-matrícula se encuentra temporalmente cerrado. Manténgase atento a nuestros comunicados oficiales para la apertura de nuevas convocatorias.', 'matricula', 'Mensaje visible cuando la pre-matrícula está inactiva'),
('periodo_lectivo_actual', '2027', 'matricula', 'Año o período lectivo vigente'),
('telefono_oficial', '+506 2451-0000', 'contacto', 'Teléfono principal de atención'),
('correo_oficial', 'info@ctprosario.ed.cr', 'contacto', 'Correo electrónico institucional'),
('direccion_oficial', 'Rosario de Naranjo, Alajuela, Costa Rica', 'contacto', 'Ubicación física del colegio'),
('horario_atencion', 'Lunes a Viernes: 7:00 AM - 4:30 PM', 'contacto', 'Horario de recepción y oficinas'),
('red_facebook', 'https://facebook.com/ctprosario', 'redes', 'URL oficial de Facebook'),
('red_instagram', 'https://instagram.com/ctprosario', 'redes', 'URL oficial de Instagram'),
('red_youtube', 'https://youtube.com/@ctprosario', 'redes', 'Canal oficial de YouTube')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
