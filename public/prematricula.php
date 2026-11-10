<?php
declare(strict_types=1);

$pageTitle = "Solicitud de Pre-Matrícula";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

// Cargar estado de la convocatoria desde configuración institucional
$prematriculaAbierta  = obtener_config('prematricula_abierta', '1') === '1';
$mensajeCierre        = obtener_config('prematricula_mensaje_cierre', 'El período oficial de recepción de solicitudes de pre-matrícula se encuentra cerrado.');
$periodoLectivo       = obtener_config('periodo_lectivo_actual', '2027');

$mensaje = '';
$tipoMensaje = '';

// Consultar especialidades técnicas activas desde la base de datos
try {
    $stmtEsp = $pdo->query("SELECT id, nombre, slug, descripcion_corta, perfil_egresado, icono FROM especialidades WHERE activa = 1 ORDER BY orden ASC, nombre ASC");
    $especialidadesDisponibles = $stmtEsp->fetchAll();
} catch (PDOException $e) {
    error_log("[Prematricula Specialties Load Error] " . $e->getMessage());
    $especialidadesDisponibles = [];
}

// Mapear especialidades por slug y por nombre para facilitar preselección
$especialidadesMap = [];
foreach ($especialidadesDisponibles as $esp) {
    $especialidadesMap[strtolower($esp['slug'])] = $esp;
    $especialidadesMap[strtolower($esp['nombre'])] = $esp;
}

// Parámetro GET para preselección desde oferta académica
$especialidadGet = trim((string)($_GET['especialidad'] ?? ''));
$especialidadPreseleccionada = '';
$datosEspecialidadSeleccionada = null;

if ($especialidadGet !== '') {
    $keyLower = strtolower($especialidadGet);
    if (isset($especialidadesMap[$keyLower])) {
        $datosEspecialidadSeleccionada = $especialidadesMap[$keyLower];
        $especialidadPreseleccionada = $datosEspecialidadSeleccionada['nombre'];
    }
}

$cedula = $nombre = $primerApellido = $segundoApellido = $fechaNacimiento = '';
$especialidad = $especialidadPreseleccionada;
$nombreEncargado = $telefonoContacto = $correoContacto = $observaciones = '';

// Procesamiento del formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$prematriculaAbierta) {
        $mensaje = 'El período de pre-matrícula no se encuentra habilitado para envíos en este momento.';
        $tipoMensaje = 'danger';
    } else {
        $submittedToken = (string)($_POST['csrf_token'] ?? '');
        if (!validar_csrf($submittedToken)) {
            $mensaje = 'Token de seguridad inválido o expirado. Por favor recargue el formulario e intente de nuevo.';
            $tipoMensaje = 'danger';
        } else {
            $cedula            = trim((string)($_POST['cedula_estudiante'] ?? ''));
            $nombre            = trim((string)($_POST['nombre_estudiante'] ?? ''));
            $primerApellido    = trim((string)($_POST['primer_apellido'] ?? ''));
            $segundoApellido   = trim((string)($_POST['segundo_apellido'] ?? ''));
            $fechaNacimiento   = trim((string)($_POST['fecha_nacimiento'] ?? ''));
            $especialidad      = trim((string)($_POST['especialidad_interes'] ?? ''));
            $nombreEncargado   = trim((string)($_POST['nombre_encargado'] ?? ''));
            $telefonoContacto  = trim((string)($_POST['telefono_contacto'] ?? ''));
            $correoContacto    = trim((string)($_POST['correo_contacto'] ?? ''));
            $observaciones     = trim((string)($_POST['observaciones'] ?? ''));

            if (empty($cedula) || empty($nombre) || empty($primerApellido) || empty($fechaNacimiento) || empty($especialidad) || empty($nombreEncargado) || empty($telefonoContacto) || empty($correoContacto)) {
                $mensaje = 'Por favor, completa todos los campos requeridos marcados con asterisco (*).';
                $tipoMensaje = 'danger';
            } elseif (!filter_var($correoContacto, FILTER_VALIDATE_EMAIL)) {
                $mensaje = 'Por favor, introduce una dirección de correo electrónico válida.';
                $tipoMensaje = 'danger';
            } else {
                try {
                    $sql = "INSERT INTO prematriculas (
                                cedula_estudiante, nombre_estudiante, primer_apellido, segundo_apellido,
                                fecha_nacimiento, especialidad_interes, nombre_encargado, telefono_contacto,
                                correo_contacto, estado_solicitud, observaciones, fecha_registro
                            ) VALUES (
                                :cedula, :nombre, :primer_apellido, :segundo_apellido,
                                :fecha_nacimiento, :especialidad, :nombre_encargado, :telefono,
                                :correo, 'pendiente', :observaciones, NOW()
                            )";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':cedula'          => $cedula,
                        ':nombre'          => $nombre,
                        ':primer_apellido' => $primerApellido,
                        ':segundo_apellido'=> $segundoApellido,
                        ':fecha_nacimiento'=> $fechaNacimiento,
                        ':especialidad'    => $especialidad,
                        ':nombre_encargado'=> $nombreEncargado,
                        ':telefono'        => $telefonoContacto,
                        ':correo'          => $correoContacto,
                        ':observaciones'   => $observaciones
                    ]);

                    $idSolicitud = (int)$pdo->lastInsertId();
                    $mensaje = "¡Solicitud de pre-matrícula registrada exitosamente! Tu número de expediente es: #{$idSolicitud}. La administración revisará tus datos y se pondrá en contacto al correo o teléfono proporcionados.";
                    $tipoMensaje = 'success';

                    // Limpiar variables tras éxito
                    $cedula = $nombre = $primerApellido = $segundoApellido = $fechaNacimiento = $especialidad = $nombreEncargado = $telefonoContacto = $correoContacto = $observaciones = '';
                    $datosEspecialidadSeleccionada = null;
                } catch (PDOException $e) {
                    error_log("[Prematricula Insert Error] " . $e->getMessage());
                    $mensaje = 'Ocurrió un error al procesar tu solicitud. Por favor intenta de nuevo más tarde o comunícate con secretaría.';
                    $tipoMensaje = 'danger';
                }
            }
        }
    }
}
?>

<div class="page-header">
    <div class="container">
        <h2>Formulario de Pre-Matrícula <?= e($periodoLectivo); ?></h2>
        <p>Inicia tu proceso de postulación a nuestras especialidades técnicas para el próximo ciclo lectivo.</p>
    </div>
</div>

<main class="container" style="margin-top: 35px; margin-bottom: 60px;">

    <?php if (!$prematriculaAbierta): ?>
        <!-- BANNER DE CONVOCATORIA CERRADA -->
        <div class="enrollment-banner" role="alert">
            <div class="enrollment-banner-icon">
                <?= icon('archive', ['class' => 'icon-xl']); ?>
            </div>
            <div class="enrollment-banner-content">
                <h3>Convocatoria de Pre-Matrícula Temporalmente Cerrada</h3>
                <p><?= nl2br(e($mensajeCierre)); ?></p>
                <div style="margin-top: 18px; display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?= url('public/oferta.php'); ?>" class="btn btn-primary" style="padding: 10px 20px;">
                        <?= icon('graduation-cap', ['class' => 'icon-sm']); ?> Conocer Especialidades
                    </a>
                    <a href="<?= url('public/contacto.php'); ?>" class="btn btn-outline" style="padding: 10px 20px;">
                        <?= icon('envelope', ['class' => 'icon-sm']); ?> Contactar al Colegio
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>

        <div class="form-card">
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= e($tipoMensaje); ?>" role="alert">
                    <?= ($tipoMensaje === 'success') ? icon('check-circle', ['class' => 'icon-md']) : icon('circle-exclamation', ['class' => 'icon-md']); ?>
                    <div><?= e($mensaje); ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= url('public/prematricula.php'); ?>" method="POST" id="prematriculaForm">
                <?= csrf_field(); ?>

                <!-- BLOQUE 1: DATOS DEL ESTUDIANTE -->
                <h3 style="color: var(--color-primary); font-weight: 800; margin-bottom: 20px; border-bottom: 2px solid var(--color-border); padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <?= icon('user', ['class' => 'icon-md']); ?> Datos del Estudiante Postulante
                </h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="cedula_estudiante">Cédula / Documento de Identidad *</label>
                        <input type="text" id="cedula_estudiante" name="cedula_estudiante" class="form-control" required value="<?= e($cedula); ?>" placeholder="Ej: 1-1234-5678">
                    </div>

                    <div class="form-group">
                        <label for="nombre_estudiante">Nombre del Estudiante *</label>
                        <input type="text" id="nombre_estudiante" name="nombre_estudiante" class="form-control" required value="<?= e($nombre); ?>" placeholder="Ej: Carlos">
                    </div>

                    <div class="form-group">
                        <label for="primer_apellido">Primer Apellido *</label>
                        <input type="text" id="primer_apellido" name="primer_apellido" class="form-control" required value="<?= e($primerApellido); ?>" placeholder="Ej: Rodríguez">
                    </div>

                    <div class="form-group">
                        <label for="segundo_apellido">Segundo Apellido</label>
                        <input type="text" id="segundo_apellido" name="segundo_apellido" class="form-control" value="<?= e($segundoApellido); ?>" placeholder="Ej: Morales">
                    </div>

                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" required value="<?= e($fechaNacimiento); ?>">
                    </div>

                    <div class="form-group">
                        <label for="especialidad_interes">Especialidad Técnica de Interés *</label>
                        <select id="especialidad_interes" name="especialidad_interes" class="form-control" required onchange="actualizarResumenEspecialidad(this.value)">
                            <option value="">-- Selecciona una especialidad técnica --</option>
                            <?php foreach ($especialidadesDisponibles as $esp): ?>
                                <?php 
                                    $isSelected = (strcasecmp($especialidad, $esp['nombre']) === 0 || strcasecmp($especialidad, $esp['slug']) === 0);
                                ?>
                                <option value="<?= e($esp['nombre']); ?>" <?= $isSelected ? 'selected' : ''; ?> data-desc="<?= e($esp['descripcion_corta']); ?>" data-slug="<?= e($esp['slug']); ?>">
                                    <?= e($esp['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- TARJETA RESUMEN INTERACTIVA DE ESPECIALIDAD -->
                <div id="resumenEspecialidadWrap" style="background: var(--color-bg-page); border: 1px solid var(--color-border); border-left: 4px solid var(--color-primary); border-radius: var(--radius-md); padding: 16px 20px; margin: 20px 0; display: <?= $datosEspecialidadSeleccionada ? 'block' : 'none'; ?>;">
                    <div style="display: flex; align-items: center; gap: 8px; color: var(--color-primary); font-weight: 700; margin-bottom: 6px;">
                        <?= icon('graduation-cap', ['class' => 'icon-sm']); ?>
                        <span id="resumenEspecialidadTitulo"><?= e($datosEspecialidadSeleccionada['nombre'] ?? ''); ?></span>
                    </div>
                    <p id="resumenEspecialidadDesc" style="color: var(--color-text-muted); font-size: 0.9rem; margin: 0; line-height: 1.5;">
                        <?= e($datosEspecialidadSeleccionada['descripcion_corta'] ?? ''); ?>
                    </p>
                </div>

                <!-- BLOQUE 2: DATOS DEL ENCARGADO LEGAL -->
                <h3 style="color: var(--color-primary); font-weight: 800; margin: 35px 0 20px 0; border-bottom: 2px solid var(--color-border); padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <?= icon('users-gear', ['class' => 'icon-md']); ?> Datos del Encargado Legal y Contacto
                </h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre_encargado">Nombre del Padre, Madre o Tutor Legal *</label>
                        <input type="text" id="nombre_encargado" name="nombre_encargado" class="form-control" required value="<?= e($nombreEncargado); ?>" placeholder="Nombre completo del encargado">
                    </div>

                    <div class="form-group">
                        <label for="telefono_contacto">Teléfono Principal de Contacto *</label>
                        <input type="tel" id="telefono_contacto" name="telefono_contacto" class="form-control" required value="<?= e($telefonoContacto); ?>" placeholder="Ej: 8888-8888">
                    </div>

                    <div class="form-group">
                        <label for="correo_contacto">Correo Electrónico de Contacto *</label>
                        <input type="email" id="correo_contacto" name="correo_contacto" class="form-control" required value="<?= e($correoContacto); ?>" placeholder="correo@ejemplo.com">
                    </div>

                    <div class="form-group full-width">
                        <label for="observaciones">Observaciones o Información Adicional</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3" placeholder="Indica aquí cualquier condición especial de salud, colegio de procedencia o información de interés..."><?= e($observaciones); ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn btn-primary" style="padding: 14px 34px; font-size: 1rem;">
                        <?= icon('file-signature', ['class' => 'icon-sm']); ?> Enviar Solicitud de Pre-Matrícula
                    </button>
                </div>
            </form>
        </div>

    <?php endif; ?>
</main>

<script>
    function actualizarResumenEspecialidad(valor) {
        const select = document.getElementById('especialidad_interes');
        const wrap = document.getElementById('resumenEspecialidadWrap');
        const tit = document.getElementById('resumenEspecialidadTitulo');
        const desc = document.getElementById('resumenEspecialidadDesc');

        if (!select || !wrap) return;

        const opt = select.options[select.selectedIndex];
        const descVal = opt ? opt.getAttribute('data-desc') : '';

        if (valor && descVal) {
            tit.innerText = valor;
            desc.innerText = descVal;
            wrap.style.display = 'block';
        } else {
            wrap.style.display = 'none';
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
