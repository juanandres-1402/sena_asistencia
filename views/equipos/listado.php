<?php
/**
 * views/equipos/listado.php
 * ---------------------------------------------------------------------
 * Módulo EQUIPOS (punto 8 del documento). Permite ver todos los
 * dispositivos registrados en el sistema y su aprendiz responsable,
 * y registrar un equipo nuevo manualmente (además del registro rápido
 * que ocurre desde la pantalla de Control de Ingreso).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador', 'porteria']);

$tituloPagina = 'Equipos registrados';
$pdo = obtenerConexion();

// Alta manual de un equipo (formulario clásico, sin AJAX, para variar el patrón).
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_equipo') {
    $documentoResponsable = trim($_POST['documento_responsable'] ?? '');

    $stmtAprendiz = $pdo->prepare("
        SELECT a.id FROM aprendices a JOIN usuarios u ON u.id = a.id_usuario WHERE u.documento = :doc LIMIT 1
    ");
    $stmtAprendiz->execute([':doc' => $documentoResponsable]);
    $idAprendiz = $stmtAprendiz->fetchColumn();

    if (!$idAprendiz) {
        $mensaje = ['tipo' => 'danger', 'texto' => 'No se encontró un aprendiz con ese documento.'];
    } else {
        $codigo = generarCodigoEquipo();
        $stmt = $pdo->prepare("
            INSERT INTO equipos (codigo_equipo, tipo_equipo, marca, modelo, numero_serie, codigo_cargador, id_aprendiz)
            VALUES (:codigo, :tipo, :marca, :modelo, :serie, :cargador, :id_aprendiz)
        ");
        $stmt->execute([
            ':codigo'  => $codigo,
            ':tipo'    => $_POST['tipo_equipo'] ?? 'portatil',
            ':marca'   => trim($_POST['marca'] ?? ''),
            ':modelo'  => trim($_POST['modelo'] ?? ''),
            ':serie'   => trim($_POST['numero_serie'] ?? ''),
            ':cargador'=> trim($_POST['codigo_cargador'] ?? ''),
            ':id_aprendiz' => $idAprendiz,
        ]);
        registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Registro manual de equipo', 'equipos', '', $codigo);
        $mensaje = ['tipo' => 'success', 'texto' => "Equipo $codigo registrado correctamente."];
    }
}

$equipos = $pdo->query("
    SELECT e.codigo_equipo, e.tipo_equipo, e.marca, e.modelo, e.numero_serie, e.codigo_cargador,
           u.nombre_completo AS responsable, f.numero_ficha
    FROM equipos e
    LEFT JOIN aprendices a ON a.id = e.id_aprendiz
    LEFT JOIN usuarios u ON u.id = a.id_usuario
    LEFT JOIN fichas f ON f.id = a.id_ficha
    ORDER BY e.id DESC
")->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible alert-auto-cerrar">
        <?= htmlspecialchars($mensaje['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-plus-circle"></i> Registrar equipo</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="crear_equipo">
                    <div class="mb-2">
                        <label class="form-label small">Documento del aprendiz responsable</label>
                        <input type="text" name="documento_responsable" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Tipo de equipo</label>
                        <select name="tipo_equipo" class="form-select form-select-sm">
                            <option value="portatil">Portátil</option>
                            <option value="tablet">Tablet</option>
                            <option value="institucional">Institucional</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Marca</label>
                        <input type="text" name="marca" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Modelo</label>
                        <input type="text" name="modelo" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Número de serie</label>
                        <input type="text" name="numero_serie" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Código del cargador</label>
                        <input type="text" name="codigo_cargador" class="form-control form-control-sm">
                    </div>
                    <button class="btn btn-sena w-100"><i class="bi bi-save"></i> Guardar equipo</button>
                </form>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-info-circle"></i> El código del equipo (ej. PORT-000125) se genera automáticamente.
                    Este módulo queda preparado para asociar en el futuro un código QR, de barras, RFID o NFC.
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-laptop"></i> Equipos registrados (<?= count($equipos) ?>)</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="sena-thead">
                        <tr><th>Código</th><th>Tipo</th><th>Marca / Modelo</th><th>Serial</th><th>Responsable</th><th>Ficha</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$equipos): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No hay equipos registrados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($equipos as $eq): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($eq['codigo_equipo']) ?></td>
                            <td><span class="badge bg-secondary text-capitalize"><?= htmlspecialchars($eq['tipo_equipo']) ?></span></td>
                            <td><?= htmlspecialchars($eq['marca'] . ' ' . $eq['modelo']) ?></td>
                            <td><?= htmlspecialchars($eq['numero_serie']) ?></td>
                            <td><?= htmlspecialchars($eq['responsable'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($eq['numero_ficha'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
