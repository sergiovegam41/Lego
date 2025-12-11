<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Add allowed_roles to menu_items table
 * 
 * Agrega campo para almacenar lista de roles permitidos para cada item de menú
 * Formato: JSON array de strings, ej: ["SUPERADMIN", "ADMIN"]
 */
return new class {
    
    public function up()
    {
        // Verificar si la columna ya existe
        if (Capsule::schema()->hasColumn('menu_items', 'allowed_roles')) {
            echo "✓ Columna 'allowed_roles' ya existe en 'menu_items'\n";
            return;
        }

        Capsule::schema()->table('menu_items', function ($table) {
            $table->text('allowed_roles')->nullable()->after('is_dynamic');
        });
        
        echo "✓ Columna 'allowed_roles' agregada a 'menu_items'\n";
    }
    
    public function down()
    {
        if (Capsule::schema()->hasColumn('menu_items', 'allowed_roles')) {
            Capsule::schema()->table('menu_items', function ($table) {
                $table->dropColumn('allowed_roles');
            });
            echo "✓ Columna 'allowed_roles' eliminada de 'menu_items'\n";
        }
    }
};


