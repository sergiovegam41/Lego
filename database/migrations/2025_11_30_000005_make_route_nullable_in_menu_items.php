<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Make route nullable in menu_items
 * 
 * Permite crear items de tipo "grupo" (carpeta) sin ruta
 */
return new class {
    
    public function up()
    {
        // En PostgreSQL, cambiar columna a nullable
        Capsule::statement('ALTER TABLE menu_items ALTER COLUMN route DROP NOT NULL');
        
        echo "✓ Columna 'route' ahora es nullable en 'menu_items'\n";
    }
    
    public function down()
    {
        // Revertir: hacer route NOT NULL de nuevo
        // Primero actualizar todos los null a un valor por defecto
        Capsule::table('menu_items')
            ->whereNull('route')
            ->update(['route' => '/component/placeholder']);
        
        Capsule::statement('ALTER TABLE menu_items ALTER COLUMN route SET NOT NULL');
        
        echo "✓ Columna 'route' ahora es NOT NULL en 'menu_items'\n";
    }
};

