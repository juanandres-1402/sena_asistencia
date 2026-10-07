<?php
/**
 * views/coordinador/ambientes.php
 * ---------------------------------------------------------------------
 * NUEVO REQUERIMIENTO: Entrega de ambiente de formación con verificación
 * de inventario.
 *
 * El coordinador académico es quien crea los ambientes de formación y
 * configura su INVENTARIO BASE (lo que el instructor deberá verificar
 * cada vez que entregue el ambiente). Sin este paso, el módulo de
 * "Entregar ambiente" del instructor no tiene contra qué comparar.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Ambientes de formación';
$pdo = obtenerConexion();
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear_ambiente') {
        $stmt = $pdo->prepare("INSERT INTO ambientes (nombre, id_sede, descripcion) VALUES (:nombre, :sede, :descripcion)");
        $stmt->execute([
            ':nombre'      => trim($_POST['nombre']),
            ':sede'        => $_POST['id_sede'] ?: null,
            ':descripcion' => trim($_POST['descripcion'] ?? ''),
        ]);
        registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Creación de ambiente de formación', 'ambientes', '', $_POST['nombre']);
        $mensaje = ['tipo' => 'success', 'texto' => 'Ambiente "' . htmlspecialchars($_POST['nombre']) . '" creado correctamente. Ahora configure su inventario base.'];
    }

    if ($accion === 'agregar_item') {
        $stmt = $pdo->prepare("
            INSERT INTO inventario_ambiente (id_ambiente, nombre_elemento, cantidad, observaciones)
            VALUES (:ambiente, :nombre, :cantidad, :obs)
        ");
        $stmt->execute([
            ':ambiente' => (int) $_POST['id_ambiente'],
            ':nombre'   => trim($_POST['nombre_elemento']),
            ':cantidad' => (int) $_POST['cantidad'],
            ':obs'      => trim($_POST['observaciones'] ?? ''),
        ]);
        registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Elemento agregado al inventario base', 'inventario_ambiente', '', $_POST['nombre_elemento']);
        $mensaje = ['tipo' => 'success', 'texto' => 'Elemento agregado al inventario base.'];
    }

    if ($accion === 'eliminar_item') {
        $stmt = $pdo->prepare("SELECT nombre_elemento FROM inventario_ambiente WHERE id = :id");
        $stmt->execute([':id' => (int) $_POST['id_item']]);
        $nombreItem = $stmt->fetchColumn();

        $pdo->prepare("DELETE FROM inventario_ambiente WHERE id = :id")->execute([':id' => (int) $_POST['id_item']]);
        registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Elemento eliminado del inventario base', 'inventario_ambiente', (string) $nombreItem, '');
        $mensaje = ['tipo' => 'warning', 'texto' => 'Elemento eliminado del inventario base.'];
    }
}

$sedes = $pdo->query("SELECT * FROM sedes")->fetchAll();

$ambientes = $pdo->query("
    SELECT a.*, s.nombre AS sede_nombre,
           (SELECT COUNT(*) FROM inventario_ambiente i WHERE i.id_ambiente = a.id) AS total_items
    FROM ambientes a
    LEFT JOIN sedes s ON s.id = a.id_sede
    ORDER BY a.id DESC
")->fetchAll();

$idAmbienteSeleccionado = (int) ($_GET['id_ambiente'] ?? ($ambientes[0]['id'] ?? 0));
$inventario = [];
if ($idAmbienteSeleccionado) {
    $stmt = $pdo->prepare("SELECT * FROM inventario_ambiente WHERE id_ambiente = :id ORDER BY id");
    $stmt->execute([':id' => $idAmbienteSeleccionado]);
    $inventario = $stmt->fetchAll();
}

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible alert-auto-cerrar"><?= $mensaje['texto'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabAmbientes">Ambientes</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCrear">Crear ambiente</button></li>
</ul>

<div class="tab-content">
    <!-- ===================== Listado + inventario ===================== -->
    <div class="tab-pane fade show active" id="tabAmbientes">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="list-group">
                    <?php if (!$ambientes): ?>
                        <div class="alert alert-info">Aún no hay ambientes creados.</div>
                    <?php endif; ?>
                    <?php foreach ($ambientes as $a): ?>
                        <a href="?id_ambiente=<?= $a['id'] ?>"
                           class="list-group-item list-group-item-action <?= $a['id'] == $idAmbienteSeleccionado ? 'active' : '' ?>">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold"><?= htmlspecialchars($a['nombre']) ?></span>
                                <span class="badge bg-secondary"><?= $a['total_items'] ?> ítems</span>
                            </div>
                            <small class="<?= $a['id'] == $idAmbienteSeleccionado ? 'text-white-50' : 'text-muted' ?>"><?= htmlspecialchars($a['sede_nombre'] ?? 'Sin sede asignada') ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-8">
                <?php if ($idAmbienteSeleccionado): ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-plus-circle"></i> Agregar elemento al inventario base</div>
                    <div class="card-body">
                        <form method="POST" class="row g-2 align-items-end">
                            <input type="hidden" name="accion" value="agregar_item">
                            <input type="hidden" name="id_ambiente" value="<?= $idAmbienteSeleccionado ?>">
                            <div class="col-md-5">
                                <label class="form-label small">Elemento</label>
                                <input type="text" name="nombre_elemento" class="form-control form-control-sm" placeholder="Ej: Videobeam" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control form-control-sm" value="1" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Observaciones</label>
                                <input type="text" name="observaciones" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-sena btn-sm w-100"><i class="bi bi-save"></i> Agregar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="sena-thead"><tr><th>Elemento</th><th>Cantidad</th><th>Observaciones</th><th class="text-end">Acción</th></tr></thead>
                        <tbody>
                            <?php if (!$inventario): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">Este ambiente aún no tiene inventario base configurado.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($inventario as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nombre_elemento']) ?></td>
                                <td><?= $item['cantidad'] ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($item['observaciones'] ?: '—') ?></td>
                                <td class="text-end">
                                    <form method="POST" data-confirmar="¿Eliminar este elemento del inventario base?">
                                        <input type="hidden" name="accion" value="eliminar_item">
                                        <input type="hidden" name="id_item" value="<?= $item['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-info">Seleccione o cree un ambiente para configurar su inventario.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===================== Crear ambiente ===================== -->
    <div class="tab-pane fade" id="tabCrear">
        <div class="card shadow-sm" style="max-width:600px;">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="crear_ambiente">
                    <div class="mb-2">
                        <label class="form-label small">Nombre del ambiente</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Ambiente 305 - Laboratorio de Redes" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Sede / ubicación</label>
                        <select name="id_sede" class="form-select">
                            <option value="">Sin asignar</option>
                            <?php foreach ($sedes as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    <button class="btn btn-sena w-100"><i class="bi bi-plus-circle"></i> Crear ambiente</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
