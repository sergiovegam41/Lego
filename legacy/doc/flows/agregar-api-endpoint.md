# Agregar API Endpoint

## Opción A: Endpoint Simple

### 1. Crear Controlador
```php
// App/Controllers/MiFeature/Controllers/MiFeatureController.php

<?php
namespace App\Controllers\MiFeature\Controllers;

use Core\Controllers\CoreController;
use Flight;

class MiFeatureController extends CoreController
{
    public function __construct()
    {
        $this->handle();
    }
    
    private function handle(): void
    {
        $data = ['mensaje' => 'Hola mundo'];
        Flight::json(['success' => true, 'data' => $data]);
    }
}
```

### 2. Registrar Ruta
```php
// Routes/Api.php

Flight::route('GET /api/mi-feature', fn() => 
    new \App\Controllers\MiFeature\Controllers\MiFeatureController()
);
```

### 3. Probar
```
GET http://localhost/api/mi-feature
```

## Opción B: CRUD Manual

```php
class ProductosController extends CoreController
{
    public function __construct()
    {
        $method = Flight::request()->method;
        $action = Flight::request()->query['action'] ?? null;
        
        match(true) {
            $method === 'GET' => $this->list(),
            $method === 'POST' && $action === 'create' => $this->create(),
            $method === 'POST' && $action === 'update' => $this->update(),
            $method === 'POST' && $action === 'delete' => $this->delete(),
            default => $this->error('Acción no válida', 400)
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
    
    private function update(): void
    {
        $data = Flight::request()->data->getData();
        $producto = Producto::find($data['id']);
        $producto->update($data);
        Flight::json(['success' => true, 'data' => $producto]);
    }
    
    private function delete(): void
    {
        $id = Flight::request()->data['id'];
        Producto::destroy($id);
        Flight::json(['success' => true]);
    }
    
    private function error(string $msg, int $code): void
    {
        Flight::json(['success' => false, 'message' => $msg], $code);
    }
}
```

Rutas:
```php
Flight::route('GET /api/productos', fn() => new ProductosController());
Flight::route('POST /api/productos/@action', fn() => new ProductosController());
```

## Opción C: CRUD Automático (Modelo)

### 1. Atributo en Modelo
```php
use Core\Attributes\ApiGetResource;
use Core\Attributes\ApiCrudResource;

#[ApiGetResource('/api/get/productos')]
#[ApiCrudResource('/api/productos')]
class Producto extends Model
{
    protected array $sortable = ['id', 'name', 'price'];
    protected array $filterable = ['name', 'category'];
}
```

### 2. Registrar Router
```php
// Routes/Api.php

use Core\Routing\ApiGetRouter;
use Core\Routing\ApiCrudRouter;

ApiGetRouter::register('productos', \App\Models\Producto::class);
ApiCrudRouter::register('productos', \App\Models\Producto::class);
```

Genera automáticamente:
- `GET /api/get/productos` - Lista con paginación
- `GET /api/productos/:id` - Obtener uno
- `POST /api/productos` - Crear
- `PUT /api/productos/:id` - Actualizar
- `DELETE /api/productos/:id` - Eliminar

## Respuestas Estándar

```php
// Éxito
Flight::json([
    'success' => true,
    'data' => $datos,
    'message' => 'Operación exitosa'
]);

// Error
Flight::json([
    'success' => false,
    'message' => 'Descripción del error'
], 400);

// Error de validación
Flight::json([
    'success' => false,
    'message' => 'Datos inválidos',
    'errors' => [
        'name' => 'El nombre es requerido',
        'price' => 'El precio debe ser positivo'
    ]
], 422);

// Paginación
Flight::json([
    'success' => true,
    'data' => $items,
    'pagination' => [
        'total' => 100,
        'page' => 1,
        'limit' => 20,
        'totalPages' => 5
    ]
]);
```

## Llamar desde JS

```javascript
// GET
const response = await fetch('/api/productos');
const { success, data } = await response.json();

// POST
const response = await fetch('/api/productos?action=create', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name: 'Nuevo', price: 100 })
});
const result = await response.json();

if (result.success) {
    AlertService.toast('Guardado', 'success');
} else {
    AlertService.error(result.message);
}
```

