<?php
declare(strict_types=1);

// Redirección al punto de entrada público de la aplicación
require_once __DIR__ . '/config/db.php';
header("Location: " . url('public/index.php'));
exit;
