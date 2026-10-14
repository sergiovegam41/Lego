<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create auth_user_sessions table
 * 
 * This migration creates the user sessions table for managing
 * authentication tokens and device sessions.
 */
return new class {
    
    public function up()
    {
        Capsule::schema()->create('auth_user_sessions', function ($table) {
            $table->id();
            $table->integer('auth_user_id');
            $table->string('device_id', 255);
            $table->text('refresh_token');
            $table->text('access_token');
            $table->text('firebase_token')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('refresh_expires_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint: one session per user per device
            $table->unique(['auth_user_id', 'device_id']);
            
            // Indexes for performance
            $table->index('auth_user_id');
            $table->index('device_id');
            $table->index('is_active');
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('auth_user_sessions');
        
        echo "✓ Tabla 'auth_user_sessions' creada exitosamente\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('auth_user_sessions');
        Capsule::schema()->dropIfExists('auth_user_sessions');
        echo "✓ Tabla 'auth_user_sessions' eliminada\n";
    }
};
