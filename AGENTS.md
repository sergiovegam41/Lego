# AGENTS.md

Puerta de entrada única para personas y agentes de IA que trabajan en Lego. Si solo lees un archivo, que sea este.

## Qué es Lego

Framework PHP 8.3 para paneles de administración. Un componente es una clase PHP con parámetros tipados que devuelve HTML. Los módulos se declaran en una carpeta y el framework deduce rutas, menú, permisos y acciones.

Estado: **Lego 2.0.0-alpha** en la rama `lego-2`. El plan está en [doc/plan-lego-2.md](doc/plan-lego-2.md) y lo pendiente del dueño en [doc/lego2/pendientes.md](doc/lego2/pendientes.md).

## Mapa

| Ruta | Contenido |
|---|---|
| `core/` | El framework, namespace `Lego\`. |
| `core/Http/` | Router, Request, Response, Kernel, errores y archivos estáticos. |
| `core/View/` | `Component`, recolección de assets y documentos. |
| `core/Ui/` | Componentes del framework con su CSS y JS al lado. Se sirven en `/_lego/`. |
| `core/Ui/Shell/`, `core/Ui/js/` | Shell, raíz de pantalla y cliente en módulos ES (`lego.js`, `ui/*.js`). Guía: [doc/frontend.md](doc/frontend.md). |
| `core/Ui/Theme/tokens.css` | Único lugar con colores, espacios y radios; tema claro y oscuro. |
| `core/Console/` | La CLI `php lego`, sobre symfony/console. |
| `core/Auth/` | Usuarios, roles, permisos, sesiones en Postgres, login y la única puerta de autorización (`Gate`). |
| `core/Database/` | Conexión Eloquent, migrador y migraciones del núcleo. |
| `core/Audit/` | Registro de acciones `action_log`, compartido por personas, CLI y agentes. |
| `core/Modules/` | Módulos del núcleo, Usuarios y Roles, hechos con las mismas piezas que un módulo de la app. |
| `core/Module/` | Descubrimiento de módulos (`#[Module]`), menú y rutas de cada módulo. |
| `core/Data/` | `Record`, los tipos de campo y el `Validator`. |
| `core/Action/` | Acciones: CRUD automático, `#[Action]`, registro, el único ejecutor (`ActionExecutor`) y la política para asistentes de IA (`AgentPolicy`). Guía: [doc/ia.md](doc/ia.md). |
| `modules/` | Módulos de la aplicación, una carpeta cada uno, namespace `Modules\`. Se sirven en `/_modules/`. Guía: [doc/modulos.md](doc/modulos.md). |
| `public/index.php` | Única entrada HTTP, para nginx y para `php -S`. |
| `tests/` | Pest: `Unit`, `Feature`, `Architecture`. |
| `docker/`, `docker-compose.yml` | Imagen y despliegue. Ver [doc/despliegue.md](doc/despliegue.md). |
| `doc/` | Documentación en español. |

## Reglas

Las decisiones vinculantes están en [doc/lego2/decisiones.md](doc/lego2/decisiones.md). Las que más afectan al día a día:

1. **Código en inglés, documentación en español.**
2. **Una sola forma de hacer cada cosa.** Antes de agregar un mecanismo, busca el que ya existe.
3. **La ruta de cada archivo coincide exactamente con su namespace**, mayúsculas incluidas. Un test lo verifica.
4. **El núcleo no lee superglobales** salvo en `Request` y `Environment`, **ni escribe salida** salvo en `Response::send`. Los handlers devuelven valores.
5. **Todo valor que llega al HTML pasa por `self::e()`** dentro de los componentes.
6. **Los assets van junto al componente** y se declaran en `$styles` y `$scripts` con rutas relativas a su carpeta. Nada de symlinks ni copias a `public/`.
7. **Configuración solo por variables de entorno.** `.env` es opcional.
8. **Todo cambio llega con tests** y `composer test` en verde.
9. **Toda ruta está cerrada por defecto.** Exige sesión salvo que se marque `->public()`, y puede exigir un permiso con `->can('modulo.operacion')`. Un test fija la lista de rutas públicas.
10. **Toda petición que modifica datos lleva token CSRF**, en el campo `_csrf` o en la cabecera `X-CSRF-Token`. El token está en `$request->csrfToken` y en `<meta name="csrf-token">`.
11. **Toda autorización pasa por `Gate::authorize()`.** No se comparan roles a mano.
12. **Un módulo nuevo no toca el núcleo.** Se crea con `php lego make:module` y se declara con `#[Module]`. Sus campos viven solo en `fields()`.
13. **Toda operación de negocio es una acción** y corre por `ActionExecutor::execute()`, venga de una pantalla, de la API o de la CLI. Lo que no es CRUD se declara con `#[Action]` en el modelo.
14. **El frontend son módulos ES nativos sin build.** Nada en `window`, sin `eval` ni `<script>` en línea, `fetch` solo en `lego.js`, y todo listener con `ctx.signal`. Los colores salen de `tokens.css`.
15. **Toda acción se describe para una IA.** Descripción en frase, cada parámetro con título o descripción, y anotaciones honestas: `destructive` o `external` llevan `confirm`. Un asistente actúa con la sesión del usuario; `X-Lego-Channel: agent` solo restringe y audita. Ver [doc/ia.md](doc/ia.md).

## Comandos

```bash
composer install
composer test            # todas las suites
php lego serve           # servidor de desarrollo en http://127.0.0.1:8000
php lego routes          # rutas registradas
php lego migrate         # aplica migraciones (también migrate:status y migrate:rollback)
php lego install --email=admin@ejemplo.com   # primer administrador; idempotente
php lego user:password ana@ejemplo.com      # contraseña nueva y cierra sus sesiones
php lego make:module Clients --label=Clientes   # módulo nuevo con modelo y migración
php lego actions                              # acciones de todos los módulos (--json para el esquema)
php lego run clients_create --as=ana@ejemplo.com --set name=Lima   # ejecutar una acción como un usuario
docker compose -f compose.test.yml run --rm tests   # suite completa con Postgres
docker compose -f docker-compose.yml -f compose.local.yml up -d --build
```

## Capas HTTP

| Prefijo | Responde | Errores |
|---|---|---|
| `/api/*` | JSON | `{"status": "error", "error": {"code", "message"}, "trace_id"}` |
| `/component/*` | Fragmento HTML para el shell | Fragmento `.lego-error` |
| resto | Página HTML completa | Página de error |

Un handler puede devolver `Response`, un arreglo (JSON), un string (HTML) o un `Component`.
