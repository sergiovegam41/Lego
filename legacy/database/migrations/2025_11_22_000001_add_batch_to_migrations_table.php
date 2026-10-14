<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Add batch column to migrations table (Transition Migration)
 * 
 * This is a special transition migration that upgrades the old migrations table
 * to the new format with batch tracking support.
 * 
 * IMPORTANT: This migration only runs if:
 * - The migrations table exists (from old system)
 * - The batch column doesn't exist yet
 */
return new class {
    
    public function up()
    {
        // Check if migrations table exists and batch column doesn't
        if (tableExists('migrations') && !columnExists('migrations', 'batch')) {
            // Add batch column
            Capsule::schema()->table('migrations', function ($table) {
                $table->integer('batch')->default(1);
                $table->timestamps();
            });
            
            // Update existing records to have batch = 1
            Capsule::statement("UPDATE migrations SET batch = 1 WHERE batch IS NULL");
            
            // Update created_at and updated_at for existing records
            Capsule::statement("
                UPDATE migrations 
                SET created_at = NOW(), updated_at = NOW() 
                WHERE created_at IS NULL
            ");
            
            echo "✓ Columna 'batch' agregada a tabla 'migrations' (migración de transición)\n";
            echo "✓ Registros existentes actualizados con batch = 1\n";
        } else {
            echo "⏭️  Tabla 'migrations' ya tiene columna 'batch' o no existe\n";
        }
    }
    
    public function down()
    {
        if (columnExists('migrations', 'batch')) {
            Capsule::schema()->table('migrations', function ($table) {
                $table->dropColumn(['batch', 'created_at', 'updated_at']);
            });
            
            echo "✓ Columna 'batch' eliminada de tabla 'migrations'\n";
        }
    }
};
