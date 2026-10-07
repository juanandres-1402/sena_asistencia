<?php
/**
 * Vista integrada desde el proyecto 'controlasistencia'.
 * Protegida con la sesión y los roles del sistema sena_asistencia.
 */
require_once __DIR__ . '/../../includes/sesion.php';
exigirRol(['instructor', 'coordinador']);
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-[#0d0a1a]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard & Gestión Educativa SENA EDU-CRAVE</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#2e1065',
                            darkBg: '#0f0c20',
                            cardBg: '#181332',
                            cardHover: '#211a42',
                            accent: '#c084fc',
                            neonPink: '#f43f5e',
                            neonBlue: '#38bdf8'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0d0a1a;
        }
        ::-webkit-scrollbar-thumb {
            background: #2b224d;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #6d28d9;
        }
        .glow-purple {
            box-shadow: 0 0 25px -5px rgba(139, 92, 246, 0.3);
        }
        .glow-card {
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.5), 0 0 15px -3px rgba(124, 58, 237, 0.15);
        }
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .submenu-transition {
            transition: max-height 0.3s ease-in-out, opacity 0.2s ease-in-out;
        }
        /* SweetAlert Custom Dark Theme Override */
        .swal2-popup {
            background-color: #181332 !important;
            color: #e2e8f0 !important;
            border: 1px solid rgba(124, 58, 237, 0.3) !important;
            border-radius: 1rem !important;
        }
        .swal2-title {
            color: #ffffff !important;
        }
        .swal2-confirm {
            background: linear-gradient(to right, #7c3aed, #8b5cf6) !important;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.4) !important;
            border-radius: 0.75rem !important;
        }
        .swal2-cancel {
            background-color: #2b224d !important;
            color: #cbd5e1 !important;
            border-radius: 0.75rem !important;
        }

        /* =========================================================
           TEMA SENA - DISEÑO ADAPTADO DEL PORTAL ACADÉMICO
        ========================================================== */
        :root {
            --sena-green: #39A900;
            --sena-green-dark: #007832;
            --sena-blue: #00304D;
            --sena-purple: #71277A;
            --sena-cyan: #50E5F9;
            --sena-yellow: #FDC300;
            --sena-bg: #0B1110;
            --sena-card: #101817;
            --sena-input: #17221F;
            --sena-border: rgba(57,169,0,.20);
            --sena-border-focus: rgba(57,169,0,.60);
        }

        html, body {
            background: var(--sena-bg) !important;
            color: #F6F6F6 !important;
        }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        ::-webkit-scrollbar-track { background: var(--sena-bg) !important; }
        ::-webkit-scrollbar-thumb { background: var(--sena-green-dark) !important; }
        ::-webkit-scrollbar-thumb:hover { background: var(--sena-green) !important; }

        /* Sidebar flotante */
        #sidebar {
            position: fixed !important;
            top: 20px !important;
            left: 20px !important;
            bottom: 20px !important;
            width: 82px !important;
            min-width: 82px !important;
            height: auto !important;
            background: var(--sena-card) !important;
            border: 1px solid var(--sena-border) !important;
            border-radius: 28px !important;
            padding: 18px 12px !important;
            box-shadow: 0 20px 50px rgba(0,0,0,.25) !important;
            backdrop-filter: none !important;
        }

        #sidebar > div:first-child {
            width: 100% !important;
        }

        #sidebar > div:first-child > div:first-child {
            height: auto !important;
            padding: 0 !important;
            border: 0 !important;
            justify-content: center !important;
            margin-bottom: 28px !important;
        }

        #brand-logo {
            justify-content: center !important;
        }

        #brand-logo > div:first-child {
            width: 48px !important;
            height: 48px !important;
            border-radius: 16px !important;
            background: var(--sena-green) !important;
            box-shadow: 0 8px 20px rgba(57,169,0,.20) !important;
        }

        #brand-logo .sidebar-text {
            display: none !important;
        }

        #toggle-sidebar {
            display: none !important;
        }

        #sidebar nav {
            padding: 0 !important;
            margin-top: 0 !important;
            max-height: none !important;
            overflow: visible !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 10px !important;
        }

        #sidebar .nav-item,
        #sidebar nav > div > button {
            width: 48px !important;
            height: 48px !important;
            min-width: 48px !important;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 16px !important;
            background: transparent !important;
            color: #A7B2AD !important;
            border: 0 !important;
            justify-content: center !important;
            transition: all .25s ease !important;
        }

        #sidebar .nav-item:hover,
        #sidebar nav > div > button:hover {
            background: var(--sena-input) !important;
            color: #fff !important;
            transform: translateY(-2px) !important;
        }

        #sidebar .nav-item.active {
            background: var(--sena-green) !important;
            color: #fff !important;
            box-shadow: 0 8px 20px rgba(57,169,0,.25) !important;
        }

        #sidebar .nav-item i,
        #sidebar nav > div > button i:first-child {
            color: currentColor !important;
            width: 20px !important;
            height: 20px !important;
        }

        #sidebar .nav-item span,
        #sidebar nav > div > button .sidebar-text,
        #sidebar .sidebar-text {
            display: none !important;
        }

        #aprendices-submenu {
            display: none !important;
        }

        #sidebar > div:last-child {
            padding: 0 !important;
            border: 0 !important;
        }

        #sidebar > div:last-child button {
            width: 48px !important;
            height: 48px !important;
            padding: 0 !important;
            justify-content: center !important;
            border-radius: 16px !important;
            color: #A7B2AD !important;
        }

        #sidebar > div:last-child button:hover {
            background: rgba(113,39,122,.18) !important;
            color: #c58bd0 !important;
        }

        /* Contenido */
        main {
            margin-left: 102px !important;
            background: var(--sena-bg) !important;
        }

        #main-content {
            padding: 28px 30px 40px !important;
            background: var(--sena-bg) !important;
        }

        header {
            height: auto !important;
            min-height: 72px !important;
            margin: 20px 30px 0 !important;
            padding: 14px 20px !important;
            background: var(--sena-card) !important;
            border: 1px solid var(--sena-border) !important;
            border-radius: 22px !important;
            backdrop-filter: none !important;
        }

        #global-search {
            background: var(--sena-input) !important;
            border: 1px solid var(--sena-border) !important;
            color: #F6F6F6 !important;
            border-radius: 14px !important;
        }

        #global-search:focus {
            border-color: var(--sena-green) !important;
            box-shadow: 0 0 0 3px rgba(57,169,0,.08) !important;
        }

        /* Tarjetas */
        #main-content .bg-\[\#181332\] {
            background: var(--sena-card) !important;
            border-color: var(--sena-border) !important;
            box-shadow: 0 12px 35px rgba(0,0,0,.16) !important;
            border-radius: 22px !important;
        }

        #main-content .bg-\[\#181332\]:hover {
            border-color: var(--sena-border-focus) !important;
        }

        #main-content .bg-purple-950\/40,
        #main-content .bg-purple-950\/50,
        #main-content .bg-purple-950\/60,
        #main-content .bg-purple-950\/80,
        #main-content .bg-purple-900\/20,
        #main-content .bg-purple-900\/30 {
            background: var(--sena-input) !important;
        }

        #main-content [class*="border-purple-"] {
            border-color: var(--sena-border) !important;
        }

        /* Títulos y textos */
        #main-content h1,
        #main-content h2,
        #main-content h3,
        #main-content h4 {
            color: #FFFFFF !important;
        }

        #main-content [class*="text-purple-400"],
        #main-content [class*="text-purple-300"] {
            color: #A7B2AD !important;
        }

        #main-content [class*="text-purple-200"] {
            color: #F6F6F6 !important;
        }

        /* Verde institucional */
        #main-content [class*="text-brand-"] {
            color: var(--sena-green) !important;
        }

        #main-content [class*="bg-brand-"] {
            background: var(--sena-green) !important;
        }

        #main-content [class*="border-brand-"] {
            border-color: var(--sena-green) !important;
        }

        #main-content [class*="from-brand-"] {
            --tw-gradient-from: var(--sena-green) !important;
        }

        #main-content [class*="to-brand-"] {
            --tw-gradient-to: var(--sena-green-dark) !important;
        }

        /* Inputs */
        #main-content input,
        #main-content select,
        #main-content textarea {
            background: var(--sena-input) !important;
            color: #F6F6F6 !important;
            border-color: var(--sena-border) !important;
            border-radius: 12px !important;
        }

        #main-content input:focus,
        #main-content select:focus,
        #main-content textarea:focus {
            border-color: var(--sena-green) !important;
            box-shadow: 0 0 0 3px rgba(57,169,0,.08) !important;
        }

        /* Tablas */
        #main-content table thead {
            background: rgba(23,34,31,.8) !important;
        }

        #main-content table th {
            color: #7F8D87 !important;
            border-color: var(--sena-border) !important;
        }

        #main-content table td {
            border-color: rgba(57,169,0,.08) !important;
        }

        #main-content table tbody tr:hover {
            background: rgba(57,169,0,.035) !important;
        }

        /* Botones */
        #main-content button.bg-gradient-to-r {
            background: var(--sena-green) !important;
            box-shadow: 0 8px 20px rgba(57,169,0,.20) !important;
        }

        #main-content button.bg-gradient-to-r:hover {
            background: var(--sena-green-dark) !important;
        }

        /* Responsive */
        @media (max-width: 700px) {
            body {
                padding-bottom: 95px !important;
            }

            #sidebar {
                position: fixed !important;
                top: auto !important;
                left: 15px !important;
                right: 15px !important;
                bottom: 15px !important;
                width: auto !important;
                height: 65px !important;
                min-width: 0 !important;
                padding: 8px 12px !important;
                flex-direction: row !important;
                border-radius: 20px !important;
            }

            #brand-logo,
            #sidebar > div:first-child > div:first-child {
                margin: 0 !important;
            }

            #brand-logo > div:first-child {
                width: 43px !important;
                height: 43px !important;
            }

            #sidebar nav {
                flex-direction: row !important;
                justify-content: center !important;
                gap: 4px !important;
                width: 100% !important;
            }

            #sidebar .nav-item,
            #sidebar nav > div > button {
                width: 42px !important;
                height: 42px !important;
                min-width: 42px !important;
                border-radius: 13px !important;
            }

            #sidebar .nav-item:nth-child(n+5) {
                display: none !important;
            }

            #sidebar > div:last-child {
                margin-left: auto !important;
            }

            main {
                margin-left: 0 !important;
            }

            header {
                margin: 15px !important;
                border-radius: 18px !important;
            }

            #main-content {
                padding: 20px 15px 95px !important;
            }
        }

    

/* ===== NAVEGACION LATERAL EXPANDIDA ===== */
#sidebar {
    width: 250px !important;
    min-width: 250px !important;
}
#sidebar > div:first-child > div:first-child {
    justify-content: flex-start !important;
    padding: 0 6px !important;
}
#brand-logo {
    justify-content: flex-start !important;
}
#brand-logo .sidebar-text {
    display: inline-flex !important;
}
#sidebar nav {
    align-items: stretch !important;
}
#sidebar .nav-item,
#sidebar nav > div > button {
    width: 100% !important;
    min-width: 0 !important;
    height: 48px !important;
    padding: 0 14px !important;
    justify-content: flex-start !important;
    gap: 12px !important;
}
#sidebar .nav-item .sidebar-text,
#sidebar nav > div > button .sidebar-text {
    display: inline-flex !important;
    opacity: 1 !important;
    visibility: visible !important;
}
#sidebar #aprendices-submenu {
    display: block !important;
    width: 100% !important;
}
#sidebar #aprendices-submenu .sidebar-text {
    display: inline-flex !important;
}
#sidebar .border-t {
    width: 100% !important;
}
#sidebar + main {
    margin-left: 270px !important;
}
@media (max-width: 768px) {
    #sidebar {
        width: calc(100% - 32px) !important;
        min-width: 0 !important;
    }
    #brand-logo .sidebar-text,
    #sidebar .nav-item .sidebar-text,
    #sidebar nav > div > button .sidebar-text,
    #sidebar .border-t .sidebar-text {
        display: none !important;
    }
    #sidebar nav {
        align-items: center !important;
        flex-direction: row !important;
        justify-content: space-around !important;
    }
    #sidebar .nav-item,
    #sidebar nav > div > button {
        width: 48px !important;
        min-width: 48px !important;
        padding: 0 !important;
        justify-content: center !important;
    }
    #sidebar + main {
        margin-left: 0 !important;
    }
}
/* ===== AJUSTES SOLICITADOS: NAVEGACION EXPANDIDA Y CALIFICACIONES ===== */
#sidebar .sidebar-text {
    display: inline-flex !important;
    align-items: center;
}
#sidebar .nav-item,
#sidebar .sidebar-footer button {
    width: 100% !important;
}
#sidebar .nav-item {
    justify-content: flex-start !important;
}
#sidebar .nav-item .sidebar-text {
    overflow: visible !important;
    opacity: 1 !important;
    visibility: visible !important;
}
#sidebar #aprendices-submenu {
    display: block !important;
}
#sidebar #aprendices-submenu .sidebar-text {
    display: inline-flex !important;
}
#sidebar .nav-item:hover {
    transform: translateX(2px);
}
@media (max-width: 768px) {
    #sidebar {
        width: calc(100% - 32px) !important;
        left: 16px !important;
        right: 16px !important;
        top: auto !important;
        bottom: 16px !important;
        height: auto !important;
        min-height: 68px !important;
        border-radius: 22px !important;
        overflow-x: auto !important;
    }
    #sidebar .sidebar-text {
        display: none !important;
    }
    #sidebar .nav-item {
        width: auto !important;
        min-width: 52px !important;
        justify-content: center !important;
    }
    #sidebar #aprendices-submenu {
        display: none !important;
    }
    #sidebar + main {
        margin-left: 0 !important;
        padding-bottom: 100px !important;
    }
}

        /* NAVEGACIÓN SENA EXPANDIDA: icono + nombre de función */
        #sidebar { width: 250px !important; min-width: 250px !important; padding: 18px 14px !important; }
        #sidebar #brand-logo { justify-content: flex-start !important; }
        #sidebar #brand-logo .sidebar-text { display:flex !important; }
        #sidebar #sidebar-text { display:flex !important; }
        #sidebar .nav-item, #sidebar nav > div > button { width:100% !important; min-width:0 !important; height:48px !important; padding:0 14px !important; justify-content:flex-start !important; }
        #sidebar .nav-item span, #sidebar nav > div > button .sidebar-text { display:inline-flex !important; }
        #sidebar .nav-item i, #sidebar nav > div > button i:first-child { flex-shrink:0 !important; }
        #sidebar nav > div > button { justify-content:space-between !important; }
        #sidebar nav > div > button > div { display:flex !important; align-items:center !important; gap:12px !important; }
        #aprendices-submenu { display:block !important; }
        main { margin-left:270px !important; }
        @media (max-width: 900px) {
            #sidebar { width:82px !important; min-width:82px !important; }
            #sidebar #brand-logo .sidebar-text, #sidebar .nav-item span, #sidebar nav > div > button .sidebar-text { display:none !important; }
            #sidebar .nav-item, #sidebar nav > div > button { width:48px !important; padding:0 !important; justify-content:center !important; }
            #sidebar nav > div > button > div { gap:0 !important; }
            main { margin-left:102px !important; }
            #aprendices-submenu { display:none !important; }
        }

        
</style>
</head>
<body class="h-full text-slate-200 bg-[#0d0a1a] flex overflow-hidden">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="sidebar-transition w-64 bg-[#140f2a]/90 backdrop-blur-xl border-r border-purple-900/30 flex flex-col justify-between z-30 relative shrink-0">
        <div>
            <!-- Sidebar Header -->
            <div class="h-20 flex items-center justify-between px-4 border-b border-purple-900/20">
                <div class="flex items-center gap-3 overflow-hidden" id="brand-logo">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-brand-400 flex items-center justify-center shadow-lg shadow-brand-600/40 shrink-0">
                        <i data-lucide="graduation-cap" class="text-white w-6 h-6"></i>
                    </div>
                    <div class="sidebar-text flex flex-col">
                        <span class="font-bold text-lg tracking-wider text-white whitespace-nowrap bg-gradient-to-r from-white via-purple-200 to-brand-400 bg-clip-text text-transparent">
                            EDU-SENA
                        </span>
                        <span class="text-[10px] text-purple-400 font-semibold uppercase tracking-widest">Gestión Académica</span>
                    </div>
                </div>
                <button id="toggle-sidebar" class="p-2 rounded-xl bg-purple-950/60 hover:bg-brand-600/40 text-purple-300 hover:text-white transition-all border border-purple-800/40 focus:outline-none">
                    <i data-lucide="chevron-left" id="toggle-icon" class="w-5 h-5 transition-transform duration-300"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-3 space-y-1.5 mt-2 overflow-y-auto max-h-[calc(100vh-160px)]">
                
                <!-- Dashboard Nav -->
                <a href="#" onclick="switchView('dashboard')" id="nav-dashboard" class="nav-item active flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Dashboard</span>
                </a>

                <!-- NUEVO: Entrega de Ambientes -->
                <a href="#" onclick="switchView('ambientes')" id="nav-ambientes" class="nav-item flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                    <i data-lucide="building-2" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Entrega de Ambientes</span>
                </a>

                <!-- NUEVO: Control Entrada/Salida Instructor -->
                <a href="#" onclick="switchView('marcaciones')" id="nav-marcaciones" class="nav-item flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                    <i data-lucide="clock" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Control de Asistencia</span>
                </a>

                <!-- Aprendices Sub-Menu Parent -->
                <div class="relative">
                    <button onclick="toggleSubmenu('aprendices-submenu')" id="nav-aprendices-btn" class="w-full flex items-center justify-between px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="users" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                            <span class="sidebar-text font-medium text-sm whitespace-nowrap">Aprendices</span>
                        </div>
                        <i data-lucide="chevron-down" id="aprendices-arrow" class="w-4 h-4 sidebar-text transition-transform duration-200 text-purple-400"></i>
                    </button>

                    <!-- Submenu Items -->
                    <div id="aprendices-submenu" class="max-h-0 opacity-0 overflow-hidden submenu-transition pl-9 pr-2 space-y-1 mt-1">
                        <a href="#" onclick="switchView('aprendices'); filterAprendices('todos')" class="block py-2 px-3 text-xs font-medium text-purple-300/70 hover:text-white hover:bg-purple-900/30 rounded-lg transition-colors">
                            <i data-lucide="list" class="w-3.5 h-3.5 inline mr-1.5"></i> Ver Todos
                        </a>
                        <a href="#" onclick="openCreateModal()" class="block py-2 px-3 text-xs font-medium text-purple-300/70 hover:text-white hover:bg-purple-900/30 rounded-lg transition-colors">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5 inline mr-1.5"></i> Registrar Nuevo
                        </a>
                        <a href="#" onclick="switchView('aprendices'); filterAprendices('riesgo')" class="block py-2 px-3 text-xs font-medium text-purple-300/70 hover:text-white hover:bg-purple-900/30 rounded-lg transition-colors">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 inline mr-1.5 text-amber-400"></i> En Riesgo
                        </a>
                    </div>
                </div>

                <a href="#" onclick="switchView('transversales')" id="nav-transversales" class="nav-item flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                    <i data-lucide="book-open" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Transversales</span>
                </a>
                <a href="#" onclick="switchView('instructor-fichas')" id="nav-instructor-fichas" class="nav-item flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                    <i data-lucide="presentation" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Fichas del Instructor</span>
                </a>
                <a href="#" onclick="switchView('jefatura-ficha')" id="nav-jefatura-ficha" class="nav-item flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                    <i data-lucide="shield-check" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Jefe de Ficha</span>
                </a>

                <!-- Eventos / Calendario -->
                <a href="#" onclick="switchView('eventos')" id="nav-eventos" class="nav-item flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                    <i data-lucide="calendar" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Eventos & Agenda</span>
                </a>

                <!-- Gestión de Notas / Calificaciones -->
                <a href="#" onclick="switchView('notas')" id="nav-notas" class="nav-item flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group">
                    <i data-lucide="award" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Gestión de Notas</span>
                </a>

                <!-- Notificaciones Center -->
                <a href="#" onclick="switchView('notificaciones')" id="nav-notificaciones" class="nav-item flex items-center gap-3 px-3.5 py-3 rounded-xl text-purple-200/80 hover:bg-brand-600/20 hover:text-white transition-all group relative">
                    <i data-lucide="bell" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="sidebar-text font-medium text-sm whitespace-nowrap">Notificaciones</span>
                    <span class="sidebar-text bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-auto" id="notif-badge-count">4</span>
                </a>

            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-purple-900/20">
            <button onclick="confirmLogout()" class="w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-rose-400 hover:bg-rose-500/10 transition-all">
                <i data-lucide="log-out" class="w-5 h-5 shrink-0"></i>
                <span class="sidebar-text font-medium text-sm whitespace-nowrap">Cerrar Sesión</span>
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-[#0d0a1a]">
        
        <!-- TOP HEADER NAVBAR -->
        <header class="h-20 border-b border-purple-900/30 px-6 flex items-center justify-between bg-[#140f2a]/50 backdrop-blur-md shrink-0">
            <div class="flex items-center gap-4 w-1/3">
                <div class="relative w-full">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-400"></i>
                    <input type="text" id="global-search" placeholder="Buscar ambiente, instructor, aprendiz o nota..." class="w-full bg-purple-950/40 border border-purple-800/40 rounded-xl pl-10 pr-4 py-2 text-sm text-purple-100 placeholder-purple-400/60 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all">
                </div>
            </div>

            <!-- Top Actions & Profile -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2 bg-purple-900/20 border border-purple-800/30 px-3 py-1.5 rounded-full text-xs text-purple-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span id="current-timestamp-display">Real-Time Clock</span>
                </div>

                <button onclick="switchView('notificaciones')" class="relative p-2.5 rounded-xl bg-purple-950/50 hover:bg-purple-900/50 border border-purple-800/40 text-purple-300 hover:text-white transition-all">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-brand-500 rounded-full ring-2 ring-[#0d0a1a]"></span>
                </button>

                <!-- User Profile -->
                <div class="flex items-center gap-3 pl-2 border-l border-purple-900/40">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150" alt="Avatar Instructor" class="w-10 h-10 rounded-xl object-cover ring-2 ring-brand-500/50">
                    <div class="hidden sm:block text-left">
                        <h4 class="text-sm font-semibold text-white leading-tight">Valeria Gómez</h4>
                        <p class="text-xs text-purple-400">Instructora Líder SENA</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- DYNAMIC CONTENT CONTAINER -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6" id="main-content">

            <!-- VIEW 1: DASHBOARD MAIN OVERVIEW -->
            <section id="view-dashboard" class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Panel Principal SENA</h1>
                        <p class="text-xs text-purple-400 mt-1">Resumen general de gestión de ambientes, asistencias y rendimiento académico.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="switchView('ambientes')" class="bg-purple-900/40 hover:bg-purple-800/50 text-purple-200 border border-purple-700/50 font-medium px-4 py-2.5 rounded-xl text-sm flex items-center gap-2 transition-all">
                            <i data-lucide="building-2" class="w-4 h-4 text-brand-400"></i> Entregar Ambiente
                        </button>
                        <button onclick="switchView('marcaciones')" class="bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 text-white font-medium px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-brand-600/30 flex items-center gap-2 transition-all">
                            <i data-lucide="clock" class="w-4 h-4"></i> Registrar Marcación
                        </button>
                    </div>
                </div>

                <!-- KPI Cards Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card relative overflow-hidden group hover:border-brand-500/50 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-purple-400">Ambientes Verificados</span>
                            <div class="p-2.5 rounded-xl bg-brand-600/20 text-brand-400 border border-brand-500/30">
                                <i data-lucide="building-2" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-3xl font-bold text-white tracking-tight" id="kpi-ambientes-count">12 / 14</h3>
                            <div class="flex items-center gap-2 mt-2 text-xs text-emerald-400 font-medium">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>85% inventariado hoy</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card relative overflow-hidden group hover:border-brand-500/50 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-purple-400">Horas Dictadas Mes</span>
                            <div class="p-2.5 rounded-xl bg-purple-600/20 text-purple-300 border border-purple-500/30">
                                <i data-lucide="hourglass" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-3xl font-bold text-white tracking-tight" id="kpi-horas-total">142 hrs</h3>
                            <div class="flex items-center gap-2 mt-2 text-xs text-brand-400 font-medium">
                                <span>100% de cumplimiento</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card relative overflow-hidden group hover:border-brand-500/50 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-purple-400">Total Aprendices</span>
                            <div class="p-2.5 rounded-xl bg-emerald-600/20 text-emerald-400 border border-emerald-500/30">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-3xl font-bold text-white tracking-tight" id="kpi-aprendices-count">482</h3>
                            <div class="flex items-center gap-2 mt-2 text-xs text-emerald-400 font-medium">
                                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                                <span>94% Asistencia promedio</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card relative overflow-hidden group hover:border-brand-500/50 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-purple-400">Novedades de Equipos</span>
                            <div class="p-2.5 rounded-xl bg-amber-600/20 text-amber-400 border border-amber-500/30">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-3xl font-bold text-amber-400 tracking-tight" id="kpi-novedades-count">3 Equipos</h3>
                            <div class="flex items-center gap-2 mt-2 text-xs text-amber-300 font-medium">
                                <span>Requieren mantenimiento</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-[#181332] border border-purple-900/30 p-6 rounded-2xl glow-card">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-semibold text-white">Registro de Horas Dictadas x Semana</h3>
                                <p class="text-xs text-purple-400">Cumplimiento de horas presenciales e instructoría</p>
                            </div>
                        </div>
                        <div class="h-64 w-full">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-[#181332] border border-purple-900/30 p-6 rounded-2xl glow-card flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-base font-semibold text-white">Estado de Ambientes</h3>
                            </div>
                            <p class="text-xs text-purple-400 mb-4">Estado físico de los equipos e inventario.</p>
                            <div class="h-48 relative flex items-center justify-center">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center pt-4 border-t border-purple-900/30 mt-4">
                            <div>
                                <p class="text-xs text-purple-400">Optimo</p>
                                <p class="text-sm font-bold text-purple-300">85%</p>
                            </div>
                            <div>
                                <p class="text-xs text-purple-400">Regular</p>
                                <p class="text-sm font-bold text-amber-400">10%</p>
                            </div>
                            <div>
                                <p class="text-xs text-purple-400">Defectuoso</p>
                                <p class="text-sm font-bold text-rose-400">5%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- VIEW 2: MÓDULO DE GESTIÓN Y ENTREGA DE AMBIENTES DE FORMACIÓN -->
            <section id="view-ambientes" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Entrega y Verificación de Ambientes</h1>
                        <p class="text-xs text-purple-400 mt-1">Inspección de inventario, equipos tecnológicos, novedades y firma digital de entrega.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="exportarReporteAmbientes()" class="bg-purple-900/40 hover:bg-purple-800/50 text-purple-200 border border-purple-700/50 font-medium px-4 py-2.5 rounded-xl text-sm flex items-center gap-2 transition-all">
                            <i data-lucide="download" class="w-4 h-4 text-brand-400"></i> Exportar Bitácora
                        </button>
                    </div>
                </div>

                <!-- Environment Selection Header -->
                <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-purple-300 mb-1.5">Seleccionar Ambiente de Formación</label>
                        <select id="ambiente-select" onchange="cargarInventarioAmbiente()" class="w-full bg-purple-950/80 border border-purple-800/50 text-xs text-purple-100 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-brand-500">
                            <option value="Aula 102 ADSO">Aula 102 - Desarrollo de Software (ADSO)</option>
                            <option value="Laboratorio Hardware 204">Laboratorio Hardware 204 - Redes & Mantenimiento</option>
                            <option value="Ambiente Multimedia 301">Ambiente Multimedia 301 - Diseño & Animación</option>
                            <option value="Taller de Electrónica 105">Taller de Electrónica 105</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-purple-300 mb-1.5">Ficha Asignada</label>
                        <input type="text" id="ambiente-ficha" value="284910 - ADSO" class="w-full bg-purple-950/60 border border-purple-800/40 text-xs text-purple-200 rounded-xl px-3.5 py-2.5 focus:outline-none" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-purple-300 mb-1.5">Fecha y Hora de Inspección</label>
                        <input type="text" id="ambiente-timestamp" class="w-full bg-purple-950/60 border border-purple-800/40 text-xs text-brand-300 font-mono rounded-xl px-3.5 py-2.5 focus:outline-none" readonly>
                    </div>
                </div>

                <!-- Form & Inventory Table Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left: Table of Inventory Elements -->
                    <div class="lg:col-span-2 bg-[#181332] border border-purple-900/30 rounded-2xl overflow-hidden glow-card flex flex-col">
                        <div class="p-4 bg-purple-950/50 border-b border-purple-900/40 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <i data-lucide="box" class="w-4 h-4 text-brand-400"></i> Inventario de Hardware y Mobiliario
                            </h3>
                            <button onclick="agregarElementoInventario()" class="text-xs text-brand-400 hover:text-brand-300 flex items-center gap-1 font-medium">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Añadir Elemento
                            </button>
                        </div>

                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-left text-xs" id="tabla-inventario-ambiente">
                                <thead class="text-purple-300 border-b border-purple-900/40 uppercase tracking-wider bg-purple-950/30">
                                    <tr>
                                        <th class="p-3.5">Elemento</th>
                                        <th class="p-3.5 text-center">Cant. Esperada</th>
                                        <th class="p-3.5 text-center">Cant. Verificada</th>
                                        <th class="p-3.5">Estado Físico</th>
                                        <th class="p-3.5">Observación / Novedad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-purple-900/20 text-purple-200" id="inventario-tbody">
                                    <!-- Dynamic rows from JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right: Handover details form & Digital Signature -->
                    <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card space-y-4">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-purple-900/30 pb-3">
                            <i data-lucide="clipboard-check" class="w-4 h-4 text-brand-400"></i> Registro de Acta de Entrega
                        </h3>

                        <form id="form-entrega-ambiente" onsubmit="guardarEntregaAmbiente(event)" class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-medium text-purple-300 mb-1">Instructor / Encargado Entrega</label>
                                <input type="text" id="entrega-remitente" value="Valeria Gómez (Instructor)" required class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-purple-300 mb-1">Recibe (Guarda / Celador / Instructor Turno)</label>
                                <input type="text" id="entrega-receptor" placeholder="Ej. Carlos Ruiz (Vigilancia / Turno Tarde)" required class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3 py-2 text-xs text-white placeholder-purple-400/50 focus:outline-none focus:border-brand-500">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-purple-300 mb-1">Observaciones Generales del Ambiente</label>
                                <textarea id="entrega-observaciones" rows="3" placeholder="Detalles de aseo, luces apagadas, aires acondicionados..." class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl p-3 text-xs text-white placeholder-purple-400/50 focus:outline-none focus:border-brand-500"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-purple-300 mb-1">Confirmación Digital</label>
                                <div class="bg-purple-950/80 border border-purple-800/40 rounded-xl p-3 text-center space-y-2">
                                    <div class="flex items-center justify-center gap-2 text-xs text-emerald-400 font-semibold">
                                        <i data-lucide="shield-check" class="w-4 h-4"></i> Firma Electrónica Validada
                                    </div>
                                    <p class="text-[10px] text-purple-400">Al presionar Registrar, se genera un acta inalterable con sello de tiempo dinámico.</p>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 shadow-lg shadow-brand-600/30 flex items-center justify-center gap-2 transition-all">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i> Finalizar y Firmar Entrega
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Handover History Table -->
                <div class="bg-[#181332] border border-purple-900/30 rounded-2xl overflow-hidden glow-card">
                    <div class="p-4 bg-purple-950/40 border-b border-purple-900/40 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i data-lucide="history" class="w-4 h-4 text-brand-400"></i> Historial Reciente de Entregas de Ambientes
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-purple-300 border-b border-purple-900/40 uppercase tracking-wider bg-purple-950/50">
                                <tr>
                                    <th class="p-4">Ambiente</th>
                                    <th class="p-4">Fecha & Hora</th>
                                    <th class="p-4">Entrega</th>
                                    <th class="p-4">Recibe</th>
                                    <th class="p-4">Estado Inventario</th>
                                    <th class="p-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-purple-900/20 text-purple-200" id="historial-ambientes-tbody">
                                <!-- Dynamic JS History -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- VIEW 3: CONTROL DE ENTRADA Y SALIDA DEL INSTRUCTOR -->
            <section id="view-marcaciones" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Control de Entrada y Salida - Instructores</h1>
                        <p class="text-xs text-purple-400 mt-1">Bitácora de asistencia en tiempo real, registro geolocalizado de horas y fichas asignadas.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="exportarReporteMarcaciones()" class="bg-purple-900/40 hover:bg-purple-800/50 text-purple-200 border border-purple-700/50 font-medium px-4 py-2.5 rounded-xl text-sm flex items-center gap-2 transition-all">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i> Descargar Excel / Reporte
                        </button>
                    </div>
                </div>

                <!-- Clock / Direct Check-In Panel -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Direct Check-In Widget -->
                    <div class="bg-gradient-to-br from-[#1d163d] to-[#140f2a] border border-purple-800/40 p-6 rounded-2xl glow-card space-y-5 text-center relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/10 rounded-full blur-2xl"></div>
                        
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-600/20 border border-brand-500/30 text-brand-300 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Reloj Biométrico Digital
                        </div>

                        <div>
                            <p class="text-xs text-purple-400 uppercase tracking-widest font-bold">Hora Oficial Colombia</p>
                            <h2 class="text-4xl font-extrabold text-white tracking-wider mt-1 font-mono" id="clock-large">00:00:00 PM</h2>
                            <p class="text-xs text-purple-300 mt-1" id="date-large">Cargando fecha...</p>
                        </div>

                        <!-- Check-in Action Box -->
                        <div class="bg-purple-950/60 border border-purple-900/40 p-4 rounded-xl space-y-3 text-left">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-purple-400 block text-[10px]">Cédula Instructor</span>
                                    <span class="text-white font-bold font-mono">1.018.293.811</span>
                                </div>
                                <div>
                                    <span class="text-purple-400 block text-[10px]">Ficha / Ambiente</span>
                                    <span class="text-brand-300 font-bold">284910 - Aula 102</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <button onclick="registrarMarcacion('Entrada')" class="py-3 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all">
                                <i data-lucide="log-in" class="w-4 h-4"></i> Registrar Entrada
                            </button>
                            <button onclick="registrarMarcacion('Salida')" class="py-3 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 shadow-lg shadow-rose-600/30 flex items-center justify-center gap-2 transition-all">
                                <i data-lucide="log-out" class="w-4 h-4"></i> Registrar Salida
                            </button>
                        </div>
                    </div>

                    <!-- Summary KPI & Metrics of Instructor Hours -->
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-purple-400">Total Horas Registradas Este Mes</span>
                                <i data-lucide="clock" class="w-5 h-5 text-brand-400"></i>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-3xl font-bold text-white">142.5 hrs</h3>
                                <p class="text-xs text-emerald-400 mt-1">Cumplimiento del 100% de la carga lectiva</p>
                            </div>
                            <div class="w-full bg-purple-950 rounded-full h-2 mt-4 overflow-hidden">
                                <div class="bg-gradient-to-r from-brand-500 to-emerald-400 h-2 rounded-full w-[100%]"></div>
                            </div>
                        </div>

                        <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-purple-400">Puntualidad en Marcaciones</span>
                                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-3xl font-bold text-emerald-400">98.2%</h3>
                                <p class="text-xs text-purple-300 mt-1">2 marcaciones con observación menor</p>
                            </div>
                            <div class="w-full bg-purple-950 rounded-full h-2 mt-4 overflow-hidden">
                                <div class="bg-emerald-400 h-2 rounded-full w-[98%]"></div>
                            </div>
                        </div>

                        <div class="sm:col-span-2 bg-[#181332] border border-purple-900/30 p-4 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 rounded-xl bg-purple-900/30 border border-purple-700/40 text-brand-400">
                                    <i data-lucide="info" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-white">Reglamento de Marcaciones Instructores SENA</h4>
                                    <p class="text-[11px] text-purple-400">Recuerda realizar el registro al inicio y fin de la jornada de formación.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Log Table with Search and Filters -->
                <div class="bg-[#181332] border border-purple-900/30 rounded-2xl overflow-hidden glow-card space-y-4 p-4">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-purple-900/30 pb-4">
                        <div class="relative w-full sm:w-72">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-400"></i>
                            <input type="text" id="search-marcacion" onkeyup="filtrarTablaMarcaciones()" placeholder="Buscar por ficha, estado o ambiente..." class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl pl-10 pr-4 py-2 text-xs text-purple-100 placeholder-purple-400/60 focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="flex items-center gap-2">
                            <select id="filter-marcacion-tipo" onchange="filtrarTablaMarcaciones()" class="bg-purple-950/60 border border-purple-800/40 text-xs text-purple-200 rounded-xl px-3 py-2 focus:outline-none">
                                <option value="todos">Todos los Tipos</option>
                                <option value="Entrada">Entrada</option>
                                <option value="Salida">Salida</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs" id="tabla-marcaciones">
                            <thead class="text-purple-300 border-b border-purple-900/40 uppercase tracking-wider bg-purple-950/50">
                                <tr>
                                    <th class="p-4">Tipo</th>
                                    <th class="p-4">Fecha & Hora</th>
                                    <th class="p-4">Instructor</th>
                                    <th class="p-4">Ficha & Programa</th>
                                    <th class="p-4">Ambiente</th>
                                    <th class="p-4">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-purple-900/20 text-purple-200" id="marcaciones-tbody">
                                <!-- Dynamic JS Population -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- VIEW 4: GESTIÓN DE CALIFICACIONES -->
            <section id="view-notas" class="hidden space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Gestión de Calificaciones</h1>
                    <p class="text-xs text-slate-400 mt-1">Selecciona una ficha y define si sus aprendices aprueban o no.</p>
                </div>

                <div class="bg-[#101817] border border-green-900/30 p-5 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-bold text-white">Fichas</h2>
                            <p class="text-[11px] text-slate-400 mt-1">Cada ficha muestra únicamente el listado de sus estudiantes.</p>
                        </div>
                    </div>
                    <div id="grade-fichas-cards" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
                </div>

                <div id="grade-students-panel" class="bg-[#101817] border border-green-900/30 rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-green-900/30 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-white" id="grade-selection-label">Selecciona una ficha</h3>
                            <p class="text-[11px] text-slate-400 mt-1">Aprueba o no aprueba a cada aprendiz de la ficha.</p>
                        </div>
                        <div id="grade-bulk-actions" class="hidden flex-wrap gap-2">
                            <button onclick="setBulkGrade(true)" class="px-4 py-2 rounded-xl text-xs font-bold bg-green-500/15 border border-green-500/30 text-green-300 hover:bg-green-500/25 flex items-center gap-2">
                                <i data-lucide="check-check" class="w-4 h-4"></i> Aprobar a todos
                            </button>
                            <button onclick="setBulkGrade(false)" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-500/15 border border-red-500/30 text-red-300 hover:bg-red-500/25 flex items-center gap-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i> No aprobar a todos
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-slate-300 border-b border-green-900/30 uppercase tracking-wider bg-[#17221F]">
                                <tr><th class="p-4">Estudiante</th><th class="p-4">Documento</th><th class="p-4">Resultado</th><th class="p-4 text-center">Acción</th></tr>
                            </thead>
                            <tbody class="divide-y divide-green-900/20 text-slate-200" id="grades-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- VIEW 5: EVENTOS Y CALENDARIO -->
            <section id="view-eventos" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Eventos y Agenda Institucional</h1>
                        <p class="text-xs text-purple-400 mt-1">Planificación académica, actividades culturales y reuniones administrativas SENA.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="openNewEventModal()" class="bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 text-white font-medium px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-brand-600/30 flex items-center gap-2 transition-all">
                            <i data-lucide="plus" class="w-4 h-4"></i> Nuevo Evento
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-[#181332] border border-purple-900/30 p-6 rounded-2xl glow-card space-y-4">
                        <div class="flex items-center justify-between border-b border-purple-900/30 pb-4">
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold text-white">Septiembre 2026</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-600/30 text-brand-300 border border-brand-500/30">Mes Actual</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-7 gap-2 text-center font-semibold text-xs text-purple-400 py-2 border-b border-purple-900/20">
                            <div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div><div>Dom</div>
                        </div>

                        <div class="grid grid-cols-7 gap-2.5" id="calendar-days-grid">
                            <!-- Dynamic Calendar Days JS -->
                        </div>
                    </div>

                    <div class="bg-[#181332] border border-purple-900/30 p-6 rounded-2xl glow-card flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-3 border-b border-purple-900/30 pb-3">
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    <i data-lucide="list-todo" class="w-5 h-5 text-brand-400"></i> Lista de Eventos
                                </h3>
                            </div>

                            <div class="space-y-3.5 max-h-[460px] overflow-y-auto pr-1" id="events-list-container">
                                <!-- Dynamic Event Item Cards -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- VIEW 6: CENTRO DE NOTIFICACIONES -->
            <section id="view-notificaciones" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Centro de Notificaciones</h1>
                        <p class="text-xs text-purple-400 mt-1">Avisos, alertas académicas y comunicados administrativos en tiempo real.</p>
                    </div>
                    <button onclick="markAllNotificationsRead()" class="text-xs font-medium text-brand-400 hover:text-brand-300 bg-purple-950/60 border border-purple-800/40 px-3.5 py-2 rounded-xl transition-all">
                        Marcar todas como leídas
                    </button>
                </div>

                <div class="bg-[#181332] border border-purple-900/30 rounded-2xl p-6 glow-card space-y-6">
                    <div class="space-y-3" id="notifications-list-container">
                        <!-- Dynamic Notifications List JS -->
                    </div>
                </div>
            </section>

            <!-- VIEW 7: APRENDICES MANAGEMENT MODULE -->
            <section id="view-aprendices" class="hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Gestión de Aprendices</h1>
                        <p class="text-xs text-purple-400 mt-1">Consulta, modifica y administra la información académica de los aprendices SENA.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="openCreateModal()" class="bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 text-white font-medium px-4 py-2.5 rounded-xl text-sm shadow-lg shadow-brand-600/30 flex items-center gap-2 transition-all">
                            <i data-lucide="user-plus" class="w-4 h-4"></i> Nuevo Aprendiz
                        </button>
                    </div>
                </div>

                <!-- EXPEDIENTE Y RELACIÓN FICHA / INSTRUCTORES -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
                    <div class="xl:col-span-2 bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                            <div>
                                <h2 class="text-base font-bold text-white flex items-center gap-2">
                                    <i data-lucide="folder-open" class="w-5 h-5 text-brand-400"></i>
                                    Expediente del Aprendiz
                                </h2>
                                <p class="text-[11px] text-purple-400 mt-1">Consulta la información académica, fichas asignadas e instructores responsables.</p>
                            </div>
                            <button onclick="abrirExpedienteSeleccionado()" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 flex items-center gap-2">
                                <i data-lucide="search" class="w-4 h-4"></i> Abrir expediente
                            </button>
                        </div>
                        <div id="expediente-resumen" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="rounded-xl bg-purple-950/40 border border-purple-900/40 p-4">
                                <span class="text-[10px] uppercase tracking-wider text-purple-400">Aprendiz</span>
                                <p class="text-sm font-bold text-white mt-1">Seleccione un aprendiz</p>
                            </div>
                            <div class="rounded-xl bg-purple-950/40 border border-purple-900/40 p-4">
                                <span class="text-[10px] uppercase tracking-wider text-purple-400">Fichas</span>
                                <p class="text-sm font-bold text-brand-300 mt-1">0 asignadas</p>
                            </div>
                            <div class="rounded-xl bg-purple-950/40 border border-purple-900/40 p-4">
                                <span class="text-[10px] uppercase tracking-wider text-purple-400">Instructores</span>
                                <p class="text-sm font-bold text-purple-200 mt-1">Seleccione una ficha</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#181332] border border-purple-900/30 p-5 rounded-2xl glow-card">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-brand-400"></i>
                            <h3 class="text-sm font-bold text-white">Ficha seleccionada</h3>
                        </div>
                        <div id="ficha-seleccionada-resumen" class="text-xs text-purple-300">
                            Seleccione "Abrir expediente" en un aprendiz para consultar sus fichas.
                        </div>
                    </div>
                </div>

                <div class="bg-[#181332] border border-purple-900/30 p-4 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4 glow-card">
                    <div class="relative w-full md:w-80">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-400"></i>
                        <input type="text" id="aprendiz-search-input" onkeyup="filterAprendicesTable()" placeholder="Buscar por nombre, documento o ficha..." class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl pl-10 pr-4 py-2 text-xs text-purple-100 placeholder-purple-400/60 focus:outline-none focus:border-brand-500">
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
                        <button onclick="filterAprendices('todos')" id="filter-btn-todos" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-medium bg-brand-600 text-white border border-brand-500">
                            Todos
                        </button>
                        <button onclick="filterAprendices('activo')" id="filter-btn-activo" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-medium bg-purple-950/40 text-purple-300 hover:bg-purple-900/40 border border-purple-800/40">
                            Activos
                        </button>
                        <button onclick="filterAprendices('riesgo')" id="filter-btn-riesgo" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-medium bg-purple-950/40 text-purple-300 hover:bg-purple-900/40 border border-purple-800/40">
                            En Riesgo
                        </button>
                        <button onclick="filterAprendices('inactivo')" id="filter-btn-inactivo" class="filter-btn px-3 py-1.5 rounded-xl text-xs font-medium bg-purple-950/40 text-purple-300 hover:bg-purple-900/40 border border-purple-800/40">
                            Inactivos
                        </button>
                    </div>
                </div>

                <div class="bg-[#181332] border border-purple-900/30 rounded-2xl overflow-hidden glow-card">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs" id="table-aprendices">
                            <thead class="text-purple-300 border-b border-purple-900/40 uppercase tracking-wider bg-purple-950/50">
                                <tr>
                                    <th class="p-4">Aprendiz</th>
                                    <th class="p-4">Documento</th>
                                    <th class="p-4">Ficha & Programa</th>
                                    <th class="p-4">Estado</th>
                                    <th class="p-4">Asistencia / Fallas</th>
                                    <th class="p-4">Promedio</th>
                                    <th class="p-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-purple-900/20 text-purple-200" id="aprendices-tbody">
                                <!-- Dynamic JS Population -->
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-purple-900/30 flex items-center justify-between text-xs text-purple-400">
                        <span id="table-count-info">Mostrando aprendices</span>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- MODAL: EXPEDIENTE COMPLETO DEL APRENDIZ -->
    <div id="modal-expediente-aprendiz" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-[#101817] border border-green-800/50 rounded-2xl w-full max-w-5xl max-h-[92vh] overflow-y-auto shadow-2xl">
            <div class="px-6 py-5 border-b border-green-900/40 flex items-center justify-between sticky top-0 bg-[#101817] z-10">
                <div>
                    <p class="text-[10px] uppercase tracking-[.2em] text-green-400 font-bold">Expediente académico</p>
                    <h3 id="expediente-title" class="text-xl font-bold text-white mt-1">Aprendiz</h3>
                    <p id="expediente-subtitle" class="text-xs text-slate-400 mt-1"></p>
                </div>
                <button onclick="cerrarExpediente()" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-green-900/30">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <div id="expediente-datos" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3"></div>

                <div class="bg-[#17221F] border border-green-900/30 rounded-2xl p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <i data-lucide="layers-3" class="w-4 h-4 text-green-400"></i>
                                Fichas asignadas al aprendiz
                            </h4>
                            <p class="text-[10px] text-slate-400 mt-1">El aprendiz puede tener una o varias fichas asociadas.</p>
                        </div>
                        <button onclick="abrirAsignarFicha()" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#39A900] hover:bg-[#007832] text-white flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i> Asignar ficha
                        </button>
                    </div>
                    <div id="expediente-fichas" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
                </div>

                <div class="bg-[#17221F] border border-green-900/30 rounded-2xl p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <i data-lucide="users-round" class="w-4 h-4 text-green-400"></i>
                                Instructores de la ficha
                            </h4>
                            <p id="expediente-ficha-instructores-label" class="text-[10px] text-slate-400 mt-1">Seleccione una ficha.</p>
                        </div>
                    </div>
                    <div id="expediente-instructores" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
                </div>

                <div class="flex justify-end">
                    <button onclick="cerrarExpediente()" class="px-4 py-2 rounded-xl text-xs text-slate-300 border border-slate-700 hover:bg-slate-800">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ASIGNAR FICHA AL APRENDIZ -->
    <div id="modal-asignar-ficha-aprendiz" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-[#101817] border border-green-800/50 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
            <div class="px-6 py-5 border-b border-green-900/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5 text-green-400"></i> Asignar ficha
                    </h3>
                    <p id="asignar-ficha-aprendiz-nombre" class="text-[11px] text-slate-400 mt-1"></p>
                </div>
                <button onclick="cerrarAsignarFicha()" class="p-2 rounded-xl text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form onsubmit="guardarFichaAprendiz(event)" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Ficha de formación</label>
                    <select id="select-ficha-aprendiz" required class="w-full bg-[#17221F] border border-green-900/40 rounded-xl px-3 py-2.5 text-xs text-white"></select>
                </div>
                <div id="preview-ficha-aprendiz" class="rounded-xl bg-green-950/20 border border-green-900/30 p-4 text-xs text-slate-300"></div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="cerrarAsignarFicha()" class="px-4 py-2 rounded-xl text-xs text-slate-300 border border-slate-700">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#39A900] hover:bg-[#007832] text-white">Asignar ficha</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: GESTIONAR FALLA Y EXCUSA -->
    <div id="modal-excusa" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-[#101817] border border-green-800/50 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-green-900/40 flex items-center justify-between">
                <div><h3 class="text-base font-bold text-white">Modificar falla</h3><p class="text-[11px] text-slate-400 mt-1" id="excuse-student-name"></p></div>
                <button onclick="closeExcuseModal()" class="p-1 rounded-lg text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form onsubmit="saveExcuse(event)" class="p-6 space-y-4">
                <input type="hidden" id="excuse-student-id">
                <div><label class="block text-xs font-medium text-slate-300 mb-1">Fecha de la falla</label><input type="date" id="excuse-date" required class="w-full bg-[#17221F] border border-green-900/40 rounded-xl px-3 py-2.5 text-xs text-white"></div>
                <div><label class="block text-xs font-medium text-slate-300 mb-1">Estado</label><select id="excuse-status" class="w-full bg-[#17221F] border border-green-900/40 rounded-xl px-3 py-2.5 text-xs text-white"><option value="justificada">Falla justificada</option><option value="injustificada">Falla injustificada</option></select></div>
                <div><label class="block text-xs font-medium text-slate-300 mb-1">Excusa válida / observación</label><textarea id="excuse-note" rows="3" placeholder="Escribe la observación o registra la excusa..." class="w-full bg-[#17221F] border border-green-900/40 rounded-xl px-3 py-2.5 text-xs text-white"></textarea></div>
                <div class="flex justify-end gap-2"><button type="button" onclick="closeExcuseModal()" class="px-4 py-2 rounded-xl text-xs text-slate-300 border border-slate-700">Cancelar</button><button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#39A900] text-white">Guardar cambio</button></div>
            </form>
        </div>
    </div>

    <!-- MODAL 1: REGISTRAR / EDITAR APRENDIZ -->
    <!-- VISTAS DE TRANSVERSALES E INSTRUCTORES -->
<section id="view-transversales" class="hidden space-y-6">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"><div><h1 class="text-2xl font-bold text-white">Transversales de Aprendices</h1><p class="text-xs text-purple-400 mt-1">Asignación general al aprendiz, independiente de la ficha.</p></div><button onclick="abrirAsignarTransversal()" class="bg-gradient-to-r from-brand-600 to-brand-500 text-white px-4 py-2.5 rounded-xl text-sm flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i>Asignar transversal</button></div>
<div class="bg-[#101817] border border-purple-900/30 rounded-2xl overflow-hidden"><div class="p-5 border-b border-purple-900/30"><h2 class="font-bold text-white">Asignaciones generales</h2><p class="text-xs text-purple-400 mt-1">Aquí se ve qué instructor dicta cada transversal, cuántas horas debe ver el aprendiz y en qué horario.</p></div><div class="overflow-x-auto"><table class="w-full text-left"><thead class="bg-purple-950/40 text-[10px] uppercase text-purple-400"><tr><th class="p-4">Aprendiz</th><th class="p-4">Transversal</th><th class="p-4">Instructor</th><th class="p-4">Horas</th><th class="p-4">Horario</th></tr></thead><tbody id="transversales-tbody"></tbody></table></div></div></section>
<section id="view-instructor-fichas" class="hidden space-y-6"><div><h1 class="text-2xl font-bold text-white">Fichas asignadas al Instructor</h1><p class="text-xs text-purple-400 mt-1">Vista independiente para consultar las fichas que el instructor tiene asignadas para dar transversales o gestionar.</p></div><div class="bg-[#101817] border border-purple-900/30 rounded-2xl p-5 flex items-center gap-4"><span class="text-sm text-purple-300">Instructor:</span><select id="instructor-ficha-select" onchange="renderFichasInstructor()" class="bg-purple-950 border border-purple-800 text-white rounded-xl px-4 py-2.5 text-sm"><option>Valeria Gómez</option><option>Carlos Pérez</option><option>Laura Méndez</option><option>Jorge Ramírez</option><option>Andrés Martínez</option></select></div><div id="fichas-instructor-grid" class="grid grid-cols-1 md:grid-cols-2 gap-5"></div></section>
<section id="view-jefatura-ficha" class="hidden space-y-6"><div><h1 class="text-2xl font-bold text-white">Jefe de Ficha</h1><p class="text-xs text-purple-400 mt-1">Aquí aparecen las fichas que fueron asignadas al jefe de ficha para que tenga a cargo sus aprendices.</p></div><div class="bg-[#101817] border border-purple-900/30 rounded-2xl p-5 flex items-center gap-4"><span class="text-sm text-purple-300">Jefe de ficha:</span><select id="jefe-ficha-select" onchange="renderJefaturaFicha()" class="bg-purple-950 border border-purple-800 text-white rounded-xl px-4 py-2.5 text-sm"><option>Andrés Martínez</option><option>Laura Méndez</option><option>Jorge Ramírez</option></select></div><div id="jefatura-ficha-grid" class="grid grid-cols-1 md:grid-cols-2 gap-5"></div></section>

<div id="modal-aprendiz" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-[#181332] border border-purple-800/50 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl glow-purple transform transition-all scale-95 opacity-0 duration-200" id="modal-container">
            <div class="px-6 py-4 border-b border-purple-900/40 flex items-center justify-between bg-purple-950/40">
                <h3 class="text-base font-bold text-white flex items-center gap-2" id="modal-title">
                    <i data-lucide="user-plus" class="w-5 h-5 text-brand-400"></i> Registrar Nuevo Aprendiz
                </h3>
                <button onclick="closeModal()" class="p-1 rounded-lg text-purple-400 hover:text-white hover:bg-purple-900/40">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="form-aprendiz" onsubmit="handleFormSubmit(event)" class="p-6 space-y-4">
                <input type="hidden" id="edit-index" value="-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-purple-300 mb-1">Nombre Completo</label>
                        <input type="text" id="input-nombre" required placeholder="Ej: Carlos Eduardo Mendoza" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white placeholder-purple-400/50 focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-purple-300 mb-1">Documento de Identidad</label>
                        <input type="text" id="input-documento" required placeholder="Ej: 101429384" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white placeholder-purple-400/50 focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-purple-300 mb-1">Correo Electrónico</label>
                        <input type="email" id="input-correo" required placeholder="ejemplo@sena.edu.co" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white placeholder-purple-400/50 focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-purple-300 mb-1">Ficha de Formación</label>
                        <select id="input-ficha" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                            <option value="284910 - ADSO">284910 - ADSO</option>
                            <option value="271029 - Ciberseguridad">271029 - Ciberseguridad</option>
                            <option value="291024 - Redes Cisco">291024 - Redes Cisco</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-purple-300 mb-1">Estado del Aprendiz</label>
                        <select id="input-estado" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                            <option value="Activo">Activo</option>
                            <option value="En Riesgo">En Riesgo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-purple-300 mb-1">Promedio Actual (1.0 - 5.0)</label>
                        <input type="number" step="0.1" min="1.0" max="5.0" id="input-promedio" required placeholder="Ej: 4.5" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="pt-4 border-t border-purple-900/40 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-purple-300 hover:text-white hover:bg-purple-900/40 border border-purple-800/30">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-medium text-white bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 shadow-lg shadow-brand-600/30">
                        Guardar Aprendiz
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: REGISTRAR NOTA -->
    <div id="modal-grade" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-[#181332] border border-purple-800/50 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl glow-purple transform transition-all scale-95 opacity-0 duration-200" id="modal-grade-container">
            <div class="px-6 py-4 border-b border-purple-900/40 flex items-center justify-between bg-purple-950/40">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="award" class="w-5 h-5 text-brand-400"></i> Asignar Nota a Aprendiz
                </h3>
                <button onclick="closeGradeModal()" class="p-1 rounded-lg text-purple-400 hover:text-white hover:bg-purple-900/40">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form onsubmit="handleGradeSubmit(event)" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-purple-300 mb-1">Aprendiz</label>
                    <select id="gr-student" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-purple-300 mb-1">Módulo / Asignatura</label>
                    <select id="gr-module" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                        <option value="Lógica de Programación">Lógica de Programación</option>
                        <option value="Bases de Datos SQL">Bases de Datos SQL</option>
                        <option value="Arquitectura Frontend">Arquitectura Frontend</option>
                        <option value="Ciberseguridad Básica">Ciberseguridad Básica</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-purple-300 mb-1">Nombre Evidencia</label>
                        <input type="text" id="gr-task" required placeholder="Ej: Proyecto Final / Parcial 1" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-purple-300 mb-1">Calificación (1.0 - 5.0)</label>
                        <input type="number" step="0.1" min="1.0" max="5.0" id="gr-score" required placeholder="Ej: 4.8" class="w-full bg-purple-950/60 border border-purple-800/40 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="pt-4 border-t border-purple-900/40 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeGradeModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-purple-300 hover:text-white border border-purple-800/30">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-medium text-white bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 shadow-lg shadow-brand-600/30">
                        Guardar Calificación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Data Structures for System
        let aprendicesData = [
            { id: 1, fallas: 1, nombre: "Juan Sebastian Silva", doc: "101829381", email: "juan.silva@sena.edu.co", ficha: "284910 - ADSO", assignedFichas: ["284910 - ADSO"], transversales: [{nombre:"Inglés", instructor:"Valeria Gómez", horas:48, horario:"Lunes y miércoles · 2:00 PM - 4:00 PM"}, {nombre:"Ética y Valores", instructor:"Carlos Pérez", horas:32, horario:"Viernes · 8:00 AM - 10:00 AM"}], estado: "Activo", asistencia: 96, promedio: 4.8 },
            { id: 2, fallas: 3, nombre: "Maria Camila Torres", doc: "102938471", email: "mc.torres@sena.edu.co", ficha: "284910 - ADSO", assignedFichas: ["284910 - ADSO"], transversales: [{nombre:"Inglés", instructor:"Valeria Gómez", horas:48, horario:"Lunes y miércoles · 2:00 PM - 4:00 PM"}, {nombre:"Ética y Valores", instructor:"Carlos Pérez", horas:32, horario:"Viernes · 8:00 AM - 10:00 AM"}], estado: "En Riesgo", asistencia: 74, promedio: 3.1 },
            { id: 3, fallas: 1, nombre: "Andrés Felipe Ruiz", doc: "109837128", email: "af.ruiz@sena.edu.co", ficha: "271029 - Ciberseguridad", assignedFichas: ["271029 - Ciberseguridad"], transversales: [{nombre:"Inglés", instructor:"Valeria Gómez", horas:48, horario:"Martes y jueves · 6:00 PM - 8:00 PM"}], estado: "Activo", asistencia: 92, promedio: 4.5 },
            { id: 4, fallas: 0, nombre: "Laura Sofia Restrepo", doc: "103291823", email: "laura.restrepo@sena.edu.co", ficha: "291024 - Redes Cisco", assignedFichas: ["291024 - Redes Cisco"], transversales: [{nombre:"Comunicación", instructor:"Laura Méndez", horas:40, horario:"Miércoles · 8:00 AM - 12:00 PM"}], estado: "Activo", asistencia: 98, promedio: 4.9 },
            { id: 5, fallas: 5, nombre: "Diego Alejandro Patiño", doc: "108726351", email: "da.patino@sena.edu.co", ficha: "284910 - ADSO", assignedFichas: ["284910 - ADSO"], transversales: [{nombre:"Inglés", instructor:"Valeria Gómez", horas:48, horario:"Lunes y miércoles · 2:00 PM - 4:00 PM"}, {nombre:"Ética y Valores", instructor:"Carlos Pérez", horas:32, horario:"Viernes · 8:00 AM - 10:00 AM"}], estado: "Inactivo", asistencia: 50, promedio: 2.5 }
        ];

        // Fichas disponibles para asignar a cada aprendiz.
        let fichasAprendizData = [
            { id: "FIC-284910", codigo: "284910", nombre: "284910 - ADSO", programa: "Análisis y Desarrollo de Software", jornada: "Diurna", estado: "Activa" },
            { id: "FIC-271029", codigo: "271029", nombre: "271029 - Ciberseguridad", programa: "Ciberseguridad", jornada: "Nocturna", estado: "Activa" },
            { id: "FIC-291024", codigo: "291024", nombre: "291024 - Redes Cisco", programa: "Redes y Telecomunicaciones", jornada: "Diurna", estado: "Activa" }
        ];

        // Instructores relacionados con cada ficha.
        let instructoresPorFicha = {
            "284910": [
                { id: "INS-01", nombre: "Valeria Gómez", rol: "Instructora Líder", area: "Desarrollo de Software", email: "valeria.gomez@sena.edu.co" },
                { id: "INS-02", nombre: "Carlos Pérez", rol: "Instructor Técnico", area: "Bases de Datos", email: "carlos.perez@sena.edu.co" }
            ],
            "271029": [
                { id: "INS-03", nombre: "Andrés Martínez", rol: "Instructor Líder", area: "Ciberseguridad", email: "andres.martinez@sena.edu.co" },
                { id: "INS-04", nombre: "Laura Méndez", rol: "Instructor Técnico", area: "Redes y Seguridad", email: "laura.mendez@sena.edu.co" }
            ],
            "291024": [
                { id: "INS-05", nombre: "Jorge Ramírez", rol: "Instructor Líder", area: "Redes Cisco", email: "jorge.ramirez@sena.edu.co" }
            ]
        };

        // TRANSVERSALES: asignación general al aprendiz, independiente de la ficha.
        let transversalesData = [
          { id:"TR-01", nombre:"Inglés", instructor:"Valeria Gómez", horas:48, horario:"Lunes y miércoles · 2:00 PM - 4:00 PM", modalidad:"Presencial" },
          { id:"TR-02", nombre:"Ética y Valores", instructor:"Carlos Pérez", horas:32, horario:"Viernes · 8:00 AM - 10:00 AM", modalidad:"Presencial" },
          { id:"TR-03", nombre:"Comunicación", instructor:"Laura Méndez", horas:40, horario:"Miércoles · 8:00 AM - 12:00 PM", modalidad:"Presencial" },
          { id:"TR-04", nombre:"Emprendimiento", instructor:"Jorge Ramírez", horas:36, horario:"Jueves · 2:00 PM - 5:00 PM", modalidad:"Virtual" }
        ];

        // Fichas que cada instructor tiene asignadas para impartir transversales o gestionar.
        let fichasInstructorData = [
          { instructor:"Valeria Gómez", tipo:"Transversal", ficha:"284910 - ADSO", transversal:"Inglés", horas:48, horario:"Lunes y miércoles · 2:00 PM - 4:00 PM" },
          { instructor:"Valeria Gómez", tipo:"Transversal", ficha:"271029 - Ciberseguridad", transversal:"Inglés", horas:48, horario:"Martes y jueves · 6:00 PM - 8:00 PM" },
          { instructor:"Carlos Pérez", tipo:"Transversal", ficha:"284910 - ADSO", transversal:"Ética y Valores", horas:32, horario:"Viernes · 8:00 AM - 10:00 AM" },
          { instructor:"Laura Méndez", tipo:"Transversal", ficha:"291024 - Redes Cisco", transversal:"Comunicación", horas:40, horario:"Miércoles · 8:00 AM - 12:00 PM" }
        ];

        // Jefes de ficha y las fichas que tienen bajo su responsabilidad.
        let jefesFichaData = [
          { jefe:"Andrés Martínez", ficha:"284910 - ADSO", programa:"Análisis y Desarrollo de Software", jornada:"Diurna", aprendices:32 },
          { jefe:"Laura Méndez", ficha:"271029 - Ciberseguridad", programa:"Ciberseguridad", jornada:"Nocturna", aprendices:28 },
          { jefe:"Jorge Ramírez", ficha:"291024 - Redes Cisco", programa:"Redes y Telecomunicaciones", jornada:"Diurna", aprendices:25 }
        ];

        let aprendizExpedienteActual = null;

        let inventarioAmbientesData = {
            "Aula 102 ADSO": [
                { id: 1, nombre: "Equipos de Cómputo All-in-One", esperada: 30, verificada: 30, estado: "Bueno", obs: "Todos operativos" },
                { id: 2, nombre: "Televisor Smart 65 pulgadas", esperada: 1, verificada: 1, estado: "Bueno", obs: "Con cable HDMI funcional" },
                { id: 3, nombre: "Sillas Ergonomicas", esperada: 30, verificada: 29, estado: "Regular", obs: "Falta 1 silla en el puesto 12" },
                { id: 4, nombre: "VideoBeam Epson HD", esperada: 1, verificada: 1, estado: "Malo", obs: "Lámpara requiere reemplazo" },
                { id: 5, nombre: "Aire Acondicionado 24000 BTU", esperada: 2, verificada: 2, estado: "Bueno", obs: "Control remoto operativo" },
                { id: 6, nombre: "Tablero Acrílico Grande", esperada: 1, verificada: 1, estado: "Bueno", obs: "Limpio" }
            ],
            "Laboratorio Hardware 204": [
                { id: 1, nombre: "Kits de Herramientas Mantenimiento", esperada: 15, verificada: 15, estado: "Bueno", obs: "Completo" },
                { id: 2, nombre: "Osciloscopios Digitales", esperada: 10, verificada: 10, estado: "Bueno", obs: "Calibrados" },
                { id: 3, nombre: "Switches Cisco 2960", esperada: 6, verificada: 5, estado: "Regular", obs: "1 switch en reparación" }
            ],
            "Ambiente Multimedia 301": [
                { id: 1, nombre: "iMac 27 pulgadas Graphics", esperada: 20, verificada: 20, estado: "Bueno", obs: "Licencias Adobe activas" },
                { id: 2, nombre: "Tabletas Digitalizadoras Wacom", esperada: 20, verificada: 18, estado: "Regular", obs: "2 Lápices ópticos desgastados" }
            ],
            "Taller de Electrónica 105": [
                { id: 1, nombre: "Estaciones de Soldadura Cautín", esperada: 12, verificada: 12, estado: "Bueno", obs: "Buen estado" }
            ]
        };

        let historialEntregas = [
            { ambiente: "Aula 102 ADSO", fecha: "2026-09-24 12:30 PM", entrego: "Valeria Gómez", recibio: "Pedro Picapiedra (Vigilancia)", estado: "Completo - Con Novedad en VideoBeam" },
            { ambiente: "Laboratorio Hardware 204", fecha: "2026-09-23 06:00 PM", entrego: "Valeria Gómez", recibio: "Jorge Martínez (Instructor)", estado: "Conforme" }
        ];

        let marcacionesData = [
            { id: 1, tipo: "Entrada", timestamp: "2026-09-24 07:02 AM", instructor: "Valeria Gómez", ficha: "284910 - ADSO", ambiente: "Aula 102 ADSO", estado: "Puntual" },
            { id: 2, tipo: "Salida", timestamp: "2026-09-23 01:00 PM", instructor: "Valeria Gómez", ficha: "284910 - ADSO", ambiente: "Aula 102 ADSO", estado: "Salida Normal" },
            { id: 3, tipo: "Entrada", timestamp: "2026-09-23 07:12 AM", instructor: "Valeria Gómez", ficha: "271029 - Ciberseguridad", ambiente: "Laboratorio Hardware 204", estado: "Retardo (12 min)" }
        ];

        let gradesData = [
            { id: 201, studentName: "Juan Sebastian Silva", ficha: "284910 - ADSO", module: "Lógica de Programación", task: "Algoritmos Estructurados", score: 4.8, date: "2026-09-12" },
            { id: 202, studentName: "Maria Camila Torres", ficha: "284910 - ADSO", module: "Lógica de Programación", task: "Algoritmos Estructurados", score: 2.9, date: "2026-09-12" },
            { id: 203, studentName: "Andrés Felipe Ruiz", ficha: "271029 - Ciberseguridad", module: "Bases de Datos SQL", task: "Consultas Complejas & JOINs", score: 4.5, date: "2026-09-20" },
            { id: 204, studentName: "Laura Valentina Pérez", ficha: "284910 - ADSO", module: "Lógica de Programación", task: "Algoritmos Estructurados", score: 3.2, date: "2026-09-12" },
            { id: 205, studentName: "Carlos Andrés Gómez", ficha: "284910 - ADSO", module: "Bases de Datos SQL", task: "Modelo Relacional", score: 4.1, date: "2026-09-18" },
            { id: 206, studentName: "Sofía Martínez", ficha: "271029 - Ciberseguridad", module: "Bases de Datos SQL", task: "Consultas Complejas & JOINs", score: 2.7, date: "2026-09-20" }
        ];

        let eventsData = [
            { id: 101, title: "Comité de Evaluación ADSO", day: 25, time: "08:00 - 11:00 AM", category: "admin", price: "Interno" },
            { id: 102, title: "Feria de Proyectos SENA", day: 28, time: "09:00 - 04:00 PM", category: "estudiante", price: "Gratis" }
        ];

        let notificationsData = [
            { id: 301, title: "Verificación de Inventario Requerida", text: "Ambiente 102 requiere reporte de mantenimento de VideoBeam.", time: "Hace 15 min", unread: true },
            { id: 302, title: "Marcación Registrada Correctamente", text: "Entrada registrada a las 07:02 AM en Aula 102.", time: "Hace 2 horas", unread: true }
        ];

        let currentFilter = 'todos';

        // Initialize App
        window.onload = function() {
            lucide.createIcons();
            startLiveClocks();
            initCharts();
            renderAprendicesTable();
            renderGradeFichas();
            renderGradesTable();
            cargarInventarioAmbiente();
            renderHistorialAmbientes();
            renderTablaMarcaciones();
            renderGradesTable();
            renderCalendarDays();
            renderEventsList();
            renderNotificationsList();
            setupSidebarToggle();
        };

        function startLiveClocks() {
            function update() {
                const now = new Date();
                const timeStr = now.toLocaleTimeString('es-CO');
                const dateStr = now.toLocaleDateString('es-CO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                
                const timeElem = document.getElementById('current-timestamp-display');
                if(timeElem) timeElem.textContent = `${dateStr} | ${timeStr}`;

                const clockLarge = document.getElementById('clock-large');
                if(clockLarge) clockLarge.textContent = timeStr;

                const dateLarge = document.getElementById('date-large');
                if(dateLarge) dateLarge.textContent = dateStr;

                const ambienteTS = document.getElementById('ambiente-timestamp');
                if(ambienteTS) ambienteTS.value = `${now.toISOString().split('T')[0]} ${timeStr}`;
            }
            update();
            setInterval(update, 1000);
        }

        function setupSidebarToggle() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggle-sidebar');
            const toggleIcon = document.getElementById('toggle-icon');
            let isCollapsed = false;

            toggleBtn.addEventListener('click', () => {
                isCollapsed = !isCollapsed;
                if (isCollapsed) {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20');
                    toggleIcon.classList.add('rotate-180');
                    document.querySelectorAll('.sidebar-text').forEach(el => el.classList.add('hidden'));
                    const submenu = document.getElementById('aprendices-submenu');
                    if(submenu) submenu.classList.add('max-h-0', 'opacity-0');
                } else {
                    sidebar.classList.remove('w-20');
                    sidebar.classList.add('w-64');
                    toggleIcon.classList.remove('rotate-180');
                    document.querySelectorAll('.sidebar-text').forEach(el => el.classList.remove('hidden'));
                }
            });
        }

        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            const arrow = document.getElementById('aprendices-arrow');
            const sidebar = document.getElementById('sidebar');

            if (sidebar.classList.contains('w-20')) {
                document.getElementById('toggle-sidebar').click();
            }

            if (submenu.classList.contains('max-h-0')) {
                submenu.classList.remove('max-h-0', 'opacity-0');
                submenu.classList.add('max-h-48', 'opacity-100');
                if (arrow) arrow.classList.add('rotate-180');
            } else {
                submenu.classList.add('max-h-0', 'opacity-0');
                submenu.classList.remove('max-h-48', 'opacity-100');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        }

        function switchView(viewName) {
            const views = ['dashboard', 'ambientes', 'marcaciones', 'notas', 'eventos', 'notificaciones', 'aprendices', 'transversales', 'instructor-fichas', 'jefatura-ficha'];
            views.forEach(v => {
                const el = document.getElementById(`view-${v}`);
                if (el) el.classList.add('hidden');
            });

            const targetView = document.getElementById(`view-${viewName}`);
            if (targetView) targetView.classList.remove('hidden');

            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('bg-brand-600/20', 'text-white'));
            const activeNavItem = document.getElementById(`nav-${viewName}`);
            if (activeNavItem) activeNavItem.classList.add('bg-brand-600/20', 'text-white');
        }

        function renderTransversales() {
          const tbody=document.getElementById('transversales-tbody'); if(!tbody) return; tbody.innerHTML='';
          aprendicesData.forEach(a=>(a.transversales||[]).forEach(t=>{ tbody.innerHTML+=`<tr class="border-t border-purple-900/20 hover:bg-purple-900/20"><td class="p-4"><div class="font-semibold text-white">${a.nombre}</div><div class="text-[10px] text-purple-400">${a.doc}</div></td><td class="p-4 text-emerald-300 font-semibold">${t.nombre}</td><td class="p-4 text-white">${t.instructor}</td><td class="p-4 text-white">${t.horas} h</td><td class="p-4 text-purple-300">${t.horario}</td></tr>`;}));
        }
        function renderFichasInstructor(){
          const instructor=document.getElementById('instructor-ficha-select')?.value; const box=document.getElementById('fichas-instructor-grid'); if(!box)return;
          const items=fichasInstructorData.filter(x=>x.instructor===instructor); box.innerHTML=items.length?items.map(x=>`<div class="bg-[#101817] border border-purple-900/30 rounded-2xl p-5 glow-card"><div class="flex justify-between gap-3"><div><p class="text-[10px] text-purple-400 uppercase font-bold">${x.tipo}</p><h3 class="text-lg font-bold text-white mt-1">${x.ficha}</h3></div><span class="px-2 py-1 rounded-lg bg-emerald-500/15 text-emerald-300 text-[10px]">Asignada</span></div><p class="text-sm text-purple-200 mt-4">Transversal: <b class="text-white">${x.transversal}</b></p><div class="grid grid-cols-2 gap-3 mt-4"><div class="bg-purple-950/40 rounded-xl p-3"><span class="text-[10px] text-purple-400">Horas</span><p class="font-bold text-white">${x.horas} h</p></div><div class="bg-purple-950/40 rounded-xl p-3"><span class="text-[10px] text-purple-400">Horario</span><p class="font-semibold text-white text-xs">${x.horario}</p></div></div><button onclick="verAprendicesFichaInstructor('${encodeURIComponent(x.ficha)}')" class="mt-4 w-full border border-purple-700/40 hover:bg-purple-900/30 rounded-xl py-2 text-xs text-purple-200">Ver aprendices de la ficha</button></div>`).join(''):`<div class="col-span-full bg-[#101817] border border-dashed border-purple-800/50 rounded-2xl p-8 text-center text-purple-400">Este instructor no tiene fichas asignadas actualmente.</div>`;
        }
        function renderJefaturaFicha(){
          const jefe=document.getElementById('jefe-ficha-select')?.value; const box=document.getElementById('jefatura-ficha-grid'); if(!box)return;
          const items=jefesFichaData.filter(x=>x.jefe===jefe); box.innerHTML=items.map(x=>{const aprendices=aprendicesData.filter(a=>(a.assignedFichas||[]).includes(x.ficha)); return `<div class="bg-[#101817] border border-purple-900/30 rounded-2xl p-5 glow-card"><p class="text-[10px] text-emerald-400 uppercase font-bold">Jefe de ficha</p><h3 class="text-xl font-bold text-white mt-1">${x.ficha}</h3><p class="text-sm text-purple-300 mt-1">${x.programa} · ${x.jornada}</p><div class="grid grid-cols-2 gap-3 mt-5"><div class="bg-purple-950/40 rounded-xl p-3"><span class="text-[10px] text-purple-400">Aprendices registrados</span><p class="text-2xl font-bold text-white">${x.aprendices}</p></div><div class="bg-purple-950/40 rounded-xl p-3"><span class="text-[10px] text-purple-400">En esta vista</span><p class="text-2xl font-bold text-white">${aprendices.length}</p></div></div><div class="mt-5 space-y-2">${aprendices.map(a=>`<div class="flex items-center justify-between bg-purple-950/30 rounded-xl p-3"><span class="text-sm text-white">${a.nombre}</span><button onclick="abrirExpedienteAprendiz(${a.id})" class="text-xs text-emerald-300 hover:text-white">Expediente</button></div>`).join('')||'<p class="text-xs text-purple-500">No hay aprendices cargados para esta ficha.</p>'}</div></div>`}).join('')||'<div class="col-span-full text-purple-400">No hay fichas asignadas a este jefe.</div>';
        }
        function verAprendicesFichaInstructor(enc){ const ficha=decodeURIComponent(enc); const items=aprendicesData.filter(a=>(a.assignedFichas||[]).includes(ficha)); Swal.fire({title:'Aprendices de la ficha',html:items.map(a=>`<div style="text-align:left;padding:8px;border-bottom:1px solid #333">${a.nombre}<br><small>${a.doc} · ${a.email}</small></div>`).join('')||'Sin aprendices registrados',confirmButtonText:'Cerrar'}); }
        function abrirAsignarTransversal(){
          const options=transversalesData.map(t=>`<option value="${t.id}">${t.nombre} — ${t.instructor} — ${t.horas} h</option>`).join('');
          const aps=aprendicesData.map(a=>`<option value="${a.id}">${a.nombre} — ${a.doc}</option>`).join('');
          Swal.fire({title:'Asignar transversal al aprendiz',html:`<select id="swal-ap" class="swal2-input">${aps}</select><select id="swal-tr" class="swal2-input">${options}</select><p style="font-size:12px;text-align:left;color:#aaa">El instructor, horas y horario vienen definidos por el transversal.</p>`,showCancelButton:true,confirmButtonText:'Asignar',cancelButtonText:'Cancelar',preConfirm:()=>{const a=aprendicesData.find(x=>x.id==document.getElementById('swal-ap').value);const t=transversalesData.find(x=>x.id===document.getElementById('swal-tr').value);a.transversales=a.transversales||[]; if(!a.transversales.some(x=>x.nombre===t.nombre)){a.transversales.push({...t});} return true;}}).then(r=>{if(r.isConfirmed){renderTransversales(); if(aprendizExpedienteActual) renderExpedienteAprendiz(aprendizExpedienteActual); Swal.fire({icon:'success',title:'Transversal asignado',timer:1200,showConfirmButton:false});}});
        }

function renderTransversales(){const tb=document.getElementById('transversales-tbody');if(!tb)return;tb.innerHTML='';aprendicesData.forEach(a=>(a.transversales||[]).forEach(t=>tb.innerHTML+=`<tr class="border-t border-purple-900/20 hover:bg-purple-900/20"><td class="p-4"><b class="text-white">${a.nombre}</b><div class="text-[10px] text-purple-400">${a.doc}</div></td><td class="p-4 text-emerald-300 font-semibold">${t.nombre}</td><td class="p-4 text-white">${t.instructor}</td><td class="p-4 text-white">${t.horas} h</td><td class="p-4 text-purple-300">${t.horario}</td></tr>`));}
function renderFichasInstructor(){const ins=document.getElementById('instructor-ficha-select')?.value,box=document.getElementById('fichas-instructor-grid');if(!box)return;const arr=fichasInstructorData.filter(x=>x.instructor===ins);box.innerHTML=arr.map(x=>`<div class="bg-[#101817] border border-purple-900/30 rounded-2xl p-5"><div class="flex justify-between"><div><p class="text-[10px] text-purple-400 uppercase">${x.tipo}</p><h3 class="text-lg font-bold text-white">${x.ficha}</h3></div><span class="text-xs text-emerald-300">Asignada</span></div><p class="text-sm text-purple-200 mt-4">Transversal: <b class="text-white">${x.transversal}</b></p><div class="grid grid-cols-2 gap-3 mt-4"><div class="bg-purple-950/40 rounded-xl p-3"><span class="text-[10px] text-purple-400">Horas</span><p class="font-bold text-white">${x.horas} h</p></div><div class="bg-purple-950/40 rounded-xl p-3"><span class="text-[10px] text-purple-400">Horario</span><p class="font-semibold text-white text-xs">${x.horario}</p></div></div><button onclick="verAprendicesFichaInstructor('${encodeURIComponent(x.ficha)}')" class="mt-4 w-full border border-purple-700/40 rounded-xl py-2 text-xs text-purple-200">Ver aprendices de la ficha</button></div>`).join('')||'<div class="col-span-full bg-[#101817] border border-dashed border-purple-800/50 rounded-2xl p-8 text-center text-purple-400">Este instructor no tiene fichas asignadas.</div>'; }
function renderJefaturaFicha(){const jefe=document.getElementById('jefe-ficha-select')?.value,box=document.getElementById('jefatura-ficha-grid');if(!box)return;const arr=jefesFichaData.filter(x=>x.jefe===jefe);box.innerHTML=arr.map(x=>{const aps=aprendicesData.filter(a=>(a.assignedFichas||[]).includes(x.ficha));return `<div class="bg-[#101817] border border-purple-900/30 rounded-2xl p-5"><p class="text-[10px] text-emerald-400 uppercase">Ficha a cargo</p><h3 class="text-xl font-bold text-white">${x.ficha}</h3><p class="text-sm text-purple-300 mt-1">${x.programa} · ${x.jornada}</p><div class="grid grid-cols-2 gap-3 mt-5"><div class="bg-purple-950/40 rounded-xl p-3"><span class="text-[10px] text-purple-400">Aprendices</span><p class="text-2xl font-bold text-white">${x.aprendices}</p></div><div class="bg-purple-950/40 rounded-xl p-3"><span class="text-[10px] text-purple-400">Cargados aquí</span><p class="text-2xl font-bold text-white">${aps.length}</p></div></div><div class="mt-5 space-y-2">${aps.map(a=>`<div class="flex items-center justify-between bg-purple-950/30 rounded-xl p-3"><span class="text-sm text-white">${a.nombre}</span><button onclick="abrirExpedienteAprendiz(${a.id})" class="text-xs text-emerald-300">Expediente</button></div>`).join('')||'<p class="text-xs text-purple-500">No hay aprendices cargados.</p>'}</div></div>`}).join('')||'<div class="col-span-full text-purple-400">No hay fichas asignadas.</div>'; }
function verAprendicesFichaInstructor(enc){const ficha=decodeURIComponent(enc),aps=aprendicesData.filter(a=>(a.assignedFichas||[]).includes(ficha));Swal.fire({title:'Aprendices de la ficha',html:aps.map(a=>`<div style="text-align:left;padding:8px;border-bottom:1px solid #333">${a.nombre}<br><small>${a.doc}</small></div>`).join('')||'Sin aprendices',confirmButtonText:'Cerrar'});}
function abrirAsignarTransversal(){const aps=aprendicesData.map(a=>`<option value="${a.id}">${a.nombre} — ${a.doc}</option>`).join(''),trs=transversalesData.map(t=>`<option value="${t.id}">${t.nombre} — ${t.instructor} — ${t.horas} h</option>`).join('');Swal.fire({title:'Asignar transversal al aprendiz',html:`<select id="swal-ap" class="swal2-input">${aps}</select><select id="swal-tr" class="swal2-input">${trs}</select>`,showCancelButton:true,confirmButtonText:'Asignar',cancelButtonText:'Cancelar',preConfirm:()=>{const a=aprendicesData.find(x=>x.id==document.getElementById('swal-ap').value),t=transversalesData.find(x=>x.id===document.getElementById('swal-tr').value);a.transversales=a.transversales||[];if(!a.transversales.some(x=>x.nombre===t.nombre))a.transversales.push({...t});}}).then(r=>{if(r.isConfirmed){renderTransversales();if(aprendizExpedienteActual)renderExpedienteAprendiz(aprendizExpedienteActual);Swal.fire({icon:'success',title:'Transversal asignado',timer:1200,showConfirmButton:false});}});}

        // AMBIENTES HANDOVER MODULE LOGIC
        function cargarInventarioAmbiente() {
            const selAmbiente = document.getElementById('ambiente-select').value;
            const tbody = document.getElementById('inventario-tbody');
            tbody.innerHTML = '';

            const items = inventarioAmbientesData[selAmbiente] || [];
            
            items.forEach((item, idx) => {
                let stateColor = item.estado === 'Bueno' ? 'text-emerald-400 bg-emerald-500/20' : item.estado === 'Regular' ? 'text-amber-400 bg-amber-500/20' : 'text-rose-400 bg-rose-500/20';

                let row = `
                    <tr class="hover:bg-purple-900/20 transition-colors">
                        <td class="p-3.5 font-semibold text-white">${item.nombre}</td>
                        <td class="p-3.5 text-center font-mono">${item.esperada}</td>
                        <td class="p-3.5 text-center">
                            <input type="number" min="0" value="${item.verificada}" onchange="actualizarCantidadInv('${selAmbiente}', ${idx}, this.value)" class="w-16 bg-purple-950 border border-purple-800 rounded px-2 py-1 text-center text-xs text-white">
                        </td>
                        <td class="p-3.5">
                            <select onchange="actualizarEstadoInv('${selAmbiente}', ${idx}, this.value)" class="bg-purple-950 border border-purple-800 text-xs text-purple-200 rounded px-2 py-1 focus:outline-none">
                                <option value="Bueno" ${item.estado === 'Bueno' ? 'selected' : ''}>Bueno</option>
                                <option value="Regular" ${item.estado === 'Regular' ? 'selected' : ''}>Regular</option>
                                <option value="Malo" ${item.estado === 'Malo' ? 'selected' : ''}>Malo</option>
                            </select>
                        </td>
                        <td class="p-3.5">
                            <input type="text" value="${item.obs}" onchange="actualizarObsInv('${selAmbiente}', ${idx}, this.value)" class="w-full bg-purple-950/60 border border-purple-800/40 rounded px-2 py-1 text-xs text-purple-200 placeholder-purple-500">
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            lucide.createIcons();
        }

        function actualizarCantidadInv(ambiente, index, val) {
            inventarioAmbientesData[ambiente][index].verificada = parseInt(val);
        }

        function actualizarEstadoInv(ambiente, index, val) {
            inventarioAmbientesData[ambiente][index].estado = val;
        }

        function actualizarObsInv(ambiente, index, val) {
            inventarioAmbientesData[ambiente][index].obs = val;
        }

        function agregarElementoInventario() {
            const selAmbiente = document.getElementById('ambiente-select').value;
            Swal.fire({
                title: 'Añadir Elemento a Inventario',
                html: `
                    <input id="swal-elem-nombre" class="swal2-input bg-purple-950 text-white" placeholder="Nombre del Elemento">
                    <input id="swal-elem-cant" type="number" class="swal2-input bg-purple-950 text-white" placeholder="Cantidad Esperada" value="1">
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Añadir',
                preConfirm: () => {
                    const nombre = document.getElementById('swal-elem-nombre').value;
                    const cant = parseInt(document.getElementById('swal-elem-cant').value);
                    if(!nombre) { Swal.showValidationMessage('Ingrese nombre del elemento'); }
                    return { nombre, cant };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    inventarioAmbientesData[selAmbiente].push({
                        id: Date.now(),
                        nombre: result.value.nombre,
                        esperada: result.value.cant,
                        verificada: result.value.cant,
                        estado: 'Bueno',
                        obs: 'Ingresado en la inspección'
                    });
                    cargarInventarioAmbiente();
                    Swal.fire('Añadido', 'El elemento fue registrado en el inventario.', 'success');
                }
            });
        }

        function guardarEntregaAmbiente(e) {
            e.preventDefault();
            const ambiente = document.getElementById('ambiente-select').value;
            const timestamp = document.getElementById('ambiente-timestamp').value;
            const entrego = document.getElementById('entrega-remitente').value;
            const recibio = document.getElementById('entrega-receptor').value;
            const obs = document.getElementById('entrega-observaciones').value || 'Sin observaciones adicionales';

            historialEntregas.unshift({
                ambiente,
                fecha: timestamp,
                entrego,
                recibio,
                estado: `Procesado - ${obs}`
            });

            renderHistorialAmbientes();

            Swal.fire({
                icon: 'success',
                title: '¡Entrega Registrada con Éxito!',
                text: `El acta de entrega para ${ambiente} fue firmada digitalmente.`,
                confirmButtonColor: '#7c3aed'
            });

            document.getElementById('entrega-observaciones').value = '';
        }

        function renderHistorialAmbientes() {
            const tbody = document.getElementById('historial-ambientes-tbody');
            tbody.innerHTML = '';

            historialEntregas.forEach(h => {
                let row = `
                    <tr class="hover:bg-purple-900/20 transition-colors">
                        <td class="p-4 font-bold text-white">${h.ambiente}</td>
                        <td class="p-4 font-mono text-purple-300 text-[11px]">${h.fecha}</td>
                        <td class="p-4 text-purple-200">${h.entrego}</td>
                        <td class="p-4 text-purple-200">${h.recibio}</td>
                        <td class="p-4"><span class="px-2.5 py-1 rounded-full text-[10px] font-medium bg-purple-600/20 text-brand-300 border border-purple-500/30">${h.estado}</span></td>
                        <td class="p-4 text-center">
                            <button onclick="Swal.fire('Acta Digital SENA', 'Ambiente: ${h.ambiente}<br>Fecha: ${h.fecha}<br>Entrega: ${h.entrego}<br>Recibe: ${h.recibio}', 'info')" class="p-1.5 rounded-lg hover:bg-purple-800/40 text-purple-300 hover:text-white" title="Ver Acta Completa">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            lucide.createIcons();
        }

        function exportarReporteAmbientes() {
            Swal.fire('Reporte Exportado', 'Se ha descargado la bitácora en formato de informe institucional.', 'success');
        }

        // INSTRUCTOR CHECK-IN / CHECK-OUT MODULE LOGIC
        function registrarMarcacion(tipo) {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('es-CO');
            const dateStr = now.toISOString().split('T')[0];
            const timestamp = `${dateStr} ${timeStr}`;

            let estado = "Puntual";
            if(tipo === "Entrada" && now.getHours() >= 7 && now.getMinutes() > 10) {
                estado = "Retardo";
            } else if(tipo === "Salida") {
                estado = "Salida Normal";
            }

            marcacionesData.unshift({
                id: Date.now(),
                tipo,
                timestamp,
                instructor: "Valeria Gómez",
                ficha: "284910 - ADSO",
                ambiente: "Aula 102 ADSO",
                estado
            });

            renderTablaMarcaciones();

            Swal.fire({
                icon: tipo === 'Entrada' ? 'success' : 'info',
                title: `Marcación de ${tipo} Confirmada`,
                html: `<b>Hora:</b> ${timeStr}<br><b>Ambiente:</b> Aula 102 ADSO<br><b>Estado:</b> ${estado}`,
                confirmButtonColor: '#7c3aed'
            });
        }

        function renderTablaMarcaciones() {
            const tbody = document.getElementById('marcaciones-tbody');
            tbody.innerHTML = '';

            const tipoFiltro = document.getElementById('filter-marcacion-tipo') ? document.getElementById('filter-marcacion-tipo').value : 'todos';

            let filtered = marcacionesData.filter(m => {
                if(tipoFiltro === 'todos') return true;
                return m.tipo === tipoFiltro;
            });

            filtered.forEach(m => {
                let badgeClass = m.tipo === 'Entrada' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border-rose-500/30';

                let row = `
                    <tr class="hover:bg-purple-900/20 transition-colors">
                        <td class="p-4"><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border ${badgeClass}">${m.tipo}</span></td>
                        <td class="p-4 font-mono text-purple-300 text-[11px]">${m.timestamp}</td>
                        <td class="p-4 font-semibold text-white">${m.instructor}</td>
                        <td class="p-4 text-purple-200">${m.ficha}</td>
                        <td class="p-4 text-purple-200">${m.ambiente}</td>
                        <td class="p-4"><span class="text-xs font-medium text-brand-300">${m.estado}</span></td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            lucide.createIcons();
        }

        function filtrarTablaMarcaciones() {
            const query = document.getElementById('search-marcacion').value.toLowerCase();
            const rows = document.querySelectorAll('#tabla-marcaciones tbody tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
            });
        }

        function exportarReporteMarcaciones() {
            Swal.fire('Bitácora Generada', 'Descargando reporte de asistencia en Excel...', 'success');
        }

        // GRADES MANAGEMENT RENDER & LOGIC
        function getGradeRecord(student, module) {
            return gradesData.find(g => g.studentName === student.nombre && g.module === module);
        }

        function renderGradeFichasCards() {
            const container = document.getElementById('grade-fichas-cards');
            if (!container) return;
            const fichas = [...new Set(aprendicesData.map(a => a.ficha))];
            container.innerHTML = fichas.map(ficha => {
                const students = aprendicesData.filter(a => a.ficha === ficha);
                const evaluated = students.filter(a => gradesData.some(g => g.studentName === a.nombre));
                const active = document.getElementById('grade-ficha-filter')?.value === ficha;
                return `<button onclick="selectGradeFicha('${ficha}')" class="text-left p-4 rounded-2xl border transition-all ${active ? 'border-green-500 bg-green-500/10 shadow-lg shadow-green-900/20' : 'border-green-900/30 bg-[#17221F] hover:border-green-700/60'}">
                    <div class="flex items-center justify-between gap-3">
                        <div class="w-11 h-11 rounded-xl bg-green-500/15 flex items-center justify-center text-green-400"><i data-lucide="users-round" class="w-5 h-5"></i></div>
                        <span class="text-[10px] px-2 py-1 rounded-full bg-green-500/10 text-green-300">${students.length} estudiantes</span>
                    </div>
                    <h3 class="mt-4 text-sm font-bold text-white">${ficha}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">${evaluated.length} con calificación registrada</p>
                    <span class="inline-flex items-center gap-1 mt-3 text-[11px] font-semibold text-green-400">Gestionar ficha <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></span>
                </button>`;
            }).join('');
            lucide.createIcons();
        }

        function selectGradeFicha(ficha) {
            document.getElementById('grade-ficha-filter').value = ficha;
            renderGradesTable();
            document.getElementById('table-grades')?.scrollIntoView({behavior:'smooth', block:'start'});
        }

        function renderGradeFichas() {
            const container = document.getElementById('grade-fichas-cards');
            if (!container) return;
            const fichas = [...new Set(aprendicesData.map(a => a.ficha))];
            container.innerHTML = fichas.map(ficha => {
                const total = aprendicesData.filter(a => a.ficha === ficha).length;
                return `<button onclick="selectGradeFicha('${ficha}')" class="text-left bg-[#17221F] border border-green-900/30 hover:border-[#39A900] p-4 rounded-2xl transition-all group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-white">${ficha}</p>
                            <p class="text-[11px] text-slate-400 mt-1">${total} aprendiz(es)</p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-green-500/10 text-green-400"><i data-lucide="users" class="w-5 h-5"></i></div>
                    </div>
                </button>`;
            }).join('');
            lucide.createIcons();
        }

        let selectedGradeFicha = '';
        function selectGradeFicha(ficha) {
            selectedGradeFicha = ficha;
            const label = document.getElementById('grade-selection-label');
            if (label) label.textContent = `Ficha ${ficha}`;
            const actions = document.getElementById('grade-bulk-actions');
            if (actions) actions.classList.remove('hidden');
            renderGradesTable();
        }

        function renderGradesTable() {
            const tbody = document.getElementById('grades-tbody');
            if (!tbody) return;
            if (!selectedGradeFicha) {
                tbody.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-slate-400">Selecciona una ficha para ver sus estudiantes.</td></tr>`;
                return;
            }
            const students = aprendicesData.filter(a => a.ficha === selectedGradeFicha);
            tbody.innerHTML = students.map(student => {
                const approved = student.aprobado === true;
                const rejected = student.aprobado === false;
                const status = approved ? `<span class="px-2.5 py-1 rounded-full bg-green-500/15 text-green-300 border border-green-500/30">Aprobado</span>` : rejected ? `<span class="px-2.5 py-1 rounded-full bg-red-500/15 text-red-300 border border-red-500/30">No aprobado</span>` : `<span class="px-2.5 py-1 rounded-full bg-yellow-500/15 text-yellow-300 border border-yellow-500/30">Pendiente</span>`;
                return `<tr class="hover:bg-green-500/5 transition-colors">
                    <td class="p-4 font-semibold text-white">${student.nombre}</td>
                    <td class="p-4 text-slate-400 font-mono">${student.doc}</td>
                    <td class="p-4">${status}</td>
                    <td class="p-4 text-center"><div class="flex justify-center gap-2">
                        <button onclick="setStudentGrade(${student.id}, true)" class="px-3 py-1.5 rounded-lg bg-green-500/15 text-green-300 border border-green-500/30 text-[11px] font-semibold">Aprobar</button>
                        <button onclick="setStudentGrade(${student.id}, false)" class="px-3 py-1.5 rounded-lg bg-red-500/15 text-red-300 border border-red-500/30 text-[11px] font-semibold">No aprobar</button>
                    </div></td>
                </tr>`;
            }).join('');
        }

        function setStudentGrade(id, approved) {
            const student = aprendicesData.find(a => a.id === id);
            if (!student) return;
            student.aprobado = approved;
            renderGradesTable();
        }

        function setBulkGrade(approved) {
            if (!selectedGradeFicha) return;
            aprendicesData.filter(a => a.ficha === selectedGradeFicha).forEach(a => a.aprobado = approved);
            renderGradesTable();
        }

function renderAprendicesTable() {
            const tbody = document.getElementById('aprendices-tbody');
            tbody.innerHTML = '';

            let filtered = aprendicesData.filter(item => {
                if (currentFilter === 'todos') return true;
                if (currentFilter === 'activo') return item.estado === 'Activo';
                if (currentFilter === 'riesgo') return item.estado === 'En Riesgo';
                if (currentFilter === 'inactivo') return item.estado === 'Inactivo';
                return true;
            });

            filtered.forEach((item) => {
                let badgeClass = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
                if (item.estado === 'En Riesgo') badgeClass = 'bg-amber-500/20 text-amber-300 border-amber-500/30';
                if (item.estado === 'Inactivo') badgeClass = 'bg-rose-500/20 text-rose-300 border-rose-500/30';

                let row = `
                    <tr class="hover:bg-purple-900/20 transition-colors">
                        <td class="p-4 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-purple-800/40 border border-purple-600/30 flex items-center justify-center font-bold text-white text-xs">
                                ${item.nombre.split(' ').map(n=>n[0]).slice(0,2).join('')}
                            </div>
                            <div>
                                <p class="font-semibold text-white">${item.nombre}</p>
                                <p class="text-[10px] text-purple-400">${item.email}</p>
                            </div>
                        </td>
                        <td class="p-4 font-mono text-purple-300">${item.doc}</td>
                        <td class="p-4"><span class="font-medium text-white">${item.ficha}</span></td>
                        <td class="p-4"><span class="px-2.5 py-1 rounded-full text-[10px] font-medium border ${badgeClass}">${item.estado}</span></td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-purple-950 rounded-full h-2 overflow-hidden border border-purple-800/30">
                                    <div class="bg-gradient-to-r from-brand-500 to-emerald-400 h-2 rounded-full" style="width: ${item.asistencia}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold">${item.asistencia}%</span>
                                <span class="text-[10px] text-rose-300">${item.fallas || 0} falla(s)</span>
                            </div>
                        </td>
                        <td class="p-4 font-bold text-white">${item.promedio}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="abrirExpedienteAprendiz(${item.id})" title="Ver expediente" class="p-1.5 rounded-lg hover:bg-emerald-500/20 text-emerald-300 hover:text-white">
                                    <i data-lucide="folder-open" class="w-4 h-4"></i>
                                </button>
                                <button onclick="openEditModal(${item.id})" title="Editar" class="p-1.5 rounded-lg hover:bg-purple-800/40 text-purple-300 hover:text-white">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <button onclick="openExcuseModal(${item.id})" title="Gestionar falla / excusa" class="p-1.5 rounded-lg hover:bg-amber-500/20 text-amber-300 hover:text-white">
                                    <i data-lucide="file-check-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteAprendiz(${item.id})" title="Eliminar" class="p-1.5 rounded-lg hover:bg-rose-500/20 text-rose-400 hover:text-rose-200">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            document.getElementById('table-count-info').textContent = `Mostrando ${filtered.length} de ${aprendicesData.length} aprendices`;
            lucide.createIcons();
        }

        function normalizarFichasAprendiz(aprendiz) {
            if (!aprendiz.assignedFichas || !Array.isArray(aprendiz.assignedFichas)) {
                aprendiz.assignedFichas = aprendiz.ficha ? [aprendiz.ficha] : [];
            }
            return aprendiz.assignedFichas;
        }

        function obtenerFichaData(nombreFicha) {
            return fichasAprendizData.find(f => f.nombre === nombreFicha || nombreFicha.startsWith(f.codigo)) || null;
        }

        function obtenerInstructoresFicha(nombreFicha) {
            const ficha = obtenerFichaData(nombreFicha);
            return ficha ? (instructoresPorFicha[ficha.codigo] || []) : [];
        }

        function abrirExpedienteSeleccionado() {
            if (aprendizExpedienteActual) {
                abrirExpedienteAprendiz(aprendizExpedienteActual);
                return;
            }
            const primero = aprendicesData[0];
            if (primero) abrirExpedienteAprendiz(primero.id);
        }

        function abrirExpedienteAprendiz(id) {
            const aprendiz = aprendicesData.find(a => a.id === id);
            if (!aprendiz) return;
            aprendizExpedienteActual = id;
            normalizarFichasAprendiz(aprendiz);

            document.getElementById('expediente-title').textContent = aprendiz.nombre;
            document.getElementById('expediente-subtitle').textContent = `${aprendiz.doc} · ${aprendiz.email}`;
            document.getElementById('modal-expediente-aprendiz').classList.remove('hidden');

            renderExpedienteAprendiz(aprendiz);
            lucide.createIcons();
        }

        function renderExpedienteAprendiz(aprendiz) {
            const transBox=document.getElementById('expediente-transversales'); if(transBox){ transBox.innerHTML=(aprendiz.transversales||[]).map(t=>`<div class="bg-purple-950/30 border border-purple-800/30 rounded-xl p-4"><div class="font-bold text-white">${t.nombre}</div><div class="text-xs text-emerald-300 mt-1">Instructor: ${t.instructor}</div><div class="text-xs text-purple-300 mt-2">${t.horas} horas · ${t.horario}</div></div>`).join('') || '<p class="text-xs text-purple-500">No tiene transversales asignados.</p>'; }
            const fichas = normalizarFichasAprendiz(aprendiz);
            const estadoClass = aprendiz.estado === 'Activo'
                ? 'text-emerald-300 bg-emerald-500/10 border-emerald-500/30'
                : aprendiz.estado === 'En Riesgo'
                    ? 'text-amber-300 bg-amber-500/10 border-amber-500/30'
                    : 'text-rose-300 bg-rose-500/10 border-rose-500/30';

            document.getElementById('expediente-datos').innerHTML = `
                <div class="rounded-xl bg-[#17221F] border border-green-900/30 p-4">
                    <span class="text-[10px] uppercase text-slate-500">Documento</span>
                    <p class="text-sm font-bold text-white mt-1">${aprendiz.doc}</p>
                </div>
                <div class="rounded-xl bg-[#17221F] border border-green-900/30 p-4">
                    <span class="text-[10px] uppercase text-slate-500">Estado</span>
                    <p class="mt-1"><span class="inline-flex px-2 py-1 rounded-full border text-[10px] font-bold ${estadoClass}">${aprendiz.estado}</span></p>
                </div>
                <div class="rounded-xl bg-[#17221F] border border-green-900/30 p-4">
                    <span class="text-[10px] uppercase text-slate-500">Asistencia</span>
                    <p class="text-sm font-bold text-emerald-300 mt-1">${aprendiz.asistencia}%</p>
                </div>
                <div class="rounded-xl bg-[#17221F] border border-green-900/30 p-4">
                    <span class="text-[10px] uppercase text-slate-500">Promedio</span>
                    <p class="text-sm font-bold text-white mt-1">${aprendiz.promedio}</p>
                </div>
            `;

            document.getElementById('expediente-fichas').innerHTML = fichas.length
                ? fichas.map((nombre, index) => {
                    const ficha = obtenerFichaData(nombre);
                    return `
                        <div class="rounded-xl border border-green-900/30 bg-[#101817] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-white">${ficha ? ficha.nombre : nombre}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">${ficha ? ficha.programa : 'Ficha de formación'}</p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-green-500/10 text-green-300 border border-green-500/20">Asignada</span>
                            </div>
                            ${ficha ? `<div class="flex flex-wrap gap-2 mt-3">
                                <span class="px-2 py-1 rounded-lg bg-slate-800 text-[9px] text-slate-300">${ficha.jornada}</span>
                                <span class="px-2 py-1 rounded-lg bg-slate-800 text-[9px] text-slate-300">${ficha.estado}</span>
                            </div>` : ''}
                            <button onclick="verInstructoresFicha('${encodeURIComponent(nombre)}')" class="mt-3 w-full py-2 rounded-lg text-[10px] font-bold text-green-300 border border-green-800/50 hover:bg-green-900/20">
                                Ver instructores de esta ficha
                            </button>
                        </div>
                    `;
                }).join('')
                : `<div class="rounded-xl border border-dashed border-green-900/40 p-5 text-center text-xs text-slate-500 md:col-span-2">Este aprendiz todavía no tiene fichas asignadas.</div>`;

            actualizarInstructoresExpediente(fichas[0] || null);

            const resumen = document.getElementById('expediente-resumen');
            if (resumen) {
                resumen.innerHTML = `
                    <div class="rounded-xl bg-purple-950/40 border border-purple-900/40 p-4">
                        <span class="text-[10px] uppercase tracking-wider text-purple-400">Aprendiz</span>
                        <p class="text-sm font-bold text-white mt-1">${aprendiz.nombre}</p>
                    </div>
                    <div class="rounded-xl bg-purple-950/40 border border-purple-900/40 p-4">
                        <span class="text-[10px] uppercase tracking-wider text-purple-400">Fichas</span>
                        <p class="text-sm font-bold text-brand-300 mt-1">${fichas.length} asignada(s)</p>
                    </div>
                    <div class="rounded-xl bg-purple-950/40 border border-purple-900/40 p-4">
                        <span class="text-[10px] uppercase tracking-wider text-purple-400">Instructoría</span>
                        <p class="text-sm font-bold text-purple-200 mt-1">${fichas.length ? obtenerInstructoresFicha(fichas[0]).length : 0} instructor(es)</p>
                    </div>
                `;
            }
        }

        function actualizarInstructoresExpediente(nombreFicha) {
            const cont = document.getElementById('expediente-instructores');
            const label = document.getElementById('expediente-ficha-instructores-label');
            if (!cont) return;

            const instructores = nombreFicha ? obtenerInstructoresFicha(nombreFicha) : [];
            label.textContent = nombreFicha ? `Instructores asignados a ${nombreFicha}` : 'Seleccione una ficha.';
            cont.innerHTML = instructores.length
                ? instructores.map(ins => `
                    <div class="rounded-xl bg-[#101817] border border-green-900/30 p-4 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-green-900/30 border border-green-700/30 flex items-center justify-center text-green-300 font-bold">
                            ${ins.nombre.split(' ').map(n => n[0]).slice(0,2).join('')}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white">${ins.nombre}</p>
                            <p class="text-[10px] text-green-300">${ins.rol}</p>
                            <p class="text-[10px] text-slate-500">${ins.area}</p>
                            <p class="text-[9px] text-slate-500 truncate">${ins.email}</p>
                        </div>
                    </div>
                `).join('')
                : `<div class="rounded-xl border border-dashed border-green-900/40 p-5 text-center text-xs text-slate-500 md:col-span-2">No hay instructores registrados para esta ficha.</div>`;

            const resumenFicha = document.getElementById('ficha-seleccionada-resumen');
            if (resumenFicha) {
                resumenFicha.innerHTML = nombreFicha
                    ? `<p class="text-sm font-bold text-white">${nombreFicha}</p>
                       <p class="text-[10px] text-slate-400 mt-1">${instructores.length} instructor(es) asignado(s)</p>
                       <button onclick="actualizarInstructoresExpediente(decodeURIComponent('${encodeURIComponent(nombreFicha)}'))" class="mt-3 text-[10px] text-green-300 hover:text-white">Actualizar vista</button>`
                    : 'Seleccione una ficha desde el expediente.';
            }
            lucide.createIcons();
        }

        function verInstructoresFicha(nombreCodificado) {
            const nombreFicha = decodeURIComponent(nombreCodificado);
            actualizarInstructoresExpediente(nombreFicha);
            const modal = document.getElementById('modal-expediente-aprendiz');
            if (modal && modal.classList.contains('hidden')) modal.classList.remove('hidden');
        }

        function abrirAsignarFicha() {
            const aprendiz = aprendicesData.find(a => a.id === aprendizExpedienteActual);
            if (!aprendiz) {
                abrirExpedienteSeleccionado();
                return;
            }
            normalizarFichasAprendiz(aprendiz);
            const select = document.getElementById('select-ficha-aprendiz');
            const disponibles = fichasAprendizData.filter(f => !aprendiz.assignedFichas.includes(f.nombre));
            select.innerHTML = `<option value="">Seleccione una ficha...</option>` +
                disponibles.map(f => `<option value="${f.nombre}">${f.nombre} · ${f.programa}</option>`).join('');
            document.getElementById('asignar-ficha-aprendiz-nombre').textContent = aprendiz.nombre;
            document.getElementById('preview-ficha-aprendiz').textContent = disponibles.length
                ? 'La ficha seleccionada quedará visible inmediatamente en el expediente del aprendiz.'
                : 'Este aprendiz ya tiene todas las fichas disponibles asignadas.';
            document.getElementById('modal-asignar-ficha-aprendiz').classList.remove('hidden');
            lucide.createIcons();
        }

        function cerrarAsignarFicha() {
            document.getElementById('modal-asignar-ficha-aprendiz').classList.add('hidden');
        }

        function guardarFichaAprendiz(event) {
            event.preventDefault();
            const aprendiz = aprendicesData.find(a => a.id === aprendizExpedienteActual);
            const ficha = document.getElementById('select-ficha-aprendiz').value;
            if (!aprendiz || !ficha) return;

            normalizarFichasAprendiz(aprendiz);
            if (!aprendiz.assignedFichas.includes(ficha)) aprendiz.assignedFichas.push(ficha);
            aprendiz.ficha = aprendiz.assignedFichas[0] || ficha;

            cerrarAsignarFicha();
            renderAprendicesTable();
            abrirExpedienteAprendiz(aprendiz.id);
            const codigo = obtenerFichaData(ficha)?.codigo;
            actualizarInstructoresExpediente(ficha);

            Swal.fire({
                icon: 'success',
                title: 'Ficha asignada',
                text: `${ficha} fue asignada a ${aprendiz.nombre}${codigo ? `.` : '.'}`,
                confirmButtonColor: '#39A900'
            });
        }

        function cerrarExpediente() {
            document.getElementById('modal-expediente-aprendiz').classList.add('hidden');
        }

        function filterAprendices(filterType) {
            currentFilter = filterType;
            renderAprendicesTable();
        }

        function filterAprendicesTable() {
            const query = document.getElementById('aprendiz-search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#table-aprendices tbody tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
            });
        }

        function openExcuseModal(id) {
            const student = aprendicesData.find(a => a.id === id);
            if (!student) return;
            document.getElementById('excuse-student-id').value = id;
            document.getElementById('excuse-student-name').textContent = `${student.nombre} · ${student.fallas || 0} falla(s)`;
            document.getElementById('excuse-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('excuse-status').value = 'justificada';
            document.getElementById('excuse-note').value = '';
            document.getElementById('modal-excusa').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeExcuseModal() {
            document.getElementById('modal-excusa').classList.add('hidden');
        }

        function saveExcuse(event) {
            event.preventDefault();
            const id = Number(document.getElementById('excuse-student-id').value);
            const student = aprendicesData.find(a => a.id === id);
            if (!student) return;
            if (document.getElementById('excuse-status').value === 'justificada') {
                student.fallas = Math.max(0, (student.fallas || 0) - 1);
            }
            renderAprendicesTable();
            closeExcuseModal();
            Swal.fire({icon:'success', title:'Falla actualizada', text:'La novedad y la excusa fueron registradas.', confirmButtonColor:'#39A900'});
        }

        function openCreateModal() {
            document.getElementById('modal-title').innerHTML = `<i data-lucide="user-plus" class="w-5 h-5 text-brand-400"></i> Registrar Nuevo Aprendiz`;
            document.getElementById('form-aprendiz').reset();
            document.getElementById('edit-index').value = "-1";

            const modal = document.getElementById('modal-aprendiz');
            const container = document.getElementById('modal-container');
            modal.classList.remove('hidden');
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function openEditModal(id) {
            const item = aprendicesData.find(a => a.id === id);
            if (!item) return;

            document.getElementById('modal-title').innerHTML = `<i data-lucide="edit-3" class="w-5 h-5 text-brand-400"></i> Editar Aprendiz`;
            document.getElementById('edit-index').value = item.id;
            document.getElementById('input-nombre').value = item.nombre;
            document.getElementById('input-documento').value = item.doc;
            document.getElementById('input-correo').value = item.email;
            document.getElementById('input-ficha').value = item.ficha;
            document.getElementById('input-estado').value = item.estado;
            document.getElementById('input-promedio').value = item.promedio;

            const modal = document.getElementById('modal-aprendiz');
            const container = document.getElementById('modal-container');
            modal.classList.remove('hidden');
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            const modal = document.getElementById('modal-aprendiz');
            const container = document.getElementById('modal-container');
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 200);
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            const editId = parseInt(document.getElementById('edit-index').value);

            const nombre = document.getElementById('input-nombre').value;
            const doc = document.getElementById('input-documento').value;
            const email = document.getElementById('input-correo').value;
            const ficha = document.getElementById('input-ficha').value;
            const estado = document.getElementById('input-estado').value;
            const promedio = parseFloat(document.getElementById('input-promedio').value);

            if (editId === -1) {
                aprendicesData.unshift({ id: Date.now(), nombre, doc, email, ficha, estado, asistencia: 90, promedio });
            } else {
                const index = aprendicesData.findIndex(a => a.id === editId);
                if (index !== -1) {
                    aprendicesData[index] = { ...aprendicesData[index], nombre, doc, email, ficha, estado, promedio };
                }
            }

            closeModal();
            renderAprendicesTable();
            switchView('aprendices');
            Swal.fire('Guardado', 'Los datos del aprendiz fueron actualizados.', 'success');
        }

        function deleteAprendiz(id) {
            Swal.fire({
                title: '¿Confirmar eliminación?',
                text: "Esta acción removerá permanentemente al aprendiz.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    aprendicesData = aprendicesData.filter(a => a.id !== id);
                    renderAprendicesTable();
                    Swal.fire('Eliminado', 'Aprendiz removido con éxito.', 'success');
                }
            });
        }

        // EVENTS AND NOTIFICATIONS RENDER
        function renderCalendarDays() {
            const grid = document.getElementById('calendar-days-grid');
            grid.innerHTML = '';

            for (let day = 1; day <= 30; day++) {
                let bgClass = "bg-purple-950/30 border-purple-900/30 hover:border-brand-500/50";
                if (day === 24 || day === 28) {
                    bgClass = "bg-brand-600 text-white font-bold shadow-lg shadow-brand-600/30 border-brand-400";
                }

                let dayCard = `
                    <div class="h-16 p-2 rounded-xl border ${bgClass} flex flex-col justify-between transition-all cursor-pointer">
                        <span class="text-xs font-semibold">${day}</span>
                    </div>
                `;
                grid.innerHTML += dayCard;
            }
        }

        function renderEventsList() {
            const container = document.getElementById('events-list-container');
            container.innerHTML = '';

            eventsData.forEach(ev => {
                let card = `
                    <div class="p-3.5 rounded-2xl bg-purple-950/40 border border-purple-800/30 space-y-1.5">
                        <span class="text-[10px] font-semibold text-purple-400">Septiembre ${ev.day}, 2026</span>
                        <h4 class="text-xs font-bold text-white">${ev.title}</h4>
                        <span class="text-[11px] text-brand-300 block">${ev.time}</span>
                    </div>
                `;
                container.innerHTML += card;
            });
        }

        function renderNotificationsList() {
            const container = document.getElementById('notifications-list-container');
            container.innerHTML = '';

            notificationsData.forEach(n => {
                let card = `
                    <div class="p-4 rounded-xl bg-purple-900/30 border border-purple-600/40 flex items-start justify-between gap-4">
                        <div>
                            <h4 class="text-xs font-bold text-white">${n.title}</h4>
                            <p class="text-xs text-purple-300 mt-1">${n.text}</p>
                            <span class="text-[10px] text-purple-400 mt-2 block">${n.time}</span>
                        </div>
                    </div>
                `;
                container.innerHTML += card;
            });
        }

        function markAllNotificationsRead() {
            notificationsData = [];
            renderNotificationsList();
            document.getElementById('notif-badge-count').textContent = '0';
        }

        function confirmLogout() {
            Swal.fire({
                title: '¿Cerrar Sesión?',
                text: "Saldrás del panel de gestión educativa.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Cerrar Sesión'
            }).then((res) => {
                if(res.isConfirmed) {
                    Swal.fire('Sesión Finalizada', 'Has salido del sistema.', 'info');
                }
            });
        }

        // CHART INITIALIZATION
        function initCharts() {
            const ctx1 = document.getElementById('performanceChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
                    datasets: [{
                        label: 'Horas Dictadas',
                        data: [36, 40, 38, 42],
                        backgroundColor: '#8b5cf6',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: 'rgba(139, 92, 246, 0.1)' }, ticks: { color: '#94a3b8' } },
                        y: { grid: { color: 'rgba(139, 92, 246, 0.1)' }, ticks: { color: '#94a3b8' } }
                    }
                }
            });

            const ctx2 = document.getElementById('statusChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Óptimo', 'Regular', 'Defectuoso'],
                    datasets: [{
                        data: [85, 10, 5],
                        backgroundColor: ['#8b5cf6', '#f59e0b', '#f43f5e'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '75%'
                }
            });
        }
    
        document.addEventListener('DOMContentLoaded', ()=>{ renderTransversales(); renderFichasInstructor(); renderJefaturaFicha(); });
document.addEventListener('DOMContentLoaded',()=>{renderTransversales();renderFichasInstructor();renderJefaturaFicha();});
</script>
</body>
</html>