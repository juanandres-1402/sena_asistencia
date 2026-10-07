-- =========================================================================
-- datos_prueba.sql
-- Datos de prueba para MySQL (producción), equivalentes a los que el
-- prototipo carga automáticamente en SQLite. Ejecutar DESPUÉS de schema.sql.
-- Clave de prueba para todos los usuarios: 123456
-- =========================================================================
USE sena_asistencia;

INSERT INTO sedes (nombre, tipo, direccion, id_sede_padre) VALUES
    ('Centro de Comercio y Servicios', 'principal', 'Cra 1 # 1-01', NULL),
    ('Ambiente 204 - Sala de Sistemas', 'ambiente', NULL, 1);

INSERT INTO programas (nombre, nivel) VALUES
    ('Análisis y Desarrollo de Software', 'Tecnólogo');

INSERT INTO competencias (nombre, id_programa) VALUES
    ('Desarrollar componentes de software', 1);

INSERT INTO resultados_aprendizaje (nombre, id_competencia) VALUES
    ('Codificar componentes de software de acuerdo con especificaciones', 1);

INSERT INTO horarios (nombre, hora_inicio, hora_fin, dias) VALUES
    ('Jornada mañana', '07:00', '13:00', 'Lunes a Viernes');

INSERT INTO usuarios (nombre_completo, documento, correo, clave, id_rol) VALUES
    ('María Fernanda Ortiz', '1000000001', 'coordinador@sena.edu.co', '123456', 1),
    ('Luis Alberto Ramírez', '1000000002', 'porteria@sena.edu.co', '123456', 2),
    ('Andrés Felipe Torres', '1000000003', 'instructor@sena.edu.co', '123456', 3),
    ('Sandra Milena Vargas', '1000000004', 'sandra.vargas@sena.edu.co', '123456', 3),
    ('Juan Pérez Rodríguez', '123456789', 'juan.perez@sena.edu.co', '123456', 4),
    ('Carlos Gómez López', '987654321', 'carlos.gomez@sena.edu.co', '123456', 4),
    ('Laura Martínez Ríos', '111222333', 'laura.martinez@sena.edu.co', '123456', 4);

INSERT INTO fichas (numero_ficha, id_programa, id_horario, id_sede, id_competencia, id_resultado_aprendizaje, id_instructor, tolerancia_minutos, estado)
    VALUES ('2825647', 1, 1, 2, 1, 1, 3, 15, 'ACTIVA');

-- Los usuarios 1-4 son coordinador/portería/instructores; los aprendices
-- quedan en los ids 5, 6 y 7 (Juan Pérez, Carlos Gómez, Laura Martínez).
INSERT INTO aprendices (id_usuario, id_ficha) VALUES (5, 1), (6, 1), (7, 1);

INSERT INTO equipos (codigo_equipo, tipo_equipo, marca, modelo, numero_serie, codigo_cargador, id_aprendiz) VALUES
    ('PORT-000125', 'portatil', 'Lenovo', 'ThinkPad E14', 'ABC123456', 'CARG-0125', 1);

-- Ambiente de formación de prueba, con su inventario base.
INSERT INTO ambientes (nombre, id_sede, descripcion) VALUES
    ('Ambiente 204 - Sala de Sistemas', 2, 'Sala de cómputo equipada para formación en desarrollo de software');

INSERT INTO inventario_ambiente (id_ambiente, nombre_elemento, cantidad, observaciones) VALUES
    (1, 'Computadores de escritorio', 25, 'Equipos para uso de los aprendices'),
    (1, 'Videobeam', 1, NULL),
    (1, 'Tablero acrílico', 1, NULL),
    (1, 'Aire acondicionado', 1, NULL),
    (1, 'Sillas universitarias', 25, NULL),
    (1, 'Control del aire acondicionado', 1, NULL);
