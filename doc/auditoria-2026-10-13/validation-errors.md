# Entrada, validación, errores, respuesta y log

Dimensión: el recorrido de una petición desde que entra hasta que se responde y se registra. Archivos revisados: `Core/providers/Request.php`, `Core/Response.php`, `Core/Models/{ResponseDTO,StatusCodes,Messages}.php`, `Core/Controllers/*.php`, los 15 controladores de `App/Controllers`, `Core/Attributes/*.php`, `App/Models/*.php`, `App/Controllers/Storage/Rules/StorageRules.php`, `assets/js/core/services/ValidationEngine.js`, los 9 `*-create.js` / `*-edit.js` de `components/App`, `assets/js/services/AlertService.js`, `components/Core/Login/login.js`, las migraciones y `vendor/mikecao/flight/flight/Engine.php` para el comportamiento por defecto. No se repiten los hallazgos de [controllers-auth.md](controllers-auth.md), [core-routing.md](core-routing.md) ni [frontend-js.md](frontend-js.md); cuando un hallazgo de aquí amplía uno de esos, se indica.

## Resumen

1. **La validación no tiene dueño.** Cada regla vive en entre dos y cuatro sitios a la vez (JS del formulario, controlador, rakit, cast del modelo, columna de la BD) y casi nunca coinciden. Las reglas de formato y rango (email válido, contraseña mínima, precio > 0, stock ≥ 0, categoría obligatoria) solo existen en el cliente. Un `curl` las salta todas.
2. **Los errores de entrada acaban siendo 500.** El servidor no comprueba tipos. Un `"stock": "abc"` llega a Postgres, vuelve como `QueryException` y se responde con 500 y con el SQL y los valores en `msj`. Un array donde se espera un string lanza `TypeError`. Los 61 `catch (\Exception)` de los controladores no lo capturan, así que Flight pinta una página HTML con la traza completa y **no lo registra en ningún sitio**.
3. **Hay ocho formas de respuesta** (`ResponseDTO`, `{status, errors}` de rakit, `{success, message, error}` del CRUD automático, `Flight::json`, HTML de Flight, `text/plain` de las subidas, cuerpo vacío con 200 y un JSON exportado con `echo`) y los códigos HTTP no siguen ninguna convención. Crear devuelve 200 o 201 según el módulo, la validación usa 400, los fallos parciales devuelven 200 con `success: true` y los 401 de auth salen como 200.
4. **Ningún error del servidor llega a un campo.** Los formularios convierten cualquier respuesta no-ok en `throw new Error(result.msj)`, que acaba en un toast. Los duplicados se detectan buscando el texto `'Ya existe'` dentro del mensaje. La infraestructura que sí sabía mostrar errores por campo (`ApiClient.getValidationErrors`, `FormBuilder.setErrors`, `ValidationEngine`) nunca se usó.
5. **El log no sirve para operar.** Hay 35 `error_log()` sin nivel, formato ni id de petición, mezclados con ruido de depuración y con cuerpos de petición. `storage/logs` se crea en la imagen y nadie escribe en él. Lo peor: precisamente los errores no capturados son los que no se registran.

Veredicto: no se conserva nada de este recorrido salvo dos ideas, el sobre `success/msj/data` como intención y la constante `requiresAuth = true` de `ApiComponent`. Para Lego 2 se proponen un sobre único con `status` (`ok`, `error`, `confirmation_required`), siete excepciones con su código HTTP, un validador propio de unas 200 líneas que se deriva de `fields()`, un formulario genérico que pinta los `error.fields` del servidor y un logger JSON a `stderr`. **rakit/validation sale.**

## Estado actual

### 1. Mapa de reglas de validación

Leyenda: **C** = cliente (JS del formulario), **S** = controlador PHP, **R** = rakit, **M** = modelo (fillable, casts o defaults), **BD** = migración. "—" significa que no existe.

| Regla | C | S | R | M | BD | Copias | Observación |
|---|---|---|---|---|---|---|---|
| Usuario: email obligatorio | users-config-create.js:122 | UsersConfigController.php:128 | — | — | migración 000002:23 `NOT NULL` | 3 | |
| Usuario: email con formato válido | users-config-create.js:124 (regex) | — | — | — | — | 1 | **Solo cliente.** En update tampoco se valida (UsersConfigController.php:206-228). |
| "Es un email" en todo el repo | users-config-create.js:124, ValidationEngine.js:152 | AuthServicesCore.php:193 y :375 (`filter_var`) | AuthGroupsController.php:32 y :79 | — | — | 5 | Cuatro implementaciones distintas de la misma regla. |
| Usuario: email único | — | UsersConfigController.php:138 y :207 (SELECT previo) | — | — | **sin UNIQUE** (migración 000002:23) | 2 | Hay carrera entre el SELECT y el INSERT, y el login hace `where email ->first()`. |
| Contraseña: longitud mínima | users-config-create.js:128 (≥ 8) | — (UsersConfigController.php:128 solo comprueba que exista) | login `min:6` (AuthGroupsController.php:33), register `min:8` (:80) | — | varchar(255) | 3 | Tres valores distintos: 6, 8 o ninguno. |
| Usuario: `status` dentro de una lista | `<select>` | — (default `'active'` en :126, acepta cualquier string) | — | — | varchar(255) | 0 | No hay regla `in` en ningún sitio. |
| Usuario: `role_id` y `auth_group_id` existen | `<select>` | solo presencia (:128) | — | — | sin FK (migración 000002:26-27) | 0 | No hay regla `exists` en ningún sitio. |
| Grupo: ID normalizado | auth-groups-config-create.js:36 | AuthGroupsController.php:126/:272 | — | — | PK varchar | 2 | |
| Rol: IDs normalizados | roles-config-create.js:37 | RolesConfigController.php:131-132 (create) | — | — | `unique(auth_group_id, role_id)` (migración 2025_12_01_000002:27) | 2 | Update **no** normaliza (:232). |
| Rol: unicidad | roles-config-create.js:291-297 (descarga la lista entera) | RolesConfigController.php:145 y :216 | — | — | índice único :27 | 3 | |
| ExampleCrud: nombre obligatorio | example-create.js:35, example-edit.js:39 | ExampleCrudController.php:153 (solo create) | — | fillable | `NOT NULL` | 4 | En update `"" ?? $record->name` guarda el nombre vacío (:231). |
| ExampleCrud: precio > 0 | example-create.js:40, example-edit.js:44 | — (default 0 en :165) | — | cast `decimal:2` | `decimal(10,2) default 0` | 2 | El cliente exige > 0; el servidor y la BD aceptan 0 y negativos. |
| ExampleCrud: stock ≥ 0 | example-create.js:45 | — | — | cast `integer`, default 0 | `integer default 0` | 1 | **Solo cliente.** |
| ExampleCrud: categoría obligatoria | example-create.js:50 | — | — | — | `nullable` (migración 2025_11_04_000001:25) | 1 | Cliente y BD se contradicen. |
| Texto ≤ 255 (name, email…) | — | — | — | — | varchar(255) | 1 | **Solo BD**: el exceso vuelve como 500 con SQL. |
| Tools: nombre obligatorio | tools-create.js:99, tools-edit.js:145 | ToolsController.php:160 (create) | — | — | `NOT NULL` | 3 | |
| Login: username email, password ≥ 6 | — (login.js no valida) | AuthServicesCore.php:192-193 | AuthGroupsController.php:31-34 | — | — | 2 | |
| Storage: path ≤ 500, limit ≤ 1000 | — | — | StorageRules.php:29, :50, :71-72, :94 | — | — | 1 | Módulo sin consumidores. |
| Menú: label obligatorio | — | MenuConfigController.php:236 | — | — | — | 1 | |

Resultado: **ninguna regla vive en un solo sitio con autoridad.** La más repetida, "nombre obligatorio" en ExampleCrud, aparece cuatro veces. Las de formato y rango solo viven en el cliente. rakit se usa en dos sitios: cuatro endpoints de auth y el módulo Storage, que se va a borrar. Los atributos `#[ApiGetResource]` y `#[ApiCrudResource]` no declaran reglas (Core/Attributes/ApiGetResource.php:67-76). Los modelos solo aportan `fillable` y `casts`, y Eloquent no aplica estos últimos al escribir.

### 2. Formatos de respuesta y códigos HTTP reales

| # | Forma | Emisor | Dónde | Códigos |
|---|---|---|---|---|
| 1 | `{success, msj, data, status_code}` | `Response::json(…, (array)new ResponseDTO)` | 203 instancias en App y Core | 200, 201, 400, 404, 409, 500 |
| 2 | `{status: false, errors: {campo: "msg"}}` | `Request::validateMake` → `Response::json(400)` | Core/providers/Request.php:50-52 | 400 |
| 3 | `{success, message, error, data, pagination, action}` | AbstractCrud y AbstractGet | AbstractCrudController.php:102-142, :463-468 | 200, 201, 400, 404, 422, 500 |
| 4 | `{success, data, message, count, query}` | `Flight::json` | MenuSearchController.php:101, MenuSystemItemsController.php:88 | 200, 500 |
| 5 | `{success, message, html, componentId, params, example, received}` | ComponentsController | ComponentsController.php:54-93 | 200, 400, 404, 500 |
| 6 | HTML `<h1>500</h1><h3>mensaje</h3><pre>traza</pre>` | `Flight::_error` por defecto (no hay `Flight::map('error')`) | vendor/mikecao/flight/flight/Engine.php:659-679 | 500 |
| 7 | `text/plain` con el id | `echo $id; exit` | FilesController.php:110-112, ExampleCrudController.php:427-428 | 200 |
| 8 | Cuerpo vacío | `getMethod` devuelve un array que nadie emite | CoreController.php:18/:26 | 200 |
| (+) | JSON crudo con `echo` + `exit` | MenuConfig export | MenuConfigController.php:422-423 | 200 |

Recuento de `Response::json` por código: 500 → 60, 200 → 52, 400 → 58, 404 → 26, 409 → 5, 201 → 3, 422 → 2 (solo en el CRUD automático, que no tiene modelos). A eso se suman los 6 `$resp->statusCode ?? 200` de auth, que siempre dan 200 (ya reportado). Nadie usa 422 para validación ni 401/403 de forma efectiva.

### 3. Cómo llegan los errores a la UI

- Los 9 formularios repiten el mismo patrón: `const result = await response.json()` (15 sitios, antes de mirar `response.ok` o el content-type), `if (!response.ok || !result.success) throw new Error(result.msj)` y `AlertService.error('Error', error.message)`. Ejemplos: example-create.js:83-98, users-config-create.js:232-244.
- Los errores por campo solo vienen de `validateForm()` del cliente y se pintan con `showValidationErrors()`. Hay 9 copias (auth-groups-config-create.js:169, example-create.js:109, users-config-create.js:255…) y todas buscan en `document`, no en el formulario (example-create.js:111). Con dos formularios abiertos (D7), uno borra los errores del otro.
- Para detectar un duplicado se busca un texto en el mensaje: `result.msj.includes('Ya existe')` (auth-groups-config-create.js:144, roles-config-create.js:327 y :411).
- Si el servidor responde con el HTML de Flight, `response.json()` lanza `SyntaxError` y el usuario ve "Unexpected token '<'".
- Login: si el login falla, solo hay un `console.error('Login failed:', data.message)` (login.js:61). Además lee `message` cuando el sobre trae `msj`, y antes de comprobar ya ha llamado a `loading(false, {success: true})` (login.js:39-42). El usuario no ve nada.

### 4. Qué se loguea y dónde

- **Mecanismo:** solo `error_log()`, 35 llamadas, sin nivel, sin formato y sin id de petición. Destino: el `error_log` por defecto de PHP. En la imagen `php:8.3-fpm` va al stderr del contenedor; con `php -S`, al terminal. `storage/logs` se crea en Dockerfile:41 y nadie escribe en él.
- **Qué se registra:** trazas en AbstractCrud, AbstractGet y Restful (`getTraceAsString`, sin consumidores); el cuerpo completo de la petición en FilesController.php:151; ids de usuario y dispositivo en AuthServicesCore.php:158 y :355; el payload de cada item en MenuConfigController.php:167; y ruido de depuración ("✓ Registered…") condicionado a `getenv('APP_ENV')`, que según core-routing nunca ve el `.env`.
- **Qué no se registra:** cualquier `\Error` o `TypeError` no capturado, porque Flight trae `flight.log_errors = false` por defecto (Engine.php:205) y el repo no lo cambia. Tampoco el fallo al cargar un controlador fuera de desarrollo: ApiControllerRouter.php:121-126 lo traga y la ruta desaparece sin dejar rastro. Y nada que relacione una respuesta con su línea de log.

## Hallazgos confirmados

| Severidad | Archivo:línea | Título | Recomendación | Principio |
|---|---|---|---|---|
| high | App/Controllers/UsersConfig/Controllers/UsersConfigController.php:28 | Los 61 `catch (\Exception)` no capturan `\Error` ni `TypeError`. Esos errores acaban en el HTML de Flight con la traza y sin log. Reproducible con `GET /api/users-config/foo` (la ruta legacy Routes/Api.php:238 acepta cualquier acción y `$this->foo()` lanza `Error`), con `{"id":["x"],"name":"a"}` en `auth-groups/create` (TypeError en `normalizeId(string)`, AuthGroupsController.php:126/:272) o con `"features":[["x"]]` en `tools/create` (`trim(array)`, ToolsController.php:179). Amplía core-routing:24 con dos cosas: el `catch` de cada controlador no protege, y Engine.php:205 (`log_errors=false`) hace que nada quede registrado. | Ningún controlador captura. Un único `ErrorHandler` en el kernel captura `\Throwable`, registra con `trace_id` y responde según la capa. `set_error_handler` convierte avisos en `ErrorException`. Test de arquitectura: ningún `catch` que devuelva una respuesta fuera del handler. | P6 |
| high | App/Controllers/UsersConfig/Controllers/UsersConfigController.php:128 | Validación de formato y rango solo en cliente. `users-config/create` acepta un email sin formato, una contraseña de 1 carácter y un `status` arbitrario (users-config-create.js:124-128 valida lo que el servidor no). `example-crud/create` acepta precio y stock negativos (example-create.js:40-46 frente a ExampleCrudController.php:162-171). En update, el nombre puede quedar vacío (:231). | Las reglas viven en `fields()` y las aplica el servidor antes de cualquier escritura. El cliente solo recibe atributos HTML derivados de las mismas reglas. Test feature por regla: entrada inválida da 422 con el campo. | P3 |
| medium | App/Controllers/ExampleCrud/Controllers/ExampleCrudController.php:193 | Los errores de tipo del usuario se convierten en 500 con SQL y valores. `"stock":"abc"` llega a Postgres (no hay comprobación de tipo; el cast `integer` del modelo solo actúa al leer), vuelve como `QueryException` y se responde `'Error al crear registro: ' . $e->getMessage()`. Laravel incluye en ese mensaje el SQL con los bindings sustituidos. Lo mismo ocurre con un texto de más de 255 caracteres. Amplía la fuga de getMessage de controllers-auth: aquí el disparador es una entrada inválida normal. | Validar `type` y `length` desde la definición del campo para que la BD nunca vea un valor mal tipado. Traducir las `QueryException` en un solo sitio (23505 → 422 en el campo, 23503 → 409, el resto → 500 genérico más log como "hueco de validación"). | P6 |
| medium | database/migrations/2024_01_01_000002_create_auth_users_table.php:23 | `auth_users.email` no tiene índice UNIQUE. La unicidad depende de un SELECT previo (UsersConfigController.php:138 y :207) con carrera. `auth_group_id` y `role_id` (:26-27) no tienen FK y el servidor no comprueba que existan. | La regla `unique()` del campo genera el índice en la migración y el validador consulta antes de escribir. La violación 23505 de la carrera se traduce al mismo error de campo. Las relaciones declaran `exists` o una FK. | P3 |
| medium | App/Controllers/RolesConfig/Controllers/RolesConfigController.php:232 | Create normaliza `auth_group_id` y `role_id` (:131-132); update hace `fill($data)` sin normalizar. Se puede guardar `admin` junto a `ADMIN` y el índice único, sensible a mayúsculas, no lo impide. La comprobación de duplicado (:215) compara valores sin normalizar. | La normalización es un transform del campo (`Code::make('role_id')->upper()`) que se aplica igual en create y update, antes de validar. Un test unitario por transform. | P2 |
| medium | App/Controllers/MenuConfig/Controllers/MenuConfigController.php:191 | Fallos parciales devueltos como 200 con `success: true`. En update, un item inexistente (:104) o un padre inexistente (:157-160) mueven el item a la raíz en silencio. El mensaje sale como `Padre ''` porque `parent_id` se pone a null antes de construirlo. Import (:536-548) acumula `$e->getMessage()` dentro de `errors` y responde 200. | Una acción es atómica: todo dentro de una transacción o 422/409 con detalle. Nunca `success: true` con errores. Menú fuera de BD en 2.0 (D1). | P6 |
| medium | Core/Controllers/CoreController.php:18 | Una acción no permitida en `/api/auth/@group/@accion` hace que `getMethod` devuelva `['status'=>'error','msg'=>…]` (:26), que nadie emite. El constructor de AuthGroupsController.php:25 lo descarta y la respuesta es 200 con cuerpo vacío. | El router responde 404 JSON a una acción desconocida antes de instanciar nada. Test feature: acción inexistente da 404 con el sobre. | P2 |
| medium | Core/providers/Request.php:18 | `Request` valida en el constructor con reglas vacías, después cada acción vuelve a validar (AuthGroupsController.php:31-34) y emite con `die` un quinto formato (`{status:false, errors}` con 400, :50-53). login.js:61 lee `data.message` (no existe; el sobre trae `msj`) y no muestra nada. | `Request` solo parsea, una vez y de forma inmutable. La validación es un paso del pipeline de acción que lanza `ValidationFailed` (422). El login es una acción más con campos y su formulario usa el genérico. | P2 |
| medium | components/App/AuthGroupsConfig/childs/AuthGroupsConfigCreate/auth-groups-config-create.js:144 | Los errores del servidor no llegan a los campos. Todo `!ok` se convierte en `throw new Error(result.msj)` y un toast. El 409 se detecta con `msj.includes('Ya existe')` (también roles-config-create.js:327 y :411). `await response.json()` se ejecuta antes de comprobar el content-type (15 sitios), así que un HTML de error se muestra como "Unexpected token '<'". | Un solo `api()` que mira el content-type y devuelve el sobre tipado. El formulario genérico pinta `error.fields` en sus campos (acotado a su raíz) y `error.code` decide el resto. Nunca se compara texto de mensajes. | P2 |
| low | assets/js/services/AlertService.js:138 | El mensaje del servidor se renderiza como HTML. La firma es `error(message, title)`, pero 21 de las 26 llamadas pasan `('Error', error.message)`, así que el mensaje del servidor acaba en `title`, que SweetAlert2 11 interpreta como HTML. Ese mensaje incluye `getMessage()` con los valores enviados (fila 3). Hoy es self-XSS. Amplía frontend-js:47, que solo vio la inversión de argumentos. | Notificaciones siempre como texto (`titleText`/`text` o `textContent`). El servidor nunca devuelve mensajes con datos de entrada; los mensajes de validación salen de un catálogo con la etiqueta del campo. | P6 |
| low | App/Controllers/Auth/Controllers/AuthGroupsController.php:33 | La misma regla aparece con valores distintos. Contraseña: `min:6` en login, `min:8` en register (:80) y en el cliente (users-config-create.js:128), ninguno en users-config. Precio: > 0 en el cliente y ≥ 0 en servidor y BD. Categoría: obligatoria en el cliente y nullable en la BD. Email: cinco implementaciones. normalizeId: cuatro copias (2 PHP, 2 JS). | Una definición por campo y cero reglas en JS. Test de arquitectura: ningún `validateForm` ni regex de validación en módulos. | P2 |
| low | App/Controllers/UsersConfig/Controllers/UsersConfigController.php:161 | Códigos HTTP incoherentes. Crear responde 200 en Users, Roles y AuthGroups (RolesConfigController.php:166, AuthGroupsController.php:157) y 201 en ExampleCrud y Tools (ExampleCrudController.php:187, ToolsController.php:205). La validación usa 400; 422 solo aparece en el CRUD sin uso y significa "error de BD". Los fallos parciales usan 200. | Tabla de mapeo única (sección de diseño) aplicada por el sobre; test de contrato por tipo de respuesta. | P2 |
| low | App/Controllers/Tools/Controllers/ToolsController.php:170 | Creaciones con hijos sin transacción. `Tool::create`, después `ToolFeature::create` en bucle y después `associateFileToEntity`; un fallo a mitad deja el registro a medias y responde 500. Lo mismo en ExampleCrudController.php:162-185 y en el update (:244-255, borra asociaciones antes de recrearlas). | El pipeline de acción envuelve validar, ejecutar y registrar en `action_log` dentro de una transacción. | P6 |
| low | App/Controllers/Files/Controllers/FilesController.php:151 | Log sin estructura ni redacción. Se registra el cuerpo completo de la petición, `getTraceAsString` como texto multilínea y ruido "✓ Registered" en cada petición. No hay nivel ni `trace_id`. `storage/logs` (Dockerfile:41) no se usa. Los fallos al cargar controladores en producción se tragan en silencio (Core/Routing/ApiControllerRouter.php:121-126). | Logger único en JSON a `php://stderr`, con niveles, `trace_id` y redacción de claves sensibles. Prohibir `error_log` fuera del logger con un test de arquitectura. Los fallos de arranque abortan, no se tragan. | P6 |

## Diseño propuesto para Lego 2

### 1. Sobre de respuesta único

Todas las respuestas JSON (las de `/api/actions/*` y cualquier otra de `/api`) tienen la misma forma y la emite un solo método del kernel. Las claves van en snake_case, igual que las columnas.

```
status        "ok" | "error" | "confirmation_required"   (siempre)
data          resultado de la acción                       (solo ok)
meta          paginación u otros datos                     (opcional, solo ok)
ui            efecto sobre la pantalla {route, filters, focus} (opcional, solo ok)
error         {code, message, fields?}                     (solo error)
confirmation  {token, expires_at, message, preview}        (solo confirmation_required)
trace_id      id de la petición                            (siempre en error y confirmación; en ok, solo en la cabecera)
debug         {exception, message, file, line, trace}      (solo con APP_DEBUG=true)
```

`error.code` es estable, en inglés y snake_case, y es lo único que el cliente compara. `error.message` va en español, listo para mostrar y **sin datos de entrada ni de excepciones**. `error.fields` aparece solo en validación: un mapa campo → lista de `{rule, message, params?}`.

#### Ejemplos

Éxito (`POST /api/actions/clients.create` → **201**):

```json
{
  "status": "ok",
  "data": { "id": 42, "name": "Florería Lima", "email": "ventas@floreria.pe", "credit": "1500.00" },
  "ui": { "focus": { "module": "clients", "id": 42 } }
}
```

Error de validación por campo (**422**):

```json
{
  "status": "error",
  "error": {
    "code": "validation_failed",
    "message": "Revisa los 2 campos marcados.",
    "fields": {
      "email": [ { "rule": "email", "message": "Introduce un correo válido." } ],
      "credit": [ { "rule": "min", "params": { "min": 0 }, "message": "Crédito no puede ser menor que 0." } ]
    }
  },
  "trace_id": "9f2c1a7e4b0d3c11"
}
```

Error de negocio (**409**):

```json
{
  "status": "error",
  "error": { "code": "role_in_use", "message": "No se puede borrar el rol porque lo usan 3 usuarios." },
  "trace_id": "4c0b9d2e11a7f803"
}
```

No autorizado. Sin sesión → **401**; con sesión pero sin permiso → **403**:

```json
{ "status": "error", "error": { "code": "unauthenticated", "message": "Tu sesión ha caducado. Vuelve a iniciar sesión." }, "trace_id": "b81e0c4d9a2f7e15" }
```

```json
{ "status": "error", "error": { "code": "forbidden", "message": "No tienes permiso para clients.delete." }, "trace_id": "a03f6b1e8c5d2a94" }
```

Confirmación requerida (**428**; la acción no se ejecutó):

```json
{
  "status": "confirmation_required",
  "confirmation": {
    "token": "c2b1e0f4a9d84e7b",
    "expires_at": "2026-10-13T15:04:05Z",
    "message": "Se bloqueará al cliente Florería Lima.",
    "preview": { "client_id": 42, "changes": { "status": ["active", "blocked"] } }
  },
  "trace_id": "e7d03a5c1f8b6e20"
}
```

El cliente reenvía la misma petición con `"confirmation_token": "c2b1e0f4a9d84e7b"` y recibe 200 con `status: ok`. Un token caducado o ya usado da 409 `confirmation_invalid`.

Error interno (**500**, en producción):

```json
{ "status": "error", "error": { "code": "internal_error", "message": "Ha ocurrido un error inesperado. Código de referencia: 1d9e5f0a2c7b4e61." }, "trace_id": "1d9e5f0a2c7b4e61" }
```

Por qué 428 y no 200 para la confirmación: un cliente ingenuo que solo mira `response.ok` falla de forma segura y no da por hecha una acción que no se ejecutó. El adaptador MCP traduce 428 a `input_required` (investigación WebMCP, punto 4).

### 2. Jerarquía mínima de excepciones y mapeo HTTP

```
Lego\Errors\HttpError (abstract, extends RuntimeException)
  status(): int · code(): string · publicMessage(): string · details(): array
  ├── BadRequest            400  bad_request          JSON mal formado, content-type distinto, cuerpo > límite (413 payload_too_large)
  ├── Unauthenticated       401  unauthenticated
  ├── Forbidden             403  forbidden | csrf_mismatch
  ├── NotFound              404  not_found            ruta, acción o registro
  ├── MethodNotAllowed      405  method_not_allowed   (con cabecera Allow)
  ├── BusinessRuleViolation 409  código propio        lanzada desde #[Action] u hooks: throw new BusinessRuleViolation('role_in_use', '…')
  └── ValidationFailed      422  validation_failed    lleva fields; la lanza solo el validador
Cualquier otro \Throwable → 500 internal_error, mensaje genérico, log de nivel error con la excepción completa.
```

- **La confirmación no es una excepción**: es un resultado del pipeline de acción (`ActionResult::confirm(...)`), porque nada ha fallado.
- **Traducción de BD en un solo sitio** (`DatabaseErrorTranslator`). SQLSTATE `23505` (unique) se convierte en `ValidationFailed` sobre el campo, resolviendo el nombre del índice con la convención de Eloquent `{tabla}_{columna}_unique`. `23503` (FK): en un delete da `BusinessRuleViolation('in_use')`; en un insert o update, `ValidationFailed` en el campo de la relación. `22P02`, `22001` y `22003` (tipo, longitud o rango) dan **500**, más un log `validation_gap`: si ocurren es un bug del validador y debe verse.
- **Render por capa** (decisión ya tomada). En `/api/*`, el sobre JSON. En `/component/*`, un fragmento `<div class="lego-error" data-status="401" data-code="unauthenticated" data-trace-id="…">mensaje escapado</div>` con el mismo código HTTP, y el cliente redirige al login con 401. En `/*`, una página de error 404, 403 o 500 renderizada por un componente del núcleo. Con `APP_DEBUG=true` se añade `debug` (JSON) o un bloque `<pre>` escapado (HTML). Sin `APP_DEBUG`, nunca.
- **Red de seguridad**: `set_error_handler` convierte avisos y notices en `ErrorException` (un "Undefined array key" deja de pasar en silencio). `register_shutdown_function` emite un 500 mínimo y su log si hay un fatal. En la imagen: `display_errors=0` y `log_errors=1` hacia stderr, solo para lo que ocurra antes de que arranque el handler.

### 3. Validación derivada de la definición de campos

La validación es un paso del pipeline de cada acción, no del controlador ni del `Request`.

```
Request (JSON parseado una vez)
  → whitelist: solo campos declarados y escribibles en el modo (create|update); un campo desconocido es error de campo "unknown_field"
  → normalización: trim; "" → null si nullable; "12" → 12 en Integer; "true"/"1" → true en Boolean; transforms del campo (upper, slug…)
  → validación de todos los campos (se recogen todos los errores, no solo el primero)
  → unique y exists (consultan la BD; solo si las reglas locales del campo pasaron)
  → ValidatedInput tipado → authorize(usuario, acción, entrada, registro) → ejecutar en transacción → action_log
```

En update se valida el estado combinado (registro + entrada), así `required` tiene sentido aunque el formulario solo envíe lo que cambió.

**Reglas mínimas.** Siete, más una opcional:

| Regla | Semántica | Mensaje (catálogo es) | HTML derivado | JSON Schema derivado |
|---|---|---|---|---|
| `required` | No es null, ni `""` tras trim, ni `[]`. `0` y `false` son valores válidos. | "{label} es obligatorio." | `required` | `required: [...]` |
| `type` | string, integer, decimal(scale), boolean, date, datetime; se comprueba tras normalizar | "{label} debe ser un número entero." … | `type=number step=1`, `type=date`… | `type`, `format` |
| `min` / `max` | Valor numérico (o fecha), inclusivo | "{label} no puede ser menor que {min}." | `min`, `max` | `minimum`, `maximum` |
| `length` | Longitud con `mb_strlen` (mín. y máx.); el máximo por defecto sale de la columna (255) | "Máximo {max} caracteres." | `minlength`, `maxlength` | `minLength`, `maxLength` |
| `email` | `filter_var(FILTER_VALIDATE_EMAIL)` tras trim y minúsculas | "Introduce un correo válido." | `type=email` | `format: email` |
| `unique` | No existe otra fila con ese valor (excluye el id actual en update); la respaldan un índice UNIQUE y la traducción de 23505 | "Ya existe un registro con este {label}." | — | — (solo servidor) |
| `in` | El valor está en las opciones del campo (enum PHP o lista) | "Elige una opción válida." | `<select>` con esas opciones | `enum` |
| `exists` (opcional) | La FK apunta a una fila existente (BelongsTo) | "{label} no existe." | `<select>` cargado del recurso | — |

**Tipos de campo y reglas implícitas** (el desarrollador no escribe reglas, elige el tipo):

| Campo | Reglas implícitas | Modificadores |
|---|---|---|
| `Text` | type string, length max 255 | `required()`, `length(min, max)`, `unique()`, `transform()` |
| `LongText` | type string | `length()` |
| `Email` | type string, email, length max 255, normaliza a minúsculas | `unique()` |
| `Password` | type string, length min 8, `sensitive` (no se devuelve ni se registra; se hashea al guardar) | `length()` |
| `Integer` | type integer | `min()`, `max()` |
| `Decimal` / `Money` | type decimal(2); `Money` añade min 0 | `min()`, `max()` |
| `Boolean` | type boolean | — |
| `Date` / `DateTime` | type date | `min()`, `max()` |
| `Select` / `Enum` | in(opciones) | — |
| `BelongsTo` | type de la FK, exists | — |

De `fields()` salen, sin otra fuente: las reglas, los atributos HTML del formulario, las columnas y filtros de la tabla y el `inputSchema` del catálogo de acciones. Un test de arquitectura recorre todos los tipos de campo y exige que implementen `rules()`, `htmlAttributes()` y `jsonSchema()`.

**El cliente no valida con JS.** El formulario genérico emite los atributos HTML nativos derivados (`required`, `maxlength`, `min`, `type=email`…), así el navegador frena lo obvio sin código propio y la autoridad sigue siendo el 422 del servidor. `ValidationEngine.js` y las 9 funciones `validateForm` desaparecen.

### 4. Cómo presenta los errores el formulario genérico

Estructura emitida por el componente `Form` (PHP):

```html
<form class="lego-form" data-action="clients.create" data-mode="create">
  <div class="lego-form__alert" role="alert" hidden></div>
  <div class="lego-field" data-field="email">
    <label for="f7a-email">Correo</label>
    <input id="f7a-email" name="email" type="email" maxlength="255" required aria-describedby="f7a-email-error">
    <p class="lego-field__error" id="f7a-email-error" hidden></p>
  </div>
  …
</form>
```

El prefijo `f7a` es único por instancia, así que varias instancias del mismo formulario (D7) no comparten ids.

Comportamiento (un módulo ES de unas 80 líneas, `form.js`, con `init(root)`; todo se busca **dentro de `root`**, nunca en `document`):

1. Al enviar: limpia errores previos, deshabilita el botón y hace `api('/api/actions/' + action, {json, csrf})`. `api()` comprueba el content-type y, si la respuesta no es JSON, la convierte en `{status:'error', error:{code:'internal_error'}}`.
2. `ok`: aplica `ui` (cerrar, enfocar el registro, volver a la lista) y muestra un toast de éxito en texto plano.
3. `validation_failed`: por cada campo de `error.fields` pone `aria-invalid="true"`, escribe el primer mensaje con `textContent` en su `<p>` y lo hace visible. Enfoca el primer campo inválido. Los campos que no están en el formulario (`unknown_field` o `_form`) y el `error.message` general van a la alerta superior.
4. `confirmation_required`: abre el diálogo de confirmación con `message` y `preview`. Si se acepta, reenvía con `confirmation_token`.
5. `unauthenticated`: redirige al login conservando la URL. `forbidden`, `not_found` y los códigos de negocio (409) van a la alerta superior con `error.message`. `internal_error` va a la alerta con el `trace_id` visible para soporte.
6. Nunca se compara el texto de un mensaje. Solo `status` y `error.code`.

### 5. Logging mínimo

- **Destino:** un único `Logger` del núcleo que escribe una línea JSON por evento en `php://stderr`. En la imagen `php:8.3-fpm`, el pool oficial (`docker.conf`: `catch_workers_output = yes`, `decorate_workers_output = no`) lleva el stderr de los workers a `docker logs` sin prefijos; hay que verificarlo en la imagen nueva con un test de humo. Con `php -S` sale por el terminal. Sin archivos dentro del contenedor (D16): se borra `storage/logs`. Dokploy y el mini PC leen los logs del contenedor.
- **Formato** (una línea; el ejemplo está partido para leerlo):

```json
{"ts":"2026-10-13T15:04:05.123Z","level":"error","msg":"unhandled_exception","trace_id":"1d9e5f0a2c7b4e61",
 "method":"POST","path":"/api/actions/clients.create","status":500,"duration_ms":38,"user_id":7,"action":"clients.create",
 "exception":{"class":"PDOException","message":"…","file":"modules/Clients/Client.php","line":88,"trace":["#0 …","#1 …"]}}
```

- **Eventos:** una línea `request` por petición al terminar (nivel `info` si el estado es menor que 500 y `error` si es 500 o más; incluye `status`, `duration_ms`, `user_id`, `action`). `unhandled_exception` (error) con la excepción. `validation_gap` (error) para 22P02, 22001 o 22003. `validation_failed` solo en `debug` y con los nombres de campo, nunca los valores. `auth.login_failed` (warning) sin la contraseña.
- **Niveles:** `debug`, `info`, `warning` y `error`, filtrados por `LOG_LEVEL` (por defecto `info`; `debug` con `APP_DEBUG`).
- **Redacción obligatoria:** las claves que contengan `password`, `token`, `secret`, `authorization`, `cookie` o `csrf`, y los campos marcados `sensitive()`, se escriben como `"[redacted]"`. Nunca se registran cuerpos de petición en `info`.
- **trace_id:** se toma `X-Request-Id` del proxy si cumple `^[A-Za-z0-9-]{8,64}$`; si no, se genera `bin2hex(random_bytes(8))`. Se devuelve en la cabecera `X-Request-Id` y en el sobre de error. Es lo que une la pantalla del usuario con la línea de log.
- **Lo que no es log:** la auditoría de acciones (`action_log` en Postgres, fase 2) es otra cosa con otro ciclo de vida. El log es operativo y puede rotarse o perderse.

## Tests

### Unitarios (núcleo)

- **Sobre:** `Response::ok`, `created`, `error` y `confirmation` serializan exactamente la forma documentada (test de contrato con snapshot). `debug` no aparece si `APP_DEBUG` es falso.
- **ErrorHandler:** cada subclase de `HttpError` produce su estado y su `code`. Un `RuntimeException`, un `TypeError` o un `Error` producen 500 `internal_error` sin el mensaje original. Render por capa: JSON en `/api`, fragmento escapado en `/component` (un mensaje con `<script>` sale escapado) y página en `/`. Con `APP_DEBUG=true` se añade `debug`.
- **DatabaseErrorTranslator:** 23505 con un índice conocido da `ValidationFailed` en el campo correcto; 23503 en un delete da 409 `in_use`; 22P02 da 500 más el log `validation_gap`.
- **Validador, una batería por regla:**
  - `required`: null, `""`, `"  "` y `[]` fallan; `0` y `false` pasan.
  - `type`: `"12"` pasa como entero; `"12.5"`, `"abc"` y `["x"]` fallan en un entero; un array en un campo string falla con 422, no con TypeError.
  - `min` y `max`: valores en el borde.
  - `length`: multibyte (`"ñandú"` mide 5).
  - `email`: casos válidos e inválidos; normaliza a minúsculas.
  - `in`: con un enum.
  - Se recogen todos los errores a la vez.
  - Un campo desconocido da `unknown_field`.
  - `""` se convierte en null cuando el campo es nullable.
  - En update se valida sobre el estado combinado.
- **Derivación desde campos:** `Text` implica una longitud máxima de 255; `Email` implica `email`; `Money` implica `min 0`; `Password` es `sensitive`. Cada tipo genera los atributos HTML y el JSON Schema esperados (snapshot).
- **Transforms:** `upper` y `slug` (sustituto de normalizeId) se aplican igual en create y update.
- **Catálogo de mensajes:** toda regla tiene un mensaje en español y la etiqueta se interpola.
- **Logger:** escribe una línea JSON válida por evento; respeta el filtro de nivel; redacta `password`, `token` y `cookie` también en contexto anidado; serializa excepciones; incluye siempre `trace_id`.
- **Request:** JSON mal formado da `BadRequest`; un content-type distinto de JSON en `/api/actions` da 400; un cuerpo por encima del límite da 413; el cuerpo se lee una sola vez.

### Feature (kernel en proceso contra Postgres)

- `clients.create` válido devuelve 201 con el sobre `ok`; uno inválido devuelve 422 con `fields` por cada regla, y el valor enviado no aparece en ningún mensaje.
- `unique`: un duplicado da 422 en el campo; un duplicado por carrera (saltando la comprobación previa) también da 422 y no 500.
- Acción desconocida: 404 en JSON (no HTML). `GET` sobre una acción: 405 con cabecera `Allow`. Cuerpo no JSON: 400.
- Sin sesión: 401 en JSON en `/api` y fragmento 401 en `/component`. Sin permiso: 403.
- `BusinessRuleViolation` en un hook da 409 con el código propio.
- Acción destructiva: sin token, 428 sin efectos en BD; con token, 200; el token reutilizado o caducado, 409 `confirmation_invalid`.
- Una excepción en la acción da 500 genérico con `trace_id`. El log capturado contiene esa excepción con el mismo `trace_id` y el cuerpo no contiene SQL, rutas ni nombres de clase.
- Una acción con hijos que falla a mitad no deja filas (transacción).
- `/no-existe` responde con la página 404 en HTML; `/component/no-existe`, con el fragmento 404.
- Login inválido da 401 con `invalid_credentials`. El formulario de login es el genérico.

### Arquitectura

- Ningún `echo`, `print`, `die`, `exit`, `header(` ni `http_response_code(` fuera del emisor del kernel.
- Ningún `->getMessage()` fuera de `ErrorHandler` y `Logger`.
- Ningún `$_GET`, `$_POST`, `$_REQUEST`, `$_FILES` ni `php://input` fuera de `Request`.
- Ningún `error_log(` fuera de `Logger`.
- Ningún `catch` en `modules/` que construya una `Response`.
- Todo tipo de campo implementa `rules()`, `htmlAttributes()` y `jsonSchema()`.
- En los JS de `modules/` no hay `validateForm` ni expresiones regulares de validación.
- La dependencia `rakit/validation` no existe en `composer.json`.

El comportamiento de `form.js` no tiene runner de JS (B10, sin Node). Se cubre con tests feature que comprueban el HTML del formulario (atributos derivados y contenedores de error por campo) y con un smoke manual documentado. Un E2E con navegador queda para después.

## Inventario

| Path | Veredicto | Razón | Principio |
|---|---|---|---|
| Core/providers/Request.php | rewrite | Objeto inmutable que parsea una vez (JSON, query, cabeceras, cookies) sin validar ni emitir. Hoy valida en el constructor, emite con `die` y depende de rakit. | P2 |
| Core/Response.php | rewrite | `Response` como objeto (estado, cabeceras, cuerpo) con fábricas del sobre (`ok`, `created`, `error`, `confirmation`); un único emisor en el kernel. Hoy hace `die`. | P6 |
| Core/Models/ResponseDTO.php | drop | Lo sustituye el sobre en `Response`. `msj`, el array cast y `status_code` en el cuerpo (la mitad del bug de auth) desaparecen. La idea `success/message/data` pasa al diseño. | P2 |
| Core/Models/StatusCodes.php | drop | 50 constantes y 4 helpers sin uso para los ~10 códigos del mapeo; el mapeo vive en las excepciones. | P5 |
| Core/Models/Messages.php | drop | Cuatro strings; lo sustituye el catálogo de mensajes del validador. | P5 |
| Core/Controllers/AbstractCrudController.php | rewrite | Pasa a ser el conjunto de acciones de `#[Resource]` con pipeline de validación; hoy hace `create($data)` sin validar y devuelve SQL con 422. | P1 |
| Core/Controllers/AbstractGetController.php | rewrite | Se fusiona con lo anterior (acción `list`); los filtros se validan contra los campos declarados. | P2 |
| Core/Controllers/RestfulController.php | drop | Sin uso. Solo se rescata la idea `success()`/`error()`/`handleException()`, que ahora hacen el sobre y el ErrorHandler. | P5 |
| Core/Controllers/CoreController.php, CoreViewController.php, DefaultCrudController.php, DefaultGetController.php | drop | Despacho dinámico sin respuesta para acciones inválidas e imports muertos de rakit (CoreController.php:9, CoreViewController.php:4). | P5 |
| Core/Attributes/ApiRoutes.php | drop | Lo sustituye `#[Action]` (D5). El parámetro `middleware` nunca se leyó. | P2 |
| Core/Attributes/ApiCrudResource.php, ApiGetResource.php | rewrite | Pasan a `#[Resource]` más `fields()`: sortable, filterable y searchable salen de modificadores del campo, no de listas paralelas. | P1 |
| Core/Attributes/ApiComponent.php | rewrite | Pasa a `#[Screen]`; se conserva `requiresAuth = true` por defecto. | P6 |
| App/Controllers/Auth/Controllers/AuthGroupsController.php | rewrite | Tres acciones (login, logout, me) con campos declarados y errores 401/422 del sobre. | P2 |
| App/Controllers/Auth/DTOs/AuthRequestDTO.php | drop | Envoltorio de `Request` más dos strings; los grupos desaparecen (D4). | P5 |
| App/Controllers/Storage/Rules/StorageRules.php | drop | Único otro consumidor de rakit; Storage sale con MinIO (B7). | P5 |
| App/Controllers/UsersConfig/Controllers/UsersConfigController.php | rewrite | `User::fields()` (`Email` unique, `Password` sensitive, `Select` status, `BelongsTo` role) y un hook de hash; el controlador desaparece. | P1 |
| App/Controllers/RolesConfig/Controllers/RolesConfigController.php | rewrite | Recurso `Role` con `Code` en mayúsculas y la lista de permisos (D2); `normalizeId` pasa a ser un transform. | P1 |
| App/Controllers/AuthGroups/Controllers/AuthGroupsController.php | drop | Los grupos se eliminan (D4). | P5 |
| App/Controllers/ExampleCrud/Controllers/ExampleCrudController.php, Tools/Controllers/ToolsController.php | drop | Los sustituye el módulo de ejemplo generado con `make:module`. | P5 |
| App/Controllers/Files/Controllers/FilesController.php | drop | Uploads fuera del núcleo 2.0 (D11). Cuando vuelvan, la validación de tipo y tamaño irá en el campo `File` del servidor. | P5 |
| App/Controllers/MenuConfig/Controllers/MenuConfigController.php, Menu/Controllers/MenuSystemItemsController.php | drop | Menú deducido del código, sin MenuConfig en 2.0 (D1). | P5 |
| App/Controllers/Menu/Controllers/MenuStructureController.php, MenuItemHierarchyController.php, MenuSearchController.php | rewrite | Funciones del núcleo sobre el menú derivado; responden con el sobre (MenuSearch usa hoy `Flight::json`). | P2 |
| App/Controllers/ComponentsController.php | rewrite | Si `/api/components/render` sobrevive, usa el sobre y valida los parámetros contra la firma del componente; nunca `InvalidArgumentException` como 404 genérico. | P6 |
| App/Models/User.php, Role.php | rewrite | Añaden `fields()`, la única fuente de reglas, columnas e inputs. | P1 |
| App/Models/UserSession.php | rewrite | Sesión opaca en Postgres (D3). | P6 |
| App/Models/AuthGroup.php, MenuItem.php, ExampleCrud.php, ExampleCrudImage.php, Tool.php, ToolFeature.php, EntityFile.php, EntityFileAssociation.php | drop | D4, D1, demos y D11. | P5 |
| database/migrations/2024_01_01_000002_create_auth_users_table.php | rewrite | `email` UNIQUE, FK de `role_id`, `status` como enum o CHECK. | P3 |
| assets/js/core/services/ValidationEngine.js | drop | Sin consumidores; el cliente deja de validar con JS. | P5 |
| components/App/\*/childs/\*/\*-create.js, \*-edit.js (9 archivos) | drop | Los sustituyen el formulario genérico (D7) y `form.js`. | P1 |
| assets/js/services/AlertService.js | rewrite | `notify(type, text)` solo con texto; fuera los `title` HTML con datos del servidor. | P6 |
| components/Core/Login/login.js | rewrite | El login usa el formulario genérico y muestra el 401. | P2 |
| **rakit/validation** (composer.json:7) | **sale** | Versión 1.4.0 del 2020-08-27 sin publicaciones desde entonces (composer.lock), reglas en strings con pipes que duplicarían `fields()`, mensajes en inglés, y solo dos consumidores (auth y Storage, que se van). Las siete reglas caben en un validador propio de unas 200 líneas, testeable regla a regla y que genera también los atributos HTML y el JSON Schema, cosa que rakit no hace. | P5 |

## Preguntas abiertas

1. **¿Campos desconocidos: rechazar o ignorar?** Se recomienda rechazar con `unknown_field` (útil para agentes y contra la asignación masiva). Contra: un cliente viejo que envía de más falla.
2. **¿Entra `exists` como octava regla?** La necesitan las relaciones (`BelongsTo`) para dar un 422 claro en vez de depender de la FK, que daría 23503. La alternativa es mapear 23503 al campo y no tener la regla.
3. **¿428 o 200 para `confirmation_required`?** Se propone 428 para que un cliente que no conoce la confirmación falle de forma segura. Hay que confirmar que nginx y el proxy de Dokploy no reescriben 428.
4. **¿Quién manda en la longitud máxima: el campo o la migración?** Si `fields()` genera la migración, hay una sola fuente. Si las migraciones se escriben a mano, hace falta un test que compare `length max` con la columna. Afecta a "2 archivos por CRUD: modelo y migración".
5. **¿Mensajes solo en español o con claves traducibles?** Se propone español en el servidor con `rule` y `params` en cada error, para que una traducción futura no cambie el contrato.
6. **¿En update se valida el estado combinado o solo lo enviado?** Se propone el combinado; con un formulario genérico que siempre envía todo, la diferencia solo se nota en la API y la CLI.
7. **¿Línea de log `request` en PHP además del access log de nginx?** Se propone que sí, porque añade `user_id`, `action` y `trace_id`, que nginx no conoce. Duplica volumen.
8. **¿El `trace_id` se guarda también en `action_log`?** Se recomienda que sí, para unir la auditoría con el log operativo.
