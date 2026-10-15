<?php
// Incluimos el archivo de conexión
require_once 'config/db.php';

// Si la conexión en $pdo existe, mostramos la alerta de éxito
if (isset($pdo) && $pdo !== null) {
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px; margin: 20px auto; max-width: 600px; text-align: center;'>";
    echo "<h2 style='margin-top: 0;'>¡Conexión exitosa! 🎉</h2>";
    echo "<p style='margin-bottom: 0;'>Se ha establecido la conexión a la base de datos <strong>ctp_rosario</strong> correctamente usando PDO. </p>";
    echo "</div>";
}
?>