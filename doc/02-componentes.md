# Componentes

## CoreComponent

Clase base para todos los componentes.

```php
namespace Components\App\MiFeature;

use Core\Components\CoreComponent\CoreComponent;

class MiComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./mi-componente.css"];
    protected $JS_PATHS = ["./mi-componente.js"];

    public function __construct(
        public string $titulo,
        public bool $activo = true
    ) {}

    protected function component(): string
    {
        return <<<HTML
        <div class="mi-componente">
            <h1>{$this->titulo}</h1>
        </div>
        HTML;
    }
}
```

## Propiedades

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| `$CSS_PATHS` | `array` | Rutas CSS a cargar |
| `$JS_PATHS` | `array` | Rutas JS a cargar |
| `$JS_PATHS_WITH_ARG` | `array` | JS con argumentos |
| `$children` | `array` | Componentes hijos |

## Métodos

| Método | Descripción |
|--------|-------------|
| `component()` | Retorna el HTML del componente |
| `render()` | Renderiza CSS + HTML + JS |
| `renderChildren()` | Renderiza los `$children` |
| `renderSlot($slot)` | Renderiza un slot específico |

## Rutas de Assets

```php
// Relativa al componente
protected $CSS_PATHS = ["./styles.css"];

// Relativa al padre
protected $CSS_PATHS = ["../shared/utils.css"];

// Absoluta desde root
protected $CSS_PATHS = ["assets/css/global.css"];
```

## Composición con Children

```php
public function __construct(
    public array $children = []
) {
    $this->children = $children;
}

protected function component(): string
{
    return <<<HTML
    <div class="container">
        {$this->renderChildren()}
    </div>
    HTML;
}
```

## Slots Nombrados

```php
public function __construct(
    public array $headerSlot = [],
    public array $bodySlot = []
) {}

protected function component(): string
{
    return <<<HTML
    <div class="card">
        <header>{$this->renderSlot($this->headerSlot)}</header>
        <div class="body">{$this->renderSlot($this->bodySlot)}</div>
    </div>
    HTML;
}
```

## Atributo ApiComponent

Define la ruta del componente:

```php
#[ApiComponent('/mi-ruta', methods: ['GET'])]
class MiComponent extends CoreComponent
```

El componente se accede en `/component/mi-ruta`.

