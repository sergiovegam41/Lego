# Crear Screen

Screen = Componente con identidad (aparece en menú).

## Pasos

### 1. Crear componente base
Ver [crear-componente.md](crear-componente.md)

### 2. Implementar ScreenInterface
```php
<?php
namespace Components\App\MiFeature;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;

#[ApiComponent('/mi-feature', methods: ['GET'])]
class MiFeatureComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // Identidad del screen
    // parent_id se obtiene proceduralmente desde la BD (no se define como constante)
    public const SCREEN_ID = 'mi-feature-list';
    public const SCREEN_LABEL = 'Mi Feature';
    public const SCREEN_ICON = 'cube-outline';
    public const SCREEN_ROUTE = '/component/mi-feature';
    public const SCREEN_ORDER = 0;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./mi-feature.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./mi-feature.js"
    ];

    protected function component(): string
    {
        $screenId = self::SCREEN_ID;
        
        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <h1>{$this->getLabel()}</h1>
            </div>
        </div>
        HTML;
    }
    
    private function getLabel(): string
    {
        return self::SCREEN_LABEL;
    }
}
```

### 3. Registrar en ScreenRegistry
```php
// Core/Registry/Screens.php

use Components\App\MiFeature\MiFeatureComponent;

function registerAllScreens(): void
{
    ScreenRegistry::registerMany([
        // Existentes...
        MiFeatureComponent::class,
    ]);
}
```

### 4. Agregar a MenuStructure
```php
// Core/Config/MenuStructure.php

use Components\App\MiFeature\MiFeatureComponent;

// En el array de get():
[
    // El id del grupo se deriva desde SCREEN_ROUTE usando getGroupIdFromRoute()
    'id' => self::getGroupIdFromRoute(MiFeatureComponent::SCREEN_ROUTE),
    'label' => MiFeatureComponent::SCREEN_LABEL,
    'route' => MiFeatureComponent::SCREEN_ROUTE,
    'icon' => MiFeatureComponent::SCREEN_ICON,
    'display_order' => 10,
    'is_visible' => true,
    'is_dynamic' => false,
    'children' => [
        [
            'id' => MiFeatureComponent::SCREEN_ID,
            // parent_id y level se deducen automáticamente desde la jerarquía (children)
            // ...resto desde constantes
        ]
    ]
]
```

### 5. Resetear menú
```bash
php lego config:reset
```

### 6. JS Config
```javascript
// mi-feature.js
const SCREEN_CONFIG = {
    screenId: 'mi-feature-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    route: '/component/mi-feature',
    apiRoute: '/api/mi-feature'
};
```

