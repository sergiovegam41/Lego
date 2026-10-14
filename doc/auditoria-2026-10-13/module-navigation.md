# Contrato de navegación de módulos abiertos

Dimensión: contrato de extremo a extremo de "módulos abiertos", desde el servidor hasta el navegador. Complementa a [frontend-js.md](frontend-js.md) sin repetir sus hallazgos; cuando uno se apoya en otro se indica.
Decisiones de referencia: B4, B10, D1, D3, D6, D7 y D8 de [../lego2/decisiones.md](../lego2/decisiones.md).

## Resumen

Hoy un módulo se abre con `fetch` del HTML de `/component/...`, se inserta en un `div#module-<id>` y los `<script>` en línea se recrean a mano. Esos scripts esperan con *polling* a `window.lego` y descargan con `fetch` cada JS de cada componente anidado, que se ejecuta con `new Function`. Los contenedores se quedan en el DOM ocultos, y eso es lo único que conserva el estado. No existe otro mecanismo de estado: el `ModuleStore` vive en memoria, no hay `pushState`, `popstate` ni `sessionStorage`, y F5 deja la pantalla en blanco. Verifiqué lo que ya se sabía y lo matizo en tres puntos:

- **El 401 no existe.** La sesión expirada se detecta olfateando el HTML del login, que llega por una redirección 302 que `fetch` sigue. La rama 401 de `ApiRouteDiscovery` es inalcanzable. Si se alcanzara, Flight seguiría a la ruta comodín y respondería 200 con "Componente no encontrado".
- **No son dos `fetch` por apertura.** Abrir "Editar" de ExampleCrud cuesta unas 21 peticiones: jerarquía, HTML, 11 JS ejecutados con `new Function` y 8 CSS. Ninguna se cachea, porque todas llevan `?v=time()` o `uniqid()`.
- **Los eventos `lego:module:*` sí están documentados, pero mal.** `reloaded` se emite en `document` y la documentación y sus dos oyentes lo escuchan en `window` o en `LegoEvents`, así que nunca se dispara. Los `detail` tampoco coinciden con la documentación, y tres eventos documentados no los emite nadie.

Hallazgos nuevos frente a `frontend-js.md`:
- Hay cuatro carreras confirmadas en `renderModule`: gana la última respuesta en llegar y no el último clic, se crean contenedores duplicados, quedan contenedores huérfanos al cerrar durante la carga y un fallo de red deja un "activo sin contenedor".
- Tras `lego:menu:updated` el menú se reconstruye sin sus *listeners* y deja de abrir módulos.
- Reutilizar la ventana de edición mete la página de login dentro del shell y deja `routeParams` y la URL obsoletos.
- Todas las URLs de assets son relativas y solo funcionan porque el shell vive en `/admin` sin barra final. Esto bloquea los enlaces directos.

Para Lego 2 propongo un núcleo de unas 250 líneas, `navigation.js`:
- La URL `/{módulo}[/new|/{id}]` es la clave de instancia.
- Un solo `openModule(url)`, idempotente y con *dedupe* de peticiones en vuelo.
- `mount(root, ctx)` con `ctx.signal` de un `AbortController` por instancia.
- Todo se busca dentro de la raíz.
- Un único `EventTarget` como bus.
- Un 401 real en `/component` sin redirección.
- `import()` de módulos ES versionados.
- Un shell que entrega ya renderizado en servidor el módulo activo, de modo que F5 y los enlaces directos no hacen ningún `fetch` extra.

## Flujo actual

### Qué viaja del servidor al cliente

**Carga de página (`GET /admin`).** `Routes/Web.php:39-44` renderiza `MainComponent` (`MainComponent.php:68-175`). Esa página trae:
- CSS globales con rutas relativas `./assets/...` y 8 `<script>` globales, cuatro de ellos sin consumidor (líneas 98-115).
- El menú renderizado desde la BD (`buildMenuFromDatabase`, 182-246). Cada ítem final es `<div class="custom-menu-section menu_item_openable" moduleId="{id}" moduleUrl="{HOST_NAME}{route}" data-menu-item-id="{id}">` (`MenuItemComponent.php:57`). La URL es absoluta, con `HOST_NAME`.
- La misma estructura del menú otra vez, como JSON, en el `arg` de `menu-component.js` (`MenuComponent.php:67-73`), que la guarda en `window.lego.menu` (`menu-component.js:10-20`).
- Un `#home-page` vacío (`MainComponent.php:135-138`). No se abre ningún módulo por defecto.
- `base-lego-framework.js?v=<?= time() ?>` (línea 165). Dentro de un *heredoc* `<?= ?>` no se interpreta, así que el *cache-buster* es un literal fijo.

**Apertura de un módulo (`GET {HOST_NAME}/component/{ruta}?{params}`).**
1. `Core/Router.php:66-72` quita `/component` y carga `Routes/Component.php`.
2. `ApiRouteDiscovery::discover()` (`Routes/Component.php:79`) registra cada `#[ApiComponent]`. El *handler* comprueba sesión y hace `new $className([])` (`ApiRouteDiscovery.php:121-130`).
3. `CoreComponent::render()` (`CoreComponent.php:356-379`) concatena, por cada componente y cada componente anidado:
   - `<link rel="stylesheet" href="components/...css?v={uniqid}">` (217-225). La ruta es relativa y no lleva barra inicial (línea 92).
   - El HTML del componente.
   - `<script>(function waitForLego(){ ... window.lego.loadModules([...rutas?v=time()]) ... setTimeout(waitForLego,10) })()</script>` (191-212).
   - `<script>... window.lego.loadModulesWithArguments({"context":{"url_servidor":null,"id_usuario_actual":null},"data":[[{"path":"...?v=time()","arg":{...}}]]})</script>` (132-184).
4. **Parámetros.** Solo la *query string* de la URL. PHP la lee de `$_GET` y el cliente la copia como `routeParams`, de solo lectura (`windows-manager.js:38`). Los datos para JS van en `arg`.
5. **Respuestas de error.** El placeholder de ruta inexistente responde 200 (`Routes/Component.php:83-115`). Una excepción produce la página de error que toque, sin contrato.

### Ciclo de vida en el cliente

| Fase | Qué ocurre | Evidencia |
|---|---|---|
| Abrir (menú) | Un *listener* por elemento, enlazado una sola vez al arrancar. Si `id` no es el activo, llama a `openModule(id, url, name, {url,name})` | `windows-manager.js:317-343`, `base-lego-framework.js:42` |
| Abrir (programático) | Hay cuatro entradas: `window.openModule`, `legoWindowManager.openModuleWithMenu` (1 GET de jerarquía y luego `openModule`), `lego.openModule` (`_openModule`, sin origen) y `legoWindowManager.openModule`, que no existe | `windows-manager.js:289-294, 346-356, 997-1052`; `header-component.js:839,939,959` |
| Registrar | `ModuleStore._openModule` es síncrono: crea `{component, params:{}, routeParams, sourceModuleId}` solo si no existe, marca el activo, sincroniza menú y breadcrumb y emite `lego:module:activated` en `window` antes de tener HTML | `windows-manager.js:29-61` |
| Cargar | `renderModule`: si no existe `#module-{id}`, hace `fetch(url)` y `text()`, olfatea el login, `insertAdjacentHTML`, recrea cada `<script>`, lo añade a `#home-page` y lo marca `active`. Sin `response.ok`, sin `AbortController` y sin *dedupe* | `windows-manager.js:214-273` |
| Ejecutar JS | El script en línea consulta `window.lego` cada 10 ms. `loadModules` hace `fetch` de cada ruta y la ejecuta con `new Function` dentro de un IIFE con `var arg`. Cada componente busca su contenedor como `#module-${moduleStore.getActiveModule()}` en el momento de ejecutarse | `CoreComponent.php:171-183`, `loads-scripts.js:73-157`, `example-edit.js:306-312` |
| Activar existente | Mueve la clase `active` entre `.module-container` (CSS `display:none/block`) y emite `activated`. Sin `fetch` | `windows-manager.js:42-60, 271-272`; `windows-manager.css:52-64` |
| Estado por módulo | DOM vivo y oculto, más `params` en memoria (`setParam/getParam`). Nada sobrevive a F5 | `windows-manager.js:136-189, 537-644` |
| Recargar | `reloadActive` borra el contenedor y la entrada del store, vuelve a abrir, restaura `params` y emite `lego:module:reloaded` en `document` tras `setTimeout(100)`. Se pierde el formulario a medio escribir | `windows-manager.js:383-438` |
| Cerrar | `legoWindowManager.closeModule` quita el DOM, `ModuleStore.closeModule` elige siguiente (`returnTo` › `sourceModuleId` › primera clave), quita el ítem dinámico y emite `lego:module:closed`. No avisa de la activación del siguiente ni limpia *listeners*, rejillas ni registros globales | `windows-manager.js:63-116, 446-477` |
| Cerrar y refrescar | `closeCurrentWindow({refresh:true})` cierra y tras `setTimeout(50)` hace `reloadActive()` del origen, que pierde su scroll y su estado | `windows-manager.js:495-516` |
| Sesión expirada | `isAutenticated()` responde 302 a `/login` y `exit` (`AdminMiddlewares.php:16`, `LegoHelpers.php:11-12`). `fetch` sigue la redirección y descarga el login completo. El cliente lo detecta por la ruta o porque el HTML contiene `login-form` y hace `location.href='/login'` sin `next` | `windows-manager.js:227-240` |
| F5 / atrás | Sin `pushState` ni `popstate`. F5 vuelve a `/admin` vacío y "atrás" sale de la aplicación. Las claves `ACTIVE_MODULES` y `LAST_ROUTE` están declaradas y no se usan | `grep` → 0; `storage-manager.js:18,27-28` |

### Coste de una apertura (ExampleCrud → Editar)

| Petición | Número | Origen |
|---|---|---|
| `/api/menu/item-hierarchy` | 1 | `windows-manager.js:1007` |
| HTML `/component/example-crud/edit?id=N` | 1 | `windows-manager.js:220` |
| JS (`fetch` + `new Function`) | 11: `screen.js`, `example-edit.js`, `input-text.js` ×3, `textarea.js`, `select` ×4, `FilePondComponent.js` | `ExampleEditComponent.php:46-49,111-149`; `SelectComponent.php:34-39` |
| CSS `<link>` | 8, cada uno con `uniqid` nuevo | `CoreComponent.php:217-224` |

`input-text.js` se descarga y se ejecuta tres veces porque cada instancia de componente emite su propio bloque.

## Hallazgos confirmados

Los principios aplicados se deducen del uso que hacen de ellos las demás secciones de la auditoría: P1 mínimo de archivos, P2 una sola forma o fuente, P3 explícito y sin magia, P4 legible y local, P5 mínimo y sin código muerto, P6 lo que existe funciona y está probado.

| Severidad | Archivo:línea | Título | Recomendación | Principio |
|---|---|---|---|---|
| high | assets/js/core/modules/windows-manager/windows-manager.js:271 | La vista la gana la última respuesta en llegar, no el último clic. Si se abre A (lenta) y luego B (rápida), al llegar A, `renderModule` la marca `active` mientras el store y el menú dicen B | Activar solo si la instancia sigue siendo el destino de la última navegación (token de navegación). La activación es una función aparte de la carga | P6 |
| high | assets/js/core/modules/windows-manager/windows-manager.js:215 | No hay *dedupe* de peticiones en vuelo. Dos `openModule` del mismo id antes de que llegue el `fetch` crean dos `#module-{id}` y ejecutan los scripts dos veces. Solo el clic del menú se protege (328); `openModuleWithMenu` (1044, 1049) y los botones de `example-crud.js:271,328` no | `Map<key, Promise>` de cargas en curso. `openModule` devuelve la misma promesa | P6 |
| high | assets/js/core/modules/windows-manager/windows-manager.js:220 | Un fallo de red deja el módulo "activo" en el store sin contenedor (`_openModule` es síncrono y va antes del `fetch`, 350-351). Volver a pulsarlo no hace nada (rama `else` de 330-337) hasta cerrarlo. Un 500 se inserta como contenido porque no se mira `response.ok` | Registrar la instancia como `loading`; si falla, estado `error` con reintento dentro de la raíz. La promesa se rechaza y el llamador hace `await` | P6 |
| high | assets/js/core/modules/windows-manager/windows-manager.js:453 | Cerrar durante la carga deja un huérfano. `closeModule` no encuentra contenedor y borra la entrada del store; al llegar el `fetch`, `renderModule` (268-272) añade y activa un contenedor que el store no conoce | `AbortController` por instancia: `close()` hace `abort()` y la carga abortada no toca el DOM | P6 |
| high | Core/Services/ApiRouteDiscovery.php:122 | El contrato 401 no existe. `isAutenticated()` redirige y hace `exit` (`AdminMiddlewares.php:16`, `LegoHelpers.php:11-12`), así que la rama 401 es inalcanzable. Si se alcanzara, el *handler* devuelve un string *truthy* y Flight pasa a la siguiente ruta (`vendor/mikecao/flight/flight/Engine.php:604-631`): el comodín `Routes/Component.php:83-115` respondería 200 con "Componente no encontrado" | Middleware de `/component` que responde `401` sin `Location` ni cuerpo de login. Ningún *handler* devuelve valores a Flight. Test de feature | P6, P3 |
| high | components/App/ExampleCrud/example-crud.js:308 | Al reutilizar la ventana de edición para otro registro, se hace `fetch` y `innerHTML` sin `response.ok` ni detección de login: con la sesión expirada mete la página de login completa, con sus scripts, dentro del shell. Además descarta cambios sin preguntar y no actualiza `component.url` ni `routeParams` (`ModuleStore` solo los fija al crear, `windows-manager.js:32-41`), así que `reloadActive` (415) recarga el registro anterior. Lo mismo en `tools-crud.js:150` | Una instancia por registro con clave `/{módulo}/{id}` (D7). Nunca se reemplaza el HTML de una instancia por el de otro registro. Un solo `fetchFragment()` del núcleo | P2, P6 |
| high | components/Core/Home/Components/MenuComponent/menu-component.js:1563 | `MenuReloadManager.renderMenu` hace `innerHTML=''` y vuelve a pintar el menú, pero los *listeners* de apertura se enlazaron por elemento una sola vez (`windows-manager.js:317-343`, `base-lego-framework.js:42`). Tras guardar el menú, ningún ítem abre su módulo y los ítems dinámicos de módulos abiertos desaparecen. Cada guardado provoca además 3 recargas: `menu-config.js:892-897` emite por `LegoEvents` y por `window`, y `lego-events.js:114` vuelve a emitir en `window` | Un solo *listener* delegado en `document` para `a[href]` del shell. El menú se renderiza solo en servidor a partir del código (D1) y nunca se reconstruye en JS | P2, P6 |
| high | Core/Components/CoreComponent/CoreComponent.php:92 | Todas las URLs de assets son relativas: `components/...` sin barra y `./assets/...` en `MainComponent.php:77-115`. Solo resuelven porque el shell está en `/admin` sin barra final (redirección de `Web.php:67-69`). En `/admin/`, que `Web.php:39` también acepta, o con `pushState` a `/admin/clientes/5`, todo JS y CSS de módulo resuelve a rutas inexistentes. Es un bloqueo directo para los enlaces directos | Toda URL emitida es absoluta respecto a la raíz (`/assets/...?v=mtime`), generada por el recolector de assets. Test que lo verifica en cada fragmento | P6, P3 |
| medium | components/shared/Essentials/TableComponent/table.js:536 | Cerrar no limpia nada. Cada tabla añade un *listener* `resize` en `window` que retiene `event.api`, deja `LEGO_TABLES[id]` y `window.legoTable_*_api` (655-663) y nunca destruye la rejilla. Como los scripts se ejecutan de nuevo en cada apertura (`?v=time()`, `CoreComponent.php:121-124,138`), abrir y cerrar N veces deja N rejillas vivas. `auth-groups-config.js:185` suma un *listener* por apertura y `example-crud.js:202-205` tuvo que inventar una bandera en `window` | `mount(root, ctx)`: todo *listener* con `{signal: ctx.signal}` y una limpieza que devuelve `mount`. Al cerrar, `abort()` y limpieza. Los módulos ES se evalúan una sola vez por URL | P4, P6 |
| medium | components/App/ExampleCrud/childs/ExampleEdit/example-edit.js:76 | Ids duplicados entre crear y editar: `example-name` está en `ExampleCreateComponent.php:63` y en `ExampleEditComponent.php:112`. Los errores de validación se colocan con `document.getElementById`, es decir, en el primer formulario abierto, y se limpian en todo el documento (70-72). Complementa el hallazgo de `select.js` en frontend-js.md | Ids generados por instancia desde PHP o ningún id: los campos se encuentran con `root.querySelector('[name=…]')`. Test de navegador sin ids repetidos | P4 |
| medium | components/App/ExampleCrud/childs/ExampleEdit/example-edit.js:306 | Cada script localiza su contenedor como "el módulo activo cuando me ejecuto". Se ejecuta tras un *polling* y un `fetch` asíncrono (`CoreComponent.php:174-181`, `loads-scripts.js:118-157`), así que si el usuario cambia de módulo mientras carga, el script se enlaza al contenedor equivocado | El cargador pasa la raíz: `mount(root, ctx)`. Ningún módulo consulta el estado global para saber dónde está | P4 |
| medium | assets/js/core/modules/windows-manager/windows-manager.js:435 | Contrato de eventos roto. `reloaded` se emite en `document`, pero `doc/frontend/window-manager.md:121`, `doc/frontend/eventos.md:53`, `auth-groups-config.js:185` (`window`) y `menu-config.js:1725` (`LegoEvents`) lo esperan en otro sitio, así que nunca llega. Los `detail` reales (`params`, `activeModuleId`) no son los documentados. `lego:auth:logout`, `lego:theme:changed` y `lego:data:updated` están documentados y nadie los emite. Al cerrar el activo no se emite `activated` para el siguiente | Un único `EventTarget` exportado con nombres en una constante congelada. Test de arquitectura que exige que cada nombre emitido tenga oyente y viceversa | P2, P3 |
| medium | assets/js/core/modules/windows-manager/windows-manager.js:229 | La sesión expirada solo se detecta en `renderModule`, y por olfateo: la ruta es `/login` o `/` (este caso nunca ocurre, porque `/` redirige), o el HTML contiene `id="login-form"` o `login-container` (falso positivo si un módulo contiene ese texto). Se descarga la página de login entera, se redirige sin `next` y las demás peticiones (`.json()` sobre el HTML del login) fallan sin avisar | 401 real en `/component` y `/api`. Un único `fetchFragment` y un único `api()` que ante un 401 hacen `location.assign('/login?next=' + url actual)` | P2, P6 |
| medium | assets/js/core/modules/windows-manager/windows-manager.js:510 | "Volver al origen y refrescar" recarga el módulo origen entero tras `setTimeout(50)`: pierde scroll, selección y filtros no guardados, en contra de B4. La cabecera ofrece además "Recargar" (`HeaderComponent.php:54-60`) sin confirmación, que descarta formularios a medio escribir | Evento `lego:data-changed {resource, id}`: la lista recarga solo el fragmento de su tabla con su *query string*. Recargar una instancia `dirty` pide confirmación con `<dialog>` | P4, P6 |
| medium | App/Controllers/Menu/Controllers/MenuItemHierarchyController.php:63 | Cada apertura dinámica consulta la BD para saber el padre del menú, con un bucle N+1 por ancestro (63-68), en una ruta sin sesión (`Routes/Api.php:101-103`) y con el trabajo hecho en el constructor (25-28). `ScreenTrait::getScreenMetadata` hace lo mismo en servidor (48) | Con D1 el padre es estático: sale del atributo `#[Module]`/`#[Screen]`. Se borra el endpoint | P2, P5 |
| medium | Core/Components/CoreComponent/CoreComponent.php:222 | El CSS de cada módulo vive dentro de su contenedor con `?v=uniqid`: se descarga en cada apertura, produce un destello sin estilo, desaparece al cerrar y, mientras está abierto, se aplica a todos los módulos | El cliente sube a `<head>` cada `link[data-lego-css]` una sola vez, deduplicado por `href` y versionado por `mtime`. Lo seguro lo da el prefijo de módulo de D15 | P5, P6 |
| medium | App/Controllers/ComponentsController.php:93 | `/api/components/render` y `batch` renderizan cualquier componente registrado con parámetros arbitrarios, sin sesión (`Routes/Api.php:211-223`), y devuelven el mensaje de la excepción (93, 183). No tienen consumidor (ver frontend-js.md) | Borrar. El único canal de HTML hacia el cliente es `/component/{ruta}` con política de acceso | P5, P6 |
| low | assets/js/core/modules/windows-manager/windows-manager.js:83 | Al cerrar el activo sin origen se salta a la primera clave por orden de inserción, no al módulo usado más recientemente | Pila de instancias por uso más reciente. Al cerrar se activa la anterior y se hace `replaceState` a su URL | P4 |
| low | assets/js/core/modules/windows-manager/windows-manager.js:300 | `updateMenu()` busca `.menu-item` y `data-module-id`, que ningún marcado estático tiene: es código muerto. `header-component.js:833` llama a un `updateMenu` fuera de alcance y `:839,939,959` a un `legoWindowManager.openModule` que no existe | Borrar. Una sola función de apertura | P5 |
| low | assets/js/core/modules/windows-manager/windows-manager.js:322 | Pulsar la X de un ítem del menú dispara primero el *listener* del ítem (que activa el módulo y emite `activated`) y después el cierre delegado (`menu-component.js:515-531`) | Un solo *listener* delegado que distingue `[data-action=close]` antes de navegar | P4 |
| low | assets/js/core/modules/windows-manager/windows-manager.js:210 | `stopMenuItemPulse` quita `is-loading` de todos los contenedores: con dos cargas a la vez, la primera en terminar apaga el indicador de la otra | Estado `data-state=loading` por raíz de instancia | P4 |
| low | components/Core/Home/Components/MainComponent/MainComponent.php:165 | `?v=<?= time() ?>` dentro de un *heredoc* sale literal: la URL del punto de entrada nunca cambia y el navegador puede servir una versión vieja | Versión por `filemtime` desde el recolector de assets | P6 |
| low | components/Core/Home/Components/MainComponent/MainComponent.php:236 | Las URLs de módulo se construyen con `HOST_NAME` absoluto (también en `MenuItemHierarchyController.php:143`). Si no coincide con el origen real (`localhost` frente a `127.0.0.1`, o un proxy de Dokploy), el `fetch` pasa a ser de otro origen y no envía la cookie | Rutas relativas a la raíz y sin host. D16 exige que la app no conozca su dominio | P6 |

## Diseño propuesto para Lego 2

### Principios del contrato

1. **La URL identifica la instancia.** La clave de instancia es el `pathname`, y la *query string* es estado de esa instancia (orden, filtro y página de la tabla, D6). Así, F5 restaura el módulo activo con sus filtros sin `sessionStorage` (D8).
2. **El mismo resolutor para página y fragmento.** `/{ruta}` y `/component/{ruta}` usan el mismo resolutor de rutas a pantallas, que sale del escaneo de atributos. Es imposible que diverjan.
3. **El servidor emite HTML más datos inertes y el cliente monta.** No hay `<script>` ejecutable en línea, ni `new Function`, ni *polling*.
4. **Toda API de JS recibe la raíz de su instancia.** Ningún módulo toca `document` ni `window`.

### Contrato de URL

| Ruta del shell | Fragmento | Clave de instancia | Pantalla |
|---|---|---|---|
| `/{module}` | `/component/{module}` | `{module}` | Lista (`#[Resource]`) o pantalla por defecto del módulo |
| `/{module}?q=&sort=-name&page=2&f[status]=active` | `/component/{module}?…` | `{module}` | Misma instancia; la *query* es su estado |
| `/{module}/new` | `/component/{module}/new` | `{module}/new` | Formulario genérico, modo crear (D7) |
| `/{module}/{id}` | `/component/{module}/{id}` | `{module}/{id}` | Formulario genérico, modo editar (D7); varias a la vez |
| `/{module}/{screen}` | `/component/{module}/{screen}` | `{module}/{screen}` | Pantalla propia (escape explícito) |

Reglas:
- **Resolución.** Primero las pantallas declaradas, después `new` y después `{id}`. Un test de arquitectura impide nombres de pantalla que parezcan ids y módulos que choquen con los prefijos reservados (`api`, `component`, `assets`, `login`, `logout`).
- **Origen de las rutas.** Coinciden con las rutas `/<módulo>` y `/<módulo>/<id>` que la investigación WebMCP propone para `ui_navigate` y `ui_open_record`.
- **Historial.** Cambiar de instancia hace `pushState({key})`. Cambiar la *query* dentro de la misma instancia hace `replaceState`, para no llenar el historial con cada tecla del filtro. `popstate` llama a `openModule(location.href, {history: false})`.
- **Cerrar.** Al cerrar se activa la anterior según el uso más reciente con `replaceState` a su URL. "Atrás" hacia una instancia cerrada la vuelve a abrir con `fetch`, porque la URL manda.
- **F5 o enlace directo.** El servidor renderiza el shell con la instancia activa ya dentro. Las demás se pierden (D8).

### Qué embebe el HTML del servidor

**Página `GET /{ruta}`** (con sesión; sin sesión, 302 a `/login?next=`):
- `<script type="importmap">` con `{"imports": {"lego": "/core/js/lego.js?v=<mtime>", "lego/": "/core/js/"}}`, para que los módulos hagan `import { api } from 'lego'` con versión y sin build (B10).
- El menú derivado del código (D1) como enlaces reales `<a href="/clients" data-lego-nav>`. Sin JS funcionan, y el clic central abre una pestaña nueva.
- `<main data-lego-outlet>` con la instancia activa renderizada en servidor (marcada `data-ssr`).
- Un solo `<script type="module" src="/core/js/lego.js?v=…">`.

**Fragmento `GET /component/{ruta}`** (sin `<html>`):

```html
<link rel="stylesheet" href="/modules/Clients/clients.css?v=1697190000" data-lego-css>
<section data-lego-module="clients" data-lego-key="clients/5" data-lego-record="5"
         data-lego-url="/clients/5" data-lego-title="Cliente 5"
         data-lego-js="/modules/Clients/clients.js?v=1697190000">
  <script type="application/json" data-lego-props>{"mode":"edit","id":5,"actions":[…]}</script>
  … HTML de la pantalla, con [data-lego="table"|"form"|"dialog"] para los comportamientos del núcleo …
</section>
```

- `data-lego-js` es opcional y existe solo si hay escape explícito. Los comportamientos del núcleo (`table`, `form`) se montan por atributo, así que un módulo CRUD no escribe JS (P1).
- `data-lego-props` es JSON inerte. Incluye el catálogo de acciones permitidas para ese usuario (plan, fase 3), con las marcas `data-lego-module` y `data-lego-record` que propone la investigación WebMCP.
- Los ids de HTML, si existen, se generan por instancia en PHP (`$this->domId('name')` → `lg-clients-5-name`).
- Códigos de estado:

| Código | Cuándo | Respuesta |
|---|---|---|
| 200 | Correcto | Fragmento |
| 401 | Sin sesión | Sin redirección ni cuerpo |
| 403 | Sin permiso `{módulo}.view` o `.update` | Fragmento de error |
| 404 | Pantalla o registro inexistente | Fragmento de error, nunca 200 |
| 500 | Error del servidor | Fragmento genérico, sin traza fuera de desarrollo |

- Cabecera `Cache-Control: no-store`.

### Ciclo de vida

```
openModule(url)
  key = keyFromUrl(url); token = ++navToken
  history push/replace (salvo history:false)
  inst = instances.get(key) ?? load(key, url)        // load se deduplica con pending.get(key)
     load: root=<section hidden data-state=loading>; ctrl=new AbortController()
           html = await fetchFragment(fragmentUrl(url), {signal: ctrl.signal})   // 401 → login?next
           si ctrl.signal.aborted → return (no toca el DOM)
           root.replaceWith(fragmento); subir <link data-lego-css> a <head> sin duplicar
           mount: comportamientos [data-lego] + import(data-lego-js) → mount(root, ctx) → cleanup
           emit lego:screen-mounted {key}
  await inst.ready
  si token !== navToken → return                        // carrera: ganó otra navegación
  activate(key): hidden=true en las demás, hidden=false en esta; document.title; aria-current en el menú; foco al h1; MRU
  emit lego:screen-activated {key, url}

closeModule(key)
  si inst.dirty → confirmar con <dialog>
  ctrl.abort()  → cancela fetch en curso y quita todo listener registrado con {signal}
  cleanup?.(); root.remove(); instances.delete(key); emit lego:screen-unmounted {key}
  si era la activa → openModule(MRU anterior || opener || módulo por defecto, {replace: true})

reloadModule(key) = unmount + load(key, inst.url) en la misma posición; la query actual se conserva
setQuery(key, q)  = replaceState + fetch del fragmento de la tabla (D6), no de toda la pantalla
```

- **Una sola instancia de rejilla o formulario por raíz.** Los comportamientos guardan su estado en un `WeakMap<Element, State>` y nunca en un `Map` por id.
- **Límite de instancias.** Propongo un máximo configurable de 10. Al superarlo se cierra la instancia usada hace más tiempo que no esté `dirty`.
- **Antes de salir.** `beforeunload` solo si hay alguna instancia `dirty`.

### Manejo del 401 y de errores

- `fetchFragment` y `api` son las dos únicas funciones que llaman a `fetch` (test de arquitectura).
- Ante un 401: `location.assign('/login?next=' + encodeURIComponent(location.pathname + location.search))`. Tras el login el servidor redirige a `next` (validado como ruta local) y el shell renderiza esa instancia en servidor. El dueño vuelve exactamente donde estaba, salvo las otras instancias (D8).
- 403, 404 y 500: el HTML del error se inserta dentro de la raíz con `data-state=error` y un botón "Reintentar" que llama a `reloadModule`. La instancia no queda "activa vacía".
- Nunca se olfatea HTML.

### Bus de eventos

Un solo `export const bus = new EventTarget()` en `lego.js` y una constante `EVENTS` congelada:

| Evento | `detail` | Uso |
|---|---|---|
| `lego:screen-mounted` | `{key}` | Futuro `tools.js` |
| `lego:screen-activated` | `{key, url}` | Menú y breadcrumb |
| `lego:screen-unmounted` | `{key}` | Limpieza externa |
| `lego:data-changed` | `{resource, id, action}` | Refresco cruzado |

Los módulos se suscriben con `ctx.on()`, que añade `{signal}` de forma automática. `LegoEvents`, los `CustomEvent` en `window` y en `document`, y los `lego:module:*` desaparecen.

### Esbozo de API JS

```js
// core/js/lego.js — único punto de entrada (<script type="module">)
export const bus = new EventTarget();
export const EVENTS = Object.freeze({ MOUNTED: 'lego:screen-mounted', ACTIVATED: 'lego:screen-activated',
  UNMOUNTED: 'lego:screen-unmounted', DATA_CHANGED: 'lego:data-changed' });

/** Única forma de navegar: abre o activa la instancia de la URL. Idempotente; deduplica cargas en vuelo. */
export async function openModule(url, { replace = false, history = true, opener = activeKey() } = {}) {} // → Promise<Instance>
export function closeModule(key = activeKey(), { force = false } = {}) {}  // → Promise<boolean> (false si el usuario cancela)
export async function reloadModule(key = activeKey()) {}                  // → Promise<void>
export function activeKey() {}                                            // → string|null
export function instances() {}                                            // → ReadonlyArray<{key, url, title, dirty, state}>
export function keyFromUrl(url) {}       // '/clients/5?page=2' → 'clients/5'
export function fragmentUrl(url) {}      // '/clients/5?page=2' → '/component/clients/5?page=2'

/** Únicas funciones que hacen fetch. 401 → /login?next=…; otros errores → LegoHttpError {status, html|json}. */
export async function fetchFragment(url, { signal } = {}) {}              // → Promise<string>
export async function api(action, input = {}, { signal } = {}) {}         // POST /api/actions/{action} con CSRF

/**
 * Contrato de una pantalla con escape explícito (modules/Clients/clients.js)
 * @typedef {object} ModuleContext
 * @property {string} key            p. ej. 'clients/5'
 * @property {URL} url               URL canónica de la instancia
 * @property {object} props          JSON de [data-lego-props]
 * @property {AbortSignal} signal    se aborta al cerrar o recargar
 * @property {(url:string, o?:object)=>Promise} navigate
 * @property {(o?:object)=>Promise<boolean>} close
 * @property {(q:Record<string,string>)=>void} setQuery   replaceState de esta instancia
 * @property {(dirty:boolean)=>void} setDirty
 * @property {(ev:string, fn:(e:CustomEvent)=>void)=>void} on    bus con {signal} automático
 * @property {(ev:string, detail?:object)=>void} emit
 */
/** @param {HTMLElement} root @param {ModuleContext} ctx @returns {void | (() => void)} limpieza */
export function mount(root, ctx) {}
```

### Cómo carga cada pantalla su módulo ES

1. Tras insertar el fragmento, el núcleo recorre `root.querySelectorAll('[data-lego]')` y hace `import('lego/ui/' + name + '.js')` (tabla, formulario, diálogo). Llama a `mount(el, ctx)` con el mismo `signal` de la instancia.
2. Si la raíz tiene `data-lego-js`, hace `import(root.dataset.legoJs)` y `mount(root, ctx)`. El módulo ES se evalúa una sola vez por URL. El estado por instancia vive en el cierre de `mount`, así que crear y dos ediciones abiertas a la vez no se pisan.
3. Las URLs se versionan por `filemtime`, con `Cache-Control: max-age=31536000, immutable`. El JS del núcleo se descarga una vez por despliegue.
4. En la carga inicial, la instancia renderizada en servidor (`data-ssr`) se monta sin `fetch`, por la misma ruta de código.

### Qué desaparece

- `ModuleStore.params` y el *popover* de parámetros: el estado persistente es la *query* de la URL más el DOM vivo.
- `addDynamicMenuItem`, `MenuHiddenItemsManager` y la consulta de jerarquía. Las instancias abiertas como `clients/5` se muestran en una franja de "abiertos" derivada de `instances()`, no insertadas en el árbol del menú (D1).
- `sourceModuleId` se mantiene como idea, con el nombre `opener`.
- `closeCurrentWindow({refresh})` se sustituye por `emit(DATA_CHANGED)` seguido de `close()`.

## Tests

### Pest Feature (servidor)

1. `GET /component/clients` sin sesión → 401, sin cabecera `Location` y sin `login-form` en el cuerpo.
2. `GET /clients/5` sin sesión → 302 a `/login?next=%2Fclients%2F5`. Tras el login, redirige a `next`. Un `next` externo (`//evil.com`) se rechaza.
3. `GET /clients/5` con sesión → 200: documento con `section[data-lego-key="clients/5"][data-ssr]`, y su HTML interno es idéntico al de `GET /component/clients/5` (paridad página/fragmento).
4. `GET /component/clients/new` y `/component/clients/5` → mismo formulario con `data-mode` create y edit. Los conjuntos de `id` de ambos fragmentos no se solapan.
5. `GET /component/clients/999999` → 404 con fragmento de error. `GET /component/no-existe` → 404 (nunca 200 con placeholder).
6. Usuario sin `clients.update` → 403 en `/component/clients/5`. Sin `clients.view`, el módulo no aparece en el menú.
7. Ningún fragmento contiene `<script>` ejecutable (solo `type="application/json"`). Todo `href` y `src` empieza por `/` y lleva `?v=`.
8. La *query string* de tabla (`sort`, `page`, `q`, `f[...]`) se refleja en `data-lego-url` y en el orden real de las filas.
9. `Cache-Control: no-store` en fragmentos y `immutable` en assets versionados. El ETag responde 304.
10. Arquitectura:
    - Toda `#[Screen]` y todo `#[Resource]` resuelve por `/{ruta}` y por `/component/{ruta}`.
    - Ningún nombre de módulo choca con los prefijos reservados ni ninguna pantalla parece un id.
    - Toda ruta tiene política de acceso.
11. Arquitectura sobre el JS, con búsqueda de texto desde Pest, sin Node:
    - En `modules/**/*.js` y `core/ui/*.js` no aparecen `document.getElementById`, `document.querySelector`, asignaciones a `window.`, `new Function`, `eval`, `setTimeout(` ni `fetch(`. `fetch` solo en `lego.js`.
    - Cada nombre emitido está en `EVENTS` y cada uno de `EVENTS` tiene emisor y oyente.

### Navegador (Playwright o Pest Browser)

1. Abrir tres módulos desde el menú: la URL cambia con `pushState`, hay una petición `/component/...` por apertura y cero al volver a una ya abierta.
2. Estado conservado: escribir en el filtro de A y hacer scroll, ir a B, volver a A. El texto y el scroll siguen intactos y no hay `fetch`.
3. Atrás y adelante: `goBack()` reactiva la instancia anterior sin `fetch`. Tras cerrar una instancia, "atrás" la vuelve a abrir con un `fetch`.
4. Enlace directo: abrir `/clients/5` en una pestaña nueva. El formulario llega renderizado en servidor, sin peticiones a `/component` al cargar, y el menú marca Clientes.
5. F5 en `/clients?sort=-name&page=2` restaura solo esa instancia con su orden y su página. Las otras ya no están (D8).
6. Crear más dos ediciones a la vez: abrir `/clients/new`, `/clients/5` y `/clients/7`. Enviar el 5 con un error de validación: el error aparece solo dentro de la raíz del 5. `document.querySelectorAll('[id]')` no tiene repetidos.
7. Limpieza al cerrar: un módulo de prueba registra con `ctx.on` y `{signal}` un contador de `resize`. Tras abrir y cerrar 20 veces y disparar `resize`, el contador no cambia y `instances().length` vuelve a su valor inicial.
8. Carrera de respuestas: `page.route` retrasa A y se abre A y luego B. B queda activo y en la URL, y cuando A responde no se activa.
9. Cerrar durante la carga: con A retrasada, cerrar A antes de que llegue. La petición se aborta (`requestfailed`) y no queda ninguna raíz huérfana.
10. Doble clic en "Editar" o doble `openModule` → una sola petición y una sola raíz.
11. Sesión expirada: borrar la cookie y abrir otro módulo → 401 → `/login?next=<URL>`. Tras el login vuelve a esa URL.
12. Guardar en editar → `lego:data-changed` → la lista refresca solo su tabla y conserva la *query*. La edición se cierra y se activa la que la abrió.
13. Un 500 en el fragmento muestra el error con "Reintentar" dentro de la raíz, y el reintento hace un `fetch` nuevo.
14. Un formulario con cambios sin guardar pide confirmación con `<dialog>` al cerrar o recargar. El clic central sobre un ítem del menú abre una pestaña nueva.

## Inventario

| Ruta | Veredicto | Razón | Principio |
|---|---|---|---|
| assets/js/core/modules/windows-manager/windows-manager.js | rewrite | Las ideas valen: registro de instancias, activar por clase y no por nuevo `fetch`, origen para el retorno. La implementación tiene cuatro carreras, cuatro entradas de apertura, 900 líneas de menú dinámico y DOM global. Pasa a `core/js/navigation.js` con unas 250 líneas | P2, P6 |
| assets/js/core/modules/windows-manager/loads-scripts.js | drop | `fetch` más `new Function`. Lo sustituye `import()` | P4 |
| assets/js/core/modules/storage/storage-manager.js | rewrite | Las claves de módulos (`ACTIVE_MODULES`, `LAST_ROUTE`, `WINDOW_STATES`) y de sesión sobran: la URL es la fuente de verdad (D8) y la cookie es HttpOnly (D3). Quedan unas 20 líneas de preferencias (tema, ancho del menú) | P5 |
| assets/js/core/base-lego-framework.js | rewrite | Pasa a ser `core/js/lego.js`: punto de entrada único que exporta la API y no escribe en `window` | P4 |
| assets/css/core/windows-manager.css | rewrite | Se conserva la idea de "ocultas pero montadas", ahora con el atributo `[hidden]`, y un indicador de carga por `[data-state=loading]` en la raíz | P5 |
| components/Core/Home/Components/MainComponent/MainComponent.php | rewrite | Pasa a ser el `Document` y el shell: *importmap*, assets absolutos versionados, menú desde código e instancia activa renderizada en servidor. Salen el menú desde la BD, los servicios sin uso y FilePond por CDN | P2, P6 |
| components/Core/Home/Components/MainComponent/main-component.js | drop | Vacío y sin referencias | P5 |
| components/Core/Home/Components/MainComponent/main-component.css | drop | Vacío | P5 |
| components/Core/Home/Components/MenuComponent/MenuComponent.php | rewrite | Menú renderizado en servidor desde `#[Module]`/`#[Screen]` con `<a href>` reales y escape. Sale el JSON duplicado de la estructura | P2 |
| components/Core/Home/Components/MenuComponent/menu-component.js | rewrite | Quedan unas 80 líneas: un *listener* delegado de navegación, `aria-current`, filtro por texto y redimensionar o plegar el lateral. Se borran `MenuHiddenItemsManager`, `MenuReloadManager`, `MenuStateManager` y la búsqueda en BD | P2, P5 |
| components/Core/Home/Components/MenuComponent/menu-component.css | drop | 0 bytes | P5 |
| components/Core/Home/Components/MenuComponent/features/MenuItemComponent/MenuItemComponent.php | rewrite | Se funde en el menú como `<a>` con escape. El marcado debe existir en un solo sitio | P2 |
| components/Core/Home/Components/MenuComponent/features/MenuItemComponent/menu-item-component.js | drop | 0 bytes | P5 |
| components/Core/Home/Components/MenuComponent/features/MenuItemComponent/menu-item-component.css | drop | 0 bytes | P5 |
| components/Core/Home/Dtos/MenuItemDto.php | rewrite | Pasa a ser un `MenuEntry` de solo lectura derivado de los atributos, con `href` y sin `level` mutable (`MenuItemComponent.php:50` lo muta al renderizar) | P2 |
| components/Core/Home/Collections/MenuItemCollection.php | drop | Un `array` tipado por PHPDoc basta; `toJson` deja de hacer falta | P5 |
| components/Core/Home/Components/HeaderComponent/HeaderComponent.php | rewrite | Breadcrumb a partir de `data-lego-title` de la instancia; botones Cerrar y Recargar con `data-action` que llaman a `closeModule` y `reloadModule`. Sin *popover* de parámetros ni datos de usuario en duro | P3 |
| components/Core/Home/Components/HeaderComponent/header-component.js | rewrite | Ver frontend-js.md. En esta dimensión: sale `openSystemMenuItem` (tres rutas de apertura) y el *polling* del *badge* cada 2 s (1054) | P2, P5 |
| components/Core/Home/Components/HeaderComponent/header-component.css | rewrite | Lo cubre css-theming.md | P5 |
| components/Core/Home/HomeComponent.php | rewrite | Pasa a ser una `#[Screen]` normal (panel) y destino por defecto de `/`. Sin `#[ApiComponent]` ni constructor vacío documentado | P1 |
| components/Core/Home/home.js | drop | 1 byte; provoca un `fetch` por apertura | P5 |
| components/Core/Home/home.css | rewrite | Se conserva con el prefijo del módulo (D15) | P5 |
| Routes/Component.php | rewrite | Un solo resolutor compartido con la capa de página. Salen el comodín que responde 200 y las rutas de assets, que pasan al servidor de assets de la fase 1 | P2, P6 |
| Core/Services/ApiRouteDiscovery.php | rewrite | Se conserva la idea de escanear atributos. La rama 401 es inalcanzable, devuelve valores a Flight y hace `new $className([])` con un argumento posicional | P6 |
| App/Controllers/ComponentsController.php | drop | Sin consumidor, sin sesión y con fuga de mensajes de excepción | P5, P6 |
| App/Controllers/Menu/Controllers/MenuItemHierarchyController.php | drop | Con D1 el padre del menú es estático. Además tiene N+1, no pide sesión y trabaja en el constructor | P2, P5 |
| Core/Traits/ScreenTrait.php | drop | Lo sustituye `#[Screen(route:, label:, icon:)]`. La consulta a la BD por padre desaparece con D1. Se conserva la idea de "la clase declara su identidad" | P2, P3 |
| Core/Components/CoreComponent/CoreComponent.php | rewrite | Solo para esta dimensión: `generate_modulesJs` y `WithArg` (el *polling* en línea) y los *cache-busters* salen. El recolector de assets por página emite `link[data-lego-css]` y `data-lego-js`. Lo demás lo cubre core-components.md | P4, P6 |
| doc/frontend/window-manager.md | drop | Describe `wm.openModule`, que no existe, y eventos en el destino equivocado. La nueva documentación se escribe a partir del JSDoc de `lego.js` y un test la contrasta | P3 |
| doc/frontend/eventos.md | drop | Documenta tres eventos que nadie emite y un `detail` incorrecto | P3 |
| AI/Contracts/window-manager-api.md | drop | Contrato global de `window.legoWindowManager` que deja de existir | P3, P5 |

## Preguntas abiertas

1. **Contradicción entre el plan y D8.** `plan-lego-2.md:145` dice que "la lista de abiertos se restaura tras F5 desde `sessionStorage`", y el criterio de la fase 4 (`:153`) que se "recupera el estado". D8 dice que solo vuelve la activa. Recomiendo dejar D8 y corregir esas dos líneas: el estado que se recupera es el de la *query* de la instancia activa.
2. **Prefijo de las rutas del shell.** Las opciones son raíz (`/clients/5`, alineado con la investigación WebMCP y con prefijos reservados) o `/app/clients/5`, que evita colisiones sin lista de reservados. Recomiendo la raíz con el test de colisiones.
3. **Varias instancias de "nuevo".** ¿Se permite más de un `clients/new` abierto a la vez? Habría que usar una clave de borrador (`/clients/new?draft=2`). Recomiendo una sola por módulo en la versión 2.0.
4. **Límite de instancias abiertas.** Hay que fijar el número y la política al superarlo. Recomiendo 10, cerrando la instancia usada hace más tiempo que no esté `dirty`.
5. **Sesión expirada con un formulario a medio escribir.** La decisión vigente (redirigir) pierde los datos. Una alternativa es un `<dialog>` que avise y guarde un borrador en `sessionStorage` solo para esa instancia. ¿Se acepta esa excepción a D8?
6. **"Atrás" hacia una instancia cerrada.** Recomiendo reabrirla con `fetch`, porque la URL manda. La alternativa es saltar esa entrada del historial, que es más compleja y sorprende.
7. **Node solo para tests de navegador.** B10 prohíbe Node en el frontend. Playwright y Pest Browser lo necesitan en el mini PC y en CI. ¿Se acepta Node solo como herramienta de test, fuera de la imagen de la app?
8. **Dónde se ven las instancias que no son de menú** (`clients/5`): en una franja de pestañas en la cabecera o bajo el ítem del módulo en el lateral. Recomiendo pestañas, porque el árbol del menú queda puro (D1).
9. **¿Cambiar filtros crea entrada de historial?** Recomiendo que no (`replaceState`), tampoco para la paginación.
10. **Comportamientos del núcleo por atributo frente a importación explícita.** Montar `data-lego="table"` sin que el módulo lo importe es magia ligera (P3), pero deja los CRUD en cero JS (P1). Hay que confirmar el equilibrio.
