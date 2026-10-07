<?php
/**
 * includes/funciones.php
 * ---------------------------------------------------------------------
 * Funciones de negocio reutilizables en todo el sistema.
 *
 * Aquí vive la REGLA DE ORO del proyecto:
 *   "La portería registra el ingreso. El sistema calcula la asistencia
 *    automáticamente. El instructor solo consulta."
 *
 * Por eso toda la lógica de "¿a qué hora es tarde?" vive en un solo
 * lugar (calcularEstadoAsistencia) y se alimenta de la configuración
 * de cada ficha (hora de inicio del horario + tolerancia en minutos),
 * NUNCA de un valor fijo escrito en el código.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Calcula el estado de asistencia comparando la hora real de ingreso
 * contra la hora de inicio del horario de la ficha + la tolerancia
 * configurada por el coordinador académico.
 *
 * Reglas (ver puntos 5 y 6 del documento de requerimientos):
 *  - Si llega antes o dentro del límite de tolerancia -> PRESENTE
 *  - Si llega después del límite de tolerancia -> LLEGADA_TARDE
 *  - En NINGÚN caso se bloquea el ingreso por llegar tarde.
 *
 * @param string $horaInicio   Hora de inicio del horario, formato HH:MM
 * @param int    $toleranciaMin Minutos de tolerancia configurados en la ficha
 * @param string $horaIngreso  Hora real de ingreso, formato HH:MM o HH:MM:SS
 * @return string 'PRESENTE' | 'LLEGADA_TARDE'
 */
function calcularEstadoAsistencia(string $horaInicio, int $toleranciaMin, string $horaIngreso): string
{
    $inicio  = strtotime($horaInicio);
    $limite  = $inicio + ($toleranciaMin * 60);
    $ingreso = strtotime($horaIngreso);

    // Un ingreso anticipado (antes del inicio de la jornada) también
    // se considera dentro del tiempo permitido: se relaciona con el
    // horario correspondiente, tal como pide el punto 6 del documento.
    return ($ingreso <= $limite) ? 'PRESENTE' : 'LLEGADA_TARDE';
}

/**
 * Devuelve la hora máxima de ingreso permitida (para mostrarla en
 * pantalla) a partir de la hora de inicio y la tolerancia.
 */
function calcularHoraMaxima(string $horaInicio, int $toleranciaMin): string
{
    $limite = strtotime($horaInicio) + ($toleranciaMin * 60);
    return date('H:i', $limite);
}

/**
 * Registra una entrada en la tabla de auditoría. TODO cambio relevante
 * del sistema (creación de fichas, cambios de horario, modificaciones
 * de asistencia, registro de equipos, etc.) debe pasar por aquí.
 * Ver punto 18 del documento de requerimientos.
 */
function registrarAuditoria(?int $idUsuario, string $usuarioNombre, string $accion, string $entidad = '', string $valorAnterior = '', string $valorNuevo = ''): void
{
    $pdo = obtenerConexion();
    $stmt = $pdo->prepare("
        INSERT INTO auditoria (id_usuario, usuario_nombre, accion, entidad, valor_anterior, valor_nuevo, fecha_hora)
        VALUES (:id_usuario, :usuario_nombre, :accion, :entidad, :valor_anterior, :valor_nuevo, :fecha_hora)
    ");
    $stmt->execute([
        ':id_usuario'     => $idUsuario,
        ':usuario_nombre' => $usuarioNombre,
        ':accion'         => $accion,
        ':entidad'        => $entidad,
        ':valor_anterior' => $valorAnterior,
        ':valor_nuevo'    => $valorNuevo,
        ':fecha_hora'     => date('Y-m-d H:i:s'),
    ]);
}

/**
 * Crea una notificación para un usuario específico (o general si
 * $idUsuarioDestino es NULL). Antes de crearla revisa la tabla
 * "configuraciones" para saber si ese tipo de evento tiene las
 * notificaciones activadas (punto 17 del documento).
 */
function generarNotificacion(?int $idUsuarioDestino, string $mensaje, string $tipo): void
{
    $pdo = obtenerConexion();

    $claveConfig = [
        'LLEGADA_TARDE' => 'notif_llegada_tarde',
        'SIN_INGRESO'   => 'notif_sin_ingreso',
        'MODIFICACION'  => 'notif_modificacion',
        // Las novedades MUY_IMPORTANTE de un ambiente no se pueden desactivar:
        // deben notificarse siempre, de forma inmediata (ver requerimiento).
    ][$tipo] ?? null;

    if ($claveConfig) {
        $stmt = $pdo->prepare("SELECT valor FROM configuraciones WHERE clave = :clave");
        $stmt->execute([':clave' => $claveConfig]);
        $activo = $stmt->fetchColumn();
        if ($activo !== false && $activo === '0') {
            return; // Este tipo de notificación está desactivado por el coordinador.
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO notificaciones (id_usuario_destino, mensaje, tipo, fecha_hora)
        VALUES (:destino, :mensaje, :tipo, :fecha)
    ");
    $stmt->execute([
        ':destino' => $idUsuarioDestino,
        ':mensaje' => $mensaje,
        ':tipo'    => $tipo,
        ':fecha'   => date('Y-m-d H:i:s'),
    ]);
}

/**
 * Genera un código único para un equipo nuevo, con el formato
 * PORT-000001, PORT-000002... (punto 8 del documento).
 */
function generarCodigoEquipo(): string
{
    $pdo = obtenerConexion();
    $total = (int) $pdo->query("SELECT COUNT(*) FROM equipos")->fetchColumn();
    $siguiente = $total + 1;
    return 'PORT-' . str_pad((string) $siguiente, 6, '0', STR_PAD_LEFT);
}

/**
 * Mini sistema de "mensaje flash": guarda un mensaje en sesión para
 * mostrarlo justo después de una redirección (patrón POST-Redirect-GET),
 * evitando que al recargar la página se vuelva a reenviar el formulario.
 */
function establecerMensajeFlash(string $tipo, string $texto): void
{
    $_SESSION['mensaje_flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function obtenerMensajeFlash(): ?array
{
    if (!empty($_SESSION['mensaje_flash'])) {
        $mensaje = $_SESSION['mensaje_flash'];
        unset($_SESSION['mensaje_flash']);
        return $mensaje;
    }
    return null;
}

/** Traduce un estado interno a una etiqueta + color/badge para la UI. */
function etiquetaEstado(string $estado): array
{
    return match ($estado) {
        'PRESENTE'      => ['texto' => 'PRESENTE',        'clase' => 'bg-success',  'icono' => 'bi-check-circle-fill', 'punto' => '🟢'],
        'LLEGADA_TARDE' => ['texto' => 'LLEGADA TARDE',   'clase' => 'bg-warning text-dark', 'icono' => 'bi-clock-fill', 'punto' => '🟡'],
        'SIN_INGRESO'   => ['texto' => 'SIN INGRESO',     'clase' => 'bg-danger',   'icono' => 'bi-x-circle-fill', 'punto' => '🔴'],
        'SALIDA_CAMPO'  => ['texto' => 'SALIDA DE CAMPO', 'clase' => 'bg-info text-dark', 'icono' => 'bi-signpost-2-fill', 'punto' => '⚪'],
        'JUSTIFICADA'   => ['texto' => 'JUSTIFICADA',     'clase' => 'bg-secondary','icono' => 'bi-file-earmark-check-fill', 'punto' => '⚪'],
        default         => ['texto' => $estado,           'clase' => 'bg-secondary','icono' => 'bi-question-circle', 'punto' => '⚪'],
    };
}

// =========================================================================
// Gestión de fichas y usuarios: matrícula de aprendices, carga masiva de
// aprendices e instructores, y utilidades compartidas por coordinador e
// instructor (ver views/aprendices/gestion.php y views/coordinador/*).
// =========================================================================

/**
 * Matricula a un aprendiz en una ficha. Si el documento ya existe en el
 * sistema con rol "aprendiz", se REUTILIZA ese usuario (solo se agrega
 * el vínculo con la ficha); si el documento no existe, se crea un
 * usuario nuevo con contraseña inicial igual a su número de documento.
 * Si el documento ya pertenece a un usuario con otro rol, se rechaza.
 *
 * Esta función es usada tanto por el alta individual como por la carga
 * masiva, para que ambas sigan exactamente las mismas reglas.
 */
function registrarAprendizEnFicha(PDO $pdo, string $documento, string $nombre, string $correo, int $idFicha): array
{
    $documento = trim($documento);
    $nombre = trim($nombre);
    $correo = trim($correo);

    if ($documento === '' || $nombre === '') {
        return ['ok' => false, 'mensaje' => 'El documento y el nombre completo son obligatorios.'];
    }

    $stmt = $pdo->prepare("SELECT u.id, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.id_rol WHERE u.documento = :doc");
    $stmt->execute([':doc' => $documento]);
    $usuarioExistente = $stmt->fetch();

    $esNuevo = false;

    if ($usuarioExistente) {
        if ($usuarioExistente['rol'] !== 'aprendiz') {
            return ['ok' => false, 'mensaje' => "El documento $documento ya pertenece a un usuario con rol \"{$usuarioExistente['rol']}\"."];
        }
        $idUsuario = (int) $usuarioExistente['id'];
    } else {
        $idRolAprendiz = (int) $pdo->query("SELECT id FROM roles WHERE nombre = 'aprendiz'")->fetchColumn();
        $pdo->prepare("
            INSERT INTO usuarios (nombre_completo, documento, correo, clave, id_rol, activo)
            VALUES (:nombre, :doc, :correo, :clave, :rol, 1)
        ")->execute([
            ':nombre' => $nombre,
            ':doc'    => $documento,
            ':correo' => $correo !== '' ? $correo : null,
            ':clave'  => $documento, // Contraseña inicial = número de documento.
            ':rol'    => $idRolAprendiz,
        ]);
        $idUsuario = (int) $pdo->lastInsertId();
        $esNuevo = true;
    }

    // ¿Ya está matriculado en esta misma ficha?
    $stmt = $pdo->prepare("SELECT id FROM aprendices WHERE id_usuario = :usuario AND id_ficha = :ficha");
    $stmt->execute([':usuario' => $idUsuario, ':ficha' => $idFicha]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'mensaje' => "El aprendiz con documento $documento ya está matriculado en esta ficha.", 'nuevo' => $esNuevo];
    }

    $pdo->prepare("INSERT INTO aprendices (id_usuario, id_ficha) VALUES (:usuario, :ficha)")
        ->execute([':usuario' => $idUsuario, ':ficha' => $idFicha]);

    return [
        'ok' => true,
        'nuevo' => $esNuevo,
        'id_usuario' => $idUsuario,
        'mensaje' => "Aprendiz $nombre matriculado correctamente." . ($esNuevo ? ' Contraseña inicial: su número de documento.' : ' (usuario existente vinculado a la ficha)'),
    ];
}

/**
 * Crea un usuario con rol INSTRUCTOR. Solo el coordinador puede invocar
 * esta función (ver exigirRol(['coordinador']) en las vistas que la usan).
 * Rechaza el documento si ya existe en el sistema, sin importar el rol.
 */
function registrarInstructor(PDO $pdo, string $documento, string $nombre, string $correo, string $clave): array
{
    $documento = trim($documento);
    $nombre = trim($nombre);
    $correo = trim($correo);
    $clave = trim($clave);

    if ($documento === '' || $nombre === '' || $clave === '') {
        return ['ok' => false, 'mensaje' => 'Documento, nombre y contraseña son obligatorios.'];
    }

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE documento = :doc");
    $stmt->execute([':doc' => $documento]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'mensaje' => "El documento $documento ya existe en el sistema."];
    }

    $idRolInstructor = (int) $pdo->query("SELECT id FROM roles WHERE nombre = 'instructor'")->fetchColumn();
    $pdo->prepare("
        INSERT INTO usuarios (nombre_completo, documento, correo, clave, id_rol, activo)
        VALUES (:nombre, :doc, :correo, :clave, :rol, 1)
    ")->execute([
        ':nombre' => $nombre,
        ':doc'    => $documento,
        ':correo' => $correo !== '' ? $correo : null,
        ':clave'  => $clave,
        ':rol'    => $idRolInstructor,
    ]);

    return ['ok' => true, 'mensaje' => "Instructor $nombre creado correctamente."];
}

/**
 * Procesa un archivo CSV de carga masiva de aprendices (columnas:
 * documento, nombre_completo, correo) y los matricula en la ficha
 * indicada, reutilizando registrarAprendizEnFicha() fila por fila.
 * La primera fila se descarta automáticamente si es un encabezado
 * (si la primera columna no es numérica).
 */
function procesarCargaMasivaAprendices(PDO $pdo, string $rutaArchivoTemporal, int $idFicha): array
{
    $resumen = ['creados' => 0, 'vinculados' => 0, 'omitidos' => 0, 'errores' => []];

    $manejador = fopen($rutaArchivoTemporal, 'r');
    if (!$manejador) {
        $resumen['errores'][] = 'No fue posible leer el archivo subido.';
        return $resumen;
    }

    $numeroFila = 0;
    while (($fila = fgetcsv($manejador, 0, ',')) !== false) {
        $numeroFila++;
        if (count($fila) === 1 && trim($fila[0]) === '') {
            continue; // Línea vacía.
        }
        if ($numeroFila === 1 && !is_numeric(trim($fila[0] ?? ''))) {
            continue; // Fila de encabezado (documento,nombre_completo,correo).
        }

        $documento = trim($fila[0] ?? '');
        $nombre    = trim($fila[1] ?? '');
        $correo    = trim($fila[2] ?? '');

        if ($documento === '' || $nombre === '') {
            $resumen['omitidos']++;
            $resumen['errores'][] = "Fila $numeroFila: documento o nombre vacío, se omitió.";
            continue;
        }

        $resultado = registrarAprendizEnFicha($pdo, $documento, $nombre, $correo, $idFicha);
        if ($resultado['ok']) {
            $resultado['nuevo'] ? $resumen['creados']++ : $resumen['vinculados']++;
        } else {
            $resumen['omitidos']++;
            $resumen['errores'][] = "Fila $numeroFila ($documento): {$resultado['mensaje']}";
        }
    }
    fclose($manejador);

    return $resumen;
}

/**
 * Procesa un archivo CSV de carga masiva de INSTRUCTORES.
 *
 * $estrategiaClave:
 *   'generica'      -> todos reciben la misma contraseña ($claveGenerica);
 *                       el CSV solo necesita documento,nombre_completo,correo.
 *   'personalizada' -> cada fila trae su propia contraseña en una 4ª
 *                       columna: documento,nombre_completo,correo,clave
 */
function procesarCargaMasivaInstructores(PDO $pdo, string $rutaArchivoTemporal, string $estrategiaClave, string $claveGenerica = ''): array
{
    $resumen = ['creados' => 0, 'omitidos' => 0, 'errores' => []];

    $manejador = fopen($rutaArchivoTemporal, 'r');
    if (!$manejador) {
        $resumen['errores'][] = 'No fue posible leer el archivo subido.';
        return $resumen;
    }

    $numeroFila = 0;
    while (($fila = fgetcsv($manejador, 0, ',')) !== false) {
        $numeroFila++;
        if (count($fila) === 1 && trim($fila[0]) === '') {
            continue;
        }
        if ($numeroFila === 1 && !is_numeric(trim($fila[0] ?? ''))) {
            continue; // Encabezado.
        }

        $documento = trim($fila[0] ?? '');
        $nombre    = trim($fila[1] ?? '');
        $correo    = trim($fila[2] ?? '');
        $clave     = $estrategiaClave === 'generica' ? $claveGenerica : trim($fila[3] ?? '');

        if ($documento === '' || $nombre === '' || $clave === '') {
            $resumen['omitidos']++;
            $resumen['errores'][] = "Fila $numeroFila: datos incompletos (documento, nombre o contraseña vacíos).";
            continue;
        }

        $resultado = registrarInstructor($pdo, $documento, $nombre, $correo, $clave);
        if ($resultado['ok']) {
            $resumen['creados']++;
        } else {
            $resumen['omitidos']++;
            $resumen['errores'][] = "Fila $numeroFila ($documento): {$resultado['mensaje']}";
        }
    }
    fclose($manejador);

    return $resumen;
}

/** Formatea una hora de 24h (HH:MM) a formato 12h con AM/PM, como en el documento. */
function formatoHora12(string $hora): string
{
    $ts = strtotime($hora);
    return $ts ? date('g:i A', $ts) : $hora;
}

/**
 * Traduce la prioridad de una novedad de ambiente a una etiqueta y un
 * color de badge para la interfaz.
 */
function etiquetaPrioridad(string $prioridad): array
{
    return match ($prioridad) {
        'MUY_IMPORTANTE'  => ['texto' => 'MUY IMPORTANTE', 'clase' => 'bg-danger',  'icono' => 'bi-exclamation-octagon-fill'],
        'IMPORTANTE'      => ['texto' => 'IMPORTANTE',     'clase' => 'bg-warning text-dark', 'icono' => 'bi-exclamation-triangle-fill'],
        'POCO_IMPORTANTE' => ['texto' => 'POCO IMPORTANTE','clase' => 'bg-secondary', 'icono' => 'bi-info-circle-fill'],
        default           => ['texto' => $prioridad,       'clase' => 'bg-secondary', 'icono' => 'bi-question-circle'],
    };
}

/**
 * Guarda la foto de evidencia de una novedad de ambiente en
 * /uploads/evidencias y devuelve la ruta relativa guardada, o NULL si
 * no se subió ningún archivo o no es una imagen válida.
 *
 * @param array $archivo Un elemento de $_FILES (ej: $_FILES['evidencia'])
 */
function guardarEvidenciaFoto(array $archivo): ?string
{
    if (!isset($archivo['error']) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // El instructor no adjuntó foto (campo opcional).
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensionesPermitidas, true)) {
        return null; // Tipo de archivo no permitido.
    }

    $carpetaDestino = __DIR__ . '/../uploads/evidencias';
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0775, true);
    }

    $nombreArchivo = 'evidencia_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $rutaDestino = $carpetaDestino . '/' . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        return null;
    }

    // Ruta relativa (a partir de la raíz del proyecto) que se guarda en BD.
    return 'uploads/evidencias/' . $nombreArchivo;
}
