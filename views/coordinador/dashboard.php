<?php
/**
 * views/coordinador/dashboard.php
 * ---------------------------------------------------------------------
 * Dashboard del coordinador académico (punto 14 del documento).
 * Muestra los indicadores globales del día y gráficas de apoyo.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/../../includes/sesion.php';
require_once __DIR__ . '/../../includes/funciones.php';
exigirRol(['coordinador']);

$tituloPagina = 'Dashboard del Coordinador';
$pdo = obtenerConexion();
$hoy = date('Y-m-d');

// --- Indicadores principales -------------------------------------------------
$totalAprendices = (int) $pdo->query("SELECT COUNT(*) FROM aprendices")->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM ingresos WHERE fecha = :hoy");
$stmt->execute([':hoy' => $hoy]);
$ingresaronHoy = (int) $stmt->fetchColumn();

$sinIngreso = max($totalAprendices - $ingresaronHoy, 0);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM ingresos WHERE fecha = :hoy AND estado_asistencia = 'LLEGADA_TARDE'");
$stmt->execute([':hoy' => $hoy]);
$llegaronTarde = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM ingresos i LEFT JOIN salidas s ON s.id_ingreso = i.id
    WHERE i.fecha = :hoy AND s.id IS NULL
");
$stmt->execute([':hoy' => $hoy]);
$dentroDelCentro = (int) $stmt->fetchColumn();

$fueraDelCentro = max($ingresaronHoy - $dentroDelCentro, 0);

$equiposRegistrados = (int) $pdo->query("SELECT COUNT(*) FROM equipos")->fetchColumn();

// --- Datos para gráfica: ingresos por hora -----------------------------------
$stmt = $pdo->prepare("SELECT substr(hora,1,2) AS hora_h, COUNT(*) AS total FROM ingresos WHERE fecha = :hoy GROUP BY hora_h ORDER BY hora_h");
$stmt->execute([':hoy' => $hoy]);
$ingresosPorHora = $stmt->fetchAll();

// --- Asistencia por ficha -----------------------------------------------------
$asistenciaPorFicha = $pdo->prepare("
    SELECT f.numero_ficha,
           SUM(CASE WHEN i.estado_asistencia='PRESENTE' THEN 1 ELSE 0 END) AS presentes,
           SUM(CASE WHEN i.estado_asistencia='LLEGADA_TARDE' THEN 1 ELSE 0 END) AS tardes
    FROM fichas f
    LEFT JOIN ingresos i ON i.id_ficha = f.id AND i.fecha = :hoy
    GROUP BY f.id
");
$asistenciaPorFicha->execute([':hoy' => $hoy]);
$asistenciaPorFicha = $asistenciaPorFicha->fetchAll();

// --- Alertas activas de ambientes (nuevo requerimiento) -----------------------
$alertasActivas = (int) $pdo->query("SELECT COUNT(*) FROM novedades_ambiente WHERE estado = 'ACTIVA'")->fetchColumn();
$alertasMuyImportantes = (int) $pdo->query("SELECT COUNT(*) FROM novedades_ambiente WHERE estado = 'ACTIVA' AND prioridad = 'MUY_IMPORTANTE'")->fetchColumn();

require __DIR__ . '/../../includes/layout_inicio.php';
?>

<p class="text-muted mb-4">Ingresos de hoy — <?= date('d/m/Y') ?></p>

<!-- ===================== Tarjetas de indicadores ===================== -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">Total aprendices</div>
            <div class="valor"><?= $totalAprendices ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">Ingresaron hoy</div>
            <div class="valor text-success"><?= $ingresaronHoy ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">Sin ingreso</div>
            <div class="valor text-danger"><?= $sinIngreso ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">Llegaron tarde</div>
            <div class="valor text-warning"><?= $llegaronTarde ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">🟢 Dentro del centro</div>
            <div class="valor"><?= $dentroDelCentro ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">⚪ Fuera del centro</div>
            <div class="valor"><?= $fueraDelCentro ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">Equipos registrados</div>
            <div class="valor"><?= $equiposRegistrados ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3 <?= $alertasMuyImportantes > 0 ? 'border-start border-danger border-4' : '' ?>">
            <div class="etiqueta">⚠️ Alertas de ambientes</div>
            <div class="valor <?= $alertasMuyImportantes > 0 ? 'text-danger' : '' ?>"><?= $alertasActivas ?></div>
            <?php if ($alertasMuyImportantes > 0): ?>
                <small class="text-danger fw-semibold"><?= $alertasMuyImportantes ?> muy importante(s)</small>
            <?php endif; ?>
            <a href="<?= rutaBase() ?>/views/ambientes/alertas.php" class="small d-block mt-1">Ver alertas</a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sena-card-indicador p-3">
            <div class="etiqueta">Accesos rápidos</div>
            <a href="<?= rutaBase() ?>/views/coordinador/fichas.php" class="small d-block">⚙️ Configurar fichas</a>
            <a href="<?= rutaBase() ?>/views/auditoria/auditoria.php" class="small d-block">🛡️ Ver auditoría</a>
        </div>
    </div>
</div>

<!-- ===================== Gráficas ===================== -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm p-3">
            <h6 class="fw-semibold">Ingresos por hora</h6>
            <canvas id="graficaIngresosHora" height="200"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm p-3">
            <h6 class="fw-semibold">Asistencia por ficha (hoy)</h6>
            <canvas id="graficaFichas" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js vía CDN permitido para graficar los indicadores del dashboard -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
    // Datos generados en PHP y pasados al JavaScript del dashboard.
    const horas = <?= json_encode(array_map(fn($h) => $h['hora_h'] . ':00', $ingresosPorHora)) ?>;
    const totalesHora = <?= json_encode(array_map(fn($h) => (int) $h['total'], $ingresosPorHora)) ?>;

    new Chart(document.getElementById('graficaIngresosHora'), {
        type: 'bar',
        data: { labels: horas, datasets: [{ label: 'Ingresos', data: totalesHora, backgroundColor: '#39A900' }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    const fichasLabels = <?= json_encode(array_map(fn($f) => $f['numero_ficha'], $asistenciaPorFicha)) ?>;
    const fichasPresentes = <?= json_encode(array_map(fn($f) => (int) $f['presentes'], $asistenciaPorFicha)) ?>;
    const fichasTardes = <?= json_encode(array_map(fn($f) => (int) $f['tardes'], $asistenciaPorFicha)) ?>;

    new Chart(document.getElementById('graficaFichas'), {
        type: 'bar',
        data: {
            labels: fichasLabels,
            datasets: [
                { label: 'Presentes', data: fichasPresentes, backgroundColor: '#39A900' },
                { label: 'Tarde', data: fichasTardes, backgroundColor: '#f0ad4e' },
            ]
        },
        options: { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
</script>

<?php require __DIR__ . '/../../includes/layout_fin.php'; ?>
