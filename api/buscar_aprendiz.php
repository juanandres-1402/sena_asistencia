<?php
/**
 * api/buscar_aprendiz.php
 * ---------------------------------------------------------------------
 * Endpoint AJAX consumido por la pantalla "Control de ingreso" de
 * portería (views/porteria/ingreso.php).
 *
 * Recibe: ?documento=XXXXXXX  (por ahora un número de documento tecleado
 * o escaneado; queda preparado para recibir en el futuro el valor leído
 * por un lector de código de barras, QR, RFID o NFC, ya que todos esos
 * mecanismos terminan entregando una cadena de texto equivalente).
 *
 * Devuelve JSON con los datos del aprendiz, su ficha activa, horario,
 * hora máxima de ingreso permitida y el estado que tendría la
 * asistencia SI se registrara el ingreso en este momento.
 * ---------------------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirRol(['porteria', 'coordinador']);

$documento = trim($_GET['documento'] ?? '');

if ($documento === '') {
    echo json_encode(['ok' => false, 'mensaje' => 'Debe indicar un documento.']);
    exit;
}

$pdo = obtenerConexion();

// Se busca el usuario + su registro de aprendiz + su ficha ACTIVA más reciente.
$stmt = $pdo->prepare("
    SELECT
        u.id            AS id_usuario,
        u.nombre_completo,
        u.documento,
        a.id            AS id_aprendiz,
        f.id            AS id_ficha,
        f.numero_ficha,
        f.tolerancia_minutos,
        f.estado        AS estado_ficha,
        p.nombre        AS programa,
        h.hora_inicio,
        h.hora_fin
    FROM usuarios u
    JOIN aprendices a ON a.id_usuario = u.id
    JOIN fichas f     ON f.id = a.id_ficha
    JOIN programas p  ON p.id = f.id_programa
    JOIN horarios h   ON h.id = f.id_horario
    WHERE u.documento = :documento
    ORDER BY f.id DESC
    LIMIT 1
");
$stmt->execute([':documento' => $documento]);
$aprendiz = $stmt->fetch();

if (!$aprendiz) {
    echo json_encode(['ok' => false, 'mensaje' => 'No se encontró ningún aprendiz con ese documento.']);
    exit;
}

if ($aprendiz['estado_ficha'] === 'FINALIZADA') {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'La ficha ' . $aprendiz['numero_ficha'] . ' se encuentra FINALIZADA. No es posible registrar nuevas asistencias.'
    ]);
    exit;
}

// ¿Ya registró ingreso hoy? (evita duplicar el ingreso del mismo día)
$hoy = date('Y-m-d');
$stmtIngreso = $pdo->prepare("SELECT id, hora FROM ingresos WHERE id_aprendiz = :id_aprendiz AND fecha = :fecha");
$stmtIngreso->execute([':id_aprendiz' => $aprendiz['id_aprendiz'], ':fecha' => $hoy]);
$ingresoHoy = $stmtIngreso->fetch();

$horaActual   = date('H:i:s');
$horaMaxima   = calcularHoraMaxima($aprendiz['hora_inicio'], (int) $aprendiz['tolerancia_minutos']);
$estadoSiIngresaAhora = calcularEstadoAsistencia($aprendiz['hora_inicio'], (int) $aprendiz['tolerancia_minutos'], $horaActual);

// Si el aprendiz ya tiene un equipo asociado por defecto, se sugiere en pantalla.
$stmtEquipo = $pdo->prepare("SELECT codigo_equipo, marca, modelo, numero_serie, codigo_cargador FROM equipos WHERE id_aprendiz = :id_aprendiz LIMIT 1");
$stmtEquipo->execute([':id_aprendiz' => $aprendiz['id_aprendiz']]);
$equipoSugerido = $stmtEquipo->fetch();

echo json_encode([
    'ok' => true,
    'aprendiz' => [
        'id_usuario'      => $aprendiz['id_usuario'],
        'id_aprendiz'     => $aprendiz['id_aprendiz'],
        'nombre_completo' => $aprendiz['nombre_completo'],
        'documento'       => $aprendiz['documento'],
        'id_ficha'        => $aprendiz['id_ficha'],
        'numero_ficha'    => $aprendiz['numero_ficha'],
        'programa'        => $aprendiz['programa'],
        'horario'         => formatoHora12($aprendiz['hora_inicio']) . ' – ' . formatoHora12($aprendiz['hora_fin']),
        'hora_maxima'     => formatoHora12($horaMaxima),
        'hora_actual'     => formatoHora12($horaActual),
        'estado_estimado' => $estadoSiIngresaAhora,
        'ya_registrado'   => (bool) $ingresoHoy,
        'hora_registrada' => $ingresoHoy ? formatoHora12($ingresoHoy['hora']) : null,
        'equipo_sugerido' => $equipoSugerido ?: null,
    ],
]);
