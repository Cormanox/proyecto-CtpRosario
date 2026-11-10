<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Tanto 'admin' como 'editor' tienen acceso a noticias
requerirRol('admin', 'editor');

// Función auxiliar para generar slug único
function crearSlugNoticia(string $texto, ?int $excluirId = null): string {
    global $pdo;
    $texto = preg_replace('~[^\pL\d]+~u', '-', $texto) ?? '';
    $texto = iconv('utf-8', 'us-ascii//TRANSLIT', $texto) ?: $texto;
    $texto = preg_replace('~[^-\w]+~', '', $texto) ?? '';
    $texto = trim($texto, '-');
    $texto = strtolower($texto);
    if (empty($texto)) {
        $texto = 'noticia-' . time();
    }

    $baseSlug = $texto;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM noticias WHERE slug = :slug";
        if ($excluirId !== null) {
            $sql .= " AND id != :id";
        }
        $stmt = $pdo->prepare($sql);
        $params = [':slug' => $texto];
        if ($excluirId !== null) {
            $params[':id'] = $excluirId;
        }
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            break;
        }
        $texto = $baseSlug . '-' . $i++;
    }
    return $texto;
}

// Función auxiliar para procesar subida de archivo de imagen
function procesarSubidaImagen(array $file): ?string {
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Tamaño máximo: 3 MB
    if ($file['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException("El archivo excede el tamaño máximo permitido de 3 MB.");
    }

    // Validar tipo MIME real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $mimesPermitidos = [
        'image/jpeg' => '.jpg',
        'image/png'  => '.png',
        'image/webp' => '.webp',
    ];

    if (!array_key_exists($mimeType, $mimesPermitidos)) {
        throw new RuntimeException("Formato no permitido ({$mimeType}). Solo se aceptan imágenes JPEG, PNG o WebP.");
    }

    $uploadDir = __DIR__ . '/../assets/uploads/noticias';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $extension = $mimesPermitidos[$mimeType];
    $nuevoNombre = 'noticia_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . $extension;
    $destino = $uploadDir . '/' . $nuevoNombre;

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        throw new RuntimeException("Error al guardar la imagen en el servidor.");
    }

    return url('assets/uploads/noticias/' . $nuevoNombre);
}

// Procesar acciones POST con Post-Redirect-Get (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    $submittedToken = (string)($_POST['csrf_token'] ?? '');

    if (!validar_csrf($submittedToken)) {
        set_flash('danger', 'Token de seguridad inválido o expirado.');
        header("Location: " . url('admin/noticias.php'));
        exit;
    }

    // ACCIÓN: CREAR NOTICIA
    if ($action === 'crear_noticia') {
        $titulo    = trim((string)($_POST['titulo'] ?? ''));
        $contenido = trim((string)($_POST['contenido'] ?? ''));
        $imagenUrl = trim((string)($_POST['imagen_url'] ?? ''));
        $estado    = in_array($_POST['estado'] ?? '', ['publicado', 'borrador'], true) ? $_POST['estado'] : 'publicado';

        if ($titulo === '' || $contenido === '') {
            set_flash('danger', 'El título y el contenido son campos obligatorios.');
        } else {
            try {
                // Verificar si se subió un archivo local
                if (!empty($_FILES['imagen_archivo']['tmp_name'])) {
                    $subida = procesarSubidaImagen($_FILES['imagen_archivo']);
                    if ($subida) {
                        $imagenUrl = $subida;
                    }
                }

                $slug   = crearSlugNoticia($titulo);
                $userId = (int)($_SESSION['user_id'] ?? 1);

                $stmt = $pdo->prepare(
                    "INSERT INTO noticias (titulo, slug, contenido, imagen_url, estado, usuario_id, fecha_publicacion) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW())"
                );
                $stmt->execute([$titulo, $slug, $contenido, $imagenUrl, $estado, $userId]);

                set_flash('success', "¡Noticia registrada exitosamente en estado '{$estado}'!");
            } catch (Throwable $e) {
                error_log("[Noticias Insert Error] " . $e->getMessage());
                set_flash('danger', 'Error al guardar la noticia: ' . $e->getMessage());
            }
        }
        header("Location: " . url('admin/noticias.php'));
        exit;

    // ACCIÓN: EDITAR NOTICIA
    } elseif ($action === 'editar_noticia') {
        $id        = (int)($_POST['noticia_id'] ?? 0);
        $titulo    = trim((string)($_POST['titulo'] ?? ''));
        $contenido = trim((string)($_POST['contenido'] ?? ''));
        $imagenUrl = trim((string)($_POST['imagen_url'] ?? ''));
        $estado    = in_array($_POST['estado'] ?? '', ['publicado', 'borrador'], true) ? $_POST['estado'] : 'publicado';

        if ($id <= 0 || $titulo === '' || $contenido === '') {
            set_flash('danger', 'Datos incompletos para actualizar la noticia.');
        } else {
            try {
                // Verificar si se subió una nueva imagen local
                if (!empty($_FILES['imagen_archivo']['tmp_name'])) {
                    $subida = procesarSubidaImagen($_FILES['imagen_archivo']);
                    if ($subida) {
                        $imagenUrl = $subida;
                    }
                }

                $stmt = $pdo->prepare(
                    "UPDATE noticias 
                     SET titulo = :titulo, contenido = :contenido, imagen_url = :img, estado = :estado
                     WHERE id = :id"
                );
                $stmt->execute([
                    ':titulo'    => $titulo,
                    ':contenido' => $contenido,
                    ':img'       => $imagenUrl,
                    ':estado'    => $estado,
                    ':id'        => $id
                ]);

                set_flash('success', "Noticia #{$id} actualizada correctamente.");
            } catch (Throwable $e) {
                error_log("[Noticias Edit Error] " . $e->getMessage());
                set_flash('danger', 'Error al actualizar la noticia: ' . $e->getMessage());
            }
        }
        header("Location: " . url('admin/noticias.php'));
        exit;

    // ACCIÓN: CAMBIAR ESTADO (PUBLICADO / BORRADOR)
    } elseif ($action === 'cambiar_estado') {
        $id = (int)($_POST['noticia_id'] ?? 0);
        $nuevoEstado = in_array($_POST['nuevo_estado'] ?? '', ['publicado', 'borrador'], true) ? $_POST['nuevo_estado'] : 'publicado';

        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE noticias SET estado = ? WHERE id = ?");
                $stmt->execute([$nuevoEstado, $id]);
                set_flash('success', "Estado de la noticia cambiado a '{$nuevoEstado}'.");
            } catch (PDOException $e) {
                error_log("[Noticias Toggle State Error] " . $e->getMessage());
                set_flash('danger', 'Error al cambiar estado de la noticia.');
            }
        }
        header("Location: " . url('admin/noticias.php'));
        exit;

    // ACCIÓN: ELIMINAR NOTICIA
    } elseif ($action === 'eliminar_noticia') {
        $id = (int)($_POST['noticia_id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
                $stmt->execute([$id]);
                set_flash('success', "Noticia eliminada correctamente.");
            } catch (PDOException $e) {
                error_log("[Noticias Delete Error] " . $e->getMessage());
                set_flash('danger', 'No se pudo eliminar la noticia.');
            }
        }
        header("Location: " . url('admin/noticias.php'));
        exit;
    }
}

// Cargar todas las noticias registradas
try {
    $noticias = $pdo->query(
        "SELECT n.*, u.nombre AS autor 
         FROM noticias n 
         LEFT JOIN usuarios u ON n.usuario_id = u.id 
         ORDER BY n.fecha_publicacion DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log("[Noticias Load Error] " . $e->getMessage());
    $noticias = [];
}

// Noticia a editar si viene por GET
$editarNoticia = null;
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    foreach ($noticias as $not) {
        if ((int)$not['id'] === $editId) {
            $editarNoticia = $not;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Noticias y Comunicados - CTP Rosario</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css'); ?>">
</head>
<body>

    <header class="admin-navbar">
        <div class="container admin-nav-container">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <h2 style="font-size: 1.25rem; font-weight: bold; color: var(--color-gold); display: flex; align-items: center; gap: 8px;">
                    <?= icon('gauge-high', ['class' => 'icon-md']); ?> CTP Rosario | Admin
                </h2>
                <ul class="admin-nav-links">
                    <li><a href="<?= url('admin/index.php'); ?>"><?= icon('gauge-high', ['class' => 'icon-sm']); ?> Dashboard</a></li>
                    <li><a href="<?= url('admin/especialidades.php'); ?>"><?= icon('graduation-cap', ['class' => 'icon-sm']); ?> Especialidades</a></li>
                    <?php if (tieneRol('admin')): ?>
                        <li><a href="<?= url('admin/configuracion.php'); ?>"><?= icon('sliders', ['class' => 'icon-sm']); ?> Configuración</a></li>
                        <li><a href="<?= url('admin/solicitudes.php'); ?>"><?= icon('file-signature', ['class' => 'icon-sm']); ?> Solicitudes & Consultas</a></li>
                        <li><a href="<?= url('admin/usuarios.php'); ?>"><?= icon('users-gear', ['class' => 'icon-sm']); ?> Usuarios</a></li>
                    <?php endif; ?>
                    <li><a href="<?= url('admin/noticias.php'); ?>" class="active"><?= icon('newspaper', ['class' => 'icon-sm']); ?> Noticias</a></li>
                </ul>
            </div>
            <div class="admin-user-info">
                <span>
                    <?= icon('user', ['class' => 'icon-sm']); ?> <?= e($_SESSION['user_name'] ?? 'Usuario'); ?>
                    <span class="badge badge-<?= e($_SESSION['user_role'] ?? 'user'); ?>" style="margin-left: 6px;">
                        <?= e($_SESSION['user_role'] ?? 'Usuario'); ?>
                    </span>
                </span>
                <a href="<?= url('public/index.php'); ?>" target="_blank" rel="noopener noreferrer" style="color: #94a3b8; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Ver Portal Web">
                    <?= icon('arrow-up-right-from-square', ['class' => 'icon-sm']); ?> Ver Web
                </a>
                <a href="<?= url('admin/logout.php'); ?>" class="btn btn-sm btn-danger" style="display: inline-flex; align-items: center; gap: 6px;">
                    <?= icon('right-from-bracket', ['class' => 'icon-sm']); ?> Salir
                </a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 35px; margin-bottom: 60px;">
        <!-- Mensajes Flash -->
        <?= render_flash(); ?>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
            <div>
                <h2 style="color: var(--color-primary); font-weight: 800; font-size: 1.65rem; margin-bottom: 4px;">
                    Noticias y Comunicados Institucionales
                </h2>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Publica comunicados oficiales, avisos de matrícula o logros estudiantiles con soporte de subida de imágenes locales o enlaces web.
                </p>
            </div>
            <?php if (!$editarNoticia): ?>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formNoticia').scrollIntoView({behavior: 'smooth'})">
                    <?= icon('plus-circle', ['class' => 'icon-sm']); ?> Redactar Noticia
                </button>
            <?php endif; ?>
        </div>

        <!-- Tabla de Noticias -->
        <div class="table-responsive" style="margin-bottom: 35px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Portada</th>
                        <th>Título y Resumen</th>
                        <th style="width: 140px;">Autor</th>
                        <th style="width: 110px;">Estado</th>
                        <th style="width: 130px;">Fecha</th>
                        <th style="text-align: right; width: 160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($noticias)): ?>
                        <?php foreach ($noticias as $n): ?>
                            <tr>
                                <td>
                                    <?php $imgSrc = !empty($n['imagen_url']) ? $n['imagen_url'] : 'https://via.placeholder.com/120x80?text=CTP'; ?>
                                    <img src="<?= e($imgSrc); ?>" alt="Portada" style="width: 64px; height: 44px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-border);" loading="lazy">
                                </td>
                                <td>
                                    <strong><?= e($n['titulo']); ?></strong>
                                    <div style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 3px;">
                                        <?= e(mb_substr($n['contenido'], 0, 95, 'UTF-8')) . '...'; ?>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--color-text-muted);">
                                    <?= icon('user', ['class' => 'icon-sm']); ?> <?= e($n['autor'] ?? 'Administración'); ?>
                                </td>
                                <td>
                                    <form action="<?= url('admin/noticias.php'); ?>" method="POST" style="display: inline;">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="cambiar_estado">
                                        <input type="hidden" name="noticia_id" value="<?= (int)$n['id']; ?>">
                                        <input type="hidden" name="nuevo_estado" value="<?= ($n['estado'] === 'publicado') ? 'borrador' : 'publicado'; ?>">
                                        <button type="submit" class="badge <?= ($n['estado'] === 'publicado') ? 'badge-activo' : 'badge-editor'; ?>" style="border: none; cursor: pointer;" title="Clic para alternar estado">
                                            <?= ($n['estado'] === 'publicado') ? icon('check-circle', ['class' => 'icon-sm']) . ' Publicado' : icon('archive', ['class' => 'icon-sm']) . ' Borrador'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="font-size: 0.82rem; color: var(--color-text-muted);">
                                    <?= date('d/m/Y H:i', strtotime($n['fecha_publicacion'])); ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="<?= url('admin/noticias.php?edit=' . (int)$n['id']); ?>#formNoticia" class="btn btn-sm btn-outline" style="margin-right: 4px;">
                                        Editar
                                    </a>
                                    <form action="<?= url('admin/noticias.php'); ?>" method="POST" style="display: inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta noticia?');">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="action" value="eliminar_noticia">
                                        <input type="hidden" name="noticia_id" value="<?= (int)$n['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <?= icon('trash', ['class' => 'icon-sm']); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--color-text-subtle); padding: 30px;">
                                No hay noticias registradas. Utiliza el formulario a continuación para publicar la primera.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario: Crear / Editar Noticia -->
        <div class="form-card" id="formNoticia" style="scroll-margin-top: 100px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid var(--color-border); padding-bottom: 12px;">
                <h3 style="color: var(--color-primary); font-weight: 800; font-size: 1.3rem; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <?= $editarNoticia ? icon('sliders', ['class' => 'icon-md']) . ' Editar Noticia #' . (int)$editarNoticia['id'] : icon('plus-circle', ['class' => 'icon-md']) . ' Redactar Nueva Noticia'; ?>
                </h3>
                <?php if ($editarNoticia): ?>
                    <a href="<?= url('admin/noticias.php'); ?>" class="btn btn-sm btn-outline">Cancelar Edición</a>
                <?php endif; ?>
            </div>

            <form action="<?= url('admin/noticias.php'); ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="<?= $editarNoticia ? 'editar_noticia' : 'crear_noticia'; ?>">
                <?php if ($editarNoticia): ?>
                    <input type="hidden" name="noticia_id" value="<?= (int)$editarNoticia['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="titulo">Título de la Noticia *</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" required value="<?= e($editarNoticia['titulo'] ?? ''); ?>" placeholder="Ej: Apertura de convocatoria para el ciclo lectivo 2027">
                    </div>

                    <div class="form-group full-width">
                        <label for="contenido">Contenido Completo de la Noticia *</label>
                        <textarea id="contenido" name="contenido" class="form-control" rows="6" required placeholder="Escribe el cuerpo completo del comunicado institucional..."><?= e($editarNoticia['contenido'] ?? ''); ?></textarea>
                    </div>

                    <!-- OPCIÓN 1: SUBIR IMAGEN LOCAL -->
                    <div class="form-group">
                        <label for="imagen_archivo">Subir Imagen Local de Portada (JPG, PNG, WebP ≤ 3MB)</label>
                        <input type="file" id="imagen_archivo" name="imagen_archivo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small style="color: var(--color-text-muted); display: block; margin-top: 4px;">
                            Se almacenará de forma segura en <code>assets/uploads/noticias/</code>.
                        </small>
                    </div>

                    <!-- OPCIÓN 2: URL EXTERNA -->
                    <div class="form-group">
                        <label for="imagen_url">O pegar URL Externa de Imagen</label>
                        <input type="text" id="imagen_url" name="imagen_url" class="form-control" value="<?= e($editarNoticia['imagen_url'] ?? ''); ?>" placeholder="https://ejemplo.com/foto.jpg">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado de Publicación *</label>
                        <select id="estado" name="estado" class="form-control" required>
                            <option value="publicado" <?= (!isset($editarNoticia) || $editarNoticia['estado'] === 'publicado') ? 'selected' : ''; ?>>
                                Publicado (Visible en portada)
                            </option>
                            <option value="borrador" <?= (isset($editarNoticia) && $editarNoticia['estado'] === 'borrador') ? 'selected' : ''; ?>>
                                Borrador (Oculto al público)
                            </option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 25px; text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
                    <?php if ($editarNoticia): ?>
                        <a href="<?= url('admin/noticias.php'); ?>" class="btn btn-outline">Cancelar</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= icon('check-circle', ['class' => 'icon-sm']); ?> <?= $editarNoticia ? 'Guardar Cambios' : 'Publicar Noticia'; ?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script type="module" src="<?= url('assets/js/animations.js'); ?>"></script>
</body>
</html>
