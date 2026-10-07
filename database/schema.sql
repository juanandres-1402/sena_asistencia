-- =========================================================================
-- schema.sql
-- Script de creación de la base de datos para PRODUCCIÓN (MySQL).
-- Sistema de Control de Ingreso y Asistencia de Aprendices - SENA
--
-- Esta estructura es equivalente a la que el prototipo genera
-- automáticamente en SQLite (ver config/database.php). Ejecutar este
-- script en un servidor MySQL y luego ajustar config/database.php para
-- apuntar a él (ver comentario de cabecera en ese archivo).
-- =========================================================================

CREATE DATABASE IF NOT EXISTS sena_asistencia
    CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE sena_asistencia;

-- --------------------------------------------------------------- roles ---
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ------------------------------------------------------------ usuarios ---
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    documento VARCHAR(30) NOT NULL UNIQUE,
    correo VARCHAR(150),
    clave VARCHAR(255) NOT NULL,          -- usar password_hash() en producción
    id_rol INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (id_rol) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------- sedes ---
CREATE TABLE sedes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    tipo ENUM('principal','subsede','ambiente','lugar_externo') NOT NULL,
    direccion VARCHAR(255),
    id_sede_padre INT NULL,
    FOREIGN KEY (id_sede_padre) REFERENCES sedes(id)
) ENGINE=InnoDB;

-- ----------------------------------------------------------- programas ---
CREATE TABLE programas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    nivel VARCHAR(60)
) ENGINE=InnoDB;

-- -------------------------------------------------------- competencias ---
CREATE TABLE competencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    id_programa INT,
    FOREIGN KEY (id_programa) REFERENCES programas(id)
) ENGINE=InnoDB;

-- ------------------------------------------- resultados_aprendizaje ---
CREATE TABLE resultados_aprendizaje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    id_competencia INT,
    FOREIGN KEY (id_competencia) REFERENCES competencias(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------ horarios ---
CREATE TABLE horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    dias VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------- fichas ---
CREATE TABLE fichas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_ficha VARCHAR(20) NOT NULL UNIQUE,
    id_programa INT NOT NULL,
    id_horario INT NOT NULL,
    id_sede INT NULL,
    id_competencia INT NULL,
    id_resultado_aprendizaje INT NULL,
    id_instructor INT NULL,
    tolerancia_minutos INT NOT NULL DEFAULT 15,
    estado ENUM('ACTIVA','FINALIZADA') NOT NULL DEFAULT 'ACTIVA',
    FOREIGN KEY (id_programa) REFERENCES programas(id),
    FOREIGN KEY (id_horario) REFERENCES horarios(id),
    FOREIGN KEY (id_sede) REFERENCES sedes(id),
    FOREIGN KEY (id_competencia) REFERENCES competencias(id),
    FOREIGN KEY (id_resultado_aprendizaje) REFERENCES resultados_aprendizaje(id),
    FOREIGN KEY (id_instructor) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------- aprendices ---
CREATE TABLE aprendices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_ficha INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_ficha) REFERENCES fichas(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------- equipos ---
CREATE TABLE equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_equipo VARCHAR(30) NOT NULL UNIQUE,
    tipo_equipo ENUM('portatil','tablet','institucional','otro') NOT NULL,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    numero_serie VARCHAR(100),
    codigo_cargador VARCHAR(100),
    id_aprendiz INT NULL,
    FOREIGN KEY (id_aprendiz) REFERENCES aprendices(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------ ingresos ---
CREATE TABLE ingresos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_aprendiz INT NOT NULL,
    id_ficha INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    estado_asistencia ENUM('PRESENTE','LLEGADA_TARDE') NOT NULL,
    registrado_por INT NULL,
    FOREIGN KEY (id_aprendiz) REFERENCES aprendices(id),
    FOREIGN KEY (id_ficha) REFERENCES fichas(id),
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id),
    INDEX idx_ingresos_fecha (fecha)
) ENGINE=InnoDB;

-- ------------------------------------------------------------- salidas ---
CREATE TABLE salidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ingreso INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    registrado_por INT NULL,
    FOREIGN KEY (id_ingreso) REFERENCES ingresos(id),
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- --------------------------------------------------------- asistencias ---
CREATE TABLE asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_aprendiz INT NOT NULL,
    id_ficha INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('PRESENTE','LLEGADA_TARDE','SIN_INGRESO','JUSTIFICADA','SALIDA_CAMPO') NOT NULL,
    origen ENUM('INGRESO','AUTOMATICO_SIN_INGRESO','SALIDA_CAMPO','MANUAL_AUTORIZADO') NOT NULL,
    id_ingreso INT NULL,
    observacion VARCHAR(255),
    FOREIGN KEY (id_aprendiz) REFERENCES aprendices(id),
    FOREIGN KEY (id_ficha) REFERENCES fichas(id),
    FOREIGN KEY (id_ingreso) REFERENCES ingresos(id),
    INDEX idx_asistencias_fecha (fecha)
) ENGINE=InnoDB;

-- --------------------------------------------------------- equipo_ingreso ---
CREATE TABLE equipo_ingreso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ingreso INT NOT NULL,
    id_equipo INT NOT NULL,
    FOREIGN KEY (id_ingreso) REFERENCES ingresos(id),
    FOREIGN KEY (id_equipo) REFERENCES equipos(id)
) ENGINE=InnoDB;

-- ----------------------------------------------------- actividades_externas ---
CREATE TABLE actividades_externas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ficha INT NOT NULL,
    tipo_actividad VARCHAR(100) NOT NULL,
    lugar VARCHAR(255),
    fecha DATE NOT NULL,
    hora TIME,
    id_instructor_responsable INT NULL,
    id_competencia INT NULL,
    id_resultado_aprendizaje INT NULL,
    FOREIGN KEY (id_ficha) REFERENCES fichas(id),
    FOREIGN KEY (id_instructor_responsable) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- -------------------------------------------------------- notificaciones ---
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_destino INT NULL,
    mensaje VARCHAR(500) NOT NULL,
    tipo VARCHAR(40),
    leida TINYINT(1) NOT NULL DEFAULT 0,
    fecha_hora DATETIME NOT NULL,
    FOREIGN KEY (id_usuario_destino) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------- auditoria ---
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    usuario_nombre VARCHAR(150),
    accion VARCHAR(150) NOT NULL,
    entidad VARCHAR(60),
    valor_anterior VARCHAR(255),
    valor_nuevo VARCHAR(255),
    fecha_hora DATETIME NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    INDEX idx_auditoria_fecha (fecha_hora)
) ENGINE=InnoDB;

-- ----------------------------------------------------------- configuraciones ---
CREATE TABLE configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(60) NOT NULL UNIQUE,
    valor VARCHAR(255) NOT NULL,
    descripcion VARCHAR(255)
) ENGINE=InnoDB;

-- =========================================================================
-- Módulo: ENTREGA DE AMBIENTE DE FORMACIÓN (inventario y novedades)
-- =========================================================================

-- Ambiente de formación (aula/laboratorio), creado por el coordinador.
CREATE TABLE ambientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    id_sede INT NULL,
    descripcion VARCHAR(255),
    FOREIGN KEY (id_sede) REFERENCES sedes(id)
) ENGINE=InnoDB;

-- Inventario BASE de cada ambiente, configurado por el coordinador.
CREATE TABLE inventario_ambiente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ambiente INT NOT NULL,
    nombre_elemento VARCHAR(150) NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    observaciones VARCHAR(255),
    FOREIGN KEY (id_ambiente) REFERENCES ambientes(id)
) ENGINE=InnoDB;

-- Entrega del ambiente por parte de un instructor (fin de su clase), con
-- verificación del inventario y, opcionalmente, instructor que lo recibe.
CREATE TABLE entregas_ambiente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ambiente INT NOT NULL,
    id_instructor_entrega INT NOT NULL,
    id_instructor_recibe INT NULL,
    fecha_hora DATETIME NOT NULL,
    FOREIGN KEY (id_ambiente) REFERENCES ambientes(id),
    FOREIGN KEY (id_instructor_entrega) REFERENCES usuarios(id),
    FOREIGN KEY (id_instructor_recibe) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Novedades reportadas en una entrega (de un elemento puntual o generales).
CREATE TABLE novedades_ambiente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_entrega INT NOT NULL,
    id_ambiente INT NOT NULL,
    id_item_inventario INT NULL,
    descripcion TEXT NOT NULL,
    evidencia_foto VARCHAR(255),
    prioridad ENUM('POCO_IMPORTANTE','IMPORTANTE','MUY_IMPORTANTE') NOT NULL,
    estado ENUM('ACTIVA','CORREGIDA') NOT NULL DEFAULT 'ACTIVA',
    reportada_por INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    corregida_por INT NULL,
    fecha_correccion DATETIME NULL,
    FOREIGN KEY (id_entrega) REFERENCES entregas_ambiente(id),
    FOREIGN KEY (id_ambiente) REFERENCES ambientes(id),
    FOREIGN KEY (id_item_inventario) REFERENCES inventario_ambiente(id),
    FOREIGN KEY (reportada_por) REFERENCES usuarios(id),
    FOREIGN KEY (corregida_por) REFERENCES usuarios(id),
    INDEX idx_novedades_estado (estado)
) ENGINE=InnoDB;

-- =========================================================================
-- Datos base mínimos (roles + configuraciones por defecto).
-- Los datos de prueba completos (usuarios, ficha, aprendices, equipo)
-- están en database/datos_prueba.sql
-- =========================================================================
INSERT INTO roles (nombre) VALUES ('coordinador'), ('porteria'), ('instructor'), ('aprendiz');

INSERT INTO configuraciones (clave, valor, descripcion) VALUES
    ('notif_llegada_tarde', '1', 'Notificar cuando un aprendiz llega tarde'),
    ('notif_sin_ingreso', '1', 'Notificar cuando un aprendiz no registra ingreso'),
    ('notif_modificacion', '1', 'Notificar cuando se modifica un registro de asistencia');
