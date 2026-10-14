# Atributos PHP

Los atributos son decoradores PHP 8 que declaran el comportamiento de componentes, controladores y modelos. El framework los lee en tiempo de arranque para registrar rutas automáticamente.

Relacionado: [[api/crud-automatico]] · [[api/get-automatico]] · [[api/controladores]] · [[componentes/core-component]]

---

## Resumen

| Atributo | Se aplica a | Genera |
|----------|------------|--------|
| `#[ApiComponent]` | Componente (clase) | Ruta `/component/ruta` |
| `#[ApiRoutes]` | Controlador (clase) | Rutas `/api/endpoint/accion` |
| `#[ApiCrudResource]` | Modelo Eloquent | 5 endpoints CRUD en `/api/recurso` |
| `#[ApiGetResource]` | Modelo Eloquent | 2 endpoints GET en `/api/get/recurso` |

---

## `#[ApiComponent]`

Registra un componente PHP como ruta del SPA.

```php
#[ApiComponent('/productos', methods: ['GET'], requiresAuth: true)]
class ProductosListComponent extends CoreComponent { }
// → GET /component/productos
```

| Parámetro | Tipo | Defecto | Descripción |
|-----------|------|---------|-------------|
| `path` | `string` | — | Ruta del componente |
| `methods` | `array` | `['GET']` | Métodos HTTP permitidos |
| `requiresAuth` | `bool` | `true` | Requiere autenticación |

---

## `#[ApiRoutes]`

Auto-registra rutas de un controlador usando presets o acciones personalizadas.

```php
#[ApiRoutes('/productos', preset: 'crud')]
class ProductosController extends CoreController { }
```

### Presets

| Preset | Acciones generadas |
|--------|------------------|
| `crud` | list(GET), get(GET), create(POST), update(POST), delete(POST) |
| `crud-rest` | list(GET), get(GET), create(POST), update(PUT), delete(DELETE) |
| `readonly` | list(GET), get(GET) |
| `writeonly` | create(POST), update(POST), delete(POST) |
| `custom` | Solo las acciones que definas manualmente |

### Parámetros

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `endpoint` | `string` | Base de la ruta (sin `/api`) |
| `preset` | `string` | Preset de acciones |
| `actions` | `array` | Acciones adicionales o personalizadas |
| `exclude` | `array` | Acciones del preset a excluir |
| `middleware` | `array` | Middlewares aplicados a todas las rutas |
| `prefix` | `string` | Prefijo adicional (ej: `'v2'`) |
| `enabled` | `bool` | Activa/desactiva el auto-registro |

### Ejemplo con acciones personalizadas

```php
#[ApiRoutes('/reportes', preset: 'custom', actions: [
    'generar'   => ['POST'],
    'descargar' => ['GET'],
    'programar' => ['POST'],
])]
class ReportesController extends CoreController
{
    public function generar(): void { ... }
    public function descargar(): void { ... }
    public function programar(): void { ... }
}
```

---

## `#[ApiCrudResource]`

Genera automáticamente un API CRUD completo desde un modelo Eloquent. Ver [[api/crud-automatico]].

```php
#[ApiCrudResource(
    pagination: 'offset',
    perPage: 20,
    sortable: ['nombre', 'precio'],
    filterable: ['categoria'],
    searchable: ['nombre', 'descripcion']
)]
class Producto extends Model { }
```

---

## `#[ApiGetResource]`

Genera endpoints de solo lectura optimizados para el `TableComponent`. Ver [[api/get-automatico]].

```php
#[ApiGetResource(
    endpoint: 'productos',
    perPage: 50,
    searchable: ['nombre']
)]
class Producto extends Model { }
```

---

## Visión

> Los atributos son la forma declarativa de Lego. La visión es que prácticamente toda la configuración del sistema —rutas, permisos, validaciones, caché— se exprese como atributos PHP sobre las clases, sin archivos de configuración separados. El framework lee los atributos en tiempo de compilación (caché de rutas) para eliminar el overhead en producción.
