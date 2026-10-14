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
use Components\App\RolesConfig\RolesConfigComponent;
use Components\App\RolesConfig\Childs\RolesConfigCreate\RolesConfigCreateComponent;
use Components\App\RolesConfig\Childs\RolesConfigEdit\RolesConfigEditComponent;
use Components\App\AuthGroupsConfig\AuthGroupsConfigComponent;
use Components\App\AuthGroupsConfig\Childs\AuthGroupsConfigCreate\AuthGroupsConfigCreateComponent;
use Components\App\AuthGroupsConfig\Childs\AuthGroupsConfigEdit\AuthGroupsConfigEditComponent;
use Components\App\UsersConfig\UsersConfigComponent;
use Components\App\UsersConfig\Childs\UsersConfigCreate\UsersConfigCreateComponent;

/**
 * MenuStructure - Fuente única de verdad para el menú de navegación
 * 
 * SCREEN PATTERN:
 * Los IDs, labels, iconos y rutas vienen DIRECTAMENTE de las constantes
 * definidas en cada Screen. Esto elimina la duplicación de strings.
 * 
 * FILOSOFÍA LEGO:
 * - Los IDs de grupos se derivan proceduralmente desde SCREEN_ROUTE usando getGroupIdFromRoute()
 * - parent_id y level se deducen automáticamente desde la jerarquía anidada (children)
 * - Todo es procedural: la BD es la fuente de verdad
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
     * FILOSOFÍA LEGO:
     * - parent_id y level se deducen automáticamente desde la jerarquía de children
     * - No se definen explícitamente - es completamente procedural
     * - La jerarquía anidada (children) define quién es padre de quién
     * 
     * USA CONSTANTES DE SCREENS - No strings duplicados
     */
    public static function get(): array
    {
        $structure = [
            // ═══════════════════════════════════════════════════════════════
            // INICIO (aún no es Screen, usa strings)
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => 'inicio',
                'label' => 'Inicio',
                'index_label' => 'Inicio',
                'route' => '/component/inicio',
                'icon' => 'home-outline',
                'display_order' => 0,
                'is_visible' => true,
                'is_dynamic' => false
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // EXAMPLE CRUD - Grupo del menú (carpeta)
            // El grupo NO es una screen, es solo un contenedor de menú
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => self::getGroupIdFromRoute(ExampleCrudComponent::SCREEN_ROUTE),
                'label' => 'Example CRUD',
                'index_label' => 'Example CRUD',
                'route' => ExampleCrudComponent::SCREEN_ROUTE,
                'icon' => 'cube-outline',
                'display_order' => 1,
                'is_visible' => true,
                'is_dynamic' => false,
                'children' => [
                    // Ver (Lista) - Desde Screen
                    [
                        'id' => ExampleCrudComponent::SCREEN_ID,
                        'label' => ExampleCrudComponent::SCREEN_LABEL,
                        'index_label' => ExampleCrudComponent::SCREEN_LABEL,
                        'route' => ExampleCrudComponent::SCREEN_ROUTE,
                        'icon' => ExampleCrudComponent::SCREEN_ICON,
                        'display_order' => ExampleCrudComponent::SCREEN_ORDER,
                        'is_visible' => ExampleCrudComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ExampleCrudComponent::SCREEN_DYNAMIC
                    ],
                    // Crear - Desde Screen
                    [
                        'id' => ExampleCreateComponent::SCREEN_ID,
                        'label' => ExampleCreateComponent::SCREEN_LABEL,
                        'index_label' => ExampleCreateComponent::SCREEN_LABEL,
                        'route' => ExampleCreateComponent::SCREEN_ROUTE,
                        'icon' => ExampleCreateComponent::SCREEN_ICON,
                        'display_order' => ExampleCreateComponent::SCREEN_ORDER,
                        'is_visible' => ExampleCreateComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ExampleCreateComponent::SCREEN_DYNAMIC
                    ],
                    // Editar - Desde Screen (dinámico)
                    [
                        'id' => ExampleEditComponent::SCREEN_ID,
                        'label' => ExampleEditComponent::SCREEN_LABEL,
                        'index_label' => ExampleEditComponent::SCREEN_LABEL,
                        'route' => ExampleEditComponent::SCREEN_ROUTE,
                        'icon' => ExampleEditComponent::SCREEN_ICON,
                        'display_order' => ExampleEditComponent::SCREEN_ORDER,
                        'is_visible' => ExampleEditComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ExampleEditComponent::SCREEN_DYNAMIC
                    ]
                ]
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // TOOLS CRUD - Herramientas
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => self::getGroupIdFromRoute(ToolsCrudComponent::SCREEN_ROUTE),
                'label' => 'Herramientas',
                'index_label' => 'Herramientas',
                'route' => ToolsCrudComponent::SCREEN_ROUTE,
                'icon' => 'construct-outline',
                'display_order' => 2,
                'is_visible' => true,
                'is_dynamic' => false,
                'children' => [
                    // Ver (Lista)
                    [
                        'id' => ToolsCrudComponent::SCREEN_ID,
                        'label' => ToolsCrudComponent::SCREEN_LABEL,
                        'index_label' => ToolsCrudComponent::SCREEN_LABEL,
                        'route' => ToolsCrudComponent::SCREEN_ROUTE,
                        'icon' => ToolsCrudComponent::SCREEN_ICON,
                        'display_order' => ToolsCrudComponent::SCREEN_ORDER,
                        'is_visible' => ToolsCrudComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ToolsCrudComponent::SCREEN_DYNAMIC
                    ],
                    // Crear
                    [
                        'id' => ToolsCreateComponent::SCREEN_ID,
                        'label' => ToolsCreateComponent::SCREEN_LABEL,
                        'index_label' => ToolsCreateComponent::SCREEN_LABEL,
                        'route' => ToolsCreateComponent::SCREEN_ROUTE,
                        'icon' => ToolsCreateComponent::SCREEN_ICON,
                        'display_order' => ToolsCreateComponent::SCREEN_ORDER,
                        'is_visible' => ToolsCreateComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ToolsCreateComponent::SCREEN_DYNAMIC
                    ],
                    // Editar (dinámico)
                    [
                        'id' => ToolsEditComponent::SCREEN_ID,
                        'label' => ToolsEditComponent::SCREEN_LABEL,
                        'index_label' => ToolsEditComponent::SCREEN_LABEL,
                        'route' => ToolsEditComponent::SCREEN_ROUTE,
                        'icon' => ToolsEditComponent::SCREEN_ICON,
                        'display_order' => ToolsEditComponent::SCREEN_ORDER,
                        'is_visible' => ToolsEditComponent::SCREEN_VISIBLE,
                        'is_dynamic' => ToolsEditComponent::SCREEN_DYNAMIC
                    ]
                ]
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // GESTIÓN DE ROLES - Grupo del menú (carpeta) - OCULTO
            // Aparece solo en popover de configuración y búsqueda
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => self::getGroupIdFromRoute(RolesConfigComponent::SCREEN_ROUTE),
                'label' => 'Gestión de Roles',
                'index_label' => 'Gestión de Roles',
                'route' => RolesConfigComponent::SCREEN_ROUTE,
                'icon' => 'shield-outline',
                'display_order' => 998,
                'is_visible' => false, // Oculto: aparece solo en popover de configuración y búsqueda
                'is_dynamic' => false,
                'children' => [
                    // Ver (Lista)
                    [
                        'id' => RolesConfigComponent::SCREEN_ID,
                        'label' => RolesConfigComponent::SCREEN_LABEL,
                        'index_label' => RolesConfigComponent::SCREEN_LABEL,
                        'route' => RolesConfigComponent::SCREEN_ROUTE,
                        'icon' => RolesConfigComponent::SCREEN_ICON,
                        'display_order' => RolesConfigComponent::SCREEN_ORDER,
                        'is_visible' => RolesConfigComponent::SCREEN_VISIBLE,
                        'is_dynamic' => RolesConfigComponent::SCREEN_DYNAMIC
                    ],
                    // Crear
                    [
                        'id' => RolesConfigCreateComponent::SCREEN_ID,
                        'label' => RolesConfigCreateComponent::SCREEN_LABEL,
                        'index_label' => RolesConfigCreateComponent::SCREEN_LABEL,
                        'route' => RolesConfigCreateComponent::SCREEN_ROUTE,
                        'icon' => RolesConfigCreateComponent::SCREEN_ICON,
                        'display_order' => RolesConfigCreateComponent::SCREEN_ORDER,
                        'is_visible' => RolesConfigCreateComponent::SCREEN_VISIBLE,
                        'is_dynamic' => RolesConfigCreateComponent::SCREEN_DYNAMIC
                    ],
                    // Editar (dinámico)
                    [
                        'id' => RolesConfigEditComponent::SCREEN_ID,
                        'label' => RolesConfigEditComponent::SCREEN_LABEL,
                        'index_label' => RolesConfigEditComponent::SCREEN_LABEL,
                        'route' => RolesConfigEditComponent::SCREEN_ROUTE,
                        'icon' => RolesConfigEditComponent::SCREEN_ICON,
                        'display_order' => RolesConfigEditComponent::SCREEN_ORDER,
                        'is_visible' => RolesConfigEditComponent::SCREEN_VISIBLE,
                        'is_dynamic' => RolesConfigEditComponent::SCREEN_DYNAMIC
                    ],
                    // ═══════════════════════════════════════════════════════════════
                    // GRUPOS DE AUTENTICACIÓN - Subcarpeta dentro de Gestión de Roles
                    // ═══════════════════════════════════════════════════════════════
                    [
                        'id' => self::getGroupIdFromRoute(AuthGroupsConfigComponent::SCREEN_ROUTE),
                        'label' => 'Grupos',
                        'index_label' => 'Grupos',
                        'route' => AuthGroupsConfigComponent::SCREEN_ROUTE,
                        'icon' => 'folder-outline',
                        'display_order' => 20,
                        'is_visible' => true,
                        'is_dynamic' => false,
                        'children' => [
                            // Ver Grupos
                            [
                                'id' => AuthGroupsConfigComponent::SCREEN_ID,
                                'label' => AuthGroupsConfigComponent::SCREEN_LABEL,
                                'index_label' => AuthGroupsConfigComponent::SCREEN_LABEL,
                                'route' => AuthGroupsConfigComponent::SCREEN_ROUTE,
                                'icon' => AuthGroupsConfigComponent::SCREEN_ICON,
                                'display_order' => AuthGroupsConfigComponent::SCREEN_ORDER,
                                'is_visible' => AuthGroupsConfigComponent::SCREEN_VISIBLE,
                                'is_dynamic' => AuthGroupsConfigComponent::SCREEN_DYNAMIC
                            ],
                            // Crear Grupo
                            [
                                'id' => AuthGroupsConfigCreateComponent::SCREEN_ID,
                                'label' => AuthGroupsConfigCreateComponent::SCREEN_LABEL,
                                'index_label' => AuthGroupsConfigCreateComponent::SCREEN_LABEL,
                                'route' => AuthGroupsConfigCreateComponent::SCREEN_ROUTE,
                                'icon' => AuthGroupsConfigCreateComponent::SCREEN_ICON,
                                'display_order' => AuthGroupsConfigCreateComponent::SCREEN_ORDER,
                                'is_visible' => AuthGroupsConfigCreateComponent::SCREEN_VISIBLE,
                                'is_dynamic' => AuthGroupsConfigCreateComponent::SCREEN_DYNAMIC
                            ],
                            // Editar Grupo
                            [
                                'id' => AuthGroupsConfigEditComponent::SCREEN_ID,
                                'label' => AuthGroupsConfigEditComponent::SCREEN_LABEL,
                                'index_label' => AuthGroupsConfigEditComponent::SCREEN_LABEL,
                                'route' => AuthGroupsConfigEditComponent::SCREEN_ROUTE,
                                'icon' => AuthGroupsConfigEditComponent::SCREEN_ICON,
                                'display_order' => AuthGroupsConfigEditComponent::SCREEN_ORDER,
                                'is_visible' => AuthGroupsConfigEditComponent::SCREEN_VISIBLE,
                                'is_dynamic' => AuthGroupsConfigEditComponent::SCREEN_DYNAMIC
                            ]
                        ]
                    ]
                ]
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // GESTIÓN DE USUARIOS - Grupo del menú (carpeta) - OCULTO
            // Aparece solo en popover de configuración y búsqueda
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => self::getGroupIdFromRoute(UsersConfigComponent::SCREEN_ROUTE),
                'label' => 'Gestión de Usuarios',
                'index_label' => 'Gestión de Usuarios',
                'route' => UsersConfigComponent::SCREEN_ROUTE,
                'icon' => 'people-outline',
                'display_order' => 997,
                'is_visible' => false, // Oculto: aparece solo en popover de configuración y búsqueda
                'is_dynamic' => false,
                'children' => [
                    // Ver (Lista)
                    [
                        'id' => UsersConfigComponent::SCREEN_ID,
                        'label' => UsersConfigComponent::SCREEN_LABEL,
                        'index_label' => UsersConfigComponent::SCREEN_LABEL,
                        'route' => UsersConfigComponent::SCREEN_ROUTE,
                        'icon' => UsersConfigComponent::SCREEN_ICON,
                        'display_order' => UsersConfigComponent::SCREEN_ORDER,
                        'is_visible' => UsersConfigComponent::SCREEN_VISIBLE,
                        'is_dynamic' => UsersConfigComponent::SCREEN_DYNAMIC
                    ],
                    // Crear
                    [
                        'id' => UsersConfigCreateComponent::SCREEN_ID,
                        'label' => UsersConfigCreateComponent::SCREEN_LABEL,
                        'index_label' => UsersConfigCreateComponent::SCREEN_LABEL,
                        'route' => UsersConfigCreateComponent::SCREEN_ROUTE,
                        'icon' => UsersConfigCreateComponent::SCREEN_ICON,
                        'display_order' => UsersConfigCreateComponent::SCREEN_ORDER,
                        'is_visible' => UsersConfigCreateComponent::SCREEN_VISIBLE,
                        'is_dynamic' => UsersConfigCreateComponent::SCREEN_DYNAMIC
                    ]
                ]
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // CONFIGURACIÓN DEL MENÚ - OCULTO pero BUSCABLE
            // Screen de configuración accesible solo por búsqueda o URL directa
            // ═══════════════════════════════════════════════════════════════
            [
                'id' => MenuConfigComponent::SCREEN_ID,
                'label' => MenuConfigComponent::SCREEN_LABEL,
                'index_label' => 'Configuración Menú',
                'route' => MenuConfigComponent::SCREEN_ROUTE,
                'icon' => MenuConfigComponent::SCREEN_ICON,
                'display_order' => MenuConfigComponent::SCREEN_ORDER,
                'is_visible' => MenuConfigComponent::SCREEN_VISIBLE,
                'is_dynamic' => MenuConfigComponent::SCREEN_DYNAMIC
            ]
        ];

        // Procesar la estructura para deducir parent_id y level desde la jerarquía
        return self::processMenuItems($structure);
    }

    /**
     * Obtiene el ID del grupo desde la ruta del componente.
     * 
     * Ejemplo: '/component/example-crud' -> 'example-crud'
     * 
     * @param string $route Ruta del componente
     * @return string ID del grupo
     */
    private static function getGroupIdFromRoute(string $route): string
    {
        $path = str_replace('/component/', '', $route);
        $parts = explode('/', trim($path, '/'));
        return $parts[0] ?? '';
    }

    /**
     * Procesa recursivamente los items del menú para deducir parent_id y level
     * desde la jerarquía anidada (children).
     * 
     * La jerarquía del JSON define quién es padre de quién.
     * 
     * @param array $items Array de items del menú
     * @param string|null $parentId ID del padre (null para items raíz)
     * @param int $level Nivel de anidación (0 para items raíz)
     * @return array Items procesados con parent_id y level asignados
     */
    private static function processMenuItems(array $items, ?string $parentId = null, int $level = 0): array
    {
        foreach ($items as &$item) {
            // Asignar parent_id y level desde la jerarquía
            $item['parent_id'] = $parentId;
            $item['level'] = $level;

            // Procesar recursivamente los children
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = self::processMenuItems($item['children'], $item['id'], $level + 1);
            }
        }
        return $items;
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
            '- Gestión de Usuarios',
            '- Gestión de Roles',
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

