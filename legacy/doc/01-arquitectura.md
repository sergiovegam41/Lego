# Arquitectura

## Flujo de Ejecución

```
1. Request HTTP
   ↓
2. index.php → Autoload → Flight Router
   ↓
3. Router matchea ruta
   ├── /component/* → ComponentsController → Renderiza componente
   └── /api/*       → Controlador API → JSON response
   ↓
4. Componente se renderiza
   ├── PHP: component() → HTML
   ├── CSS: $CSS_PATHS → <link>
   └── JS: $JS_PATHS → <script> vía loadModules
```

## Capas

| Capa | Responsabilidad | Ubicación |
|------|-----------------|-----------|
| Presentación | Componentes UI | `components/` |
| Lógica | Controladores, Modelos | `App/` |
| Framework | CoreComponent, Routing | `Core/` |
| Assets | CSS/JS globales | `assets/` |

## Namespaces

```php
// Componentes de aplicación
namespace Components\App\ExampleCrud;

// Componentes compartidos
namespace Components\Shared\Buttons;

// Core del framework
namespace Core\Components\CoreComponent;

// Controladores
namespace App\Controllers\ExampleCrud\Controllers;

// Modelos
namespace App\Models;
```

## Routing

```php
// Routes/Web.php - Páginas
Flight::route('/', fn() => new LoginComponent());
Flight::route('/admin', fn() => new HomeComponent());

// Routes/Api.php - API
Flight::route('GET /api/get/@model', fn($model) => ...);
Flight::route('POST /api/@model/create', fn($model) => ...);

// Atributo en componente - Define ruta automática
#[ApiComponent('/example-crud', methods: ['GET'])]
class ExampleCrudComponent extends CoreComponent
```

## Carga de Assets

```php
class MiComponent extends CoreComponent
{
    // CSS se inyecta como <link>
    protected $CSS_PATHS = ["./mi-componente.css"];
    
    // JS se carga vía window.lego.loadModules()
    protected $JS_PATHS = ["./mi-componente.js"];
}
```

Rutas relativas (`./`) se resuelven desde la ubicación del componente.

## Composición

Componentes contienen otros componentes:

```php
protected function component(): string
{
    $boton = new ButtonComponent(label: "Guardar");
    $input = new InputTextComponent(id: "nombre");
    
    return <<<HTML
    <div>
        {$input->render()}
        {$boton->render()}
    </div>
    HTML;
}
```

