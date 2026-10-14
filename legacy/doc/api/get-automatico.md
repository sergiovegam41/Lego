# GET Automático

El atributo `#[ApiGetResource]` genera dos endpoints de solo lectura optimizados para ser consumidos por el `TableComponent` del frontend.

Relacionado: [[api/atributos]] · [[api/crud-automatico]] · [[base-de-datos/modelos]]

Código: `Core/Routing/ApiGetRouter.php` · `Core/Attributes/ApiGetResource.php`

---

## Declaración

```php
#[ApiGetResource(
    endpoint: 'users-config',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'nombre', 'email', 'created_at'],
    filterable: ['id', 'nombre', 'email', 'rol_id', 'estado'],
    searchable: ['nombre', 'email']
)]
class User extends Model { }
```

## Endpoints Generados

| Método | Ruta | Acción |
|--------|------|--------|
| `GET` | `/api/get/users-config` | Lista paginada |
| `GET` | `/api/get/users-config/{id}` | Obtener uno |

El prefijo `/api/get/` distingue estas rutas de las CRUD.

## Para Qué Sirve

`ApiGetResource` es la pareja del `TableComponent` del frontend. La tabla hace una sola request con todos sus parámetros (página, filtros, orden, búsqueda) y recibe los datos formateados listos para renderizar.

```javascript
// El TableComponent hace esto internamente:
GET /api/get/users-config?page=1&sort=nombre&search=carlos&rol_id=2
```

## Parámetros del Atributo

| Parámetro | Tipo | Defecto | Descripción |
|-----------|------|---------|-------------|
| `endpoint` | `string` | Auto (nombre del modelo) | Endpoint personalizado |
| `pagination` | `string` | `'offset'` | Tipo de paginación |
| `perPage` | `int` | `20` | Registros por página |
| `sortable` | `array` | `[]` | Campos de ordenamiento permitidos |
| `filterable` | `array` | `[]` | Campos de filtro permitidos |
| `searchable` | `array` | `[]` | Campos de búsqueda de texto |
| `hidden` | `array` | `[]` | Campos a ocultar en respuesta |
| `appends` | `array` | `[]` | Campos calculados a incluir |

## Comparación con ApiCrudResource

| Característica | `ApiGetResource` | `ApiCrudResource` |
|---------------|-----------------|-------------------|
| Endpoints | 2 (GET only) | 5 (CRUD completo) |
| Prefijo | `/api/get/` | `/api/` |
| Mutación | ❌ No | ✅ Sí |
| Uso ideal | Tablas de consulta | Paneles de gestión |

Ambos atributos pueden coexistir en el mismo modelo si se necesita tanto lectura como escritura por separado.

## Visión

> `ApiGetResource` tendrá soporte para proyecciones: el frontend especifica qué columnas necesita (`?fields=id,nombre,email`) y el servidor retorna solo esas, reduciendo el payload. Útil para tablas con muchas columnas donde se muestran solo algunas.
