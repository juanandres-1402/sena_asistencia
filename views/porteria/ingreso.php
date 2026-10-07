<?php
/**
 * views/porteria/ingreso.php
 * ---------------------------------------------------------------------
 * CONTROL DE INGRESO (punto 3 y 15 del documento de requerimientos).
 * Pantalla principal del usuario de portería: debe ser extremadamente
 * simple y rápida. Su única función aquí es registrar el ingreso; el
 * sistema calcula la asistencia automáticamente en el servidor.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';

exigirRol(['porteria', 'coordinador']);

$tituloPagina = 'Control de Ingreso';

// Últimos ingresos del día, para la tabla inferior (punto 15).
$pdo = obtenerConexion();
$hoy = date('Y-m-d');
$ultimosIngresos = $pdo->prepare("
    SELECT i.hora, u.nombre_completo, f.numero_ficha, i.estado_asistencia,
           (SELECT COUNT(*) FROM equipo_ingreso ei WHERE ei.id_ingreso = i.id) AS tiene_equipo
    FROM ingresos i
    JOIN aprendices a ON a.id = i.id_aprendiz
    JOIN usuarios u ON u.id = a.id_usuario
    JOIN fichas f ON f.id = i.id_ficha
    WHERE i.fecha = :hoy
    ORDER BY i.id DESC
    LIMIT 10
");
$ultimosIngresos->execute([':hoy' => $hoy]);
$ultimosIngresos = $ultimosIngresos->fetchAll();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<div class="porteria-panel">

    <!-- ============ Buscador rápido ============ -->
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center py-4">
            <p class="fw-semibold mb-2">Ingrese documento / escanee identificación / código QR</p>
            <form id="formBuscarAprendiz" class="d-flex gap-2" autocomplete="off">
                <input type="text" id="inputDocumento" class="form-control" placeholder="N.º de documento" required>
                <button type="submit" class="btn btn-sena px-4">
                    <i class="bi bi-search"></i>
                </button>
            </form>
            <small class="text-muted d-block mt-2">
                <i class="bi bi-info-circle"></i> Este campo queda preparado para recibir la lectura automática
                de un lector de código de barras, QR, RFID o NFC.
            </small>
        </div>
    </div>

    <!-- ============ Resultado / tarjeta del aprendiz ============ -->
    <div id="contenedorResultado"></div>

    <!-- ============ Mensajes de alerta dinámicos ============ -->
    <div id="contenedorAlertas"></div>

    <!-- ============ Últimos ingresos de hoy ============ -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-clock-history me-1"></i> Últimos ingresos de hoy
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="sena-thead">
                    <tr>
                        <th>Hora</th>
                        <th>Aprendiz</th>
                        <th>Ficha</th>
                        <th>Estado</th>
                        <th>Equipo</th>
                    </tr>
                </thead>
                <tbody id="tablaUltimosIngresos">
                    <?php if (!$ultimosIngresos): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Aún no hay ingresos registrados hoy.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($ultimosIngresos as $ing): $e = etiquetaEstado($ing['estado_asistencia']); ?>
                        <tr>
                            <td><?= formatoHora12($ing['hora']) ?></td>
                            <td><?= htmlspecialchars($ing['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($ing['numero_ficha']) ?></td>
                            <td><span class="badge <?= $e['clase'] ?>"><?= $e['texto'] ?></span></td>
                            <td><?= $ing['tiene_equipo'] ? '💻' : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ Plantilla (template) de la tarjeta de resultado ============
     Se llena dinámicamente vía JavaScript en assets/js/porteria.js -->
<template id="plantillaTarjetaAprendiz">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <small class="text-muted text-uppercase">Aprendiz</small>
                    <h5 class="fw-bold mb-0" data-campo="nombre"></h5>
                    <small class="text-muted" data-campo="documento"></small>
                </div>
                <span class="estado-chip" data-campo="estado-chip"></span>
            </div>
            <hr>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Ficha</small>
                    <span class="fw-semibold" data-campo="ficha"></span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Programa</small>
                    <span class="fw-semibold" data-campo="programa"></span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Horario</small>
                    <span class="fw-semibold" data-campo="horario"></span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Hora actual</small>
                    <span class="fw-semibold" data-campo="hora_actual"></span>
                </div>
            </div>

            <hr>

            <!-- Pregunta del portátil (punto 7 del documento) -->
            <div id="bloquePortatil">
                <p class="fw-semibold mb-2">¿Ingresa con equipo (portátil)?</p>
                <div class="btn-group mb-3" role="group">
                    <input type="radio" class="btn-check" name="traeEquipo" id="equipoSi" value="si" autocomplete="off">
                    <label class="btn btn-outline-success" for="equipoSi"><i class="bi bi-laptop"></i> Sí</label>

                    <input type="radio" class="btn-check" name="traeEquipo" id="equipoNo" value="no" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary" for="equipoNo">No</label>
                </div>

                <div id="formEquipo" class="d-none border rounded p-3 bg-light mb-3">
                    <div id="equipoReconocidoAviso" class="alert alert-info py-2 small d-none"></div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Marca</label>
                            <input type="text" id="equipoMarca" class="form-control form-control-sm" placeholder="Ej: Lenovo">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Modelo</label>
                            <input type="text" id="equipoModelo" class="form-control form-control-sm" placeholder="Ej: ThinkPad E14">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Número de serie</label>
                            <input type="text" id="equipoSerie" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Código del cargador</label>
                            <input type="text" id="equipoCargador" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>

            <button id="btnConfirmarIngreso" class="btn btn-sena w-100 py-2">
                <i class="bi bi-check2-circle"></i> Registrar ingreso
            </button>
        </div>
    </div>
</template>

<script src="<?= rutaBase() ?>/assets/js/porteria.js"></script>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
