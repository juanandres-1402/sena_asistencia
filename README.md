# Sistema de Control de Ingreso y Asistencia de Aprendices — SENA

Prototipo funcional en **HTML5 + CSS3 + JavaScript + Bootstrap 5 + PHP + MySQL**
que automatiza el control de ingreso y la asistencia de los aprendices,
eliminando el llamado manual por parte del instructor.

> **Principio del sistema:** la portería registra el ingreso → el sistema
> calcula automáticamente la asistencia → el instructor solo consulta → el
> coordinador configura las reglas.

## 0. Importante: todas las rutas parten de `/sena_asistencia`

Para que el sistema funcione igual en cualquier servidor (XAMPP, WAMP,
un hosting compartido, etc.), **todas** las rutas internas (menús,
formularios, llamadas AJAX, CSS, JS) se arman a partir de una sola
constante:

```php
// includes/sesion.php
define('RUTA_BASE_PROYECTO', '/sena_asistencia');
```

Esto significa que el sistema espera ser publicado en una carpeta llamada
exactamente **`sena_asistencia`**, de modo que se acceda como:

```
http://localhost/sena_asistencia/index.php
```

- Si renombra la carpeta (por ejemplo a `asistencia_sena`), actualice ese
  mismo valor en `includes/sesion.php` (ej: `'/asistencia_sena'`).
- Si el proyecto se va a servir exactamente desde la raíz del dominio
  (sin subcarpeta, ej: `http://midominio.com/index.php`), deje el valor
  como cadena vacía: `define('RUTA_BASE_PROYECTO', '');`

## 1. Ejecutar el prototipo (no requiere instalar MySQL)

Este prototipo usa **SQLite** como motor de base de datos para que se pueda
probar de inmediato, sin instalar ni configurar un servidor MySQL. La
estructura de tablas es idéntica a la de producción (ver `database/schema.sql`).

Requisitos: tener PHP 8.1+ instalado (con la extensión `pdo_sqlite`, que viene
activada por defecto en la mayoría de instalaciones de PHP).

Como todas las rutas parten de `/sena_asistencia` (ver punto 0), el
servidor embebido de PHP debe iniciarse **desde la carpeta que CONTIENE**
el proyecto (un nivel arriba), no desde adentro de `sena_asistencia`:

```bash
# Ubíquese en la carpeta que contiene a "sena_asistencia" (no adentro de ella)
php -S localhost:8000
```

Abrir en el navegador: **http://localhost:8000/sena_asistencia/index.php**

Si prefiere usar XAMPP/WAMP: copie la carpeta `sena_asistencia` completa
dentro de `htdocs` (XAMPP) o `www` (WAMP), inicie Apache, y abra
**http://localhost/sena_asistencia/index.php**

Al iniciar por primera vez, el sistema crea automáticamente el archivo
`data/sena.db` con toda la estructura y los datos de prueba.

### Usuarios de prueba (clave para todos: `123456`)

| Rol          | Documento   |
|--------------|-------------|
| Coordinador  | 1000000001  |
| Portería     | 1000000002  |
| Instructor   | 1000000003 (Andrés Felipe Torres) |
| Instructor   | 1000000004 (Sandra Milena Vargas) |
| Aprendiz     | 123456789 (Juan Pérez) |
| Aprendiz     | 987654321 (Carlos Gómez) |
| Aprendiz     | 111222333 (Laura Martínez) |

Flujo sugerido para probar: iniciar sesión como **Portería** → módulo
*Control de ingreso* → registrar el ingreso de Juan Pérez (documento
`123456789`), indicando si trae portátil → iniciar sesión como
**Instructor** para ver cómo la asistencia ya quedó generada → iniciar
sesión como **Coordinador** para ver el dashboard y la auditoría.

Para probar la **entrega de ambiente de formación**: iniciar sesión como
**Instructor** (`1000000003`) → *Entregar ambiente* → marcar una novedad
en algún elemento del inventario (ej. "Videobeam"), con texto, foto y
prioridad **Muy importante**, seleccionando a Sandra Milena Vargas
(`1000000004`) como instructora que recibe → iniciar sesión como
**Coordinador** o como **Sandra** para ver la notificación inmediata y la
alerta en *Alertas de ambientes* → marcarla como corregida y confirmar
que queda en *Historial de ambientes*.

## 2. Pasar a producción con MySQL

1. Ejecutar `database/schema.sql` en el servidor MySQL (crea la base de
   datos `sena_asistencia` y sus tablas).
2. Ejecutar `database/datos_prueba.sql` si se desean los mismos datos de
   prueba del prototipo.
3. Editar `config/database.php` y reemplazar la conexión SQLite por una
   conexión MySQL estándar, por ejemplo:
   ```php
   $pdo = new PDO('mysql:host=localhost;dbname=sena_asistencia;charset=utf8mb4', 'usuario', 'clave');
   ```
4. El resto del sistema (controladores, vistas, API) no requiere cambios,
   ya que todo el acceso a datos usa PDO estándar.
5. Programar `controllers/cerrar_jornada.php` como tarea CRON diaria para
   marcar automáticamente "SIN INGRESO" a quien no haya registrado ingreso
   (ver comentarios dentro del archivo).

## 3. Entrega de ambiente de formación (verificación de inventario)

El coordinador crea los **ambientes de formación** y su **inventario
base** desde *Coordinador → Ambientes de formación*. Al finalizar su
clase, el instructor entra a *Entregar ambiente*, verifica cada elemento
del inventario y, si hay algún problema, reporta una **novedad** con:

- Texto explicativo (obligatorio)
- Evidencia fotográfica (opcional, se guarda en `/uploads/evidencias`)
- Prioridad: **Poco importante**, **Importante** o **Muy importante**

También puede seleccionar qué instructor recibe el ambiente a
continuación. Si alguna novedad queda como **Muy importante**, el
sistema notifica de inmediato al coordinador y a ese instructor (desde
*Notificaciones* / el aviso en su dashboard), para que no tenga que
volver a reportarla al llegar: la pantalla de entrega le muestra las
novedades activas de ese ambiente antes de empezar.

Cualquier novedad activa se gestiona desde *Alertas de ambientes*
(coordinador e instructores), donde se puede marcar como **corregida**:
en ese momento la alerta desaparece de esa vista, pero el registro
permanece disponible para siempre en *Historial de ambientes*, junto
con el historial de entregas de cada ambiente.

Archivos relevantes: `views/coordinador/ambientes.php`,
`views/instructor/entregar_ambiente.php`, `views/ambientes/alertas.php`,
`views/ambientes/historial.php`, tablas `ambientes`,
`inventario_ambiente`, `entregas_ambiente` y `novedades_ambiente`.

## 4. Gestión de fichas y usuarios (aprendices, instructores, contraseñas)

- **Aprendices → Aprendices** (menú visible para **coordinador e
  instructor**): matricula aprendices en una ficha, de forma individual
  o por **carga masiva (CSV)**. El instructor solo puede operar sobre
  sus propias fichas (validado también en el servidor). Un aprendiz
  nuevo queda con contraseña inicial igual a su número de documento.
- **Coordinador → Usuarios y contraseñas** (solo coordinador): **única**
  pantalla del sistema donde se puede cambiar la contraseña de
  cualquier usuario (coordinador, portería, instructor o aprendiz), y
  activar/desactivar cuentas. Un usuario inactivo no puede iniciar
  sesión. El coordinador no puede desactivarse a sí mismo.
- **Coordinador → Cargar instructores** (solo coordinador): crea
  instructores individualmente o por carga masiva (CSV), asignando una
  contraseña inicial de dos formas: **misma contraseña genérica para
  todos** (solo cambia el documento como usuario) o **personalizada**
  (columna `clave` propia por fila en el CSV).

Las plantillas CSV de ejemplo se descargan desde
`controllers/plantilla_csv.php?tipo=aprendices|instructores_generica|instructores_personalizada`
(enlaces ya incluidos en cada pantalla).

## 5. Estructura del proyecto

```
/config            Conexión a base de datos
/includes          Sesión, funciones de negocio, layout compartido
/controllers       Scripts de proceso (cierre de jornada, etc.)
/api               Endpoints AJAX (JSON) usados por portería
/views             Pantallas, organizadas por rol/módulo
  /coordinador      Dashboard, fichas y horarios, salidas de campo,
                     notificaciones
  /porteria         Control de ingreso, control de salida
  /instructor       Dashboard (mi ficha), novedades
  /aprendiz         Dashboard, asistencia, equipos
  /equipos          Listado y registro de equipos
  /asistencia       Consulta de asistencia (coordinador/instructor)
  /reportes         Reportes filtrables y exportación CSV
  /auditoria        Historial de auditoría
/assets/css         Paleta institucional SENA (style.css)
/assets/js          main.js (general) y porteria.js (control de ingreso)
/database           schema.sql (MySQL) y datos_prueba.sql
/data               Base de datos SQLite del prototipo (se genera sola)
```

## 6. Reglas configurables (no están "quemadas" en el código)

- **Hora máxima de ingreso**: se calcula siempre como
  `hora_inicio_horario + tolerancia_minutos`, ambos configurables por
  ficha desde *Coordinador → Fichas y horarios*. Ver
  `includes/funciones.php::calcularEstadoAsistencia()`.
- **Notificaciones**: cada tipo de evento (llegada tarde, sin ingreso,
  modificación) se puede activar/desactivar desde
  *Coordinador → Notificaciones*.
- **Salidas de campo**: se manejan como una excepción configurada por
  ficha y fecha, sin exigir ingreso físico.

## 7. Preparado para lectores físicos

El campo de búsqueda de la pantalla de portería (`views/porteria/ingreso.php`)
y el endpoint `api/buscar_aprendiz.php` reciben un simple texto (el
documento). Un lector de código de barras, QR, RFID o NFC normalmente actúa
como un "teclado" que escribe ese texto automáticamente en el campo, por lo
que la integración futura no requiere cambios de arquitectura: solo
apuntar el lector físico a ese mismo campo (o adaptar el endpoint para
recibir el identificador que entregue el lector).

## 8. Normativa institucional

El diseño de los estados de asistencia (PRESENTE, LLEGADA_TARDE,
SIN_INGRESO, JUSTIFICADA, SALIDA_CAMPO) y su conversión en inasistencia
formal debe revisarse y ajustarse contra la versión vigente del Acuerdo
009 del SENA y la normativa institucional relacionada, tal como señala el
documento de requerimientos. Este prototipo deja la lógica centralizada y
configurable (ver `includes/funciones.php` y la tabla `configuraciones`)
precisamente para facilitar ese ajuste sin tener que modificar cada
pantalla del sistema.

## 11. Integración con el proyecto `controlasistencia`

Este paquete unifica dos proyectos: el sistema funcional `sena_asistencia`
(base: PHP + SQLite/MySQL, roles, sesión, auditoría) y las pantallas del
proyecto `controlasistencia` (vistas de interfaz con datos de ejemplo).
La carpeta raíz es única: `sena_asistencia`.

| Origen (`controlasistencia`)  | Destino en `sena_asistencia`                  | Acceso por rol          |
|-------------------------------|-----------------------------------------------|-------------------------|
| `views/admi.php`              | `views/instructor/panel_gestion.php`          | instructor, coordinador |
| `views/lista.php`             | `views/instructor/control_asistencia.php`     | instructor, coordinador |
| `views/aprendiz.php`          | `views/aprendiz/portal.php`                   | aprendiz                |
| `views/gerente.php`           | `views/coordinador/panel_gerente.php`         | coordinador             |
| `views/ingreso.php` (login)   | `views/auth/login_nexora.php`                 | público                 |
| `publico/css/style2.css`      | `assets/css/login_nexora.css`                 | —                       |
| `publico/img/fondo.png`       | `assets/img/fondo.png`                        | —                       |

Cambios de integración:
- Las vistas importadas quedan protegidas con `exigirRol()` (misma sesión del sistema). El rol "gerente" se asignó a **coordinador**.
- Todas las rutas usan `rutaBase()` (`RUTA_BASE_PROYECTO`); se corrigieron los enlaces rotos de `lista.php` hacia las pantallas reales.
- El login de `controlasistencia` apuntaba a un controlador inexistente (`UsuarioController2.php`); ahora envía a `index.php`, que acepta **documento o correo** + clave.
- Las vistas importadas se agregaron al menú lateral con la etiqueta "(prototipo)": usan datos de ejemplo del lado del cliente, aún no conectados a la base de datos.
- `publico/css/style.css` se descartó por ser una copia exacta del CSS en línea de `admi.php`. No se incluyó la carpeta `.git` del proyecto original.
- Dependencias: ambos proyectos usan CDN (Bootstrap 5, Bootstrap Icons, Font Awesome, Tailwind, Chart.js, SweetAlert2, SheetJS, Lucide); no requieren instalación, solo conexión a internet. Backend: PHP 8.1+ con `pdo_sqlite`.
