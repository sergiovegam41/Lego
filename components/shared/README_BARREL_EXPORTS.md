# Barrel Exports - Sistema de Importación Agrupada

## ¿Qué son los Barrel Exports?

Similar a Flutter y React, los Barrel Exports permiten importar múltiples componentes desde un solo namespace, eliminando la necesidad de importar cada componente individualmente.

## Uso

### Antes (sin barrels):
```php
use Components\Shared\Forms\InputTextComponent\InputTextComponent as InputText;
use Components\Shared\Forms\SelectComponent\SelectComponent as Select;
use Components\Shared\Forms\ButtonComponent\ButtonComponent as Button;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent as TextArea;
use Components\Shared\Forms\CheckboxComponent\CheckboxComponent as Checkbox;
use Components\Shared\Forms\RadioComponent\RadioComponent as Radio;
// ... 10+ imports más
```

### Ahora (con barrels):
```php
use Components\Shared\Forms\Forms\{
    Form,
    InputText,
    Select,
    Button,
    Checkbox,
    Radio
};

use Components\Shared\Essentials\Essentials\{
    Column,
    Row,
    Div,
    Grid,
    Fragment
};

// Uso limpio
new Form(...)
new InputText(...)
new Row(...)
```

## Barrels Disponibles

### 📋 Forms
**Namespace:** `Components\Shared\Forms\Forms`

**Componentes:**
- `Form` - Formulario principal
- `FormGroup` - Agrupación de campos
- `FormActions` - Contenedor de botones
- `InputText` - Campo de texto
- `TextArea` - Área de texto
- `Select` - Selector dropdown
- `Checkbox` - Casilla de verificación
- `Radio` - Botón de radio
- `Button` - Botón

### 🎨 Essentials
**Namespace:** `Components\Shared\Essentials\Essentials`

**Componentes:**
- `Column` - Layout vertical
- `Row` - Layout horizontal
- `Div` - Contenedor genérico
- `Grid` - Cuadrícula CSS Grid
- `Fragment` - Agrupación sin wrapper

## Setup para Nuevos Desarrolladores

### ¿Necesito configurar algo?

**NO.** Todo está configurado automáticamente:

1. ✅ Los barrels se autocargan via Composer
2. ✅ Los IDE helpers se generan automáticamente
3. ✅ El autocompletado funciona inmediatamente

### Después de clonar el proyecto:

```bash
# 1. Instalar dependencias
composer install

# 2. (Opcional) Regenerar helpers manualmente
php scripts/generate-ide-helpers.php

# 3. Recargar el IDE (VSCode: Cmd+Shift+P → "Reload Window")
```

## IDE Helpers

### ¿Qué son?

Los archivos `_ide_helper.php` son stubs que permiten que el IDE reconozca las clases creadas dinámicamente con `class_alias`.

### ¿Se cargan en runtime?

**NO.** Solo existen para el autocompletado del IDE. En producción solo se usan los `class_alias` de los barrels.

### ¿Cómo se generan?

**Automáticamente** cada vez que ejecutas `composer dump-autoload`.

También puedes regenerarlos manualmente:
```bash
php scripts/generate-ide-helpers.php
```

### ¿Debo editarlos manualmente?

**NO.** Se regeneran automáticamente. Si necesitas agregar componentes:

1. Agrégalos al barrel (`Forms.php` o `Essentials.php`)
2. Ejecuta el generador: `php scripts/generate-ide-helpers.php`

## Agregar Nuevos Componentes

### 1. Agrega el class_alias al barrel

**Ejemplo en `components/shared/Forms/Forms.php`:**
```php
if (!class_exists('Components\Shared\Forms\Forms\MiNuevoComponente')) {
    class_alias(
        \Components\Shared\Forms\MiNuevoComponenteComponent\MiNuevoComponenteComponent::class,
        'Components\Shared\Forms\Forms\MiNuevoComponente'
    );
}
```

### 2. Regenera los helpers

```bash
php scripts/generate-ide-helpers.php
```

### 3. Úsalo

```php
use Components\Shared\Forms\Forms\MiNuevoComponente;

new MiNuevoComponente(...)
```

## Crear un Nuevo Barrel

### 1. Crea el archivo barrel

**Ejemplo: `components/shared/MiGrupo/MiGrupo.php`**
```php
<?php
if (!class_exists('Components\Shared\MiGrupo\MiGrupo\Componente1')) {
    class_alias(
        \Components\Shared\MiGrupo\Componente1Component\Componente1Component::class,
        'Components\Shared\MiGrupo\MiGrupo\Componente1'
    );
}
```

### 2. Agrégalo a composer.json

```json
"autoload": {
    "files": [
        "components/shared/Forms/Forms.php",
        "components/shared/Essentials/Essentials.php",
        "components/shared/MiGrupo/MiGrupo.php"
    ]
}
```

### 3. Agrégalo al generador

**En `scripts/generate-ide-helpers.php`:**
```php
$barrels = [
    'Forms' => [...],
    'Essentials' => [...],
    'MiGrupo' => [
        'file' => __DIR__ . '/../components/shared/MiGrupo/MiGrupo.php',
        'namespace' => 'Components\Shared\MiGrupo\MiGrupo',
        'output' => __DIR__ . '/../components/shared/MiGrupo/MiGrupo/_ide_helper.php',
    ],
];
```

### 4. Regenera

```bash
composer dump-autoload
```

## Troubleshooting

### El IDE no reconoce las clases

1. Regenera los helpers: `php scripts/generate-ide-helpers.php`
2. Recarga el IDE: `Cmd+Shift+P` → "Reload Window"
3. Si usas Intelephense, limpia el cache: `Cmd+Shift+P` → "Clear Cache and Reload"

### Los componentes no se encuentran en runtime

Verifica que el barrel esté en `composer.json` > `autoload` > `files`

### Los CSS no se cargan

Los barrels usan `class_alias` (no herencia), lo que preserva las rutas CSS/JS originales. Si no cargan:
1. Verifica que el componente original tenga `$CSS_PATHS` definido
2. Limpia el cache del navegador

## Ventajas

✅ **Menos imports** - 2 líneas en lugar de 15+
✅ **Mejor organización** - Componentes agrupados lógicamente
✅ **Autocompletado** - El IDE sugiere todos los componentes
✅ **Type-safe** - Full type hints y validación
✅ **Sin overhead** - Los alias son referencias, no copias
✅ **Zero config** - Funciona automáticamente para todos los desarrolladores
