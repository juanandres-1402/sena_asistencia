<?php
/**
 * Vista integrada desde el proyecto 'controlasistencia'.
 * Protegida con la sesión y los roles del sistema sena_asistencia.
 */
require_once __DIR__ . '/../../includes/sesion.php';
exigirRol(['aprendiz']);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Portal Académico | Dashboard del Aprendiz</title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


    <style>

        /* =====================================================
           VARIABLES - COLORIMETRÍA SENA
        ====================================================== */

        :root {

            --sena-green: #39A900;
            --sena-green-dark: #007832;

            --sena-blue: #00304D;
            --sena-purple: #71277A;
            --sena-cyan: #50E5F9;
            --sena-yellow: #FDC300;

            --bg-dark: #0B1110;
            --card-dark: #101817;
            --input-bg: #17221F;

            --border-dark: rgba(57, 169, 0, 0.20);
            --border-focus: rgba(57, 169, 0, 0.60);

            --text-white: #FFFFFF;
            --text-light: #F6F6F6;
            --text-muted: #A7B2AD;
            --text-secondary: #7F8D87;

            --primary: #39A900;
            --primary-hover: #007832;

            --success: #39A900;
        }

        /* =====================================================
           MODO CLARO - SENA
        ===================================================== */

        [data-theme="light"] {

            --bg-dark: #F4F7F3;
            --card-dark: #FFFFFF;
            --input-bg: #EEF3EF;

            --border-dark: rgba(0, 120, 50, 0.16);
            --border-focus: rgba(57, 169, 0, 0.50);

            --text-white: #00304D;
            --text-light: #17221F;
            --text-muted: #53635C;
            --text-secondary: #718078;

            --primary: #39A900;
            --primary-hover: #007832;

            --success: #39A900;
        }

        /* =====================================================
           RESET
        ====================================================== */

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {

            margin: 0;

            min-height: 100vh;

            background: var(--bg-dark);

            color: var(--text-light);

            font-family:
                "Segoe UI",
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                sans-serif;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }


        /* =====================================================
           SCROLLBAR
        ====================================================== */

        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }

        ::-webkit-scrollbar-thumb {

            background: var(--sena-green-dark);

            border-radius: 20px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--sena-green);
        }


        /* =====================================================
           SIDEBAR
        ====================================================== */

        .sidebar {

            position: fixed;

            top: 20px;
            left: 20px;
            bottom: 20px;

            width: 260px;

            background: var(--card-dark);

            border: 1px solid var(--border-dark);

            border-radius: 28px;

            padding: 18px 14px;

            display: flex;

            flex-direction: column;

            z-index: 1000;

            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.25);

            transition:
                width 0.3s cubic-bezier(0.4, 0, 0.2, 1);

            overflow: hidden;
        }


        /* =====================================================
           SIDEBAR COLAPSADA
        ====================================================== */

        .sidebar.collapsed {

            width: 82px;

            align-items: center;
        }


        /* =====================================================
           BOTÓN TOGGLE
        ====================================================== */

        .sidebar-toggle-btn {

            position: absolute;

            top: 18px;
            right: 14px;

            width: 28px;
            height: 28px;

            border-radius: 50%;

            background: var(--input-bg);

            border: 1px solid var(--border-dark);

            color: var(--text-muted);

            display: flex;

            align-items: center;
            justify-content: center;

            cursor: pointer;

            transition: all 0.25s ease;

            z-index: 10;
        }

        .sidebar-toggle-btn:hover {

            background: var(--sena-green);

            color: var(--text-white);

            border-color: var(--sena-green);
        }


        .sidebar.collapsed .sidebar-toggle-btn {

            position: relative;

            top: 0;
            right: 0;

            margin-bottom: 15px;

            transform: rotate(180deg);
        }


        /* =====================================================
           HEADER SIDEBAR
        ====================================================== */

        .sidebar-header {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 28px;

            width: 100%;
        }


        /* =====================================================
           LOGO
        ====================================================== */

        .sidebar-logo {

            min-width: 48px;

            width: 48px;
            height: 48px;

            background: var(--sena-green);

            border-radius: 16px;

            display: flex;

            align-items: center;
            justify-content: center;

            color: var(--text-white);

            font-size: 22px;

            box-shadow:
                0 8px 20px rgba(57, 169, 0, 0.20);
        }


        /* =====================================================
           TEXTO LOGO
        ====================================================== */

        .sidebar-brand-text {

            display: flex;

            flex-direction: column;

            white-space: nowrap;

            opacity: 1;

            transition: opacity 0.2s ease;
        }

        .brand-title {

            color: var(--text-white);

            font-weight: 800;

            font-size: 16px;

            letter-spacing: 0.5px;
        }

        .brand-subtitle {

            color: var(--sena-cyan);

            font-weight: 700;

            font-size: 10px;

            letter-spacing: 0.8px;
        }


        .sidebar.collapsed .sidebar-brand-text {

            display: none;

            opacity: 0;
        }


        /* =====================================================
           MENÚ
        ====================================================== */

        .sidebar-nav {

            width: 100%;

            display: flex;

            flex-direction: column;

            gap: 8px;
        }


        .sidebar-btn {

            width: 100%;

            height: 48px;

            border: none;

            border-radius: 16px;

            background: transparent;

            color: var(--text-muted);

            display: flex;

            align-items: center;

            padding: 0 14px;

            gap: 14px;

            font-size: 14px;

            font-weight: 500;

            cursor: pointer;

            transition: all 0.25s ease;

            white-space: nowrap;
        }


        .sidebar-btn i {

            font-size: 18px;

            min-width: 20px;

            text-align: center;
        }


        .sidebar-btn span {

            transition: opacity 0.2s ease;
        }


        /* =====================================================
           SIDEBAR COLAPSADA - BOTONES
        ====================================================== */

        .sidebar.collapsed .sidebar-btn {

            width: 48px;

            justify-content: center;

            padding: 0;
        }


        .sidebar.collapsed .sidebar-btn span {

            display: none;
        }


        .sidebar.collapsed .sidebar-btn .menu-arrow {

            display: none;
        }


        /* =====================================================
           HOVER
        ====================================================== */

        .sidebar-btn:hover {

            background: var(--input-bg);

            color: var(--text-white);

            transform: translateY(-1px);
        }


        /* =====================================================
           ACTIVE
        ====================================================== */

        .sidebar-btn.active {

            background: var(--sena-green);

            color: var(--text-white);

            box-shadow:
                0 8px 20px rgba(57, 169, 0, 0.25);
        }


        /* =====================================================
           BADGE
        ====================================================== */

        .badge-count {

            margin-left: auto;

            background: #FF4D4D;

            color: white;

            font-size: 10px;

            font-weight: 700;

            padding: 2px 7px;

            border-radius: 10px;
        }


        .sidebar.collapsed .badge-count {

            display: none;
        }


        /* =====================================================
           PARTE INFERIOR
        ====================================================== */

        .sidebar-bottom {

            margin-top: auto;

            width: 100%;

            display: flex;

            flex-direction: column;

            gap: 10px;

            padding-top: 15px;

            border-top: 1px solid var(--border-dark);
        }


        /* =====================================================
           PERFIL
        ====================================================== */

        .user-profile-card {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 8px;

            border-radius: 16px;

            background: rgba(23, 34, 31, 0.5);
        }


        .sidebar-avatar {

            min-width: 40px;

            width: 40px;
            height: 40px;

            border-radius: 50%;

            background: var(--sena-purple);

            color: var(--text-white);

            display: flex;

            align-items: center;
            justify-content: center;

            font-weight: 700;

            font-size: 14px;
        }


        .user-details {

            display: flex;

            flex-direction: column;

            overflow: hidden;

            white-space: nowrap;
        }


        .user-details .name {

            color: var(--text-white);

            font-size: 12px;

            font-weight: 600;
        }


        .user-details .role {

            color: var(--text-secondary);

            font-size: 10px;
        }


        .sidebar.collapsed .user-details {

            display: none;
        }


        .sidebar.collapsed .user-profile-card {

            background: transparent;

            padding: 0;

            justify-content: center;
        }


        /* =====================================================
           CERRAR SESIÓN
        ====================================================== */

        .logout-btn {

            color: #FF5A5A !important;
        }


        .logout-btn:hover {

            background: rgba(255, 90, 90, 0.10) !important;

            transform: none;
        }


        /* =====================================================
           CONTENIDO PRINCIPAL
        ====================================================== */

        .main-content {

            margin-left: 300px;

            padding: 28px 30px 40px;

            min-height: 100vh;

            transition:
                margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }


        .main-content.expanded {

            margin-left: 124px;
        }


        /* =====================================================
           HEADER
        ====================================================== */

        .dashboard-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 28px;
        }


        .welcome-title {

            margin: 0;

            color: var(--text-white);

            font-size: 28px;

            font-weight: 700;
        }


        .welcome-title span {

            color: var(--sena-green);
        }


        .welcome-subtitle {

            margin: 6px 0 0;

            color: var(--text-muted);

            font-size: 13px;
        }


        /* =====================================================
           USUARIO HEADER
        ====================================================== */

        .user-area {

            display: flex;

            align-items: center;

            gap: 14px;
        }


        .notification-btn {

            width: 45px;
            height: 45px;

            border: 1px solid var(--border-dark);

            border-radius: 14px;

            background: var(--card-dark);

            color: var(--text-muted);

            position: relative;

            cursor: pointer;

            transition: 0.25s;
        }


        .notification-btn:hover {

            color: var(--sena-green);

            border-color: var(--border-focus);
        }


        .notification-dot {

            position: absolute;

            top: 8px;
            right: 8px;

            width: 7px;
            height: 7px;

            background: var(--sena-green);

            border-radius: 50%;
        }


        .user-info {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .user-avatar {

            width: 45px;
            height: 45px;

            border-radius: 15px;

            background: var(--sena-green-dark);

            color: var(--text-white);

            display: flex;

            align-items: center;
            justify-content: center;

            font-weight: 700;
        }


        .user-name {

            color: var(--text-white);

            font-size: 13px;

            font-weight: 600;
        }


        .user-role {

            color: var(--text-secondary);

            font-size: 11px;

            margin-top: 2px;
        }


        /* =====================================================
           CARDS
        ====================================================== */

        .dashboard-card {

            height: 100%;

            padding: 22px;

            background: var(--card-dark);

            border: 1px solid var(--border-dark);

            border-radius: 22px;

            box-shadow:
                0 12px 35px rgba(0, 0, 0, 0.16);

            transition:
                border-color 0.25s ease,
                transform 0.25s ease;
        }


        .dashboard-card:hover {

            border-color: var(--border-focus);
        }


        .card-title {

            color: var(--text-white);

            font-size: 16px;

            font-weight: 700;

            margin-bottom: 4px;
        }


        .card-subtitle {

            color: var(--text-secondary);

            font-size: 11px;

            margin-bottom: 18px;
        }


        /* =====================================================
           ESTADÍSTICAS
        ====================================================== */

        .stat-card {

            position: relative;

            overflow: hidden;
        }


        .stat-icon {

            width: 48px;
            height: 48px;

            border-radius: 15px;

            display: flex;

            align-items: center;
            justify-content: center;

            margin-bottom: 15px;

            font-size: 20px;
        }


        .stat-green {

            background: rgba(57, 169, 0, 0.14);

            color: var(--sena-green);
        }


        .stat-blue {

            background: rgba(0, 48, 77, 0.35);

            color: var(--sena-cyan);
        }


        .stat-purple {

            background: rgba(113, 39, 122, 0.20);

            color: var(--sena-purple);
        }


        .stat-yellow {

            background: rgba(253, 195, 0, 0.13);

            color: var(--sena-yellow);
        }


        .stat-number {

            color: var(--text-white);

            font-size: 27px;

            font-weight: 700;

            line-height: 1;
        }


        .stat-label {

            color: var(--text-muted);

            font-size: 11px;

            margin-top: 6px;
        }


        /* =====================================================
           PROGRESO
        ====================================================== */

        .progress {

            height: 8px;

            background: var(--input-bg);

            border-radius: 20px;

            overflow: hidden;
        }


        .progress-bar {

            background: var(--sena-green);

            border-radius: 20px;
        }


        /* =====================================================
           BADGES
        ====================================================== */

        .status-badge {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            border-radius: 20px;

            padding: 6px 10px;

            font-size: 9px;

            font-weight: 600;

            white-space: nowrap;
        }


        .status-success {

            background: rgba(57, 169, 0, 0.13);

            color: var(--sena-green);
        }


        .status-warning {

            background: rgba(253, 195, 0, 0.12);

            color: var(--sena-yellow);
        }


        .status-danger {

            background: rgba(113, 39, 122, 0.15);

            color: #c58bd0;
        }


        .status-info {

            background: rgba(80, 229, 249, 0.10);

            color: var(--sena-cyan);
        }


        /* =====================================================
           ACCIONES RÁPIDAS
        ====================================================== */

        .quick-grid {

            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 11px;
        }


        .quick-action {

            background: var(--input-bg);

            border: 1px solid var(--border-dark);

            border-radius: 17px;

            padding: 16px 10px;

            text-align: center;

            cursor: pointer;

            transition: 0.25s;
        }


        .quick-action:hover {

            border-color: var(--sena-green);

            transform: translateY(-2px);
        }


        .quick-action i {

            display: block;

            color: var(--sena-green);

            font-size: 20px;

            margin-bottom: 7px;
        }


        .quick-action span {

            color: var(--text-muted);

            font-size: 10px;
        }


        /* =====================================================
           ACTIVIDAD
        ====================================================== */

        .notification-item {

            display: flex;

            gap: 12px;

            padding: 14px 0;

            border-bottom: 1px solid var(--border-dark);
        }


        .notification-item:last-child {

            border-bottom: none;
        }


        .notification-icon {

            min-width: 38px;
            height: 38px;

            border-radius: 12px;

            display: flex;

            align-items: center;
            justify-content: center;
        }


        .notification-icon.green {

            background: rgba(57, 169, 0, 0.13);

            color: var(--sena-green);
        }


        .notification-icon.yellow {

            background: rgba(253, 195, 0, 0.12);

            color: var(--sena-yellow);
        }


        .notification-icon.cyan {

            background: rgba(80, 229, 249, 0.10);

            color: var(--sena-cyan);
        }


        .notification-title {

            color: var(--text-white);

            font-size: 12px;

            font-weight: 600;

            margin-bottom: 3px;
        }


        .notification-text {

            color: var(--text-secondary);

            font-size: 10px;

            line-height: 1.5;

            margin: 0;
        }


        /* =====================================================
           TABS
        ====================================================== */

        .dashboard-tabs {

            display: flex;

            gap: 7px;

            padding: 7px;

            margin-bottom: 22px;

            background: var(--card-dark);

            border: 1px solid var(--border-dark);

            border-radius: 18px;

            overflow-x: auto;
        }


        .dashboard-tabs .nav-link {

            color: var(--text-muted);

            background: transparent;

            border-radius: 13px;

            padding: 10px 15px;

            font-size: 11px;

            font-weight: 600;

            white-space: nowrap;

            border: none;
        }


        .dashboard-tabs .nav-link:hover {

            background: var(--input-bg);

            color: var(--text-white);
        }


        .dashboard-tabs .nav-link.active {

            background: var(--sena-green);

            color: var(--text-white);

            box-shadow:
                0 5px 15px rgba(57, 169, 0, 0.18);
        }


        /* =====================================================
           TABLA
        ====================================================== */

        .table {

            --bs-table-bg: transparent;

            --bs-table-color: var(--text-light);

            margin-bottom: 0;
        }


        .table thead th {

            color: var(--text-secondary);

            border-bottom: 1px solid var(--border-dark);

            padding: 13px;

            font-size: 10px;

            font-weight: 600;

            text-transform: uppercase;
        }


        .table tbody td {

            color: var(--text-muted);

            border-bottom: 1px solid rgba(57, 169, 0, 0.08);

            padding: 14px 13px;

            font-size: 11px;
        }


        .table tbody tr:hover {

            background: rgba(57, 169, 0, 0.035);
        }


        /* =====================================================
           CALENDARIO
        ====================================================== */

        .calendar-grid {

            display: grid;

            grid-template-columns: repeat(7, 1fr);

            gap: 8px;
        }


        .calendar-day-header {

            color: var(--text-secondary);

            text-align: center;

            padding: 8px;

            font-size: 10px;

            font-weight: 600;
        }


        .calendar-day {

            min-height: 75px;

            padding: 8px;

            background: var(--input-bg);

            border: 1px solid var(--border-dark);

            border-radius: 12px;

            color: var(--text-muted);

            font-size: 10px;

            transition: 0.2s;
        }


        .calendar-day:hover {

            border-color: var(--sena-green);
        }


        .calendar-day.today {

            border-color: var(--sena-green);

            color: var(--text-white);

            box-shadow:
                inset 0 0 0 1px var(--sena-green);
        }


        .event-badge {

            display: block;

            margin-top: 8px;

            padding: 4px;

            border-radius: 6px;

            font-size: 7px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .event-green {

            background: rgba(57, 169, 0, 0.17);

            color: var(--sena-green);
        }


        .event-yellow {

            background: rgba(253, 195, 0, 0.14);

            color: var(--sena-yellow);
        }


        .event-purple {

            background: rgba(113, 39, 122, 0.18);

            color: #c58bd0;
        }


        /* =====================================================
           FORMULARIOS
        ====================================================== */

        .form-label {

            color: var(--text-muted);

            font-size: 11px;

            font-weight: 600;
        }


        .form-control,
        .form-select {

            background: var(--input-bg);

            color: var(--text-light);

            border: 1px solid var(--border-dark);

            border-radius: 12px;

            padding: 11px 13px;

            font-size: 11px;
        }


        .form-control:focus,
        .form-select:focus {

            background: var(--input-bg);

            color: var(--text-white);

            border-color: var(--border-focus);

            box-shadow:
                0 0 0 3px rgba(57, 169, 0, 0.08);
        }


        .form-control::placeholder {

            color: var(--text-secondary);
        }


        .form-select option {

            background: var(--input-bg);

            color: var(--text-white);
        }


        /* =====================================================
           BOTONES
        ====================================================== */

        .btn-sena {

            background: var(--primary);

            color: var(--text-white);

            border: none;

            border-radius: 12px;

            padding: 11px 18px;

            font-size: 11px;

            font-weight: 600;

            transition: 0.25s;
        }


        .btn-sena:hover {

            background: var(--primary-hover);

            color: var(--text-white);

            transform: translateY(-1px);
        }


        .btn-outline-sena {

            background: transparent;

            color: var(--sena-green);

            border: 1px solid var(--sena-green);

            border-radius: 10px;

            padding: 7px 11px;

            font-size: 11px;

            transition: 0.2s;
        }


        .btn-outline-sena:hover {

            background: var(--sena-green);

            color: var(--text-white);
        }


        /* =====================================================
           COMPETENCIAS
        ====================================================== */

        .competency-card {

            height: 100%;

            padding: 18px;

            background: var(--input-bg);

            border: 1px solid var(--border-dark);

            border-radius: 17px;

            transition: 0.25s;
        }


        .competency-card:hover {

            border-color: var(--border-focus);

            transform: translateY(-2px);
        }


        .competency-icon {

            width: 40px;
            height: 40px;

            border-radius: 12px;

            background: rgba(57, 169, 0, 0.13);

            color: var(--sena-green);

            display: flex;

            align-items: center;
            justify-content: center;

            margin-bottom: 13px;
        }


        .competency-title {

            color: var(--text-white);

            font-size: 12px;

            font-weight: 600;

            margin: 10px 0 6px;
        }


        .competency-description {

            color: var(--text-secondary);

            font-size: 9px;

            line-height: 1.5;

            margin-bottom: 15px;
        }


        /* =====================================================
           INFORMACIÓN
        ====================================================== */

        .info-label {

            color: var(--text-secondary);

            font-size: 10px;

            margin-bottom: 4px;
        }


        .info-value {

            color: var(--text-white);

            font-size: 11px;

            font-weight: 600;
        }


        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (max-width: 1000px) {

            .sidebar {

                width: 82px;

                left: 14px;

                top: 14px;

                bottom: 14px;
            }


            .sidebar .sidebar-brand-text,
            .sidebar .sidebar-btn span,
            .sidebar .user-details,
            .sidebar .badge-count {

                display: none;
            }


            .sidebar .sidebar-btn {

                width: 48px;

                justify-content: center;

                padding: 0;
            }


            .sidebar .sidebar-toggle-btn {

                position: relative;

                top: 0;

                right: 0;

                margin-bottom: 15px;

                transform: rotate(180deg);
            }


            .main-content {

                margin-left: 124px;
            }


            .main-content.expanded {

                margin-left: 124px;
            }
        }


        /* =====================================================
           MÓVIL
        ====================================================== */

        @media (max-width: 700px) {

            body {

                padding-bottom: 95px;
            }


            .sidebar {

                position: fixed;

                top: auto;

                left: 15px;

                right: 15px;

                bottom: 15px;

                width: auto;

                height: 65px;

                padding: 8px 12px;

                flex-direction: row;

                border-radius: 20px;
            }


            .sidebar-toggle-btn {

                display: none;
            }


            .sidebar-header {

                width: auto;

                margin: 0 8px 0 0;
            }


            .sidebar-logo {

                width: 43px;

                height: 43px;

                min-width: 43px;
            }


            .sidebar-brand-text {

                display: none;
            }


            .sidebar-nav {

                flex-direction: row;

                justify-content: center;

                gap: 4px;
            }


            .sidebar-btn {

                width: 42px;

                height: 42px;

                border-radius: 13px;
            }


            .sidebar-nav .sidebar-btn:nth-child(n+5) {

                display: none;
            }


            .sidebar-bottom {

                margin: 0 0 0 auto;

                width: auto;

                flex-direction: row;

                border: none;

                padding: 0;
            }


            .sidebar-bottom .sidebar-btn {

                display: none;
            }


            .user-profile-card {

                background: transparent;

                padding: 0;
            }


            .user-details {

                display: none;
            }


            .main-content {

                margin-left: 0 !important;

                padding: 20px 15px;

                padding-bottom: 95px;
            }


            .dashboard-header {

                align-items: flex-start;
            }


            .welcome-title {

                font-size: 21px;
            }


            .welcome-subtitle {

                font-size: 11px;
            }


            .user-info > div {

                display: none;
            }


            .dashboard-card {

                padding: 17px;

                border-radius: 18px;
            }


            .calendar-grid {

                gap: 4px;
            }


            .calendar-day {

                min-height: 55px;

                padding: 5px;

                font-size: 9px;
            }


            .event-badge {

                font-size: 6px;
            }
        }


        @media (max-width: 450px) {

            .notification-btn {

                width: 40px;

                height: 40px;
            }


            .user-avatar {

                width: 40px;

                height: 40px;
            }


            .sidebar {

                left: 8px;

                right: 8px;

                bottom: 8px;
            }


            .sidebar-logo {

                display: none;
            }


            .sidebar-nav {

                width: 100%;
            }


            .sidebar-btn {

                width: 39px;

                height: 39px;
            }
        }


        /* =====================================================
   BOTÓN MODO CLARO / OSCURO
===================================================== */

.theme-toggle {

    width: 45px;
    height: 45px;

    min-width: 45px;

    border: 1px solid var(--border-dark);

    border-radius: 14px;

    background: var(--card-dark);

    color: var(--text-muted);

    display: flex;

    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    cursor: pointer;

    transition:
        background 0.25s ease,
        color 0.25s ease,
        border-color 0.25s ease,
        transform 0.25s ease;
}


.theme-toggle:hover {

    color: var(--sena-green);

    border-color: var(--border-focus);

    transform: translateY(-1px);
}


.theme-toggle i {

    font-size: 17px;

    line-height: 1;
}


/* =====================================================
   AJUSTES PARA MODO CLARO
===================================================== */

[data-theme="light"] .sidebar {

    box-shadow:
        0 15px 40px rgba(0, 48, 77, 0.08);
}


[data-theme="light"] .dashboard-card {

    box-shadow:
        0 10px 30px rgba(0, 48, 77, 0.07);
}


[data-theme="light"] .user-profile-card {

    background: #EEF3EF;
}


[data-theme="light"] .quick-action:hover {

    background: #F8FBF8;
}


[data-theme="light"] .calendar-day {

    background: #EEF3EF;
}


[data-theme="light"] .dashboard-tabs {

    background: #FFFFFF;
}


[data-theme="light"] .notification-item {

    border-color: rgba(0, 120, 50, 0.12);
}


[data-theme="light"] .table tbody tr:hover {

    background: rgba(57, 169, 0, 0.05);
}


[data-theme="light"] .sidebar-btn:hover {

    background: #EEF3EF;

    color: var(--sena-blue);
}

/* Base de la tarjeta de competencia */
.competency-card {
    background: var(--card-dark);
    border: 1px solid var(--border-dark);
    border-radius: 18px;
    padding: 20px;
    height: 100%;
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease, opacity 0.3s ease;
}

/* Efecto Hover Genérico (Resplandor) */
.competency-card:hover {
    transform: translateY(-4px);
    opacity: 1 !important; /* Asegura que la tarjeta sin iniciar brille bien al pasar el cursor */
    border-color: var(--hover-color, var(--sena-green));
    box-shadow: 0 8px 25px var(--hover-glow, rgba(57, 169, 0, 0.35));
}

/* =====================================================
   CLASES DE ILUMINACIÓN POR ESTADO
====================================================== */

/* 1. Aprobadas -> Verde SENA */
.card-hover-aprobada {
    --hover-color: var(--sena-green);
    --hover-glow: rgba(57, 169, 0, 0.40);
}

/* 2. En Curso / Pendientes -> Amarillo SENA */
.card-hover-pendiente {
    --hover-color: var(--sena-yellow);
    --hover-glow: rgba(253, 195, 0, 0.38);
}

/* 3. Por Ver / Sin Iniciar -> Violeta SENA */
.card-hover-sin-iniciar {
    --hover-color: var(--sena-purple);
    --hover-glow: rgba(113, 39, 122, 0.50);
}

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================== -->

<aside class="sidebar" id="sidebar">


    <!-- BOTÓN PARA EXPANDIR / COLAPSAR -->

    <button
        class="sidebar-toggle-btn"
        id="toggle-btn"
        title="Expandir / Colapsar menú"
        onclick="toggleSidebar()">

        <i class="bi bi-chevron-left"></i>

    </button>


    <!-- HEADER SIDEBAR -->

    <div class="sidebar-header">


        <div class="sidebar-logo">

            <i class="bi bi-mortarboard-fill"></i>

        </div>


        <div class="sidebar-brand-text">

            <span class="brand-title">
                EDU-SENA
            </span>

            <span class="brand-subtitle">
                GESTIÓN ACADÉMICA
            </span>

        </div>

    </div>


    <!-- =====================================================
         MENÚ
    ====================================================== -->

    <nav class="sidebar-nav">


        <!-- INICIO -->

        <button
            class="sidebar-btn active"
            title="Inicio"
            onclick="activarTab('notes-tab', this)">

            <i class="bi bi-grid-1x2-fill"></i>

            <span>
                Inicio
            </span>

        </button>


        <!-- CALENDARIO -->

        <button
            class="sidebar-btn"
            title="Calendario"
            onclick="activarTab('calendar-tab', this)">

            <i class="bi bi-calendar3"></i>

            <span>
                Calendario
            </span>

        </button>


        <!-- ASISTENCIA -->

        <button
            class="sidebar-btn"
            title="Asistencia"
            onclick="activarTab('attendance-tab', this)">

            <i class="bi bi-person-check"></i>

            <span>
                Asistencia y Excusas
            </span>

        </button>


        <!-- COMPETENCIAS -->

        <button
            class="sidebar-btn"
            title="Competencias"
            onclick="activarTab('competencies-tab', this)">

            <i class="bi bi-award"></i>

            <span>
                Competencias
            </span>

        </button>


        <!-- DOCUMENTOS -->

        <button
            class="sidebar-btn"
            title="Documentos"
            onclick="mostrarMensaje('Documentos')">

            <i class="bi bi-folder2"></i>

            <span>
                Documentos
            </span>

        </button>


        <!-- MENSAJES -->

        <button
            class="sidebar-btn"
            title="Mensajes"
            onclick="mostrarMensaje('Mensajes')">

            <i class="bi bi-chat-dots"></i>

            <span>
                Mensajes
            </span>

        </button>

    </nav>


    <!-- =====================================================
         PARTE INFERIOR
    ====================================================== -->

    <div class="sidebar-bottom">


        <!-- CONFIGURACIÓN -->

        <button
            class="sidebar-btn"
            title="Configuración"
            onclick="mostrarMensaje('Configuración')">

            <i class="bi bi-gear"></i>

            <span>
                Configuración
            </span>

        </button>


        <!-- PERFIL -->

        <div class="user-profile-card">

            <div class="sidebar-avatar">
                P
            </div>


            <div class="user-details">

                <span class="name">
                    Paula Duque
                </span>

                <span class="role">
                    Aprendiz ADSO
                </span>

            </div>

        </div>


        <!-- CERRAR SESIÓN -->

        <button
            class="sidebar-btn logout-btn"
            title="Cerrar Sesión"
            onclick="cerrarSesion()">

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Cerrar Sesión
            </span>

        </button>

    </div>

</aside>


<!-- =========================================================
     CONTENIDO
========================================================== -->

<main class="main-content" id="main-content">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="dashboard-header">


        <div>

            <h1 class="welcome-title">

                Hola,
                <span>Paula</span>

            </h1>


            <p class="welcome-subtitle">

                Aquí tienes un resumen de tu actividad académica.

            </p>

        </div>

        <div class="user-area">

    <!-- MODO CLARO / OSCURO -->
    <button
        class="theme-toggle"
        id="theme-toggle"
        type="button"
        title="Cambiar tema"
        onclick="toggleTheme()">

        <i class="bi bi-sun-fill" id="theme-icon"></i>

    </button>


    <!-- NOTIFICACIONES -->
    <button
        class="notification-btn"
        title="Notificaciones">

        <i class="bi bi-bell"></i>

        <span class="notification-dot"></span>

    </button>


    <!-- USUARIO -->
    <div class="user-info">

        <div class="user-avatar">
            P
        </div>


        <div>

            <div class="user-name">
                Paula Duque
            </div>

            <div class="user-role">
                Aprendiz ADSO
            </div>

        </div>

    </div>

</div>


<!--         <div class="user-area">


            <button
                class="notification-btn"
                title="Notificaciones">

                <i class="bi bi-bell"></i>

                <span class="notification-dot"></span>

            </button>


            <div class="user-info">

                <div class="user-avatar">
                    P
                </div>


                <div>

                    <div class="user-name">
                        Paula Duque
                    </div>

                    <div class="user-role">
                        Aprendiz ADSO
                    </div>

                </div>

            </div>

        </div> -->

    </header>


    <!-- =====================================================
         ESTADÍSTICAS
    ====================================================== -->

    <div class="row g-3 mb-4">


        <div class="col-6 col-xl-3">

            <div class="dashboard-card stat-card">

                <div class="stat-icon stat-green">
                    <i class="bi bi-person-check"></i>
                </div>

                <div class="stat-number">
                    92%
                </div>

                <div class="stat-label">
                    Asistencia
                </div>

            </div>

        </div>


        <div class="col-6 col-xl-3">

            <div class="dashboard-card stat-card">

                <div class="stat-icon stat-blue">
                    <i class="bi bi-book"></i>
                </div>

                <div class="stat-number">
                    8
                </div>

                <div class="stat-label">
                    Competencias
                </div>

            </div>

        </div>


        <div class="col-6 col-xl-3">

            <div class="dashboard-card stat-card">

                <div class="stat-icon stat-purple">
                    <i class="bi bi-award"></i>
                </div>

                <div class="stat-number">
                    2
                </div>

                <div class="stat-label">
                    Competencias aprobadas
                </div>

            </div>

        </div>


        <div class="col-6 col-xl-3">

            <div class="dashboard-card stat-card">

                <div class="stat-icon stat-yellow">
                    <i class="bi bi-bell"></i>
                </div>

                <div class="stat-number">
                    2
                </div>

                <div class="stat-label">
                    Notificaciones nuevas
                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         PROGRESO + ACCIONES
    ====================================================== -->

    <div class="row g-3 mb-4">


        <div class="col-lg-7">

            <div class="dashboard-card">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <div class="card-title">
                            Progreso académico
                        </div>

                        <div class="card-subtitle">
                            Seguimiento de tu formación
                        </div>

                    </div>


                    <span class="status-badge status-success">
                        ● En formación
                    </span>

                </div>


                <div class="mb-4">

                    <div class="d-flex justify-content-between mb-2">

                        <span class="stat-label">
                            Progreso general
                        </span>

                        <strong
                            class="small"
                            style="color:var(--text-white);">

                            68%

                        </strong>

                    </div>


                    <div class="progress">

                        <div
                            class="progress-bar"
                            style="width:68%;">

                        </div>

                    </div>

                </div>


                <div class="row g-4">


                    <div class="col-6">

                        <div class="info-label">
                            Programa
                        </div>

                        <div class="info-value">
                            Análisis y Desarrollo de Software
                        </div>

                    </div>


                    <div class="col-6">

                        <div class="info-label">
                            Fase actual
                        </div>

                        <div class="info-value">
                            Desarrollo
                        </div>

                    </div>


                    <div class="col-6">

                        <div class="info-label">
                            Estado
                        </div>

                        <div class="info-value">
                            Activo
                        </div>

                    </div>


                    <div class="col-6">

                        <div class="info-label">
                            Modalidad
                        </div>

                        <div class="info-value">
                            Formación
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-5">

            <div class="dashboard-card">

                <div class="card-title">
                    Acciones rápidas
                </div>

                <div class="card-subtitle">
                    Accede rápidamente a tus opciones
                </div>


                <div class="quick-grid">


                    <div
                        class="quick-action"
                        onclick="activarTab('attendance-tab')">

                        <i class="bi bi-person-check"></i>

                        <span>
                            Asistencia
                        </span>

                    </div>


                    <div
                        class="quick-action"
                        onclick="activarTab('calendar-tab')">

                        <i class="bi bi-calendar-event"></i>

                        <span>
                            Calendario
                        </span>

                    </div>


                    <div
                        class="quick-action"
                        onclick="activarTab('competencies-tab')">

                        <i class="bi bi-award"></i>

                        <span>
                            Competencias
                        </span>

                    </div>


                    <div
                        class="quick-action"
                        onclick="mostrarMensaje('Documentos')">

                        <i class="bi bi-file-earmark-text"></i>

                        <span>
                            Documentos
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         TABS
    ====================================================== -->

    <ul
        class="nav nav-pills dashboard-tabs"
        id="mainTab"
        role="tablist">


        <li class="nav-item">

            <button
                class="nav-link active"
                id="notes-tab"
                data-bs-toggle="tab"
                data-bs-target="#notes-view"
                type="button"
                role="tab">

                <i class="bi bi-grid me-2"></i>

                Resumen

            </button>

        </li>


        <li class="nav-item">

            <button
                class="nav-link"
                id="calendar-tab"
                data-bs-toggle="tab"
                data-bs-target="#calendar-view"
                type="button"
                role="tab">

                <i class="bi bi-calendar3 me-2"></i>

                Calendario

            </button>

        </li>


        <li class="nav-item">

            <button
                class="nav-link"
                id="attendance-tab"
                data-bs-toggle="tab"
                data-bs-target="#attendance-view"
                type="button"
                role="tab">

                <i class="bi bi-person-check me-2"></i>

                Asistencia y Excusas

            </button>

        </li>


        <li class="nav-item">

            <button
                class="nav-link"
                id="competencies-tab"
                data-bs-toggle="tab"
                data-bs-target="#competencies-view"
                type="button"
                role="tab">

                <i class="bi bi-award me-2"></i>

                Competencias

            </button>

        </li>

    </ul>


    <!-- =====================================================
         CONTENIDO DE TABS
    ====================================================== -->

    <div
        class="tab-content"
        id="mainTabContent">


        <!-- =================================================
             RESUMEN
        ================================================== -->

        <div
            class="tab-pane fade show active"
            id="notes-view"
            role="tabpanel">

            <div class="row g-3">


                <div class="col-lg-7">

                    <div class="dashboard-card">

                        <div class="card-title">
                            Actividad reciente
                        </div>

                        <div class="card-subtitle">
                            Últimos acontecimientos de tu formación
                        </div>


                        <div class="notification-item">

                            <div class="notification-icon green">
                                <i class="bi bi-check-circle"></i>
                            </div>

                            <div>

                                <div class="notification-title">
                                    Excusa aprobada
                                </div>

                                <p class="notification-text">
                                    Tu excusa correspondiente al
                                    20/09 fue validada correctamente.
                                </p>

                            </div>

                        </div>


                        <div class="notification-item">

                            <div class="notification-icon cyan">
                                <i class="bi bi-award"></i>
                            </div>

                            <div>

                                <div class="notification-title">
                                    Nueva competencia evaluada
                                </div>

                                <p class="notification-text">
                                    Se registró una nueva evaluación
                                    en Bases de Datos.
                                </p>

                            </div>

                        </div>


                        <div class="notification-item">

                            <div class="notification-icon yellow">
                                <i class="bi bi-calendar-event"></i>
                            </div>

                            <div>

                                <div class="notification-title">
                                    Sesión sincrónica
                                </div>

                                <p class="notification-text">
                                    La reunión de revisión de sprint
                                    se realizará el viernes a las 8:00 AM.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-lg-5">

                    <div class="dashboard-card">

                        <div class="card-title">
                            Recordatorios
                        </div>

                        <div class="card-subtitle">
                            Actividades pendientes
                        </div>


                        <div class="notification-item">

                            <div class="notification-icon yellow">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>

                            <div>

                                <div class="notification-title">
                                    Subir evidencias
                                </div>

                                <p class="notification-text">
                                    Caso de estudio pendiente.
                                </p>

                            </div>

                        </div>


                        <div class="notification-item">

                            <div class="notification-icon green">
                                <i class="bi bi-check"></i>
                            </div>

                            <div>

                                <div class="notification-title">
                                    Guía 3 revisada
                                </div>

                                <p class="notification-text">
                                    Actividad completada correctamente.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             CALENDARIO
        ================================================== -->

        <div
            class="tab-pane fade"
            id="calendar-view"
            role="tabpanel">

            <div class="dashboard-card">

                <div
                    class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <div class="card-title">
                            Calendario de actividades
                        </div>

                        <div class="card-subtitle">
                            Septiembre 2026
                        </div>

                    </div>


                    <div>

                        <button class="btn-outline-sena">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <button class="btn-outline-sena">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                    </div>

                </div>


                <div class="calendar-grid">

                    <div class="calendar-day-header">Lun</div>
                    <div class="calendar-day-header">Mar</div>
                    <div class="calendar-day-header">Mié</div>
                    <div class="calendar-day-header">Jue</div>
                    <div class="calendar-day-header">Vie</div>
                    <div class="calendar-day-header">Sáb</div>
                    <div class="calendar-day-header">Dom</div>

                    <div class="calendar-day"></div>
                    <div class="calendar-day"></div>

                    <div class="calendar-day">1</div>
                    <div class="calendar-day">2</div>

                    <div class="calendar-day">
                        3
                        <span class="event-badge event-green">
                            Entrega Guía 1
                        </span>
                    </div>

                    <div class="calendar-day">4</div>
                    <div class="calendar-day">5</div>
                    <div class="calendar-day">6</div>
                    <div class="calendar-day">7</div>
                    <div class="calendar-day">8</div>
                    <div class="calendar-day">9</div>

                    <div class="calendar-day">
                        10
                        <span class="event-badge event-yellow">
                            Examen SQL
                        </span>
                    </div>

                    <div class="calendar-day">11</div>
                    <div class="calendar-day">12</div>
                    <div class="calendar-day">13</div>
                    <div class="calendar-day">14</div>
                    <div class="calendar-day">15</div>
                    <div class="calendar-day">16</div>
                    <div class="calendar-day">17</div>
                    <div class="calendar-day">18</div>
                    <div class="calendar-day">19</div>

                    <div class="calendar-day">
                        20
                        <span class="event-badge event-purple">
                            Falta registrada
                        </span>
                    </div>

                    <div class="calendar-day">21</div>
                    <div class="calendar-day">22</div>
                    <div class="calendar-day">23</div>

                    <div class="calendar-day">
                        24
                        <span class="event-badge event-green">
                            Sincronía 8 AM
                        </span>
                    </div>

                    <div class="calendar-day today">25</div>
                    <div class="calendar-day">26</div>
                    <div class="calendar-day">27</div>
                    <div class="calendar-day">28</div>
                    <div class="calendar-day">29</div>
                    <div class="calendar-day">30</div>

                </div>

            </div>

        </div>


        <!-- =================================================
             ASISTENCIA
        ================================================== -->

        <div
            class="tab-pane fade"
            id="attendance-view"
            role="tabpanel">

            <div class="row g-3">


                <div class="col-lg-7">

                    <div class="dashboard-card">

                        <div
                            class="d-flex justify-content-between align-items-start mb-4">

                            <div>

                                <div class="card-title">
                                    Historial de asistencia
                                </div>

                                <div class="card-subtitle">
                                    Registro de tus sesiones académicas
                                </div>

                            </div>

                            <span class="status-badge status-success">
                                92% asistencia
                            </span>

                        </div>


                        <div class="table-responsive">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>Fecha</th>
                                        <th>Sesión</th>
                                        <th>Estado</th>
                                        <th>Soporte</th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <tr>

                                        <td>24/09/2026</td>

                                        <td>
                                            Front-End Avanzado
                                        </td>

                                        <td>

                                            <span class="status-badge status-success">
                                                ● Asistió
                                            </span>

                                        </td>

                                        <td>
                                            N/A
                                        </td>

                                    </tr>


                                    <tr>

                                        <td>20/09/2026</td>

                                        <td>
                                            Bases de Datos MySQL
                                        </td>

                                        <td>

                                            <span class="status-badge status-danger">
                                                ● Inasistencia
                                            </span>

                                        </td>

                                        <td>

                                            <span class="status-badge status-info">
                                                Excusa aprobada
                                            </span>

                                        </td>

                                    </tr>


                                    <tr>

                                        <td>15/09/2026</td>

                                        <td>
                                            Lógica de Programación
                                        </td>

                                        <td>

                                            <span class="status-badge status-success">
                                                ● Asistió
                                            </span>

                                        </td>

                                        <td>
                                            N/A
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


                <div class="col-lg-5">

                    <div class="dashboard-card">

                        <div class="card-title">
                            Adjuntar excusa
                        </div>

                        <div class="card-subtitle">
                            Envía el soporte de tu inasistencia
                        </div>


                        <form id="excuseForm">

                            <div class="mb-3">

                                <label class="form-label">
                                    Fecha de inasistencia
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    required>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Motivo de la falta
                                </label>

                                <select
                                    class="form-select"
                                    required>

                                    <option selected disabled>
                                        Seleccione una opción...
                                    </option>

                                    <option>
                                        Incapacidad médica
                                    </option>

                                    <option>
                                        Calamidad doméstica / laboral
                                    </option>

                                    <option>
                                        Otra razón justificada
                                    </option>

                                </select>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Adjuntar documento
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    accept=".pdf,.png,.jpg,.jpeg"
                                    required>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Observaciones
                                </label>

                                <textarea
                                    class="form-control"
                                    rows="3"
                                    placeholder="Escribe una observación..."></textarea>

                            </div>


                            <button
                                type="submit"
                                class="btn-sena w-100">

                                <i class="bi bi-send me-2"></i>

                                Enviar justificación

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             COMPETENCIAS
        ================================================== -->

        <div class="tab-pane fade" id="competencies-view" role="tabpanel">

    <div class="dashboard-card">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="card-title">
                    Progreso de competencias
                </div>
                <div class="card-subtitle">
                    Consulta el estado de tus competencias
                </div>
            </div>

            <span class="status-badge status-success">
                2 aprobadas
            </span>
        </div>


        <div class="row g-3">

            <!-- 1. COMPETENCIA APROBADA (Alumbra Verde) -->
            <div class="col-md-6 col-lg-3">
                <div class="competency-card card-hover-aprobada">
                    <div class="competency-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    <span class="status-badge status-success">
                        Aprobado
                    </span>

                    <div class="competency-title">
                        Análisis de Requerimientos
                    </div>

                    <div class="competency-description">
                        Fase 1 - Definición y modelado de software.
                    </div>

                    <small class="text-secondary">
                        Resultado:
                        <strong style="color:var(--text-white);">A</strong>
                    </small>
                </div>
            </div>

            <!-- 2. COMPETENCIA APROBADA (Alumbra Verde) -->
            <div class="col-md-6 col-lg-3">
                <div class="competency-card card-hover-aprobada">
                    <div class="competency-icon">
                        <i class="bi bi-database-check"></i>
                    </div>

                    <span class="status-badge status-success">
                        Aprobado
                    </span>

                    <div class="competency-title">
                        Diseño de Bases de Datos
                    </div>

                    <div class="competency-description">
                        Fase 2 - Normalización y modelado ER.
                    </div>

                    <small class="text-secondary">
                        Resultado:
                        <strong style="color:var(--text-white);">A</strong>
                    </small>
                </div>
            </div>

            <!-- 3. COMPETENCIA EN CURSO (Alumbra Amarillo SENA) -->
            <div class="col-md-6 col-lg-3">
                <div class="competency-card card-hover-pendiente">
                    <div class="competency-icon" style="background:rgba(253,195,0,.12); color:var(--sena-yellow);">
                        <i class="bi bi-clock"></i>
                    </div>

                    <span class="status-badge status-warning">
                        En curso
                    </span>

                    <div class="competency-title">
                        Desarrollo Web Front-End
                    </div>

                    <div class="competency-description">
                        Fase 3 - Maquetación, HTML, CSS y JavaScript.
                    </div>

                    <small class="text-secondary">
                        Resultado:
                        <strong style="color:var(--text-white);">Pendiente</strong>
                    </small>
                </div>
            </div>

            <!-- 4. COMPETENCIA POR VER (Alumbra Violeta SENA) -->
            <div class="col-md-6 col-lg-3">
                <div class="competency-card card-hover-sin-iniciar" style="opacity:.65;">
                    <div class="competency-icon" style="background:rgba(113,39,122,.16); color:var(--sena-purple);">
                        <i class="bi bi-dash"></i>
                    </div>

                    <span class="status-badge status-danger">
                        Por ver
                    </span>

                    <div class="competency-title">
                        Pruebas e Implantación
                    </div>

                    <div class="competency-description">
                        Fase 4 - Despliegue y pruebas unitarias.
                    </div>

                    <small class="text-secondary">
                        Resultado:
                        <strong style="color:var(--text-white);">Sin iniciar</strong>
                    </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>


<!-- =========================================================
     BOOTSTRAP JS
========================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>

    /* =====================================================
       EXPANDIR / COLAPSAR SIDEBAR
    ====================================================== */

    function toggleSidebar() {

        const sidebar =
            document.getElementById('sidebar');

        const mainContent =
            document.getElementById('main-content');


        sidebar.classList.toggle('collapsed');

        mainContent.classList.toggle('expanded');

    }


    /* =====================================================
       CAMBIAR ENTRE SECCIONES
    ====================================================== */

    function activarTab(tabId, botonSidebar = null) {

        const tab =
            document.getElementById(tabId);


        if (!tab) {
            return;
        }


        const bootstrapTab =
            new bootstrap.Tab(tab);


        bootstrapTab.show();


        document
            .querySelectorAll('.sidebar-nav .sidebar-btn')
            .forEach(function(btn) {

                btn.classList.remove('active');

            });


        if (botonSidebar) {

            botonSidebar.classList.add('active');

        }

        else {

            const botones =
                document.querySelectorAll(
                    '.sidebar-nav .sidebar-btn'
                );


            if (tabId === 'notes-tab') {
                botones[0].classList.add('active');
            }

            else if (tabId === 'calendar-tab') {
                botones[1].classList.add('active');
            }

            else if (tabId === 'attendance-tab') {
                botones[2].classList.add('active');
            }

            else if (tabId === 'competencies-tab') {
                botones[3].classList.add('active');
            }

        }

    }


    /* =====================================================
       SINCRONIZAR TABS CON SIDEBAR
    ====================================================== */

    document
        .querySelectorAll('#mainTab .nav-link')
        .forEach(function(tab) {

            tab.addEventListener(
                'shown.bs.tab',
                function() {

                    const tabId =
                        this.id;


                    const botones =
                        document.querySelectorAll(
                            '.sidebar-nav .sidebar-btn'
                        );


                    botones.forEach(function(btn) {

                        btn.classList.remove('active');

                    });


                    if (tabId === 'notes-tab') {
                        botones[0].classList.add('active');
                    }

                    if (tabId === 'calendar-tab') {
                        botones[1].classList.add('active');
                    }

                    if (tabId === 'attendance-tab') {
                        botones[2].classList.add('active');
                    }

                    if (tabId === 'competencies-tab') {
                        botones[3].classList.add('active');
                    }

                }
            );

        });


    /* =====================================================
       FORMULARIO DE EXCUSA
    ====================================================== */

    document
        .getElementById('excuseForm')
        .addEventListener('submit', function(e) {

            e.preventDefault();

            alert(
                '¡Excusa y soporte enviados correctamente para revisión del instructor!'
            );

            this.reset();

        });


    /* =====================================================
       OPCIONES FUTURAS
    ====================================================== */

    function mostrarMensaje(nombre) {

        alert(
            nombre +
            ' estará disponible próximamente.'
        );

    }


    /* =====================================================
       CERRAR SESIÓN
    ====================================================== */

    function cerrarSesion() {

        if (
            confirm(
                '¿Estás segura de que deseas cerrar sesión?'
            )
        ) {

            alert(
                'Sesión cerrada correctamente'
            );

        }

    }

    /* =====================================================
   MODO CLARO / OSCURO
===================================================== */

function toggleTheme() {

    const html = document.documentElement;

    const icon = document.getElementById('theme-icon');

    const currentTheme =
        html.getAttribute('data-theme');


    if (currentTheme === 'light') {

        html.setAttribute('data-theme', 'dark');

        icon.classList.remove('bi-moon-fill');

        icon.classList.add('bi-sun-fill');

        localStorage.setItem('theme', 'dark');

    }

    else {

        html.setAttribute('data-theme', 'light');

        icon.classList.remove('bi-sun-fill');

        icon.classList.add('bi-moon-fill');

        localStorage.setItem('theme', 'light');

    }

}


/* =====================================================
   CARGAR TEMA GUARDADO
===================================================== */

document.addEventListener('DOMContentLoaded', function() {

    const savedTheme =
        localStorage.getItem('theme') || 'dark';

    const html =
        document.documentElement;

    const icon =
        document.getElementById('theme-icon');


    html.setAttribute(
        'data-theme',
        savedTheme
    );


    if (savedTheme === 'light') {

        icon.classList.remove('bi-sun-fill');

        icon.classList.add('bi-moon-fill');

    }

    else {

        icon.classList.remove('bi-moon-fill');

        icon.classList.add('bi-sun-fill');

    }

});

</script>


</body>

</html>

