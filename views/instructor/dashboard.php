<?php
/**
 * views/instructor/dashboard.php
 * ---------------------------------------------------------------------
 * MI FICHA (punto 16 del documento). El instructor entra y ve
 * directamente el resumen de su ficha de hoy. A propósito NO existe
 * ningún botón "Tomar asistencia": esa información ya fue generada
 * automáticamente por portería.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['instructor']);

$tituloPagina = 'Mi ficha';
$pdo = obtenerConexion();
$hoy = date('Y-m-d');

$fichas = $pdo->prepare("
    SELECT f.id, f.numero_ficha, p.nombre AS programa, h.hora_inicio, h.hora_fin, f.tolerancia_minutos, f.estado,
           (SELECT COUNT(*) FROM aprendices a WHERE a.id_ficha = f.id) AS total_aprendices
    FROM fichas f
    JOIN programas p ON p.id = f.id_programa
    JOIN horarios h ON h.id = f.id_horario
    WHERE f.id_instructor = :id
");
$fichas->execute([':id' => $_SESSION['usuario_id']]);
$fichas = $fichas->fetchAll();

foreach ($fichas as &$f) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingresos WHERE id_ficha = :f AND fecha = :h AND estado_asistencia='PRESENTE'");
    $stmt->execute([':f' => $f['id'], ':h' => $hoy]);
    $f['presentes'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingresos WHERE id_ficha = :f AND fecha = :h AND estado_asistencia='LLEGADA_TARDE'");
    $stmt->execute([':f' => $f['id'], ':h' => $hoy]);
    $f['tardes'] = (int) $stmt->fetchColumn();

    $f['sin_ingreso'] = max($f['total_aprendices'] - $f['presentes'] - $f['tardes'], 0);
}
unset($f);

// Alertas activas de ambientes (nuevo requerimiento), visibles para cualquier instructor.
$alertasActivas = (int) $pdo->query("SELECT COUNT(*) FROM novedades_ambiente WHERE estado = 'ACTIVA'")->fetchColumn();
$alertasMuyImportantes = (int) $pdo->query("SELECT COUNT(*) FROM novedades_ambiente WHERE estado = 'ACTIVA' AND prioridad = 'MUY_IMPORTANTE'")->fetchColumn();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($alertasActivas > 0): ?>
<div class="alert alert-<?= $alertasMuyImportantes > 0 ? 'danger' : 'warning' ?> d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span><i class="bi bi-exclamation-triangle-fill"></i> Hay <?= $alertasActivas ?> alerta(s) activa(s) en ambientes de formación<?= $alertasMuyImportantes > 0 ? ', ' . $alertasMuyImportantes . ' muy importante(s)' : '' ?>.</span>
    <a href="<?= rutaBase() ?>/views/ambientes/alertas.php" class="btn btn-sm btn-dark">Ver alertas</a>
</div>
<?php endif; ?>

<div class="d-flex gap-2 flex-wrap mb-4">
    <a href="<?= rutaBase() ?>/views/instructor/entregar_ambiente.php" class="btn btn-sena"><i class="bi bi-box-arrow-up"></i> Entregar ambiente de formación</a>
</div>

<?php if (!$fichas): ?>
    <div class="alert alert-info">Aún no tiene ninguna ficha asignada. Contacte al coordinador académico.</div>
<?php endif; ?>

<?php foreach ($fichas as $f): ?>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h5 class="fw-bold mb-0"><?= htmlspecialchars($f['numero_ficha']) ?></h5>
                <p class="text-muted mb-2"><?= htmlspecialchars($f['programa']) ?> · <?= formatoHora12($f['hora_inicio']) ?> – <?= formatoHora12($f['hora_fin']) ?></p>
            </div>
            <span class="badge <?= $f['estado'] === 'ACTIVA' ? 'bg-success' : 'bg-dark' ?>"><?= $f['estado'] ?></span>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-4"><div class="card sena-card-indicador p-2 text-center"><div class="valor">🟢 <?= $f['presentes'] ?></div><div class="etiqueta">Presentes</div></div></div>
            <div class="col-4"><div class="card sena-card-indicador p-2 text-center"><div class="valor">🟡 <?= $f['tardes'] ?></div><div class="etiqueta">Tardes</div></div></div>
            <div class="col-4"><div class="card sena-card-indicador p-2 text-center"><div class="valor">🔴 <?= $f['sin_ingreso'] ?></div><div class="etiqueta">Sin ingreso</div></div></div>
        </div>

        <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="<?= rutaBase() ?>/views/asistencia/consulta.php?id_ficha=<?= $f['id'] ?>" class="btn btn-sena-outline btn-sm"><i class="bi bi-clipboard-check"></i> Consultar asistencia</a>
            <a href="<?= rutaBase() ?>/views/instructor/novedades.php" class="btn btn-sena-outline btn-sm"><i class="bi bi-bell"></i> Ver novedades</a>
            <a href="<?= rutaBase() ?>/views/asistencia/consulta.php?id_ficha=<?= $f['id'] ?>" class="btn btn-sena-outline btn-sm"><i class="bi bi-clock-history"></i> Ver historial</a>
        </div>
    </div>
</div>
<?php endforeach; ?>

<p class="small text-muted"><i class="bi bi-lock"></i> El instructor no toma asistencia: ya fue generada automáticamente mediante el control de ingreso.</p>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
