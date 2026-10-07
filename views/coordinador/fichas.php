<?php
/**
 * views/coordinador/fichas.php
 * ---------------------------------------------------------------------
 * Configuración académica (puntos 1, 5 y 19 del documento):
 *   - Crear/editar fichas y asociarlas a programa, horario, sede,
 *     competencia, resultado de aprendizaje e instructor.
 *   - Configurar la hora de inicio y la tolerancia de ingreso (la
 *     hora máxima se calcula sola, nunca se escribe "a mano").
 *   - Crear horarios nuevos.
 *   - Finalizar una ficha (impide seguir generando asistencia).
 * Cada acción queda registrada en la auditoría.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Fichas y horarios';
$pdo = obtenerConexion();
$mensaje = null;

// ---------------------------------------------------------------------
// Procesamiento de formularios (crear horario / crear ficha / finalizar)
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear_horario') {
        $stmt = $pdo->prepare("INSERT INTO horarios (nombre, hora_inicio, hora_fin, dias) VALUES (:nombre, :inicio, :fin, :dias)");
        $stmt->execute([
            ':nombre' => trim($_POST['nombre']),
            ':inicio' => $_POST['hora_inicio'],
            ':fin'    => $_POST['hora_fin'],
            ':dias'   => trim($_POST['dias']),
        ]);
        registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Creación de horario', 'horarios', '', $_POST['nombre']);
        $mensaje = ['tipo' => 'success', 'texto' => 'Horario creado correctamente.'];
    }

    if ($accion === 'crear_ficha') {
        $stmt = $pdo->prepare("
            INSERT INTO fichas (numero_ficha, id_programa, id_horario, id_sede, id_competencia, id_resultado_aprendizaje, id_instructor, tolerancia_minutos, estado)
            VALUES (:numero, :programa, :horario, :sede, :competencia, :resultado, :instructor, :tolerancia, 'ACTIVA')
        ");
        $stmt->execute([
            ':numero'      => trim($_POST['numero_ficha']),
            ':programa'    => (int) $_POST['id_programa'],
            ':horario'     => (int) $_POST['id_horario'],
            ':sede'        => $_POST['id_sede'] ?: null,
            ':competencia' => $_POST['id_competencia'] ?: null,
            ':resultado'   => $_POST['id_resultado_aprendizaje'] ?: null,
            ':instructor'  => $_POST['id_instructor'] ?: null,
            ':tolerancia'  => (int) $_POST['tolerancia_minutos'],
        ]);
        registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Creación de ficha', 'fichas', '', $_POST['numero_ficha']);
        $mensaje = ['tipo' => 'success', 'texto' => 'Ficha ' . htmlspecialchars($_POST['numero_ficha']) . ' creada correctamente.'];
    }

    if ($accion === 'actualizar_tolerancia') {
        $idFicha = (int) $_POST['id_ficha'];
        $stmtAnterior = $pdo->prepare("SELECT tolerancia_minutos FROM fichas WHERE id = :id");
        $stmtAnterior->execute([':id' => $idFicha]);
        $anterior = $stmtAnterior->fetchColumn();

        $stmt = $pdo->prepare("UPDATE fichas SET tolerancia_minutos = :tolerancia WHERE id = :id");
        $stmt->execute([':tolerancia' => (int) $_POST['tolerancia_minutos'], ':id' => $idFicha]);

        registrarAuditoria(
            $_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Modificación de tolerancia de ingreso', 'fichas',
            (string) $anterior . ' min', (string) $_POST['tolerancia_minutos'] . ' min'
        );
        generarNotificacion(null, 'Se modificó la tolerancia de ingreso de una ficha.', 'MODIFICACION');
        $mensaje = ['tipo' => 'success', 'texto' => 'Tolerancia de ingreso actualizada.'];
    }

    if ($accion === 'finalizar_ficha') {
        $idFicha = (int) $_POST['id_ficha'];
        $pdo->prepare("UPDATE fichas SET estado = 'FINALIZADA' WHERE id = :id")->execute([':id' => $idFicha]);
        registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Finalización de ficha', 'fichas', 'ACTIVA', 'FINALIZADA');
        $mensaje = ['tipo' => 'warning', 'texto' => 'La ficha fue marcada como FINALIZADA. Ya no generará nueva asistencia; su historial permanece disponible.'];
    }
}

// ---------------------------------------------------------------------
// Datos para los combos y la tabla
// ---------------------------------------------------------------------
$programas    = $pdo->query("SELECT * FROM programas")->fetchAll();
$horarios     = $pdo->query("SELECT * FROM horarios")->fetchAll();
$sedes        = $pdo->query("SELECT * FROM sedes")->fetchAll();
$competencias = $pdo->query("SELECT * FROM competencias")->fetchAll();
$resultados   = $pdo->query("SELECT * FROM resultados_aprendizaje")->fetchAll();
$instructores = $pdo->query("SELECT u.id, u.nombre_completo FROM usuarios u JOIN roles r ON r.id = u.id_rol WHERE r.nombre='instructor'")->fetchAll();

$fichas = $pdo->query("
    SELECT f.*, p.nombre AS programa, h.hora_inicio, h.hora_fin, h.nombre AS horario_nombre, u.nombre_completo AS instructor,
           (SELECT COUNT(*) FROM aprendices a WHERE a.id_ficha = f.id) AS total_aprendices
    FROM fichas f
    JOIN programas p ON p.id = f.id_programa
    JOIN horarios h ON h.id = f.id_horario
    LEFT JOIN usuarios u ON u.id = f.id_instructor
    ORDER BY f.id DESC
")->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $mensaje['tipo'] ?> alert-dismissible alert-auto-cerrar">
        <?= $mensaje['texto'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabFichas">Fichas</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCrearFicha">Crear ficha</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHorarios">Horarios</button></li>
</ul>

<div class="tab-content">

    <!-- ===================== Listado de fichas ===================== -->
    <div class="tab-pane fade show active" id="tabFichas">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="sena-thead">
                    <tr>
                        <th>Ficha</th><th>Programa</th><th>Horario</th><th>Hora máx. ingreso</th>
                        <th>Tolerancia</th><th>Instructor</th><th>Aprendices</th><th>Estado</th><th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fichas as $f): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($f['numero_ficha']) ?></td>
                        <td><?= htmlspecialchars($f['programa']) ?></td>
                        <td><?= formatoHora12($f['hora_inicio']) ?> – <?= formatoHora12($f['hora_fin']) ?></td>
                        <td><?= formatoHora12(calcularHoraMaxima($f['hora_inicio'], (int) $f['tolerancia_minutos'])) ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="accion" value="actualizar_tolerancia">
                                <input type="hidden" name="id_ficha" value="<?= $f['id'] ?>">
                                <input type="number" name="tolerancia_minutos" value="<?= $f['tolerancia_minutos'] ?>" min="0" max="120" class="form-control form-control-sm" style="width:70px" <?= $f['estado']==='FINALIZADA' ? 'disabled' : '' ?>>
                                <button class="btn btn-sm btn-sena-outline" <?= $f['estado']==='FINALIZADA' ? 'disabled' : '' ?>><i class="bi bi-check2"></i></button>
                            </form>
                        </td>
                        <td><?= htmlspecialchars($f['instructor'] ?? '—') ?></td>
                        <td><span class="badge bg-secondary"><?= $f['total_aprendices'] ?></span></td>
                        <td>
                            <?php if ($f['estado'] === 'ACTIVA'): ?>
                                <span class="badge bg-success">ACTIVA</span>
                            <?php else: ?>
                                <span class="badge bg-dark">FINALIZADA</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($f['estado'] === 'ACTIVA'): ?>
                            <form method="POST" class="d-inline" data-confirmar="¿Finalizar esta ficha? No podrá volver a generar asistencia.">
                                <input type="hidden" name="accion" value="finalizar_ficha">
                                <input type="hidden" name="id_ficha" value="<?= $f['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-flag-fill"></i> Finalizar</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="small text-muted">
            <i class="bi bi-info-circle"></i> La "hora máxima de ingreso" se calcula automáticamente (hora de inicio + tolerancia)
            y determina si la asistencia queda como PRESENTE o LLEGADA TARDE. Una ficha FINALIZADA conserva su historial pero no genera asistencia nueva.
        </p>
    </div>

    <!-- ===================== Crear ficha ===================== -->
    <div class="tab-pane fade" id="tabCrearFicha">
        <div class="card shadow-sm" style="max-width:700px;">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="crear_ficha">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Número de ficha</label>
                            <input type="text" name="numero_ficha" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Programa</label>
                            <select name="id_programa" class="form-select" required>
                                <?php foreach ($programas as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Horario</label>
                            <select name="id_horario" class="form-select" required>
                                <?php foreach ($horarios as $h): ?>
                                    <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['nombre']) ?> (<?= $h['hora_inicio'] ?>-<?= $h['hora_fin'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Sede / Ambiente</label>
                            <select name="id_sede" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($sedes as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Competencia</label>
                            <select name="id_competencia" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($competencias as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Resultado de aprendizaje</label>
                            <select name="id_resultado_aprendizaje" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($resultados as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Instructor</label>
                            <select name="id_instructor" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($instructores as $i): ?>
                                    <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['nombre_completo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Tolerancia de ingreso (minutos)</label>
                            <input type="number" name="tolerancia_minutos" class="form-control" value="15" min="0" max="120" required>
                        </div>
                    </div>
                    <button class="btn btn-sena mt-3"><i class="bi bi-plus-circle"></i> Crear ficha</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================== Horarios ===================== -->
    <div class="tab-pane fade" id="tabHorarios">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold">Crear horario</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="crear_horario">
                            <div class="mb-2">
                                <label class="form-label small">Nombre</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Jornada tarde" required>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small">Hora inicio</label>
                                    <input type="time" name="hora_inicio" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Hora fin</label>
                                    <input type="time" name="hora_fin" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-2 mt-2">
                                <label class="form-label small">Días</label>
                                <input type="text" name="dias" class="form-control" placeholder="Ej: Lunes a Viernes" required>
                            </div>
                            <button class="btn btn-sena w-100 mt-2"><i class="bi bi-plus-circle"></i> Crear horario</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <table class="table table-sm">
                    <thead class="sena-thead"><tr><th>Nombre</th><th>Inicio</th><th>Fin</th><th>Días</th></tr></thead>
                    <tbody>
                        <?php foreach ($horarios as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars($h['nombre']) ?></td>
                            <td><?= formatoHora12($h['hora_inicio']) ?></td>
                            <td><?= formatoHora12($h['hora_fin']) ?></td>
                            <td><?= htmlspecialchars($h['dias']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
