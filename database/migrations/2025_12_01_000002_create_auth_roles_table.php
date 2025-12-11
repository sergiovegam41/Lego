<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create auth_roles table
 * 
 * Catálogo de roles disponibles por grupo de autenticación
 * Permite definir roles sin necesidad de tener usuarios con esos roles
 */
return new class {
    
    public function up()
    {
        Capsule::schema()->create('auth_roles', function ($table) {
            $table->id();
            $table->string('auth_group_id', 255); // ADMINS, APIS, etc.
            $table->string('role_id', 255); // SUPERADMIN, ADMIN, etc.
            $table->string('role_name', 255)->nullable(); // Nombre descriptivo opcional
            $table->text('description')->nullable(); // Descripción del rol
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['auth_group_id', 'role_id']);
            $table->index('auth_group_id');
            $table->index('is_active');
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('auth_roles');
        
        // Insertar roles por defecto para ADMINS
        $timestamp = date('Y-m-d H:i:s');
        Capsule::table('auth_roles')->insert([
            [
                'auth_group_id' => 'ADMINS',
                'role_id' => 'SUPERADMIN',
                'role_name' => 'Super Administrador',
                'description' => 'Acceso completo al sistema',
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ],
            [
                'auth_group_id' => 'ADMINS',
                'role_id' => 'ADMIN',
                'role_name' => 'Administrador',
                'description' => 'Administrador con permisos limitados',
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]
        ]);
        
        echo "✓ Tabla 'auth_roles' creada exitosamente\n";
        echo "✓ Roles por defecto insertados\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('auth_roles');
        Capsule::schema()->dropIfExists('auth_roles');
        echo "✓ Tabla 'auth_roles' eliminada\n";
    }
};

