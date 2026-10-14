# IA en Lego

Lego no trae un asistente de IA. Deja el terreno listo para conectar uno sin tocar los módulos: un asistente hace exactamente lo mismo que la persona, con sus permisos, por el mismo camino, y queda auditado.

El porqué de cada pieza está en la investigación [investigacion-webmcp-2026-10-13.md](investigacion-webmcp-2026-10-13.md). Este documento dice qué hay y cómo se usa.

## La idea: la acción

Toda operación de negocio es una **acción** con nombre, descripción, esquema de entrada, permiso y anotaciones. Las de CRUD salen del `Record`; las demás se declaran con `#[Action]` (ver [modulos.md](modulos.md)). Una pantalla, la API, la CLI y un asistente ejecutan la misma acción con el mismo `ActionExecutor`:

1. política de agentes (solo si la llamada viene marcada como de agente),
2. carga del registro,
3. `Gate::authorize()` con el usuario de la sesión,
4. validación,
5. confirmación en dos fases si la acción la pide,
6. ejecución en transacción,
7. registro en `action_log` con el canal (`ui`, `api`, `cli`, `agent`).

Un asistente nunca tiene un usuario propio ni permisos propios. Actúa con la sesión de la persona.

## Qué ve un asistente

| Fuente | Contenido |
|---|---|
| `GET /api/actions` | Catálogo de las acciones que el usuario puede ejecutar. `?module=clients` filtra por módulo. Cada entrada trae `agent: true/false`. |
| `<script type="application/json" data-lego-actions="clients">` | El mismo catálogo, solo con las acciones del módulo de la pantalla, embebido en cada pantalla. En el navegador se lee con `screenActions()` de `lego.js`. |
| `UI_ACTIONS` en `lego.js` | Tres acciones de pantalla: `ui_navigate`, `ui_open_record` y `ui_set_filter`. Se ejecutan con `runUiAction(name, input)` y solo abren módulos que están en el menú del usuario. |
| `POST /api/actions/{name}` | Ejecuta una acción. Respuesta `ok`, `error` o `confirmation_required`, y un bloque `ui` opcional para que la pantalla muestre el resultado. |

Una entrada del catálogo:

```json
{
  "name": "clients_block",
  "module": "clients",
  "label": "Bloquear",
  "description": "Impide que el cliente compre.",
  "permission": "clients.block",
  "scope": "record",
  "input_schema": {"type": "object", "properties": {"id": {"type": "integer", "minimum": 1}, "reason": {"type": "string", "title": "Motivo"}}, "required": ["id", "reason"], "additionalProperties": false},
  "annotations": {"read_only": false, "destructive": true, "idempotent": false, "confirm": true, "untrusted_content": false, "external": false},
  "agent": false
}
```

## Anotaciones

El vocabulario es de Lego y se traduce en el adaptador. Así, si WebMCP o MCP renombran un hint, los módulos no cambian.

| Lego | Significa | WebMCP | MCP |
|---|---|---|---|
| `read_only` | No modifica nada. `list`, `get` y `#[Action(readOnly: true)]`. | `readOnlyHint` | `readOnlyHint` |
| `destructive` | Borra o altera de forma difícil de deshacer. `delete` y las que lo declaren. | `consequentialHint` | `destructiveHint` |
| `idempotent` | Repetirla con la misma entrada no tiene efecto adicional. `get`, `list`, `update`, `delete`. | — | `idempotentHint` |
| `confirm` | El servidor pide confirmación en dos fases (HTTP 428 y token de un solo uso). Obligatoria si es `destructive` o `external`. | — | se traduce a `input_required` |
| `untrusted_content` | La salida contiene texto libre escrito por personas. El asistente debe tratarlo como datos, nunca como instrucciones. `list` y `get` cuando el registro tiene campos de texto. | `untrustedContentHint` | — |
| `external` | Comunica con el exterior (correo, webhook). Lego exige `confirm: true`. | `consequentialHint` | `openWorldHint` |

Las anotaciones son pistas para el asistente. Las garantías las pone el servidor: permiso, validación, confirmación y auditoría se aplican igual aunque el asistente las ignore.

## Activar la IA

Por defecto los asistentes no pueden ejecutar nada. Se activa con una variable de entorno:

| `LEGO_AGENT_ACCESS` | Qué pueden hacer los agentes |
|---|---|
| `off` (por defecto) | Nada. `Permissions-Policy: tools=()` apaga WebMCP en el navegador antes de que corra ningún script. |
| `read` | Solo acciones `read_only`. La cabecera pasa a `tools=(self)`. |
| `write` | Además, las escrituras de los módulos que lo permiten con `#[Module(agentWrites: true)]`. |

```php
#[Module(label: 'Notas', agentWrites: true)]
final class Note extends Record
```

El adaptador marca sus llamadas con la cabecera `X-Lego-Channel: agent`. Esa marca solo sirve para **negar** (aplicar la política de arriba) y para **auditar** (`via = agent` en `action_log`). Nunca concede nada: un agente que maneja la pantalla como una persona no la envía y queda limitado por los permisos de esa persona, igual que ella.

## Cabeceras de seguridad

Cada respuesta lleva:

- `Content-Security-Policy` estricta: solo scripts y estilos propios, sin `unsafe-inline`; el import map lleva un nonce nuevo en cada respuesta.
- `Permissions-Policy` con `tools=()` o `tools=(self)` según `LEGO_AGENT_ACCESS`, y cámara, micrófono, geolocalización, pagos y USB apagados.
- `X-Frame-Options: DENY`, `frame-ancestors 'none'`, `nosniff`, `Referrer-Policy: same-origin`, y HSTS y `Cross-Origin-Opener-Policy: same-origin` cuando la app corre en HTTPS.

Un admin sin scripts de terceros evita que alguien inyecte herramientas falsas a mitad de sesión.

## Cómo se conectaría cada tipo de asistente

### WebMCP (agente del navegador)

Un archivo `tools.js` de unas decenas de líneas, que todavía no existe:

1. Si existe `document.modelContext`, al montar cada pantalla lee `screenActions()` y registra cada acción con `agent: true` usando `registerTool({ name, description, inputSchema, annotations, execute })`, con un `AbortController` por pantalla para desregistrarlas al cerrarla.
2. `execute` llama a `api(name, input, { agent: true })` de `lego.js`, que añade la cabecera `X-Lego-Channel: agent`.
3. Ante `confirmation_required`, muestra el diálogo de confirmación de Lego a la persona y reenvía el token solo si acepta.
4. Aplica el bloque `ui` del resultado para que la persona vea lo que hizo el agente.
5. Registra también `UI_ACTIONS` con `runUiAction` como `execute`.
6. Traduce las anotaciones con la tabla de arriba.

WebMCP es un borrador del W3C en origin trial, sin soporte en Safari ni Firefox. Revisar su estado después de TPAC (fines de octubre de 2026) antes de escribir `tools.js`.

### Asistente propio dentro de Lego

Un endpoint PHP (por ejemplo `POST /api/ai/chat`) que hace el bucle de tool calling con el proveedor elegido. La clave del LLM vive en el servidor, nunca en el navegador. El catálogo se pasa tal cual: el formato `name`, `description`, `input_schema` es el que aceptan Anthropic y OpenAI. Cada llamada a una tool pasa por `ActionExecutor` con `via = agent`. Las acciones de UI se devuelven al navegador para que `runUiAction` las ejecute.

### MCP remoto (Claude Desktop, Cursor)

Un endpoint `POST /mcp` (Streamable HTTP) que sirve `tools/list` desde el mismo catálogo y `tools/call` desde `ActionExecutor`, con tokens por usuario acotados a sus permisos. `confirmation_required` se traduce a `input_required` y el bloque `ui` se ignora.

## Qué falta, a propósito

No se construye hasta que haya un consumidor real:

- `tools.js` (adaptador WebMCP), endpoint MCP, panel de chat y elección de proveedor.
- Límites de tasa y presupuestos propios para agentes.
- Borrado suave y "deshacer" como acción.
- Pruebas de inyección (descripciones y datos envenenados) y evals por módulo.

## Reglas para quien escribe módulos

Un test de arquitectura ([tests/Architecture/AiReadinessTest.php](../tests/Architecture/AiReadinessTest.php)) las verifica en todos los módulos:

- Nombre `<módulo>_<verbo>` en snake_case, de 30 caracteres como máximo.
- Descripción de 20 a 500 caracteres, escrita como frase para alguien que no conoce la app.
- Esquema de entrada con `additionalProperties: false` y cada parámetro con título o descripción. En `#[Action]`, usa `#[Param('Título', 'Descripción.')]`.
- Una acción `destructive` o `external` siempre lleva `confirm: true`.
- Si una acción manda algo fuera de la app, declárala `external: true`.
