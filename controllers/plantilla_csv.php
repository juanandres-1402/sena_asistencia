<?php
/**
 * controllers/plantilla_csv.php
 * ---------------------------------------------------------------------
 * Genera y entrega para descarga las plantillas CSV usadas por los
 * módulos de carga masiva (?tipo=aprendices | instructores_generica |
 * instructores_personalizada).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../includes/funciones.php';
exigirRol(['coordinador', 'instructor']);

$plantillas = [
    'aprendices' => [
        'documento,nombre_completo,correo',
        '1070000111,Ejemplo Aprendiz Uno,ejemplo1@correo.com',
        '1070000112,Ejemplo Aprendiz Dos,',
    ],
    'instructores_generica' => [
        'documento,nombre_completo,correo',
        '1070000222,Ejemplo Instructor Dos,ejemplo2@correo.com',
    ],
    'instructores_personalizada' => [
        'documento,nombre_completo,correo,clave',
        '1070000333,Ejemplo Instructor Tres,ejemplo3@correo.com,ClaveSegura123',
    ],
];

$tipo = $_GET['tipo'] ?? 'aprendices';
if (!isset($plantillas[$tipo])) {
    $tipo = 'aprendices';
}

// Las plantillas de instructores solo tienen sentido para el coordinador.
if (str_starts_with($tipo, 'instructores') && $_SESSION['usuario_rol'] !== 'coordinador') {
    http_response_code(403);
    exit('No autorizado.');
}

$nombreArchivo = 'plantilla_' . $tipo . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

// BOM para que Excel abra correctamente los acentos en español.
echo "\xEF\xBB\xBF";
echo implode("\r\n", $plantillas[$tipo]);
