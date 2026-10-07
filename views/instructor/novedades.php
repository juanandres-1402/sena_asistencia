<?php
/**
 * views/instructor/novedades.php
 * ---------------------------------------------------------------------
 * Novedades visibles para el instructor: llegadas tarde y ausencias de
 * sus propias fichas, generadas automáticamente por el sistema.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['instructor']);

$tituloPagina = 'Novedades';
$pdo = obtenerConexion();

$stmt = $pdo->prepare("
    SELECT a.fecha, a.estado, u.nombre_completo, f.numero_ficha
    FROM asistencias a
    JOIN aprendices ap ON ap.id = a.id_aprendiz
    JOIN usuarios u ON u.id = ap.id_usuario
    JOIN fichas f ON f.id = a.id_ficha
    WHERE f.id_instructor = :id AND a.estado IN ('LLEGADA_TARDE','SIN_INGRESO')
    ORDER BY a.fecha DESC
    LIMIT 100
");
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$novedades = $stmt->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead class="sena-thead"><tr><th>Fecha</th><th>Aprendiz</th><th>Ficha</th><th>Novedad</th></tr></thead>
        <tbody>
            <?php if (!$novedades): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No hay novedades registradas.</td></tr>
            <?php endif; ?>
            <?php foreach ($novedades as $n): $e = etiquetaEstado($n['estado']); ?>
            <tr>
                <td><?= htmlspecialchars($n['fecha']) ?></td>
                <td><?= htmlspecialchars($n['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($n['numero_ficha']) ?></td>
                <td><span class="badge <?= $e['clase'] ?>"><?= $e['texto'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
