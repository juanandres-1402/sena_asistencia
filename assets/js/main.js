/**
 * assets/js/main.js
 * ---------------------------------------------------------------------
 * Comportamientos generales de la interfaz que aplican a todo el
 * sistema (no específicos de un módulo). Los scripts propios de un
 * módulo (por ejemplo control de ingreso en portería) viven en su
 * propio archivo dentro de /assets/js/.
 * ---------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', function () {
    // Confirmación genérica para botones marcados con data-confirmar.
    // Se usa, por ejemplo, para "Finalizar ficha" o "Eliminar equipo".
    document.querySelectorAll('[data-confirmar]').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            const mensaje = boton.getAttribute('data-confirmar') || '¿Está seguro de continuar?';
            if (!window.confirm(mensaje)) {
                evento.preventDefault();
                evento.stopPropagation();
            }
        });
    });

    // Cierra automáticamente las alertas de Bootstrap después de 4 segundos.
    document.querySelectorAll('.alert.alert-auto-cerrar').forEach(function (alerta) {
        setTimeout(function () {
            const instancia = bootstrap.Alert.getOrCreateInstance(alerta);
            instancia.close();
        }, 4000);
    });
});
