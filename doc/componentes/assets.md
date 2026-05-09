# Carga de Assets

Cada componente declara sus propios CSS y JS. El framework los inyecta automáticamente cuando el componente se renderiza.

Relacionado: [[componentes/core-component]] · [[routing/rutas-componentes]]

---

## Declaración

```php
class MiComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./mi-componente.css"];
    protected $JS_PATHS  = ["./mi-componente.js"];
}
```

## Tipos de Rutas

| Formato | Ejemplo | Resolución |
|---------|---------|-----------|
| Relativa al componente | `"./styles.css"` | `components/App/MiFeature/styles.css` |
| Relativa al padre | `"../shared/utils.css"` | Un nivel arriba |
| Absoluta desde root | `"assets/css/global.css"` | Desde la raíz del proyecto |

## JS con Argumentos PHP

Cuando el JavaScript necesita recibir datos del servidor al cargar:

```php
use Core\DTO\ScriptCoreDTO;

protected $JS_PATHS_WITH_ARG = [
    new ScriptCoreDTO("./mi-componente.js", [
        'apiRoute' => '/api/productos',
        'perPage'  => 20,
    ])
];
```

En el JavaScript, los argumentos llegan como parámetro de la función de inicialización.

## Cómo se Inyectan

Cuando se llama a `render()`, el framework genera:

```html
<link rel="stylesheet" href="/component/mi-feature/mi-componente.css?v=1234567890">

<div class="mi-feature">
    <!-- HTML del componente -->
</div>

<script type="module">
    import init from '/component/mi-feature/mi-componente.js?v=1234567890';
    init();
</script>
```

La ruta `/component/mi-feature/mi-componente.css` es servida por [[routing/rutas-componentes]] con cabeceras de caché optimizadas.

## Cache Busting

El parámetro `?v=` es el timestamp del archivo. Si el archivo cambia, el timestamp cambia y el navegador descarga la versión nueva, ignorando el caché.

## Servicio de Archivos Estáticos

El [[routing/rutas-componentes|Router de Componentes]] detecta cuando la URL termina en `.css` o `.js` y sirve el archivo con:
- Cabecera `Cache-Control: max-age=31536000, immutable` (1 año)
- Validación ETag
- Respuesta `304 Not Modified` si el archivo no cambió

## Assets Globales

Los assets que aplican a toda la aplicación (no a un componente específico) van en `assets/`:

```
assets/
├── css/core/
│   ├── base.css
│   ├── theme-variables.css
│   └── sidebar/menu-style.css
└── js/core/
    ├── base-lego-framework.js
    ├── api/ApiClient.js
    └── modules/windows-manager/windows-manager.js
```

Estos se cargan en `MainComponent` una vez, al entrar al SPA.

## Visión

> En el futuro, los assets de componentes se podrán agrupar en un bundle por pantalla: todos los CSS/JS de los componentes que conforman una pantalla se fusionan en un solo archivo. Esto reduce las requests HTTP sin cambiar la forma en que se declaran los assets en cada componente.
