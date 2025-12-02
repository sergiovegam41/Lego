<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create tools table
 * 
 * CRUD de Herramientas - Tabla principal
 * Campos: nombre, descripción
 * Relaciones: tool_features (1:N), entity_files para imágenes
 */
return new class {
    
    public function up()
    {
        Capsule::schema()->create('tools', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('is_active');
            $table->index('created_at');
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('tools');
        
        echo "✓ Tabla 'tools' creada exitosamente\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('tools');
        Capsule::schema()->dropIfExists('tools');
        echo "✓ Tabla 'tools' eliminada\n";
    }
};

