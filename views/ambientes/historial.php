<?php
/**
 * views/ambientes/historial.php
 * ---------------------------------------------------------------------
 * NUEVO REQUERIMIENTO: Historial de novedades de un ambiente.
 *
 * Aunque una novedad se marque como CORREGIDA y su alerta desaparezca
 * de views/ambientes/alertas.php, el registro permanece disponible
 * aquí de forma permanente, junto con el historial de entregas.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador', 'instructor']);

$tituloPagina = 'Historial de ambientes';
$pdo = obtenerConexion();

$ambientes = $pdo->query("SELECT id, nombre FROM ambientes ORDER BY nombre")->fetchAll();
$idAmbiente = (int) ($_GET['id_ambiente'] ?? ($ambientes[0]['id'] ?? 0));

$entregas = [];
$novedades = [];
if ($idAmbiente) {
    $stmt = $pdo->prepare("
        SELECT e.*, ue.nombre_completo AS entrega_nombre, ur.nombre_completo AS recibe_nombre
        FROM entregas_ambiente e
        JOIN usuarios ue ON ue.id = e.id_instructor_entrega
        LEFT JOIN usuarios ur ON ur.id = e.id_instructor_recibe
        WHERE e.id_ambiente = :id
        ORDER BY e.id DESC
    ");
    $stmt->execute([':id' => $idAmbiente]);
    $entregas = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT n.*, u.nombre_completo AS reportado_por_nombre, c.nombre_completo AS corregida_por_nombre, i.nombre_elemento
        FROM novedades_ambiente n
        JOIN usuarios u ON u.id = n.reportada_por
        LEFT JOIN usuarios c ON c.id = n.corregida_por
        LEFT JOIN inventario_ambiente i ON i.id = n.id_item_inventario
        WHERE n.id_ambiente = :id
        ORDER BY n.fecha_hora DESC
    ");
    $stmt->execute([':id' => $idAmbiente]);
    $novedades = $stmt->fetchAll();
}

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<form class="row g-2 mb-4" method="GET">
    <div class="col-md-5">
        <select name="id_ambiente" class="form-select" onchange="this.form.submit()">
            <?php foreach ($ambientes as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $a['id'] == $idAmbiente ? 'selected' : '' ?>><?= htmlspecialchars($a['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabNovedades">Novedades (<?= count($novedades) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabEntregas">Entregas (<?= count($entregas) ?>)</button></li>
</ul>

<div class="tab-content">
    <!-- ===================== Novedades (activas + corregidas) ===================== -->
    <div class="tab-pane fade show active" id="tabNovedades">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="sena-thead">
                    <tr><th>Fecha</th><th>Elemento</th><th>Descripción</th><th>Prioridad</th><th>Reportada por</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php if (!$novedades): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Este ambiente no tiene novedades registradas.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($novedades as $n): $p = etiquetaPrioridad($n['prioridad']); ?>
                    <tr>
                        <td class="text-nowrap small"><?= htmlspecialchars($n['fecha_hora']) ?></td>
                        <td><?= htmlspecialchars($n['nombre_elemento'] ?? 'General') ?></td>
                        <td>
                            <?= htmlspecialchars($n['descripcion']) ?>
                            <?php if ($n['evidencia_foto']): ?>
                                <a href="<?= rutaBase() ?>/<?= htmlspecialchars($n['evidencia_foto']) ?>" target="_blank" class="ms-1"><i class="bi bi-image"></i></a>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $p['clase'] ?>"><?= $p['texto'] ?></span></td>
                        <td class="small"><?= htmlspecialchars($n['reportado_por_nombre']) ?></td>
                        <td>
                            <?php if ($n['estado'] === 'ACTIVA'): ?>
                                <span class="badge bg-danger">ACTIVA</span>
                            <?php else: ?>
                                <span class="badge bg-success">CORREGIDA</span>
                                <small class="d-block text-muted"><?= htmlspecialchars($n['corregida_por_nombre'] ?? '') ?> · <?= htmlspecialchars($n['fecha_correccion'] ?? '') ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================== Entregas ===================== -->
    <div class="tab-pane fade" id="tabEntregas">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="sena-thead"><tr><th>Fecha</th><th>Entregado por</th><th>Recibido por</th></tr></thead>
                <tbody>
                    <?php if (!$entregas): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No hay entregas registradas para este ambiente.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($entregas as $e): ?>
                    <tr>
                        <td class="small"><?= htmlspecialchars($e['fecha_hora']) ?></td>
                        <td><?= htmlspecialchars($e['entrega_nombre']) ?></td>
                        <td><?= htmlspecialchars($e['recibe_nombre'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
