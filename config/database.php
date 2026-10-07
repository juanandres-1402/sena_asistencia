<?php
/**
 * config/database.php
 * ---------------------------------------------------------------------
 * Conexión a la base de datos del sistema.
 *
 * IMPORTANTE PARA EL EQUIPO DE DESARROLLO:
 * Este prototipo usa SQLite (archivo /data/sena.db) únicamente para que
 * el sistema funcione "out of the box" sin necesidad de instalar un
 * servidor MySQL. La estructura de tablas es la MISMA que se entrega
 * en /database/schema.sql (versión MySQL para producción).
 *
 * Para pasar a producción con MySQL solo se debe:
 *   1. Ejecutar /database/schema.sql en el motor MySQL.
 *   2. Cambiar el DSN de abajo por el de MySQL (mysql:host=...;dbname=...)
 *   3. Ajustar credenciales de usuario/clave.
 * El resto del código (controladores, modelos) usa PDO estándar, por lo
 * que no requiere cambios adicionales.
 * ---------------------------------------------------------------------
 */

// Ruta física del archivo de base de datos SQLite del prototipo.
define('SENA_DB_PATH', __DIR__ . '/../data/sena.db');

/**
 * Devuelve una conexión PDO única (patrón singleton simple) hacia la
 * base de datos del sistema.
 */
function obtenerConexion(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $existeBD = file_exists(SENA_DB_PATH);

    $pdo = new PDO('sqlite:' . SENA_DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    // Si la base de datos no existía, se crea la estructura y se cargan
    // los datos de prueba (esto simula el "primer despliegue").
    if (!$existeBD) {
        inicializarEstructura($pdo);
        cargarDatosDePrueba($pdo);
    }

    return $pdo;
}

/**
 * Crea todas las tablas necesarias para el sistema.
 * Los nombres y relaciones corresponden al listado solicitado en el
 * punto 24 (Base de datos) del documento de requerimientos.
 */
function inicializarEstructura(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL UNIQUE      -- coordinador, porteria, instructor, aprendiz
        );

        CREATE TABLE usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre_completo TEXT NOT NULL,
            documento TEXT NOT NULL UNIQUE,
            correo TEXT,
            clave TEXT NOT NULL,             -- en producción: password_hash()
            id_rol INTEGER NOT NULL,
            activo INTEGER NOT NULL DEFAULT 1,
            FOREIGN KEY (id_rol) REFERENCES roles(id)
        );

        CREATE TABLE sedes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            tipo TEXT NOT NULL,              -- principal, subsede, ambiente, lugar_externo
            direccion TEXT,
            id_sede_padre INTEGER,           -- para subsedes / ambientes dependientes
            FOREIGN KEY (id_sede_padre) REFERENCES sedes(id)
        );

        CREATE TABLE programas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            nivel TEXT                       -- técnico, tecnólogo, complementaria, etc.
        );

        CREATE TABLE competencias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            id_programa INTEGER,
            FOREIGN KEY (id_programa) REFERENCES programas(id)
        );

        CREATE TABLE resultados_aprendizaje (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            id_competencia INTEGER,
            FOREIGN KEY (id_competencia) REFERENCES competencias(id)
        );

        CREATE TABLE horarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,            -- ej: Jornada mañana
            hora_inicio TEXT NOT NULL,       -- HH:MM
            hora_fin TEXT NOT NULL,          -- HH:MM
            dias TEXT NOT NULL               -- ej: 'L-V'
        );

        CREATE TABLE fichas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            numero_ficha TEXT NOT NULL UNIQUE,
            id_programa INTEGER NOT NULL,
            id_horario INTEGER NOT NULL,
            id_sede INTEGER,
            id_competencia INTEGER,
            id_resultado_aprendizaje INTEGER,
            id_instructor INTEGER,
            tolerancia_minutos INTEGER NOT NULL DEFAULT 15, -- minutos de tolerancia configurables
            estado TEXT NOT NULL DEFAULT 'ACTIVA',          -- ACTIVA, FINALIZADA
            FOREIGN KEY (id_programa) REFERENCES programas(id),
            FOREIGN KEY (id_horario) REFERENCES horarios(id),
            FOREIGN KEY (id_sede) REFERENCES sedes(id),
            FOREIGN KEY (id_competencia) REFERENCES competencias(id),
            FOREIGN KEY (id_resultado_aprendizaje) REFERENCES resultados_aprendizaje(id),
            FOREIGN KEY (id_instructor) REFERENCES usuarios(id)
        );

        CREATE TABLE aprendices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_usuario INTEGER NOT NULL,
            id_ficha INTEGER NOT NULL,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
            FOREIGN KEY (id_ficha) REFERENCES fichas(id)
        );

        CREATE TABLE equipos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo_equipo TEXT NOT NULL UNIQUE,   -- ej: PORT-000125
            tipo_equipo TEXT NOT NULL,            -- portatil, tablet, institucional, otro
            marca TEXT,
            modelo TEXT,
            numero_serie TEXT,
            codigo_cargador TEXT,
            id_aprendiz INTEGER,                  -- responsable habitual
            FOREIGN KEY (id_aprendiz) REFERENCES aprendices(id)
        );

        CREATE TABLE ingresos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_aprendiz INTEGER NOT NULL,
            id_ficha INTEGER NOT NULL,
            fecha TEXT NOT NULL,             -- YYYY-MM-DD
            hora TEXT NOT NULL,              -- HH:MM:SS
            estado_asistencia TEXT NOT NULL, -- PRESENTE, LLEGADA_TARDE
            registrado_por INTEGER,          -- usuario de portería
            FOREIGN KEY (id_aprendiz) REFERENCES aprendices(id),
            FOREIGN KEY (id_ficha) REFERENCES fichas(id),
            FOREIGN KEY (registrado_por) REFERENCES usuarios(id)
        );

        CREATE TABLE salidas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_ingreso INTEGER NOT NULL,
            fecha TEXT NOT NULL,
            hora TEXT NOT NULL,
            registrado_por INTEGER,
            FOREIGN KEY (id_ingreso) REFERENCES ingresos(id),
            FOREIGN KEY (registrado_por) REFERENCES usuarios(id)
        );

        -- La asistencia se genera 1:1 a partir de un ingreso (o de una
        -- inasistencia detectada, o de una salida de campo). Se deja
        -- como tabla independiente porque puede tener ajustes propios
        -- (autorizados y auditados) sin alterar el registro de ingreso.
        CREATE TABLE asistencias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_aprendiz INTEGER NOT NULL,
            id_ficha INTEGER NOT NULL,
            fecha TEXT NOT NULL,
            estado TEXT NOT NULL,            -- PRESENTE, LLEGADA_TARDE, SIN_INGRESO, JUSTIFICADA, SALIDA_CAMPO
            origen TEXT NOT NULL,             -- INGRESO, AUTOMATICO_SIN_INGRESO, SALIDA_CAMPO, MANUAL_AUTORIZADO
            id_ingreso INTEGER,
            observacion TEXT,
            FOREIGN KEY (id_aprendiz) REFERENCES aprendices(id),
            FOREIGN KEY (id_ficha) REFERENCES fichas(id),
            FOREIGN KEY (id_ingreso) REFERENCES ingresos(id)
        );

        CREATE TABLE equipo_ingreso (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_ingreso INTEGER NOT NULL,
            id_equipo INTEGER NOT NULL,
            FOREIGN KEY (id_ingreso) REFERENCES ingresos(id),
            FOREIGN KEY (id_equipo) REFERENCES equipos(id)
        );

        CREATE TABLE actividades_externas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_ficha INTEGER NOT NULL,
            tipo_actividad TEXT NOT NULL,     -- Salida de campo, etc.
            lugar TEXT,
            fecha TEXT NOT NULL,
            hora TEXT,
            id_instructor_responsable INTEGER,
            id_competencia INTEGER,
            id_resultado_aprendizaje INTEGER,
            FOREIGN KEY (id_ficha) REFERENCES fichas(id),
            FOREIGN KEY (id_instructor_responsable) REFERENCES usuarios(id)
        );

        CREATE TABLE notificaciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_usuario_destino INTEGER,
            mensaje TEXT NOT NULL,
            tipo TEXT,                        -- LLEGADA_TARDE, SIN_INGRESO, MODIFICACION, SALIDA_CAMPO
            leida INTEGER NOT NULL DEFAULT 0,
            fecha_hora TEXT NOT NULL,
            FOREIGN KEY (id_usuario_destino) REFERENCES usuarios(id)
        );

        CREATE TABLE auditoria (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_usuario INTEGER,
            usuario_nombre TEXT,
            accion TEXT NOT NULL,
            entidad TEXT,
            valor_anterior TEXT,
            valor_nuevo TEXT,
            fecha_hora TEXT NOT NULL,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
        );

        CREATE TABLE configuraciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            clave TEXT NOT NULL UNIQUE,
            valor TEXT NOT NULL,
            descripcion TEXT
        );

        -- =================================================================
        -- Módulo: ENTREGA DE AMBIENTE DE FORMACIÓN (inventario y novedades)
        -- =================================================================

        -- Ambiente de formación (aula/laboratorio) con su inventario base.
        -- El coordinador académico es quien crea los ambientes.
        CREATE TABLE ambientes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            id_sede INTEGER,                 -- ubicación física (sede/subsede)
            descripcion TEXT,
            FOREIGN KEY (id_sede) REFERENCES sedes(id)
        );

        -- Inventario BASE de cada ambiente, configurado por el coordinador.
        CREATE TABLE inventario_ambiente (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_ambiente INTEGER NOT NULL,
            nombre_elemento TEXT NOT NULL,   -- ej: Videobeam, Computadores, Tablero
            cantidad INTEGER NOT NULL DEFAULT 1,
            observaciones TEXT,
            FOREIGN KEY (id_ambiente) REFERENCES ambientes(id)
        );

        -- Cada vez que un instructor ENTREGA el ambiente (fin de su clase),
        -- verificando el inventario y, opcionalmente, indicando quién lo recibe.
        CREATE TABLE entregas_ambiente (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_ambiente INTEGER NOT NULL,
            id_instructor_entrega INTEGER NOT NULL,
            id_instructor_recibe INTEGER,    -- instructor que recibe el ambiente a continuación (si aplica)
            fecha_hora TEXT NOT NULL,
            FOREIGN KEY (id_ambiente) REFERENCES ambientes(id),
            FOREIGN KEY (id_instructor_entrega) REFERENCES usuarios(id),
            FOREIGN KEY (id_instructor_recibe) REFERENCES usuarios(id)
        );

        -- Novedades reportadas durante una entrega de ambiente (pueden
        -- asociarse a un elemento puntual del inventario, o ser generales).
        CREATE TABLE novedades_ambiente (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_entrega INTEGER NOT NULL,
            id_ambiente INTEGER NOT NULL,
            id_item_inventario INTEGER,      -- NULL = novedad general del ambiente
            descripcion TEXT NOT NULL,
            evidencia_foto TEXT,             -- ruta del archivo subido
            prioridad TEXT NOT NULL,         -- POCO_IMPORTANTE, IMPORTANTE, MUY_IMPORTANTE
            estado TEXT NOT NULL DEFAULT 'ACTIVA', -- ACTIVA, CORREGIDA
            reportada_por INTEGER NOT NULL,
            fecha_hora TEXT NOT NULL,
            corregida_por INTEGER,
            fecha_correccion TEXT,
            FOREIGN KEY (id_entrega) REFERENCES entregas_ambiente(id),
            FOREIGN KEY (id_ambiente) REFERENCES ambientes(id),
            FOREIGN KEY (id_item_inventario) REFERENCES inventario_ambiente(id),
            FOREIGN KEY (reportada_por) REFERENCES usuarios(id),
            FOREIGN KEY (corregida_por) REFERENCES usuarios(id)
        );
    ");
}

/**
 * Carga datos de prueba (usuarios de cada rol, una ficha completa con
 * horario y aprendices, un par de equipos, etc.) para que el prototipo
 * se pueda navegar y probar de inmediato.
 */
function cargarDatosDePrueba(PDO $pdo): void
{
    // --- Roles ---------------------------------------------------------
    $pdo->exec("INSERT INTO roles (nombre) VALUES
        ('coordinador'), ('porteria'), ('instructor'), ('aprendiz')");

    // --- Sedes -----------------------------------------------------------
    $pdo->exec("INSERT INTO sedes (nombre, tipo, direccion, id_sede_padre) VALUES
        ('Centro de Comercio y Servicios', 'principal', 'Cra 1 # 1-01', NULL),
        ('Ambiente 204 - Sala de Sistemas', 'ambiente', NULL, 1)");

    // --- Programas / competencias / resultados --------------------------
    $pdo->exec("INSERT INTO programas (nombre, nivel) VALUES
        ('Análisis y Desarrollo de Software', 'Tecnólogo')");
    $pdo->exec("INSERT INTO competencias (nombre, id_programa) VALUES
        ('Desarrollar componentes de software', 1)");
    $pdo->exec("INSERT INTO resultados_aprendizaje (nombre, id_competencia) VALUES
        ('Codificar componentes de software de acuerdo con especificaciones', 1)");

    // --- Horario ---------------------------------------------------------
    $pdo->exec("INSERT INTO horarios (nombre, hora_inicio, hora_fin, dias) VALUES
        ('Jornada mañana', '07:00', '13:00', 'Lunes a Viernes')");

    // --- Usuarios (clave de prueba para todos: 123456) -------------------
    // id_rol: 1 coordinador, 2 porteria, 3 instructor, 4 aprendiz
    $pdo->exec("INSERT INTO usuarios (nombre_completo, documento, correo, clave, id_rol) VALUES
        ('María Fernanda Ortiz', '1000000001', 'coordinador@sena.edu.co', '123456', 1),
        ('Luis Alberto Ramírez', '1000000002', 'porteria@sena.edu.co', '123456', 2),
        ('Andrés Felipe Torres', '1000000003', 'instructor@sena.edu.co', '123456', 3),
        ('Sandra Milena Vargas', '1000000004', 'sandra.vargas@sena.edu.co', '123456', 3),
        ('Juan Pérez Rodríguez', '123456789', 'juan.perez@sena.edu.co', '123456', 4),
        ('Carlos Gómez López', '987654321', 'carlos.gomez@sena.edu.co', '123456', 4),
        ('Laura Martínez Ríos', '111222333', 'laura.martinez@sena.edu.co', '123456', 4)");

    // --- Ficha -------------------------------------------------------------
    $pdo->exec("INSERT INTO fichas
        (numero_ficha, id_programa, id_horario, id_sede, id_competencia, id_resultado_aprendizaje, id_instructor, tolerancia_minutos, estado)
        VALUES ('2825647', 1, 1, 2, 1, 1, 3, 15, 'ACTIVA')");

    // --- Aprendices asociados a la ficha ------------------------------------
    // (los usuarios 1-4 son coordinador/portería/instructores; los aprendices
    // quedan en los ids 5, 6 y 7 -> Juan Pérez, Carlos Gómez, Laura Martínez)
    $pdo->exec("INSERT INTO aprendices (id_usuario, id_ficha) VALUES
        (5, 1), (6, 1), (7, 1)");

    // --- Equipo de prueba ya registrado (para probar reconocimiento automático)
    $pdo->exec("INSERT INTO equipos (codigo_equipo, tipo_equipo, marca, modelo, numero_serie, codigo_cargador, id_aprendiz) VALUES
        ('PORT-000125', 'portatil', 'Lenovo', 'ThinkPad E14', 'ABC123456', 'CARG-0125', 1)");

    // --- Configuraciones institucionales por defecto ------------------------
    $pdo->exec("INSERT INTO configuraciones (clave, valor, descripcion) VALUES
        ('notif_llegada_tarde', '1', 'Notificar cuando un aprendiz llega tarde'),
        ('notif_sin_ingreso', '1', 'Notificar cuando un aprendiz no registra ingreso'),
        ('notif_modificacion', '1', 'Notificar cuando se modifica un registro de asistencia')");

    // --- Ambiente de formación de prueba, con su inventario base -------------
    $pdo->exec("INSERT INTO ambientes (nombre, id_sede, descripcion) VALUES
        ('Ambiente 204 - Sala de Sistemas', 2, 'Sala de cómputo equipada para formación en desarrollo de software')");

    $pdo->exec("INSERT INTO inventario_ambiente (id_ambiente, nombre_elemento, cantidad, observaciones) VALUES
        (1, 'Computadores de escritorio', 25, 'Equipos para uso de los aprendices'),
        (1, 'Videobeam', 1, NULL),
        (1, 'Tablero acrílico', 1, NULL),
        (1, 'Aire acondicionado', 1, NULL),
        (1, 'Sillas universitarias', 25, NULL),
        (1, 'Control del aire acondicionado', 1, NULL)");
}
