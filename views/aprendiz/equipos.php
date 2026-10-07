<?php
/**
 * views/aprendiz/equipos.php
 * ---------------------------------------------------------------------
 * Equipos registrados a nombre del aprendiz en sesión (solo lectura).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['aprendiz']);

$tituloPagina = 'Mis equipos';
$pdo = obtenerConexion();

$stmtAprendiz = $pdo->prepare("SELECT id FROM aprendices WHERE id_usuario = :id");
$stmtAprendiz->execute([':id' => $_SESSION['usuario_id']]);
$idAprendiz = $stmtAprendiz->fetchColumn();

$equipos = [];
if ($idAprendiz) {
    $stmt = $pdo->prepare("SELECT * FROM equipos WHERE id_aprendiz = :id");
    $stmt->execute([':id' => $idAprendiz]);
    $equipos = $stmt->fetchAll();
}

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<div class="row g-3">
    <?php if (!$equipos): ?>
        <div class="col-12"><div class="alert alert-info">No tiene equipos registrados.</div></div>
    <?php endif; ?>
    <?php foreach ($equipos as $eq): ?>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-laptop fs-2 text-success"></i>
                <h6 class="fw-bold mt-2"><?= htmlspecialchars($eq['codigo_equipo']) ?></h6>
                <p class="small text-muted mb-1"><?= htmlspecialchars($eq['marca'] . ' ' . $eq['modelo']) ?></p>
                <p class="small text-muted mb-0">Serial: <?= htmlspecialchars($eq['numero_serie']) ?></p>
                <p class="small text-muted mb-0">Cargador: <?= htmlspecialchars($eq['codigo_cargador'] ?: '—') ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
