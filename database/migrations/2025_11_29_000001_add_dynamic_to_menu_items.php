<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Add is_dynamic column to menu_items
 * 
 * PROPÓSITO:
 * Distinguir entre items ocultos que SÍ son buscables vs items dinámicos/fantasma.
 * 
 * TIPOS DE ITEMS:
 * 
 * 1. NORMAL (is_visible=true, is_dynamic=false)
 *    - Aparece en menú lateral
 *    - Aparece en búsquedas
 *    - Ejemplo: "Inicio", "Ver", "Crear"
 * 
 * 2. OCULTO BUSCABLE (is_visible=false, is_dynamic=false)
 *    - NO aparece en menú lateral
 *    - SÍ aparece en búsquedas
 *    - Ejemplo: Configuración avanzada, páginas secundarias
 * 
 * 3. DINÁMICO/FANTASMA (is_visible=false, is_dynamic=true)
 *    - NO aparece en menú lateral por defecto
 *    - NO aparece en búsquedas (no tiene sentido sin contexto)
 *    - Se activa programáticamente con contexto
 *    - Ejemplo: "Editar Producto #5" (se activa al hacer clic en editar)
 */
return new class {
    
    public function up()
    {
        if (!Capsule::schema()->hasColumn('menu_items', 'is_dynamic')) {
            Capsule::schema()->table('menu_items', function ($table) {
                $table->boolean('is_dynamic')->default(false)->after('is_visible');
            });
            
            Capsule::schema()->table('menu_items', function ($table) {
                $table->index('is_dynamic');
            });
            
            echo "✓ Columna 'is_dynamic' agregada a menu_items\n";
        } else {
            echo "⚠ Columna 'is_dynamic' ya existe, saltando...\n";
        }
    }
    
    public function down()
    {
        if (Capsule::schema()->hasColumn('menu_items', 'is_dynamic')) {
            Capsule::schema()->table('menu_items', function ($table) {
                $table->dropIndex(['is_dynamic']);
                $table->dropColumn('is_dynamic');
            });
            echo "✓ Columna 'is_dynamic' eliminada de menu_items\n";
        }
    }
};

