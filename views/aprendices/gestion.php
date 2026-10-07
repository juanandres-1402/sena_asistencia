<?php
/**
 * views/aprendices/gestion.php
 * ---------------------------------------------------------------------
 * NUEVO REQUERIMIENTO: "Tanto el coordinador como el instructor deben
 * poder ingresar aprendices a las fichas y realizar la carga masiva de
 * aprendices en el sistema."
 *
 * - El COORDINADOR puede gestionar cualquier ficha ACTIVA.
 * - El INSTRUCTOR solo puede gestionar las fichas ACTIVAS que tiene
 *   asignadas (id_instructor = su propio usuario).
 * - Un aprendiz nuevo recibe automáticamente una contraseña inicial
 *   igual a su número de documento. El coordinador es quien más
 *   adelante puede cambiar esa contraseña desde
 *   "Coordinador → Usuarios y contraseñas" (único módulo autorizado
 *   para administrar contraseñas de todos los usuarios).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador', 'instructor']);

$tituloPagina = 'Aprendices de la ficha';
$pdo = obtenerConexion();
$esCoordinador = $_SESSION['usuario_rol'] === 'coordinador';
$mensaje = null;
$resumenCarga = null;

// ---------------------------------------------------------------------
// Fichas que este usuario puede gestionar.
// ---------------------------------------------------------------------
if ($esCoordinador) {
    $fichas = $pdo->query("SELECT id, numero_ficha FROM fichas WHERE estado = 'ACTIVA' ORDER BY numero_ficha")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT id, numero_ficha FROM fichas WHERE estado = 'ACTIVA' AND id_instructor = :id ORDER BY numero_ficha");
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $fichas = $stmt->fetchAll();
}
$idsPermitidas = array_column($fichas, 'id');

// ---------------------------------------------------------------------
// Procesar formularios (alta individual / carga masiva)
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idFichaPost = (int) ($_POST['id_ficha'] ?? 0);
    $permitido = $esCoordinador || in_array($idFichaPost, $idsPermitidas, true);

    if (!$permitido) {
        $mensaje = ['tipo' => 'danger', 'texto' => 'No tiene permisos para matricular aprendices en esa ficha.'];
    } elseif ($accion === 'agregar_individual') {
        $resultado = registrarAprendizEnFicha($pdo, $_POST['documento'] ?? '', $_POST['nombre_completo'] ?? '', $_POST['correo'] ?? '', $idFichaPost);
        $mensaje = ['tipo' => $resultado['ok'] ? 'success' : 'danger', 'texto' => $resultado['mensaje']];
        if ($resultado['ok']) {
            registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Aprendiz matriculado en ficha', 'aprendices', '', ($_POST['documento'] ?? '') . ' -> ficha #' . $idFichaPost);
        }
    } elseif ($accion === 'carga_masiva') {
        if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
            $mensaje = ['tipo' => 'danger', 'texto' => 'Debe adjuntar un archivo CSV válido.'];
        } else {
            $resumenCarga = procesarCargaMasivaAprendices($pdo, $_FILES['archivo_csv']['tmp_name'], $idFichaPost);
            registrarAuditoria(
                $_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Carga masiva de aprendices', 'aprendices', '',
                "{$resumenCarga['creados']} creados, {$resumenCarga['vinculados']} vinculados, {$resumenCarga['omitidos']} omitidos (ficha #$idFichaPost)"
            );
            $mensaje = ['tipo' => 'success', 'texto' => 'Carga masiva procesada. Revise el resumen más abajo.'];
        }
    }

    $idFichaSeleccionada = $idFichaPost;
} else {
    $idFichaSeleccionada = (int) ($_GET['id_ficha'] ?? ($fichas[0]['id'] ?? 0));
}

if ($idFichaSeleccionada && !in_array($idFichaSeleccionada, $idsPermitidas, true)) {
    $idFichaSeleccionada = $fichas[0]['id'] ?? 0;
}

// Listado de aprendices de la ficha seleccionada.
$aprendices = [];
if ($idFichaSeleccionada) {
    $stmt = $pdo->prepare("
        SELECT u.nombre_completo, u.documento, u.correo, u.activo
        FROM aprendices a JOIN usuarios u ON u.id = a.id_usuario
        WHERE a.id_ficha = :ficha
        ORDER BY u.nombre_completo
    ");
    $stmt->execute([':ficha' => $idFichaSeleccionada]);
    $aprendices = $stmt->fetchAll();
}

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if (!$fichas): ?>
    <div class="alert alert-info">
        <?= $esCoordinador ? 'No hay fichas activas en el sistema.' : 'No tiene fichas activas asignadas. Contacte al coordinador académico.' ?>
    </div>
<?php else: ?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible alert-auto-cerrar"><?= htmlspecialchars($mensaje['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($resumenCarga): ?>
    <div class="card shadow-sm mb-3 border-success">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-upload"></i> Resumen de la carga masiva</div>
        <div class="card-body">
            <div class="row g-2 text-center mb-2">
                <div class="col-4"><div class="fw-bold fs-4 text-success"><?= $resumenCarga['creados'] ?></div><small>Creados</small></div>
                <div class="col-4"><div class="fw-bold fs-4 text-primary"><?= $resumenCarga['vinculados'] ?></div><small>Vinculados (ya existían)</small></div>
                <div class="col-4"><div class="fw-bold fs-4 text-danger"><?= $resumenCarga['omitidos'] ?></div><small>Omitidos</small></div>
            </div>
            <?php if ($resumenCarga['errores']): ?>
                <details class="small text-muted">
                    <summary>Ver detalle de filas omitidas</summary>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($resumenCarga['errores'] as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ===================== Selector de ficha ===================== -->
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <select name="id_ficha" class="form-select" onchange="this.form.submit()">
            <?php foreach ($fichas as $f): ?>
                <option value="<?= $f['id'] ?>" <?= $f['id'] == $idFichaSeleccionada ? 'selected' : '' ?>>Ficha <?= htmlspecialchars($f['numero_ficha']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabListado">Aprendices (<?= count($aprendices) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabIndividual">Matricular individual</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMasiva">Carga masiva (CSV)</button></li>
</ul>

<div class="tab-content">
    <!-- ===================== Listado ===================== -->
    <div class="tab-pane fade show active" id="tabListado">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="sena-thead"><tr><th>Nombre</th><th>Documento</th><th>Correo</th><th>Estado</th></tr></thead>
                <tbody>
                    <?php if (!$aprendices): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Esta ficha aún no tiene aprendices matriculados.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($aprendices as $ap): ?>
                    <tr>
                        <td><?= htmlspecialchars($ap['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($ap['documento']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($ap['correo'] ?: '—') ?></td>
                        <td><span class="badge <?= $ap['activo'] ? 'bg-success' : 'bg-secondary' ?>"><?= $ap['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================== Matricular individual ===================== -->
    <div class="tab-pane fade" id="tabIndividual">
        <div class="card shadow-sm" style="max-width:600px;">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="agregar_individual">
                    <input type="hidden" name="id_ficha" value="<?= $idFichaSeleccionada ?>">
                    <div class="mb-2">
                        <label class="form-label small">Número de documento</label>
                        <input type="text" name="documento" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nombre completo</label>
                        <input type="text" name="nombre_completo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Correo (opcional)</label>
                        <input type="email" name="correo" class="form-control">
                    </div>
                    <button class="btn btn-sena w-100"><i class="bi bi-person-plus"></i> Matricular en esta ficha</button>
                </form>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-info-circle"></i> Si el documento ya existe como aprendiz, solo se vincula a la ficha.
                    Si es nuevo, se crea con contraseña inicial igual a su documento.
                </small>
            </div>
        </div>
    </div>

    <!-- ===================== Carga masiva ===================== -->
    <div class="tab-pane fade" id="tabMasiva">
        <div class="card shadow-sm" style="max-width:600px;">
            <div class="card-body">
                <p class="small text-muted">
                    El archivo debe ser CSV con las columnas <code>documento,nombre_completo,correo</code>
                    (el correo puede ir vacío). La primera fila puede ser un encabezado.
                </p>
                <a href="<?= rutaBase() ?>/controllers/plantilla_csv.php?tipo=aprendices" class="btn btn-sena-outline btn-sm mb-3">
                    <i class="bi bi-download"></i> Descargar plantilla CSV
                </a>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="carga_masiva">
                    <input type="hidden" name="id_ficha" value="<?= $idFichaSeleccionada ?>">
                    <div class="mb-3">
                        <label class="form-label small">Archivo CSV</label>
                        <input type="file" name="archivo_csv" class="form-control" accept=".csv" required>
                    </div>
                    <button class="btn btn-sena w-100"><i class="bi bi-upload"></i> Procesar carga masiva</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
