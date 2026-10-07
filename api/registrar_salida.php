<?php
/**
 * api/registrar_salida.php
 * ---------------------------------------------------------------------
 * Endpoint AJAX (POST) que registra la SALIDA de un aprendiz que ya
 * tiene un ingreso registrado hoy (punto 9 del documento). Permite
 * saber en todo momento quién sigue dentro del centro.
 * ---------------------------------------------------------------------
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirRol(['porteria', 'coordinador']);

$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$idIngreso = (int) ($datos['id_ingreso'] ?? 0);

if ($idIngreso <= 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'Ingreso no válido.']);
    exit;
}

$pdo = obtenerConexion();

// Verifica que no se haya registrado ya la salida de este ingreso.
$stmt = $pdo->prepare("SELECT id FROM salidas WHERE id_ingreso = :id");
$stmt->execute([':id' => $idIngreso]);
if ($stmt->fetch()) {
    echo json_encode(['ok' => false, 'mensaje' => 'Ya se había registrado la salida para este ingreso.']);
    exit;
}

$hoy = date('Y-m-d');
$horaActual = date('H:i:s');

$stmtInsert = $pdo->prepare("
    INSERT INTO salidas (id_ingreso, fecha, hora, registrado_por)
    VALUES (:id_ingreso, :fecha, :hora, :registrado_por)
");
$stmtInsert->execute([
    ':id_ingreso' => $idIngreso,
    ':fecha' => $hoy,
    ':hora' => $horaActual,
    ':registrado_por' => $_SESSION['usuario_id'],
]);

registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Registro de salida', 'salidas', '', 'id_ingreso=' . $idIngreso);

echo json_encode(['ok' => true, 'mensaje' => 'Salida registrada correctamente.', 'hora' => formatoHora12($horaActual)]);
