<?php
/**
 * Vista integrada desde el proyecto 'controlasistencia'.
 * Protegida con la sesión y los roles del sistema sena_asistencia.
 */
require_once __DIR__ . '/../../includes/sesion.php';
exigirRol(['instructor', 'coordinador']);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Control de Asistencia | Portal Instructor</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>

        /* =====================================================
           VARIABLES
        ===================================================== */

        :root {

            --sena-green: #39A900;
            --sena-green-dark: #007832;
            --sena-blue: #00304D;

            --sena-purple: #71277A;
            --sena-cyan: #50E5F9;
            --sena-yellow: #FDC300;

            --bg-main: #0B1110;
            --bg-card: #101817;
            --bg-card-2: #17221F;

            --border: rgba(255,255,255,.08);

            --text-main: #F4F7F3;
            --text-muted: #91A09A;

            --danger: #E74C3C;
            --success: #39A900;
            --warning: #FDC300;

            --sidebar-width: 250px;
        }


        /* =====================================================
           GENERAL
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family: 'Plus Jakarta Sans', sans-serif;

            background: var(--bg-main);

            color: var(--text-main);

        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: var(--sidebar-width);
            height: 100vh;

            background: #0E1513;

            border-right: 1px solid var(--border);

            padding: 20px 14px;

            z-index: 1000;

        }


        .logo {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 10px 12px 25px;

            border-bottom: 1px solid var(--border);

            margin-bottom: 20px;

        }


        .logo-icon {

            width: 42px;
            height: 42px;

            background: var(--sena-green);

            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: white;

            font-size: 22px;

        }


        .logo-text strong {

            display: block;

            font-size: 15px;

        }


        .logo-text span {

            font-size: 11px;

            color: var(--text-muted);

        }


        /* =====================================================
           MENU
        ===================================================== */

        .menu-title {

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: 1px;

            color: #65716D;

            margin: 20px 12px 8px;

        }


        .menu-item {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 11px 13px;

            margin-bottom: 4px;

            color: var(--text-muted);

            text-decoration: none;

            border-radius: 9px;

            font-size: 13px;

            transition: .2s;

        }


        .menu-item i {

            font-size: 17px;

        }


        .menu-item:hover {

            background: rgba(57,169,0,.10);

            color: white;

        }


        .menu-item.active {

            background: var(--sena-green);

            color: white;

        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {

            margin-left: var(--sidebar-width);

            min-height: 100vh;

            padding: 28px 32px;

        }


        /* =====================================================
           HEADER
        ===================================================== */

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 28px;

        }


        .page-title h1 {

            font-size: 25px;

            font-weight: 700;

            margin: 0;

        }


        .page-title p {

            color: var(--text-muted);

            font-size: 13px;

            margin: 7px 0 0;

        }


        .instructor {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .avatar {

            width: 40px;
            height: 40px;

            border-radius: 50%;

            background: var(--sena-blue);

            display: flex;

            align-items: center;
            justify-content: center;

            font-weight: 700;

            color: white;

        }


        .instructor-info strong {

            display: block;

            font-size: 13px;

        }


        .instructor-info span {

            font-size: 11px;

            color: var(--text-muted);

        }


        /* =====================================================
           SUMMARY CARDS
        ===================================================== */

        .summary-grid {

            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 16px;

            margin-bottom: 24px;

        }


        .summary-card {

            background: var(--bg-card);

            border: 1px solid var(--border);

            border-radius: 14px;

            padding: 19px;

            position: relative;

            overflow: hidden;

        }


        .summary-card::after {

            content: "";

            position: absolute;

            right: -20px;

            top: -20px;

            width: 70px;
            height: 70px;

            border-radius: 50%;

            background: rgba(255,255,255,.025);

        }


        .summary-top {

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .summary-icon {

            width: 38px;
            height: 38px;

            border-radius: 10px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 18px;

        }


        .icon-green {

            background: rgba(57,169,0,.13);

            color: var(--sena-green);

        }


        .icon-blue {

            background: rgba(0,48,77,.35);

            color: #62A9D2;

        }


        .icon-red {

            background: rgba(231,76,60,.12);

            color: var(--danger);

        }


        .icon-yellow {

            background: rgba(253,195,0,.12);

            color: var(--warning);

        }


        .summary-number {

            font-size: 27px;

            font-weight: 800;

            margin-top: 13px;

        }


        .summary-label {

            color: var(--text-muted);

            font-size: 12px;

        }


        /* =====================================================
           FILTER CARD
        ===================================================== */

        .filter-card {

            background: var(--bg-card);

            border: 1px solid var(--border);

            border-radius: 14px;

            padding: 20px;

            margin-bottom: 20px;

        }


        .filter-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 18px;

        }


        .filter-title {

            font-size: 14px;

            font-weight: 700;

        }


        .filter-title i {

            color: var(--sena-green);

            margin-right: 7px;

        }


        /* =====================================================
           VIEW BUTTONS
        ===================================================== */

        .view-buttons {

            display: flex;

            background: #0B1110;

            border-radius: 9px;

            padding: 4px;

        }


        .view-btn {

            border: none;

            background: transparent;

            color: var(--text-muted);

            padding: 8px 16px;

            border-radius: 7px;

            font-size: 12px;

            cursor: pointer;

        }


        .view-btn.active {

            background: var(--sena-green);

            color: white;

        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-label {

            color: var(--text-muted);

            font-size: 11px;

            margin-bottom: 6px;

        }


        .form-control,
        .form-select {

            background: #0B1110 !important;

            border: 1px solid var(--border) !important;

            color: white !important;

            font-size: 12px;

            border-radius: 8px;

            padding: 10px 12px;

        }


        .form-control:focus,
        .form-select:focus {

            border-color: var(--sena-green) !important;

            box-shadow: 0 0 0 2px rgba(57,169,0,.12) !important;

        }


        .form-select option {

            background: #101817;

            color: white;

        }


        /* =====================================================
           TABLE CARD
        ===================================================== */

        .table-card {

            background: var(--bg-card);

            border: 1px solid var(--border);

            border-radius: 14px;

            overflow: hidden;

        }


        .table-header {

            padding: 18px 20px;

            border-bottom: 1px solid var(--border);

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .table-header h3 {

            margin: 0;

            font-size: 14px;

            font-weight: 700;

        }


        .table-header span {

            font-size: 11px;

            color: var(--text-muted);

        }


        .table-responsive {

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 850px;

        }


        thead th {

            background: #141E1B;

            color: #A9B5B0;

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: .4px;

            padding: 13px 12px;

            border-bottom: 1px solid var(--border);

            white-space: nowrap;

        }


        tbody td {

            padding: 13px 12px;

            border-bottom: 1px solid rgba(255,255,255,.045);

            font-size: 12px;

            color: #D6DEDA;

            vertical-align: middle;

        }


        tbody tr:hover {

            background: rgba(255,255,255,.02);

        }


        tbody tr:last-child td {

            border-bottom: none;

        }


        .student {

            display: flex;

            align-items: center;

            gap: 10px;

            min-width: 180px;

        }


        .student-avatar {

            width: 32px;
            height: 32px;

            background: var(--sena-blue);

            border-radius: 8px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 11px;

            font-weight: 700;

        }


        .student-name {

            font-weight: 600;

            color: white;

        }


        .student-document {

            display: block;

            color: #65716D;

            font-size: 10px;

            margin-top: 2px;

        }


        /* =====================================================
           STATUS
        ===================================================== */

        .status {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 30px;
            height: 27px;

            border-radius: 7px;

            font-size: 11px;

            font-weight: 800;

        }


        .present {

            background: rgba(57,169,0,.13);

            color: #62D52D;

        }


        .absent {

            background: rgba(231,76,60,.13);

            color: #FF7165;

        }


        .excused {

            background: rgba(253,195,0,.13);

            color: #FFD84A;

        }


        .empty {

            color: #52605B;

            background: rgba(255,255,255,.025);

        }


        .total-fail {

            color: #FF7165;

            font-weight: 700;

        }


        /* =====================================================
           ACTION BUTTON
        ===================================================== */

        .btn-action {

            width: 30px;
            height: 30px;

            border: 1px solid var(--border);

            background: #17221F;

            color: #AAB5B0;

            border-radius: 7px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            cursor: pointer;

        }


        .btn-action:hover {

            background: var(--sena-green);

            color: white;

        }


        /* =====================================================
           LEGEND
        ===================================================== */

        .legend {

            display: flex;

            align-items: center;

            gap: 17px;

            padding: 15px 20px;

            border-top: 1px solid var(--border);

            color: var(--text-muted);

            font-size: 10px;

        }


        .legend-item {

            display: flex;

            align-items: center;

            gap: 5px;

        }


        .legend-dot {

            width: 9px;
            height: 9px;

            border-radius: 3px;

        }


        .dot-green {
            background: var(--sena-green);
        }

        .dot-red {
            background: var(--danger);
        }

        .dot-yellow {
            background: var(--warning);
        }


        /* =====================================================
           MONTH / YEAR
        ===================================================== */

        .month-view,
        .year-view {

            display: none;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .summary-grid {

                grid-template-columns: repeat(2, 1fr);

            }

        }


        @media (max-width: 800px) {

            .sidebar {

                width: 70px;

            }

            .logo-text,
            .menu-title,
            .menu-item span {

                display: none;

            }

            .logo {

                justify-content: center;

            }

            .menu-item {

                justify-content: center;

            }

            .main {

                margin-left: 70px;

                padding: 20px;

            }

            .page-header {

                align-items: flex-start;

            }

            .instructor-info {

                display: none;

            }

        }


        @media (max-width: 600px) {

            .summary-grid {

                grid-template-columns: 1fr;

            }

            .page-header {

                flex-direction: column;

                gap: 15px;

            }

            .filter-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 12px;

            }

            .view-buttons {

                width: 100%;

            }

            .view-btn {

                flex: 1;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <div class="logo-text">

            <strong>Portal SENA</strong>

            <span>Instructor</span>

        </div>

    </div>


    <div class="menu-title">
        Principal
    </div>


    <a href="<?= rutaBase() ?>/views/instructor/dashboard.php" class="menu-item">

        <i class="bi bi-grid-1x2-fill"></i>

        <span>Dashboard</span>

    </a>


    <a href="<?= rutaBase() ?>/views/aprendices/gestion.php" class="menu-item">

        <i class="bi bi-people-fill"></i>

        <span>Aprendices</span>

    </a>


    <a href="<?= rutaBase() ?>/views/asistencia/consulta.php" class="menu-item active">

        <i class="bi bi-calendar-check-fill"></i>

        <span>Asistencia</span>

    </a>


    <a href="<?= rutaBase() ?>/views/instructor/panel_gestion.php" class="menu-item">

        <i class="bi bi-clipboard2-check-fill"></i>

        <span>Calificaciones</span>

    </a>


    <a href="<?= rutaBase() ?>/views/instructor/novedades.php" class="menu-item">

        <i class="bi bi-exclamation-circle-fill"></i>

        <span>Novedades</span>

    </a>


    <div class="menu-title">
        Gestión
    </div>


    <a href="<?= rutaBase() ?>/views/reportes/reportes.php" class="menu-item">

        <i class="bi bi-bar-chart-fill"></i>

        <span>Reportes</span>

    </a>


    <a href="<?= rutaBase() ?>/views/instructor/novedades.php" class="menu-item">

        <i class="bi bi-bell-fill"></i>

        <span>Notificaciones</span>

    </a>


    <a href="<?= rutaBase() ?>/logout.php" class="menu-item">

        <i class="bi bi-box-arrow-left"></i>

        <span>Cerrar sesión</span>

    </a>

</aside>



<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


    <!-- HEADER -->

    <div class="page-header">

        <div class="page-title">

            <h1>
                Control de asistencia
            </h1>

            <p>
                Consulta y seguimiento de la asistencia de los aprendices.
            </p>

        </div>


        <div class="instructor">

            <div class="avatar">
                JP
            </div>

            <div class="instructor-info">

                <strong>
                    Juan Pérez
                </strong>

                <span>
                    Instructor SENA
                </span>

            </div>

        </div>

    </div>



    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <section class="summary-grid">


        <div class="summary-card">

            <div class="summary-top">

                <div>

                    <div class="summary-label">
                        Aprendices
                    </div>

                    <div class="summary-number">
                        32
                    </div>

                </div>

                <div class="summary-icon icon-blue">

                    <i class="bi bi-people-fill"></i>

                </div>

            </div>

        </div>



        <div class="summary-card">

            <div class="summary-top">

                <div>

                    <div class="summary-label">
                        Presentes hoy
                    </div>

                    <div class="summary-number">
                        27
                    </div>

                </div>

                <div class="summary-icon icon-green">

                    <i class="bi bi-person-check-fill"></i>

                </div>

            </div>

        </div>



        <div class="summary-card">

            <div class="summary-top">

                <div>

                    <div class="summary-label">
                        Fallas hoy
                    </div>

                    <div class="summary-number">
                        5
                    </div>

                </div>

                <div class="summary-icon icon-red">

                    <i class="bi bi-person-x-fill"></i>

                </div>

            </div>

        </div>



        <div class="summary-card">

            <div class="summary-top">

                <div>

                    <div class="summary-label">
                        Fallas justificadas
                    </div>

                    <div class="summary-number">
                        2
                    </div>

                </div>

                <div class="summary-icon icon-yellow">

                    <i class="bi bi-file-earmark-check-fill"></i>

                </div>

            </div>

        </div>


    </section>



    <!-- =====================================================
         FILTERS
    ====================================================== -->

    <section class="filter-card">


        <div class="filter-header">

            <div class="filter-title">

                <i class="bi bi-funnel-fill"></i>

                Filtros de asistencia

            </div>


            <div class="view-buttons">

                <button
                    class="view-btn active"
                    onclick="cambiarVista('dia', this)">

                    <i class="bi bi-calendar-day"></i>

                    Día

                </button>


                <button
                    class="view-btn"
                    onclick="cambiarVista('mes', this)">

                    <i class="bi bi-calendar-month"></i>

                    Mes

                </button>


                <button
                    class="view-btn"
                    onclick="cambiarVista('anio', this)">

                    <i class="bi bi-calendar3"></i>

                    Año

                </button>

            </div>

        </div>



        <div class="row g-3">


            <!-- FICHA -->

            <div class="col-lg-4">

                <label class="form-label">
                    Ficha de formación
                </label>

                <select class="form-select">

                    <option>
                        ADSO - 2026
                    </option>

                    <option>
                        ADSO - 2025
                    </option>

                </select>

            </div>



            <!-- FECHA -->

            <div class="col-lg-4" id="campoDia">

                <label class="form-label">
                    Fecha
                </label>

                <input
                    type="date"
                    class="form-control"
                    value="2026-10-02">

            </div>



            <!-- MES -->

            <div
                class="col-lg-4"
                id="campoMes"
                style="display:none;">

                <label class="form-label">
                    Mes
                </label>

                <select class="form-select">

                    <option>
                        Octubre 2026
                    </option>

                    <option>
                        Septiembre 2026
                    </option>

                    <option>
                        Agosto 2026
                    </option>

                </select>

            </div>



            <!-- AÑO -->

            <div
                class="col-lg-4"
                id="campoAnio"
                style="display:none;">

                <label class="form-label">
                    Año
                </label>

                <select class="form-select">

                    <option>2026</option>
                    <option>2025</option>
                    <option>2024</option>

                </select>

            </div>



            <!-- SEARCH -->

            <div class="col-lg-4">

                <label class="form-label">
                    Buscar aprendiz
                </label>

                <div class="input-group">

                    <input
                        type="text"
                        class="form-control"
                        placeholder="Nombre o documento...">

                    <button
                        class="btn btn-success"
                        style="background:#39A900;border:none;">

                        <i class="bi bi-search"></i>

                    </button>

                </div>

            </div>

        </div>

    </section>



    <!-- =====================================================
         DAY TABLE
    ====================================================== -->

    <section
        class="table-card"
        id="vistaDia">


        <div class="table-header">

            <div>

                <h3>
                    Asistencia del día
                </h3>

                <span>
                    Viernes, 02 de octubre de 2026
                </span>

            </div>


            <button class="btn-action">

                <i class="bi bi-download"></i>

            </button>

        </div>



        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>
                            Aprendiz
                        </th>

                        <th>
                            Hora
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Observación
                        </th>

                        <th>
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <!-- APRENDIZ 1 -->

                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    AR
                                </div>

                                <div>

                                    <span class="student-name">
                                        Ana Rodríguez
                                    </span>

                                    <span class="student-document">
                                        CC 1.023.456.789
                                    </span>

                                </div>

                            </div>

                        </td>


                        <td>
                            07:58 AM
                        </td>


                        <td>

                            <span class="status present">
                                ✓ Presente
                            </span>

                        </td>


                        <td>
                            —
                        </td>


                        <td>

                            <button
                                class="btn-action"
                                title="Editar">

                                <i class="bi bi-pencil"></i>

                            </button>

                        </td>

                    </tr>



                    <!-- APRENDIZ 2 -->

                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    CG
                                </div>

                                <div>

                                    <span class="student-name">
                                        Carlos Gómez
                                    </span>

                                    <span class="student-document">
                                        CC 1.098.765.432
                                    </span>

                                </div>

                            </div>

                        </td>


                        <td>
                            —
                        </td>


                        <td>

                            <span class="status absent">
                                ✕ Falla
                            </span>

                        </td>


                        <td>
                            Sin excusa
                        </td>


                        <td>

                            <button
                                class="btn-action"
                                title="Justificar falla"
                                data-bs-toggle="modal"
                                data-bs-target="#modalExcusa">

                                <i class="bi bi-file-earmark-plus"></i>

                            </button>

                        </td>

                    </tr>



                    <!-- APRENDIZ 3 -->

                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    LM
                                </div>

                                <div>

                                    <span class="student-name">
                                        Laura Martínez
                                    </span>

                                    <span class="student-document">
                                        CC 1.112.345.678
                                    </span>

                                </div>

                            </div>

                        </td>


                        <td>
                            08:03 AM
                        </td>


                        <td>

                            <span class="status excused">
                                E Excusada
                            </span>

                        </td>


                        <td>
                            Cita médica
                        </td>


                        <td>

                            <button
                                class="btn-action"
                                title="Editar">

                                <i class="bi bi-pencil"></i>

                            </button>

                        </td>

                    </tr>



                    <!-- APRENDIZ 4 -->

                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    DS
                                </div>

                                <div>

                                    <span class="student-name">
                                        Daniel Sánchez
                                    </span>

                                    <span class="student-document">
                                        CC 1.123.789.456
                                    </span>

                                </div>

                            </div>

                        </td>


                        <td>
                            08:01 AM
                        </td>


                        <td>

                            <span class="status present">
                                ✓ Presente
                            </span>

                        </td>


                        <td>
                            —
                        </td>


                        <td>

                            <button class="btn-action">

                                <i class="bi bi-pencil"></i>

                            </button>

                        </td>

                    </tr>



                    <!-- APRENDIZ 5 -->

                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    SM
                                </div>

                                <div>

                                    <span class="student-name">
                                        Sofía Méndez
                                    </span>

                                    <span class="student-document">
                                        CC 1.067.234.890
                                    </span>

                                </div>

                            </div>

                        </td>


                        <td>
                            —
                        </td>


                        <td>

                            <span class="status absent">
                                ✕ Falla
                            </span>

                        </td>


                        <td>
                            Sin excusa
                        </td>


                        <td>

                            <button
                                class="btn-action"
                                data-bs-toggle="modal"
                                data-bs-target="#modalExcusa">

                                <i class="bi bi-file-earmark-plus"></i>

                            </button>

                        </td>

                    </tr>


                </tbody>

            </table>

        </div>



        <div class="legend">

            <div class="legend-item">

                <span class="legend-dot dot-green"></span>

                Presente

            </div>


            <div class="legend-item">

                <span class="legend-dot dot-red"></span>

                Falla

            </div>


            <div class="legend-item">

                <span class="legend-dot dot-yellow"></span>

                Falla justificada

            </div>

        </div>

    </section>



    <!-- =====================================================
         MONTH VIEW
    ====================================================== -->

    <section
        class="table-card month-view"
        id="vistaMes">


        <div class="table-header">

            <div>

                <h3>
                    Asistencia mensual
                </h3>

                <span>
                    Octubre 2026
                </span>

            </div>


            <button class="btn-action">

                <i class="bi bi-download"></i>

            </button>

        </div>



        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>
                            Aprendiz
                        </th>

                        <th>01</th>
                        <th>02</th>
                        <th>03</th>
                        <th>04</th>
                        <th>05</th>
                        <th>06</th>
                        <th>07</th>
                        <th>08</th>
                        <th>09</th>
                        <th>10</th>

                        <th>
                            Fallas
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    AR
                                </div>

                                <span class="student-name">
                                    Ana Rodríguez
                                </span>

                            </div>

                        </td>

                        <td><span class="status present">✓</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status absent">✕</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status empty">—</span></td>
                        <td><span class="status empty">—</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status present">✓</span></td>

                        <td class="total-fail">
                            1
                        </td>

                    </tr>


                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    CG
                                </div>

                                <span class="student-name">
                                    Carlos Gómez
                                </span>

                            </div>

                        </td>

                        <td><span class="status present">✓</span></td>
                        <td><span class="status absent">✕</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status absent">✕</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status empty">—</span></td>
                        <td><span class="status empty">—</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status excused">E</span></td>
                        <td><span class="status present">✓</span></td>

                        <td class="total-fail">
                            2
                        </td>

                    </tr>


                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    LM
                                </div>

                                <span class="student-name">
                                    Laura Martínez
                                </span>

                            </div>

                        </td>

                        <td><span class="status present">✓</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status excused">E</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status empty">—</span></td>
                        <td><span class="status empty">—</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status present">✓</span></td>
                        <td><span class="status present">✓</span></td>

                        <td>
                            0
                        </td>

                    </tr>


                </tbody>

            </table>

        </div>


        <div class="legend">

            <div class="legend-item">
                <span class="legend-dot dot-green"></span>
                Presente
            </div>

            <div class="legend-item">
                <span class="legend-dot dot-red"></span>
                Falla
            </div>

            <div class="legend-item">
                <span class="legend-dot dot-yellow"></span>
                Excusa
            </div>

        </div>

    </section>



    <!-- =====================================================
         YEAR VIEW
    ====================================================== -->

    <section
        class="table-card year-view"
        id="vistaAnio">


        <div class="table-header">

            <div>

                <h3>
                    Resumen anual
                </h3>

                <span>
                    Año 2026
                </span>

            </div>


            <button class="btn-action">

                <i class="bi bi-download"></i>

            </button>

        </div>



        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>
                            Aprendiz
                        </th>

                        <th>Ene</th>
                        <th>Feb</th>
                        <th>Mar</th>
                        <th>Abr</th>
                        <th>May</th>
                        <th>Jun</th>
                        <th>Jul</th>
                        <th>Ago</th>
                        <th>Sep</th>
                        <th>Oct</th>
                        <th>Nov</th>
                        <th>Dic</th>

                        <th>
                            Total
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    AR
                                </div>

                                <span class="student-name">
                                    Ana Rodríguez
                                </span>

                            </div>

                        </td>

                        <td>1</td>
                        <td>0</td>
                        <td>2</td>
                        <td>1</td>
                        <td>0</td>
                        <td>1</td>
                        <td>0</td>
                        <td>2</td>
                        <td>0</td>
                        <td>1</td>
                        <td>0</td>
                        <td>0</td>

                        <td class="total-fail">
                            8
                        </td>

                    </tr>


                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    CG
                                </div>

                                <span class="student-name">
                                    Carlos Gómez
                                </span>

                            </div>

                        </td>

                        <td>2</td>
                        <td>1</td>
                        <td>3</td>
                        <td>0</td>
                        <td>2</td>
                        <td>0</td>
                        <td>1</td>
                        <td>2</td>
                        <td>1</td>
                        <td>2</td>
                        <td>0</td>
                        <td>0</td>

                        <td class="total-fail">
                            14
                        </td>

                    </tr>


                    <tr>

                        <td>

                            <div class="student">

                                <div class="student-avatar">
                                    LM
                                </div>

                                <span class="student-name">
                                    Laura Martínez
                                </span>

                            </div>

                        </td>

                        <td>0</td>
                        <td>0</td>
                        <td>1</td>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>
                        <td>1</td>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>

                        <td>
                            2
                        </td>

                    </tr>


                </tbody>

            </table>

        </div>

    </section>


</main>



<!-- =========================================================
     MODAL PARA EXCUSA
========================================================= -->

<div
    class="modal fade"
    id="modalExcusa"
    tabindex="-1"
    aria-hidden="true">


    <div class="modal-dialog modal-dialog-centered">


        <div
            class="modal-content"
            style="
                background:#101817;
                color:white;
                border:1px solid rgba(255,255,255,.08);
                border-radius:15px;
            ">


            <div class="modal-header">

                <h5 class="modal-title">

                    <i
                        class="bi bi-file-earmark-medical-fill"
                        style="color:#FDC300;">
                    </i>

                    Justificar falla

                </h5>


                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">


                <div class="mb-3">

                    <label class="form-label">
                        Aprendiz
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="Carlos Gómez"
                        readonly>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Tipo de novedad
                    </label>

                    <select class="form-select">

                        <option>
                            Incapacidad médica
                        </option>

                        <option>
                            Cita médica
                        </option>

                        <option>
                            Calamidad familiar
                        </option>

                        <option>
                            Otro
                        </option>

                    </select>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Observación
                    </label>

                    <textarea
                        class="form-control"
                        rows="3"
                        placeholder="Escriba la observación..."></textarea>

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Adjuntar soporte
                    </label>

                    <input
                        type="file"
                        class="form-control"
                        accept=".pdf,.jpg,.jpeg,.png">

                </div>


            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn"
                    style="
                        background:#39A900;
                        color:white;
                    ">

                    <i class="bi bi-check-lg"></i>

                    Guardar justificación

                </button>

            </div>


        </div>

    </div>

</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>



<script>

    /* =====================================================
       CAMBIAR ENTRE DÍA / MES / AÑO
    ===================================================== */

    function cambiarVista(vista, boton) {

        /*
        Quitar active de todos los botones
        */

        document
            .querySelectorAll('.view-btn')
            .forEach(btn => {

                btn.classList.remove('active');

            });


        /*
        Activar botón seleccionado
        */

        boton.classList.add('active');


        /*
        Ocultar todas las vistas
        */

        document.getElementById('vistaDia').style.display = 'none';

        document.getElementById('vistaMes').style.display = 'none';

        document.getElementById('vistaAnio').style.display = 'none';


        /*
        Ocultar campos
        */

        document.getElementById('campoDia').style.display = 'none';

        document.getElementById('campoMes').style.display = 'none';

        document.getElementById('campoAnio').style.display = 'none';


        /*
        Mostrar vista seleccionada
        */

        if (vista === 'dia') {

            document.getElementById('vistaDia').style.display = 'block';

            document.getElementById('campoDia').style.display = 'block';

        }


        if (vista === 'mes') {

            document.getElementById('vistaMes').style.display = 'block';

            document.getElementById('campoMes').style.display = 'block';

        }


        if (vista === 'anio') {

            document.getElementById('vistaAnio').style.display = 'block';

            document.getElementById('campoAnio').style.display = 'block';

        }

    }

</script>


</body>

</html>