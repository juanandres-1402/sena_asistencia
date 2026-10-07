<?php
/**
 * views/coordinador/usuarios.php
 * ---------------------------------------------------------------------
 * NUEVO REQUERIMIENTO: "El coordinador es el único que puede administrar
 * las contraseñas de todos los usuarios."
 *
 * Esta es la ÚNICA pantalla del sistema donde se puede cambiar la
 * contraseña de otro usuario (coordinador, portería, instructor o
 * aprendiz). Ni el instructor ni portería tienen forma de hacerlo desde
 * ningún otro módulo: cuando ellos crean aprendices (views/aprendices/
 * gestion.php) o se crean instructores por carga masiva, la contraseña
 * inicial se asigna automáticamente o la define el coordinador aquí.
 *
 * También permite activar/desactivar usuarios (un usuario inactivo no
 * puede iniciar sesión, ver index.php).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Usuarios y contraseñas';
$pdo = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idUsuario = (int) ($_POST['id_usuario'] ?? 0);

    if ($accion === 'cambiar_clave') {
        $nuevaClave = trim($_POST['nueva_clave'] ?? '');
        if (strlen($nuevaClave) < 4) {
            establecerMensajeFlash('danger', 'La nueva contraseña debe tener al menos 4 caracteres.');
        } else {
            $pdo->prepare("UPDATE usuarios SET clave = :clave WHERE id = :id")
                ->execute([':clave' => $nuevaClave, ':id' => $idUsuario]);
            // Por seguridad, la auditoría NO guarda el valor de la contraseña, solo que cambió.
            registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Cambio de contraseña de usuario', 'usuarios', '(oculta)', '(oculta) - usuario #' . $idUsuario);
            establecerMensajeFlash('success', 'Contraseña actualizada correctamente.');
        }
    }

    if ($accion === 'toggle_activo') {
        if ($idUsuario === (int) $_SESSION['usuario_id']) {
            establecerMensajeFlash('danger', 'No puede desactivar su propio usuario.');
        } else {
            $stmt = $pdo->prepare("SELECT activo, nombre_completo FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $idUsuario]);
            $usuario = $stmt->fetch();
            if ($usuario) {
                $nuevoEstado = $usuario['activo'] ? 0 : 1;
                $pdo->prepare("UPDATE usuarios SET activo = :activo WHERE id = :id")->execute([':activo' => $nuevoEstado, ':id' => $idUsuario]);
                registrarAuditoria(
                    $_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Cambio de estado de usuario', 'usuarios',
                    $usuario['activo'] ? 'ACTIVO' : 'INACTIVO', ($nuevoEstado ? 'ACTIVO' : 'INACTIVO') . ' - ' . $usuario['nombre_completo']
                );
                establecerMensajeFlash('success', 'Estado del usuario actualizado.');
            }
        }
    }

    header('Location: ' . rutaBase() . '/views/coordinador/usuarios.php' . (isset($_GET['rol']) ? '?rol=' . urlencode($_GET['rol']) : ''));
    exit;
}

$mensajeFlash = obtenerMensajeFlash();
$filtroRol = $_GET['rol'] ?? '';
$busqueda = trim($_GET['buscar'] ?? '');

$sql = "SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.id_rol WHERE 1=1";
$params = [];
if ($filtroRol !== '') {
    $sql .= " AND r.nombre = :rol";
    $params[':rol'] = $filtroRol;
}
if ($busqueda !== '') {
    $sql .= " AND (u.nombre_completo LIKE :busqueda OR u.documento LIKE :busqueda)";
    $params[':busqueda'] = '%' . $busqueda . '%';
}
$sql .= " ORDER BY r.nombre, u.nombre_completo";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($mensajeFlash): ?>
    <div class="alert alert-<?= $mensajeFlash['tipo'] ?> alert-dismissible alert-auto-cerrar"><?= $mensajeFlash['texto'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="GET">
    <div class="col-md-3">
        <select name="rol" class="form-select" onchange="this.form.submit()">
            <option value="">Todos los roles</option>
            <option value="coordinador" <?= $filtroRol === 'coordinador' ? 'selected' : '' ?>>Coordinador</option>
            <option value="porteria" <?= $filtroRol === 'porteria' ? 'selected' : '' ?>>Portería</option>
            <option value="instructor" <?= $filtroRol === 'instructor' ? 'selected' : '' ?>>Instructor</option>
            <option value="aprendiz" <?= $filtroRol === 'aprendiz' ? 'selected' : '' ?>>Aprendiz</option>
        </select>
    </div>
    <div class="col-md-4">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o documento" value="<?= htmlspecialchars($busqueda) ?>">
    </div>
    <div class="col-md-2">
        <button class="btn btn-sena-outline w-100"><i class="bi bi-search"></i> Buscar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead class="sena-thead">
            <tr><th>Nombre</th><th>Documento</th><th>Rol</th><th>Correo</th><th>Estado</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
            <?php if (!$usuarios): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No hay usuarios con ese filtro.</td></tr>
            <?php endif; ?>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($u['documento']) ?></td>
                <td><span class="badge bg-secondary text-capitalize"><?= htmlspecialchars($u['rol']) ?></span></td>
                <td class="text-muted small"><?= htmlspecialchars($u['correo'] ?: '—') ?></td>
                <td>
                    <?php if ($u['activo']): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-sena-outline" data-bs-toggle="modal" data-bs-target="#modalClave<?= $u['id'] ?>">
                        <i class="bi bi-key"></i> Contraseña
                    </button>
                    <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                    <form method="POST" class="d-inline" data-confirmar="¿<?= $u['activo'] ? 'Desactivar' : 'Activar' ?> a <?= htmlspecialchars($u['nombre_completo']) ?>?">
                        <input type="hidden" name="accion" value="toggle_activo">
                        <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
                        <button class="btn btn-sm btn-outline-<?= $u['activo'] ? 'danger' : 'success' ?>">
                            <i class="bi bi-power"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Modal para cambiar la contraseña de este usuario -->
            <div class="modal fade" id="modalClave<?= $u['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <input type="hidden" name="accion" value="cambiar_clave">
                            <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
                            <div class="modal-header">
                                <h6 class="modal-title">Cambiar contraseña · <?= htmlspecialchars($u['nombre_completo']) ?></h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label small">Nueva contraseña</label>
                                <input type="text" name="nueva_clave" class="form-control" minlength="4" required>
                                <small class="text-muted">Mínimo 4 caracteres. El usuario deberá usar esta nueva contraseña en su próximo inicio de sesión.</small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-sena btn-sm"><i class="bi bi-save"></i> Guardar contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="small text-muted"><i class="bi bi-shield-lock"></i> Esta es la única pantalla del sistema donde se puede cambiar la contraseña de otro usuario. Ni portería ni los instructores tienen acceso a esta función.</p>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
