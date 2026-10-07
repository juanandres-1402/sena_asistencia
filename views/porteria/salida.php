<?php
/**
 * views/porteria/salida.php
 * ---------------------------------------------------------------------
 * CONTROL DE SALIDA. Muestra quién se encuentra actualmente dentro del
 * centro (ingresó hoy y aún no registra salida) y permite marcar su
 * salida con un solo clic (puntos 9 y 10 del documento).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['porteria', 'coordinador']);

$tituloPagina = 'Control de Salida';
$pdo = obtenerConexion();
$hoy = date('Y-m-d');

// Aprendices que ingresaron hoy y AÚN no tienen salida registrada -> 🟢 dentro del centro
$dentro = $pdo->prepare("
    SELECT i.id AS id_ingreso, i.hora AS hora_ingreso, u.nombre_completo, f.numero_ficha, i.estado_asistencia
    FROM ingresos i
    JOIN aprendices a ON a.id = i.id_aprendiz
    JOIN usuarios u ON u.id = a.id_usuario
    JOIN fichas f ON f.id = i.id_ficha
    LEFT JOIN salidas s ON s.id_ingreso = i.id
    WHERE i.fecha = :hoy AND s.id IS NULL
    ORDER BY i.hora ASC
");
$dentro->execute([':hoy' => $hoy]);
$dentro = $dentro->fetchAll();

// Aprendices que ya salieron hoy -> tabla histórica del día
$fueron = $pdo->prepare("
    SELECT u.nombre_completo, f.numero_ficha, i.hora AS hora_ingreso, s.hora AS hora_salida
    FROM salidas s
    JOIN ingresos i ON i.id = s.id_ingreso
    JOIN aprendices a ON a.id = i.id_aprendiz
    JOIN usuarios u ON u.id = a.id_usuario
    JOIN fichas f ON f.id = i.id_ficha
    WHERE s.fecha = :hoy
    ORDER BY s.id DESC
");
$fueron->execute([':hoy' => $hoy]);
$fueron = $fueron->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-door-open text-success"></i> Aprendices dentro del centro (<?= count($dentro) ?>)
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="tablaDentro">
            <thead class="sena-thead">
                <tr><th>Aprendiz</th><th>Ficha</th><th>Hora ingreso</th><th>Estado</th><th class="text-end">Acción</th></tr>
            </thead>
            <tbody>
                <?php if (!$dentro): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No hay aprendices dentro del centro en este momento.</td></tr>
                <?php endif; ?>
                <?php foreach ($dentro as $d): $e = etiquetaEstado($d['estado_asistencia']); ?>
                <tr data-id-ingreso="<?= $d['id_ingreso'] ?>">
                    <td><?= htmlspecialchars($d['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($d['numero_ficha']) ?></td>
                    <td><?= formatoHora12($d['hora_ingreso']) ?></td>
                    <td><span class="badge <?= $e['clase'] ?>"><?= $e['punto'] ?> <?= $e['texto'] ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-sena-outline btn-registrar-salida">
                            <i class="bi bi-box-arrow-right"></i> Registrar salida
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="contenedorAlertasSalida"></div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-clock-history"></i> Salidas registradas hoy</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="sena-thead"><tr><th>Aprendiz</th><th>Ficha</th><th>Ingreso</th><th>Salida</th></tr></thead>
            <tbody>
                <?php if (!$fueron): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">Aún no hay salidas registradas hoy.</td></tr>
                <?php endif; ?>
                <?php foreach ($fueron as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($f['numero_ficha']) ?></td>
                    <td><?= formatoHora12($f['hora_ingreso']) ?></td>
                    <td><?= formatoHora12($f['hora_salida']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
/**
 * Lógica de la pantalla de Control de Salida: al dar clic en
 * "Registrar salida" se llama al endpoint AJAX correspondiente y se
 * remueve la fila de la lista de "dentro del centro" sin recargar.
 */
document.querySelectorAll('.btn-registrar-salida').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const fila = boton.closest('tr');
        const idIngreso = fila.getAttribute('data-id-ingreso');
        boton.disabled = true;

        try {
            const resp = await fetch(`${RUTA_BASE}/api/registrar_salida.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_ingreso: idIngreso }),
            });
            const data = await resp.json();
            const contenedor = document.getElementById('contenedorAlertasSalida');

            if (data.ok) {
                contenedor.innerHTML = `<div class="alert alert-success alert-auto-cerrar">Salida registrada a las ${data.hora}.</div>`;
                fila.remove();
            } else {
                contenedor.innerHTML = `<div class="alert alert-danger alert-auto-cerrar">${data.mensaje}</div>`;
                boton.disabled = false;
            }
        } catch (e) {
            boton.disabled = false;
        }
    });
});
</script>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
