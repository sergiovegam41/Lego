# Crear Componente

## Pasos

### 1. Crear carpeta
```
components/App/MiFeature/
```

### 2. Crear archivo PHP
```php
// components/App/MiFeature/MiFeatureComponent.php

<?php
namespace Components\App\MiFeature;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

#[ApiComponent('/mi-feature', methods: ['GET'])]
class MiFeatureComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./mi-feature.css"];
    protected $JS_PATHS = ["./mi-feature.js"];

    public function __construct(
        public string $titulo = 'Mi Feature'
    ) {}

    protected function component(): string
    {
        return <<<HTML
        <div class="mi-feature">
            <h1>{$this->titulo}</h1>
        </div>
        HTML;
    }
}
```

### 3. Crear CSS
```css
/* components/App/MiFeature/mi-feature.css */

.mi-feature {
    padding: var(--space-lg);
}

.mi-feature h1 {
    color: var(--text-primary);
}
```

### 4. Crear JS (si necesario)
```javascript
// components/App/MiFeature/mi-feature.js

console.log('[MiFeature] Cargado');

// Tu lógica aquí
```

### 5. Acceder
```
http://localhost/component/mi-feature
```

## Estructura Final
```
components/App/MiFeature/
├── MiFeatureComponent.php
├── mi-feature.css
└── mi-feature.js
```

