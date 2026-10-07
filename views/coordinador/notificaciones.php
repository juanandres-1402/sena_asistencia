<?php
/**
 * views/coordinador/notificaciones.php
 * ---------------------------------------------------------------------
 * Notificaciones (punto 17). El coordinador puede ver el historial de
 * eventos notificados y decidir qué tipos de evento generan
 * notificación (tabla "configuraciones").
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Notificaciones';
$pdo = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['notif_llegada_tarde', 'notif_sin_ingreso', 'notif_modificacion'] as $clave) {
        $valor = isset($_POST[$clave]) ? '1' : '0';
        $pdo->prepare("UPDATE configuraciones SET valor = :valor WHERE clave = :clave")
            ->execute([':valor' => $valor, ':clave' => $clave]);
    }
    registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Actualización de preferencias de notificación', 'configuraciones');
}

$config = $pdo->query("SELECT clave, valor FROM configuraciones")->fetchAll(PDO::FETCH_KEY_PAIR);
$notificaciones = $pdo->query("SELECT * FROM notificaciones ORDER BY id DESC LIMIT 50")->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Eventos que generan notificación</div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="notif_llegada_tarde" id="n1" <?= ($config['notif_llegada_tarde'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="n1">Llegada tarde de un aprendiz</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="notif_sin_ingreso" id="n2" <?= ($config['notif_sin_ingreso'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="n2">Aprendiz sin ingreso registrado</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="notif_modificacion" id="n3" <?= ($config['notif_modificacion'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="n3">Modificación de un registro de asistencia</label>
                    </div>
                    <button class="btn btn-sena w-100"><i class="bi bi-save"></i> Guardar preferencias</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bell"></i> Historial de notificaciones</div>
            <ul class="list-group list-group-flush" style="max-height:480px; overflow-y:auto;">
                <?php if (!$notificaciones): ?>
                    <li class="list-group-item text-muted text-center py-4">No hay notificaciones registradas.</li>
                <?php endif; ?>
                <?php foreach ($notificaciones as $n): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-secondary mb-1"><?= htmlspecialchars($n['tipo']) ?></span>
                            <p class="mb-0"><?= htmlspecialchars($n['mensaje']) ?></p>
                        </div>
                        <small class="text-muted text-nowrap ms-3"><?= htmlspecialchars($n['fecha_hora']) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
