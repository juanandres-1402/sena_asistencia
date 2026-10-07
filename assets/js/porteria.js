/**
 * assets/js/porteria.js
 * ---------------------------------------------------------------------
 * Lógica de la pantalla "Control de Ingreso" (views/porteria/ingreso.php).
 *
 * Flujo:
 *   1. El usuario de portería escribe/escanea el documento y envía el
 *      formulario -> se consulta api/buscar_aprendiz.php
 *   2. Se pinta la tarjeta del aprendiz con su estado calculado.
 *   3. El usuario responde si trae portátil (y llena los datos si es
 *      un equipo nuevo, o se reconoce automáticamente si ya existía).
 *   4. Al confirmar, se llama api/registrar_ingreso.php, que guarda el
 *      ingreso + genera la asistencia automáticamente + guarda el
 *      equipo si aplica.
 * ---------------------------------------------------------------------
 */

const formBuscar        = document.getElementById('formBuscarAprendiz');
const inputDocumento    = document.getElementById('inputDocumento');
const contenedorResultado = document.getElementById('contenedorResultado');
const contenedorAlertas = document.getElementById('contenedorAlertas');
const plantilla          = document.getElementById('plantillaTarjetaAprendiz');
const tablaUltimosIngresos = document.getElementById('tablaUltimosIngresos');

let aprendizActual = null; // Guarda en memoria los datos del último aprendiz consultado.

/** Muestra una alerta Bootstrap temporal (éxito, error, advertencia). */
function mostrarAlerta(tipo, mensaje) {
    contenedorAlertas.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible alert-auto-cerrar mt-3" role="alert">
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    setTimeout(() => {
        const alerta = contenedorAlertas.querySelector('.alert');
        if (alerta) bootstrap.Alert.getOrCreateInstance(alerta).close();
    }, 4000);
}

/** Devuelve la clase de badge y el emoji según el estado calculado. */
function estilosPorEstado(estado) {
    if (estado === 'LLEGADA_TARDE') {
        return { clase: 'bg-warning text-dark', texto: '🟡 LLEGADA TARDE' };
    }
    return { clase: 'bg-success', texto: '🟢 PRESENTE' };
}

// ------------------------------------------------------------------
// 1) Buscar aprendiz
// ------------------------------------------------------------------
formBuscar.addEventListener('submit', async function (evento) {
    evento.preventDefault();
    const documento = inputDocumento.value.trim();
    if (!documento) return;

    contenedorResultado.innerHTML = '<p class="text-center text-muted">Buscando...</p>';

    try {
        const respuesta = await fetch(`${RUTA_BASE}/api/buscar_aprendiz.php?documento=${encodeURIComponent(documento)}`);
        const datos = await respuesta.json();

        if (!datos.ok) {
            contenedorResultado.innerHTML = '';
            mostrarAlerta('danger', datos.mensaje);
            return;
        }

        if (datos.aprendiz.ya_registrado) {
            contenedorResultado.innerHTML = '';
            mostrarAlerta('warning', `${datos.aprendiz.nombre_completo} ya registró su ingreso hoy a las ${datos.aprendiz.hora_registrada}.`);
            return;
        }

        aprendizActual = datos.aprendiz;
        pintarTarjeta(aprendizActual);
    } catch (error) {
        contenedorResultado.innerHTML = '';
        mostrarAlerta('danger', 'No fue posible conectar con el servidor. Intente nuevamente.');
    }
});

// ------------------------------------------------------------------
// 2) Pintar la tarjeta del aprendiz con su estado
// ------------------------------------------------------------------
function pintarTarjeta(aprendiz) {
    const nodo = plantilla.content.cloneNode(true);

    nodo.querySelector('[data-campo="nombre"]').textContent = aprendiz.nombre_completo;
    nodo.querySelector('[data-campo="documento"]').textContent = 'Documento: ' + aprendiz.documento;
    nodo.querySelector('[data-campo="ficha"]').textContent = aprendiz.numero_ficha;
    nodo.querySelector('[data-campo="programa"]').textContent = aprendiz.programa;
    nodo.querySelector('[data-campo="horario"]').textContent = aprendiz.horario;
    nodo.querySelector('[data-campo="hora_actual"]').textContent = aprendiz.hora_actual;

    const estilos = estilosPorEstado(aprendiz.estado_estimado);
    const chip = nodo.querySelector('[data-campo="estado-chip"]');
    chip.textContent = estilos.texto;
    chip.classList.add(...estilos.clase.split(' '), 'badge');

    // Si el aprendiz ya tiene un equipo registrado, se deja pre-cargado.
    const radioSi = nodo.getElementById('equipoSi');
    const radioNo = nodo.getElementById('equipoNo');
    const formEquipo = nodo.getElementById('formEquipo');
    const aviso = nodo.getElementById('equipoReconocidoAviso');

    let codigoEquipoExistente = null;

    if (aprendiz.equipo_sugerido) {
        radioSi.checked = true;
        formEquipo.classList.remove('d-none');
        aviso.classList.remove('d-none');
        aviso.innerHTML = `<i class="bi bi-check-circle"></i> Equipo reconocido: <strong>${aprendiz.equipo_sugerido.codigo_equipo}</strong> (se asociará automáticamente).`;
        nodo.getElementById('equipoMarca').value = aprendiz.equipo_sugerido.marca || '';
        nodo.getElementById('equipoModelo').value = aprendiz.equipo_sugerido.modelo || '';
        nodo.getElementById('equipoSerie').value = aprendiz.equipo_sugerido.numero_serie || '';
        nodo.getElementById('equipoCargador').value = aprendiz.equipo_sugerido.codigo_cargador || '';
        codigoEquipoExistente = aprendiz.equipo_sugerido.codigo_equipo;
    }

    [radioSi, radioNo].forEach(radio => {
        radio.addEventListener('change', () => {
            formEquipo.classList.toggle('d-none', !radioSi.checked);
        });
    });

    // Botón confirmar ingreso.
    nodo.getElementById('btnConfirmarIngreso').addEventListener('click', async function (evento) {
        const boton = evento.currentTarget;
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Registrando...';

        const traeEquipo = radioSi.checked;
        const payload = {
            id_aprendiz: aprendiz.id_aprendiz,
            id_ficha: aprendiz.id_ficha,
            trae_equipo: traeEquipo,
            equipo: traeEquipo ? {
                tipo: 'portatil',
                marca: document.getElementById('equipoMarca').value,
                modelo: document.getElementById('equipoModelo').value,
                numero_serie: document.getElementById('equipoSerie').value,
                codigo_cargador: document.getElementById('equipoCargador').value,
                codigo_existente: codigoEquipoExistente,
            } : null,
        };

        try {
            const respuesta = await fetch(`${RUTA_BASE}/api/registrar_ingreso.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const resultado = await respuesta.json();

            if (!resultado.ok) {
                mostrarAlerta('danger', resultado.mensaje);
                boton.disabled = false;
                boton.innerHTML = '<i class="bi bi-check2-circle"></i> Registrar ingreso';
                return;
            }

            const estiloFinal = estilosPorEstado(resultado.estado);
            mostrarAlerta('success', `Ingreso registrado correctamente. Estado: <strong>${estiloFinal.texto}</strong> a las ${resultado.hora}.`);
            agregarFilaTabla(aprendiz, resultado);
            contenedorResultado.innerHTML = '';
            inputDocumento.value = '';
            inputDocumento.focus();
        } catch (error) {
            mostrarAlerta('danger', 'No fue posible registrar el ingreso. Intente nuevamente.');
            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-check2-circle"></i> Registrar ingreso';
        }
    });

    contenedorResultado.innerHTML = '';
    contenedorResultado.appendChild(nodo);
}

/** Agrega una fila nueva arriba de la tabla de "últimos ingresos" sin recargar la página. */
function agregarFilaTabla(aprendiz, resultado) {
    const filaVacia = tablaUltimosIngresos.querySelector('td[colspan]');
    if (filaVacia) filaVacia.closest('tr').remove();

    const estilos = estilosPorEstado(resultado.estado);
    const fila = document.createElement('tr');
    fila.innerHTML = `
        <td>${resultado.hora}</td>
        <td>${aprendiz.nombre_completo}</td>
        <td>${aprendiz.numero_ficha}</td>
        <td><span class="badge ${estilos.clase}">${estilos.texto.replace(/^[^ ]+ /, '')}</span></td>
        <td>${resultado.equipo ? '💻' : '—'}</td>
    `;
    tablaUltimosIngresos.prepend(fila);
}
