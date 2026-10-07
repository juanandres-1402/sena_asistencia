<?php
/**
 * views/reportes/reportes.php
 * ---------------------------------------------------------------------
 * Reportes (punto 14, parte de gráficas e indicadores). Permite
 * consultar la asistencia consolidada filtrando por ficha y rango de
 * fecha, y exportarla a CSV para análisis externo.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Reportes de asistencia';
$pdo = obtenerConexion();

$idFicha    = $_GET['id_ficha'] ?? '';
$fechaDesde = $_GET['desde'] ?? date('Y-m-01');
$fechaHasta = $_GET['hasta'] ?? date('Y-m-d');

$sql = "
    SELECT a.fecha, u.nombre_completo, u.documento, f.numero_ficha, a.estado, a.origen
    FROM asistencias a
    JOIN aprendices ap ON ap.id = a.id_aprendiz
    JOIN usuarios u ON u.id = ap.id_usuario
    JOIN fichas f ON f.id = a.id_ficha
    WHERE a.fecha BETWEEN :desde AND :hasta
";
$params = [':desde' => $fechaDesde, ':hasta' => $fechaHasta];

if ($idFicha !== '') {
    $sql .= " AND f.id = :ficha";
    $params[':ficha'] = $idFicha;
}
$sql .= " ORDER BY a.fecha DESC, u.nombre_completo";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll();

$fichas = $pdo->query("SELECT id, numero_ficha FROM fichas")->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<form class="row g-2 mb-3" method="GET">
    <div class="col-md-3">
        <label class="form-label small">Ficha</label>
        <select name="id_ficha" class="form-select">
            <option value="">Todas</option>
            <?php foreach ($fichas as $f): ?>
                <option value="<?= $f['id'] ?>" <?= (string) $idFicha === (string) $f['id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['numero_ficha']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small">Desde</label>
        <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($fechaDesde) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label small">Hasta</label>
        <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($fechaHasta) ?>">
    </div>
    <div class="col-md-3 d-flex align-items-end gap-2">
        <button class="btn btn-sena-outline flex-fill"><i class="bi bi-filter"></i> Filtrar</button>
        <button type="button" id="btnExportarCsv" class="btn btn-sena flex-fill"><i class="bi bi-download"></i> CSV</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-sm" id="tablaReporte">
        <thead class="sena-thead">
            <tr><th>Fecha</th><th>Aprendiz</th><th>Documento</th><th>Ficha</th><th>Estado</th><th>Origen</th></tr>
        </thead>
        <tbody>
            <?php if (!$resultados): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No hay registros para el filtro seleccionado.</td></tr>
            <?php endif; ?>
            <?php foreach ($resultados as $r): $e = etiquetaEstado($r['estado']); ?>
            <tr>
                <td><?= htmlspecialchars($r['fecha']) ?></td>
                <td><?= htmlspecialchars($r['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($r['documento']) ?></td>
                <td><?= htmlspecialchars($r['numero_ficha']) ?></td>
                <td><span class="badge <?= $e['clase'] ?>"><?= $e['texto'] ?></span></td>
                <td class="text-muted small"><?= htmlspecialchars($r['origen']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
/**
 * Exporta la tabla visible a un archivo CSV, generado completamente
 * en el navegador (no requiere ninguna librería externa).
 */
document.getElementById('btnExportarCsv').addEventListener('click', function () {
    const filas = document.querySelectorAll('#tablaReporte tr');
    let csv = [];
    filas.forEach(fila => {
        const columnas = fila.querySelectorAll('th, td');
        const valores = Array.from(columnas).map(c => '"' + c.innerText.replace(/"/g, '""') + '"');
        csv.push(valores.join(';'));
    });
    const blob = new Blob(['\ufeff' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const enlace = document.createElement('a');
    enlace.href = URL.createObjectURL(blob);
    enlace.download = 'reporte_asistencia.csv';
    enlace.click();
});
</script>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
