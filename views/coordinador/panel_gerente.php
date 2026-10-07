<?php
/**
 * Vista integrada desde el proyecto 'controlasistencia'.
 * Protegida con la sesión y los roles del sistema sena_asistencia.
 */
require_once __DIR__ . '/../../includes/sesion.php';
exigirRol(['coordinador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema Integrado de Control, Inventario y Personal - SENA</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
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
      --border-dark: rgba(57,169,0,.20);
      --border-focus: rgba(57,169,0,.60);
      --text-white: #FFFFFF;
      --text-light: #F2F5F3;
      --text-muted: #A7B2AD;
      --text-secondary: #7F8D87;
      --primary: #39A900;
      --primary-hover: #007832;
    }

    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      min-height: 100vh;
      background: var(--bg-dark);
      color: var(--text-light);
      font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    button, input, select, textarea { font-family: inherit; }
    ::-webkit-scrollbar { width: 7px; height: 7px; }
    ::-webkit-scrollbar-track { background: var(--bg-dark); }
    ::-webkit-scrollbar-thumb { background: var(--sena-green-dark); border-radius: 20px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--sena-green); }

    /* Menú lateral */
    .sidebar {
      position: fixed; top: 20px; left: 20px; bottom: 20px;
      width: 260px; padding: 18px 14px;
      display: flex; flex-direction: column;
      background: var(--card-dark); border: 1px solid var(--border-dark);
      border-radius: 28px; z-index: 1000; overflow: hidden;
      box-shadow: 0 20px 50px rgba(0,0,0,.25);
      transition: width .3s cubic-bezier(.4,0,.2,1);
    }
    .sidebar.collapsed { width: 82px; align-items: center; }
    .sidebar-toggle-btn {
      position: absolute; top: 18px; right: 14px;
      width: 28px; height: 28px; border-radius: 50%;
      background: var(--input-bg); border: 1px solid var(--border-dark);
      color: var(--text-muted); display: flex; align-items: center;
      justify-content: center; cursor: pointer; z-index: 10;
      transition: all .25s ease;
    }
    .sidebar-toggle-btn:hover { background: var(--sena-green); color: white; }
    .sidebar.collapsed .sidebar-toggle-btn {
      position: relative; top: 0; right: 0; margin-bottom: 15px;
      transform: rotate(180deg);
    }
    .sidebar-header { display:flex; align-items:center; gap:12px; margin: 8px 0 30px; width:100%; }
    .sidebar-logo {
      min-width:48px; width:48px; height:48px; border-radius:16px;
      background:var(--sena-green); color:white; display:flex;
      align-items:center; justify-content:center; font-size:21px;
      box-shadow:0 8px 20px rgba(57,169,0,.2);
    }
    .sidebar-brand-text { display:flex; flex-direction:column; white-space:nowrap; }
    .brand-title { color:var(--text-white); font-weight:800; font-size:15px; letter-spacing:.5px; }
    .brand-subtitle { color:var(--sena-cyan); font-weight:700; font-size:9px; letter-spacing:.8px; }
    .sidebar.collapsed .sidebar-brand-text,
    .sidebar.collapsed .sidebar-section-label,
    .sidebar.collapsed .sidebar-btn span,
    .sidebar.collapsed .user-details,
    .sidebar.collapsed .sidebar-footer-note span { display:none; }
    .sidebar-section-label { color:var(--text-secondary); font-size:9px; font-weight:700; letter-spacing:1.2px; padding:0 12px; margin-bottom:10px; white-space:nowrap; }
    .sidebar-nav { width:100%; display:flex; flex-direction:column; gap:8px; }
    .sidebar-btn {
      width:100%; min-height:48px; border:0; border-radius:16px;
      background:transparent; color:var(--text-muted);
      display:flex; align-items:center; padding:0 14px; gap:14px;
      font-size:12px; font-weight:600; cursor:pointer;
      transition:all .25s ease; white-space:nowrap; text-align:left;
    }
    .sidebar-btn i { font-size:17px; min-width:20px; text-align:center; }
    .sidebar-btn:hover { background:var(--input-bg); color:var(--text-white); transform:translateY(-1px); }
    .sidebar-btn.active { background:var(--sena-green); color:white; box-shadow:0 8px 20px rgba(57,169,0,.25); }
    .sidebar.collapsed .sidebar-btn { width:48px; justify-content:center; padding:0; }
    .sidebar-bottom { margin-top:auto; width:100%; display:flex; flex-direction:column; gap:12px; padding-top:15px; border-top:1px solid var(--border-dark); }
    .user-profile-card { display:flex; align-items:center; gap:12px; padding:8px; border-radius:16px; background:rgba(23,34,31,.7); min-width:0; }
    .sidebar-avatar { min-width:40px; width:40px; height:40px; border-radius:50%; background:var(--sena-purple); color:white; display:flex; align-items:center; justify-content:center; font-size:15px; }
    .user-details { display:flex; flex-direction:column; overflow:hidden; white-space:nowrap; }
    .user-details .name { color:var(--text-white); font-size:11px; font-weight:700; }
    .user-details .role { color:var(--text-secondary); font-size:10px; }
    .sidebar.collapsed .user-profile-card { background:transparent; padding:0; justify-content:center; }
    .sidebar-footer-note { color:var(--text-secondary); font-size:10px; padding:0 10px; display:flex; align-items:center; gap:9px; white-space:nowrap; }

    /* Contenido y encabezado */
    .main-content { margin-left:300px; padding:28px 30px 40px; min-height:100vh; transition:margin-left .3s cubic-bezier(.4,0,.2,1); }
    .main-content.expanded { margin-left:124px; }
    .dashboard-header { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:25px; }
    /* Notificaciones independientes del contenido principal */
    .notification-area { position:relative; }
    .notification-trigger {
      width:42px; height:42px; border:1px solid var(--border-dark);
      border-radius:13px; background:var(--card-dark); color:var(--text-muted);
      display:flex; align-items:center; justify-content:center; cursor:pointer;
      position:relative; transition:.25s;
    }
    .notification-trigger:hover { color:var(--sena-green); border-color:var(--border-focus); background:var(--input-bg); }
    .notification-trigger .notification-count {
      position:absolute; top:-5px; right:-5px; min-width:18px; height:18px;
      padding:0 5px; border-radius:20px; background:#ff4d4d; color:#fff;
      font-size:9px; font-weight:800; display:flex; align-items:center; justify-content:center;
      border:2px solid var(--bg-dark);
    }
    .notification-panel {
      position:absolute; right:0; top:52px; width:360px; max-width:calc(100vw - 30px);
      background:var(--card-dark); border:1px solid var(--border-dark);
      border-radius:18px; padding:15px; z-index:1100;
      box-shadow:0 20px 50px rgba(0,0,0,.35); display:none;
    }
    .notification-panel.show { display:block; animation:notificationIn .18s ease-out; }
    .notification-panel-header { display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid var(--border-dark); margin-bottom:7px; }
    .notification-panel-header strong { color:var(--text-white); font-size:13px; }
    .notification-panel-header button { background:transparent; border:0; color:var(--sena-green); font-size:10px; font-weight:700; cursor:pointer; }
    .notification-list { max-height:330px; overflow-y:auto; }
    .notification-panel .notification-item { padding:11px !important; margin:7px 0; }
    .notification-panel .notification-item strong { font-size:10px; }
    .notification-panel .notification-item small { font-size:9px; line-height:1.4; }
    @keyframes notificationIn { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:translateY(0); } }
    #mainTab { display:none !important; }
    .eyebrow { color:var(--sena-cyan); font-size:9px; font-weight:800; letter-spacing:1.4px; margin:0 0 7px; }
    .welcome-title { margin:0; color:var(--text-white); font-size:27px; font-weight:750; }
    .welcome-title span { color:var(--sena-green); }
    .welcome-subtitle { margin:6px 0 0; color:var(--text-muted); font-size:12px; }
    .user-area { display:flex; align-items:center; gap:11px; padding:9px 13px; background:var(--card-dark); border:1px solid var(--border-dark); border-radius:16px; }
    .header-user-icon { width:38px; height:38px; border-radius:12px; display:grid; place-items:center; background:rgba(57,169,0,.14); color:var(--sena-green); }
    .header-user-copy { display:flex; flex-direction:column; white-space:nowrap; }
    .header-user-copy strong { color:var(--text-white); font-size:11px; }
    .header-user-copy small { color:var(--text-secondary); font-size:9px; }

    /* Tarjetas, notificaciones, tablas y formularios */
    .card-custom, .modal-content {
      background:var(--card-dark); color:var(--text-light);
      border:1px solid var(--border-dark); border-radius:22px;
      box-shadow:0 12px 35px rgba(0,0,0,.16);
    }
    .card-custom { padding:22px !important; }
    .card-custom h5, .card-custom h6, .modal-title { color:var(--text-white) !important; }
    .card-custom p, .card-custom small { color:var(--text-muted) !important; }
    .env-card { transition:transform .2s, box-shadow .2s; border-top:3px solid var(--sena-green) !important; }
    .env-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,.25); }
    .notification-item { border-left:3px solid var(--sena-green); background:var(--input-bg) !important; border-radius:12px; }
    .notification-item:hover { background:#1c2b25 !important; }
    .notification-item strong { color:var(--text-white) !important; }
    .notification-item small { color:var(--text-muted) !important; }
    .nav-tabs { border:0; gap:7px; padding:7px !important; background:var(--card-dark) !important; border:1px solid var(--border-dark) !important; border-radius:18px !important; overflow-x:auto; flex-wrap:nowrap; }
    .nav-tabs .nav-link { color:var(--text-muted); background:transparent; border:0 !important; border-radius:13px; padding:10px 14px; font-size:11px; font-weight:650; white-space:nowrap; }
    .nav-tabs .nav-link:hover { background:var(--input-bg); color:var(--text-white); }
    .nav-tabs .nav-link.active { color:white; background:var(--sena-green); }
    .table { --bs-table-bg:transparent; --bs-table-color:var(--text-light); --bs-table-border-color:rgba(57,169,0,.12); margin-bottom:0; }
    .table thead th { color:var(--text-secondary) !important; background:var(--input-bg) !important; border-color:var(--border-dark) !important; padding:13px; font-size:10px; text-transform:uppercase; }
    .table tbody td { color:var(--text-muted); border-color:rgba(57,169,0,.10); padding:13px; font-size:11px; }
    .table tbody tr:hover { background:rgba(57,169,0,.05); }
    .table-light, .table-dark { --bs-table-bg:var(--input-bg); --bs-table-color:var(--text-light); --bs-table-border-color:var(--border-dark); }
    .form-label { color:var(--text-muted); font-size:11px; font-weight:650; }
    .form-control, .form-select {
      background:var(--input-bg); color:var(--text-light);
      border:1px solid var(--border-dark); border-radius:12px;
      padding:10px 12px; font-size:11px;
    }
    .form-control:focus, .form-select:focus { background:var(--input-bg); color:white; border-color:var(--border-focus); box-shadow:0 0 0 3px rgba(57,169,0,.09); }
    .form-control::placeholder { color:var(--text-secondary); }
    .form-select option { background:var(--input-bg); color:white; }
    .btn-success, .btn-sena { background:var(--sena-green) !important; border-color:var(--sena-green) !important; color:white !important; border-radius:11px; }
    .btn-success:hover, .btn-sena:hover { background:var(--sena-green-dark) !important; border-color:var(--sena-green-dark) !important; }
    .btn-outline-success { color:var(--sena-green); border-color:var(--sena-green); border-radius:10px; }
    .btn-outline-success:hover { background:var(--sena-green); color:white; }
    .btn-outline-secondary { color:var(--text-muted); border-color:var(--border-dark); border-radius:10px; }
    .btn-outline-secondary:hover { background:var(--input-bg); color:white; }
    .modal-header, .modal-footer { border-color:var(--border-dark) !important; }
    .modal-header.bg-light { background:var(--input-bg) !important; }
    .modal-body { color:var(--text-light); }
    .btn-close { filter:invert(1) grayscale(100%) brightness(200%); }
    .text-dark { color:var(--text-white) !important; }
    .text-secondary, .text-muted { color:var(--text-muted) !important; }
    .bg-white, .bg-light { background:var(--input-bg) !important; }
    .border { border-color:var(--border-dark) !important; }
    .shadow-sm { box-shadow:0 8px 22px rgba(0,0,0,.16) !important; }
    .badge-sena { background:var(--sena-green); color:white; }
    .status-disponible { background:rgba(57,169,0,.14); color:#8cdb69; border:1px solid rgba(57,169,0,.25); }
    .status-prestado { background:rgba(253,195,0,.12); color:var(--sena-yellow); border:1px solid rgba(253,195,0,.25); }
    .status-mantenimiento { background:rgba(255,77,77,.12); color:#ff8585; border:1px solid rgba(255,77,77,.25); }
    .placa-badge { font-family:monospace; font-weight:bold; letter-spacing:.5px; background:rgba(80,229,249,.10); color:var(--sena-cyan); padding:4px 8px; border-radius:7px; }
    .calendar-day-header { background:var(--input-bg); color:var(--sena-cyan); text-align:center; padding:9px; font-weight:700; border:1px solid var(--border-dark); border-radius:9px; font-size:10px; }
    .calendar-cell { min-height:90px; background:var(--input-bg); color:var(--text-muted); border:1px solid var(--border-dark); border-radius:10px; padding:7px; }
    .event-badge { font-size:.68rem; padding:3px 6px; border-radius:5px; margin-top:4px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .instructor-card { cursor:pointer; transition:all .2s; }
    .instructor-card:hover { border-color:var(--sena-green) !important; background:#1b2922 !important; transform:translateY(-2px); }
    .text-success { color:var(--sena-green) !important; }
    .text-primary { color:var(--sena-cyan) !important; }
    .text-warning { color:var(--sena-yellow) !important; }

    @media (max-width: 900px) {
      .sidebar { top:10px; left:10px; bottom:10px; width:82px; padding:15px 10px; }
      .sidebar:not(.mobile-open) { width:82px; }
      .sidebar:not(.mobile-open) .sidebar-brand-text,
      .sidebar:not(.mobile-open) .sidebar-section-label,
      .sidebar:not(.mobile-open) .sidebar-btn span,
      .sidebar:not(.mobile-open) .user-details,
      .sidebar:not(.mobile-open) .sidebar-footer-note span { display:none; }
      .sidebar:not(.mobile-open) .sidebar-btn { width:48px; justify-content:center; padding:0; }
      .sidebar:not(.mobile-open) .user-profile-card { justify-content:center; padding:0; background:transparent; }
      .main-content, .main-content.expanded { margin-left:102px; padding:20px 16px; }
      .dashboard-header { align-items:flex-start; }
      .user-area { display:none; }
    }
    @media (max-width: 560px) {
      .sidebar { width:64px; left:7px; padding:12px 7px; }
      .sidebar-logo { min-width:42px; width:42px; height:42px; }
      .sidebar-toggle-btn { right:7px; }
      .main-content, .main-content.expanded { margin-left:78px; padding:18px 10px; }
      .welcome-title { font-size:20px; }
      .welcome-subtitle { font-size:10px; }
      .card-custom { padding:15px !important; }
      .nav-tabs .nav-link { padding:9px 10px; }
    }
  </style>
</head>
<body>

  <!-- MENÚ LATERAL -->
  <aside class="sidebar" id="sidebar">
    <button class="sidebar-toggle-btn" id="toggle-btn" type="button"
            title="Expandir / contraer menú" onclick="toggleSidebar()">
      <i class="fa-solid fa-chevron-left"></i>
    </button>

    <div class="sidebar-header">
      <div class="sidebar-logo"><i class="fa-solid fa-boxes-stacked"></i></div>
      <div class="sidebar-brand-text">
        <span class="brand-title">SENA CONTROL</span>
        <span class="brand-subtitle">GESTIÓN INSTITUCIONAL</span>
      </div>
    </div>

    <div class="sidebar-section-label">MENÚ PRINCIPAL</div>
    <nav class="sidebar-nav">
      <button class="sidebar-btn active" type="button" title="Inventario general"
              onclick="activarSeccion('inventario-tab', this)">
        <i class="fa-solid fa-boxes-stacked"></i><span>Inventario general</span>
      </button>
      <button class="sidebar-btn" type="button" title="Ambientes y préstamos"
              onclick="activarSeccion('ambientes-tab', this)">
        <i class="fa-solid fa-door-open"></i><span>Ambientes y préstamos</span>
      </button>
      <button class="sidebar-btn" type="button" title="Historial de movimientos"
              onclick="activarSeccion('historico-tab', this)">
        <i class="fa-solid fa-clock-rotate-left"></i><span>Historial de movimientos</span>
      </button>
      <button class="sidebar-btn" type="button" title="Calendario de eventos"
              onclick="activarSeccion('calendario-tab', this)">
        <i class="fa-solid fa-calendar-days"></i><span>Calendario de eventos</span>
      </button>
      <button class="sidebar-btn" type="button" title="Personal e instructores"
              onclick="activarSeccion('personal-tab', this)">
        <i class="fa-solid fa-users"></i><span>Personal e instructores</span>
      </button>
      <button class="sidebar-btn" type="button" title="Expediente del aprendiz"
              onclick="activarSeccion('expediente-tab', this)">
        <i class="fa-solid fa-folder-open"></i><span>Expediente del aprendiz</span>
      </button>
      <button class="sidebar-btn" type="button" title="Asignación de fichas" onclick="activarSeccion('asignaciones-tab', this)">
        <i class="fa-solid fa-user-shield"></i><span>Asignación de fichas</span>
      </button>
      <button class="sidebar-btn" type="button" title="Fichas y aprendices" onclick="activarSeccion('fichas-tab', this)">
        <i class="fa-solid fa-users-viewfinder"></i><span>Fichas y aprendices</span>
      </button>
    </nav>

    <div class="sidebar-bottom">
      <div class="user-profile-card">
        <div class="sidebar-avatar"><i class="fa-solid fa-user-shield"></i></div>
        <div class="user-details">
          <span class="name">Administrador</span>
          <span class="role">Cuentadante SENA</span>
        </div>
      </div>
      <div class="sidebar-footer-note"><i class="fa-solid fa-shield-halved"></i><span>Panel administrativo</span></div>
    </div>
  </aside>

  <main class="main-content" id="main-content">
    <header class="dashboard-header">
      <div>
        <p class="eyebrow">CENTRO DE FORMACIÓN · SENA</p>
        <h1 class="welcome-title">Sistema de <span>Control e Inventario</span></h1>
        <p class="welcome-subtitle">Gestión de activos, ambientes, personal y asistencia.</p>
      </div>
      <div class="user-area">
        <div class="notification-area">
          <button class="notification-trigger" id="notificationTrigger" type="button"
                  onclick="toggleNotifications()" title="Notificaciones">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-count" id="notificationHeaderCount">3</span>
          </button>
          <div class="notification-panel" id="notificationPanel">
            <div class="notification-panel-header">
              <strong><i class="fa-solid fa-bell me-2"></i>Notificaciones</strong>
              <button type="button" onclick="clearNotifications()">Marcar como leídas</button>
            </div>
            <div class="notification-list" id="notificationsContainer">
              <div class="p-2 rounded notification-item shadow-sm d-flex align-items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation text-warning fs-5 mt-1"></i>
                <div><strong class="d-block text-dark">Coordinación Académica - Bloque B</strong><small class="text-muted d-block">Mantenimiento preventivo programado en el Ambiente 204 para mañana 8:00 AM.</small><span class="badge bg-light text-secondary mt-1">Hace 15 mins</span></div>
              </div>
              <div class="p-2 rounded notification-item shadow-sm d-flex align-items-start gap-2">
                <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                <div><strong class="d-block text-dark">Portería Principal / Control Ingreso</strong><small class="text-muted d-block">Instructor Carlos Pérez registró ingreso a las 06:45 AM.</small><span class="badge bg-light text-secondary mt-1">Hace 1 hora</span></div>
              </div>
              <div class="p-2 rounded notification-item shadow-sm d-flex align-items-start gap-2">
                <i class="fa-solid fa-arrows-rotate text-info fs-5 mt-1"></i>
                <div><strong class="d-block text-dark">Solicitud de Préstamo</strong><small class="text-muted d-block">Dra. María López solicitó reserva del Ambiente 101 para evento ADSO.</small><span class="badge bg-light text-secondary mt-1">Hace 2 horas</span></div>
              </div>
            </div>
          </div>
        </div>
        <div class="header-user-icon"><i class="fa-solid fa-user-shield"></i></div>
        <div class="header-user-copy">
          <strong>Administrador Cuentadante</strong>
          <small>Panel de administración</small>
        </div>
      </div>
    </header>

  <!-- Container Principal -->
  <div class="container-fluid py-4 px-4">

    <!-- Pestañas Principales de Navegación -->
    <ul class="nav nav-tabs mb-4 dashboard-tabs" id="mainTab" role="tablist">
      <li class="nav-item">
        <button class="nav-link active fs-6" id="inventario-tab" data-bs-toggle="tab" data-bs-target="#inventario-pane">
          <i class="fa-solid fa-list-check me-2"></i>1. Inventario General (<span id="total-items-badge">0</span>)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link fs-6" id="ambientes-tab" data-bs-toggle="tab" data-bs-target="#ambientes-pane">
          <i class="fa-solid fa-door-open me-2"></i>2. Ambientes y Préstamos
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link fs-6" id="historico-tab" data-bs-toggle="tab" data-bs-target="#historico-pane">
          <i class="fa-solid fa-clock-rotate-left me-2"></i>3. Registro de Salidas / Cambios
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link fs-6" id="calendario-tab" data-bs-toggle="tab" data-bs-target="#calendario-pane">
          <i class="fa-solid fa-calendar-days me-2"></i>4. Calendario de Eventos
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link fs-6" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal-pane">
          <i class="fa-solid fa-users me-2"></i>5. Personal SENA y Horarios
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link fs-6" id="expediente-tab" data-bs-toggle="tab" data-bs-target="#expediente-pane">
          <i class="fa-solid fa-folder-open me-2"></i>6. Expediente del Aprendiz
        </button>
      </li>
      <li class="nav-item"><button class="nav-link fs-6" id="asignaciones-tab" data-bs-toggle="tab" data-bs-target="#asignaciones-pane"><i class="fa-solid fa-user-shield me-2"></i>7. Asignación de Fichas</button></li>
      <li class="nav-item"><button class="nav-link fs-6" id="fichas-tab" data-bs-toggle="tab" data-bs-target="#fichas-pane"><i class="fa-solid fa-users-viewfinder me-2"></i>8. Fichas y Aprendices</button></li>
    </ul>

    <div class="tab-content" id="mainTabContent">

      <!-- VISTA 1: INVENTARIO GENERAL COMPLETO (CON O SIN AMBIENTE + VIDA ÚTIL) -->
      <div class="tab-pane fade show active" id="inventario-pane">
        <div class="card card-custom p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-boxes-stacked text-success me-2"></i>Lista Completa de Activos del SENA</h5>
            <small class="text-muted">Se muestran todos los activos, asignados a ambientes o almacenados en Bodega.</small>
          </div>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalCargaInventario">
              <i class="fa-solid fa-file-arrow-up me-2"></i>Carga masiva de inventario
            </button>
            <button class="btn btn-outline-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoInventario">
              <i class="fa-solid fa-plus me-2"></i>Agregar elemento
            </button>
            <button class="btn btn-outline-secondary fw-bold" onclick="descargarPlantillaInventario()">
              <i class="fa-solid fa-file-excel me-2"></i>Plantilla inventario
            </button>
          </div>

          <div class="row g-3 mb-4 align-items-center">
            <div class="col-md-3">
              <input type="text" id="searchInventory" class="form-control" placeholder="Buscar Placa, Nombre o Elemento...">
            </div>
            <div class="col-md-3">
              <select id="filterEnvironment" class="form-select">
                <option value="">Todos los Ambientes / Ubicaciones</option>
                <option value="SIN_ASIGNAR">Sin Asignar (Bodega Principal)</option>
              </select>
            </div>
            <div class="col-md-3">
              <select id="filterVidaUtil" class="form-select">
                <option value="">Todas las Vidas Útiles</option>
                <option value="En Vida Útil">En Vida Útil</option>
                <option value="Vida Útil Agotada">Vida Útil Agotada</option>
                <option value="En Mantenimiento">En Mantenimiento</option>
                <option value="Dado de Baja">Dado de Baja</option>
              </select>
            </div>
            <div class="col-md-3 text-end">
              <button class="btn btn-outline-secondary w-100" onclick="resetInventoryFilters()">
                <i class="fa-solid fa-rotate-left me-1"></i> Limpiar Filtros
              </button>
            </div>
          </div>

          <!-- Tabla Inventario General -->
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Placa / Código SENA</th>
                  <th>Elemento</th>
                  <th>Categoría</th>
                  <th>Ubicación Actual</th>
                  <th>Vida Útil / Estado</th>
                  <th>Instructor Responsable</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody id="inventoryTableBody">
                <!-- JavaScript llena esta tabla -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- VISTA 2: GESTIÓN DE AMBIENTES Y PRÉSTAMOS -->
      <div class="tab-pane fade" id="ambientes-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-building-user me-2"></i>Ambientes de Formación Registrados</h5>
          <button class="btn btn-success fw-bold px-4" data-bs-toggle="modal" data-bs-target="#modalNuevoAmbiente" style="background-color: var(--sena-green); border: none;">
            <i class="fa-solid fa-plus me-2"></i> Crear Nuevo Ambiente
          </button>
        </div>

        <div class="row g-4" id="environmentsGrid">
          <!-- Tarjetas dinámicas cargadas por JS -->
        </div>
      </div>

      <!-- VISTA 3: HISTÓRICO Y REGISTRO DE SALIDAS/CAMBIOS -->
      <div class="tab-pane fade" id="historico-pane">
        <div class="card card-custom p-4">
          <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-file-signature text-primary me-2"></i>Historial de Salidas y Movimientos de Elementos</h5>
          <p class="text-muted small">Muestra el registro de todos los elementos retirados de un ambiente, devueltos a bodega o movidos de ubicación.</p>

          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead class="table-dark">
                <tr>
                  <th>Fecha y Hora</th>
                  <th>Placa SENA</th>
                  <th>Elemento</th>
                  <th>Origen (Ambiente de Salida)</th>
                  <th>Destino Actual</th>
                  <th>Motivo / Novedad Registrada</th>
                </tr>
              </thead>
              <tbody id="historicoTableBody">
                <!-- Se llena dinámicamente -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- VISTA 4: CALENDARIO DE EVENTOS INSTITUCIONALES -->
      <div class="tab-pane fade" id="calendario-pane">
        <div class="card card-custom p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-calendar-check text-success me-2"></i>Programación y Eventos de Ambientes</h5>
            <button class="btn btn-outline-success btn-sm fw-bold" onclick="addNewEvent()">
              <i class="fa-solid fa-plus me-1"></i> Agendar Evento
            </button>
          </div>

          <div class="row g-2 mb-2 text-center">
            <div class="col"><div class="calendar-day-header">Lunes</div></div>
            <div class="col"><div class="calendar-day-header">Martes</div></div>
            <div class="col"><div class="calendar-day-header">Miércoles</div></div>
            <div class="col"><div class="calendar-day-header">Jueves</div></div>
            <div class="col"><div class="calendar-day-header">Viernes</div></div>
            <div class="col"><div class="calendar-day-header">Sábado</div></div>
          </div>

          <!-- Días del Calendario Grid -->
          <div class="row g-2 text-dark" id="calendarGrid">
            <!-- Cargado por JS -->
          </div>
        </div>
      </div>

      <!-- VISTA 5: PERSONAL SENA Y CONTROL DE ASISTENCIA / HORARIOS -->
      <div class="tab-pane fade" id="personal-pane">
        <div class="card card-custom p-4">
          <h5 class="fw-bold mb-2 text-dark"><i class="fa-solid fa-address-book text-primary me-2"></i>Directorio de Personal e Instructores SENA</h5>
          <p class="text-muted small mb-3">Haz clic en cualquier persona para desplegar el historial detallado de días asistidos, hora de ingreso y salida.</p>

          <div class="d-flex flex-wrap gap-2 mb-4">
            <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalCargaPersonal">
              <i class="fa-solid fa-file-arrow-up me-2"></i>Carga masiva de personal
            </button>
            <button class="btn btn-outline-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoPersonal">
              <i class="fa-solid fa-user-plus me-2"></i>Agregar personal
            </button>
            <button class="btn btn-outline-secondary fw-bold" onclick="descargarPlantillaPersonal()">
              <i class="fa-solid fa-file-excel me-2"></i>Plantilla personal
            </button>
          </div>

          <div class="row g-3" id="personalGrid">
            <!-- Cargado por JS -->
          </div>
        </div>
      </div>

      <!-- VISTA 6: EXPEDIENTE DEL APRENDIZ -->
      <div class="tab-pane fade" id="expediente-pane">
        <div class="card card-custom p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
              <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-folder-open text-success me-2"></i>Expediente del Aprendiz</h5>
              <small class="text-muted">Consulta la información académica, personal y las novedades del aprendiz.</small>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-success fw-bold" onclick="imprimirExpediente()"><i class="fa-solid fa-print me-1"></i>Imprimir expediente</button>
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-lg-7">
              <label class="form-label fw-semibold">Buscar aprendiz</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="searchAprendiz" class="form-control" placeholder="Nombre, documento o ficha..." oninput="renderAprendicesExpediente()">
              </div>
            </div>
            <div class="col-lg-5">
              <label class="form-label fw-semibold">Aprendiz seleccionado</label>
              <select id="selectAprendizExpediente" class="form-select" onchange="mostrarExpedienteAprendiz(this.value)">
                <option value="">Seleccione un aprendiz</option>
              </select>
            </div>
          </div>

          <div id="aprendizExpedienteEmpty" class="text-center py-5 border rounded-4 bg-light">
            <i class="fa-solid fa-folder-open fs-1 text-success mb-3"></i>
            <h6 class="fw-bold">Seleccione un aprendiz</h6>
            <p class="text-muted mb-0">Aquí se mostrará su expediente completo.</p>
          </div>

          <div id="aprendizExpedienteContenido" style="display:none;">
            <div class="row g-3 mb-3">
              <div class="col-xl-4">
                <div class="border rounded-4 p-4 h-100 bg-light">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px;">
                      <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div>
                      <h5 id="expNombre" class="fw-bold mb-1"></h5>
                      <span id="expEstado" class="badge bg-success"></span>
                    </div>
                  </div>
                  <div class="small text-muted">Documento</div><div id="expDocumento" class="fw-semibold mb-2"></div>
                  <div class="small text-muted">Ficha</div><div id="expFicha" class="fw-semibold mb-2"></div>
                  <div class="small text-muted">Ficha relacionada</div><div id="expFichaRelacion" class="fw-semibold mb-2"></div>
                  <div class="small text-muted">Ambiente asignado</div><div id="expAmbiente" class="fw-semibold text-success mb-2">Sin ambiente asignado</div>
                  <div class="small text-muted">Programa</div><div id="expPrograma" class="fw-semibold"></div>
                </div>
              </div>
              <div class="col-xl-8">
                <div class="row g-3">
                  <div class="col-md-4"><div class="border rounded-4 p-3 h-100"><small class="text-muted">Asistencia</small><h4 id="expAsistencia" class="fw-bold text-success mb-0"></h4></div></div>
                  <div class="col-md-4"><div class="border rounded-4 p-3 h-100"><small class="text-muted">Promedio</small><h4 id="expPromedio" class="fw-bold text-primary mb-0"></h4></div></div>
                  <div class="col-md-4"><div class="border rounded-4 p-3 h-100"><small class="text-muted">Fallas</small><h4 id="expFallas" class="fw-bold text-danger mb-0"></h4></div></div>
                  <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><small class="text-muted">Jornada</small><div id="expJornada" class="fw-semibold mt-1"></div></div></div>
                  <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><small class="text-muted">Periodo de formación</small><div id="expFechas" class="fw-semibold mt-1"></div></div></div>
                  <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><small class="text-muted">Correo</small><div id="expCorreo" class="fw-semibold mt-1"></div></div></div>
                  <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><small class="text-muted">Teléfono</small><div id="expTelefono" class="fw-semibold mt-1"></div></div></div>
                </div>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-lg-6">
                <div class="border rounded-4 p-3 h-100">
                  <h6 class="fw-bold"><i class="fa-solid fa-graduation-cap text-success me-2"></i>Resultados de aprendizaje</h6>
                  <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Competencia</th><th>Resultado</th><th>Estado</th></tr></thead><tbody id="expCalificaciones"></tbody></table></div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="border rounded-4 p-3 h-100">
                  <h6 class="fw-bold"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Novedades y observaciones</h6>
                  <div id="expNovedades"></div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="border rounded-4 p-3 h-100">
                  <h6 class="fw-bold"><i class="fa-solid fa-file-lines text-primary me-2"></i>Documentos del expediente</h6>
                  <div id="expDocumentos"></div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="border rounded-4 p-3 h-100">
                  <h6 class="fw-bold"><i class="fa-solid fa-clipboard-check text-success me-2"></i>Estado de solicitudes</h6>
                  <div id="expSolicitudes"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- VISTA 7: ASIGNACIÓN DE INSTRUCTORES A FICHAS -->
      <div class="tab-pane fade" id="asignaciones-pane">
        <div class="card card-custom p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div><h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-user-shield text-success me-2"></i>Asignación de Instructores a Fichas</h5><small class="text-muted">Asigna una ficha a un instructor y define el periodo exacto durante el cual tendrá acceso.</small></div>
            <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalAsignarFicha"><i class="fa-solid fa-link me-2"></i>Asignar ficha a instructor</button>
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="p-3 rounded-4 bg-light border"><small class="text-muted d-block">Instructores</small><strong class="fs-4" id="countInstructores">0</strong></div></div>
            <div class="col-md-4"><div class="p-3 rounded-4 bg-light border"><small class="text-muted d-block">Fichas</small><strong class="fs-4" id="countFichas">0</strong></div></div>
            <div class="col-md-4"><div class="p-3 rounded-4 bg-light border"><small class="text-muted d-block">Accesos vigentes</small><strong class="fs-4 text-success" id="countAccesosVigentes">0</strong></div></div>
          </div>
          <div class="table-responsive"><table class="table table-hover align-middle"><thead class="table-dark"><tr><th>Instructor</th><th>Ficha</th><th>Programa</th><th>Desde</th><th>Hasta</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody id="asignacionesFichasBody"></tbody></table></div>
        </div>
      </div>

      <!-- VISTA 8: FICHAS Y APRENDICES -->
      <div class="tab-pane fade" id="fichas-pane">
        <div class="card card-custom p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-users-viewfinder text-success me-2"></i>Fichas de Formación</h5><small class="text-muted">Haz clic en una ficha para ver el listado de aprendices matriculados.</small></div><div class="col-md-4 p-0"><input id="searchFicha" class="form-control" placeholder="Buscar ficha, programa o instructor..." oninput="renderFichas()"></div></div>
          <div class="row g-3" id="fichasGrid"></div>
          <div id="detalleFichaSeleccionada" class="mt-4" style="display:none;"></div>
        </div>
      </div>

    </div>
  </div>

  <!-- MODAL: ASIGNAR FICHA A INSTRUCTOR -->
  <div class="modal fade" id="modalAsignarFicha" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header text-white" style="background-color: var(--sena-dark-green);"><h5 class="modal-title fw-bold"><i class="fa-solid fa-user-shield me-2"></i>Asignar ficha a instructor</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="formAsignarFicha" class="row g-3">
      <div class="col-md-6"><label class="form-label fw-bold">Instructor *</label><select id="asignInstructor" class="form-select" required></select></div>
      <div class="col-md-6"><label class="form-label fw-bold">Ficha *</label><select id="asignFicha" class="form-select" required></select></div>
      <div class="col-md-6"><label class="form-label fw-bold">Inicio del acceso *</label><input type="datetime-local" id="asignDesde" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label fw-bold">Fin del acceso *</label><input type="datetime-local" id="asignHasta" class="form-control" required></div>
      <div class="col-12"><div class="alert alert-info small mb-0"><i class="fa-solid fa-circle-info me-2"></i>El instructor podrá trabajar con esa ficha dentro de este periodo. Después aparecerá como acceso vencido.</div></div>
    </form></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success fw-bold" onclick="guardarAsignacionFicha()">Guardar asignación</button></div>
  </div></div></div>

  <!-- MODAL: EDITAR ACCESO -->
  <div class="modal fade" id="modalEditarAccesoFicha" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header text-white" style="background-color: var(--sena-dark-green);"><h5 class="modal-title fw-bold"><i class="fa-solid fa-clock-rotate-left me-2"></i>Modificar acceso</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="formEditarAccesoFicha" class="row g-3"><input type="hidden" id="editarAsignacionId">
      <div class="col-12"><label class="form-label fw-bold">Instructor</label><input id="editarInstructorNombre" class="form-control" readonly></div><div class="col-12"><label class="form-label fw-bold">Ficha</label><input id="editarFichaNombre" class="form-control" readonly></div>
      <div class="col-6"><label class="form-label fw-bold">Desde</label><input type="datetime-local" id="editarDesde" class="form-control" required></div><div class="col-6"><label class="form-label fw-bold">Hasta</label><input type="datetime-local" id="editarHasta" class="form-control" required></div>
    </form></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success fw-bold" onclick="guardarEdicionAccesoFicha()">Guardar cambios</button></div>
  </div></div></div>

  <!-- MODAL: CARGA MASIVA DE INVENTARIO -->
  <div class="modal fade" id="modalCargaInventario" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: var(--sena-dark-green);">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-import me-2"></i>Carga masiva de inventario</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info small"><i class="fa-solid fa-circle-info me-2"></i>Sube un archivo <strong>.xlsx, .xls o .csv</strong>. Columnas recomendadas: <strong>placa, nombre, categoria, ambiente, vidaUtil, estadoFisico</strong>. Si el ambiente no existe, el elemento queda en Bodega para que pueda asignarse después.</div>
          <input type="file" class="form-control mb-3" id="archivoInventario" accept=".xlsx,.xls,.csv" onchange="previsualizarInventario(event)">
          <div id="inventarioCargaInfo" class="small text-muted mb-2"></div>
          <div class="table-responsive" style="max-height:330px; overflow:auto;">
            <table class="table table-sm table-hover align-middle"><thead class="table-dark"><tr><th>Placa</th><th>Elemento</th><th>Categoría</th><th>Ambiente</th><th>Vida útil</th><th>Estado</th></tr></thead><tbody id="inventarioPreviewBody"><tr><td colspan="6" class="text-center text-muted py-4">Selecciona un archivo para previsualizarlo.</td></tr></tbody></table>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success fw-bold" onclick="confirmarCargaInventario()"><i class="fa-solid fa-check me-1"></i>Agregar registros</button></div>
      </div>
    </div>
  </div>

  <!-- MODAL: AGREGAR INVENTARIO INDIVIDUAL -->
  <div class="modal fade" id="modalNuevoInventario" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header text-white" style="background-color: var(--sena-dark-green);"><h5 class="modal-title fw-bold"><i class="fa-solid fa-box-open me-2"></i>Agregar elemento al inventario</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><form id="formNuevoInventario" class="row g-3">
        <div class="col-md-6"><label class="form-label fw-bold">Placa / Código SENA *</label><input id="newInvPlaca" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label fw-bold">Nombre / Descripción *</label><input id="newInvNombre" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label fw-bold">Categoría *</label><input id="newInvCategoria" class="form-control" placeholder="Computadores, impresoras..." required></div>
        <div class="col-md-4"><label class="form-label fw-bold">Ambiente</label><select id="newInvAmbiente" class="form-select"><option value="">Sin asignar - Bodega</option></select></div>
        <div class="col-md-4"><label class="form-label fw-bold">Vida útil</label><select id="newInvVida" class="form-select"><option>En Vida Útil</option><option>Vida Útil Agotada</option><option>En Mantenimiento</option><option>Dado de Baja</option></select></div>
        <div class="col-12"><label class="form-label fw-bold">Estado físico</label><input id="newInvEstado" class="form-control" value="Bueno"></div>
      </form></div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success fw-bold" onclick="agregarInventarioIndividual()">Guardar elemento</button></div>
    </div></div>
  </div>

  <!-- MODAL: CARGA MASIVA DE PERSONAL -->
  <div class="modal fade" id="modalCargaPersonal" tabindex="-1">
    <div class="modal-dialog modal-xl"><div class="modal-content">
      <div class="modal-header text-white" style="background-color: var(--sena-dark-green);"><h5 class="modal-title fw-bold"><i class="fa-solid fa-users-gear me-2"></i>Carga masiva de personal</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="alert alert-info small"><i class="fa-solid fa-circle-info me-2"></i>Sube <strong>.xlsx, .xls o .csv</strong>. Columnas recomendadas: <strong>id, nombre, area, documento</strong>. La asistencia puede seguir registrándose posteriormente desde el sistema.</div>
        <input type="file" class="form-control mb-3" id="archivoPersonal" accept=".xlsx,.xls,.csv" onchange="previsualizarPersonal(event)">
        <div id="personalCargaInfo" class="small text-muted mb-2"></div>
        <div class="table-responsive" style="max-height:330px; overflow:auto;"><table class="table table-sm table-hover align-middle"><thead class="table-dark"><tr><th>ID</th><th>Nombre</th><th>Área</th><th>Documento</th></tr></thead><tbody id="personalPreviewBody"><tr><td colspan="4" class="text-center text-muted py-4">Selecciona un archivo para previsualizarlo.</td></tr></tbody></table></div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success fw-bold" onclick="confirmarCargaPersonal()"><i class="fa-solid fa-check me-1"></i>Agregar registros</button></div>
    </div></div>
  </div>

  <!-- MODAL: AGREGAR PERSONAL INDIVIDUAL -->
  <div class="modal fade" id="modalNuevoPersonal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header text-white" style="background-color: var(--sena-dark-green);"><h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Agregar personal</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><form id="formNuevoPersonal" class="row g-3">
        <div class="col-md-4"><label class="form-label fw-bold">ID *</label><input id="newStaffId" class="form-control" placeholder="INST-04" required></div>
        <div class="col-md-8"><label class="form-label fw-bold">Nombre completo *</label><input id="newStaffNombre" class="form-control" required></div>
        <div class="col-md-7"><label class="form-label fw-bold">Área / Programa *</label><input id="newStaffArea" class="form-control" required></div>
        <div class="col-md-5"><label class="form-label fw-bold">Documento *</label><input id="newStaffDocumento" class="form-control" required></div>
      </form></div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success fw-bold" onclick="agregarPersonalIndividual()">Guardar personal</button></div>
    </div></div>
  </div>

  <!-- MODAL: SACAR / EXTRAER UN ELEMENTO DE UN AMBIENTE CON MOTIVO -->
  <div class="modal fade" id="modalSacarElemento" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Sacar Elemento del Ambiente</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formSacarElemento">
            <input type="hidden" id="sacarItemId">
            <div class="mb-3">
              <label class="form-label fw-bold">Elemento:</label>
              <input type="text" id="sacarItemNombre" class="form-control" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Placa SENA:</label>
              <input type="text" id="sacarItemPlaca" class="form-control" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Ambiente Actual:</label>
              <input type="text" id="sacarItemAmbienteActual" class="form-control" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Nuevo Destino / Estado *</label>
              <select id="sacarNuevoDestino" class="form-select" required>
                <option value="SIN_ASIGNAR">Devolver a Bodega Principal (Sin Ambiente)</option>
                <option value="EN_MANTENIMIENTO">Enviar a Mantenimiento Técnico</option>
                <option value="DADO_DE_BAJA">Dar de Baja por Obsolescencia/Daño</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Motivo o Observación de la Salida *</label>
              <textarea id="sacarMotivo" class="form-control" rows="3" placeholder="Ej: Reporta fallo en la tarjeta madre / Traslado por requerimiento de bodega..." required></textarea>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold">Registrar Salida y Dejar Registro</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: CAMBIAR VIDA ÚTIL / ESTADO DE UN ELEMENTO -->
  <div class="modal fade" id="modalVidaUtil" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-heart-pulse me-2"></i>Actualizar Estado / Vida Útil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formVidaUtil">
            <input type="hidden" id="vidaUtilItemId">
            <div class="mb-3">
              <label class="form-label fw-semibold">Placa SENA:</label>
              <input type="text" id="vidaUtilPlaca" class="form-control" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Estado de Vida Útil *</label>
              <select id="vidaUtilStateSelect" class="form-select" required>
                <option value="En Vida Útil">En Vida Útil (Operativo)</option>
                <option value="Vida Útil Agotada">Vida Útil Agotada (Requiere Cambio)</option>
                <option value="En Mantenimiento">En Mantenimiento</option>
                <option value="Dado de Baja">Dado de Baja</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: var(--sena-green); border:none;">
              Guardar Cambios
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: ASIGNAR / REASIGNAR AMBIENTE A ELEMENTO -->
  <div class="modal fade" id="modalReasignarItem" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-arrows-split-up-and-left me-2"></i>Asignar / Mover a Ambiente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formReasignar">
            <input type="hidden" id="reasignarItemId">
            <div class="mb-3">
              <label class="form-label fw-semibold">Elemento:</label>
              <input type="text" id="reasignarItemNombre" class="form-control" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Placa SENA:</label>
              <input type="text" id="reasignarItemPlaca" class="form-control" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Seleccionar Ambiente Destino:</label>
              <select id="reasignarNuevoAmbienteSelect" class="form-select" required>
                <!-- Opciones dinámicas -->
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold" style="background-color: var(--sena-green); border:none;">
              Guardar Asignación
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: CREAR AMBIENTE CON LOTES DE INVENTARIO MASIVO -->
  <div class="modal fade" id="modalNuevoAmbiente" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: var(--sena-dark-green);">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle me-2"></i> Registrar Nuevo Ambiente con Inventario Inicial</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formNuevoAmbiente">
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Nombre del Ambiente *</label>
                <input type="text" id="envNombre" class="form-control" placeholder="Ej: Ambiente 302 - ADSO" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Bloque / Sede *</label>
                <input type="text" id="envBloque" class="form-control" placeholder="Ej: Bloque B - Sede Principal" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Instructor a Cargo (Cuentadante Principal) *</label>
              <select id="envInstructor" class="form-select" required>
                <option value="">Seleccione un instructor...</option>
                <option value="Ing. Carlos Pérez">Ing. Carlos Pérez - Redes & Telecomunicaciones</option>
                <option value="Dra. María Fernanda López">Dra. María Fernanda López - Software & ADSO</option>
                <option value="Lic. Jorge Ramírez">Lic. Jorge Ramírez - Mecatrónica</option>
                <option value="Ing. Andrea Gómez">Ing. Andrea Gómez - Diseño 3D</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Ficha de formación asignada *</label>
              <select id="envFicha" class="form-select" required onchange="mostrarResumenFichaAmbiente()">
                <option value="">Seleccione una ficha...</option>
              </select>
              <div id="envFichaInfo" class="form-text text-muted">Al crear el ambiente, esta ficha quedará vinculada al ambiente y sus aprendices se mostrarán automáticamente en Fichas y Aprendices.</div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold text-success"><i class="fa-solid fa-cubes-stacked me-2"></i>Asignar Lotes de Inventario Inicial (Ej: 30 Computadores)</h6>

            <div id="lotsContainer">
              <div class="row g-2 align-items-center mb-2 lot-row">
                <div class="col-md-4">
                  <input type="text" class="form-control lot-name" placeholder="Ej: Computador de Torre" required>
                </div>
                <div class="col-md-3">
                  <input type="number" class="form-control lot-count" placeholder="Cantidad (Ej: 30)" min="1" required>
                </div>
                <div class="col-md-3">
                  <select class="form-select lot-category" required>
                    <option value="Cómputo">Cómputo</option>
                    <option value="Mobiliario">Mobiliario</option>
                    <option value="Audiovisual">Audiovisual</option>
                    <option value="Electrónica/Especializado">Electrónica/Especializado</option>
                  </select>
                </div>
                <div class="col-md-2 text-center">
                  <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeLotRow(this)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>

            <button type="button" class="btn btn-outline-success btn-sm mb-3" onclick="addLotRow()">
              <i class="fa-solid fa-plus me-1"></i> Agregar Otro Grupo
            </button>

            <div class="modal-footer px-0 pb-0 pt-3">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success fw-bold px-4" style="background-color: var(--sena-green);">Crear Ambiente e Inventario</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: ASISTENCIA Y HORARIOS DEL INSTRUCTOR -->
  <div class="modal fade" id="modalAsistenciaInstructor" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: var(--sena-dark-green);">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-clock me-2"></i>Historial de Asistencia y Horarios</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded border">
            <div class="rounded-circle bg-success text-white p-3 fs-3 text-center" style="width: 60px; height: 60px;">
              <i class="fa-solid fa-user-tie"></i>
            </div>
            <div>
              <h5 class="fw-bold mb-0 text-dark" id="instModalNombre">Instructor</h5>
              <small class="text-muted d-block" id="instModalArea">Área / Especialidad</small>
              <small class="text-success fw-bold" id="instModalDocumento">Doc: 10203040</small>
            </div>
          </div>

          <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-calendar-days me-2 text-success"></i>Registro de Días Asistidos, Ingreso y Salida:</h6>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Día / Fecha</th>
                  <th>Hora Ingreso</th>
                  <th>Hora Salida</th>
                  <th>Horas Trabajadas</th>
                  <th>Estado / Novedad</th>
                </tr>
              </thead>
              <tbody id="instAttendanceTableBody">
                <!-- Javascript llena los datos -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: CAMBIAR ESTADO DE PRÉSTAMO -->
  <div class="modal fade" id="modalPrestamo" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: var(--sena-dark-green);">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-handshake me-2"></i>Estado del Ambiente / Préstamo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formPrestamo">
            <input type="hidden" id="prestamoEnvId">
            <div class="mb-3">
              <label class="form-label fw-bold">Ambiente:</label>
              <input type="text" id="prestamoEnvNombre" class="form-control fw-bold" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Estado Actual:</label>
              <select id="prestamoEstadoSelect" class="form-select" required onchange="togglePrestamoFields(this.value)">
                <option value="Disponible">Disponible</option>
                <option value="Prestado">Prestado / En Uso</option>
                <option value="En Mantenimiento">En Mantenimiento</option>
              </select>
            </div>
            <div id="prestamoDetailsGroup">
              <div class="mb-3">
                <label class="form-label fw-bold">Instructor Solicitante:</label>
                <input type="text" id="prestamoSolicitante" class="form-control" placeholder="Nombre del instructor temporal">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Observaciones:</label>
              <textarea id="prestamoObservaciones" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold" style="background-color: var(--sena-green); border:none;">
              Actualizar Estado
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Librería para leer y generar Excel/CSV -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

  <!-- SweetAlert2 & Bootstrap JS -->
  </main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>

    function toggleNotifications() {
      const panel = document.getElementById('notificationPanel');
      if (panel) panel.classList.toggle('show');
    }

    document.addEventListener('click', function(e) {
      const area = document.querySelector('.notification-area');
      if (area && !area.contains(e.target)) {
        const panel = document.getElementById('notificationPanel');
        if (panel) panel.classList.remove('show');
      }
    });

    // Navegación lateral: activa la pestaña correspondiente sin alterar sus funciones.
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('main-content').classList.toggle('expanded');
    }

    function activarSeccion(tabId, boton) {
      const tab = document.getElementById(tabId);
      if (!tab) return;
      bootstrap.Tab.getOrCreateInstance(tab).show();
      document.querySelectorAll('.sidebar-nav .sidebar-btn').forEach(btn => btn.classList.remove('active'));
      if (boton) boton.classList.add('active');
    }

    document.querySelectorAll('#mainTab .nav-link').forEach(tab => {
      tab.addEventListener('shown.bs.tab', function () {
        const mapping = {
          'inventario-tab': 0,
          'ambientes-tab': 1,
          'historico-tab': 2,
          'calendario-tab': 3,
          'personal-tab': 4,
          'expediente-tab': 5
        };
        document.querySelectorAll('.sidebar-nav .sidebar-btn').forEach(btn => btn.classList.remove('active'));
        const index = mapping[this.id];
        const buttons = document.querySelectorAll('.sidebar-nav .sidebar-btn');
        if (index !== undefined && buttons[index]) buttons[index].classList.add('active');
      });
    });

    // --- ESTADO INICIAL GLOBAL ---
    let environments = [
      {
        id: "ENV-101",
        nombre: "Ambiente 101 - ADSO",
        bloque: "Bloque A",
        instructor: "Dra. María Fernanda López",
        estadoPrestamo: "Prestado",
        solicitante: "Ing. Carlos Pérez",
        observaciones: "En uso para taller práctico de bases de datos",
        fichaId: "FIC-284910"
      },
      {
        id: "ENV-204",
        nombre: "Ambiente 204 - Redes y Seguridad",
        bloque: "Bloque B",
        instructor: "Ing. Carlos Pérez",
        estadoPrestamo: "Disponible",
        solicitante: "",
        observaciones: "Equipos verificados",
        fichaId: null
      }
    ];

    let inventory = [];
    let movementHistory = [];

    let staff = [
      {
        id: "INST-01",
        nombre: "Ing. Carlos Pérez",
        area: "Redes & Telecomunicaciones",
        documento: "CC 1.019.234.890",
        asistencia: [
          { fecha: "2026-10-02 (Viernes)", ingreso: "06:48 AM", salida: "04:15 PM", horas: "9.5 hrs", novedad: "A Tiempo" },
          { fecha: "2026-10-01 (Jueves)", ingreso: "06:55 AM", salida: "04:00 PM", horas: "9.0 hrs", novedad: "A Tiempo" },
          { fecha: "2026-09-30 (Miércoles)", ingreso: "07:10 AM", salida: "04:30 PM", horas: "9.3 hrs", novedad: "Ingreso con Novedad" },
          { fecha: "2026-09-29 (Martes)", ingreso: "06:45 AM", salida: "04:00 PM", horas: "9.2 hrs", novedad: "A Tiempo" }
        ]
      },
      {
        id: "INST-02",
        nombre: "Dra. María Fernanda López",
        area: "Análisis y Desarrollo de Software (ADSO)",
        documento: "CC 52.890.123",
        asistencia: [
          { fecha: "2026-10-02 (Viernes)", ingreso: "07:00 AM", salida: "05:00 PM", horas: "10 hrs", novedad: "A Tiempo" },
          { fecha: "2026-10-01 (Jueves)", ingreso: "06:50 AM", salida: "05:05 PM", horas: "10.2 hrs", novedad: "A Tiempo" },
          { fecha: "2026-09-30 (Miércoles)", ingreso: "06:58 AM", salida: "04:55 PM", horas: "10 hrs", novedad: "A Tiempo" }
        ]
      },
      {
        id: "INST-03",
        nombre: "Lic. Jorge Ramírez",
        area: "Mecatrónica e Robótica",
        documento: "CC 80.456.789",
        asistencia: [
          { fecha: "2026-10-02 (Viernes)", ingreso: "07:15 AM", salida: "03:30 PM", horas: "8.2 hrs", novedad: "Permiso de Salida Temprana" },
          { fecha: "2026-10-01 (Jueves)", ingreso: "06:45 AM", salida: "04:00 PM", horas: "9.2 hrs", novedad: "A Tiempo" }
        ]
      }
    ];

    // Expedientes de aprendices
    let aprendices = [
      {
        id: 'APR-001', nombre: 'Paula Duque', documento: 'CC 1.023.456.789', ficha: '284910 - ADSO', programa: 'Análisis y Desarrollo de Software', estado: 'Activo',
        jornada: 'Diurna', telefono: '300 000 0000', correo: 'paula.duque@sena.edu.co', fechaInicio: '25/07/2025', fechaFin: '24/10/2027',
        asistencia: 96, promedio: 4.8, fallas: 1,
        calificaciones: [
          { competencia: 'Construcción de software', resultado: '4.8', estado: 'Aprobado' },
          { competencia: 'Bases de datos', resultado: '4.7', estado: 'Aprobado' },
          { competencia: 'Desarrollo web', resultado: '4.9', estado: 'Aprobado' }
        ],
        novedades: ['Sin novedades disciplinarias registradas.', 'Falla del 02/10/2026 justificada mediante excusa.'],
        documentos: ['Documento de identidad', 'Matrícula', 'Contrato de aprendizaje', 'Excusas y soportes'],
        solicitudes: ['Solicitud de permiso · Aprobada', 'Actualización de datos · Aprobada']
      },
      {
        id: 'APR-002', nombre: 'Juan Sebastián Silva', documento: 'CC 1.018.293.811', ficha: '284910 - ADSO', programa: 'Análisis y Desarrollo de Software', estado: 'Activo',
        jornada: 'Diurna', telefono: '301 000 0000', correo: 'juan.silva@sena.edu.co', fechaInicio: '25/07/2025', fechaFin: '24/10/2027',
        asistencia: 92, promedio: 4.3, fallas: 3,
        calificaciones: [
          { competencia: 'Construcción de software', resultado: '4.4', estado: 'Aprobado' },
          { competencia: 'Bases de datos', resultado: '4.1', estado: 'Aprobado' },
          { competencia: 'Desarrollo web', resultado: '4.4', estado: 'Aprobado' }
        ],
        novedades: ['Una inasistencia pendiente de revisión.', 'Se registró seguimiento académico.'],
        documentos: ['Documento de identidad', 'Matrícula', 'Seguimiento académico'],
        solicitudes: ['Solicitud de permiso · Pendiente']
      }
    ];

    // Fichas de formación y accesos de instructores
    let fichas = [
      { id: 'FIC-284910', codigo: '284910', programa: 'Análisis y Desarrollo de Software', jornada: 'Diurna', sede: 'Centro de Servicios', estado: 'Activa', instructorPrincipal: 'INST-02', aprendizIds: ['APR-001','APR-002'] },
      { id: 'FIC-271029', codigo: '271029', programa: 'Ciberseguridad', jornada: 'Nocturna', sede: 'Centro de Tecnología', estado: 'Activa', instructorPrincipal: 'INST-01', aprendizIds: [] }
    ];
    let asignacionesFichas = [
      { id: 'ASIG-001', instructorId: 'INST-02', fichaId: 'FIC-284910', desde: '2026-01-15T06:00', hasta: '2027-10-24T23:59' },
      { id: 'ASIG-002', instructorId: 'INST-01', fichaId: 'FIC-271029', desde: '2026-07-01T18:00', hasta: '2027-06-30T23:59' }
    ];

    // Cargar Inventario Inicial
    function seedInventory() {
      // 30 Computadores en Amb. 101
      for (let i = 1; i <= 30; i++) {
        inventory.push({
          id: `ITEM-PC-${i}`,
          placa: `SENA-PC-${100 + i}`,
          nombre: `Computador HP ProDesk 400 #${i}`,
          categoria: "Cómputo",
          ambienteId: "ENV-101",
          vidaUtil: "En Vida Útil",
          estadoFisico: "Bueno"
        });
      }

      // Elementos SIN Ambiente (En Bodega Principal)
      for (let i = 1; i <= 8; i++) {
        inventory.push({
          id: `ITEM-BOD-${i}`,
          placa: `SENA-BOD-${500 + i}`,
          nombre: `Impresora Multifuncional Kyocera #${i}`,
          categoria: "Audiovisual",
          ambienteId: null, // SIN AMBIENTE
          vidaUtil: i % 2 === 0 ? "Vida Útil Agotada" : "En Vida Útil",
          estadoFisico: "Almacenado en Bodega"
        });
      }

      // 5 Routers en Amb. 204
      for (let i = 1; i <= 5; i++) {
        inventory.push({
          id: `ITEM-NET-${i}`,
          placa: `SENA-NET-${200 + i}`,
          nombre: `Switch Cisco Catalyst 2960 #${i}`,
          categoria: "Electrónica/Especializado",
          ambienteId: "ENV-204",
          vidaUtil: "En Vida Útil",
          estadoFisico: "Bueno"
        });
      }

      // Inicializar Histórico Ficticio
      movementHistory.push({
        fecha: "2026-10-02 09:30 AM",
        placa: "SENA-PC-105",
        nombre: "Computador HP ProDesk 400 #5",
        origen: "Ambiente 101 - ADSO",
        destino: "En Mantenimiento",
        motivo: "Se extrajo del ambiente por fallo de fuente de poder."
      });
    }

    document.addEventListener("DOMContentLoaded", () => {
      seedInventory();
      renderAll();
      renderCalendar();
      renderPersonalGrid();
      renderAprendicesExpediente();
      renderAsignacionesFichas();
      renderFichas();
      cargarSelectoresAsignacionFicha();
      cargarFichasEnFormularioAmbiente();
    });

    function renderAll() {
      renderInventoryTable();
      renderEnvironmentsGrid();
      renderHistoricoTable();
      updateFilterSelects();
      document.getElementById('total-items-badge').textContent = inventory.length;
    }

    // RENDER VISTA 1: TABLA DE INVENTARIO COMPLETA
    function renderInventoryTable() {
      const tbody = document.getElementById('inventoryTableBody');
      const search = document.getElementById('searchInventory').value.toLowerCase();
      const filterEnv = document.getElementById('filterEnvironment').value;
      const filterVida = document.getElementById('filterVidaUtil').value;

      tbody.innerHTML = '';

      const filtered = inventory.filter(item => {
        const matchesSearch = item.placa.toLowerCase().includes(search) || item.nombre.toLowerCase().includes(search);
        
        let matchesEnv = true;
        if (filterEnv === "SIN_ASIGNAR") {
          matchesEnv = !item.ambienteId;
        } else if (filterEnv !== "") {
          matchesEnv = item.ambienteId === filterEnv;
        }

        const matchesVida = !filterVida || item.vidaUtil === filterVida;

        return matchesSearch && matchesEnv && matchesVida;
      });

      if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron activos registradas con los filtros aplicados.</td></tr>`;
        return;
      }

      filtered.forEach(item => {
        const env = environments.find(e => e.id === item.ambienteId);
        const envNombre = env ? env.nombre : '<span class="badge bg-warning text-dark"><i class="fa-solid fa-warehouse me-1"></i> Sin Asignar (Bodega)</span>';
        const instructor = env ? env.instructor : '<span class="text-muted">N/A</span>';

        let vidaBadge = 'bg-success';
        if (item.vidaUtil === 'Vida Útil Agotada') vidaBadge = 'bg-danger';
        if (item.vidaUtil === 'En Mantenimiento') vidaBadge = 'bg-warning text-dark';
        if (item.vidaUtil === 'Dado de Baja') vidaBadge = 'bg-dark';

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><span class="placa-badge">${item.placa}</span></td>
          <td class="fw-bold text-dark">${item.nombre}</td>
          <td><span class="badge bg-secondary">${item.categoria}</span></td>
          <td>${envNombre}</td>
          <td>
            <span class="badge ${vidaBadge} cursor-pointer" onclick="openVidaUtilModal('${item.id}')" title="Clic para cambiar vida útil">
              ${item.vidaUtil} <i class="fa-solid fa-pen ms-1"></i>
            </span>
          </td>
          <td class="small">${instructor}</td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" onclick="openReasignarModal('${item.id}')" title="Asignar o Cambiar de Ambiente">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
              </button>
              ${item.ambienteId ? `
                <button class="btn btn-outline-danger" onclick="openSacarModal('${item.id}')" title="Sacar de este Ambiente con Motivo">
                  <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
              ` : ''}
            </div>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    document.getElementById('searchInventory').addEventListener('input', renderInventoryTable);
    document.getElementById('filterEnvironment').addEventListener('change', renderInventoryTable);
    document.getElementById('filterVidaUtil').addEventListener('change', renderInventoryTable);

    function resetInventoryFilters() {
      document.getElementById('searchInventory').value = '';
      document.getElementById('filterEnvironment').value = '';
      document.getElementById('filterVidaUtil').value = '';
      renderInventoryTable();
    }

    // RENDER VISTA 2: AMBIENTES Y PRÉSTAMOS
    function renderEnvironmentsGrid() {
      const grid = document.getElementById('environmentsGrid');
      grid.innerHTML = '';

      environments.forEach(env => {
        const envItems = inventory.filter(i => i.ambienteId === env.id);

        let statusBadgeClass = 'status-disponible';
        if (env.estadoPrestamo === 'Prestado') statusBadgeClass = 'status-prestado';
        if (env.estadoPrestamo === 'En Mantenimiento') statusBadgeClass = 'status-mantenimiento';

        const card = document.createElement('div');
        card.className = 'col-md-6';
        card.innerHTML = `
          <div class="card card-custom env-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h5 class="fw-bold mb-1 text-dark">${env.nombre}</h5>
                <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i>${env.bloque}</small>
              </div>
              <span class="badge ${statusBadgeClass} px-3 py-2 fs-6">
                ${env.estadoPrestamo}
              </span>
            </div>

            <div class="bg-light p-2 rounded my-2 border small">
              <div><i class="fa-solid fa-user-tie me-1 text-primary"></i><strong>Cuentadante:</strong> ${env.instructor}</div>
              ${(() => { const f = fichas.find(x => x.id === env.fichaId); return f ? `<div><i class="fa-solid fa-id-card me-1 text-success"></i><strong>Ficha:</strong> ${escapeHtml(f.codigo)} · ${escapeHtml(f.programa)}</div>` : ''; })()}
              ${env.solicitante ? `<div><i class="fa-solid fa-handshake me-1 text-warning"></i><strong>Prestado a:</strong> ${env.solicitante}</div>` : ''}
            </div>

            <div class="d-flex justify-content-between align-items-center my-2">
              <span class="fw-bold text-success">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Total Activos: ${envItems.length}
              </span>
              <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEnv-${env.id}">
                <i class="fa-solid fa-eye me-1"></i> Ver Placas
              </button>
            </div>

            <div class="collapse my-2" id="collapseEnv-${env.id}">
              <div class="card card-body p-2 bg-light border" style="max-height: 180px; overflow-y: auto;">
                <table class="table table-sm small mb-0">
                  <thead><tr><th>Placa SENA</th><th>Elemento</th><th>Acción</th></tr></thead>
                  <tbody>
                    ${envItems.length > 0 ? envItems.map(item => `
                      <tr>
                        <td><span class="placa-badge">${item.placa}</span></td>
                        <td>${item.nombre}</td>
                        <td>
                          <button class="btn btn-xs btn-outline-danger p-0 px-1" title="Sacar" onclick="openSacarModal('${item.id}')">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </td>
                      </tr>
                    `).join('') : '<tr><td colspan="3" class="text-muted text-center">Sin elementos asignados</td></tr>'}
                  </tbody>
                </table>
              </div>
            </div>

            <button class="btn btn-sm btn-outline-primary w-100 fw-bold mt-2" onclick="openPrestamoModal('${env.id}')">
              <i class="fa-solid fa-handshake me-1"></i> Registrar Préstamo / Estado
            </button>
          </div>
        `;
        grid.appendChild(card);
      });
    }

    // RENDER VISTA 3: HISTÓRICO DE MOVIMIENTOS
    function renderHistoricoTable() {
      const tbody = document.getElementById('historicoTableBody');
      tbody.innerHTML = '';

      if (movementHistory.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-muted">No hay movimientos registrados.</td></tr>`;
        return;
      }

      movementHistory.forEach(h => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><small class="text-muted">${h.fecha}</small></td>
          <td><span class="placa-badge">${h.placa}</span></td>
          <td class="fw-bold">${h.nombre}</td>
          <td><span class="badge bg-secondary">${h.origen}</span></td>
          <td><span class="badge bg-warning text-dark">${h.destino}</span></td>
          <td class="small text-muted">${h.motivo}</td>
        `;
        tbody.appendChild(tr);
      });
    }

    // ACCIONES DE SACAR ELEMENTO DE UN AMBIENTE
    function openSacarModal(itemId) {
      const item = inventory.find(i => i.id === itemId);
      if (!item) return;

      const env = environments.find(e => e.id === item.ambienteId);

      document.getElementById('sacarItemId').value = item.id;
      document.getElementById('sacarItemNombre').value = item.nombre;
      document.getElementById('sacarItemPlaca').value = item.placa;
      document.getElementById('sacarItemAmbienteActual').value = env ? env.nombre : 'Bodega';
      document.getElementById('sacarMotivo').value = '';

      const modal = new bootstrap.Modal(document.getElementById('modalSacarElemento'));
      modal.show();
    }

    document.getElementById('formSacarElemento').addEventListener('submit', (e) => {
      e.preventDefault();
      const itemId = document.getElementById('sacarItemId').value;
      const destino = document.getElementById('sacarNuevoDestino').value;
      const motivo = document.getElementById('sacarMotivo').value;

      const item = inventory.find(i => i.id === itemId);
      if (!item) return;

      const envOrigen = environments.find(e => e.id === item.ambienteId);
      const nombreOrigen = envOrigen ? envOrigen.nombre : 'Bodega';

      // Registrar en el histórico
      let nombreDestinoText = "Bodega Principal";
      if (destino === 'SIN_ASIGNAR') {
        item.ambienteId = null;
      } else if (destino === 'EN_MANTENIMIENTO') {
        item.ambienteId = null;
        item.vidaUtil = 'En Mantenimiento';
        nombreDestinoText = "Taller Mantenimiento";
      } else if (destino === 'DADO_DE_BAJA') {
        item.ambienteId = null;
        item.vidaUtil = 'Dado de Baja';
        nombreDestinoText = "Dado de Baja";
      }

      movementHistory.unshift({
        fecha: new Date().toLocaleString(),
        placa: item.placa,
        nombre: item.nombre,
        origen: nombreOrigen,
        destino: nombreDestinoText,
        motivo: motivo
      });

      renderAll();
      bootstrap.Modal.getInstance(document.getElementById('modalSacarElemento')).hide();

      Swal.fire({
        title: 'Elemento Retirado',
        text: `El activo con placa ${item.placa} se retiró del ambiente y se registró la novedad.`,
        icon: 'success'
      });
    });

    // ACCIÓN: CAMBIAR VIDA ÚTIL
    function openVidaUtilModal(itemId) {
      const item = inventory.find(i => i.id === itemId);
      if (!item) return;

      document.getElementById('vidaUtilItemId').value = item.id;
      document.getElementById('vidaUtilPlaca').value = item.placa;
      document.getElementById('vidaUtilStateSelect').value = item.vidaUtil;

      const modal = new bootstrap.Modal(document.getElementById('modalVidaUtil'));
      modal.show();
    }

    document.getElementById('formVidaUtil').addEventListener('submit', (e) => {
      e.preventDefault();
      const itemId = document.getElementById('vidaUtilItemId').value;
      const state = document.getElementById('vidaUtilStateSelect').value;

      const item = inventory.find(i => i.id === itemId);
      if (item) {
        item.vidaUtil = state;
        renderAll();
        bootstrap.Modal.getInstance(document.getElementById('modalVidaUtil')).hide();
        Swal.fire('Estado Actualizado', `La vida útil de la placa ${item.placa} cambió a: ${state}`, 'success');
      }
    });

    // ACCIÓN: REASIGNAR AMBIENTE
    function openReasignarModal(itemId) {
      const item = inventory.find(i => i.id === itemId);
      if (!item) return;

      document.getElementById('reasignarItemId').value = item.id;
      document.getElementById('reasignarItemNombre').value = item.nombre;
      document.getElementById('reasignarItemPlaca').value = item.placa;
      document.getElementById('reasignarNuevoAmbienteSelect').value = item.ambienteId || '';

      const modal = new bootstrap.Modal(document.getElementById('modalReasignarItem'));
      modal.show();
    }

    document.getElementById('formReasignar').addEventListener('submit', (e) => {
      e.preventDefault();
      const itemId = document.getElementById('reasignarItemId').value;
      const newEnvId = document.getElementById('reasignarNuevoAmbienteSelect').value;

      const item = inventory.find(i => i.id === itemId);
      if (item) {
        item.ambienteId = newEnvId || null;
        renderAll();
        bootstrap.Modal.getInstance(document.getElementById('modalReasignarItem')).hide();
        Swal.fire('Asignación Completada', `El elemento fue trasladado exitosamente.`, 'success');
      }
    });

    // RENDER VISTA 4: CALENDARIO DE EVENTOS
    function renderCalendar() {
      const grid = document.getElementById('calendarGrid');
      grid.innerHTML = '';

      const sampleEvents = [
        { day: 1, text: "Ambiente 101 - Inducción ADSO", bg: "bg-primary" },
        { day: 2, text: "Ambiente 204 - Taller Redes Cisco", bg: "bg-success" },
        { day: 3, text: "Ambiente 101 - Reserva Externa", bg: "bg-warning text-dark" },
        { day: 4, text: "Mantenimiento General Bloque B", bg: "bg-danger" },
        { day: 5, text: "Comité Técnico de Centro", bg: "bg-info text-dark" }
      ];

      for (let day = 1; day <= 12; day++) {
        const col = document.createElement('div');
        col.className = 'col-md-2 col-4';
        
        const eventsForDay = sampleEvents.filter(e => e.day === ((day % 5) + 1));

        col.innerHTML = `
          <div class="calendar-cell">
            <strong class="text-secondary small">Oct ${day}</strong>
            ${eventsForDay.map(e => `<span class="event-badge ${e.bg}">${e.text}</span>`).join('')}
          </div>
        `;
        grid.appendChild(col);
      }
    }

    function addNewEvent() {
      Swal.fire({
        title: 'Agendar Evento en Ambiente',
        html: `
          <input id="swal-ev-name" class="swal2-input" placeholder="Nombre del Evento">
          <input id="swal-ev-env" class="swal2-input" placeholder="Ambiente (Ej: Amb 101)">
        `,
        showCancelButton: true,
        confirmButtonText: 'Agendar'
      }).then((res) => {
        if (res.isConfirmed) {
          Swal.fire('Evento Agendado', 'Se añadió el evento al calendario institucional.', 'success');
        }
      });
    }

    // RENDER VISTA 5: PERSONAL Y ASISTENCIA
    function renderPersonalGrid() {
      const grid = document.getElementById('personalGrid');
      grid.innerHTML = '';

      staff.forEach(inst => {
        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `
          <div class="card card-custom instructor-card p-3 border" onclick="openInstructorAttendance('${inst.id}')">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-success text-white p-3 fs-3 text-center" style="width: 55px; height: 55px;">
                <i class="fa-solid fa-user-tie"></i>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-0">${inst.nombre}</h6>
                <small class="text-muted d-block">${inst.area}</small>
                <small class="text-success fw-semibold"><i class="fa-solid fa-clock me-1"></i>Ver Días y Horarios</small>
              </div>
            </div>
          </div>
        `;
        grid.appendChild(col);
      });
    }

    function openInstructorAttendance(instId) {
      const inst = staff.find(s => s.id === instId);
      if (!inst) return;

      document.getElementById('instModalNombre').textContent = inst.nombre;
      document.getElementById('instModalArea').textContent = inst.area;
      document.getElementById('instModalDocumento').textContent = inst.documento;

      const tbody = document.getElementById('instAttendanceTableBody');
      tbody.innerHTML = '';

      inst.asistencia.forEach(a => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="fw-bold text-dark">${a.fecha}</td>
          <td><span class="badge bg-success-subtle text-success border border-success">${a.ingreso}</span></td>
          <td><span class="badge bg-danger-subtle text-danger border border-danger">${a.salida}</span></td>
          <td class="fw-bold">${a.horas}</td>
          <td><span class="badge bg-light text-dark border">${a.novedad}</span></td>
        `;
        tbody.appendChild(tr);
      });

      const modal = new bootstrap.Modal(document.getElementById('modalAsistenciaInstructor'));
      modal.show();
    }

    // GESTIÓN DE ASIGNACIÓN DE FICHAS
    function estadoAccesoFicha(a) { const ahora=new Date(), desde=new Date(a.desde), hasta=new Date(a.hasta); if(ahora<desde)return{texto:'Programado',clase:'bg-warning-subtle text-warning-emphasis border border-warning'}; if(ahora>hasta)return{texto:'Vencido',clase:'bg-danger-subtle text-danger border border-danger'}; return{texto:'Vigente',clase:'bg-success-subtle text-success border border-success'}; }
    function formatearFechaHora(v){if(!v)return'—';const d=new Date(v);return Number.isNaN(d.getTime())?v:d.toLocaleString('es-CO',{dateStyle:'short',timeStyle:'short'});}
    function renderAsignacionesFichas(){const body=document.getElementById('asignacionesFichasBody');if(!body)return;document.getElementById('countInstructores').textContent=staff.length;document.getElementById('countFichas').textContent=fichas.length;document.getElementById('countAccesosVigentes').textContent=asignacionesFichas.filter(a=>estadoAccesoFicha(a).texto==='Vigente').length;body.innerHTML=asignacionesFichas.length?asignacionesFichas.map(a=>{const i=staff.find(x=>x.id===a.instructorId),f=fichas.find(x=>x.id===a.fichaId),e=estadoAccesoFicha(a);return `<tr><td><strong>${escapeHtml(i?.nombre||'Instructor no encontrado')}</strong><small class="d-block text-muted">${escapeHtml(i?.documento||'')}</small></td><td><span class="badge bg-light text-dark border">${escapeHtml(f?.codigo||'—')}</span></td><td>${escapeHtml(f?.programa||'—')}</td><td>${formatearFechaHora(a.desde)}</td><td>${formatearFechaHora(a.hasta)}</td><td><span class="badge ${e.clase}">${e.texto}</span></td><td class="text-end"><button class="btn btn-sm btn-outline-success" onclick="editarAccesoFicha('${a.id}')"><i class="fa-solid fa-pen"></i></button> <button class="btn btn-sm btn-outline-danger" onclick="eliminarAsignacionFicha('${a.id}')"><i class="fa-solid fa-trash"></i></button></td></tr>`}).join(''):'<tr><td colspan="7" class="text-center text-muted py-5">No hay asignaciones registradas.</td></tr>';}
    function cargarSelectoresAsignacionFicha(){const i=document.getElementById('asignInstructor'),f=document.getElementById('asignFicha');if(!i||!f)return;i.innerHTML='<option value="">Seleccione un instructor...</option>'+staff.map(s=>`<option value="${escapeHtml(s.id)}">${escapeHtml(s.nombre)} · ${escapeHtml(s.area)}</option>`).join('');f.innerHTML='<option value="">Seleccione una ficha...</option>'+fichas.map(x=>`<option value="${escapeHtml(x.id)}">${escapeHtml(x.codigo)} · ${escapeHtml(x.programa)}</option>`).join('');}
    function guardarAsignacionFicha(){const form=document.getElementById('formAsignarFicha');if(!form.checkValidity()){form.reportValidity();return;}const instructorId=document.getElementById('asignInstructor').value,fichaId=document.getElementById('asignFicha').value,desde=document.getElementById('asignDesde').value,hasta=document.getElementById('asignHasta').value;if(new Date(hasta)<=new Date(desde)){alert('La fecha de finalización debe ser posterior a la fecha de inicio.');return;}if(asignacionesFichas.some(a=>a.instructorId===instructorId&&a.fichaId===fichaId&&new Date(a.hasta)>=new Date(desde)&&new Date(a.desde)<=new Date(hasta))){alert('El instructor ya tiene un acceso que se cruza con ese periodo para esta ficha.');return;}asignacionesFichas.push({id:`ASIG-${Date.now()}`,instructorId,fichaId,desde,hasta});renderAsignacionesFichas();form.reset();bootstrap.Modal.getInstance(document.getElementById('modalAsignarFicha')).hide();Swal.fire('Asignación guardada','El instructor tendrá acceso a la ficha durante el periodo indicado.','success');}
    function editarAccesoFicha(id){const a=asignacionesFichas.find(x=>x.id===id);if(!a)return;const i=staff.find(x=>x.id===a.instructorId),f=fichas.find(x=>x.id===a.fichaId);document.getElementById('editarAsignacionId').value=id;document.getElementById('editarInstructorNombre').value=i?.nombre||'';document.getElementById('editarFichaNombre').value=`${f?.codigo||''} · ${f?.programa||''}`;document.getElementById('editarDesde').value=a.desde;document.getElementById('editarHasta').value=a.hasta;new bootstrap.Modal(document.getElementById('modalEditarAccesoFicha')).show();}
    function guardarEdicionAccesoFicha(){const a=asignacionesFichas.find(x=>x.id===document.getElementById('editarAsignacionId').value),desde=document.getElementById('editarDesde').value,hasta=document.getElementById('editarHasta').value;if(!a||!desde||!hasta||new Date(hasta)<=new Date(desde)){alert('Verifica las fechas de acceso.');return;}a.desde=desde;a.hasta=hasta;renderAsignacionesFichas();bootstrap.Modal.getInstance(document.getElementById('modalEditarAccesoFicha')).hide();}
    function eliminarAsignacionFicha(id){const a=asignacionesFichas.find(x=>x.id===id);if(!a)return;const i=staff.find(x=>x.id===a.instructorId),f=fichas.find(x=>x.id===a.fichaId);if(!confirm(`¿Eliminar el acceso de ${i?.nombre||'el instructor'} a la ficha ${f?.codigo||''}?`))return;asignacionesFichas=asignacionesFichas.filter(x=>x.id!==id);renderAsignacionesFichas();}
    function renderFichas(){
      const grid=document.getElementById('fichasGrid');
      if(!grid)return;
      const q=(document.getElementById('searchFicha')?.value||'').toLowerCase().trim();
      const lista=fichas.filter(f=>{
        const i=staff.find(x=>x.id===f.instructorPrincipal);
        const env=environments.find(x=>x.fichaId===f.id);
        return [f.codigo,f.programa,f.jornada,f.sede,i?.nombre,env?.nombre].some(v=>String(v||'').toLowerCase().includes(q));
      });
      grid.innerHTML=lista.length?lista.map(f=>{
        const i=staff.find(x=>x.id===f.instructorPrincipal);
        const env=environments.find(x=>x.fichaId===f.id);
        const alumnos=aprendices.filter(a=>f.aprendizIds.includes(a.id)||String(a.ficha||'').startsWith(f.codigo));
        return `<div class="col-xl-4 col-md-6"><button type="button" class="card card-custom p-4 w-100 text-start border h-100" style="cursor:pointer;" onclick="mostrarFicha('${f.id}')"><div class="d-flex justify-content-between align-items-start mb-3"><span class="badge bg-success-subtle text-success border border-success">${escapeHtml(f.estado)}</span><i class="fa-solid fa-chevron-right text-muted"></i></div><h5 class="fw-bold text-dark mb-1">Ficha ${escapeHtml(f.codigo)}</h5><p class="text-muted small mb-3">${escapeHtml(f.programa)}</p><div class="small"><div class="mb-1"><i class="fa-solid fa-user-tie text-success me-2"></i>${escapeHtml(i?.nombre||'Sin instructor')}</div><div class="mb-1"><i class="fa-solid fa-clock text-success me-2"></i>${escapeHtml(f.jornada)}</div><div class="mb-1"><i class="fa-solid fa-door-open text-success me-2"></i>${env?escapeHtml(env.nombre):'Sin ambiente asignado'}</div><div><i class="fa-solid fa-users text-success me-2"></i>${alumnos.length} aprendiz(es) matriculado(s)</div></div></button></div>`
      }).join(''):'<div class="col-12"><div class="text-center py-5 text-muted">No se encontraron fichas.</div></div>';
    }
    function mostrarFicha(id){const f=fichas.find(x=>x.id===id),panel=document.getElementById('detalleFichaSeleccionada');if(!f||!panel)return;const i=staff.find(x=>x.id===f.instructorPrincipal),env=environments.find(x=>x.fichaId===f.id),alumnos=aprendices.filter(a=>f.aprendizIds.includes(a.id)||String(a.ficha||'').startsWith(f.codigo));panel.style.display='block';panel.innerHTML=`<div class="border rounded-4 p-4 bg-light"><div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><span class="badge bg-success mb-2">Ficha ${escapeHtml(f.codigo)}</span><h5 class="fw-bold mb-1">${escapeHtml(f.programa)}</h5><small class="text-muted">${escapeHtml(f.jornada)} · ${escapeHtml(f.sede)}</small><div class="mt-2"><span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-door-open me-1"></i>Ambiente: ${env?escapeHtml(env.nombre):'Sin ambiente asignado'}</span></div></div><div class="text-end"><small class="text-muted d-block">Instructor principal</small><strong>${escapeHtml(i?.nombre||'Sin asignar')}</strong></div></div><div class="table-responsive"><table class="table table-hover align-middle mb-0 bg-white"><thead class="table-dark"><tr><th>#</th><th>Aprendiz</th><th>Documento</th><th>Estado</th><th>Asistencia</th><th>Promedio</th><th>Ambiente</th><th></th></tr></thead><tbody>${alumnos.length?alumnos.map((a,n)=>`<tr><td>${n+1}</td><td><strong>${escapeHtml(a.nombre)}</strong></td><td>${escapeHtml(a.documento)}</td><td><span class="badge bg-success-subtle text-success border border-success">${escapeHtml(a.estado)}</span></td><td>${escapeHtml(a.asistencia)}%</td><td>${escapeHtml(a.promedio)}</td><td>${env?escapeHtml(env.nombre):'Sin ambiente'}</td><td><button class="btn btn-sm btn-outline-success" onclick="abrirExpedienteDesdeFicha('${a.id}')">Ver expediente</button></td></tr>`).join(''):'<tr><td colspan="8" class="text-center text-muted py-4">No hay aprendices matriculados en esta ficha.</td></tr>'}</tbody></table></div></div>`;panel.scrollIntoView({behavior:'smooth',block:'start'});}
    function abrirExpedienteDesdeFicha(id){document.getElementById('searchAprendiz').value='';bootstrap.Tab.getOrCreateInstance(document.getElementById('expediente-tab')).show();setTimeout(()=>{renderAprendicesExpediente();document.getElementById('selectAprendizExpediente').value=id;mostrarExpedienteAprendiz(id);},100);}

    // RENDER VISTA 6: EXPEDIENTE DEL APRENDIZ
    function obtenerFichaDelAprendiz(aprendiz) {
      if (!aprendiz) return null;
      return fichas.find(f => f.aprendizIds.includes(aprendiz.id) || String(aprendiz.ficha || '').startsWith(f.codigo)) || null;
    }

    function obtenerAmbienteDelAprendiz(aprendiz) {
      const ficha = obtenerFichaDelAprendiz(aprendiz);
      return ficha ? environments.find(env => env.fichaId === ficha.id) || null : null;
    }

    function sincronizarAprendicesDeFicha(fichaId) {
      const ficha = fichas.find(f => f.id === fichaId);
      if (!ficha) return [];
      const ids = aprendices
        .filter(a => ficha.aprendizIds.includes(a.id) || String(a.ficha || '').startsWith(ficha.codigo))
        .map(a => a.id);
      ficha.aprendizIds = [...new Set([...ficha.aprendizIds, ...ids])];
      return aprendices.filter(a => ficha.aprendizIds.includes(a.id) || String(a.ficha || '').startsWith(ficha.codigo));
    }

    function renderAprendicesExpediente() {
      const select = document.getElementById('selectAprendizExpediente');
      const search = (document.getElementById('searchAprendiz')?.value || '').toLowerCase().trim();
      if (!select) return;
      const filtrados = aprendices.filter(a => {
        const ficha = obtenerFichaDelAprendiz(a);
        const env = ficha ? environments.find(e => e.fichaId === ficha.id) : null;
        return [a.nombre, a.documento, a.ficha, a.programa, ficha?.codigo, ficha?.programa, env?.nombre]
          .some(v => String(v || '').toLowerCase().includes(search));
      });
      const actual = select.value;
      select.innerHTML = '<option value="">Seleccione un aprendiz</option>' + filtrados.map(a => {
        const ficha = obtenerFichaDelAprendiz(a);
        const env = obtenerAmbienteDelAprendiz(a);
        return `<option value="${escapeHtml(a.id)}">${escapeHtml(a.nombre)} · ${escapeHtml(a.ficha)}${env ? ' · ' + escapeHtml(env.nombre) : ''}</option>`;
      }).join('');
      if (filtrados.some(a => a.id === actual)) {
        select.value = actual;
      } else if (filtrados.length === 1) {
        select.value = filtrados[0].id;
        mostrarExpedienteAprendiz(filtrados[0].id);
      }
    }

    function mostrarExpedienteAprendiz(id) {
      const aprendiz = aprendices.find(a => a.id === id);
      const empty = document.getElementById('aprendizExpedienteEmpty');
      const contenido = document.getElementById('aprendizExpedienteContenido');
      if (!aprendiz) {
        empty.style.display = 'block';
        contenido.style.display = 'none';
        return;
      }
      empty.style.display = 'none';
      contenido.style.display = 'block';
      document.getElementById('expNombre').textContent = aprendiz.nombre;
      document.getElementById('expEstado').textContent = aprendiz.estado;
      document.getElementById('expDocumento').textContent = aprendiz.documento;
      const fichaAprendiz = obtenerFichaDelAprendiz(aprendiz);
      const ambienteAprendiz = obtenerAmbienteDelAprendiz(aprendiz);
      document.getElementById('expFicha').textContent = aprendiz.ficha;
      document.getElementById('expPrograma').textContent = aprendiz.programa;
      const expAmbiente = document.getElementById('expAmbiente');
      if (expAmbiente) expAmbiente.textContent = ambienteAprendiz ? ambienteAprendiz.nombre : 'Sin ambiente asignado';
      const expFichaRelacion = document.getElementById('expFichaRelacion');
      if (expFichaRelacion) expFichaRelacion.textContent = fichaAprendiz ? `Ficha ${fichaAprendiz.codigo}` : 'Sin ficha';
      document.getElementById('expAsistencia').textContent = aprendiz.asistencia + '%';
      document.getElementById('expPromedio').textContent = aprendiz.promedio;
      document.getElementById('expFallas').textContent = aprendiz.fallas;
      document.getElementById('expJornada').textContent = aprendiz.jornada;
      document.getElementById('expFechas').textContent = `${aprendiz.fechaInicio} → ${aprendiz.fechaFin}`;
      document.getElementById('expCorreo').textContent = aprendiz.correo;
      document.getElementById('expTelefono').textContent = aprendiz.telefono;
      document.getElementById('expCalificaciones').innerHTML = aprendiz.calificaciones.map(c => `<tr><td>${escapeHtml(c.competencia)}</td><td class="fw-bold">${escapeHtml(c.resultado)}</td><td><span class="badge bg-success-subtle text-success border border-success">${escapeHtml(c.estado)}</span></td></tr>`).join('');
      document.getElementById('expNovedades').innerHTML = aprendiz.novedades.map(n => `<div class="p-2 rounded-3 bg-light border mb-2 small"><i class="fa-solid fa-circle-info text-primary me-2"></i>${escapeHtml(n)}</div>`).join('');
      document.getElementById('expDocumentos').innerHTML = aprendiz.documentos.map(d => `<div class="d-flex justify-content-between align-items-center p-2 border-bottom small"><span><i class="fa-solid fa-file-lines text-secondary me-2"></i>${escapeHtml(d)}</span><span class="badge bg-success-subtle text-success">Registrado</span></div>`).join('');
      document.getElementById('expSolicitudes').innerHTML = aprendiz.solicitudes.map(s => `<div class="p-2 rounded-3 bg-light border mb-2 small"><i class="fa-solid fa-clipboard-check text-success me-2"></i>${escapeHtml(s)}</div>`).join('');
    }

    function imprimirExpediente() {
      const id = document.getElementById('selectAprendizExpediente').value;
      if (!id) { alert('Selecciona primero un aprendiz.'); return; }
      const aprendiz = aprendices.find(a => a.id === id);
      if (!aprendiz) return;
      const ventana = window.open('', '_blank');
      ventana.document.write(`<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Expediente - ${escapeHtml(aprendiz.nombre)}</title><style>body{font-family:Arial,sans-serif;padding:35px;color:#222}h1{color:#007832}h2{border-bottom:1px solid #ddd;padding-bottom:6px}table{width:100%;border-collapse:collapse}td,th{padding:8px;border:1px solid #ddd;text-align:left}.box{border:1px solid #ddd;padding:15px;margin-bottom:15px;border-radius:8px}</style></head><body><h1>SENA · Expediente del Aprendiz</h1><div class="box"><h2>${escapeHtml(aprendiz.nombre)}</h2><p><b>Documento:</b> ${escapeHtml(aprendiz.documento)}</p><p><b>Ficha:</b> ${escapeHtml(aprendiz.ficha)}</p><p><b>Programa:</b> ${escapeHtml(aprendiz.programa)}</p><p><b>Estado:</b> ${escapeHtml(aprendiz.estado)}</p><p><b>Asistencia:</b> ${aprendiz.asistencia}% &nbsp; <b>Promedio:</b> ${aprendiz.promedio} &nbsp; <b>Fallas:</b> ${aprendiz.fallas}</p></div><div class="box"><h2>Resultados de aprendizaje</h2><table><tr><th>Competencia</th><th>Resultado</th><th>Estado</th></tr>${aprendiz.calificaciones.map(c=>`<tr><td>${escapeHtml(c.competencia)}</td><td>${escapeHtml(c.resultado)}</td><td>${escapeHtml(c.estado)}</td></tr>`).join('')}</table></div><div class="box"><h2>Novedades</h2>${aprendiz.novedades.map(n=>`<p>${escapeHtml(n)}</p>`).join('')}</div></body></html>`);
      ventana.document.close();
      ventana.onload = () => ventana.print();
    }

    // UTILITIES
    function updateFilterSelects() {
      const selectFilter = document.getElementById('filterEnvironment');
      const selectReasignar = document.getElementById('reasignarNuevoAmbienteSelect');

      const options = environments.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');

      selectFilter.innerHTML = `<option value="">Todos los Ambientes / Ubicaciones</option><option value="SIN_ASIGNAR">Sin Asignar (Bodega Principal)</option>` + options;
      selectReasignar.innerHTML = `<option value="">Sin Asignar (Enviar a Bodega)</option>` + options;
    }

    function clearNotifications() {
      document.getElementById('notificationsContainer').style.opacity = '0.4';
      document.getElementById('notificationHeaderCount').textContent = '0';
      document.getElementById('notificationHeaderCount').style.display = 'none';
    }

    function addLotRow() {
      const container = document.getElementById('lotsContainer');
      const div = document.createElement('div');
      div.className = 'row g-2 align-items-center mb-2 lot-row';
      div.innerHTML = `
        <div class="col-md-4"><input type="text" class="form-control lot-name" placeholder="Tipo de Elemento" required></div>
        <div class="col-md-3"><input type="number" class="form-control lot-count" placeholder="Cantidad" min="1" required></div>
        <div class="col-md-3">
          <select class="form-select lot-category" required>
            <option value="Cómputo">Cómputo</option>
            <option value="Mobiliario">Mobiliario</option>
            <option value="Audiovisual">Audiovisual</option>
            <option value="Electrónica/Especializado">Electrónica/Especializado</option>
          </select>
        </div>
        <div class="col-md-2 text-center">
          <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeLotRow(this)"><i class="fa-solid fa-trash"></i></button>
        </div>
      `;
      container.appendChild(div);
    }

    function removeLotRow(btn) {
      btn.closest('.lot-row').remove();
    }

    document.getElementById('modalNuevoAmbiente')?.addEventListener('show.bs.modal', cargarFichasEnFormularioAmbiente);

    // CARGAR FICHAS DISPONIBLES AL CREAR UN AMBIENTE
    function cargarFichasEnFormularioAmbiente() {
      const select = document.getElementById('envFicha');
      if (!select) return;

      const disponibles = fichas.filter(f => !environments.some(env => env.fichaId === f.id));
      select.innerHTML = '<option value="">Seleccione una ficha...</option>' +
        disponibles.map(f => `<option value="${f.id}">Ficha ${escapeHtml(f.codigo)} · ${escapeHtml(f.programa)} · ${escapeHtml(f.jornada)}</option>`).join('');

      mostrarResumenFichaAmbiente();
    }

    function mostrarResumenFichaAmbiente() {
      const select = document.getElementById('envFicha');
      const info = document.getElementById('envFichaInfo');
      if (!select || !info) return;
      const ficha = fichas.find(f => f.id === select.value);
      if (!ficha) {
        info.innerHTML = 'Al crear el ambiente, esta ficha quedará vinculada al ambiente y sus aprendices se mostrarán automáticamente en <strong>Fichas y Aprendices</strong>.';
        return;
      }
      const alumnos = aprendices.filter(a => ficha.aprendizIds.includes(a.id) || String(a.ficha || '').startsWith(ficha.codigo));
      info.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i><strong>${escapeHtml(ficha.codigo)}</strong> · ${escapeHtml(ficha.programa)} · <strong>${alumnos.length}</strong> aprendiz(es) asociados.`;
    }

    // CREAR AMBIENTE MASIVO
    document.getElementById('formNuevoAmbiente').addEventListener('submit', (e) => {
      e.preventDefault();
      const newEnvId = `ENV-${Math.floor(100 + Math.random() * 900)}`;
      const nombre = document.getElementById('envNombre').value;
      const bloque = document.getElementById('envBloque').value;
      const instructor = document.getElementById('envInstructor').value;
      const fichaId = document.getElementById('envFicha').value;

      if (!fichaId) {
        alert('Selecciona una ficha para el ambiente.');
        return;
      }

      if (environments.some(env => env.fichaId === fichaId)) {
        alert('La ficha seleccionada ya está asignada a otro ambiente.');
        cargarFichasEnFormularioAmbiente();
        return;
      }

      environments.push({
        id: newEnvId,
        nombre: nombre,
        bloque: bloque,
        instructor: instructor,
        estadoPrestamo: "Disponible",
        solicitante: "",
        observaciones: "Ambiente registrado",
        fichaId: fichaId
      });

      // La ficha seleccionada queda asociada al ambiente y sus aprendices
      // quedan sincronizados para que la relación se vea en Fichas y Aprendices.
      const aprendicesDeFicha = sincronizarAprendicesDeFicha(fichaId);

      const lotRows = document.querySelectorAll('.lot-row');
      let total = 0;

      lotRows.forEach(row => {
        const itemType = row.querySelector('.lot-name').value;
        const count = parseInt(row.querySelector('.lot-count').value);
        const category = row.querySelector('.lot-category').value;

        for (let i = 1; i <= count; i++) {
          inventory.push({
            id: `ITEM-${Date.now()}-${total + i}`,
            placa: `SENA-ACT-${Math.floor(1000 + Math.random() * 9000)}`,
            nombre: `${itemType} #${i}`,
            categoria: category,
            ambienteId: newEnvId,
            vidaUtil: "En Vida Útil",
            estadoFisico: "Excelente"
          });
        }
        total += count;
      });

      renderAll();
      renderFichas();
      renderAprendicesExpediente();
      bootstrap.Modal.getInstance(document.getElementById('modalNuevoAmbiente')).hide();
      document.getElementById('formNuevoAmbiente').reset();
      cargarFichasEnFormularioAmbiente();
      const fichaCreada = fichas.find(f => f.id === fichaId);
      const cantidadAprendices = aprendicesDeFicha.length;
      Swal.fire('Ambiente Creado', `Se creó ${nombre} y se asignó la ficha ${fichaCreada?.codigo || ''}. ${cantidadAprendices} aprendiz(es) de esa ficha quedaron vinculados al ambiente y se muestran en Fichas y Aprendices.`, 'success');
    });

    // AMBIENTE PRESTAMO
    function openPrestamoModal(envId) {
      const env = environments.find(e => e.id === envId);
      if (!env) return;

      document.getElementById('prestamoEnvId').value = env.id;
      document.getElementById('prestamoEnvNombre').value = env.nombre;
      document.getElementById('prestamoEstadoSelect').value = env.estadoPrestamo;
      document.getElementById('prestamoSolicitante').value = env.solicitante || '';
      document.getElementById('prestamoObservaciones').value = env.observaciones || '';

      const modal = new bootstrap.Modal(document.getElementById('modalPrestamo'));
      modal.show();
    }

    function togglePrestamoFields(val) {
      document.getElementById('prestamoDetailsGroup').style.display = val === 'Prestado' ? 'block' : 'none';
    }

    document.getElementById('formPrestamo').addEventListener('submit', (e) => {
      e.preventDefault();
      const envId = document.getElementById('prestamoEnvId').value;
      const estado = document.getElementById('prestamoEstadoSelect').value;
      const solicitante = document.getElementById('prestamoSolicitante').value;
      const obs = document.getElementById('prestamoObservaciones').value;

      const env = environments.find(e => e.id === envId);
      if (env) {
        env.estadoPrestamo = estado;
        env.solicitante = estado === 'Prestado' ? solicitante : '';
        env.observaciones = obs;
        renderAll();
        bootstrap.Modal.getInstance(document.getElementById('modalPrestamo')).hide();
        Swal.fire('Estado Actualizado', `El ambiente ahora está marcado como: ${estado}`, 'success');
      }
    });

    // =====================================================
    // CARGA MASIVA Y REGISTRO MANUAL
    // =====================================================
    let pendingInventoryImport = [];
    let pendingStaffImport = [];

    function normalizarClave(valor) {
      return String(valor ?? '').trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[\s_\-\/]+/g, '');
    }

    function leerArchivoExcel(file, callback) {
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function(e) {
        try {
          const workbook = XLSX.read(e.target.result, { type: 'array' });
          const sheet = workbook.Sheets[workbook.SheetNames[0]];
          callback(XLSX.utils.sheet_to_json(sheet, { defval: '' }));
        } catch (error) {
          alert('No fue posible leer el archivo. Verifica que sea un Excel o CSV válido.');
        }
      };
      reader.readAsArrayBuffer(file);
    }

    function valorPorClave(row, claves) {
      const encontrada = Object.keys(row).find(k => claves.includes(normalizarClave(k)));
      return encontrada !== undefined ? String(row[encontrada] ?? '').trim() : '';
    }

    function previsualizarInventario(event) {
      const file = event.target.files[0];
      if (!file) return;
      leerArchivoExcel(file, rows => {
        pendingInventoryImport = rows.map((r, index) => ({
          placa: valorPorClave(r, ['placa','codigo','codigosena','placasena']),
          nombre: valorPorClave(r, ['nombre','elemento','descripcion','descripcionelemento']),
          categoria: valorPorClave(r, ['categoria','clase','tipo']),
          ambiente: valorPorClave(r, ['ambiente','ubicacion','ubicacionactual']),
          vidaUtil: valorPorClave(r, ['vidautil','estado','estadovida']) || 'En Vida Útil',
          estadoFisico: valorPorClave(r, ['estadofisico','condicion','estadoelemento']) || 'Bueno'
        })).filter(r => r.placa || r.nombre);
        const tbody = document.getElementById('inventarioPreviewBody');
        tbody.innerHTML = pendingInventoryImport.length ? pendingInventoryImport.map(r => `<tr><td>${escapeHtml(r.placa)}</td><td>${escapeHtml(r.nombre)}</td><td>${escapeHtml(r.categoria || 'Sin categoría')}</td><td>${escapeHtml(r.ambiente || 'Bodega')}</td><td>${escapeHtml(r.vidaUtil)}</td><td>${escapeHtml(r.estadoFisico)}</td></tr>`).join('') : '<tr><td colspan="6" class="text-center text-danger py-4">No se encontraron registros válidos.</td></tr>';
        document.getElementById('inventarioCargaInfo').textContent = `${pendingInventoryImport.length} registro(s) listo(s) para importar.`;
      });
    }

    function confirmarCargaInventario() {
      if (!pendingInventoryImport.length) return alert('Selecciona primero un archivo con registros.');
      let agregados = 0, duplicados = 0;
      pendingInventoryImport.forEach((r, index) => {
        if (!r.placa || !r.nombre) return;
        if (inventory.some(i => normalizarClave(i.placa) === normalizarClave(r.placa))) { duplicados++; return; }
        const env = environments.find(e => normalizarClave(e.nombre) === normalizarClave(r.ambiente) || normalizarClave(e.id) === normalizarClave(r.ambiente));
        inventory.push({ id: `IMPORT-ITEM-${Date.now()}-${index}`, placa: r.placa, nombre: r.nombre, categoria: r.categoria || 'Sin categoría', ambienteId: env ? env.id : null, vidaUtil: r.vidaUtil, estadoFisico: r.estadoFisico });
        agregados++;
      });
      renderAll();
      pendingInventoryImport = [];
      document.getElementById('archivoInventario').value = '';
      document.getElementById('inventarioPreviewBody').innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Selecciona un archivo para previsualizarlo.</td></tr>';
      document.getElementById('inventarioCargaInfo').textContent = '';
      bootstrap.Modal.getInstance(document.getElementById('modalCargaInventario')).hide();
      alert(`Carga finalizada. ${agregados} elemento(s) agregado(s)${duplicados ? ` y ${duplicados} duplicado(s) omitido(s)` : ''}.`);
    }

    function agregarInventarioIndividual() {
      const form = document.getElementById('formNuevoInventario');
      if (!form.checkValidity()) { form.reportValidity(); return; }
      const placa = document.getElementById('newInvPlaca').value.trim();
      if (inventory.some(i => normalizarClave(i.placa) === normalizarClave(placa))) return alert('Ya existe un elemento con esa placa/código SENA.');
      inventory.push({
        id: `ITEM-MAN-${Date.now()}`, placa,
        nombre: document.getElementById('newInvNombre').value.trim(),
        categoria: document.getElementById('newInvCategoria').value.trim(),
        ambienteId: document.getElementById('newInvAmbiente').value || null,
        vidaUtil: document.getElementById('newInvVida').value,
        estadoFisico: document.getElementById('newInvEstado').value.trim() || 'Bueno'
      });
      renderAll(); form.reset(); document.getElementById('newInvVida').value = 'En Vida Útil'; document.getElementById('newInvEstado').value = 'Bueno';
      bootstrap.Modal.getInstance(document.getElementById('modalNuevoInventario')).hide();
    }

    function cargarAmbientesEnFormularioInventario() {
      const select = document.getElementById('newInvAmbiente');
      if (!select) return;
      select.innerHTML = '<option value="">Sin asignar - Bodega</option>' + environments.map(e => `<option value="${e.id}">${escapeHtml(e.nombre)}</option>`).join('');
    }

    function previsualizarPersonal(event) {
      const file = event.target.files[0];
      if (!file) return;
      leerArchivoExcel(file, rows => {
        pendingStaffImport = rows.map((r, index) => ({
          id: valorPorClave(r, ['id','codigo','idpersonal']) || `INST-IMP-${index + 1}`,
          nombre: valorPorClave(r, ['nombre','nombrecompleto','personal']),
          area: valorPorClave(r, ['area','programa','dependencia']),
          documento: valorPorClave(r, ['documento','cedula','cc','numerodocumento'])
        })).filter(r => r.nombre);
        const tbody = document.getElementById('personalPreviewBody');
        tbody.innerHTML = pendingStaffImport.length ? pendingStaffImport.map(r => `<tr><td>${escapeHtml(r.id)}</td><td>${escapeHtml(r.nombre)}</td><td>${escapeHtml(r.area || 'Sin área')}</td><td>${escapeHtml(r.documento || 'Sin documento')}</td></tr>`).join('') : '<tr><td colspan="4" class="text-center text-danger py-4">No se encontraron registros válidos.</td></tr>';
        document.getElementById('personalCargaInfo').textContent = `${pendingStaffImport.length} registro(s) listo(s) para importar.`;
      });
    }

    function confirmarCargaPersonal() {
      if (!pendingStaffImport.length) return alert('Selecciona primero un archivo con registros.');
      let agregados = 0, duplicados = 0;
      pendingStaffImport.forEach(r => {
        if (staff.some(s => normalizarClave(s.id) === normalizarClave(r.id) || (r.documento && normalizarClave(s.documento) === normalizarClave(r.documento)))) { duplicados++; return; }
        staff.push({ id: r.id, nombre: r.nombre, area: r.area || 'Sin área', documento: r.documento || 'Sin documento', asistencia: [] });
        agregados++;
      });
      renderPersonalGrid();
      cargarSelectoresAsignacionFicha();
      renderAsignacionesFichas();
      renderFichas();
      pendingStaffImport = [];
      document.getElementById('archivoPersonal').value = '';
      document.getElementById('personalPreviewBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Selecciona un archivo para previsualizarlo.</td></tr>';
      document.getElementById('personalCargaInfo').textContent = '';
      bootstrap.Modal.getInstance(document.getElementById('modalCargaPersonal')).hide();
      alert(`Carga finalizada. ${agregados} persona(s) agregada(s)${duplicados ? ` y ${duplicados} duplicado(s) omitido(s)` : ''}.`);
    }

    function agregarPersonalIndividual() {
      const form = document.getElementById('formNuevoPersonal');
      if (!form.checkValidity()) { form.reportValidity(); return; }
      const id = document.getElementById('newStaffId').value.trim();
      const documento = document.getElementById('newStaffDocumento').value.trim();
      if (staff.some(s => normalizarClave(s.id) === normalizarClave(id) || normalizarClave(s.documento) === normalizarClave(documento))) return alert('Ya existe personal con ese ID o documento.');
      staff.push({ id, nombre: document.getElementById('newStaffNombre').value.trim(), area: document.getElementById('newStaffArea').value.trim(), documento, asistencia: [] });
      renderPersonalGrid();
      cargarSelectoresAsignacionFicha();
      renderAsignacionesFichas();
      renderFichas();
      form.reset();
      bootstrap.Modal.getInstance(document.getElementById('modalNuevoPersonal')).hide();
    }

    function descargarArchivo(nombre, contenido, tipo) {
      const blob = new Blob([contenido], { type: tipo });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = nombre; a.click(); URL.revokeObjectURL(a.href);
    }

    function descargarPlantillaInventario() {
      const ws = XLSX.utils.json_to_sheet([{ placa:'SENA-PC-999', nombre:'Computador ejemplo', categoria:'Cómputo', ambiente:'Ambiente 101 - ADSO', vidaUtil:'En Vida Útil', estadoFisico:'Bueno' }]);
      const wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, ws, 'Inventario'); XLSX.writeFile(wb, 'plantilla_inventario_sena.xlsx');
    }

    function descargarPlantillaPersonal() {
      const ws = XLSX.utils.json_to_sheet([{ id:'INST-04', nombre:'Nombre Apellido', area:'ADSO', documento:'CC 000000000' }]);
      const wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, ws, 'Personal'); XLSX.writeFile(wb, 'plantilla_personal_sena.xlsx');
    }

    function escapeHtml(value) {
      return String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
    }

    document.addEventListener('DOMContentLoaded', cargarAmbientesEnFormularioInventario);

  </script>
</body>
</html>