<?php

namespace Core\Config;

use Core\Registry\ScreenRegistry;

// ═══════════════════════════════════════════════════════════════════
// SCREEN IMPORTS - Importar los screens para usar sus constantes
// ═══════════════════════════════════════════════════════════════════
use Components\App\ExampleCrud\ExampleCrudComponent;
use Components\App\ExampleCrud\Childs\ExampleCreate\ExampleCreateComponent;
use Components\App\ExampleCrud\Childs\ExampleEdit\ExampleEditComponent;
use Components\App\ToolsCrud\ToolsCrudComponent;
use Components\App\ToolsCrud\Childs\ToolsCreate\ToolsCreateComponent;
use Components\App\ToolsCrud\Childs\ToolsEdit\ToolsEditComponent;
use Components\App\MenuConfig\MenuConfigComponent;

/**
 * MenuStructure - Fuente única de verdad para el menú de navegación
 * 
 * SCREEN PATTERN:
 * Los IDs, labels, iconos y rutas vienen DIRECTAMENTE de las constantes
 * definidas en cada Screen. Esto elimina la duplicación de strings.
 * 
 * BENEFICIOS:
 * - Si cambias SCREEN_ID en el componente, se actualiza aquí automáticamente
 * - No hay strings mágicos duplicados
 * - El IDE puede hacer "Find Usages" y refactoring
 * - Errores de typo se detectan en tiempo de compilación
 * 
 * TIPOS DE ITEMS:
 * 1. NORMAL (visible=true, dynamic=false) - En menú y búsquedas
 * 2. OCULTO (visible=false, dynamic=false) - Solo en búsquedas
 * 3. DINÁMICO (visible=false, dynamic=true) - Se activa por contexto
 */
class MenuStructure
{
    /**
     * Obtener la estructura completa del menú
     * 
     * USA CONSTANTES DE SCREENS - No strings duplicados
     */
    public static function get(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════
            // INICIO (aún no es Screen, usa strings)
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => 'inicio',
                'parent_id' => null,
                'label' => 'Inicio',
                'index_label' => 'Inicio',
                'route' => '/component/inicio',
                'icon' => 'home-outline',
                'display_order' => 0,
                'level' => 0,
                'is_visible' => true,
                'is_dynamic' => false
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // EXAMPLE CRUD - Grupo del menú (carpeta)
            // El grupo NO es una screen, es solo un contenedor de menú
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => ExampleCrudComponent::MENU_GROUP_ID,  // 'example-crud' (grupo)
                'parent_id' => null,
                'label' => 'Example CRUD',
                'index_label' => 'Example CRUD',
                'route' => ExampleCrudComponent::SCREEN_ROUTE, // Redirige a la lista
                'icon' => 'cube-outline',
                'display_order' => 1,
                'level' => 0,
                'is_visible' => true,
                'is_dynamic' => false,
                'children' => [
                    // Ver (Lista) - Desde Screen
                    [
                        'id' => ExampleCrudComponent::SCREEN_ID,
                        'parent_id' => ExampleCrudComponent::MENU_GROUP_ID,
                        'label' => ExampleCrudComponent::SCREEN_LABEL,
                        'index_label' => ExampleCrudComponent::SCREEN_LABEL,
                        'route' => ExampleCrudComponent::SCREEN_ROUTE,
                        'icon' => ExampleCrudComponent::SCREEN_ICON,
                        'display_order' => ExampleCrudComponent::SCREEN_ORDER,
                        'level' => 1,
                        'is_visible' => ExampleCrudComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ExampleCrudComponent::SCREEN_DYNAMIC
                    ],
                    // Crear - Desde Screen
                    [
                        'id' => ExampleCreateComponent::SCREEN_ID,
                        'parent_id' => ExampleCrudComponent::MENU_GROUP_ID,
                        'label' => ExampleCreateComponent::SCREEN_LABEL,
                        'index_label' => ExampleCreateComponent::SCREEN_LABEL,
                        'route' => ExampleCreateComponent::SCREEN_ROUTE,
                        'icon' => ExampleCreateComponent::SCREEN_ICON,
                        'display_order' => ExampleCreateComponent::SCREEN_ORDER,
                        'level' => 1,
                        'is_visible' => ExampleCreateComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ExampleCreateComponent::SCREEN_DYNAMIC
                    ],
                    // Editar - Desde Screen (dinámico)
                    [
                        'id' => ExampleEditComponent::SCREEN_ID,
                        'parent_id' => ExampleCrudComponent::MENU_GROUP_ID,
                        'label' => ExampleEditComponent::SCREEN_LABEL,
                        'index_label' => ExampleEditComponent::SCREEN_LABEL,
                        'route' => ExampleEditComponent::SCREEN_ROUTE,
                        'icon' => ExampleEditComponent::SCREEN_ICON,
                        'display_order' => ExampleEditComponent::SCREEN_ORDER,
                        'level' => 1,
                        'is_visible' => ExampleEditComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ExampleEditComponent::SCREEN_DYNAMIC
                    ]
                ]
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // TOOLS CRUD - Herramientas
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => ToolsCrudComponent::MENU_GROUP_ID,
                'parent_id' => null,
                'label' => 'Herramientas',
                'index_label' => 'Herramientas',
                'route' => ToolsCrudComponent::SCREEN_ROUTE,
                'icon' => 'construct-outline',
                'display_order' => 2,
                'level' => 0,
                'is_visible' => true,
                'is_dynamic' => false,
                'children' => [
                    // Ver (Lista)
                    [
                        'id' => ToolsCrudComponent::SCREEN_ID,
                        'parent_id' => ToolsCrudComponent::MENU_GROUP_ID,
                        'label' => ToolsCrudComponent::SCREEN_LABEL,
                        'index_label' => ToolsCrudComponent::SCREEN_LABEL,
                        'route' => ToolsCrudComponent::SCREEN_ROUTE,
                        'icon' => ToolsCrudComponent::SCREEN_ICON,
                        'display_order' => ToolsCrudComponent::SCREEN_ORDER,
                        'level' => 1,
                        'is_visible' => ToolsCrudComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ToolsCrudComponent::SCREEN_DYNAMIC
                    ],
                    // Crear
                    [
                        'id' => ToolsCreateComponent::SCREEN_ID,
                        'parent_id' => ToolsCrudComponent::MENU_GROUP_ID,
                        'label' => ToolsCreateComponent::SCREEN_LABEL,
                        'index_label' => ToolsCreateComponent::SCREEN_LABEL,
                        'route' => ToolsCreateComponent::SCREEN_ROUTE,
                        'icon' => ToolsCreateComponent::SCREEN_ICON,
                        'display_order' => ToolsCreateComponent::SCREEN_ORDER,
                        'level' => 1,
                        'is_visible' => ToolsCreateComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ToolsCreateComponent::SCREEN_DYNAMIC
                    ],
                    // Editar (dinámico)
                    [
                        'id' => ToolsEditComponent::SCREEN_ID,
                        'parent_id' => ToolsCrudComponent::MENU_GROUP_ID,
                        'label' => ToolsEditComponent::SCREEN_LABEL,
                        'index_label' => ToolsEditComponent::SCREEN_LABEL,
                        'route' => ToolsEditComponent::SCREEN_ROUTE,
                        'icon' => ToolsEditComponent::SCREEN_ICON,
                        'display_order' => ToolsEditComponent::SCREEN_ORDER,
                        'level' => 1,
                        'is_visible' => ToolsEditComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ToolsEditComponent::SCREEN_DYNAMIC
                    ]
                ]
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // CONFIGURACIÓN DEL MENÚ - OCULTO pero BUSCABLE
            // Screen de configuración accesible solo por búsqueda o URL directa
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => MenuConfigComponent::SCREEN_ID,
                'parent_id' => MenuConfigComponent::SCREEN_PARENT,
                'label' => MenuConfigComponent::SCREEN_LABEL,
                'index_label' => 'Configuración Menú',
                'route' => MenuConfigComponent::SCREEN_ROUTE,
                'icon' => MenuConfigComponent::SCREEN_ICON,
                'display_order' => MenuConfigComponent::SCREEN_ORDER,
                'level' => 0,
                'is_visible' => MenuConfigComponent::SCREEN_VISIBLE,
                'is_dynamic' => MenuConfigComponent::SCREEN_DYNAMIC
            ]
        ];
    }

    /**
     * Obtener resumen legible del menú para mostrar en consola
     */
    public static function getSummary(): array
    {
        return [
            '- Inicio',
            '- Example CRUD (carpeta)',
            '  - Ver',
            '  - Crear',
            '  - Editar [dinámico]',
            '- Herramientas (carpeta)',
            '  - Ver',
            '  - Nueva Herramienta',
            '  - Editar [dinámico]',
            '- Configuración del Menú [oculto, buscable]'
        ];
    }
    
    /**
     * Obtener estructura del menú desde ScreenRegistry
     * 
     * SCREEN PATTERN:
     * Los screens que implementan ScreenInterface se auto-registran
     * y esta función genera la estructura del menú automáticamente.
     * 
     * USO:
     * 1. Los componentes definen constantes SCREEN_*
     * 2. Se registran en Core/Registry/Screens.php
     * 3. Este método genera la estructura para el menú
     * 
     * NOTA: Requiere que Screens.php haya sido incluido antes.
     * 
     * @return array Estructura del menú generada desde ScreenRegistry
     */
    public static function getFromScreens(): array
    {
        return ScreenRegistry::getMenuStructure();
    }
    
    /**
     * Combina la estructura manual con la de ScreenRegistry
     * 
     * Útil para transición gradual:
     * - Items manuales que aún no son screens
     * - Screens que ya están registrados
     * 
     * @return array Estructura combinada
     */
    public static function getMerged(): array
    {
        // Por ahora, retorna solo la estructura manual
        // En el futuro, se puede combinar con getFromScreens()
        return self::get();
    }
}

