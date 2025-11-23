<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Remove SKU column from example_crud table
 * 
 * Removes the SKU field as it's not needed for the example CRUD.
 */
return new class {
    
    public function up()
    {
        if (columnExists('example_crud', 'sku')) {
            Capsule::schema()->table('example_crud', function ($table) {
                $table->dropColumn('sku');
            });
            
            // Drop the index if it exists
            try {
                Capsule::statement('DROP INDEX IF EXISTS idx_example_crud_sku');
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }
            
            echo "✓ Columna 'sku' eliminada de 'example_crud'\n";
        } else {
            echo "⏭️  Columna 'sku' no existe en 'example_crud'\n";
        }
    }
    
    public function down()
    {
        Capsule::schema()->table('example_crud', function ($table) {
            $table->string('sku', 100)->nullable()->after('name');
            $table->index('sku');
        });
        
        echo "✓ Columna 'sku' restaurada en 'example_crud'\n";
    }
};
