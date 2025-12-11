# Menú

## MenuStructure

Fuente de verdad para el menú lateral.

```php
// Core/Config/MenuStructure.php
use Components\App\Productos\ProductosListComponent;

class MenuStructure
{
    public static function get(): array
    {
        return [
            [
                // El id del grupo se deriva desde SCREEN_ROUTE usando getGroupIdFromRoute()
                'id' => self::getGroupIdFromRoute(ProductosListComponent::SCREEN_ROUTE),
                'label' => 'Productos',
                'route' => ProductosListComponent::SCREEN_ROUTE,
                'icon' => 'cube-outline',
                'display_order' => 1,
                'is_visible' => true,
                'is_dynamic' => false,
                'children' => [
                    [
                        'id' => ProductosListComponent::SCREEN_ID,
                        // parent_id y level se deducen automáticamente desde la jerarquía (children)
                        'label' => ProductosListComponent::SCREEN_LABEL,
                        // ... resto de propiedades desde constantes
                    ]
                ]
            ]
        ];
    }
}
```

## Propiedades de Item

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| `id` | `string` | ID único del item |
| `parent_id` | `string\|null` | ⚠️ **Se deduce automáticamente** desde la jerarquía (children) |
| `label` | `string` | Texto mostrado |
| `index_label` | `string` | Texto para búsquedas |
| `route` | `string` | Ruta al hacer clic |
| `icon` | `string` | Nombre de icono ionicon |
| `display_order` | `int` | Orden de aparición |
| `level` | `int` | ⚠️ **Se deduce automáticamente** desde la jerarquía (children) |
| `is_visible` | `bool` | Si aparece en menú |
| `is_dynamic` | `bool` | Si se activa por contexto |
| `children` | `array` | Items hijos (define la jerarquía) |

**NOTA IMPORTANTE:** `parent_id` y `level` se deducen automáticamente desde la jerarquía anidada (`children`). No se deben definir explícitamente en `MenuStructure.php`.

## Items Dinámicos

Se agregan programáticamente:

```javascript
window.legoWindowManager.openModuleWithMenu({
    moduleId: 'productos-edit',
    // parentMenuId se obtiene automáticamente desde la BD (no se debe hardcodear)
    label: 'Editar Producto',
    url: '/component/productos/edit?id=5',
    icon: 'create-outline'
});
```

Se remueven automáticamente al cerrar el módulo.

## Sincronización con BD

```bash
php lego config:reset
```

Ejecuta la migración `seed_menu_items.php` que usa `MenuStructure::get()`.

## Búsqueda de Menú

```
GET /api/menu/search?q=texto
```

Busca en:
- Items con `is_visible = true`
- Items con `is_visible = false` pero `is_dynamic = false`

Excluye:
- Items con `is_dynamic = true` (requieren contexto)

