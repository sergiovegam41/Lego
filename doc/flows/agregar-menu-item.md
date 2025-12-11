# Agregar Item al Menú

## Opción A: Item para Screen Existente

Si ya tienes un componente con ScreenInterface:

### 1. Importar en MenuStructure
```php
// Core/Config/MenuStructure.php

use Components\App\MiFeature\MiFeatureComponent;
```

### 2. Agregar al array
```php
public static function get(): array
{
    return [
        // Items existentes...
        
        [
            // El id del grupo se deriva desde SCREEN_ROUTE usando getGroupIdFromRoute()
            'id' => self::getGroupIdFromRoute(MiFeatureComponent::SCREEN_ROUTE),
            'label' => 'Mi Feature',
            'index_label' => 'Mi Feature',
            'route' => MiFeatureComponent::SCREEN_ROUTE,
            'icon' => 'cube-outline',
            'display_order' => 10,
            'is_visible' => true,
            'is_dynamic' => false,
            'children' => [
                [
                    'id' => MiFeatureComponent::SCREEN_ID,
                    // parent_id y level se deducen automáticamente desde la jerarquía (children)
                    'label' => MiFeatureComponent::SCREEN_LABEL,
                    'index_label' => MiFeatureComponent::SCREEN_LABEL,
                    'route' => MiFeatureComponent::SCREEN_ROUTE,
                    'icon' => MiFeatureComponent::SCREEN_ICON,
                    'display_order' => MiFeatureComponent::SCREEN_ORDER,
                    'is_visible' => MiFeatureComponent::SCREEN_VISIBLE,
                    'is_dynamic' => MiFeatureComponent::SCREEN_DYNAMIC
                ]
            ]
        ]
    ];
}
```

### 3. Ejecutar reset
```bash
php lego config:reset
```

## Opción B: Item Simple (Sin Screen)

```php
[
    'id' => 'configuracion',
    'parent_id' => null,
    'label' => 'Configuración',
    'index_label' => 'Configuración',
    'route' => '/component/configuracion',
    'icon' => 'settings-outline',
    'display_order' => 99,
    'level' => 0,
    'is_visible' => true,
    'is_dynamic' => false
]
```

## Opción C: Subitem

```php
[
    'id' => 'productos',
    // ...
    'children' => [
        [
            'id' => 'productos-list',
            // parent_id se deduce automáticamente desde la jerarquía (children)
            'label' => 'Ver todos',
            'route' => '/component/productos',
            // level se deduce automáticamente
            // ...
        ],
        [
            'id' => 'productos-categorias',
            // parent_id se deduce automáticamente desde la jerarquía (children)
            'label' => 'Categorías',
            'route' => '/component/productos/categorias',
            // level se deduce automáticamente
            // ...
        ]
    ]
]
```

## Item Dinámico (Se activa por contexto)

```php
[
    'id' => 'productos-edit',
    // parent_id se deduce automáticamente desde la jerarquía (children)
    'label' => 'Editar',
    'route' => '/component/productos/edit',
    'icon' => 'create-outline',
    'display_order' => 20,
    // level se deduce automáticamente
    'is_visible' => false,   // No aparece por defecto
    'is_dynamic' => true     // Se activa programáticamente
]
```

Activación desde JS:
```javascript
window.legoWindowManager.openModuleWithMenu({
    moduleId: 'productos-edit',
    // parentMenuId se obtiene automáticamente desde la BD (no se debe hardcodear)
    label: 'Editar Producto',
    url: '/component/productos/edit?id=5',
    icon: 'create-outline'
});
```

## Item Oculto pero Buscable

```php
[
    'id' => 'configuracion-avanzada',
    'label' => 'Configuración Avanzada',
    'is_visible' => false,   // No en menú
    'is_dynamic' => false    // Pero sí en búsquedas
]
```

## Verificar

1. Ejecutar `php lego config:reset`
2. Refrescar página
3. El item debería aparecer en el menú lateral

