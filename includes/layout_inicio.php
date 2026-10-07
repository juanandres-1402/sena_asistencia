<?php
/**
 * includes/layout_inicio.php
 * ---------------------------------------------------------------------
 * Abre el documento HTML, carga Bootstrap 5 + Bootstrap Icons, dibuja
 * la barra superior y el sidebar según el rol del usuario en sesión.
 * Cada vista debe hacer:
 *     require '../../includes/layout_inicio.php';
 *     ... contenido propio ...
 *     require '../../includes/layout_fin.php';
 * ---------------------------------------------------------------------
 */

$rolActual   = $_SESSION['usuario_rol']   ?? '';
$nombreUsr   = $_SESSION['usuario_nombre'] ?? '';
$tituloPagina = $tituloPagina ?? 'Sistema de Asistencia SENA';

// Menú lateral por rol. Cada ítem: [icono, etiqueta, url relativa a /views/]
$menus = [
    'coordinador' => [
        ['bi-speedometer2', 'Dashboard', 'coordinador/dashboard.php'],
        ['bi-journal-bookmark', 'Fichas y horarios', 'coordinador/fichas.php'],
        ['bi-person-vcard', 'Aprendices', 'aprendices/gestion.php'],
        ['bi-signpost-split', 'Salidas de campo', 'coordinador/salidas_campo.php'],
        ['bi-laptop', 'Equipos', 'equipos/listado.php'],
        ['bi-building', 'Ambientes de formación', 'coordinador/ambientes.php'],
        ['bi-exclamation-triangle', 'Alertas de ambientes', 'ambientes/alertas.php'],
        ['bi-clock-history', 'Historial de ambientes', 'ambientes/historial.php'],
        ['bi-people-fill', 'Asistencia', 'asistencia/consulta.php'],
        ['bi-bar-chart-line', 'Reportes', 'reportes/reportes.php'],
        ['bi-bell', 'Notificaciones', 'coordinador/notificaciones.php'],
        ['bi-person-badge', 'Cargar instructores', 'coordinador/instructores.php'],
        ['bi-key-fill', 'Usuarios y contraseñas', 'coordinador/usuarios.php'],
        ['bi-shield-lock', 'Auditoría', 'auditoria/auditoria.php'],
    ],
    'porteria' => [
        ['bi-door-open', 'Control de ingreso', 'porteria/ingreso.php'],
        ['bi-box-arrow-right', 'Control de salida', 'porteria/salida.php'],
        ['bi-laptop', 'Equipos', 'equipos/listado.php'],
    ],
    'instructor' => [
        ['bi-speedometer2', 'Mi ficha', 'instructor/dashboard.php'],
        ['bi-person-vcard', 'Aprendices', 'aprendices/gestion.php'],
        ['bi-clipboard-check', 'Consultar asistencia', 'asistencia/consulta.php'],
        ['bi-box-arrow-up', 'Entregar ambiente', 'instructor/entregar_ambiente.php'],
        ['bi-exclamation-triangle', 'Alertas de ambientes', 'ambientes/alertas.php'],
        ['bi-clock-history', 'Historial de ambientes', 'ambientes/historial.php'],
        ['bi-bell', 'Novedades', 'instructor/novedades.php'],
    ],
    'aprendiz' => [
        ['bi-speedometer2', 'Mi panel', 'aprendiz/dashboard.php'],
        ['bi-clipboard-check', 'Mi asistencia', 'aprendiz/asistencia.php'],
        ['bi-laptop', 'Mis equipos', 'aprendiz/equipos.php'],
    ],
];

$etiquetasRol = [
    'coordinador' => 'Coordinador académico',
    'porteria'    => 'Portería',
    'instructor'  => 'Instructor',
    'aprendiz'    => 'Aprendiz',
];

$menuActual = $menus[$rolActual] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina) ?> · SENA</title>

    <!-- Bootstrap 5 y Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Hoja de estilos institucional propia del sistema -->
    <link href="<?= rutaBase() ?>/assets/css/style.css" rel="stylesheet">

    <!-- Variable global para que el JavaScript del sistema arme TODAS sus
         rutas (llamadas a /api/...) a partir de la raíz del proyecto,
         igual que los enlaces PHP generados con rutaBase(). -->
    <script>const RUTA_BASE = "<?= rutaBase() ?>";</script>
</head>
<body>

<!-- ===================== BARRA SUPERIOR ===================== -->
<nav class="navbar navbar-expand navbar-dark sena-navbar sticky-top px-3">
    <button class="btn btn-sm btn-outline-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMovil">
        <i class="bi bi-list"></i>
    </button>
    <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <i class="bi bi-mortarboard-fill fs-4"></i>
        <span class="fw-bold">SENA · Control de Ingreso y Asistencia</span>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-white-50 small d-none d-md-inline"><?= htmlspecialchars($etiquetasRol[$rolActual] ?? '') ?></span>
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($nombreUsr) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= rutaBase() ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="d-flex">
    <!-- ===================== SIDEBAR ESCRITORIO ===================== -->
    <aside class="sena-sidebar d-none d-lg-flex flex-column p-3">
        <?php foreach ($menuActual as [$icono, $etiqueta, $url]): ?>
            <a href="<?= rutaBase() ?>/views/<?= $url ?>"
               class="sena-menu-item <?= (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], basename($url))) ? 'active' : '' ?>">
                <i class="bi <?= $icono ?>"></i> <span><?= $etiqueta ?></span>
            </a>
        <?php endforeach; ?>
    </aside>

    <!-- ===================== SIDEBAR MÓVIL (offcanvas) ===================== -->
    <div class="offcanvas offcanvas-start sena-sidebar" tabindex="-1" id="sidebarMovil">
        <div class="offcanvas-header">
            <h6 class="offcanvas-title">Menú</h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <?php foreach ($menuActual as [$icono, $etiqueta, $url]): ?>
                <a href="<?= rutaBase() ?>/views/<?= $url ?>" class="sena-menu-item">
                    <i class="bi <?= $icono ?>"></i> <span><?= $etiqueta ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ===================== CONTENIDO PRINCIPAL ===================== -->
    <main class="flex-fill p-3 p-md-4 sena-contenido">
        <h4 class="mb-3 fw-bold text-sena-verde-oscuro"><?= htmlspecialchars($tituloPagina) ?></h4>
