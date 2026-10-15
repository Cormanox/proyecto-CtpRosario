<?php
declare(strict_types=1);

// Incluimos el archivo de conexión y configuración global
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Conexión - CTP Rosario</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; padding: 40px 20px;">
    <?php if (isset($pdo) && $pdo instanceof PDO): ?>
        <div style="padding: 25px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px; margin: 0 auto; max-width: 600px; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;">¡Conexión exitosa! 🎉</h2>
            <p>Se ha establecido la conexión a la base de datos <strong>ctp_rosario</strong> correctamente usando PDO.</p>
            <p style="margin-bottom: 0; margin-top: 15px;">
                <a href="<?= url('public/index.php'); ?>" style="color: #155724; font-weight: bold; text-decoration: underline;">&rarr; Ir al Portal Web Institucional</a>
            </p>
        </div>
    <?php endif; ?>
</body>
</html>