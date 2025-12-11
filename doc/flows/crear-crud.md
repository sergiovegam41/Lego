# Crear CRUD

CRUD = Lista + Crear + Editar + Eliminar

## Estructura Final
```
components/App/Productos/
├── ProductosComponent.php          # Lista (screen principal)
├── productos.css
├── productos.js
└── childs/
    ├── ProductosCreate/
    │   ├── ProductosCreateComponent.php
    │   ├── productos-form.css
    │   └── productos-create.js
    └── ProductosEdit/
        ├── ProductosEditComponent.php
        └── productos-edit.js
```

## Pasos

### 1. Crear Modelo
```php
// App/Models/Producto.php

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;

#[ApiGetResource('/api/get/productos')]
class Producto extends Model
{
    protected $table = 'productos';
    protected $fillable = ['name', 'description', 'price', 'stock', 'category'];
    protected array $sortable = ['id', 'name', 'price'];
    protected array $filterable = ['name', 'category'];
}
```

### 2. Crear Migración
```php
// database/migrations/xxxx_create_productos_table.php

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

### 3. Crear Controlador
```php
// App/Controllers/Productos/Controllers/ProductosController.php

<?php
namespace App\Controllers\Productos\Controllers;

use Core\Controllers\CoreController;
use App\Models\Producto;
use Flight;

class ProductosController extends CoreController
{
    public function __construct()
    {
        $method = Flight::request()->method;
        match($method) {
            'POST' => $this->handlePost(),
            default => Flight::json(['error' => 'Method not allowed'], 405)
        };
    }
    
    private function handlePost(): void
    {
        $action = Flight::request()->query['action'] ?? 'create';
        match($action) {
            'create' => $this->create(),
            'update' => $this->update(),
            'delete' => $this->delete(),
            default => Flight::json(['error' => 'Unknown action'], 400)
        };
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
        $data = Flight::request()->data->getData();
        Producto::destroy($data['id']);
        Flight::json(['success' => true]);
    }
}
```

### 4. Registrar Rutas API
```php
// Routes/Api.php

Flight::route('POST /api/productos/@action', fn($action) => 
    new \App\Controllers\Productos\Controllers\ProductosController()
);
```

### 5. Crear Screen Principal (Lista)
```php
// components/App/Productos/ProductosComponent.php

<?php
namespace Components\App\Productos;

use Core\Components\CoreComponent\CoreComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Essentials\TableComponent\TableComponent;
// ... imports de Column, RowActions

#[ApiComponent('/productos', methods: ['GET'])]
class ProductosComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // parent_id se obtiene proceduralmente desde la BD (no se define como constante)
    public const SCREEN_ID = 'productos-list';
    public const SCREEN_LABEL = 'Ver';
    public const SCREEN_ICON = 'list-outline';
    public const SCREEN_ROUTE = '/component/productos';
    public const SCREEN_ORDER = 0;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ... CSS_PATHS, JS_PATHS incluyendo screen.css/js
    
    protected function component(): string
    {
        // Configurar columnas y acciones
        // Crear TableComponent con model: Producto::class
        // Retornar HTML con wrapper lego-screen
    }
}
```

### 6. Crear Screen Create
```php
// components/App/Productos/childs/ProductosCreate/ProductosCreateComponent.php

class ProductosCreateComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    public const SCREEN_ID = 'productos-create';
    public const SCREEN_LABEL = 'Crear Producto';
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ORDER = 10;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // Formulario con InputText, Select, etc.
}
```

### 7. Crear Screen Edit (Dinámico)
```php
// components/App/Productos/childs/ProductosEdit/ProductosEditComponent.php

class ProductosEditComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    public const SCREEN_ID = 'productos-edit';
    public const SCREEN_LABEL = 'Editar Producto';
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ORDER = 20;
    public const SCREEN_VISIBLE = false;  // No en menú
    public const SCREEN_DYNAMIC = true;   // Se activa por contexto
    
    // Similar a Create pero carga datos existentes
}
```

### 8. Registrar Screens y Menú
Ver [crear-screen.md](crear-screen.md) pasos 3-5.

### 9. JS para Lista
```javascript
// productos.js

const SCREEN_CONFIG = {
    screenId: 'productos-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    route: '/component/productos',
    apiRoute: '/api/productos',
    children: {
        create: 'productos-create',
        edit: 'productos-edit'
    },
    tableId: 'productos-table'
};

window.handleEdit = function(rowData) {
    window.legoWindowManager.openModuleWithMenu({
        moduleId: SCREEN_CONFIG.children.edit,
        // parentMenuId se obtiene automáticamente desde la BD
        label: 'Editar',
        url: `${SCREEN_CONFIG.route}/edit?id=${rowData.id}`,
        icon: 'create-outline'
    });
};

window.handleDelete = async function(rowData) {
    const confirmed = await ConfirmationService.confirm({
        title: '¿Eliminar?',
        type: 'danger'
    });
    if (confirmed) {
        await fetch(`${SCREEN_CONFIG.apiRoute}/delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: rowData.id })
        });
        window.legoWindowManager.reloadActive();
    }
};

function openCreateModule() {
    window.legoWindowManager.openModuleWithMenu({
        moduleId: SCREEN_CONFIG.children.create,
        // parentMenuId se obtiene automáticamente desde la BD
        label: 'Nuevo',
        url: `${SCREEN_CONFIG.route}/create`,
        icon: 'add-circle-outline'
    });
}

window.openCreateModule = openCreateModule;
```

