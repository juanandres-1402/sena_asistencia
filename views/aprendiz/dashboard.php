<?php
/**
 * views/aprendiz/dashboard.php
 * ---------------------------------------------------------------------
 * Panel del aprendiz. Solo consulta su propia información: asistencia,
 * ingresos, salidas, horarios, novedades y equipos registrados.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['aprendiz']);

$tituloPagina = 'Mi panel';
$pdo = obtenerConexion();

$stmt = $pdo->prepare("
    SELECT ap.id AS id_aprendiz, f.numero_ficha, p.nombre AS programa, h.hora_inicio, h.hora_fin, f.tolerancia_minutos
    FROM aprendices ap
    JOIN fichas f ON f.id = ap.id_ficha
    JOIN programas p ON p.id = f.id_programa
    JOIN horarios h ON h.id = f.id_horario
    WHERE ap.id_usuario = :id
");
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$info = $stmt->fetch();

$resumen = ['PRESENTE' => 0, 'LLEGADA_TARDE' => 0, 'SIN_INGRESO' => 0];
if ($info) {
    $stmt = $pdo->prepare("SELECT estado, COUNT(*) AS total FROM asistencias WHERE id_aprendiz = :id GROUP BY estado");
    $stmt->execute([':id' => $info['id_aprendiz']]);
    foreach ($stmt->fetchAll() as $row) {
        $resumen[$row['estado']] = (int) $row['total'];
    }

    $ultimoIngreso = $pdo->prepare("SELECT fecha, hora, estado_asistencia FROM ingresos WHERE id_aprendiz = :id ORDER BY id DESC LIMIT 1");
    $ultimoIngreso->execute([':id' => $info['id_aprendiz']]);
    $ultimoIngreso = $ultimoIngreso->fetch();
}

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if (!$info): ?>
    <div class="alert alert-warning">No se encontró una ficha asociada a su usuario. Contacte al coordinador académico.</div>
<?php else: ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-1"><?= htmlspecialchars($info['numero_ficha']) ?></h5>
        <p class="text-muted mb-0"><?= htmlspecialchars($info['programa']) ?> · Horario: <?= formatoHora12($info['hora_inicio']) ?> – <?= formatoHora12($info['hora_fin']) ?></p>
        <p class="text-muted small mb-0">Hora máxima de ingreso permitida: <strong><?= formatoHora12(calcularHoraMaxima($info['hora_inicio'], (int) $info['tolerancia_minutos'])) ?></strong></p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card sena-card-indicador p-3"><div class="etiqueta">🟢 Presentes</div><div class="valor"><?= $resumen['PRESENTE'] ?></div></div></div>
    <div class="col-6 col-md-3"><div class="card sena-card-indicador p-3"><div class="etiqueta">🟡 Tarde</div><div class="valor"><?= $resumen['LLEGADA_TARDE'] ?></div></div></div>
    <div class="col-6 col-md-3"><div class="card sena-card-indicador p-3"><div class="etiqueta">🔴 Sin ingreso</div><div class="valor"><?= $resumen['SIN_INGRESO'] ?></div></div></div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">Último ingreso</div>
            <?php if ($ultimoIngreso): $e = etiquetaEstado($ultimoIngreso['estado_asistencia']); ?>
                <div class="fw-semibold"><?= htmlspecialchars($ultimoIngreso['fecha']) ?> · <?= formatoHora12($ultimoIngreso['hora']) ?></div>
                <span class="badge <?= $e['clase'] ?>"><?= $e['texto'] ?></span>
            <?php else: ?>
                <div class="text-muted small">Aún no registra ingresos.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap">
    <a href="<?= rutaBase() ?>/views/aprendiz/asistencia.php" class="btn btn-sena-outline"><i class="bi bi-clipboard-check"></i> Ver mi asistencia</a>
    <a href="<?= rutaBase() ?>/views/aprendiz/equipos.php" class="btn btn-sena-outline"><i class="bi bi-laptop"></i> Mis equipos</a>
</div>

<?php endif; ?>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
