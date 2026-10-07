<?php
/**
 * views/ambientes/alertas.php
 * ---------------------------------------------------------------------
 * NUEVO REQUERIMIENTO: Alertas activas de novedades de ambientes.
 *
 * Lista las novedades con estado ACTIVA (ordenadas por prioridad).
 * Tanto el coordinador como cualquier instructor pueden marcarlas como
 * CORREGIDAS una vez solucionado el problema: en ese momento la alerta
 * desaparece de esta vista, pero el registro permanece disponible en
 * el historial del ambiente (views/ambientes/historial.php).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador', 'instructor']);

$tituloPagina = 'Alertas de ambientes';
$pdo = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'marcar_corregida') {
    $idNovedad = (int) $_POST['id_novedad'];

    $stmt = $pdo->prepare("
        SELECT n.descripcion, a.nombre AS ambiente
        FROM novedades_ambiente n JOIN ambientes a ON a.id = n.id_ambiente
        WHERE n.id = :id AND n.estado = 'ACTIVA'
    ");
    $stmt->execute([':id' => $idNovedad]);
    $novedad = $stmt->fetch();

    if ($novedad) {
        $pdo->prepare("
            UPDATE novedades_ambiente
            SET estado = 'CORREGIDA', corregida_por = :usuario, fecha_correccion = :fecha
            WHERE id = :id
        ")->execute([
            ':usuario' => $_SESSION['usuario_id'],
            ':fecha'   => date('Y-m-d H:i:s'),
            ':id'      => $idNovedad,
        ]);

        registrarAuditoria(
            $_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Novedad de ambiente marcada como corregida',
            'novedades_ambiente', 'ACTIVA', 'CORREGIDA (' . $novedad['ambiente'] . ')'
        );
        establecerMensajeFlash('success', 'La novedad fue marcada como corregida. El registro queda disponible en el historial del ambiente.');
    }
    header('Location: ' . rutaBase() . '/views/ambientes/alertas.php');
    exit;
}

$mensajeFlash = obtenerMensajeFlash();

// Orden por prioridad (MUY_IMPORTANTE primero) y luego por fecha más reciente.
$alertas = $pdo->query("
    SELECT n.*, a.nombre AS ambiente, u.nombre_completo AS reportado_por_nombre, i.nombre_elemento,
        CASE n.prioridad WHEN 'MUY_IMPORTANTE' THEN 1 WHEN 'IMPORTANTE' THEN 2 ELSE 3 END AS orden_prioridad
    FROM novedades_ambiente n
    JOIN ambientes a ON a.id = n.id_ambiente
    JOIN usuarios u ON u.id = n.reportada_por
    LEFT JOIN inventario_ambiente i ON i.id = n.id_item_inventario
    WHERE n.estado = 'ACTIVA'
    ORDER BY orden_prioridad ASC, n.fecha_hora DESC
")->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($mensajeFlash): ?>
    <div class="alert alert-<?= $mensajeFlash['tipo'] ?> alert-dismissible alert-auto-cerrar"><?= $mensajeFlash['texto'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<p class="text-muted">Novedades activas reportadas en la entrega de ambientes de formación (<?= count($alertas) ?>).</p>

<?php if (!$alertas): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> No hay alertas activas en este momento. Todos los ambientes están al día.</div>
<?php endif; ?>

<div class="row g-3">
    <?php foreach ($alertas as $a): $p = etiquetaPrioridad($a['prioridad']); ?>
    <div class="col-md-6">
        <div class="card shadow-sm h-100 <?= $a['prioridad'] === 'MUY_IMPORTANTE' ? 'border-danger' : '' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge <?= $p['clase'] ?>"><i class="bi <?= $p['icono'] ?>"></i> <?= $p['texto'] ?></span>
                    <small class="text-muted"><?= htmlspecialchars($a['fecha_hora']) ?></small>
                </div>
                <h6 class="fw-bold mb-0"><?= htmlspecialchars($a['ambiente']) ?></h6>
                <small class="text-muted d-block mb-2"><?= htmlspecialchars($a['nombre_elemento'] ?? 'Novedad general del ambiente') ?></small>
                <p class="mb-2"><?= nl2br(htmlspecialchars($a['descripcion'])) ?></p>

                <?php if ($a['evidencia_foto']): ?>
                    <a href="<?= rutaBase() ?>/<?= htmlspecialchars($a['evidencia_foto']) ?>" target="_blank">
                        <img src="<?= rutaBase() ?>/<?= htmlspecialchars($a['evidencia_foto']) ?>" class="img-fluid rounded mb-2" style="max-height:160px;">
                    </a>
                <?php endif; ?>

                <p class="small text-muted mb-3">Reportada por <?= htmlspecialchars($a['reportado_por_nombre']) ?></p>

                <form method="POST" data-confirmar="¿Confirma que esta novedad ya fue corregida?">
                    <input type="hidden" name="accion" value="marcar_corregida">
                    <input type="hidden" name="id_novedad" value="<?= $a['id'] ?>">
                    <button class="btn btn-sena-outline btn-sm w-100"><i class="bi bi-check2-circle"></i> Marcar como corregida</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
