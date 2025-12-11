<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create auth_groups table
 * 
 * Catálogo de grupos de autenticación disponibles
 */
return new class {
    
    public function up()
    {
        Capsule::schema()->create('auth_groups', function ($table) {
            $table->string('id', 255)->primary(); // ADMINS, APIS, etc.
            $table->string('name', 255); // Nombre descriptivo
            $table->text('description')->nullable(); // Descripción del grupo
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('auth_groups');
        
        // Insertar grupos por defecto
        $timestamp = date('Y-m-d H:i:s');
        Capsule::table('auth_groups')->insert([
            [
                'id' => 'ADMINS',
                'name' => 'Administradores',
                'description' => 'Grupo de usuarios administradores del sistema',
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ],
            [
                'id' => 'APIS',
                'name' => 'APIs',
                'description' => 'Grupo de usuarios para acceso API',
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]
        ]);
        
        echo "✓ Tabla 'auth_groups' creada exitosamente\n";
        echo "✓ Grupos por defecto insertados\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('auth_groups');
        Capsule::schema()->dropIfExists('auth_groups');
        echo "✓ Tabla 'auth_groups' eliminada\n";
    }
};

