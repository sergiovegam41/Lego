<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migration: Add menu config item
 * 
 * Agrega el item de Configuración del Menú a la tabla menu_items.
 * Este item es VISIBLE en el menú lateral.
 */
return new class {
    
    public function up()
    {
        // Verificar si ya existe
        $exists = Capsule::table('menu_items')->where('id', 'menu-config')->exists();
        
        if (!$exists) {
            Capsule::table('menu_items')->insert([
                'id' => 'menu-config',
                'parent_id' => null,
                'label' => 'Configuración del Menú',
                'index_label' => 'Configuración Menú',
                'route' => '/component/menu-config',
                'icon' => 'settings-outline',
                'display_order' => 999,
                'level' => 0,
                'is_visible' => true,   // VISIBLE en el menú lateral
                'is_dynamic' => false,  // No requiere contexto
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            echo "✓ Item 'menu-config' agregado a menu_items\n";
        } else {
            echo "→ Item 'menu-config' ya existe, saltando...\n";
        }
    }
    
    public function down()
    {
        Capsule::table('menu_items')->where('id', 'menu-config')->delete();
        echo "✓ Item 'menu-config' eliminado de menu_items\n";
    }
};

