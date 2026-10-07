<?php
/**
 * acceso_denegado.php
 * Se muestra cuando un usuario autenticado intenta entrar a una
 * pantalla que no corresponde a su rol (ej: portería intentando
 * entrar a configuración académica).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso denegado</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="text-center">
        <i class="bi bi-shield-lock display-1 text-danger"></i>
        <h3 class="mt-3">Acceso denegado</h3>
        <p class="text-muted">Su rol no tiene permisos para ver esta sección.</p>
        <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
    </div>
</body>
</html>
