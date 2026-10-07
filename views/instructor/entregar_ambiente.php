<?php
/**
 * views/instructor/entregar_ambiente.php
 * ---------------------------------------------------------------------
 * NUEVO REQUERIMIENTO: Entrega de ambiente de formación.
 *
 * El instructor entrega el ambiente en el que dictó clase, verificando
 * el inventario BASE configurado por el coordinador (ver
 * views/coordinador/ambientes.php). Por cada elemento puede marcar
 * "todo en orden" o reportar una novedad con:
 *   - Texto explicativo (obligatorio)
 *   - Evidencia fotográfica (opcional mientras no sea MUY_IMPORTANTE)
 *   - Prioridad: Poco importante, Importante, Muy importante
 *
 * También puede reportar una novedad GENERAL del ambiente (no asociada
 * a un elemento puntual del inventario).
 *
 * Si alguna novedad queda con prioridad MUY_IMPORTANTE, el sistema
 * notifica DE INMEDIATO al coordinador y al instructor que recibe el
 * ambiente (si fue seleccionado), para que este último no tenga que
 * volver a reportarla al llegar.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['instructor']);

$tituloPagina = 'Entregar ambiente de formación';
$pdo = obtenerConexion();

// ---------------------------------------------------------------------
// Procesar el envío del formulario (patrón POST-Redirect-GET)
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idAmbiente = (int) ($_POST['id_ambiente'] ?? 0);
    $idInstructorRecibe = $_POST['id_instructor_recibe'] !== '' ? (int) $_POST['id_instructor_recibe'] : null;

    $stmtAmbiente = $pdo->prepare("SELECT nombre FROM ambientes WHERE id = :id");
    $stmtAmbiente->execute([':id' => $idAmbiente]);
    $nombreAmbiente = $stmtAmbiente->fetchColumn();

    if (!$idAmbiente || !$nombreAmbiente) {
        establecerMensajeFlash('danger', 'Debe seleccionar un ambiente válido.');
        header('Location: ' . rutaBase() . '/views/instructor/entregar_ambiente.php');
        exit;
    }

    $pdo->beginTransaction();
    try {
        // 1) Se crea la ENTREGA del ambiente.
        $stmtEntrega = $pdo->prepare("
            INSERT INTO entregas_ambiente (id_ambiente, id_instructor_entrega, id_instructor_recibe, fecha_hora)
            VALUES (:ambiente, :entrega, :recibe, :fecha)
        ");
        $stmtEntrega->execute([
            ':ambiente' => $idAmbiente,
            ':entrega'  => $_SESSION['usuario_id'],
            ':recibe'   => $idInstructorRecibe,
            ':fecha'    => date('Y-m-d H:i:s'),
        ]);
        $idEntrega = (int) $pdo->lastInsertId();

        $totalNovedades = 0;
        $huboMuyImportante = false;

        // 2) Se revisan las novedades por ELEMENTO del inventario.
        $estadoItems = $_POST['estado_item'] ?? [];
        foreach ($estadoItems as $idItem => $estado) {
            if ($estado !== 'novedad') {
                continue; // El elemento quedó "todo en orden", no genera novedad.
            }
            $idItem = (int) $idItem;
            $texto = trim($_POST['texto_item'][$idItem] ?? '');
            $prioridad = $_POST['prioridad_item'][$idItem] ?? 'POCO_IMPORTANTE';
            if ($texto === '') {
                continue; // Protección adicional: sin texto no se guarda la novedad.
            }

            $rutaFoto = null;
            if (!empty($_FILES['foto_item']['name'][$idItem])) {
                $archivo = [
                    'name'     => $_FILES['foto_item']['name'][$idItem],
                    'type'     => $_FILES['foto_item']['type'][$idItem],
                    'tmp_name' => $_FILES['foto_item']['tmp_name'][$idItem],
                    'error'    => $_FILES['foto_item']['error'][$idItem],
                    'size'     => $_FILES['foto_item']['size'][$idItem],
                ];
                $rutaFoto = guardarEvidenciaFoto($archivo);
            }

            $pdo->prepare("
                INSERT INTO novedades_ambiente (id_entrega, id_ambiente, id_item_inventario, descripcion, evidencia_foto, prioridad, estado, reportada_por, fecha_hora)
                VALUES (:entrega, :ambiente, :item, :descripcion, :foto, :prioridad, 'ACTIVA', :reportada_por, :fecha)
            ")->execute([
                ':entrega'      => $idEntrega,
                ':ambiente'     => $idAmbiente,
                ':item'         => $idItem,
                ':descripcion'  => $texto,
                ':foto'         => $rutaFoto,
                ':prioridad'    => $prioridad,
                ':reportada_por'=> $_SESSION['usuario_id'],
                ':fecha'        => date('Y-m-d H:i:s'),
            ]);
            $totalNovedades++;
            if ($prioridad === 'MUY_IMPORTANTE') {
                $huboMuyImportante = true;
            }
        }

        // 3) Novedad GENERAL del ambiente (no asociada a un elemento puntual).
        if (!empty($_POST['reportar_general']) && trim($_POST['texto_general'] ?? '') !== '') {
            $textoGeneral = trim($_POST['texto_general']);
            $prioridadGeneral = $_POST['prioridad_general'] ?? 'POCO_IMPORTANTE';

            $rutaFotoGeneral = null;
            if (!empty($_FILES['foto_general']['name'])) {
                $rutaFotoGeneral = guardarEvidenciaFoto($_FILES['foto_general']);
            }

            $pdo->prepare("
                INSERT INTO novedades_ambiente (id_entrega, id_ambiente, id_item_inventario, descripcion, evidencia_foto, prioridad, estado, reportada_por, fecha_hora)
                VALUES (:entrega, :ambiente, NULL, :descripcion, :foto, :prioridad, 'ACTIVA', :reportada_por, :fecha)
            ")->execute([
                ':entrega'      => $idEntrega,
                ':ambiente'     => $idAmbiente,
                ':descripcion'  => $textoGeneral,
                ':foto'         => $rutaFotoGeneral,
                ':prioridad'    => $prioridadGeneral,
                ':reportada_por'=> $_SESSION['usuario_id'],
                ':fecha'        => date('Y-m-d H:i:s'),
            ]);
            $totalNovedades++;
            if ($prioridadGeneral === 'MUY_IMPORTANTE') {
                $huboMuyImportante = true;
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        establecerMensajeFlash('danger', 'Ocurrió un error al registrar la entrega: ' . $e->getMessage());
        header('Location: ' . rutaBase() . '/views/instructor/entregar_ambiente.php');
        exit;
    }

    // 4) Notificación INMEDIATA si hubo alguna novedad MUY_IMPORTANTE:
    //    se notifica al coordinador y al instructor que recibe el ambiente,
    //    para que este último no tenga que volver a reportarla.
    if ($huboMuyImportante) {
        $mensajeNotif = 'Novedad MUY IMPORTANTE reportada en el ambiente "' . $nombreAmbiente . '" por ' . $_SESSION['usuario_nombre'] . '.';
        generarNotificacion(null, $mensajeNotif, 'NOVEDAD_AMBIENTE'); // null = visible para el coordinador
        if ($idInstructorRecibe) {
            generarNotificacion($idInstructorRecibe, $mensajeNotif . ' Ya quedó registrada: no es necesario volver a reportarla.', 'NOVEDAD_AMBIENTE');
        }
    }

    registrarAuditoria(
        $_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Entrega de ambiente de formación', 'ambientes',
        '', $nombreAmbiente . ' (' . $totalNovedades . ' novedad(es), ' . ($huboMuyImportante ? 'con alerta MUY IMPORTANTE' : 'sin alertas críticas') . ')'
    );

    establecerMensajeFlash('success', 'Entrega del ambiente "' . htmlspecialchars($nombreAmbiente) . '" registrada correctamente.' .
        ($totalNovedades > 0 ? " Se registraron $totalNovedades novedad(es)." : ' No se reportaron novedades.') .
        ($huboMuyImportante ? ' Se notificó de inmediato al coordinador' . ($idInstructorRecibe ? ' y al instructor que recibe el ambiente.' : '.') : ''));
    header('Location: ' . rutaBase() . '/views/instructor/entregar_ambiente.php');
    exit;
}

// ---------------------------------------------------------------------
// Datos para el formulario (GET)
// ---------------------------------------------------------------------
$mensajeFlash = obtenerMensajeFlash();

$ambientes = $pdo->query("SELECT id, nombre FROM ambientes ORDER BY nombre")->fetchAll();
$idAmbienteSeleccionado = (int) ($_GET['id_ambiente'] ?? ($ambientes[0]['id'] ?? 0));

$inventario = [];
$novedadesActivas = [];
if ($idAmbienteSeleccionado) {
    $stmt = $pdo->prepare("SELECT * FROM inventario_ambiente WHERE id_ambiente = :id ORDER BY id");
    $stmt->execute([':id' => $idAmbienteSeleccionado]);
    $inventario = $stmt->fetchAll();

    // Novedades activas YA reportadas para este ambiente: se muestran para
    // que el instructor no tenga que volver a reportarlas.
    $stmt = $pdo->prepare("
        SELECT n.*, u.nombre_completo AS reportado_por_nombre, i.nombre_elemento
        FROM novedades_ambiente n
        JOIN usuarios u ON u.id = n.reportada_por
        LEFT JOIN inventario_ambiente i ON i.id = n.id_item_inventario
        WHERE n.id_ambiente = :id AND n.estado = 'ACTIVA'
        ORDER BY n.fecha_hora DESC
    ");
    $stmt->execute([':id' => $idAmbienteSeleccionado]);
    $novedadesActivas = $stmt->fetchAll();
}

// Instructores a quienes se les puede entregar el ambiente (todos menos el actual).
$stmt = $pdo->prepare("
    SELECT u.id, u.nombre_completo FROM usuarios u
    JOIN roles r ON r.id = u.id_rol
    WHERE r.nombre = 'instructor' AND u.id != :yo
    ORDER BY u.nombre_completo
");
$stmt->execute([':yo' => $_SESSION['usuario_id']]);
$instructores = $stmt->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($mensajeFlash): ?>
    <div class="alert alert-<?= $mensajeFlash['tipo'] ?> alert-dismissible alert-auto-cerrar"><?= $mensajeFlash['texto'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- ===================== Selector de ambiente ===================== -->
<form method="GET" class="row g-2 mb-4">
    <div class="col-md-6">
        <label class="form-label small">Ambiente a entregar</label>
        <select name="id_ambiente" class="form-select" onchange="this.form.submit()">
            <?php if (!$ambientes): ?><option value="">No hay ambientes configurados</option><?php endif; ?>
            <?php foreach ($ambientes as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $a['id'] == $idAmbienteSeleccionado ? 'selected' : '' ?>><?= htmlspecialchars($a['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<?php if (!$idAmbienteSeleccionado): ?>
    <div class="alert alert-info">Aún no hay ambientes de formación configurados. Solicite al coordinador que cree uno.</div>
<?php else: ?>

<!-- ===================== Novedades activas ya reportadas ===================== -->
<?php if ($novedadesActivas): ?>
<div class="card shadow-sm mb-4 border-warning">
    <div class="card-header bg-warning-subtle fw-semibold">
        <i class="bi bi-exclamation-triangle-fill text-warning"></i> Novedades activas ya reportadas para este ambiente
    </div>
    <ul class="list-group list-group-flush">
        <?php foreach ($novedadesActivas as $n): $p = etiquetaPrioridad($n['prioridad']); ?>
        <li class="list-group-item">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <span class="badge <?= $p['clase'] ?>"><?= $p['texto'] ?></span>
                    <strong class="ms-1"><?= htmlspecialchars($n['nombre_elemento'] ?? 'Novedad general') ?></strong>
                    <p class="mb-0 small text-muted"><?= htmlspecialchars($n['descripcion']) ?></p>
                </div>
                <small class="text-muted text-nowrap">Reportada por <?= htmlspecialchars($n['reportado_por_nombre']) ?><br><?= htmlspecialchars($n['fecha_hora']) ?></small>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    <div class="card-footer small text-muted">
        <i class="bi bi-info-circle"></i> No es necesario volver a reportar estas novedades: ya quedaron registradas y notificadas.
        Puede marcarlas como corregidas desde <a href="<?= rutaBase() ?>/views/ambientes/alertas.php">Alertas de ambientes</a> una vez solucionadas.
    </div>
</div>
<?php endif; ?>

<!-- ===================== Formulario de entrega ===================== -->
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_ambiente" value="<?= $idAmbienteSeleccionado ?>">

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-clipboard-check"></i> Verificación de inventario base</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="sena-thead">
                    <tr><th>Elemento</th><th>Cantidad</th><th>¿Todo en orden?</th><th>Novedad</th></tr>
                </thead>
                <tbody>
                    <?php if (!$inventario): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Este ambiente no tiene inventario base configurado por el coordinador.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($inventario as $item): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($item['nombre_elemento']) ?></td>
                        <td><?= $item['cantidad'] ?></td>
                        <td style="width:220px;">
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check radio-estado-item" data-item="<?= $item['id'] ?>" name="estado_item[<?= $item['id'] ?>]" id="ok<?= $item['id'] ?>" value="ok" checked>
                                <label class="btn btn-outline-success" for="ok<?= $item['id'] ?>"><i class="bi bi-check-lg"></i> En orden</label>

                                <input type="radio" class="btn-check radio-estado-item" data-item="<?= $item['id'] ?>" name="estado_item[<?= $item['id'] ?>]" id="nov<?= $item['id'] ?>" value="novedad">
                                <label class="btn btn-outline-danger" for="nov<?= $item['id'] ?>"><i class="bi bi-exclamation-triangle"></i> Novedad</label>
                            </div>
                        </td>
                        <td>
                            <div class="bloque-novedad-item d-none border rounded p-2 bg-light" data-item="<?= $item['id'] ?>">
                                <label class="form-label small mb-1">Texto explicativo</label>
                                <textarea name="texto_item[<?= $item['id'] ?>]" class="form-control form-control-sm mb-2" rows="2" placeholder="Describa la novedad..."></textarea>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Evidencia fotográfica</label>
                                        <input type="file" name="foto_item[<?= $item['id'] ?>]" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Prioridad</label>
                                        <select name="prioridad_item[<?= $item['id'] ?>]" class="form-select form-select-sm">
                                            <option value="POCO_IMPORTANTE">Poco importante</option>
                                            <option value="IMPORTANTE">Importante</option>
                                            <option value="MUY_IMPORTANTE">Muy importante</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================== Novedad general ===================== -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="chkGeneral" name="reportar_general" value="1">
                <label class="form-check-label" for="chkGeneral">Reportar una novedad general del ambiente (no asociada a un elemento puntual)</label>
            </div>
        </div>
        <div class="card-body d-none" id="bloqueGeneral">
            <label class="form-label small">Texto explicativo</label>
            <textarea name="texto_general" class="form-control mb-2" rows="2"></textarea>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">Evidencia fotográfica</label>
                    <input type="file" name="foto_general" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Prioridad</label>
                    <select name="prioridad_general" class="form-select">
                        <option value="POCO_IMPORTANTE">Poco importante</option>
                        <option value="IMPORTANTE">Importante</option>
                        <option value="MUY_IMPORTANTE">Muy importante</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== Instructor que recibe ===================== -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <label class="form-label small">Instructor que recibe el ambiente a continuación (opcional)</label>
            <select name="id_instructor_recibe" class="form-select">
                <option value="">No aplica / no hay otro instructor en este momento</option>
                <?php foreach ($instructores as $i): ?>
                    <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['nombre_completo']) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted"><i class="bi bi-info-circle"></i> Si hay una novedad MUY IMPORTANTE, este instructor será notificado de inmediato.</small>
        </div>
    </div>

    <button class="btn btn-sena w-100 py-2" <?= !$inventario ? 'disabled' : '' ?>>
        <i class="bi bi-box-arrow-up"></i> Registrar entrega del ambiente
    </button>
</form>

<?php endif; ?>

<script>
/**
 * Muestra/oculta el bloque de novedad de cada fila del inventario según
 * el radio seleccionado ("En orden" / "Novedad"), y hace lo mismo con
 * el bloque de novedad general según el checkbox correspondiente.
 */
document.querySelectorAll('.radio-estado-item').forEach(function (radio) {
    radio.addEventListener('change', function () {
        const id = radio.getAttribute('data-item');
        const bloque = document.querySelector('.bloque-novedad-item[data-item="' + id + '"]');
        bloque.classList.toggle('d-none', radio.value !== 'novedad');
    });
});

const chkGeneral = document.getElementById('chkGeneral');
if (chkGeneral) {
    chkGeneral.addEventListener('change', function () {
        document.getElementById('bloqueGeneral').classList.toggle('d-none', !chkGeneral.checked);
    });
}
</script>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
