<?php

namespace Core\Config;

/**
 * MenuStructure - Fuente única de verdad para el menú de navegación
 * 
 * IMPORTANTE: Esta es la ÚNICA definición del menú del sistema.
 * No duplicar esta estructura en otros archivos.
 * 
 * Usado por:
 * - database/migrations/2025_11_25_000003_seed_menu_items.php
 * - Core/Commands/ConfigResetCommand.php
 * 
 * TIPOS DE ITEMS:
 * 
 * 1. NORMAL (is_visible=true, is_dynamic=false)
 *    - Aparece en el menú lateral
 *    - Aparece en búsquedas
 *    - Ejemplo: "Inicio", "Ver", "Crear"
 * 
 * 2. OCULTO BUSCABLE (is_visible=false, is_dynamic=false)
 *    - NO aparece en el menú lateral
 *    - SÍ aparece en búsquedas
 *    - Ejemplo: Configuración avanzada, páginas secundarias
 * 
 * 3. DINÁMICO/FANTASMA (is_visible=false, is_dynamic=true)
 *    - NO aparece en el menú lateral
 *    - NO aparece en búsquedas (no tiene sentido sin contexto)
 *    - Se activa programáticamente cuando hay contexto
 *    - Ejemplo: "Editar" (requiere saber QUÉ editar)
 * 
 * ESTRUCTURA:
 * - Inicio (normal)
 * - Example CRUD (carpeta)
 *   - Ver (normal)
 *   - Crear (normal)
 *   - Editar (dinámico - requiere contexto)
 */
class MenuStructure
{
    /**
     * Obtener la estructura completa del menú
     */
    public static function get(): array
    {
        return [
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
            [
                'id' => 'example-crud',
                'parent_id' => null,
                'label' => 'Example CRUD',
                'index_label' => 'Example CRUD',
                'route' => '/component/example-crud',
                'icon' => 'cube-outline',
                'display_order' => 1,
                'level' => 0,
                'is_visible' => true,
                'is_dynamic' => false,
                'children' => [
                    [
                        'id' => 'example-crud-list',
                        'parent_id' => 'example-crud',
                        'label' => 'Ver',
                        'index_label' => 'Ver',
                        'route' => '/component/example-crud',
                        'icon' => 'list-outline',
                        'display_order' => 0,
                        'level' => 1,
                        'is_visible' => true,
                        'is_dynamic' => false
                    ],
                    [
                        'id' => 'example-crud-create',
                        'parent_id' => 'example-crud',
                        'label' => 'Crear',
                        'index_label' => 'Crear',
                        'route' => '/component/example-crud/create',
                        'icon' => 'add-circle-outline',
                        'display_order' => 1,
                        'level' => 1,
                        'is_visible' => true,
                        'is_dynamic' => false
                    ],
                    [
                        'id' => 'example-crud-edit',
                        'parent_id' => 'example-crud',
                        'label' => 'Editar',
                        'index_label' => 'Editar',
                        'route' => '/component/example-crud/edit',
                        'icon' => 'create-outline',
                        'display_order' => 2,
                        'level' => 1,
                        'is_visible' => false,   // No aparece en menú
                        'is_dynamic' => true     // No aparece en búsquedas, requiere contexto
                    ]
                ]
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
            '  - Editar [dinámico]'
        ];
    }
}

