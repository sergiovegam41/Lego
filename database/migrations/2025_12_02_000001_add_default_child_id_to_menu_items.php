<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Add default_child_id to menu_items table
 * 
 * PROPÓSITO:
 * Permite especificar qué hijo se debe abrir por defecto cuando se hace clic en un grupo padre.
 * 
 * COMPORTAMIENTO:
 * - Si un item tiene default_child_id configurado, al hacer clic en el grupo se abrirá ese hijo específico
 * - Si default_child_id es NULL, se usa el comportamiento por defecto (primer hijo con ruta válida)
 * - Solo aplica a items que tienen hijos (grupos)
 * 
 * EJEMPLO:
 * - Grupo "Gestión de Roles" (roles-config) tiene default_child_id = "roles-config-list"
 * - Al hacer clic en "Gestión de Roles", se abrirá automáticamente "Ver Roles" (roles-config-list)
 */
return new class {
    
    public function up()
    {
        // Verificar si la columna ya existe
        if (Capsule::schema()->hasColumn('menu_items', 'default_child_id')) {
            echo "✓ Columna 'default_child_id' ya existe en 'menu_items'\n";
            return;
        }

        Capsule::schema()->table('menu_items', function ($table) {
            $table->string('default_child_id', 50)->nullable()->after('allowed_roles');
        });
        
        // Agregar índice para mejorar performance en búsquedas
        Capsule::schema()->table('menu_items', function ($table) {
            $table->index('default_child_id');
        });
        
        echo "✓ Columna 'default_child_id' agregada a 'menu_items'\n";
    }
    
    public function down()
    {
        if (Capsule::schema()->hasColumn('menu_items', 'default_child_id')) {
            Capsule::schema()->table('menu_items', function ($table) {
                $table->dropIndex(['default_child_id']);
                $table->dropColumn('default_child_id');
            });
            echo "✓ Columna 'default_child_id' eliminada de 'menu_items'\n";
        }
    }
};

