<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\MenuItem;

require_once __DIR__ . '/helpers.php';

/**
 * Seed: Menú inicial
 * 
 * Crea los items de menú iniciales para la aplicación
 */
return new class {
    
    /**
     * Definición jerárquica del menú
     * Estructura similar al formato original de MainComponent
     */
    private const MENU_STRUCTURE = [
        [
            'id' => 'inicio',
            'parent_id' => null,
            'label' => 'Inicio',
            'index_label' => 'Inicio',
            'route' => '/component/inicio',
            'icon' => 'home-outline',
            'display_order' => 0,
            'level' => 0
        ],
        [
            'id' => 'example-crud',
            'parent_id' => null,
            'label' => 'Example CRUD',
            'index_label' => 'Ver',
            'route' => '/component/example-crud',
            'icon' => 'cube-outline',
            'display_order' => 1,
            'level' => 0,
            'children' => [
                [
                    'id' => 'example-crud-create',
                    'parent_id' => 'example-crud',
                    'label' => 'Crear',
                    'index_label' => 'Crear',
                    'route' => '/component/example-crud/create',
                    'icon' => 'add-circle-outline',
                    'display_order' => 1,
                    'level' => 1
                ]
            ]
        ]
    ];
    
    public function up()
    {
        // Limpiar tabla
        MenuItem::truncate();
        echo "✓ Tabla limpiada\n\n";
        
        echo "🌱 Poblando menú...\n";
        
        // Función recursiva para insertar items y sus hijos
        $insertItem = function($item, $level = 0) use (&$insertItem) {
            MenuItem::create([
                'id' => $item['id'],
                'parent_id' => $item['parent_id'],
                'label' => $item['label'],
                'index_label' => $item['index_label'],
                'route' => $item['route'],
                'icon' => $item['icon'],
                'display_order' => $item['display_order'],
                'level' => $item['level']
            ]);
            
            // Mostrar jerarquía visualmente
            $indent = str_repeat('  ', $level);
            $parentInfo = $item['parent_id'] ? " (hijo de {$item['parent_id']})" : " (raíz)";
            echo "{$indent}✓ {$item['label']}{$parentInfo}\n";
            
            // Insertar hijos si existen
            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    $insertItem($child, $level + 1);
                }
            }
        };
        
        // Insertar todos los items desde la estructura
        foreach (self::MENU_STRUCTURE as $item) {
            $insertItem($item);
        }
        
        echo "\n✅ Menú inicial creado exitosamente\n";
        echo "\nEstructura del menú:\n";
        echo "  - Inicio\n";
        echo "  - Example CRUD\n";
        echo "    - Ver\n";
        echo "    - Crear\n";
        echo "  - TODO List\n";
    }
    
    public function down()
    {
        MenuItem::truncate();
        echo "✓ Menú limpiado\n";
    }
};
