<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create auth_users table
 * 
 * This migration creates the base authentication users table.
 * Includes a default admin user for initial system access.
 */
return new class {
    
    public function up()
    {
        // Create the update_updated_at_column function if it doesn't exist
        createUpdateTimestampFunction();
        
        Capsule::schema()->create('auth_users', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('password', 255);
            $table->string('status', 255);
            $table->string('auth_group_id', 255);
            $table->string('role_id', 255);
            $table->timestamps();
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('auth_users');
        
        // Insert default admin user
        // Password: admin123 (hashed with bcrypt)
        $timestamp = date('Y-m-d H:i:s');
        Capsule::table('auth_users')->insert([
            'name' => 'admin',
            'email' => 'admin@lego.com',
            'password' => '$2a$12$4QQvg9HzVZN5eZRY8kpMyuAVFqO7B5gMkGPqNFLcsKtPPA5KjXwRq',
            'status' => 'active',
            'auth_group_id' => 'ADMINS',
            'role_id' => 'SUPERADMIN',
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ]);
        
        echo "✓ Tabla 'auth_users' creada exitosamente\n";
        echo "✓ Usuario admin creado (email: admin@lego.com, password: admin123)\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('auth_users');
        Capsule::schema()->dropIfExists('auth_users');
        echo "✓ Tabla 'auth_users' eliminada\n";
    }
};
