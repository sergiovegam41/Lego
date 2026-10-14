# Decisiones de Lego 2

Registro de decisiones de diseño. Cada una es vinculante para el código de la rama `lego-2` hasta que se reemplace por otra entrada con fecha.
Principios de referencia: P1 a P6, definidos en [../plan-lego-2.md](../plan-lego-2.md).

## Base (entrevista, 2026-10-13)

| # | Decisión | Por qué |
|---|---|---|
| B1 | Audiencia: el dueño; público como portafolio. Sin retrocompatibilidad. | Permite opiniones fuertes y romper lo que haga falta. |
| B2 | FloraFresh no condiciona el diseño. | Su migración, si ocurre, es posterior. |
| B3 | Innegociable: backend en PHP y componentes como clases PHP que devuelven HTML. | Es la identidad de Lego. Todo lo demás es reemplazable. |
| B4 | Navegar entre módulos sin recargar es una funcionalidad principal. | Es la experiencia que distingue a Lego. |
| B5 | Módulos declarativos con escape explícito. | P1: un módulo nuevo con el mínimo de archivos. |
| B6 | Lego 2 es un núcleo nuevo; cada pieza actual entra solo si pasa el filtro. | La auditoría muestra que menos del 10 % entra tal cual. |
| B7 | Infraestructura obligatoria: solo PostgreSQL. | P5. Redis, MinIO, Mongo y n8n salen. |
| B8 | Código en inglés, documentación en español. | Código publicable, documentación cercana. |
| B9 | Tests: unitarios del núcleo, feature de API y auth, arquitectura. | P6. |
| B10 | Frontend con módulos ES nativos, sin Node ni build. | Elimina funciones globales y choques de nombres. |
| B11 | Un módulo de app es una carpeta con todo dentro. | P4: para entender un módulo se abre una sola carpeta. |

## Fase 0 (2026-10-13)

| # | Tema | Decisión | Por qué |
|---|---|---|---|
| D1 | Menú | Se deduce solo del código, de los atributos de módulo y pantalla. Sin tabla de menú ni MenuConfig en 2.0. | Hoy hay tres fuentes de verdad y `config:reset` borra lo configurado. P1, P2. |
| D2 | Permisos | Permisos `módulo.operación` deducidos del código; roles en base de datos con su lista de permisos. Una sola función `authorize(usuario, acción, entrada, registro)`. | Granularidad suficiente; la firma admite reglas por fila después sin romper nada. |
| D3 | Sesión | Token opaco hasheado en Postgres, cookie HttpOnly y SameSite, renovación deslizante, revocación real en logout, varias sesiones por usuario. Sin JWT ni Redis. | Hoy el JWT nunca se verifica y Redis es la fuente de verdad. B7. |
| D4 | Grupos de autenticación | Se eliminan. Solo usuarios y roles. | Un solo flujo de login. P2, P5. |
| D5 | Estilo de API | Acciones POST por nombre en `/api/actions/{nombre}`. El CRUD es un conjunto de acciones. | Encaja con permisos, auditoría, CLI y la futura IA. P2. |
| D6 | Tabla | Propia, renderizada en servidor, con orden, filtro y paginación por query string y un módulo JS pequeño. AG Grid sale. | Sin generación de JS desde PHP ni `new Function`. P4, P5. |
| D7 | Crear y editar | Un solo formulario genérico con modo, abierto como módulo con el id en la URL. Varias instancias a la vez. | Un archivo menos por módulo y sin ventanas que se pisan. P1, P2. |
| D8 | Recarga con F5 | Solo se restaura el módulo activo, indicado por la URL. Los demás abiertos se pierden. | Más simple: la URL es la única fuente de verdad. |
| D9 | Temas | Oscuro y claro sobre un solo archivo de tokens, cambio por clase en `<html>`. | Se conserva la idea correcta y se elimina la duplicación. |
| D10 | ORM y CLI | Eloquent para datos; `symfony/console` para la CLI. | Eloquent es maduro; el parser propio causó dos bugs. |
| D11 | Archivos y uploads | Fuera del núcleo 2.0. Vuelven después como módulo opcional con disco local. | Sin MinIO no hay implementación; P5. |
| D12 | Entornos | Docker con nginx y php-fpm para servir; `php -S` para desarrollo. Ambos con tests. | P6. Obliga a servir assets desde PHP sin symlinks. |
| D13 | Dónde corren los tests | Mini PC por SSH para el ciclo rápido; GitHub Actions con matriz Linux, Windows y macOS. | Esta máquina no tiene PHP. |
| D14 | Código actual | Se mueve a `legacy/` en la rama `lego-2`, fuera del autoload, y se borra al final de la fase 5. | Referencia de lectura durante la migración. |
| D15 | CSS por módulo | Permitido solo con el prefijo del módulo y sin redefinir tokens globales, verificado por test de arquitectura. | Flexibilidad sin repetir el desorden de hoy. |
| D16 | Despliegue | Compatible con Dokploy, portable y replicable. Una imagen de app autocontenida con el código dentro, sin bind mounts; toda la configuración por variables de entorno; arranque que corre migraciones de forma idempotente; healthcheck HTTP; datos solo en volúmenes con nombre; compose sin `container_name` ni puertos fijos del host, listo para el proxy de Dokploy. El mismo compose sirve en el mini PC y en Dokploy. | Desplegar en cualquier servidor con un clic o un comando, sin pasos manuales. P6. |
| D17 | Sobre de respuesta y errores | Un solo sobre JSON: `status` (`ok`, `error`, `confirmation_required`), `data`, `error {code, message, fields}`, `trace_id` y `debug` solo con `APP_DEBUG`. Errores como subclases de `HttpError` con código estable en inglés y mensaje en español sin datos internos. Cualquier otro `Throwable` es 500 `internal_error` con código de referencia, y se registra en stderr. Cada respuesta lleva `X-Trace-Id`. | Hoy conviven 8 formatos y los errores filtran SQL y trazas. Propuesta de [../auditoria-2026-10-13/validation-errors.md](../auditoria-2026-10-13/validation-errors.md). |

## Fase 3 (2026-10-15)

| # | Tema | Decisión | Por qué |
|---|---|---|---|
| D18 | Declarar un módulo | `#[Module]` sobre un `Record` basta para tener CRUD; no hay un atributo `#[Resource]` aparte. Sobre un `Component`, el módulo es una sola pantalla. | Un concepto menos (P1, P5). `operations:` limita el CRUD cuando hace falta. |
| D19 | Nombres de acciones | `<módulo>_<verbo>` en snake_case: `clients_create`, `clients_block`. El permiso es `<módulo>.<verbo>`. | Es el formato que aceptan las herramientas de OpenAI, Anthropic y WebMCP; el punto queda para los permisos. |
| D20 | Confirmación | El token de un solo uso queda atado al usuario, la acción, el registro y la entrada validada, y expira en 5 minutos. | Un token emitido para borrar un registro no puede borrar otro. Lo detectó un test. |
| D21 | Auditoría | Se registran todas las acciones que cambian datos y todos los rechazos por permiso; las de solo lectura no. | Evita ruido en `action_log` sin perder trazabilidad de cambios. |
| D22 | Altas parciales | Al crear, un campo opcional omitido conserva el valor por defecto de la base de datos; al editar, solo cambian los campos enviados. | Forzar nulos rompía columnas con default. Lo detectó un test. |

## Fase 4 (2026-10-15)

Aplicadas por defecto las recomendaciones C1 a C9 de [pendientes.md](pendientes.md), a confirmar por el dueño.

| # | Tema | Decisión | Por qué |
|---|---|---|---|
| D23 | Rutas del cliente | La URL de una pantalla es la misma en página y en fragmento: `/clients/5` y `/component/clients/5`. La clave de instancia es la ruta; la query es su estado. | Una sola tabla de rutas para servidor y cliente; enlaces compartibles; F5 funciona. |
| D24 | Envío de formularios con JavaScript | El formulario con JS usa la API de acciones (`POST /api/actions/{name}`), igual que la CLI y los futuros agentes. Sin JS usa el POST de la pantalla. | Una sola forma de ejecutar acciones desde el navegador, y errores por campo en JSON. |
| D25 | Valores por defecto de campos | `Field::default()` define el valor de una entrada vacía; al crear, un campo opcional vacío se omite. | Un formulario envía todos los campos como texto; sin esto, una columna `NOT NULL` con default fallaba. |
| D26 | Caché | Páginas, fragmentos y JSON llevan `Cache-Control: no-store`; los assets con `?v=` se cachean un año. | Las respuestas dependen de la sesión; los assets se versionan por fecha de modificación. |
| D27 | Tema | El tema sigue al sistema por defecto; el botón del shell lo fija y lo guarda en `localStorage`, solo como preferencia del navegador. | Preferencia personal sin estado en servidor. |

## Fase 5 (2026-10-16)

| # | Tema | Decisión | Por qué |
|---|---|---|---|
| D28 | Usuarios y Roles | Son módulos del núcleo en `core/Modules/`, descubiertos con la misma convención de carpeta que `modules/`. Los modelos `User` y `Role` pasan a `Lego\Modules\*`. | Un módulo propio de Lego sirve de ejemplo real y no hay una segunda forma de hacer pantallas de administración (P2). |
| D29 | Contraseñas desde la UI | No se escriben en formularios. Un usuario nuevo empieza sin contraseña utilizable; "Restablecer contraseña" genera una, la muestra una vez y cierra sus sesiones. | Evita contraseñas débiles elegidas a mano y su paso por formularios y logs. |
| D30 | Reglas de negocio del CRUD | `Record::beforeChange($operation, $clean, $by)` y parámetros `Identity` en `#[Action]` reciben al usuario que actúa. | Reglas como "no puedes borrarte" necesitan saber quién actúa, sin salir del modelo. |
| D31 | Esquemas de entrada perezosos | El esquema JSON de una acción se calcula la primera vez que se pide, no al arrancar. | Las opciones de un campo pueden venir de la base de datos; arrancar no debe consultarla. Lo detectó un test. |
| D32 | Catálogo de permisos | Se deduce de los módulos y sus acciones (`PermissionCatalog`); un rol solo puede otorgar permisos que existen. | Sin lista de permisos mantenida a mano (P1, P3). |
| D33 | Breadcrumb y botón de icono de Lego 1 | No se portan. Las pestañas del shell y los botones con tokens cubren su función. | Lo marcado "keep" en la auditoría quedó obsoleto con el shell de la fase 4 (P5). |

## Fase 6 (2026-10-17)

| # | Tema | Decisión | Por qué |
|---|---|---|---|
| D34 | Acceso de asistentes de IA | `LEGO_AGENT_ACCESS` = `off` (por defecto), `read` o `write`. En `write`, un módulo solo acepta escrituras de agentes con `#[Module(agentWrites: true)]`. Lo aplica `AgentPolicy` dentro de `ActionExecutor`. | Interruptor por app y opt-in por módulo, como Strapi, WordPress y Directus. Las escrituras son una decisión consciente, en una línea. |
| D35 | Canal de agente | La cabecera `X-Lego-Channel: agent` solo restringe (aplica `AgentPolicy`) y audita (`via = agent`). Nunca concede permisos: un agente actúa con la sesión y los permisos del usuario. | Un agente puede manejar la pantalla sin anunciarse; la seguridad no puede depender de que lo haga. |
| D36 | Anotaciones | Se añaden `untrusted_content` (derivada: `list` y `get` de registros con campos de texto) y `external` (declarada; exige `confirm`). `delete` pasa a `idempotent`. El vocabulario es de Lego y se traduce a WebMCP y MCP en el adaptador. | Marca los datos que no son instrucciones y la "trifecta letal" de forma mecánica; un renombrado en WebMCP no toca los módulos. |
| D37 | Descripciones obligatorias | `#[Action]` sin descripción es un error al arrancar. Un test de arquitectura exige en toda acción nombre de 30 caracteres como máximo, descripción de 20 a 500 caracteres, esquema con `additionalProperties: false` y cada parámetro descrito. | El asistente decide por la descripción; si falta, adivina. |
| D38 | Cabeceras | CSP estricta con nonce por respuesta para el import map, sin `unsafe-inline`. `Permissions-Policy: tools=()` salvo que la IA esté activa (`tools=(self)`), más cámara, micrófono, geolocalización, pagos y USB apagados. `SecurityHeaders` las arma en un solo lugar. | WebMCP se bloquea antes de que corra un script; sin scripts de terceros no hay inyección de tools. |
| D39 | Catálogo por pantalla | El catálogo embebido (`data-lego-actions`) lo emite `Screen`, una vez por pantalla de módulo, con la marca `agent` por acción. `lego.js` lo expone con `screenActions()` y añade `UI_ACTIONS` y `runUiAction()` para navegar, abrir y filtrar. | Un solo lugar para el catálogo; el adaptador futuro no necesita otro fetch ni tocar módulos. |
| D40 | Qué no se construye | Ni `tools.js`, ni endpoint MCP, ni chat, ni límites de tasa de agentes, ni deshacer. Se revisa WebMCP después de TPAC 2026. | No hay consumidor estable; construirlo ahora sería código que nadie usa (P5). |
