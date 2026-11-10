-- =============================================================================
-- MIGRACIÓN ADITIVA: AUTOGESTIÓN TOTAL Y CMS DEL CTP ROSARIO DE NARANJO
-- Base de datos: ctp_rosario
-- =============================================================================

USE `ctp_rosario`;

-- 1. TABLA: especialidades
CREATE TABLE IF NOT EXISTS `especialidades` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_corta` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `perfil_egresado` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'graduation-cap',
  `orden` INT NOT NULL DEFAULT 0,
  `activa` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Activa, 0: Inactiva',
  `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_especialidades_slug` (`slug`),
  KEY `idx_especialidades_activa_orden` (`activa`, `orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: configuracion_sitio (Clave-Valor)
CREATE TABLE IF NOT EXISTS `configuracion_sitio` (
  `clave` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `categoria` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `descripcion` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `actualizado_en` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`clave`),
  KEY `idx_config_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. POBLACIÓN INICIAL: 5 Especialidades Técnicas Existentes
INSERT INTO `especialidades` (`id`, `nombre`, `slug`, `descripcion_corta`, `perfil_egresado`, `icono`, `orden`, `activa`) VALUES
(1, 'Informática en Desarrollo de Software', 'desarrollo-de-software', 
 'Aprende diseño, programación y mantenimiento de aplicaciones web, móviles y bases de datos con los estándares y lenguajes modernos de la industria.',
 'Programación web (HTML, CSS, JS, PHP, Python)\nGestión de Bases de Datos relacionales (MySQL, SQL)\nMetodologías ágiles y proyectos colaborativos',
 'code', 1, 1),

(2, 'Contabilidad y Finanzas', 'contabilidad-y-finanzas', 
 'Domina los principios contables, gestión tributaria, auditoría básica, nómina y uso de software contable especializado para empresas públicas y privadas.',
 'Ciclo contable completo y estados financieros\nLegislación tributaria y laboral vigente\nSistemas automatizados de gestión contable',
 'calculator', 2, 1),

(3, 'Electrónica Industrial', 'electronica-industrial', 
 'Capacitación en automatización, control de motores, sistemas embebidos, robótica, sensores y mantenimiento preventivo de líneas industriales.',
 'Controladores lógicos programables (PLC)\nCircuitos digitales y electrónica de potencia\nDiagnóstico y reparación de equipo electrónico',
 'microchip', 3, 1),

(4, 'Secretariado Ejecutivo', 'secretariado-ejecutivo', 
 'Desarrolla habilidades en administración de oficinas modernas, comunicación corporativa, servicio al cliente bilingüe y redacción comercial ejecutiva.',
 'Gestión documental y ofimática avanzada\nProtocolo empresarial y relaciones públicas\nManejo de agendas y organización de eventos',
 'briefcase', 4, 1),

(5, 'Mecánica de Precisión', 'mecanica-de-precision', 
 'Formación en mecanizado con tornos, fresadoras, diseño asistido por computadora (CAD/CAM) y control numérico computarizado (CNC).',
 'Mecanizado convencional y CNC\nMetrología y control de calidad dimensional\nInterpretación y elaboración de planos técnicos',
 'gears', 5, 1)
ON DUPLICATE KEY UPDATE 
  `nombre` = VALUES(`nombre`),
  `descripcion_corta` = VALUES(`descripcion_corta`),
  `perfil_egresado` = VALUES(`perfil_egresado`),
  `icono` = VALUES(`icono`),
  `orden` = VALUES(`orden`),
  `activa` = VALUES(`activa`);

-- 4. POBLACIÓN INICIAL: Parámetros Institucionales Configurables
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
ON DUPLICATE KEY UPDATE 
  `valor` = VALUES(`valor`),
  `categoria` = VALUES(`categoria`),
  `descripcion` = VALUES(`descripcion`);

