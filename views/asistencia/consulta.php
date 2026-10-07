<?php
/**
 * views/asistencia/consulta.php
 * ---------------------------------------------------------------------
 * CONSULTAR ASISTENCIA (puntos 1 y 16 del documento).
 * Pantalla de solo lectura. El instructor jamás ve aquí un botón para
 * "tomar asistencia": la asistencia ya fue generada automáticamente
 * por el control de ingreso en portería.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador', 'instructor']);

$tituloPagina = 'Consultar asistencia';
$pdo = obtenerConexion();
$rol = $_SESSION['usuario_rol'];

// El instructor solo puede ver las fichas que tiene asignadas.
if ($rol === 'instructor') {
    $fichas = $pdo->prepare("SELECT id, numero_ficha FROM fichas WHERE id_instructor = :id");
    $fichas->execute([':id' => $_SESSION['usuario_id']]);
} else {
    $fichas = $pdo->query("SELECT id, numero_ficha FROM fichas");
}
$fichas = $fichas->fetchAll();

$idFichaSeleccionada = $_GET['id_ficha'] ?? ($fichas[0]['id'] ?? null);
$fecha = $_GET['fecha'] ?? date('Y-m-d');

$registros = [];
if ($idFichaSeleccionada) {
    // Para un instructor, se valida que la ficha solicitada sea realmente suya.
    $idsPermitidos = array_column($fichas, 'id');
    if (in_array((int) $idFichaSeleccionada, $idsPermitidos, true)) {
        $stmt = $pdo->prepare("
            SELECT u.nombre_completo, u.documento, a.estado, a.origen, i.hora
            FROM asistencias a
            JOIN aprendices ap ON ap.id = a.id_aprendiz
            JOIN usuarios u ON u.id = ap.id_usuario
            LEFT JOIN ingresos i ON i.id = a.id_ingreso
            WHERE a.id_ficha = :ficha AND a.fecha = :fecha
            ORDER BY u.nombre_completo
        ");
        $stmt->execute([':ficha' => $idFichaSeleccionada, ':fecha' => $fecha]);
        $registros = $stmt->fetchAll();

        // Aprendices de la ficha que NO tienen ningún registro ese día -> SIN INGRESO
        $stmtTodos = $pdo->prepare("
            SELECT u.nombre_completo, u.documento FROM aprendices ap
            JOIN usuarios u ON u.id = ap.id_usuario
            WHERE ap.id_ficha = :ficha
            AND ap.id NOT IN (SELECT id_aprendiz FROM asistencias WHERE id_ficha = :ficha2 AND fecha = :fecha2)
        ");
        $stmtTodos->execute([':ficha' => $idFichaSeleccionada, ':ficha2' => $idFichaSeleccionada, ':fecha2' => $fecha]);
        foreach ($stmtTodos->fetchAll() as $sinIngreso) {
            $registros[] = [
                'nombre_completo' => $sinIngreso['nombre_completo'],
                'documento' => $sinIngreso['documento'],
                'estado' => 'SIN_INGRESO',
                'origen' => '—',
                'hora' => null,
            ];
        }
    }
}

// Resumen rápido para la cabecera.
$conteo = ['PRESENTE' => 0, 'LLEGADA_TARDE' => 0, 'SIN_INGRESO' => 0, 'SALIDA_CAMPO' => 0, 'JUSTIFICADA' => 0];
foreach ($registros as $r) {
    $conteo[$r['estado']] = ($conteo[$r['estado']] ?? 0) + 1;
}

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4">
        <label class="form-label small">Ficha</label>
        <select name="id_ficha" class="form-select">
            <?php foreach ($fichas as $f): ?>
                <option value="<?= $f['id'] ?>" <?= (string) $idFichaSeleccionada === (string) $f['id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['numero_ficha']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small">Fecha</label>
        <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($fecha) ?>">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-sena-outline w-100"><i class="bi bi-search"></i> Consultar</button>
    </div>
</form>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card sena-card-indicador p-2 text-center"><div class="valor" style="font-size:1.4rem">🟢 <?= $conteo['PRESENTE'] ?></div><div class="etiqueta">Presentes</div></div></div>
    <div class="col-6 col-md-3"><div class="card sena-card-indicador p-2 text-center"><div class="valor" style="font-size:1.4rem">🟡 <?= $conteo['LLEGADA_TARDE'] ?></div><div class="etiqueta">Tarde</div></div></div>
    <div class="col-6 col-md-3"><div class="card sena-card-indicador p-2 text-center"><div class="valor" style="font-size:1.4rem">🔴 <?= $conteo['SIN_INGRESO'] ?></div><div class="etiqueta">Sin ingreso</div></div></div>
    <div class="col-6 col-md-3"><div class="card sena-card-indicador p-2 text-center"><div class="valor" style="font-size:1.4rem">⚪ <?= $conteo['SALIDA_CAMPO'] + $conteo['JUSTIFICADA'] ?></div><div class="etiqueta">Excepciones</div></div></div>
</div>

<div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead class="sena-thead"><tr><th>Aprendiz</th><th>Documento</th><th>Hora ingreso</th><th>Estado</th></tr></thead>
        <tbody>
            <?php if (!$registros): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No hay información de asistencia para esta ficha en la fecha seleccionada.</td></tr>
            <?php endif; ?>
            <?php foreach ($registros as $r): $e = etiquetaEstado($r['estado']); ?>
            <tr>
                <td><?= htmlspecialchars($r['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($r['documento']) ?></td>
                <td><?= $r['hora'] ? formatoHora12($r['hora']) : '—' ?></td>
                <td><span class="badge <?= $e['clase'] ?>"><?= $e['punto'] ?> <?= $e['texto'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="small text-muted"><i class="bi bi-lock"></i> Esta pantalla es de solo consulta: la asistencia se genera automáticamente desde el control de ingreso en portería.</p>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
