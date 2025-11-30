# API

## Rutas

```php
// Routes/Api.php

// CRUD automático desde modelo
use Core\Routing\ApiCrudRouter;
ApiCrudRouter::register('productos', \App\Models\Producto::class);
// Genera: GET/POST/PUT/DELETE /api/productos

// GET automático desde modelo
use Core\Routing\ApiGetRouter;
ApiGetRouter::register('productos', \App\Models\Producto::class);
// Genera: GET /api/get/productos

// Ruta manual
Flight::route('POST /api/productos/custom', fn() => new CustomController());
```

## Controladores

```php
namespace App\Controllers\Productos\Controllers;

use Core\Controllers\CoreController;
use App\Models\Producto;
use Flight;

class ProductosController extends CoreController
{
    public function __construct()
    {
        $this->handleRequest();
    }
    
    private function handleRequest(): void
    {
        $method = Flight::request()->method;
        
        match($method) {
            'GET' => $this->list(),
            'POST' => $this->create(),
            default => $this->error('Método no permitido', 405)
        };
    }
    
    private function list(): void
    {
        $productos = Producto::all();
        Flight::json(['success' => true, 'data' => $productos]);
    }
    
    private function create(): void
    {
        $data = Flight::request()->data->getData();
        $producto = Producto::create($data);
        Flight::json(['success' => true, 'data' => $producto]);
    }
    
    private function error(string $msg, int $code): void
    {
        Flight::json(['success' => false, 'message' => $msg], $code);
    }
}
```

## Atributos en Modelos

```php
use Core\Attributes\ApiGetResource;
use Core\Attributes\ApiCrudResource;

#[ApiGetResource('/api/get/productos')]
#[ApiCrudResource('/api/productos')]
class Producto extends Model
{
    // Campos para server-side
    protected array $sortable = ['id', 'name', 'price'];
    protected array $filterable = ['name', 'category'];
    protected array $searchable = ['name', 'description'];
}
```

## Respuestas Estándar

```json
// Éxito
{
    "success": true,
    "data": [...],
    "message": "Operación exitosa"
}

// Error
{
    "success": false,
    "message": "Descripción del error",
    "errors": { "campo": "Error específico" }
}

// Paginación
{
    "success": true,
    "data": [...],
    "pagination": {
        "total": 100,
        "page": 1,
        "limit": 20,
        "totalPages": 5
    }
}
```

## Llamadas desde JS

```javascript
// Fetch simple
const response = await fetch('/api/productos');
const data = await response.json();

// Con helper
function apiUrl(action) {
    return `/api/productos/${action}`;
}

const response = await fetch(apiUrl('create'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name: 'Nuevo' })
});
```

