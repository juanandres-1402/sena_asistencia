<?php
/**
 * views/coordinador/instructores.php
 * ---------------------------------------------------------------------
 * NUEVO REQUERIMIENTO: "El coordinador debe poder realizar la carga
 * masiva de instructores, asignándoles una contraseña inicial (ya sea
 * la misma contraseña genérica para todos cambiando únicamente el
 * número de documento como usuario, o una personalizada según se
 * requiera)."
 *
 * Solo el coordinador tiene acceso a esta pantalla: es, junto con
 * views/coordinador/usuarios.php, de las pocas donde el sistema asigna
 * o cambia una contraseña directamente (aquí porque es una creación de
 * cuenta nueva, hecha precisamente por quien administra contraseñas).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Carga de instructores';
$pdo = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear_individual') {
        $resultado = registrarInstructor($pdo, $_POST['documento'] ?? '', $_POST['nombre_completo'] ?? '', $_POST['correo'] ?? '', $_POST['clave'] ?? '');
        if ($resultado['ok']) {
            registrarAuditoria($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Instructor creado (individual)', 'usuarios', '', ($_POST['documento'] ?? ''));
        }
        establecerMensajeFlash($resultado['ok'] ? 'success' : 'danger', $resultado['mensaje']);
        header('Location: ' . rutaBase() . '/views/coordinador/instructores.php');
        exit;
    }

    if ($accion === 'carga_masiva') {
        $estrategia = $_POST['estrategia_clave'] ?? 'generica';
        $claveGenerica = trim($_POST['clave_generica'] ?? '');

        if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
            establecerMensajeFlash('danger', 'Debe adjuntar un archivo CSV válido.');
        } elseif ($estrategia === 'generica' && $claveGenerica === '') {
            establecerMensajeFlash('danger', 'Debe indicar la contraseña genérica que se asignará a todos los instructores.');
        } else {
            $resumen = procesarCargaMasivaInstructores($pdo, $_FILES['archivo_csv']['tmp_name'], $estrategia, $claveGenerica);
            registrarAuditoria(
                $_SESSION['usuario_id'], $_SESSION['usuario_nombre'], 'Carga masiva de instructores', 'usuarios', '',
                "{$resumen['creados']} creados, {$resumen['omitidos']} omitidos (estrategia: $estrategia)"
            );

            $texto = "Carga masiva procesada: {$resumen['creados']} instructor(es) creado(s)" .
                ($resumen['omitidos'] > 0 ? ", {$resumen['omitidos']} omitido(s)." : '.') .
                ($estrategia === 'generica' ? ' Todos pueden iniciar sesión con su número de documento y la contraseña genérica indicada.' : ' Cada uno con la contraseña indicada en el archivo.');
            if ($resumen['errores']) {
                $texto .= '<br><small>' . implode('<br>', array_map('htmlspecialchars', array_slice($resumen['errores'], 0, 8))) . '</small>';
            }
            establecerMensajeFlash($resumen['omitidos'] > 0 ? 'warning' : 'success', $texto);
        }
        header('Location: ' . rutaBase() . '/views/coordinador/instructores.php');
        exit;
    }
}

$mensajeFlash = obtenerMensajeFlash();

$instructores = $pdo->query("
    SELECT u.nombre_completo, u.documento, u.correo, u.activo
    FROM usuarios u JOIN roles r ON r.id = u.id_rol
    WHERE r.nombre = 'instructor'
    ORDER BY u.nombre_completo
")->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<?php if ($mensajeFlash): ?>
    <div class="alert alert-<?= $mensajeFlash['tipo'] ?> alert-dismissible alert-auto-cerrar"><?= $mensajeFlash['texto'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabListado">Instructores (<?= count($instructores) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabIndividual">Crear instructor</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMasiva">Carga masiva (CSV)</button></li>
</ul>

<div class="tab-content">
    <!-- ===================== Listado ===================== -->
    <div class="tab-pane fade show active" id="tabListado">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="sena-thead"><tr><th>Nombre</th><th>Documento</th><th>Correo</th><th>Estado</th></tr></thead>
                <tbody>
                    <?php if (!$instructores): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No hay instructores registrados.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($instructores as $i): ?>
                    <tr>
                        <td><?= htmlspecialchars($i['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($i['documento']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($i['correo'] ?: '—') ?></td>
                        <td><span class="badge <?= $i['activo'] ? 'bg-success' : 'bg-secondary' ?>"><?= $i['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="small text-muted">
            <i class="bi bi-info-circle"></i> Para cambiar la contraseña de un instructor ya existente, vaya a
            <a href="<?= rutaBase() ?>/views/coordinador/usuarios.php?rol=instructor">Usuarios y contraseñas</a>.
        </p>
    </div>

    <!-- ===================== Crear instructor individual ===================== -->
    <div class="tab-pane fade" id="tabIndividual">
        <div class="card shadow-sm" style="max-width:600px;">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="crear_individual">
                    <div class="mb-2">
                        <label class="form-label small">Nombre completo</label>
                        <input type="text" name="nombre_completo" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Documento</label>
                        <input type="text" name="documento" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Correo (opcional)</label>
                        <input type="email" name="correo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Contraseña inicial</label>
                        <input type="text" name="clave" class="form-control" required minlength="4">
                    </div>
                    <button class="btn btn-sena w-100"><i class="bi bi-person-plus"></i> Crear instructor</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================== Carga masiva ===================== -->
    <div class="tab-pane fade" id="tabMasiva">
        <div class="card shadow-sm" style="max-width:650px;">
            <div class="card-body">
                <p class="fw-semibold mb-2">¿Cómo se asigna la contraseña inicial?</p>
                <div class="btn-group w-100 mb-3" role="group">
                    <input type="radio" class="btn-check" name="modo" id="modoGenerica" value="generica" checked>
                    <label class="btn btn-outline-success" for="modoGenerica">Misma contraseña genérica para todos</label>

                    <input type="radio" class="btn-check" name="modo" id="modoPersonalizada" value="personalizada">
                    <label class="btn btn-outline-success" for="modoPersonalizada">Personalizada (columna "clave" en el CSV)</label>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="carga_masiva">
                    <input type="hidden" name="estrategia_clave" id="estrategiaClaveOculta" value="generica">

                    <div id="bloqueGenerica" class="mb-3">
                        <label class="form-label small">Contraseña genérica para todos los instructores de este archivo</label>
                        <input type="text" name="clave_generica" id="inputClaveGenerica" class="form-control" minlength="4">
                        <small class="text-muted">Cada instructor inicia sesión con su número de documento como usuario y esta misma contraseña (puede cambiarla luego desde "Usuarios y contraseñas").</small>
                        <p class="small text-muted mt-2 mb-0">Columnas del CSV: <code>documento,nombre_completo,correo</code>. 
                            <a href="<?= rutaBase() ?>/controllers/plantilla_csv.php?tipo=instructores_generica">Descargar plantilla</a>.
                        </p>
                    </div>

                    <div id="bloquePersonalizada" class="mb-3 d-none">
                        <p class="small text-muted mb-0">Columnas del CSV: <code>documento,nombre_completo,correo,clave</code> (cada fila con su propia contraseña). 
                            <a href="<?= rutaBase() ?>/controllers/plantilla_csv.php?tipo=instructores_personalizada">Descargar plantilla</a>.
                        </p>
                    </div>

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

<script>
/**
 * Alterna entre el modo "contraseña genérica" y "contraseña
 * personalizada en el CSV" para la carga masiva de instructores.
 */
const radiosModo = document.querySelectorAll('input[name="modo"]');
const bloqueGenerica = document.getElementById('bloqueGenerica');
const bloquePersonalizada = document.getElementById('bloquePersonalizada');
const estrategiaOculta = document.getElementById('estrategiaClaveOculta');
const inputClaveGenerica = document.getElementById('inputClaveGenerica');

radiosModo.forEach(function (radio) {
    radio.addEventListener('change', function () {
        const esGenerica = radio.value === 'generica' && radio.checked;
        if (radio.checked) {
            estrategiaOculta.value = radio.value;
            bloqueGenerica.classList.toggle('d-none', radio.value !== 'generica');
            bloquePersonalizada.classList.toggle('d-none', radio.value !== 'personalizada');
            inputClaveGenerica.required = radio.value === 'generica';
        }
    });
});
</script>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
