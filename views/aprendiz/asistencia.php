<?php
/**
 * views/aprendiz/asistencia.php
 * ---------------------------------------------------------------------
 * Historial completo de asistencia del aprendiz en sesión (solo lectura).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['aprendiz']);

$tituloPagina = 'Mi asistencia';
$pdo = obtenerConexion();

$stmtAprendiz = $pdo->prepare("SELECT id FROM aprendices WHERE id_usuario = :id");
$stmtAprendiz->execute([':id' => $_SESSION['usuario_id']]);
$idAprendiz = $stmtAprendiz->fetchColumn();

$historial = [];
if ($idAprendiz) {
    $stmt = $pdo->prepare("
        SELECT a.fecha, a.estado, a.origen, i.hora, s.hora AS hora_salida
        FROM asistencias a
        LEFT JOIN ingresos i ON i.id = a.id_ingreso
        LEFT JOIN salidas s ON s.id_ingreso = i.id
        WHERE a.id_aprendiz = :id
        ORDER BY a.fecha DESC
    ");
    $stmt->execute([':id' => $idAprendiz]);
    $historial = $stmt->fetchAll();
}

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead class="sena-thead"><tr><th>Fecha</th><th>Ingreso</th><th>Salida</th><th>Estado</th></tr></thead>
        <tbody>
            <?php if (!$historial): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Aún no tiene registros de asistencia.</td></tr>
            <?php endif; ?>
            <?php foreach ($historial as $h): $e = etiquetaEstado($h['estado']); ?>
            <tr>
                <td><?= htmlspecialchars($h['fecha']) ?></td>
                <td><?= $h['hora'] ? formatoHora12($h['hora']) : '—' ?></td>
                <td><?= $h['hora_salida'] ? formatoHora12($h['hora_salida']) : '—' ?></td>
                <td><span class="badge <?= $e['clase'] ?>"><?= $e['punto'] ?> <?= $e['texto'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
