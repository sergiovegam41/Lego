<?php

namespace Core\Commands;

use App\Models\MenuItem;

/**
 * ConfigResetCommand - Reset Lego configuration to defaults
 * 
 * Usage:
 *   php lego config:reset              # Reset all (with confirmation)
 *   php lego config:reset --menu       # Reset only menu
 *   php lego config:reset --force      # Skip confirmation
 */
class ConfigResetCommand extends CoreCommand
{
    protected string $name = 'config:reset';
    protected string $description = 'Reset Lego configuration to defaults';
    protected string $signature = 'config:reset [--menu] [--force]';

    /**
     * Definición jerárquica del menú (misma que en seed)
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
        ],
        [
            'id' => 'todo',
            'parent_id' => null,
            'label' => 'TODO List',
            'index_label' => 'Ver',
            'route' => '/component/todo',
            'icon' => 'checkbox-outline',
            'display_order' => 2,
            'level' => 0
        ]
    ];

    public function execute(): bool
    {
        $menuOnly = $this->option('menu');
        $force = $this->option('force');
        
        // Determinar qué resetear
        $resetMenu = $menuOnly || !$menuOnly; // Si --menu o sin flags
        
        // Confirmación
        if (!$force) {
            $this->warning("⚠️  ADVERTENCIA: Esta acción eliminará y recreará la configuración de Lego\n");
            
            if ($resetMenu) {
                $this->line("  - Menú de navegación");
            }
            
            $this->line("");
            $confirm = $this->ask("¿Estás seguro? (yes/no): ");
            
            if (strtolower($confirm) !== 'yes') {
                $this->info("Operación cancelada");
                return true;
            }
        }
        
        $this->info("\n🔄 Reseteando configuración de Lego...\n");
        
        // Resetear menú
        if ($resetMenu) {
            if (!$this->resetMenu()) {
                return false;
            }
        }
        
        $this->success("\n✅ Configuración reseteada exitosamente!");
        return true;
    }

    /**
     * Resetear menú a valores por defecto
     */
    private function resetMenu(): bool
    {
        try {
            $this->info("📋 Reseteando menú...");
            
            // Limpiar tabla
            MenuItem::truncate();
            $this->line("  ✓ Tabla limpiada");
            
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
                $indent = str_repeat('  ', $level + 1);
                $this->line("{$indent}✓ {$item['label']}");
                
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
            
            $this->success("\n  ✅ Menú reseteado correctamente");
            $this->line("\n  Estructura del menú:");
            $this->line("    - Inicio");
            $this->line("    - Example CRUD");
            $this->line("      - Ver");
            $this->line("      - Crear");
            $this->line("    - TODO List");
            
            return true;
            
        } catch (\Exception $e) {
            $this->error("  ❌ Error reseteando menú: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Preguntar al usuario (helper)
     */
    private function ask(string $question): string
    {
        echo $question;
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        return trim($line);
    }
}
