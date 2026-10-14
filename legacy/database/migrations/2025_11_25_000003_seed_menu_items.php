<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\MenuItem;
use Core\Config\MenuStructure;

require_once __DIR__ . '/helpers.php';

/**
 * Seed: Menú inicial
 * 
 * Crea los items de menú iniciales para la aplicación.
 * 
 * IMPORTANTE: La estructura del menú está definida en:
 * Core/Config/MenuStructure.php (fuente única de verdad)
 */
return new class {
    
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
                'level' => $item['level'],
                'is_visible' => $item['is_visible'] ?? true,
                'is_dynamic' => $item['is_dynamic'] ?? false
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
        
        // Insertar todos los items desde la fuente única (MenuStructure)
        foreach (MenuStructure::get() as $item) {
            $insertItem($item);
        }
        
        echo "\n✅ Menú inicial creado exitosamente\n";
        echo "\nEstructura del menú:\n";
        foreach (MenuStructure::getSummary() as $line) {
            echo "  " . $line . "\n";
        }
    }
    
    public function down()
    {
        MenuItem::truncate();
        echo "✓ Menú limpiado\n";
    }
};
