<?php
/**
 * views/coordinador/salidas_campo.php
 * ---------------------------------------------------------------------
 * ACTIVIDAD EXTERNA / SALIDA DE CAMPO (punto 12 del documento).
 * Cuando una ficha tiene actividad fuera de la sede, se registra como
 * excepción: el sistema NO exige que el aprendiz pase por portería y,
 * en su lugar, genera asistencia con estado SALIDA_CAMPO para toda la
 * ficha ese día.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Salidas de campo / Actividad externa';
$pdo = obtenerConexion();
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idFicha = (int) $_POST['id_ficha'];
    $fecha   = $_POST['fecha'];

    $stmt = $pdo->prepare("
        INSERT INTO actividades_externas (id_ficha, tipo_actividad, lugar, fecha, hora, id_instructor_responsable, id_competencia, id_resultado_aprendizaje)
        VALUES (:ficha, :tipo, :lugar, :fecha, :hora, :instructor, :competencia, :resultado)
    ");
    $stmt->execute([
        ':ficha'      => $idFicha,
        ':tipo'       => trim($_POST['tipo_actividad']),
        ':lugar'      => trim($_POST['lugar']),
        ':fecha'      => $fecha,
        ':hora'       => $_POST['hora'],
        ':instructor' => $_POST['id_instructor'] ?: null,
        ':competencia'=> $_POST['id_competencia'] ?: null,
        ':resultado'  => $_POST['id_resultado_aprendizaje'] ?: null,
    ]);

    // Se genera asistencia automática tipo SALIDA_CAMPO para todos los aprendices de la ficha
    // en la fecha indicada, como excepción configurada (no requiere ingreso físico).
    $aprendices = $pdo->prepare("SELECT id FROM aprendices WHERE id_ficha = :ficha");
    $aprendices->execute([':ficha' => $idFicha]);
    $stmtAsistencia = $pdo->prepare("
        INSERT INTO asistencias (id_aprendiz, id_ficha, fecha, estado, origen, observacion)
        VALUES (:aprendiz, :ficha, :fecha, 'SALIDA_CAMPO', 'SALIDA_CAMPO', :obs)
    ");
    foreach ($aprendices->fetchAll() as $a) {
        $stmtAsistencia->execute([
            ':aprendiz' => $a['id'],
            ':ficha'    => $idFicha,
            ':fecha'    => $fecha,
            ':obs'      => trim($_POST['tipo_actividad']) . ' - ' . trim($_POST['lugar']),
        ]);
    }

    registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Registro de salida de campo', 'actividades_externas', '', $_POST['lugar']);
    generarNotificacion(null, 'Se registró una salida de campo para la ficha seleccionada.', 'SALIDA_CAMPO');

    $mensaje = ['tipo' => 'success', 'texto' => 'Actividad externa registrada. No se exigirá ingreso físico a los aprendices de esta ficha en la fecha indicada.'];
}

$fichas = $pdo->query("SELECT id, numero_ficha FROM fichas WHERE estado = 'ACTIVA'")->fetchAll();
$instructores = $pdo->query("SELECT u.id, u.nombre_completo FROM usuarios u JOIN roles r ON r.id=u.id_rol WHERE r.nombre='instructor'")->fetchAll();
$competencias = $pdo->query("SELECT * FROM competencias")->fetchAll();
$resultados   = $pdo->query("SELECT * FROM resultados_aprendizaje")->fetchAll();

$historial = $pdo->query("
    SELECT ae.*, f.numero_ficha, u.nombre_completo AS instructor
    FROM actividades_externas ae
    JOIN fichas f ON f.id = ae.id_ficha
    LEFT JOIN usuarios u ON u.id = ae.id_instructor_responsable
    ORDER BY ae.id DESC
")->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible alert-auto-cerrar"><?= $mensaje['texto'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Registrar actividad externa</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-2">
                        <label class="form-label small">Ficha</label>
                        <select name="id_ficha" class="form-select" required>
                            <?php foreach ($fichas as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['numero_ficha']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Tipo de actividad</label>
                        <input type="text" name="tipo_actividad" class="form-control" value="Salida de campo" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Lugar</label>
                        <input type="text" name="lugar" class="form-control" placeholder="Finca / empresa / municipio / institución" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Hora</label>
                            <input type="time" name="hora" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2 mt-2">
                        <label class="form-label small">Instructor responsable</label>
                        <select name="id_instructor" class="form-select">
                            <option value="">Sin asignar</option>
                            <?php foreach ($instructores as $i): ?>
                                <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Competencia</label>
                        <select name="id_competencia" class="form-select">
                            <option value="">Sin asignar</option>
                            <?php foreach ($competencias as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Resultado de aprendizaje</label>
                        <select name="id_resultado_aprendizaje" class="form-select">
                            <option value="">Sin asignar</option>
                            <?php foreach ($resultados as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-sena w-100"><i class="bi bi-signpost-2"></i> Registrar actividad</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <table class="table table-sm">
            <thead class="sena-thead"><tr><th>Ficha</th><th>Tipo</th><th>Lugar</th><th>Fecha</th><th>Instructor</th></tr></thead>
            <tbody>
                <?php if (!$historial): ?><tr><td colspan="5" class="text-center text-muted py-3">Sin actividades registradas.</td></tr><?php endif; ?>
                <?php foreach ($historial as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['numero_ficha']) ?></td>
                    <td><?= htmlspecialchars($h['tipo_actividad']) ?></td>
                    <td><?= htmlspecialchars($h['lugar']) ?></td>
                    <td><?= htmlspecialchars($h['fecha']) ?></td>
                    <td><?= htmlspecialchars($h['instructor'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
