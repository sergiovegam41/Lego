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
                'id' => ProductosListComponent::MENU_GROUP_ID,
                'parent_id' => null,
                'label' => 'Productos',
                'route' => ProductosListComponent::SCREEN_ROUTE,
                'icon' => 'cube-outline',
                'display_order' => 1,
                'level' => 0,
                'is_visible' => true,
                'is_dynamic' => false,
                'children' => [
                    [
                        'id' => ProductosListComponent::SCREEN_ID,
                        'parent_id' => ProductosListComponent::MENU_GROUP_ID,
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
| `parent_id` | `string\|null` | ID del padre (null = raíz) |
| `label` | `string` | Texto mostrado |
| `index_label` | `string` | Texto para búsquedas |
| `route` | `string` | Ruta al hacer clic |
| `icon` | `string` | Nombre de icono ionicon |
| `display_order` | `int` | Orden de aparición |
| `level` | `int` | Nivel de anidación (0, 1, 2...) |
| `is_visible` | `bool` | Si aparece en menú |
| `is_dynamic` | `bool` | Si se activa por contexto |
| `children` | `array` | Items hijos |

## Items Dinámicos

Se agregan programáticamente:

```javascript
window.legoWindowManager.openModuleWithMenu({
    moduleId: 'productos-edit',
    parentMenuId: 'productos',  // Grupo donde aparece
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

