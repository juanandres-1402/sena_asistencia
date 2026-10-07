<?php
/**
 * api/registrar_ingreso.php
 * ---------------------------------------------------------------------
 * Endpoint AJAX (POST) que ejecuta el flujo completo descrito en el
 * punto 21 del documento de requerimientos:
 *
 *   1. Registra el INGRESO del aprendiz (hora real).
 *   2. Calcula AUTOMÁTICAMENTE el estado de asistencia (PRESENTE /
 *      LLEGADA_TARDE) según la hora de inicio + tolerancia de la ficha.
 *   3. Si el aprendiz trae portátil, registra (o reconoce) el equipo y
 *      lo asocia a este ingreso.
 *   4. Genera notificación si el aprendiz llegó tarde.
 *   5. Deja constancia en la auditoría.
 *
 * El instructor NO participa en este proceso: solo consultará el
 * resultado más adelante desde su propio panel.
 * ---------------------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/sesion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirRol(['porteria', 'coordinador']);

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

$idAprendiz = (int) ($datos['id_aprendiz'] ?? 0);
$idFicha    = (int) ($datos['id_ficha'] ?? 0);
$traeEquipo = (bool) ($datos['trae_equipo'] ?? false);
$equipo     = $datos['equipo'] ?? [];   // tipo, marca, modelo, numero_serie, codigo_cargador, codigo_existente

if ($idAprendiz <= 0 || $idFicha <= 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos incompletos para registrar el ingreso.']);
    exit;
}

$pdo = obtenerConexion();

// --- Se recalcula todo en el servidor (nunca se confía en lo que llega del navegador) ---
$stmtFicha = $pdo->prepare("
    SELECT f.tolerancia_minutos, f.estado, h.hora_inicio, u.nombre_completo AS aprendiz_nombre, u.id AS id_usuario_aprendiz
    FROM fichas f
    JOIN horarios h ON h.id = f.id_horario
    JOIN aprendices a ON a.id = :id_aprendiz
    JOIN usuarios u ON u.id = a.id_usuario
    WHERE f.id = :id_ficha
");
$stmtFicha->execute([':id_aprendiz' => $idAprendiz, ':id_ficha' => $idFicha]);
$ficha = $stmtFicha->fetch();

if (!$ficha) {
    echo json_encode(['ok' => false, 'mensaje' => 'No fue posible ubicar la ficha del aprendiz.']);
    exit;
}
if ($ficha['estado'] === 'FINALIZADA') {
    echo json_encode(['ok' => false, 'mensaje' => 'Esta ficha se encuentra finalizada. No es posible registrar nuevas asistencias.']);
    exit;
}

$hoy        = date('Y-m-d');
$horaActual = date('H:i:s');

// Evita doble ingreso el mismo día.
$stmtDup = $pdo->prepare("SELECT id FROM ingresos WHERE id_aprendiz = :id_aprendiz AND fecha = :fecha");
$stmtDup->execute([':id_aprendiz' => $idAprendiz, ':fecha' => $hoy]);
if ($stmtDup->fetch()) {
    echo json_encode(['ok' => false, 'mensaje' => 'Este aprendiz ya registró su ingreso hoy.']);
    exit;
}

$estado = calcularEstadoAsistencia($ficha['hora_inicio'], (int) $ficha['tolerancia_minutos'], $horaActual);

$pdo->beginTransaction();
try {
    // 1) INGRESO
    $stmtIngreso = $pdo->prepare("
        INSERT INTO ingresos (id_aprendiz, id_ficha, fecha, hora, estado_asistencia, registrado_por)
        VALUES (:id_aprendiz, :id_ficha, :fecha, :hora, :estado, :registrado_por)
    ");
    $stmtIngreso->execute([
        ':id_aprendiz'     => $idAprendiz,
        ':id_ficha'        => $idFicha,
        ':fecha'           => $hoy,
        ':hora'            => $horaActual,
        ':estado'          => $estado,
        ':registrado_por'  => $_SESSION['usuario_id'],
    ]);
    $idIngreso = (int) $pdo->lastInsertId();

    // 2) ASISTENCIA (generada automáticamente, origen = INGRESO)
    $stmtAsistencia = $pdo->prepare("
        INSERT INTO asistencias (id_aprendiz, id_ficha, fecha, estado, origen, id_ingreso)
        VALUES (:id_aprendiz, :id_ficha, :fecha, :estado, 'INGRESO', :id_ingreso)
    ");
    $stmtAsistencia->execute([
        ':id_aprendiz' => $idAprendiz,
        ':id_ficha'    => $idFicha,
        ':fecha'       => $hoy,
        ':estado'      => $estado,
        ':id_ingreso'  => $idIngreso,
    ]);

    // 3) EQUIPO (si aplica)
    $equipoRegistrado = null;
    if ($traeEquipo) {
        $codigoExistente = trim($equipo['codigo_existente'] ?? '');

        if ($codigoExistente !== '') {
            // El equipo ya estaba registrado: solo se reconoce y asocia.
            $stmtBuscarEquipo = $pdo->prepare("SELECT id, codigo_equipo FROM equipos WHERE codigo_equipo = :codigo");
            $stmtBuscarEquipo->execute([':codigo' => $codigoExistente]);
            $equipoRegistrado = $stmtBuscarEquipo->fetch();
        }

        if (!$equipoRegistrado) {
            // Equipo nuevo: se registra con todos sus datos (punto 7 del documento).
            $codigoNuevo = generarCodigoEquipo();
            $stmtNuevoEquipo = $pdo->prepare("
                INSERT INTO equipos (codigo_equipo, tipo_equipo, marca, modelo, numero_serie, codigo_cargador, id_aprendiz)
                VALUES (:codigo, :tipo, :marca, :modelo, :serie, :cargador, :id_aprendiz)
            ");
            $stmtNuevoEquipo->execute([
                ':codigo'      => $codigoNuevo,
                ':tipo'        => $equipo['tipo'] ?? 'portatil',
                ':marca'       => $equipo['marca'] ?? '',
                ':modelo'      => $equipo['modelo'] ?? '',
                ':serie'       => $equipo['numero_serie'] ?? '',
                ':cargador'    => $equipo['codigo_cargador'] ?? '',
                ':id_aprendiz' => $idAprendiz,
            ]);
            $equipoRegistrado = ['id' => (int) $pdo->lastInsertId(), 'codigo_equipo' => $codigoNuevo];
        }

        $stmtRelacion = $pdo->prepare("INSERT INTO equipo_ingreso (id_ingreso, id_equipo) VALUES (:id_ingreso, :id_equipo)");
        $stmtRelacion->execute([':id_ingreso' => $idIngreso, ':id_equipo' => $equipoRegistrado['id']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'mensaje' => 'Ocurrió un error al registrar el ingreso: ' . $e->getMessage()]);
    exit;
}

// 4) Notificación si llegó tarde (punto 17)
if ($estado === 'LLEGADA_TARDE') {
    generarNotificacion(
        $ficha['id_usuario_aprendiz'],
        'El aprendiz ' . $ficha['aprendiz_nombre'] . ' ingresó después de la hora configurada para su ficha.',
        'LLEGADA_TARDE'
    );
}

// 5) Auditoría (punto 18)
registrarAuditoria(
    $_SESSION['usuario_id'],
    $_SESSION['usuario_nombre'],
    'Registro de ingreso' . ($traeEquipo ? ' (con equipo)' : ''),
    'ingresos',
    '',
    $ficha['aprendiz_nombre'] . ' - ' . $estado . ' - ' . $horaActual
);

echo json_encode([
    'ok' => true,
    'mensaje' => 'Ingreso registrado correctamente.',
    'estado' => $estado,
    'hora' => formatoHora12($horaActual),
    'equipo' => $equipoRegistrado['codigo_equipo'] ?? null,
]);
