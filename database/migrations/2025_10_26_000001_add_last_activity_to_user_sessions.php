<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Add last_activity_at to auth_user_sessions
 * 
 * Adds last_activity_at column to track user activity for token extension.
 * Converted from: 20251026_add_last_activity_to_user_sessions.sql
 */
return new class {
    
    public function up()
    {
        // Check if column already exists
        if (!columnExists('auth_user_sessions', 'last_activity_at')) {
            Capsule::schema()->table('auth_user_sessions', function ($table) {
                $table->timestamp('last_activity_at')->nullable()->default(Capsule::raw('NOW()'));
            });
            
            // Update existing records
            Capsule::statement("
                UPDATE auth_user_sessions
                SET last_activity_at = COALESCE(updated_at, created_at, NOW())
                WHERE last_activity_at IS NULL
            ");
            
            // Add comment to document the column purpose
            Capsule::statement("
                COMMENT ON COLUMN auth_user_sessions.last_activity_at 
                IS 'Tracks the last user activity to enable automatic token extension'
            ");
            
            echo "✓ Columna 'last_activity_at' agregada a 'auth_user_sessions'\n";
        } else {
            echo "⏭️  Columna 'last_activity_at' ya existe en 'auth_user_sessions'\n";
        }
    }
    
    public function down()
    {
        Capsule::schema()->table('auth_user_sessions', function ($table) {
            $table->dropColumn('last_activity_at');
        });
        
        echo "✓ Columna 'last_activity_at' eliminada de 'auth_user_sessions'\n";
    }
};
