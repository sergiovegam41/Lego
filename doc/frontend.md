# Frontend: shell y pantallas abiertas

El frontend de Lego son módulos ES nativos, sin Node ni build (B10). El servidor renderiza HTML. El cliente monta comportamientos sobre ese HTML y navega entre pantallas sin recargar la página.

## Cómo funciona

1. **El servidor siempre renderiza la pantalla.** `GET /clients/5` devuelve la página completa con el shell y esa pantalla dentro. `GET /component/clients/5` devuelve solo la pantalla, como fragmento. Sin JavaScript todo sigue funcionando con enlaces y formularios normales.
2. **La URL nombra la pantalla activa.** La ruta es la clave de la instancia (`clients/5`) y la query es su estado (`?sort=name&page=2`). Atrás y adelante funcionan, y cualquier pantalla se puede compartir con un enlace.
3. **Las pantallas abiertas siguen montadas.** Al cambiar de pestaña se ocultan, no se destruyen, así que el scroll, los filtros y los formularios a medio escribir se conservan. Tras F5 vuelve solo la activa (D8).
4. **Los comportamientos se montan por atributo.** `data-lego="table"` carga `lego/ui/table.js` y `data-lego="form"` carga `lego/ui/form.js`. Un módulo CRUD no escribe JavaScript.

## Contrato de una pantalla

Toda pantalla tiene una raíz `<section data-lego-screen>` con estos atributos:

| Atributo | Ejemplo | Uso |
|---|---|---|
| `data-lego-key` | `clients/5` | Identifica la instancia abierta |
| `data-lego-url` | `/clients/5` | URL canónica, con su query |
| `data-lego-title` | `Florería Lima · Clientes` | Pestaña y título del documento |
| `data-lego-module` | `clients` | Módulo al que pertenece |
| `data-lego-record` | `5` | Registro que muestra, si hay uno |

Dentro, `<script type="application/json" data-lego-actions>` trae el catálogo de acciones que el usuario puede ejecutar en esa pantalla.

Los ids HTML de cada pantalla llevan su prefijo (`lg-clients-5-name`), así dos formularios abiertos nunca comparten ids.

## Archivos

| Archivo | Responsabilidad |
|---|---|
| `core/Ui/js/lego.js` | Navegación, instancias, pestañas, historial, tema, bus de eventos. Único que llama a `fetch`. |
| `core/Ui/js/ui/table.js` | Búsqueda, orden y paginación cambian la query de la misma pantalla, sin crear historial (C8). |
| `core/Ui/js/ui/form.js` | Envía por la API de acciones, muestra errores por campo, pide confirmación y marca cambios sin guardar. |
| `core/Ui/js/ui/dialog.js` | Confirmación con `<dialog>` nativo y avisos breves. |
| `core/Ui/Theme/tokens.css` | Colores, espacios y radios, tema claro y oscuro (D9). |
| `core/Ui/Shell/` | El shell y la raíz de pantalla en PHP. |

Un mapa de importación permite escribir `import { api } from 'lego'` y `import('lego/ui/table.js')` sin build.

## API de `lego.js`

```js
import { openModule, closeModule, reloadModule, api, bus, EVENTS } from 'lego';

await openModule('/clients/5');                  // abre o activa; la URL manda
await closeModule('clients/5');                  // pregunta si hay cambios sin guardar
await reloadModule('clients');                   // vuelve a pedir la pantalla al servidor
const result = await api('clients_update', { id: 5, credit: 99 });   // sobre de respuesta + http
bus.addEventListener(EVENTS.DATA_CHANGED, (e) => console.info(e.detail));
```

| Evento | `detail` |
|---|---|
| `lego:screen-mounted` | `{key}` |
| `lego:screen-activated` | `{key, url}` |
| `lego:screen-unmounted` | `{key}` |
| `lego:data-changed` | `{module, list, action, from}` |

## Comportamiento propio de un módulo

Si una pantalla necesita JavaScript propio, su raíz declara `data-lego-js` con la URL de un módulo ES que exporta `mount`:

```js
// modules/Reports/reports.js
export function mount(root, ctx) {
  root.querySelector('button').addEventListener('click', () => ctx.reload(), { signal: ctx.signal });
}
```

`ctx` trae `key`, `url`, `props`, `signal`, `navigate`, `close`, `reload`, `setDirty`, `on`, `emit` y `api`. Todo listener debe usar `ctx.signal`, que se aborta al cerrar o recargar la pantalla.

## Reglas verificadas por tests

- Sin `eval`, `new Function`, `document.write`, `alert`, `prompt` ni `console.log`.
- Sin variables globales en `window`.
- `fetch` solo en `lego.js`.
- Sin `<script>` en línea ejecutable en los componentes PHP.
- Cada archivo JS empieza con un comentario que explica qué hace.

## Tests de navegador

Corren con Playwright para Python dentro de su imagen oficial, contra `php -S` con los módulos de prueba (C6):

```bash
docker compose -f compose.test.yml run --rm browser
docker compose -f compose.test.yml down -v
```
