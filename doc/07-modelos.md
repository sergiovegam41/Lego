# Modelos

## Estructura Básica

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;

#[ApiGetResource('/api/get/productos')]
class Producto extends Model
{
    protected $table = 'productos';
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'created_at' => 'datetime',
    ];
}
```

## Atributos API

### ApiGetResource
```php
#[ApiGetResource('/api/get/productos')]
```
Genera endpoint GET con paginación server-side automática.

### ApiCrudResource
```php
#[ApiCrudResource('/api/productos')]
```
Genera endpoints CRUD completos.

## Server-Side Config

```php
class Producto extends Model
{
    // Campos ordenables
    protected array $sortable = ['id', 'name', 'price', 'created_at'];
    
    // Campos filtrables
    protected array $filterable = ['name', 'category', 'price'];
    
    // Campos buscables (búsqueda general)
    protected array $searchable = ['name', 'description'];
}
```

## Relaciones

```php
class Producto extends Model
{
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'category_id');
    }
    
    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class);
    }
}
```

## Scopes

```php
class MenuItem extends Model
{
    // Solo visibles
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true)
                     ->where('is_dynamic', false);
    }
    
    // Buscables (no dinámicos)
    public function scopeSearchable($query)
    {
        return $query->where('is_dynamic', false);
    }
}

// Uso
MenuItem::visible()->get();
MenuItem::searchable()->where('label', 'LIKE', '%term%')->get();
```

## Migración Correspondiente

```php
// database/migrations/2024_01_01_000001_create_productos_table.php
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->integer('stock')->default(0);
    $table->string('category')->nullable();
    $table->timestamps();
});
```

