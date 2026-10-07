<?php
/**
 * views/auditoria/auditoria.php
 * ---------------------------------------------------------------------
 * AUDITORÍA (punto 18 del documento - "MUY IMPORTANTE").
 * Muestra cada cambio relevante realizado en el sistema: quién lo
 * hizo, cuándo, qué acción fue y los valores anterior/nuevo cuando
 * aplica. Esta tabla se alimenta desde includes/funciones.php ->
 * registrarAuditoria(), llamada en cada punto del sistema donde se
 * crea, modifica o elimina algo relevante.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Auditoría del sistema';
$pdo = obtenerConexion();

$filtroAccion = trim($_GET['accion'] ?? '');

$sql = "SELECT * FROM auditoria";
$params = [];
if ($filtroAccion !== '') {
    $sql .= " WHERE accion LIKE :accion";
    $params[':accion'] = '%' . $filtroAccion . '%';
}
$sql .= " ORDER BY id DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4">
        <input type="text" name="accion" class="form-control" placeholder="Filtrar por acción (ej: ficha, ingreso, equipo)" value="<?= htmlspecialchars($filtroAccion) ?>">
    </div>
    <div class="col-md-2">
        <button class="btn btn-sena-outline w-100"><i class="bi bi-filter"></i> Filtrar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead class="sena-thead">
            <tr><th>Fecha / hora</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>Valor anterior</th><th>Valor nuevo</th></tr>
        </thead>
        <tbody>
            <?php if (!$registros): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No hay registros de auditoría con ese filtro.</td></tr>
            <?php endif; ?>
            <?php foreach ($registros as $r): ?>
            <tr>
                <td class="text-nowrap"><?= htmlspecialchars($r['fecha_hora']) ?></td>
                <td><?= htmlspecialchars($r['usuario_nombre'] ?? '—') ?></td>
                <td><?= htmlspecialchars($r['accion']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($r['entidad'] ?: '—') ?></span></td>
                <td class="text-muted small"><?= htmlspecialchars($r['valor_anterior'] ?: '—') ?></td>
                <td class="text-muted small"><?= htmlspecialchars($r['valor_nuevo'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
