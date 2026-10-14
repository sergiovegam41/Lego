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
