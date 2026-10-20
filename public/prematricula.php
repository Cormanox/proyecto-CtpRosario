<?php
declare(strict_types=1);

$pageTitle = "Solicitud de Pre-Matrícula";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

$mensaje = '';
$tipoMensaje = '';

$especialidadesDisponibles = [
    'Desarrollo de Software'  => 'Informática en Desarrollo de Software',
    'Contabilidad y Finanzas' => 'Contabilidad y Finanzas',
    'Electronica Industrial'  => 'Electrónica Industrial',
    'Secretariado Ejecutivo'  => 'Secretariado Ejecutivo',
    'Mecanica de Precision'   => 'Mecánica de Precisión'
];

// Validación y sanitización del parámetro GET
$especialidadGet = trim($_GET['especialidad'] ?? '');
$especialidadSeleccionada = array_key_exists($especialidadGet, $especialidadesDisponibles) ? $especialidadGet : '';

$cedula = $nombre = $primerApellido = $segundoApellido = $fechaNacimiento = '';
$especialidad = '';
$nombreEncargado = $telefonoContacto = $correoContacto = $observaciones = '';

// Procesamiento del formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula            = trim($_POST['cedula_estudiante'] ?? '');
    $nombre            = trim($_POST['nombre_estudiante'] ?? '');
    $primerApellido    = trim($_POST['primer_apellido'] ?? '');
    $segundoApellido   = trim($_POST['segundo_apellido'] ?? '');
    $fechaNacimiento   = trim($_POST['fecha_nacimiento'] ?? '');
    $especialidad      = trim($_POST['especialidad_interes'] ?? '');
    $nombreEncargado   = trim($_POST['nombre_encargado'] ?? '');
    $telefonoContacto  = trim($_POST['telefono_contacto'] ?? '');
    $correoContacto    = trim($_POST['correo_contacto'] ?? '');
    $observaciones     = trim($_POST['observaciones'] ?? '');

    // Validación básica de campos obligatorios
    if (empty($cedula) || empty($nombre) || empty($primerApellido) || empty($fechaNacimiento) || empty($especialidad) || empty($nombreEncargado) || empty($telefonoContacto) || empty($correoContacto)) {
        $mensaje = 'Por favor, completa todos los campos requeridos marcados con (*).';
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
            $mensaje = "¡Solicitud de pre-matrícula registrada exitosamente! Tu número de expediente es: #{$idSolicitud}. La administración revisará tus datos y te contactará a la brevedad.";
            $tipoMensaje = 'success';

            // Limpiar variables tras éxito
            $cedula = $nombre = $primerApellido = $segundoApellido = $fechaNacimiento = $especialidad = $nombreEncargado = $telefonoContacto = $correoContacto = $observaciones = '';
            $especialidadSeleccionada = '';
        } catch (PDOException $e) {
            error_log("[Prematricula Error] " . $e->getMessage());
            $mensaje = 'Ocurrió un error al procesar tu solicitud. Por favor intenta más tarde o comunícate con secretaría.';
            $tipoMensaje = 'danger';
        }
    }
}
?>

<div class="page-header">
    <div class="container">
        <h2>Formulario de Pre-Matrícula</h2>
        <p>Inicia el proceso de postulación a nuestras especialidades técnicas para el ciclo lectivo.</p>
    </div>
</div>

<main class="container">
    <div class="form-card">
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= e($tipoMensaje); ?>">
                <i class="fa-solid <?= ($tipoMensaje === 'success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                <div><?= e($mensaje); ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= url('public/prematricula.php'); ?>" method="POST">
            <h3 style="color: var(--color-primary); font-weight: 800; margin-bottom: 20px; border-bottom: 2px solid var(--color-border); padding-bottom: 10px;">
                <i class="fa-solid fa-user-graduate" style="color: var(--color-secondary);"></i> Datos del Estudiante
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
                    <select id="especialidad_interes" name="especialidad_interes" class="form-control" required>
                        <option value="">-- Selecciona una especialidad --</option>
                        <?php
                        $valActual = $especialidad !== '' ? $especialidad : $especialidadSeleccionada;
                        foreach ($especialidadesDisponibles as $key => $nombreCompleto):
                            $isSelected = (strcasecmp($valActual, $key) === 0 || strcasecmp($valActual, $nombreCompleto) === 0);
                        ?>
                            <option value="<?= e($nombreCompleto); ?>" <?= $isSelected ? 'selected' : ''; ?>>
                                <?= e($nombreCompleto); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h3 style="color: var(--color-primary); font-weight: 800; margin: 30px 0 20px 0; border-bottom: 2px solid var(--color-border); padding-bottom: 10px;">
                <i class="fa-solid fa-users" style="color: var(--color-secondary);"></i> Datos del Encargado Legal y Contacto
            </h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre_encargado">Nombre del Padre, Madre o Encargado Legal *</label>
                    <input type="text" id="nombre_encargado" name="nombre_encargado" class="form-control" required value="<?= e($nombreEncargado); ?>" placeholder="Nombre completo del tutor">
                </div>

                <div class="form-group">
                    <label for="telefono_contacto">Teléfono de Contacto *</label>
                    <input type="tel" id="telefono_contacto" name="telefono_contacto" class="form-control" required value="<?= e($telefonoContacto); ?>" placeholder="Ej: 8888-8888">
                </div>

                <div class="form-group">
                    <label for="correo_contacto">Correo Electrónico de Contacto *</label>
                    <input type="email" id="correo_contacto" name="correo_contacto" class="form-control" required value="<?= e($correoContacto); ?>" placeholder="correo@ejemplo.com">
                </div>

                <div class="form-group full-width">
                    <label for="observaciones">Observaciones o Información Adicional</label>
                    <textarea id="observaciones" name="observaciones" class="form-control" placeholder="Indica aquí cualquier condición especial, colegio de procedencia o información de interés..."><?= e($observaciones); ?></textarea>
                </div>
            </div>

            <div style="margin-top: 25px; text-align: right;">
                <button type="submit" class="btn btn-primary" style="padding: 14px 30px; font-size: 1rem; cursor: pointer;">
                    <i class="fa-solid fa-paper-plane"></i> Enviar Solicitud de Pre-Matrícula
                </button>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
