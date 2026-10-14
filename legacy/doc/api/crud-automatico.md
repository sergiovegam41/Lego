# CRUD Automático

Con el atributo `#[ApiCrudResource]` en un modelo Eloquent, el framework genera automáticamente 5 endpoints CRUD sin escribir ningún controlador.

Relacionado: [[api/atributos]] · [[api/get-automatico]] · [[api/controladores]] · [[base-de-datos/modelos]]

Código: `Core/Routing/ApiCrudRouter.php` · `Core/Attributes/ApiCrudResource.php`

---

## Declaración

```php
#[ApiCrudResource(
    pagination: 'offset',
    perPage: 20,
    sortable: ['nombre', 'precio', 'created_at'],
    filterable: ['categoria', 'activo'],
    searchable: ['nombre', 'descripcion']
)]
class Producto extends Model
{
    protected $table = 'productos';
    protected $fillable = ['nombre', 'descripcion', 'precio', 'categoria', 'activo'];
}
```

## Endpoints Generados

El endpoint base se deriva del nombre del modelo en kebab-case plural:
`Producto` → `/api/productos`

| Método | Ruta | Acción |
|--------|------|--------|
| `GET` | `/api/productos` | Lista paginada |
| `GET` | `/api/productos/{id}` | Obtener uno |
| `POST` | `/api/productos` | Crear |
| `PUT` | `/api/productos/{id}` | Actualizar |
| `DELETE` | `/api/productos/{id}` | Eliminar |

## Parámetros de Consulta (Lista)

```
GET /api/productos?page=2&per_page=50&sort=precio&order=desc&search=laptop&categoria=tecnologia
```

| Parámetro | Descripción |
|-----------|-------------|
| `page` | Número de página |
| `per_page` | Registros por página (máximo 100) |
| `sort` | Campo de ordenamiento (debe estar en `sortable`) |
| `order` | `asc` o `desc` |
| `search` | Búsqueda en campos `searchable` |
| `{campo}` | Filtro exacto por campos `filterable` |

## Formato de Respuesta

```json
{
    "data": [
        { "id": 1, "nombre": "Laptop Pro", "precio": 1500.00 }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "last_page": 8
    }
}
```

## Parámetros del Atributo

| Parámetro | Tipo | Defecto | Descripción |
|-----------|------|---------|-------------|
| `endpoint` | `string` | Auto (nombre del modelo) | Endpoint personalizado |
| `pagination` | `string` | `'offset'` | `'offset'`, `'cursor'`, `'page'` |
| `perPage` | `int` | `20` | Registros por página (1-100) |
| `sortable` | `array` | `[]` | Campos permitidos para sort |
| `filterable` | `array` | `[]` | Campos permitidos para filtrar |
| `searchable` | `array` | `[]` | Campos para búsqueda de texto |
| `middleware` | `array` | `[]` | Middlewares requeridos |
| `softDeletes` | `bool` | `false` | Soporte para borrado suave |
| `hidden` | `array` | `[]` | Campos a ocultar en respuesta |
| `controllerClass` | `string` | `DefaultCrudController` | Controlador personalizado |

## Controlador Personalizado

Si la lógica por defecto no es suficiente:

```php
#[ApiCrudResource(controllerClass: ProductoController::class)]
class Producto extends Model { }
```

```php
class ProductoController extends AbstractCrudController
{
    public function create(): void
    {
        // Lógica personalizada
        parent::create(); // Llama al comportamiento base
    }
}
```

## Diferencia con ApiGetResource

| | `ApiCrudResource` | `ApiGetResource` |
|-|-------------------|-----------------|
| Endpoints | 5 (CRUD completo) | 2 (solo lectura) |
| Prefijo URL | `/api/{recurso}` | `/api/get/{recurso}` |
| Uso típico | Formularios de gestión | Tablas de consulta |

## Visión

> El CRUD automático evolucionará para incluir validación declarativa directamente en el atributo (`validation: ['nombre' => 'required|max:100']`), hooks pre/post por acción, y soporte para relaciones anidadas en el payload de creación/actualización.
