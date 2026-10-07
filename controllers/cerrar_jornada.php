<?php
/**
 * controllers/cerrar_jornada.php
 * ---------------------------------------------------------------------
 * Utilidad de cierre de jornada (punto 11 del documento: "SIN INGRESO
 * REGISTRADO"). Este script revisa, para la fecha indicada, todos los
 * aprendices de fichas ACTIVAS que:
 *   - No tengan ya un registro de ingreso ese día, y
 *   - No tengan una excepción de salida de campo ese día.
 * y les genera una asistencia con estado SIN_INGRESO.
 *
 * USO:
 *   Línea de comandos:  php controllers/cerrar_jornada.php [YYYY-MM-DD]
 *   (si no se indica fecha, usa la fecha de hoy)
 *
 * En un ambiente de producción este script se debe programar como una
 * tarea CRON que se ejecute, por ejemplo, al finalizar la última
 * jornada del día. La conversión definitiva de "sin ingreso" en una
 * inasistencia formal debe ajustarse al Acuerdo 009 y a la normativa
 * institucional vigente (punto 20); este script deja el estado
 * calculado, pero NO decide por sí mismo sanciones disciplinarias.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/funciones.php';

$fecha = $argv[1] ?? date('Y-m-d');
$pdo = obtenerConexion();

$fichasActivas = $pdo->query("SELECT id, numero_ficha FROM fichas WHERE estado = 'ACTIVA'")->fetchAll();

$totalMarcados = 0;

foreach ($fichasActivas as $ficha) {
    // Aprendices de la ficha que NO tienen ningún registro de asistencia ese día.
    $stmt = $pdo->prepare("
        SELECT ap.id AS id_aprendiz, u.nombre_completo, u.id AS id_usuario
        FROM aprendices ap
        JOIN usuarios u ON u.id = ap.id_usuario
        WHERE ap.id_ficha = :ficha
        AND ap.id NOT IN (SELECT id_aprendiz FROM asistencias WHERE id_ficha = :ficha2 AND fecha = :fecha)
    ");
    $stmt->execute([':ficha' => $ficha['id'], ':ficha2' => $ficha['id'], ':fecha' => $fecha]);

    foreach ($stmt->fetchAll() as $aprendiz) {
        $pdo->prepare("
            INSERT INTO asistencias (id_aprendiz, id_ficha, fecha, estado, origen, observacion)
            VALUES (:aprendiz, :ficha, :fecha, 'SIN_INGRESO', 'AUTOMATICO_SIN_INGRESO', 'Generado por cierre de jornada')
        ")->execute([':aprendiz' => $aprendiz['id_aprendiz'], ':ficha' => $ficha['id'], ':fecha' => $fecha]);

        generarNotificacion($aprendiz['id_usuario'], 'El aprendiz ' . $aprendiz['nombre_completo'] . ' no registra ingreso.', 'SIN_INGRESO');
        $totalMarcados++;
    }
}

registrarAuditoria(null, 'Sistema (cierre de jornada)', 'Cierre de jornada automático', 'asistencias', '', "$totalMarcados aprendices marcados SIN_INGRESO para $fecha");

echo "Cierre de jornada ejecutado para $fecha. Aprendices marcados como SIN_INGRESO: $totalMarcados" . PHP_EOL;
