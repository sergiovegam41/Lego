# IA en el navegador y WebMCP — investigación para Lego 2 (2026-10-13)

Lego se va a rehacer como Lego 2 (PHP 8.3, componentes PHP que devuelven HTML, módulos declarativos a partir de un modelo con atributos, módulos ES nativos sin build, PostgreSQL como única infraestructura obligatoria). El dueño quiere que, más adelante, una IA integrada en el navegador pueda hacer lo mismo que una persona en el panel (crear, editar, borrar, navegar, filtrar) respetando sus permisos, y recuerda haber visto "WebMCP" como estándar.
Este documento sintetiza lo que encontraron seis agentes de búsqueda (spec de WebMCP; MCP y agentes de navegador; patrones de arquitectura; precedentes en frameworks; seguridad y permisos; y un ángulo extra sobre qué agentes de navegador consumen WebMCP hoy), leyendo fuentes primarias con fecha de corte 2026-10-13, más una revisión crítica que verificó 23 URLs con la fuente en mano. Cada hecho lleva su fuente entre corchetes; la lista completa está al final.
El objetivo no es implementar la IA ahora, sino decidir qué forma debe tener Lego 2 para que esa integración sea fácil, escalable y segura cuando llegue, respetando los principios P1 a P6 y las decisiones ya tomadas.

## Respuesta corta

WebMCP existe, pero **no es un estándar aprobado**. A 2026-10-13 es un *Draft Community Group Report* del Web Machine Learning Community Group del W3C, editado por Microsoft y Google, y el propio documento dice que "no es un W3C Standard ni está en el W3C Standards Track" [1][2] (la página se fecha en cada build: los agentes la leyeron el 2026-10-09 y el revisor el 2026-10-13). Es una propuesta en incubación: Chrome la tiene en origin trial desde Chrome 149, sin milestone de envío, con las revisiones del TAG, de seguridad y de privacidad "Pending" (Chrome Status, 2026-09-28) [26]; Edge la tiene en origin trial (Edge 150 según el estado oficial de implementación; flag en Canary/Dev) [4][34][35]; WebKit/Apple se opone formalmente (2026-06-11) [28]; Mozilla es neutral (2026-08-03) y está preparando una posición "refrescada" que a 2026-10-13 no se ha publicado [29][30]; la revisión del W3C TAG sigue abierta sin posición y con la etiqueta "Missing: Multi-stakeholder support" [30]. La API ha cambiado durante el trial (de `navigator.modelContext` a `document.modelContext` en mayo de 2026 [10]) y el único consumidor en producción que se encontró es el navegador integrado de la app de escritorio de ChatGPT ("Site tools", 2026-08-25) [36][110]; Claude in Chrome, Copilot en Edge, Perplexity Comet, Dia y Opera Neon no consumen tools WebMCP hoy según sus propias fuentes [112][115][118][116], y Gemini in Chrome lo soportará "pronto", sin fecha [17].

Lo que sí es estable es la **forma del contrato**: en WebMCP, en MCP (hoy gobernado por la Agentic AI Foundation de la Linux Foundation [51]), en el function calling de OpenAI [74] y de Anthropic [76], una "herramienta" es siempre *nombre + descripción + JSON Schema de entrada + anotaciones de comportamiento + ejecución*. Todos los frameworks que ya exponen acciones a IA (WordPress Abilities API [86][88], Laravel MCP [66], Strapi [100], Directus [99], Payload [102]) mantienen un registro único en el servidor y tratan el navegador o el protocolo como adaptador.

**Recomendación para Lego 2**: no implementar WebMCP ni MCP ahora. Convertir la **acción en ciudadano de primera clase**: cada operación de un módulo (el CRUD derivado del modelo y las acciones custom marcadas con un atributo `#[Action]`) se declara una sola vez con nombre, descripción, esquema de entrada, permiso y anotaciones; de ahí se derivan el botón de la UI, el endpoint JSON y, cuando toque, la tool WebMCP, la tool MCP remota y el comando CLI. Navegar y filtrar se cubren con tres acciones de UI fijas del framework (`ui_navigate`, `ui_open_record`, `ui_set_filter`) y con un efecto `ui` opcional en el resultado de cada acción, de modo que el agente actúa sobre la misma pantalla que ve la persona. Permisos, validación, confirmación y auditoría viven siempre en el servidor y se ejecutan como el usuario real. Con eso, publicar las acciones en `document.modelContext` o en un endpoint MCP será un adaptador de unas decenas de líneas que no toca los módulos.

## Qué es WebMCP

### Definición y estado

WebMCP ("Web Model Context") es una API de navegador que permite a una página web registrar "herramientas" (funciones JavaScript con nombre, descripción y esquema de entrada) para que un agente de IA integrado en el navegador, en una extensión o en la propia página las descubra y las ejecute, en lugar de rascar el DOM o interpretar capturas de pantalla [3].

| Afirmación | Madurez |
|---|---|
| Es un *Draft Community Group Report* del W3C WebML CG (no Working Group, no Standards Track); editores Brandon Walderman (Microsoft), Khushal Sagar y Dominic Farolino (Google). El `index.bs` no tiene campo de fecha: la página renderizada se fecha en cada commit (2026-10-09 al leerla los agentes, 2026-10-13 al verificarla) [1][2]. | **Propuesta** |
| Nació el 2025-08-13 como explainer conjunto Microsoft+Google; antecedente directo: el explainer "Web Model Context API" de Edge (julio 2025, hoy archivado) con `window.agent.provideContext()` [3][42]. | **Propuesta** |
| En las actas del CG del 2026-01-22 se aclara que "no se esperan wide reviews formales para Draft Community Group Reports" [32]. | **Propuesta** |
| Existen tests de plataforma (`wpt.fyi/results/webmcp`) [2]. | **Propuesta** |

### API imperativa (la que está en la spec)

Punto de entrada: `document.modelContext` (atributo de `Document`, solo en contexto seguro / HTTPS) [2]. La interfaz `ModelContext` tiene tres métodos y tres eventos [1][2][3]:

- `registerTool(tool, { signal, exposedTo })`: registra una herramienta; se desregistra abortando el `AbortSignal` (no existe `unregisterTool`). Rechaza con `NotAllowedError` si la Permissions Policy lo bloquea.
- `getTools({ fromOrigins })`: devuelve las tools registradas (por defecto, solo las same-origin del árbol de frames).
- `executeTool(tool, input, { signal })`: ejecuta una tool; en Chrome devuelve `null` si la tool disparó una navegación [19].
- Eventos `toolchange`, `toolactivated`, `toolcancel`.

Un descriptor `ModelContextTool` es [2]:

```js
{
  name: 'flowers_create',            // obligatorio, 1-128 chars ASCII
  title: 'Create flower batch',      // opcional, para UI nativa
  description: '...',                // obligatorio
  inputSchema: { /* JSON Schema */ },
  execute: async (input, { signal }) => { /* Promise<any> */ },
  annotations: {
    readOnlyHint: false,
    untrustedContentHint: false,
    consequentialHint: false,
    debugging: false,
  },
}
```

Ejemplo mínimo, adaptado del `book_flight` de la documentación de Chrome para la API imperativa (publicado 2026-05-18, actualizado 2026-09-21) [19]. El ejemplo de Chrome no usa el segundo parámetro de `execute` ni el segundo argumento de `registerTool`; según el WebIDL de la spec, ese segundo parámetro es un diccionario `{ signal }`, no un `AbortSignal` suelto [2]:

```js
document.modelContext.registerTool({
  name: 'book_flight',
  description: 'Book a flight for the given passengers',
  inputSchema: {
    type: 'object',
    properties: {
      flightId: { type: 'string' },
      passengers: { type: 'integer' },
    },
    required: ['flightId', 'passengers'],
  },
  annotations: {
    readOnlyHint: false,
    consequentialHint: true,
    untrustedContentHint: false,
    debugging: false,
  },
  execute: async ({ flightId, passengers }) => {
    // ... call the site's own API ...
    return 'Booked';
  },
});
```

Detalles relevantes [1][2][19]:

- Permissions Policy `tools`, allowlist por defecto `'self'`; `Permissions-Policy: tools=()` apaga WebMCP en todo el sitio antes de que corra ningún script. Los iframes cross-origin necesitan `allow="tools"`.
- `consequentialHint` es un hint propio de WebMCP: en el issue #176 (abierto 2026-05-08 por Mozilla) el grupo decidió el 2026-05-28 crear un hint nuevo con default `false` en lugar de adoptar el `destructiveHint` de MCP; se cerró el 2026-09-03 vía PR #217; la propuesta de renombrarlo `irreversibleHint` (2026-09-10) sigue sin respuesta [12].
- El formato de retorno de `execute` no está fijado: la spec declara `Promise<any>` y `executeTool` devuelve `DOMString`; el README ejemplifica `{content:[{type:'text',text}]}` estilo MCP; Meta pasa JSON string; la documentación de Chrome dice que pasar los argumentos como JSON string queda "deprecated from Chrome 155" (versión aún no estable a 2026-10-13) [3][19][38].

### API declarativa (fuera de la spec)

Un `<form>` con `toolname` y `tooldescription` se convierte en tool; `toolparamdescription` en cada control describe el parámetro (si falta, se usa el `<label>` y luego `aria-description`); `toolautosubmit` permite que el agente envíe el formulario; `SubmitEvent.agentInvoked` y `respondWith(Promise)` devuelven el resultado; pseudo-clases `:tool-form-active` y `:tool-submit-active` [5][20].

| Afirmación | Madurez |
|---|---|
| El README dice textualmente que la declarativa "is not part of the specification at this time" y que, si entra, "complementaría" a la imperativa [3]. | **Propuesta** |
| Chromium la implementa "de forma laxa" para probar; las reglas de reducción a JSON Schema están "TBD" [5][20]. | **Experimento** |
| OpenAI (ChatGPT Desktop) no la soporta [36]. | **Producto** |

### Quién lo impulsa y estado por navegador y agente

| Actor | Estado (fecha) | Madurez |
|---|---|---|
| Chrome | Early preview 2026-02-10 [15]; DevTrial M146; Intent to Experiment aprobado 2026-05-18 para origin trial M149–M156 [27]. Chrome Status (API JSON, actualizado 2026-09-28): origin trial M149–M156 en escritorio, Android y WebView, extensión **solo de escritorio** hasta M162 con motivo textual "OT feedback has suggested improvements to the API, which we are implementing"; sin milestone de envío; estado "Proposed / Incubation"; TAG, seguridad y privacidad "Pending"; Firefox y Safari "No signal" [26]. Flag local `chrome://flags/#enable-webmcp-testing` [18]. | **Experimento** |
| Gemini in Chrome | "Will soon support WebMCP APIs" (I/O, 2026-05-19), sin fecha [17]. Expedia, Booking.com, Shopify, Credit Karma, TurboTax, Redfin, Etsy, Instacart y Target aparecen en ese post solo en el texto alternativo de una imagen como "marcas experimentando con WebMCP", no como partners de Gemini [17]. La documentación de Chrome (2026-10-07) solo documenta extensiones como consumidores y dice que son "separate from Gemini in Chrome" [18]. | **Anuncio** |
| Edge | "Ready for testing" (blog 2026-09-21; habla de "a browsing agent" genérico, no de Copilot) [33]; repo webmcp-labs con la extensión WebMCP Explorer como único consumidor y activación en Canary/Dev con `about://flags/#enable-webmcp-testing` [34]; origin trial en Edge 150 según implementation-status.md [4], con expiración 2027-03-30 [35]. **Copilot en Edge no consume tools**: un colaborador de Microsoft confirmó el 2026-10-06 que "there is currently no public way to test WebMCP with Copilot in Edge, including the drawer" [115]. | **Experimento** |
| ChatGPT Desktop | "Site tools" (anuncio 2026-08-25): soporte real en producción en el navegador integrado de la app de escritorio, para ChatGPT Work y Codex; requiere GPT-5.6 Sol o GPT-6 Sol (GPT-5.6 Luna lo tiene desactivado); solo API imperativa en el documento top-level, sin iframes ni declarativa; en workspaces Enterprise un administrador debe habilitarlo por política y no está disponible en Edu; cada invocación pasa una revisión de seguridad y las acciones consecuentes exigen confirmación [36][110]. La página no tiene fechas ni habla de tokens de trial. | **Producto** |
| ChatGPT Atlas | Retirado: OpenAI anunció el 2026-07-09 que lo sustituye por una extensión de Chrome, el navegador integrado de la app de escritorio y un navegador en la nube [111] (el artículo oficial de ayuda devolvió 403). No existe fuente que diga que Atlas consumiera WebMCP. | **Producto retirado** |
| Anthropic / Claude in Chrome | GA en planes de pago desde 2026-08-26 [112]; actúa leyendo la página, clicando y tecleando (permisos `scripting`, `debugger`, `tabs`) [113]. **No menciona WebMCP** en el blog, el centro de ayuda ni la doc de Claude Code; la petición anthropics/claude-code#76809 (2026-07-12) sigue abierta sin respuesta de Anthropic y la anterior (#30645) se cerró "not planned" [114]. El navegador integrado de Cowork (2026-08-26) tampoco lo menciona [123]. | **Producto (sin WebMCP)** |
| Brave Leo | Experimental tras flag en Nightly; prompt de permiso por tool con "Allow for session"; el propio autor lo califica "still feels very experimental"; issue reabierto 2026-10-07 [37]. | **Experimento** |
| Perplexity Comet | **No se encontró ninguna fuente primaria** sobre WebMCP: el centro de ayuda de Comet devuelve 404 y el blog no lo menciona [119]; solo hay servidores MCP comunitarios que manejan Comet por CDP. | **Sin dato** |
| Opera Neon / Opera | Dirección inversa: Neon expone el navegador como **servidor MCP** a Claude, ChatGPT, Lovable, n8n y cualquier cliente MCP (2026-03-31; gratis para agentes externos desde 2026-08-14); ninguna mención a WebMCP [116][117]. | **Producto (sin WebMCP)** |
| Dia | Changelog hasta Mac 1.52.0 / Windows 1.1.0 (2026-10-08) sin ninguna mención a WebMCP, `modelContext` ni MCP [118]. | **Producto (sin WebMCP)** |
| Cloudflare Browser Run | Soporta WebMCP en sesiones "lab" desde 2026-04-15: un cliente MCP conectado por CDP descubre y ejecuta tools con pausas de confirmación; consumidor headless, útil para pruebas automatizadas [120]. | **Producto** |
| Meta Ray-Ban Display | "Coming soon"; timeout de 10 s por tool; formato de argumentos distinto de la spec [38]. | **Producto anunciado** |
| Firefox | Sin implementación enviada ("No signal" en Chrome Status [26]). Existe un meta-bug de prototipo (Bugzilla 2018306, asignado a Benjamin VanderSloot, con la API imperativa en content process y el servicio de enumeración ya resueltos) [121]; los bugs 2042248 y 2053854 son sincronizaciones de web-platform-tests, no implementación [122]. Mozilla "position: neutral" (2026-08-03): el nombre es engañoso ("Website Tool API" sería mejor), riesgos adversariales, interés en reformular como RPC genérico (issue #236) [29][13]. Sin comentarios en #1412 desde 2026-08-26; la posición "refrescada" no está publicada. | **Experimento / opinión** |
| Safari / WebKit | "position: oppose" (2026-06-11): los huecos deben cerrarse en HTML/ARIA, los agentes son tecnología asistiva y el sitio no debería saber que lo opera un agente, falta modelo de consentimiento y reversibilidad, cross-origin sin examinar; el 2026-06-17 propuso apartar WebMCP, crear un nuevo CG ("agent-assisted user agents") y un workshop en TPAC (26–30 oct 2026). No se encontró evidencia de que ese CG se haya formado [28]. | **Opinión** |
| W3C TAG | Revisión #1238 abierta desde 2026-06-11, "in progress", sin posición formal; el 2026-10-06 dijo que "is taking longer than usual"; el 2026-10-09 Dominic Farolino (Google) pidió al TAG **esperar a "la posición refrescada de Mozilla" y a TPAC** antes de publicar, diciendo que trabaja con Mozilla en cambios de API para ganar su apoyo [30]. | **Propuesta** |

Dato cerrado por el revisor: la aparente discrepancia entre agentes sobre "Chrome 162" se resuelve con el registro de Chrome Status (2026-09-28): el intent original fue M149–M156 con envío tentativo M157 [27]; después se extendió el trial solo en escritorio hasta M162 por el feedback recibido, y hoy **no hay milestone de envío** [26]. Con la cadencia de cuatro semanas (Chrome 149 estable en junio de 2026; DevTools 149 el 2026-06-02 [25]), Chrome 155 y 156 corresponden a noviembre y diciembre de 2026: los cambios que Chrome documenta para esas versiones son de Canary/Dev, no de estable.

### Tooling

Chrome DevTools 149 tiene un panel WebMCP en Application (lista de tools, log de invocaciones, ejecución manual) [25]; GoogleChromeLabs publica demos, polyfill, evals y una extensión de ejemplo ("not production-ready") [39][40]; el CG resolvió alojar un polyfill oficial (actas 2026-08-20) [31]; Playwright 1.64 añade `page.webmcp.tools()` / `callTool()` [54]; y la comunidad MCP-B / WebMCP-org mantiene polyfills y transportes ("Not an official W3C or MCP project") [55][56].

### Limitaciones y críticas

- **Seguridad no normativa**: la sección 6 de la spec dice que "cannot define precise mitigations"; "Same-Origin Boundaries" es un TODO; no hay verificación de que la intención declarada coincida con el comportamiento (ejemplo `finalizeCart` que compra) [1][8].
- **Las anotaciones son solo hints**: la spec no define conducta obligatoria para ninguna; Chrome admite que "it's impossible to guarantee safety inside of a large language model" [1][23].
- **Confirmación de usuario sin resolver**: `requestUserInput` (issue #165) sigue abierto; solo el modo "interactive" se ha resuelto especificar (PR #204) [11].
- **Churn durante el trial**: `navigator` → `document` (2026-05-27) [10]; Chrome documenta que los argumentos como JSON string quedan "deprecated from Chrome 155", que la anotación `debugging` y la retirada de `window.ontoolactivated` llegan "from Chrome 156", versiones que a 2026-10-13 todavía no son estables [19][20]; la API imperativa sigue en discusión con Mozilla (handles `Tool`, `call()`, renombrar `executeTool`, issue #236, último comentario 2026-09-24) [13].
- **Inyección de tools**: el paper arXiv 2606.06387 (2026-06-04) describe "Mid-Session Tool Injection" por scripts de terceros (Tool Hijacking, Tool Framing) [41]. The Decoder (2026-02-13) recoge que Google traslada la defensa contra prompt injection al agente [109] (fuente secundaria, confianza media).
- **Adopción real baja**: de 11.538 sitios comprobables, 580 registran al menos una tool, casi todas de búsqueda/lectura y vía unos pocos plugins (issue #319, 2026-09-23) [14].
- **Sin descubrimiento externo**: "Clients and browsers must visit a site directly to know if it has callable tools" [18].
- **No normativo todavía**: continuations (flujos multi-página) [6], service workers (tools en segundo plano; el propio explainer cita la "lethal trifecta") [7], `outputSchema`, streaming, multimodal [3].

## El ecosistema alrededor

### MCP (Model Context Protocol)

MCP es una especificación abierta de facto, donada por Anthropic a la Agentic AI Foundation (Linux Foundation; cofundada con Block y OpenAI, con apoyo de Google, Microsoft, AWS, Cloudflare y Bloomberg) el 2025-12-09; Anthropic reportaba entonces "más de 10.000 servidores MCP públicos activos" (cifra del anuncio, no verificada de forma independiente) [51]. **Madurez: estándar de facto, no de organismo**. La revisión vigente es 2026-07-28 [43]:

- Transporte Streamable HTTP con un solo endpoint POST, **sin sesiones ni handshake `initialize`** (cada petición lleva versión y capacidades), cabeceras obligatorias `MCP-Protocol-Version`, `Mcp-Method`, `Mcp-Name`, validación de `Origin` (403), 405 a GET/DELETE [44]. Encaja de forma natural con el modelo petición/respuesta de PHP-FPM.
- Autorización opcional OAuth 2.1 donde el servidor MCP es resource server (RFC 9728 obligatoria, RFC 8707, PKCE, CIMD recomendado, DCR deprecado) [45].
- Tool = `name`, `title`, `description`, `inputSchema` (JSON Schema 2020-12), `outputSchema` opcional, `annotations` (`readOnlyHint`, `destructiveHint`, `idempotentHint`, `openWorldHint`) [46][50]. `tools/list` no debe variar por conexión, pero **puede variar según la autorización** (devolver solo lo que los scopes permiten); se recomienda orden determinista y `ttlMs`/`cacheScope` [46][43].
- Patrón MRTR: una tool responde `resultType: "input_required"` y el cliente reintenta con las respuestas del usuario (accept/decline/cancel); es el mecanismo estándar de confirmación a mitad de una acción [43].
- SDK oficial PHP `mcp/sdk` 0.8.1 (2026-08-29, PHP ^8.1, atributos `#[McpTool]`, descubrimiento por directorio), **marcado experimental** [52].
- MCP Apps (SEP-1865, estable 2026-01-26): un servidor MCP puede entregar UI HTML en iframe sandbox [53].

### WebMCP frente a MCP

Chrome lo dice explícito: WebMCP es "MCP-inspired", no una implementación de MCP; es efímero y ligado a la pestaña, no tiene "resources" ni concepto de servidor; "MCP is for backend, WebMCP is for frontend"; recomienda usar ambos [21]. Mozilla critica el nombre precisamente porque la API "no usa MCP" [29]. El puente práctico entre ambos existe como producto/experimento: Chrome DevTools MCP lista y ejecuta tools WebMCP desde Chrome 149 [25][65]; MCP-B reenvía tools de la página a Claude Desktop/Cursor (la extensión original ya no es open source) [55][56]; Cloudflare Browser Run hace lo mismo en la nube [120].

### IA integrada en el navegador

- **Prompt API / Gemini Nano** (Chrome): estable en Chrome 148 para web, pero con requisitos duros (22 GB libres, GPU > 4 GB VRAM o 16 GB RAM, solo escritorio) y sin Web Workers [57]. El tool calling en ciclo cerrado "no forma parte aún de la especificación ni está implementada"; Chrome tiene un flag experimental `#prompt-api-tool-use` [58]. **Producto (API) / propuesta (tools)**.
- **Gemini in Chrome "auto browse"** (preview, enero 2026): tareas multi-paso desde el panel lateral, confirmación antes de compras o publicaciones; el post no menciona WebMCP [62]. **Producto**.

### Agentes de navegación actuales

Todos operan hoy sobre el árbol de accesibilidad/DOM y capturas, no sobre tools estructuradas (con la única excepción del navegador integrado de ChatGPT), y todos reconocen la inyección indirecta de prompt como problema no resuelto:

- **Claude in Chrome** (Anthropic): research preview el 2025-08-25 para 1.000 usuarios Max, beta para Max el 2025-11-24, Pro/Team/Enterprise el 2025-12-18 [59], GA en todos los planes de pago el 2026-08-26 [112]; permisos por sitio, confirmación antes de acciones de alto riesgo, categorías bloqueadas; tasa de éxito de prompt injection 23,6 % → 11,2 % (cifras del post de 2025) [59]. **No consume WebMCP** (issue #76809 abierto) [114]. **Producto**.
- **ChatGPT**: Atlas (2025-10-21) fue retirado en julio de 2026 [111]; su CISO había dicho que "prompt injection remains a frontier, unsolved security problem" [61] (confianza media: fuente secundaria, openai.com devolvió 403). El consumo de WebMCP vive en el navegador integrado de la app de escritorio ("Site tools") [36][110]. **Producto**.
- **Gemini in Chrome**: "User Alignment Critic" (modelo separado que solo ve metadatos de la acción), "Agent Origin Sets", confirmación en acciones consecuentes [60]; soporte WebMCP anunciado sin fecha [17]. **Producto**.
- **Copilot en Edge**: sin consumo de tools WebMCP, confirmado por Microsoft el 2026-10-06 [115]. **Producto (sin WebMCP)**.
- **Perplexity Comet, Dia, Opera Neon**: sin fuente primaria sobre WebMCP (Comet) [119], sin mención en el changelog (Dia) [118], y Opera va en dirección inversa exponiendo el navegador como servidor MCP [116]. **Producto (sin WebMCP)**.
- **Playwright MCP**, **Browser Use**, **Chrome DevTools MCP**: agentes/servidores MCP que ven la página por snapshot de accesibilidad o DOM indexado [63][64][65]. **Producto**.

### Hacia dónde converge

Tres señales apuntan al mismo sitio: (1) los frameworks web ya exponen acciones estructuradas con el vocabulario de MCP (WordPress, Laravel, Strapi, Directus, Payload; ver "Precedentes"); (2) los agentes de navegador van añadiendo consumo de tools estructuradas encima de la navegación por DOM (Chrome DevTools MCP, ChatGPT Site tools, Cloudflare Browser Run, Gemini "soon"); (3) la discusión en el W3C (Mozilla, issue #236) empuja a reformular WebMCP como llamadas de función genéricas entre entornos [13]. Lo que no converge todavía es el **transporte** (API del navegador, protocolo HTTP, extensión), y por eso la pieza estable es el catálogo de acciones en el servidor. Mientras tanto, lo que mejor funciona con los agentes reales de hoy (Claude in Chrome, Comet, Copilot, Playwright MCP) es HTML semántico y accesible: etiquetas, roles y nombres accesibles en los componentes PHP benefician a todos los agentes actuales sin esperar a WebMCP, y responden a la objeción de WebKit [28][63]. Un rastreador secundario (webmcp.com, 2026-10-01) coincide con este panorama; sus afirmaciones sin fuente primaria (p. ej. que la extensión de Codex funcione tras un toggle CDP) no se han verificado [124].

## El patrón: la acción como ciudadano de primera clase

Síntesis de los patrones encontrados, con nombre, fuente y principio:

| Patrón | Fuente | Principio |
|---|---|---|
| **Agent-native / una acción, todas las superficies** | Builder.io, `@agent-native/core` [71][72] | "One action powers every app surface: UI, agent, HTTP, MCP, A2A, and CLI". Cada copia (handler de UI, endpoint, hook, tool, CLI) genera deriva; "define the action once". |
| **Paridad humano-IA** | Builder.io [72]; James Phoenix [73]; Shopify Sidekick [104] | "Anything the UI can do, the agent can do" y, a la inversa, "if you cannot access a customer record, the agent should not be able to access it on your behalf". Phoenix propone un *parity audit*: listar cada acción de UI y verificar que el agente la alcanza. |
| **Action class con autorización y validación dentro** | Laravel Actions [70]; Laravel MCP [66] | Una clase que corre como controlador, job o comando; `authorize()` y `rules()` viven en la clase, no en el transporte. |
| **Registro único con permiso obligatorio** | WordPress Abilities API [86][87] | `wp_register_ability()` exige `permission_callback`; de ahí salen REST, JS y MCP. |
| **Vocabulario de anotaciones de MCP** | MCP [46][50]; WordPress [90]; Laravel [66] | `readOnly`, `destructive`, `idempotent`, `openWorld`; en WordPress deciden incluso el método HTTP (readonly → GET, destructive+idempotent → DELETE, resto → POST) [88]. |
| **Verificar dos veces: al listar y al ejecutar** | Laravel MCP 1.0 [67]; API Platform 4.3.18 [98]; MCP [46] | "Un resultado de búsqueda nunca es una concesión de capacidad". El catálogo se filtra por usuario, y la ejecución vuelve a autorizar. |
| **Proponer → confirmar** | Shopify Sidekick [104]; OpenAI GPT Actions [75]; MCP MRTR [43]; Laravel AI `Approvable` [69] | Las acciones de escritura requieren confirmación humana; GPT Actions lo hace por defecto para todo lo que no sea GET (`x-openai-isConsequential`). |
| **Pocas tools de flujo, bien nombradas** | Anthropic [77]; OpenAI [74]; Chrome [22] | Namespacing por recurso, parámetros inequívocos, descripciones "como para un recién contratado", menos de 20 tools por turno, una función por tool. |
| **Estado compartido humano/agente** | Spec WebMCP [1]; Chrome best practices [22]; Chrome docs [18] | El agente actúa sobre la misma pantalla que ve la persona ("tools execute on your webpage visibly"); tras cada acción la UI se sincroniza porque el agente la usa para planificar. Navegar y filtrar son cambios de esa pantalla, no solo datos. |
| **Catálogo buscable cuando crece** | Laravel `ToolSearch` [67]; Directus registry mode [99]; WP mcp-adapter [90] | Tres meta-operaciones (search/describe/execute) cuando listar todo infla el contexto. |
| **Affordances por estado** | Nordic APIs / Darrel Miller [80]; schema.org `potentialAction` [79] | Cada respuesta incluye las acciones permitidas en ese estado; sin evidencia empírica de mejora para LLM, pero con beneficio directo para la UI (sin lógica de permisos duplicada en el front). **Opinión / propuesta**. |
| **Intención ≠ efecto** | ESAA arXiv 2602.23193 [85]; patrones de seguridad arXiv 2506.08837 [108] | El agente emite intenciones JSON validadas; un orquestador determinista aplica los efectos y lleva log append-only. |

### La paridad humano-IA en Lego 2

Una acción declarada una vez se deriva a cinco superficies:

```
                     ┌─────────────────────────────────────┐
                     │  Action (declarada una sola vez)    │
                     │  name · description · inputSchema   │
                     │  permission · annotations · handle  │
                     └──────────────┬──────────────────────┘
        ┌──────────────┬────────────┼─────────────┬──────────────────┐
        ▼              ▼            ▼             ▼                  ▼
   Botón / form    Endpoint     Tool WebMCP   Tool MCP remota    Comando CLI
   (componente     JSON         (tools.js →   (POST /mcp,        (php lego run
    PHP)           POST /api/   document.     Streamable HTTP)    <name> ...)
                   actions/x    modelContext)
```

Las cinco llaman a la **misma función PHP** con el **mismo contexto de usuario**; ninguna tiene lógica propia. Esto es exactamente lo que evita el problema que la propia spec de WebMCP señala en 6.3.1.3: que la vía de tools valide distinto que la UI [1]. Las dos superficies del navegador (botón y tool WebMCP) comparten además la pantalla: el resultado de una acción puede traer un efecto `ui` (ruta, filtros, registro a enfocar) que el cliente aplica, y tres acciones de UI fijas del framework cubren navegar, abrir y filtrar sin pasar por el servidor (ver "Propuesta").

## Precedentes

| Framework | Cómo declara una capacidad | Cómo mapea permisos | Cómo la expone | Qué copiar |
|---|---|---|---|---|
| **WordPress Abilities API** (núcleo desde 6.9, 2025-12-02) [86][87][89] | `wp_register_ability('ns/name', [label, description, category, input_schema, output_schema, execute_callback, permission_callback, meta.annotations{readonly,destructive,idempotent}, meta.public])` en el hook `wp_abilities_api_init`. | `permission_callback` **obligatorio**, recibe `$args` (permite decidir por registro); dos capas en MCP: transporte (`is_user_logged_in()` por defecto) + ability [91]. | REST `wp-abilities/v1` (list/get/run, opt-in `show_in_rest`); desde WP 7.0 (mayo de 2026; la fecha exacta 2026-05-20 que dio un agente no se verificó) store JS `@wordpress/abilities` + `@wordpress/core-abilities` que carga el registro y ejecuta vía REST con la sesión del usuario, con abilities de solo cliente para navegar (ejemplo `my-plugin/navigate-to-settings`) [88]; MCP Adapter 0.7.0 (2026-10-02) opt-in con `meta.public` [90]; WebMCP solo como PR en borrador no fusionado [92]. | El registro único con permiso obligatorio; el mapeo anotación → método HTTP; el módulo JS que replica el registro en el navegador y admite acciones de solo cliente; "privado por defecto". |
| **Laravel MCP 1.0** (estable sep-2026) [66][68] | Clase `Tool` con `#[Name]`, `#[Description]`, `schema(JsonSchema)`, `handle(Request)`; anotaciones como atributos `#[IsReadOnly]`, `#[IsDestructive]`, `#[IsIdempotent]`, `#[IsOpenWorld]`. | `shouldRegister(Request)` oculta por usuario; `$request->user()->can()` dentro de `handle()`; re-verificación al ejecutar en 1.0 [67]. | Servidor MCP (Sanctum/Passport); `ToolSearch` para catálogos grandes. | Los atributos nativos como anotaciones; la re-verificación en ejecución. **No copiar** el registro manual en `$tools` (viola P1). |
| **Laravel Actions** (2.x) [70] | Una clase PHP que corre como objeto, controlador, job, listener o comando. | `authorize(ActionRequest)` y `rules()` en la clase, invocados automáticamente como controlador. | Cualquier superficie de Laravel. | La idea "una clase, muchas superficies" con autorización dentro. |
| **Symfony: mcp/sdk, AI Bundle, API Platform 4.3** [52][96][97][98] | `#[McpTool]`/`#[Schema]` sobre métodos; `#[AsTool]`; o `mcp: [...]` dentro de `#[ApiResource]`. Schema derivado de la firma tipada y docblocks. | `#[IsGrantedTool('ROLE_X')]`; expresiones `security` y voters aplicados a tools; `access_control` por path. `needs_confirmation` "solo describe, no impone". | Servidor MCP; descubrimiento por directorio o por contenedor. | Descubrimiento por escaneo (Reflection) sin lista manual; schema desde la firma. |
| **Filament (guava/filament-mcp 1.0.0, 2026-08-06; sin paquete oficial)** [94][95] | `McpResource::make(PostResource::class)->crud()` genera list/get/create/update/delete; argumentos derivados del form schema. | list→viewAny, get→view, create→create, update→update, delete→delete de la policy; `requiresConfirmation()` **no** se traslada a MCP. | Endpoint `/mcp/{panel}` con tokens por recurso. | El CRUD derivado del esquema de formulario; el mapeo CRUD → permiso por convención. |
| **Strapi MCP** (GA 5.49, 2026-09-03) [100][101] | Hasta 8 tools por content-type generadas; custom tools con `registerTool({..., auth: {policies}})`; schemas que se estrechan según `userAbility`. | Token acotado al dueño; 4 niveles: visibilidad de tool, filtrado de campos, locale, chequeo por documento en ejecución. | Endpoint `/mcp`, opt-in (`server.mcp.enabled=false`). | Permisos en capas y schema dependiente del usuario. |
| **Directus** (nativo desde 11.12) [99] | Tools fijas (items, schema, flows...) + modo registry (search/execute/schema). | Permisos del usuario conectado; toggle "Allow Deletes" off por defecto. | `/mcp`, deshabilitado por defecto, OAuth o token. | El modo registry para escalar; "deletes off" como default. |
| **Payload** (plugin beta 3.61, 2025-10-23) [102][103] | `collections: { slug: { enabled: { find, create, update, delete } } }`. | API key por usuario con toggles por colección y operación; CVE-2026-105806 por permitir gestionar keys ajenas. | `POST /api/mcp`. | La granularidad por operación. **Evitar** el fallo de tokens no acotados al dueño. |
| **Shopify Sidekick app extensions** (2026-06-17) [104] | Tools con `name` ≤ 64 y `description` ≤ 512, máximo 20 por app; "data extensions" solo lectura, "action extensions" con confirmación obligatoria. | Confirmación del comerciante no evitable. | Dentro de Sidekick. | Límites de tamaño y la separación lectura / acción con confirmación. |
| **Builder.io agent-native** (TS, MIT) [71] | `defineAction({ description, schema, http: {method}, run })`. | "Both paths use the same validation, permissions, and implementation". | UI (`useActionQuery`), HTTP, MCP, A2A, CLI. | El concepto completo de cinco superficies. |

**Conclusión de los precedentes**: ningún framework PHP expone de forma oficial acciones a una IA embebida en su propio panel (Filament no tiene paquete de primera parte; el "Site Agent" de WordPress está en roadmap [93]). Lego 2 estaría por delante; conviene validar con un prototipo mínimo antes de fijar la API. Las versiones y fechas de los precedentes (Strapi 5.49, guava/filament-mcp 1.0.0, Payload CVE-2026-105806, mcp/sdk 0.8.1, Playwright 1.64) proceden de un solo agente y el revisor no las comprobó: confianza media.

## Seguridad y permisos por diseño

Reglas concretas que Lego 2 debería imponer desde el framework, no desde el prompt:

1. **Una sola puerta de autorización, en el servidor, como el usuario real.** Toda acción es un comando PHP con su comprobación de permisos; UI, API y tools son front-ends finos del mismo comando. Nunca un "usuario IA" con permisos propios (OWASP LLM06: "Execute extensions in user's context", "Complete mediation" [83]; OWASP ASI03 [82]; Directus, Strapi, Payload y Agentforce ejecutan como el usuario conectado [99][100][102][105]; OpenAI exige además que un administrador habilite "Site tools" por política en Enterprise [36]).
2. **Re-verificar el permiso al ejecutar, no solo al listar.** El catálogo se filtra por RBAC, pero `execute` vuelve a autorizar contra la sesión actual (OWASP ASI03 TOCTOU, "Mandate Per-Action Authorization" [82]; Laravel MCP 1.0 [67]). La identidad sale siempre de la sesión del servidor, jamás de un parámetro de la tool (MCP Elicitation: "MUST NOT rely on client-provided user identification" [49]).
3. **Las anotaciones son hints; la confirmación es del servidor.** MCP: "clients MUST consider tool annotations to be untrusted" [47]; la spec de WebMCP no exige conducta alguna [1]. Para acciones destructivas o consecuentes: fase 1 *preview* sin efectos + fase 2 *commit* con un token de confirmación de un solo uso, corta vida, ligado al usuario y al hash de los argumentos (OWASP ASI02 "pre-execution plan or dry-run", ASI09 "Separate preview from effect" [82]; MCP MRTR `input_required` [43]). "Un solo uso" exige registrar el consumo: el preview crea la fila en `action_log` con estado `pending` y el commit la marca `done`; no hace falta ninguna tabla ni almacén adicional.
4. **Esquema de entrada estricto y derivado del modelo.** La validación real es la de los tipos PHP de la firma y las reglas del modelo (longitudes, enums, obligatorios); el JSON Schema con `additionalProperties: false` es una **exportación derivada** de esas mismas reglas, no una segunda vía de validación ni una dependencia de librería JSON Schema (P2, P5). Solo campos que existan en el modelo (evita la "over-parameterization" que la spec de WebMCP y WebKit señalan como fuga de privacidad [1][28]); Salesforce prohíbe aceptar IDs, objetos o campos arbitrarios como input [105]. Estricto en código, laxo en schema, con errores accionables [22].
5. **Datos no son instrucciones.** Salidas en JSON tipado, nunca HTML; campos de texto libre de usuario marcados `untrustedContent` y delimitados (spotlighting); paginación y límite de ~1,5 K caracteres por salida [23][24]; el escape explícito de Lego se mantiene para todo lo que renderice el agente (OWASP LLM05 [84]).
6. **Auditoría append-only en PostgreSQL** con `actor_user_id`, `via` (`ui` | `api` | `agent` | `cli`), `agent_id` opcional, acción, argumentos (o hash si hay PII), estado de confirmación, resultado, `trace_id` (OWASP ASI02 "immutable logs of all tool invocations" [82]; MCP "Log tool usage for audit purposes" [47]; AWS AGENTSEC03-BP02: acciones de agente y de humano distinguibles [81]).
7. **Reversibilidad por defecto.** Soft delete y guardar el "antes" en las actualizaciones, de modo que "deshacer" sea otra Action; la confirmación no elimina el error humano (OWASP ASI09: el revisor aprueba porque confía en la IA [82]).
8. **Límites de tasa y presupuestos para agentes**, distinguibles de la sesión humana (OWASP LLM06 "Rate limiting", ASI02 "Adaptive Tool Budgeting" [82][83]; MCP "Rate limit tool invocations" [47]). El marcador de canal que pone el adaptador (p. ej. cabecera `X-Lego-Channel: tool`) sirve solo para auditoría y límites, nunca para autorizar: cualquier agente puede operar la UI con la sesión del usuario sin que el servidor lo sepa [116][112].
9. **Aislamiento de origen y cabeceras.** `Permissions-Policy: tools=()` por defecto (el navegador lo aplica antes de ejecutar cualquier script, lo que protege frente a scripts de terceros comprometidos [1]); solo `'self'` cuando el dueño active la IA; nunca `exposedTo` cross-origin; CSP `script-src 'self'` + nonce; cookies `__Host-`, `Secure`, `HttpOnly`, `SameSite=Lax`; CSRF en toda escritura (MCP security best practices [48]); HTTPS obligatorio (la API es `[SecureContext]` [2]). Sin scripts de terceros en el admin (ataque de inyección de tools a mitad de sesión [41]).
10. **Evitar la "lethal trifecta".** Un asistente que lee datos privados con texto de usuario no debe tener además canales de comunicación externa (email, webhooks, exportar a URL) sin confirmación humana por acción [106]. Declararlo en el descriptor (`external: true`) para que la política sea mecánica, no de prompt. Los patrones CaMeL y "Design Patterns for Securing LLM Agents" formalizan el mismo principio: la entrada no confiable no debe poder disparar acciones consecuentes [107][108].
11. **No-objetivos explícitos**: tools en service workers (sin pestaña ni humano) [7][28]; `toolautosubmit` en formularios consecuentes [5]; claves de LLM en el navegador (si hay asistente propio, la clave vive en PHP); generación o `eval()` de código PHP por el modelo (Laravel lo descartó por seguridad y fragilidad [67]).
12. **Kill switch y alcance administrable.** Toggle por app para exponer acciones a agentes; por defecto solo acciones de solo lectura; las escrituras exigen opt-in explícito del módulo (precedentes: WordPress `meta.public=false`, Strapi `mcp.enabled=false`, Directus deshabilitado [86][100][99]; listas de permitidos/bloqueados de administrador en Claude for Chrome [59]; política de administrador para "Site tools" en ChatGPT Enterprise [36]).

## Propuesta para Lego 2

### Prerrequisito: el modelo de permisos

Toda la propuesta asume un modelo de permisos que **aún no está decidido** para Lego 2 (ver decisión 1). Lo mínimo que la propuesta necesita: permisos por módulo y operación con nombre por convención (`<modulo>.<view|create|update|delete>`), una función única que resuelva "qué puede hacer este usuario con esta acción sobre este registro" y que reciba `(User, Action, array $input, ?Model $record)`, y que esa función sea la única que consultan la UI, el endpoint, la CLI y los adaptadores. Si el dueño añade permisos por fila, cambia la firma de esa función, no la propuesta.

### El concepto: `Action`

Cada operación que una persona puede hacer en un módulo es una **Action**. Metadatos mínimos:

| Campo | Para qué | Origen |
|---|---|---|
| `name` | Identificador único, `snake_case`, `<modulo>_<verbo>`, ≤ 30 chars (límite recomendado por Chrome [23]; MCP admite `[A-Za-z0-9_.-]` hasta 128) | Derivado (`flowers_list`) o declarado |
| `title` | Texto corto para humanos (botón, menú) | Declarado o derivado del nombre |
| `description` | Texto para la IA, ≤ 500 chars, "como para un recién contratado" [77][23] | Declarado; para CRUD, plantilla + descripción del modelo |
| `inputSchema` | JSON Schema 2020-12 con `additionalProperties: false`; descripciones de parámetro ≤ 150 chars. **Exportación derivada** de los tipos PHP y de las reglas del modelo; no se valida contra él en el servidor | Derivado del modelo (create/update/filter) o de la firma tipada del método |
| `permission` | Permiso requerido, `<modulo>.<view|create|update|delete>` por convención; explícito en acciones custom | Convención (P3) |
| `readOnly` | No modifica estado | Derivado (list/get → true) |
| `destructive` | Borra o altera de forma no trivial | Derivado (delete → true) |
| `idempotent` | Repetir con los mismos argumentos no tiene efecto adicional | Derivado (get/list/update/delete → true; create → false) |
| `confirm` | Requiere confirmación humana en dos fases | Derivado: `destructive` → true; declarable en custom |
| `untrustedContent` | La salida contiene texto libre escrito por usuarios | Derivado: el modelo tiene campos de texto |
| `external` | Comunica con el exterior (email, webhook) | Declarado; por defecto false |
| `ui` (en el resultado, no en el descriptor) | Efecto sobre la pantalla tras ejecutar: `{ route?, filters?, focus? }` | Derivado: create → `focus` en el nuevo registro; delete → volver a la lista; declarable en custom |

El vocabulario es **propio de Lego** y se mapea en el adaptador: a WebMCP (`readOnlyHint`, `consequentialHint` = destructive o external, `untrustedContentHint`) y a MCP (`readOnlyHint`, `destructiveHint`, `idempotentHint`, `openWorldHint` = external). Así un renombrado futuro (`irreversibleHint` [12]) no toca los módulos.

### Navegar y filtrar: acciones de UI

El dueño enumera cinco verbos. Crear, editar y borrar son acciones de servidor; **navegar** (cambiar de módulo, abrir un registro) y **filtrar** (cambiar lo que muestra la tabla que la persona está viendo) son cambios de estado de la pantalla, y la spec y Chrome insisten en que el agente actúa sobre la misma pantalla que ve el humano [1][22]. Una tool `flowers_list` que devolviera JSON sin que la tabla cambiase rompería esa paridad. Propuesta, en dos piezas que no añaden nada a los módulos:

1. **Tres acciones de UI fijas del framework**, registradas en el cliente por `tools.js` (no por los módulos) y marcadas `readOnly` desde el punto de vista del servidor: `ui_navigate({ module, view? })`, `ui_open_record({ module, id })`, `ui_set_filter({ module, filters })`. Las dos primeras usan rutas estables por módulo (`/<modulo>`, `/<modulo>/<id>`), que la arquitectura de fragmentos HTML de Lego ya resuelve sin recargar; el `inputSchema` de `ui_set_filter` es el **mismo esquema de filtros** que genera el modelo para el formulario de filtros de la tabla (una sola fuente, P2). Son el equivalente de las abilities de solo cliente de WordPress (`navigate-to-settings`) [88].
2. **Efecto `ui` en el resultado de una acción de servidor.** `flowers_create` devuelve `{ result, ui: { focus: { module: 'flowers', id: 12 } } }`; `flowers_list` devuelve `{ result, ui: { filters } }` para que la tabla se repinte con los mismos filtros. `tools.js` y los componentes aplican `ui`; la CLI y el adaptador MCP remoto lo ignoran. Esto cumple "actualiza la UI tras cada acción" de Chrome [22] sin lógica paralela.

Lo que debe emitir cada **componente PHP de pantalla** (la clase `Screen` del framework, no cada módulo) para que esto funcione:

- El **catálogo de su pantalla embebido en el HTML**: un `<script type="application/json" data-lego-actions>` con los descriptores de las acciones disponibles para ese usuario en esa pantalla (ya filtrados por permiso en el servidor, 5–10 por pantalla). Así `tools.js` sabe qué registrar sin un fetch extra a `GET /api/actions`, y el catálogo cambia con cada fragmento HTML que se monta (P5).
- Marcadores estables: `data-lego-module`, `data-lego-record`, y rutas por convención; son los mismos que usan los agentes basados en DOM [63].
- El formulario de filtros con `name` iguales a las propiedades del esquema de filtros del modelo, y un evento de ciclo de vida (`lego:screen-mounted` / `lego:screen-unmounted`) que `tools.js` usa para el `AbortController` de registro y desregistro.

### Ejemplo en PHP 8

El CRUD no se declara: sale del modelo con atributos que ya genera API, tabla y formularios. Solo las acciones custom llevan `#[Action]`:

```php
<?php
declare(strict_types=1);

namespace App\Modules\Flowers;

use Lego\Action\Action;
use Lego\Action\Param;
use Lego\Module\Module;

final class FlowersModule extends Module
{
    // list / get / create / update / delete / filter are derived from the Flower
    // model attributes. Nothing to declare for them.

    #[Action(
        description: 'Mark a flower batch as sold and remove it from the available stock.',
        permission: 'flowers.update',
        idempotent: true,
        confirm: true,
    )]
    public function markSold(
        #[Param('ID of the flower batch to mark as sold')] int $batch_id,
        #[Param('Optional note for the sales log, max 200 chars', maxLength: 200)] ?string $note = null,
    ): array {
        $batch = $this->repo->find($batch_id);
        $batch->sold_at = $this->clock->now();
        $batch->sold_note = $note;
        $this->repo->save($batch);

        return [
            'batch_id' => $batch->id,
            'sold_at'  => $batch->sold_at,
            'ui'       => ['focus' => ['module' => 'flowers', 'id' => $batch->id]],
        ];
    }
}
```

El registro deriva de ahí este descriptor (el `name` sale de `<modulo>_<metodo en snake_case>`; el schema, de la firma tipada y de `#[Param]`; la validación en servidor la hacen los tipos PHP y `#[Param]`, no el JSON):

```json
{
  "name": "flowers_mark_sold",
  "title": "Mark sold",
  "description": "Mark a flower batch as sold and remove it from the available stock.",
  "inputSchema": {
    "type": "object",
    "properties": {
      "batch_id": { "type": "integer", "description": "ID of the flower batch to mark as sold" },
      "note": { "type": ["string", "null"], "maxLength": 200, "description": "Optional note for the sales log, max 200 chars" }
    },
    "required": ["batch_id"],
    "additionalProperties": false
  },
  "permission": "flowers.update",
  "readOnly": false, "destructive": false, "idempotent": true,
  "confirm": true, "untrustedContent": false, "external": false
}
```

Y así se deriva a las cinco superficies:

1. **Botón / formulario (componente PHP)**: el componente de fila de la tabla de `flowers` recibe del registro las acciones permitidas para ese usuario y ese registro, pinta un botón "Mark sold" y embebe el descriptor en el catálogo JSON de la pantalla; como `confirm: true`, el botón abre el diálogo de confirmación con el preview que devuelve el servidor. El front no contiene lógica de permisos.
2. **Endpoint JSON**: `POST /api/actions/flowers_mark_sold` con sesión + CSRF; el cuerpo se mapea a los parámetros tipados del método y se valida con las reglas del modelo y de `#[Param]`; respuesta `{ status: "confirmation_required", preview, token }` y, tras reenviar el token, `{ status: "ok", result, ui }`. `GET /api/actions` (catálogo completo filtrado por permisos) y `GET /api/actions/{name}` existen para la CLI, los adaptadores remotos y la documentación; la pantalla no los necesita porque lleva el catálogo embebido.
3. **Tool WebMCP**: `tools.js` lee el catálogo embebido de la pantalla y, si existe `document.modelContext`, llama a `registerTool({ name, description, inputSchema, annotations: map(...), execute: input => fetch('/api/actions/'+name, ...) })` con un `AbortController` por pantalla; registra además las tres acciones de UI; aplica `ui` tras cada resultado y traduce `confirmation_required` a la UI de confirmación de Lego.
4. **Tool MCP remota**: `tools/list` serializa los mismos descriptores (orden determinista, `ttlMs`), `tools/call` llama al mismo endpoint interno; `confirmation_required` se mapea a `input_required` (MRTR) [43]; `ui` se ignora.
5. **Comando CLI**: `php lego run flowers_mark_sold --batch_id=12 --as=sergio` resuelve el usuario, autoriza y ejecuta la misma función; útil para scripts, tests y el futuro "parity audit".

### Registro central y cero registro manual (P1)

- Un `ActionRegistry` escanea las carpetas de módulos (`app/modules/*/*Module.php`) con Reflection, genera las acciones CRUD a partir del modelo y recoge los métodos con `#[Action]`. Se cachea en producción (un array PHP serializado; sin infraestructura extra). Precedentes: `setDiscovery()` de mcp/sdk y la autoconfiguración de Symfony [52][96].
- Un módulo nuevo = una carpeta con modelo y clase de módulo; sus acciones aparecen en el catálogo sin tocar nada más. No existe un segundo sitio donde registrar. Las tres acciones de UI pertenecen al framework, no a los módulos.
- El registro es serializable 1:1 a `Tool` de MCP y a `ModelContextTool` de WebMCP; los adaptadores son conversiones puras.
- Documentación por módulo en español (P4): el descriptor JSON de sus acciones se puede volcar a `doc/` del módulo con un comando, para que humanos e IA lean lo mismo.

### Qué construir ahora (lo mínimo)

1. Clase base `Action` + atributos `#[Action]` y `#[Param]`.
2. `ActionRegistry` con descubrimiento por Reflection, caché y exportación del `inputSchema` desde tipos y modelo.
3. Endpoints `GET /api/actions`, `GET /api/actions/{name}`, `POST /api/actions/{name}` (sesión + CSRF) con validación por tipos y reglas del modelo, la puerta de confirmación en dos fases y el bloque `ui` en las respuestas.
4. Tabla `action_log` (append-only) con columnas `via` y estado de confirmación (`pending` / `done`), que es también donde se consume el token de un solo uso.
5. Cabecera `Permissions-Policy: tools=()` y CSP estricta por defecto en la capa HTTP.
6. Que la clase `Screen` embeba el catálogo de su pantalla y los marcadores estables, y que los componentes de UI (botones de tabla, formularios, filtros) consuman ese catálogo en lugar de endpoints ad hoc. Esto es lo que hace real la paridad.

### Qué NO construir ahora

- El adaptador WebMCP (`tools.js` con `registerTool`) y las tres acciones de UI: la API sigue cambiando y no hay consumidor estable en el navegador del dueño. Sí se deja el catálogo embebido y los eventos de ciclo de vida, que son los enganches.
- Un servidor MCP (ni con `mcp/sdk`, que está en 0.x experimental [52]), ni OAuth 2.1 / RFC 9728.
- El panel de chat con LLM, la elección de proveedor y la Prompt API local (requisitos de hardware altos, tool calling en flag [57][58]).
- Atributos declarativos `toolname`/`tooldescription` en formularios: no están en la spec y OpenAI no los soporta [3][36]. Si algún día se emiten, es un atributo extra que no rompe nada.
- Tools en service workers, continuations, exposición cross-origin.
- Tabla de idempotencia (`Idempotency-Key`): el draft IETF sigue sin ser RFC [78]; basta con que las acciones de update/delete sean idempotentes por diseño y reservar el hueco.
- Modo de catálogo buscable (search/describe/execute): solo cuando haya decenas de módulos.

### Secuencia para cuando llegue el momento de integrar la IA

1. **Decidir el host del agente** (ver decisión 7): asistente propio in-page que llama a un LLM vía un endpoint PHP (la clave en el servidor), o agente del navegador vía WebMCP. Revisar antes el estado de WebMCP tras TPAC 2026 (26–30 oct, F2F del CG el 27) [31] y la posición refrescada de Mozilla [30].
2. **Escribir `tools.js`** (un solo archivo ES, desechable): lee el catálogo embebido de cada pantalla, registra por pantalla con `AbortController` en los eventos de ciclo de vida, registra las tres acciones de UI, mapea anotaciones, traduce `confirmation_required` al diálogo de Lego y aplica el bloque `ui` de cada resultado (la spec exige estado compartido humano/agente [22]). Con feature-detect de `document.modelContext`; si no existe, sirve el mismo catálogo al asistente propio.
3. **Endpoint `POST /api/ai/chat`** en PHP que hace el bucle de tool calling contra el mismo catálogo (formato `name/description/input_schema` idéntico para Anthropic [76] y OpenAI [74]), con los límites de tasa y presupuestos de la regla 8.
4. **Evals mínimos por módulo**: tarea en lenguaje natural → tool esperada; usar el panel WebMCP de DevTools [25], la extensión de ejemplo de Chrome [40], WebMCP Explorer de Edge [34], `page.webmcp.tools()` / `callTool()` de Playwright [54] o Cloudflare Browser Run [120] para recorrer crear/editar/borrar/navegar/filtrar por cada vía.
5. **Set de pruebas de inyección**: descripciones envenenadas y salidas con instrucciones (OWASP LLM01 "adversarial testing" [84]) como test del framework.
6. **Opcional, si hay clientes externos** (Claude Desktop, Cursor): endpoint `POST /mcp` Streamable HTTP 2026-07-28 sobre el mismo registro, con tokens por usuario acotados a sus permisos (patrón Strapi/Guava/Payload [100][95][102]) y, si hace falta, OAuth 2.1 con un authorization server externo [45].

## Decisiones que debe tomar el dueño

1. **Modelo de permisos de Lego 2 (prerrequisito).** ¿Por módulo y operación, por acción custom, por fila? Recomendación: **por módulo y operación con nombres por convención** (`<modulo>.<view|create|update|delete>`), acciones custom con permiso explícito en el atributo, y una única función `authorize(User, Action, array $input, ?Model $record)` que ya reciba el registro para que los permisos por fila puedan añadirse después sin tocar la firma. Toda la propuesta "Action" depende de esto; WordPress pasa `$args` al `permission_callback` y Guava exige `view` sobre el registro concreto [86][95].
2. **Nombre del concepto y convención de nombres.** Recomendación: **`Action`** (término de Laravel Actions, Builder.io y Shopify; describe lo que la persona hace y deja "tool" para la vista del agente y "ability" para WordPress) y **`snake_case`** `<modulo>_<verbo>` ≤ 30 caracteres, que es lo que usan Chrome, OpenAI y los despliegues reales [14][19]; distinguir ejecutar (`flowers_create`) de iniciar un flujo en pantalla (`ui_open_record`, `flowers_open_form`) [22]. La spec aún no fija convención; podría cambiar.
3. **¿El CRUD derivado del modelo se expone a agentes automáticamente o solo con opt-in?** Recomendación: el CRUD **siempre está en el catálogo** (lo necesitan la UI y la API, P1/P2), pero la **superficie de agente** se activa con un toggle por app y, dentro de ella, las acciones de solo lectura y las tres de UI van por defecto y las escrituras exigen `agent: true` en el atributo de módulo. Una línea consciente por módulo, sin lista manual. Precedentes divididos (Strapi/Directus generan todo; WordPress/Payload opt-in).
4. **Vocabulario de anotaciones: propio o literal de MCP.** Recomendación: **propio y mínimo** (`readOnly`, `destructive`, `idempotent`, `confirm`, `untrustedContent`, `external`) con mapeo en el adaptador. WebMCP y MCP ya divergen entre sí (`consequentialHint` vs `destructiveHint`) y WebMCP sigue renombrando [12].
5. **¿La confirmación de acciones destructivas vive en el cliente o en el servidor?** Recomendación: **servidor, en dos fases con token de un solo uso cuyo consumo se registra en `action_log`**. Las anotaciones no obligan a nada [47][1], Guava y Symfony confirman que la confirmación de UI no se traslada a MCP [95][97], y un comentario en el issue #165 reporta que solo la restricción del motor impidió la acción [11]. Coste: un endpoint que responde `confirmation_required` y una fila `pending` que ya se quería para auditoría; no hace falta otro almacén.
6. **Granularidad y cómo llega el catálogo a la pantalla.** Recomendación: **una tool por acción**, exponiendo solo las del módulo activo (5–10 tools, bajo el límite de ~20 que recomienda OpenAI [74]), con el catálogo de cada pantalla **embebido como JSON en el HTML que devuelve el componente** (sin fetch extra; coherente con "componentes PHP que devuelven HTML" y P5), y reservar `?q=` en `GET /api/actions` para el modo buscable futuro. Ninguna fuente aporta evidencia sobre granularidad; se decide por legibilidad (P4).
7. **¿Dónde correrá la IA: asistente propio de Lego (LLM vía PHP) o agente del navegador (WebMCP)?** Recomendación: **asistente propio primero**, construido como "author-provided agent" (figura que la spec ya contempla [3]) consumiendo el mismo catálogo; el adaptador WebMCP se añade cuando haya un consumidor real en el navegador del dueño. Hoy solo el navegador integrado de ChatGPT Desktop consume WebMCP en producción [36][110]; Claude in Chrome no lo hace y Anthropic no ha respondido a la petición [114]; Copilot en Edge no lo consume [115]; Gemini in Chrome no tiene fecha [17]; Firefox solo prototipa y Safari se opone [121][28].
8. **¿Auditoría desde el día uno?** Recomendación: **sí**, una sola tabla `action_log` compartida por humanos, CLI y agentes con columna `via` y estado de confirmación. Todos los precedentes maduros la tienen, la necesita el token de un solo uso, y añadirla después implica una segunda forma de hacer lo mismo (P2).

## Preguntas abiertas y lo que no se encontró

- **¿Sobrevivirá WebMCP en su forma actual?** WebKit se opone y propuso un CG nuevo del que no se ha encontrado rastro de que se formara [28]; Mozilla es neutral, empuja hacia RPC genérico (issue #236, sin comentarios desde 2026-09-24) y está revisando esa posición: el 2026-10-09 Google pidió al TAG esperar a "la posición refrescada de Mozilla" y a TPAC, lo que indica que la API puede cambiar para ganar su apoyo [29][13][30]; el TAG no tiene posición y falta apoyo multi-stakeholder [30]. Fecha natural de revisión: después de TPAC 2026 (26–30 oct, F2F del CG el 27) [31] y cuando Chrome fije o descarte el envío.
- **¿Entrará la API declarativa en la spec?** Hoy está fuera, Chromium la implementa laxamente, OpenAI no la soporta y Mozilla la considera irrelevante para su posición [3][20][36][29].
- **Formato canónico de argumentos y retorno de `execute`**: spec, README, Chrome y Meta difieren [3][19][38]. Lego 2 debería devolver JSON plano y dejar que el adaptador normalice.
- **Exposición por defecto al agente nativo** (`exposedTo: native-agent`, issue #179): afecta a si una app puede registrar tools solo para su asistente in-page sin exponerlas a Gemini/ChatGPT [3].
- **Confirmación dentro de una tool** (`requestUserInput`, issue #165): solo el modo "interactive" resuelto; cómo se integrará la UI de confirmación de Lego con ese modo cuando exista [11].
- **Permisos por fila**: si Lego 2 los tiene, el catálogo embebido no puede filtrarlos del todo de antemano y el preview/commit debe evaluarlos por registro (decisión 1).
- **Qué guardar en `action_log` cuando los argumentos tienen PII** (texto completo, hash o diff).
- **Inventario de acciones de FloraFresh con efectos externos no reversibles** (emails, pagos, integraciones): son las que necesitan `external: true` y confirmación por acción.
- **Cómo distinguir sesiones de agente de sesiones humanas** para límites de tasa distintos sin romper la paridad: cabecera firmada, scope en el token o solo el canal. Hay que elegir una (P2), sabiendo que WebKit y Mozilla empujan a que el sitio no pueda saberlo [28][29].
- **Idempotency-Key**: el draft IETF (07, 2025-10-15) expira en abril de 2026 y puede cambiar sintaxis o códigos [78]; verificar antes de implementar.
- **Evidencia empírica de que las affordances por estado mejoren la selección de tools por un LLM**: no la hay; las fuentes son opinión o académicas sin experimentos [80].

**Lo que no se encontró o no se pudo verificar**:

- La página de "Site tools" de OpenAI no tiene fechas; la fecha 2026-08-25 sale del post de anuncio en el foro de desarrolladores de OpenAI [110]. Las páginas de openai.com y help.openai.com (incluido el artículo de retirada de Atlas) devolvieron 403 [61][111].
- Ninguna fuente primaria que confirme que Gemini in Chrome **ya** invoca tools WebMCP; la documentación de Chrome solo documenta extensiones [18][17].
- Ninguna fuente primaria de Perplexity sobre WebMCP (centro de ayuda de Comet: 404) [119]; ninguna declaración de Opera o Dia [116][118]; ninguna respuesta de Anthropic a la petición de WebMCP en Claude in Chrome [114]; ninguna fecha para Copilot en Edge [115].
- Ningún calendario oficial del fin del origin trial de Chrome ni de la decisión de envío; solo la extensión a M162 en escritorio [26][27].
- La "posición refrescada" de Mozilla que Google menciona el 2026-10-09 no está publicada en ningún sitio localizable [29][30]; tampoco se encontró evidencia de que el CG "agent-assisted user agents" propuesto por WebKit se haya formado [28].
- Ningún framework PHP con una IA embebida oficial en su panel que ejecute acciones del usuario (ver "Precedentes").
- Las páginas de developer.salesforce.com y el ticket Trac #64591 de WordPress (`using_abilities()`) devolvieron 403; esos hechos quedan en confianza media [105][93].
- Las convenciones semánticas OpenTelemetry GenAI para logs de tools no pudieron leerse (repositorio movido).
- Quedan en confianza media, porque el revisor no los comprobó: la URL de The Decoder [109]; la fecha exacta de WordPress 7.0 [88]; la cifra de "más de 10.000 servidores MCP" [51]; y las versiones y fechas de los precedentes (Strapi 5.49, guava/filament-mcp 1.0.0, Payload CVE-2026-105806, mcp/sdk 0.8.1, Playwright 1.64).
- Uno de los seis agentes no encontró las posiciones de Mozilla y WebKit y las dio como inexistentes; los otros sí las leyeron en fuente primaria y el revisor las verificó [28][29]. Este documento usa las fuentes primarias.

## Fuentes

1. WebMCP — Draft Community Group Report (W3C WebML CG) — https://webmachinelearning.github.io/webmcp/ — fechada por build: 2026-10-09 al leerla, 2026-10-13 al verificarla
2. index.bs, fuente Bikeshed de la spec WebMCP (WebIDL) — https://raw.githubusercontent.com/webmachinelearning/webmcp/main/index.bs — 2026-10-09 (sin campo Date)
3. README / Explainer del repo webmachinelearning/webmcp — https://github.com/webmachinelearning/webmcp — 2026-10
4. implementation-status.md (WebMCP) — https://raw.githubusercontent.com/webmachinelearning/webmcp/main/implementation-status.md — 2026-10
5. declarative-api-explainer.md (WebMCP) — https://raw.githubusercontent.com/webmachinelearning/webmcp/main/declarative-api-explainer.md — 2026-10
6. continuations-explainer.md (WebMCP) — https://raw.githubusercontent.com/webmachinelearning/webmcp/main/continuations-explainer.md — 2026
7. docs/service-workers.md (WebMCP) — https://raw.githubusercontent.com/webmachinelearning/webmcp/main/docs/service-workers.md — 2025-08-28
8. security-privacy-questionnaire.md (WebMCP) — https://raw.githubusercontent.com/webmachinelearning/webmcp/main/security-privacy-questionnaire.md — 2026-10-09
9. WebMCP API Proposal original — https://webmachinelearning.github.io/webmcp/docs/proposal.html — 2025-08-13
10. Issue #173: Make tools Document-scoped instead of Window-scoped — https://github.com/webmachinelearning/webmcp/issues/173 — 2026-05-27
11. Issue #165: elicitación via requestUserInteraction / requestUserInput — https://github.com/webmachinelearning/webmcp/issues/165 — 2026-09-14
12. Issue #176: Hint for reversible or consequential actions — https://github.com/webmachinelearning/webmcp/issues/176 — 2026-09-10
13. Issue #236: Reposition feature as a generic communication tool? — https://github.com/webmachinelearning/webmcp/issues/236 — 2026-09-24 (último comentario)
14. Issue #319: datos de despliegue (580 sitios) — https://github.com/webmachinelearning/webmcp/issues/319 — 2026-09-24
15. Chrome blog: WebMCP is available for early preview — https://developer.chrome.com/blog/webmcp-epp — 2026-02-10
16. Chrome blog: Join the WebMCP origin trial — https://developer.chrome.com/blog/ai-webmcp-origin-trial — 2026-06-09
17. Chrome blog: 15 updates from Google I/O 2026 — https://developer.chrome.com/blog/chrome-at-io26 — 2026-05-19
18. Chrome docs: WebMCP overview — https://developer.chrome.com/docs/ai/webmcp — 2026-10-07
19. Chrome docs: WebMCP Imperative API — https://developer.chrome.com/docs/ai/webmcp/imperative-api — 2026-09-21
20. Chrome docs: WebMCP Declarative API — https://developer.chrome.com/docs/ai/webmcp/declarative-api — 2026-09-25
21. Chrome docs: WebMCP versus MCP — https://developer.chrome.com/docs/ai/webmcp/compare-mcp — 2026-05-19
22. Chrome docs: WebMCP best practices — https://developer.chrome.com/docs/ai/webmcp/best-practices — 2026-05-18
23. Chrome docs: WebMCP tool security — https://developer.chrome.com/docs/ai/webmcp/secure-tools — 2026-09-01
24. Chrome docs: Agent security considerations — https://developer.chrome.com/docs/agents/security — 2026-06-09
25. What's new in DevTools (Chrome 149) — https://developer.chrome.com/blog/new-in-devtools-149 — 2026-06-02
26. Chrome Status feature 5117755740913664 (WebMCP; verificado vía API JSON https://chromestatus.com/api/v0/features?q=WebMCP) — https://chromestatus.com/feature/5117755740913664 — 2026-09-28
27. blink-dev: Intent to Experiment: WebMCP — https://groups.google.com/a/chromium.org/g/blink-dev/c/gmYffo5WOE8/m/OJxuQRP3AAAJ — 2026-05-18
28. WebKit standards-positions #670 — WebMCP (oppose) — https://github.com/WebKit/standards-positions/issues/670 — 2026-06-17
29. Mozilla standards-positions #1412 — WebMCP (neutral) — https://github.com/mozilla/standards-positions/issues/1412 — 2026-08-26 (último comentario)
30. w3ctag/design-reviews #1238 — Incubation: WebMCP — https://github.com/w3ctag/design-reviews/issues/1238 — 2026-10-09
31. W3C WebML CG minutes — https://www.w3.org/2026/08/20-webmachinelearning-minutes.html — 2026-08-20
32. W3C WebML CG minutes — https://www.w3.org/2026/01/22-webmachinelearning-minutes.html — 2026-01-22
33. Microsoft Edge blog: make your site agent-ready — https://blogs.windows.com/msedgedev/2026/09/21/new-in-edge-for-developers-create-better-components-and-make-your-site-agent-ready/ — 2026-09-21
34. MicrosoftEdge/webmcp-labs (README: flag en Canary/Dev, extensión WebMCP Explorer) — https://github.com/MicrosoftEdge/webmcp-labs — 2026
35. Edge origin trial: WebMCP — https://developer.microsoft.com/en-us/microsoft-edge/origin-trials/trials/0b76fe60-b266-458e-a285-04e375c0c31a — consultado 2026-10-13 (expira 2027-03-30; no indica rango de versiones)
36. OpenAI docs: Site tools (WebMCP) — https://learn.chatgpt.com/docs/webmcp — sin fecha en la página
37. Brave issue 55232: WebMCP en Leo — https://github.com/brave/brave-browser/issues/55232 — 2026-10-07
38. Meta Wearables: WebMCP for Web Apps — https://wearables.developer.meta.com/docs/develop/webapps/agent-tools — 2026
39. GoogleChromeLabs/webmcp-tools — https://github.com/GoogleChromeLabs/webmcp-tools — 2026
40. GoogleChromeLabs/webmcp-extension — https://github.com/GoogleChromeLabs/webmcp-extension — 2026-09-28
41. arXiv 2606.06387: WebMCP Tool Surface Poisoning — https://arxiv.org/abs/2606.06387 — 2026-06-04
42. MSEdgeExplainers: Web Model Context API (archivado) — https://github.com/MicrosoftEdge/MSEdgeExplainers/blob/main/WebModelContext/explainer.md — 2025-07
43. MCP spec 2026-07-28 — Key Changes — https://modelcontextprotocol.io/specification/2026-07-28/changelog — 2026-07-28
44. MCP spec 2026-07-28 — Streamable HTTP — https://modelcontextprotocol.io/specification/2026-07-28/basic/transports/streamable-http — 2026-07-28
45. MCP spec 2026-07-28 — Authorization — https://modelcontextprotocol.io/specification/2026-07-28/basic/authorization — 2026-07-28
46. MCP spec 2026-07-28 — Tools — https://modelcontextprotocol.io/specification/2026-07-28/server/tools — 2026-07-28
47. MCP spec 2025-11-25 — Tools (Security Considerations) — https://modelcontextprotocol.io/specification/2025-11-25/server/tools — 2025-11-25
48. MCP spec 2025-11-25 — Security Best Practices — https://modelcontextprotocol.io/specification/2025-11-25/basic/security_best_practices — 2025-11-25
49. MCP spec 2025-11-25 — Elicitation — https://modelcontextprotocol.io/specification/2025-11-25/client/elicitation — 2025-11-25
50. MCP spec 2025-06-18 — ToolAnnotations — https://modelcontextprotocol.io/specification/2025-06-18/schema#toolannotations — 2025-06-18
51. Anthropic: Donating MCP and establishing the Agentic AI Foundation — https://www.anthropic.com/news/donating-the-model-context-protocol-and-establishing-of-the-agentic-ai-foundation — 2025-12-09
52. modelcontextprotocol/php-sdk (mcp/sdk 0.8.1) — https://github.com/modelcontextprotocol/php-sdk — 2026-08-29
53. SEP-1865: MCP Apps — https://modelcontextprotocol.io/seps/1865-mcp-apps-interactive-user-interfaces-for-mcp — 2026-01-26
54. Playwright — class WebMCP (v1.64) — https://playwright.dev/docs/api/class-webmcp — 2026
55. MiguelsPizza/WebMCP (MCP-B) — https://github.com/MiguelsPizza/WebMCP — 2026
56. WebMCP-org/npm-packages — https://github.com/WebMCP-org/npm-packages — 2026-10-12
57. Chrome docs — Prompt API — https://developer.chrome.com/docs/ai/prompt-api — 2026-08-26
58. webmachinelearning/prompt-api explainer — https://github.com/webmachinelearning/prompt-api — 2026
59. Anthropic — Piloting Claude for Chrome — https://claude.com/blog/claude-for-chrome — 2025-08-25 (actualizaciones 2025-11-24 y 2025-12-18)
60. Google Security Blog — Architecting security for agentic browsing — https://blog.google/security/architecting-security-for-agentic/ — 2025-12-08
61. Simon Willison — OpenAI CISO on prompt injection risks for ChatGPT Atlas — https://simonwillison.net/2025/Oct/22/openai-ciso-on-atlas/ — 2025-10-22
62. Google — Gemini 3 y auto browse en Chrome — https://blog.google/products-and-platforms/products/chrome/gemini-3-auto-browse/ — 2026-01
63. microsoft/playwright-mcp — https://github.com/microsoft/playwright-mcp — 2026
64. browser-use docs — MCP Server — https://docs.browser-use.com/open-source/customize/integrations/mcp-server.md — 2026
65. ChromeDevTools/chrome-devtools-mcp — https://github.com/ChromeDevTools/chrome-devtools-mcp — 2026
66. Laravel MCP docs (12.x) — https://laravel.com/docs/12.x/mcp — 2026
67. Laravel blog — A better way to build MCP servers with Laravel — https://laravel.com/blog/a-better-way-to-build-mcp-servers-with-laravel — 2026-09-11
68. Laravel News — Laravel MCP 1.0 — https://laravel-news.com/laravel-mcp-1-0 — 2026-09-15
69. Laravel AI SDK docs (13.x) — https://laravel.com/docs/13.x/ai-sdk — 2026-09-23
70. Laravel Actions — Add validation to your controllers — https://www.laravelactions.com/2.x/add-validation-to-controllers.html — 2.x
71. BuilderIO/agent-native — https://github.com/BuilderIO/agent-native — 2026
72. Builder.io — Agent-native architecture — https://www.builder.io/blog/agent-native-architecture — 2026-05-08
73. James Phoenix — Agent-Native Architecture — https://understandingdata.com/posts/agent-native-architecture/ — 2025-01
74. OpenAI — Function calling guide — https://developers.openai.com/api/docs/guides/function-calling — 2026
75. OpenAI — GPT Actions in production — https://developers.openai.com/api/docs/actions/production — 2026
76. Anthropic — Tool use with Claude (overview) — https://platform.claude.com/docs/en/agents-and-tools/tool-use/overview — 2026
77. Anthropic Engineering — Writing effective tools for agents — https://www.anthropic.com/engineering/writing-tools-for-agents — 2025-09-11
78. IETF draft-ietf-httpapi-idempotency-key-header-07 — https://www.ietf.org/archive/id/draft-ietf-httpapi-idempotency-key-header-07.html — 2025-10-15
79. schema.org — Actions — https://schema.org/docs/actions.html — 2014-04
80. Nordic APIs — HATEOAS: The API Design Style That Was Waiting for AI — https://nordicapis.com/hateoas-the-api-design-style-that-was-waiting-for-ai/ — 2025-09-25
81. AWS Well-Architected Agentic AI Lens — AGENTSEC03-BP02 — https://docs.aws.amazon.com/wellarchitected/latest/agentic-ai-lens/agentsec03-bp02.html — 2026
82. OWASP Top 10 for Agentic Applications 2026 — https://genai.owasp.org/resource/owasp-top-10-for-agentic-applications-for-2026/ — 2025-12-09
83. OWASP LLM06:2025 Excessive Agency — https://genai.owasp.org/llmrisk/llm062025-excessive-agency/ — 2025-03-12
84. OWASP LLM01:2025 Prompt Injection — https://genai.owasp.org/llmrisk/llm01-prompt-injection/ — 2025-03-12
85. arXiv 2602.23193 — ESAA: Event Sourcing for Autonomous Agents — https://arxiv.org/abs/2602.23193 — 2026-02-26
86. WordPress — wp_register_ability() reference — https://developer.wordpress.org/reference/functions/wp_register_ability/ — 2025-12-03
87. Make WordPress Core — Abilities API in WordPress 6.9 — https://make.wordpress.org/core/2025/11/10/abilities-api-in-wordpress-6-9/ — 2025-11-10
88. Make WordPress Core — Client-Side Abilities API in WordPress 7.0 — https://make.wordpress.org/core/2026/03/24/client-side-abilities-api-in-wordpress-7-0/ — 2026-03-24
89. WordPress 6.9 "Gene" release post — https://wordpress.org/news/2025/12/gene/ — 2025-12-02
90. WordPress/mcp-adapter — creating-abilities.md — https://raw.githubusercontent.com/WordPress/mcp-adapter/trunk/docs/guides/creating-abilities.md — 2026-10-02
91. WordPress/mcp-adapter — transport-permissions.md — https://raw.githubusercontent.com/WordPress/mcp-adapter/trunk/docs/guides/transport-permissions.md — 2026-10
92. WordPress/ai PR #224 — Add WebMCP adapter experiment — https://github.com/WordPress/ai/pull/224 — 2026-02-10
93. Make WordPress Core — Proposal for merging WP AI Client into WordPress 7.0 — https://make.wordpress.org/core/2026/02/03/proposal-for-merging-wp-ai-client-into-wordpress-7-0/ — 2026-02-03
94. guava/filament-mcp en Packagist — https://packagist.org/packages/guava/filament-mcp — 2026-08-06
95. Filament MCP docs — Authorization — https://guava.cz/developers/packages/filament-mcp/1.x/security/authorization — 2026
96. Symfony AI — MCP Bundle docs — https://symfony.com/doc/current/ai/bundles/mcp-bundle.html — 2026-09
97. Symfony AI — AI Bundle docs — https://symfony.com/doc/current/ai/bundles/ai-bundle.html — 2026
98. api-platform/core CHANGELOG (MCP en 4.3.0) — https://raw.githubusercontent.com/api-platform/core/main/CHANGELOG.md — 2026-03-13
99. Directus docs — MCP installation — https://directus.com/docs/guides/ai/mcp/installation — 2025-11-07
100. Strapi docs — MCP server — https://docs.strapi.io/cms/features/strapi-mcp-server — 2026-09-03
101. Strapi blog — Extend MCP server with custom tools via plugin — https://strapi.io/blog/how-to-extend-strapi-s-mcp-server-with-a-custom-tools-via-a-plugin — 2026-06-13
102. Payload docs — MCP Plugin — https://payloadcms.com/docs/plugins/mcp — 2025-10-23
103. GitLab advisory — CVE-2026-105806 (@payloadcms/plugin-mcp) — https://advisories.gitlab.com/npm/@payloadcms/plugin-mcp/CVE-2026-105806/ — 2026
104. Shopify dev docs — Sidekick app extensions — https://shopify.dev/docs/apps/build/sidekick — 2026-06-17
105. Salesforce — Secure Your Agentforce Custom Actions — https://developer.salesforce.com/docs/platform/isvforce/guide/secure-agentforce-actions.html — 2025-07
106. Simon Willison — The lethal trifecta for AI agents — https://simonwillison.net/2025/Jun/16/the-lethal-trifecta/ — 2025-06-16
107. CaMeL: Defeating Prompt Injections by Design (arXiv 2503.18813) — https://arxiv.org/abs/2503.18813 — 2025-06-24
108. Design Patterns for Securing LLM Agents against Prompt Injections (arXiv 2506.08837) — https://arxiv.org/abs/2506.08837 — 2025-06-27
109. The Decoder — Google's WebMCP (secundaria; URL no verificada) — https://the-decoder.com/?p=32088 — 2026-02-13
110. OpenAI Developer Community — Build Agent Ready Websites with ChatGPT — https://community.openai.com/t/build-agent-ready-websites-with-chatgpt/1392588 — 2026-08-25
111. TechCrunch — OpenAI is shutting down Atlas (secundaria; el artículo oficial devolvió 403) — https://techcrunch.com/2026/07/09/openai-is-shutting-down-atlas-but-its-ai-browser-ambitions-are-still-growing/ — 2026-07-09
112. Anthropic — Claude in Chrome is generally available — https://claude.com/blog/claude-in-chrome-generally-available — 2026-08-26
113. Claude Help — Getting started with Claude in Chrome — https://support.claude.com/en/articles/12012173-getting-started-with-claude-for-chrome — 2026-08-26
114. anthropics/claude-code#76809 — WebMCP tool discovery in Claude in Chrome — https://github.com/anthropics/claude-code/issues/76809 — 2026-07-12 (último comentario 2026-09-17)
115. MicrosoftEdge/webmcp-labs#17 — Copilot drawer not consuming WebMCP tools — https://github.com/MicrosoftEdge/webmcp-labs/issues/17 — 2026-10-06
116. Opera press — MCP Connector in Opera Neon — https://press.opera.com/?p=3885 — 2026-03-31
117. Opera blog — Your AI agents can use Opera Neon free of charge — https://blogs.opera.com/news/2026/08/your-ai-agents-can-use-opera-neon-free-of-charge/ — 2026-08-14
118. Dia — Changelog — https://www.diabrowser.com/changelog — 2026-10-08
119. Perplexity — Hub blog (comprobación de ausencia) — https://www.perplexity.ai/hub/blog — consultado 2026-10-13
120. Cloudflare changelog — Browser Run supports WebMCP — https://developers.cloudflare.com/changelog/post/2026-04-15-br-webmcp/ — 2026-04-15
121. Bugzilla 2018306 — [meta] Implement a prototype WebMCP API — https://bugzilla.mozilla.org/show_bug.cgi?id=2018306 — ~2026-09 (última actualización relativa)
122. Bugzilla 2053854 — wpt sync "WebMCP: Remove navigator.modelContext" — https://bugzilla.mozilla.org/show_bug.cgi?id=2053854 — 2026-08
123. Anthropic — Cowork built-in browser — https://claude.com/blog/cowork-built-in-browser — 2026-08-26
124. webmcp.com — Ecosystem Adoption Tracker (secundaria, confianza baja) — https://webmcp.com/ecosystem-tracker — 2026-10-01
