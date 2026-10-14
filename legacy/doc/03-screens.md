# Screens

Screen = Componente que representa una ventana/pantalla del sistema.

## ScreenInterface

```php
interface ScreenInterface
{
    public static function getScreenMetadata(): array;
    public static function getScreenId(): string;
    public static function getScreenRoute(): string;
}
```

## ScreenTrait

Implementación por defecto:

```php
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;

class ProductosListComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // Identidad del screen
    // parent_id se obtiene proceduralmente desde la BD (no se define como constante)
    public const SCREEN_ID = 'productos-list';
    public const SCREEN_LABEL = 'Ver Productos';
    public const SCREEN_ICON = 'list-outline';
    public const SCREEN_ROUTE = '/component/productos';
    public const SCREEN_ORDER = 0;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
}
```

## Constantes

| Constante | Requerida | Default | Descripción |
|-----------|-----------|---------|-------------|
| `SCREEN_ID` | ✅ | - | ID único |
| `SCREEN_ROUTE` | ✅ | - | Ruta del componente |
| `SCREEN_LABEL` | ❌ | SCREEN_ID | Texto en menú |
| `SCREEN_ICON` | ❌ | `document-outline` | Icono ionicon |
| `SCREEN_ORDER` | ❌ | `100` | Orden en menú |
| `SCREEN_VISIBLE` | ❌ | `true` | Si aparece en menú |
| `SCREEN_DYNAMIC` | ❌ | `false` | Si se activa por contexto |
| ~~`SCREEN_PARENT`~~ | ❌ | - | ❌ **OBSOLETO** - Se obtiene proceduralmente desde la BD |
| ~~`MENU_GROUP_ID`~~ | ❌ | - | ❌ **OBSOLETO** - Se obtiene proceduralmente desde la BD |

**NOTA IMPORTANTE:** `parent_id` y `menu_group_id` se obtienen **proceduralmente desde la base de datos** usando `SCREEN_ID`. No se deben definir como constantes. La BD es la fuente de verdad.

## Tipos de Screens

### 1. Normal
```php
public const SCREEN_VISIBLE = true;
public const SCREEN_DYNAMIC = false;
```
Aparece en menú y búsquedas.

### 2. Oculto Buscable
```php
public const SCREEN_VISIBLE = false;
public const SCREEN_DYNAMIC = false;
```
No en menú, sí en búsquedas.

### 3. Dinámico
```php
public const SCREEN_VISIBLE = false;
public const SCREEN_DYNAMIC = true;
```
Se activa por contexto (ej: "Editar" requiere saber qué editar).

## Estructura Padre-Hijo

```
📁 productos (grupo del menú, definido en MenuStructure.php)
├── 📄 productos-list (SCREEN_ID del componente principal)
├── 📄 productos-create
└── 📄 productos-edit (dinámico)
```

**FILOSOFÍA LEGO - PROCEDURAL:**
La jerarquía padre-hijo se define en `Core/Config/MenuStructure.php` usando la estructura anidada (`children`). El `parent_id` se deduce automáticamente desde esta jerarquía.

```php
// ProductosListComponent
// parent_id se obtiene proceduralmente desde la BD
public const SCREEN_ID = 'productos-list';
public const SCREEN_ROUTE = '/component/productos';

// ProductosCreateComponent
// parent_id se obtiene proceduralmente desde la BD
public const SCREEN_ID = 'productos-create';
public const SCREEN_ROUTE = '/component/productos/create';
```

**NO se definen `MENU_GROUP_ID` ni `SCREEN_PARENT`** - todo se obtiene desde la BD proceduralmente.

## ScreenRegistry

```php
// Core/Registry/Screens.php
ScreenRegistry::registerMany([
    ProductosListComponent::class,
    ProductosCreateComponent::class,
    ProductosEditComponent::class,
]);

// Obtener metadata
$meta = ScreenRegistry::get('productos-list');

// Generar estructura de menú
$menu = ScreenRegistry::getMenuStructure();
```

## Wrapper HTML

Todo screen debe usar el wrapper:

```php
protected function component(): string
{
    $screenId = self::SCREEN_ID;
    
    return <<<HTML
    <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
        <div class="lego-screen__content">
            <!-- Contenido aquí -->
        </div>
    </div>
    HTML;
}
```

## JS Config

```javascript
const SCREEN_CONFIG = {
    screenId: 'productos-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    route: '/component/productos',
    apiRoute: '/api/productos',
    children: {
        create: 'productos-create',
        edit: 'productos-edit'
    }
};
```

**NOTA:** `parentMenuId` se obtiene automáticamente desde la BD cuando se usa `openModuleWithMenu()`. No se debe hardcodear.

