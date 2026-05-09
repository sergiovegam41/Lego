# Modelos Eloquent

Los modelos representan las tablas de la base de datos. Lego usa Illuminate Eloquent (el ORM de Laravel) de forma standalone.

Relacionado: [[base-de-datos/postgresql]] · [[base-de-datos/migraciones]] · [[api/crud-automatico]] · [[api/get-automatico]]

Código: `App/Models/`

---

## Estructura de un Modelo

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;

#[ApiGetResource(
    endpoint: 'productos',
    sortable: ['nombre', 'precio', 'created_at'],
    filterable: ['categoria', 'activo'],
    searchable: ['nombre', 'descripcion']
)]
class Producto extends Model
{
    protected $table    = 'productos';
    protected $fillable = ['nombre', 'descripcion', 'precio', 'categoria', 'activo'];
    protected $hidden   = ['deleted_at'];
    protected $casts    = [
        'activo'     => 'boolean',
        'precio'     => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Relaciones
    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class);
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // Accessors
    public function getPrecioFormateadoAttribute(): string
    {
        return '$' . number_format($this->precio, 2);
    }
}
```

## Modelos del Sistema

| Modelo | Tabla | Atributo API |
|--------|-------|-------------|
| `User` | `auth_users` | `#[ApiGetResource]` |
| `Role` | `auth_roles` | `#[ApiGetResource]` |
| `AuthGroup` | `auth_groups` | `#[ApiGetResource]` |
| `MenuItem` | `menu_items` | — (acceso directo via rutas de menú) |
| `Tool` | `tools` | `#[ApiGetResource]` |
| `ToolFeature` | `tool_features` | — |
| `ExampleCrud` | `example_crud` | `#[ApiGetResource]` |
| `ExampleCrudImage` | `example_crud_images` | — |

## Convenciones

- Namespace: `App\Models`
- Un modelo por archivo
- Nombre en PascalCase singular: `Producto`, `MenuItem`
- Tabla en snake_case plural: `productos`, `menu_items`
- Si el nombre de tabla difiere, declarar `$table`

## Scopes

Los scopes encapsulan condiciones de query reutilizables:

```php
// Definición
public function scopeActivo($query)
{
    return $query->where('activo', true);
}

// Uso
Producto::activo()->paginate(20);
Producto::activo()->byCategoria('tech')->get();
```

## Accessors y Mutators

Los accessors calculan valores derivados sin guardarlos en la BD:

```php
// Accessor (get)
public function getPrecioFormateadoAttribute(): string
{
    return '$' . number_format($this->precio, 2);
}
// Uso: $producto->precio_formateado

// Mutator (set)
public function setNombreAttribute(string $value): void
{
    $this->attributes['nombre'] = strtolower(trim($value));
}
```

## Relaciones Típicas

```php
// Un producto tiene muchas imágenes
public function imagenes(): HasMany
{
    return $this->hasMany(ProductoImagen::class);
}

// Un usuario pertenece a un rol
public function rol(): BelongsTo
{
    return $this->belongsTo(Role::class, 'role_id');
}

// Un item de menú tiene hijos
public function hijos(): HasMany
{
    return $this->hasMany(MenuItem::class, 'parent_id');
}
```

## Visión

> Los modelos tendrán soporte para observers nativos de Lego: clases que reaccionan a eventos del modelo (created, updated, deleted) y disparan acciones como enviar notificaciones, invalidar caché, o registrar en un log de auditoría — sin modificar el modelo ni el controlador.
