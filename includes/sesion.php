<?php
/**
 * includes/sesion.php
 * ---------------------------------------------------------------------
 * Manejo de la sesión de usuario y control de acceso por rol.
 * Todas las vistas protegidas deben incluir este archivo de primero
 * y llamar a exigirRol([...roles permitidos...]).
 * ---------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Devuelve true si hay una sesión activa. */
function usuarioAutenticado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Corta la ejecución y redirige al login si no hay sesión, o si el
 * rol del usuario en sesión no está dentro de los roles permitidos
 * para la pantalla actual.
 */
function exigirRol(array $rolesPermitidos): void
{
    if (!usuarioAutenticado()) {
        header('Location: ' . rutaBase() . '/index.php');
        exit;
    }
    if (!in_array($_SESSION['usuario_rol'], $rolesPermitidos, true)) {
        header('Location: ' . rutaBase() . '/acceso_denegado.php');
        exit;
    }
}

/**
 * Nombre de la carpeta raíz del proyecto, TAL COMO QUEDA PUBLICADA en
 * el servidor web. Por ejemplo, si el proyecto se sube a:
 *   htdocs/sena_asistencia      (XAMPP/WAMP en Windows)
 *   /var/www/html/sena_asistencia  (Linux/Apache)
 * entonces se accede desde el navegador como:
 *   http://localhost/sena_asistencia/...
 *
 * TODAS las rutas del sistema (menús, formularios, llamadas AJAX,
 * CSS, JS) se arman a partir de esta única constante, por lo que si
 * el proyecto se renombra o se mueve de carpeta, SOLO hay que
 * actualizar este valor aquí.
 *
 * IMPORTANTE: debe empezar con "/" y NO debe terminar en "/".
 * Si el proyecto se sirve exactamente desde la raíz del dominio
 * (ej: http://midominio.com/index.php, sin subcarpeta), deje este
 * valor como cadena vacía: ''
 */
define('RUTA_BASE_PROYECTO', '/sena_asistencia');

/**
 * Devuelve el prefijo de URL que debe anteponerse a toda ruta interna
 * del sistema (ej: rutaBase() . '/logout.php' => '/sena_asistencia/logout.php').
 */
function rutaBase(): string
{
    return RUTA_BASE_PROYECTO;
}
