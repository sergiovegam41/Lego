<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create tool_features table
 * 
 * Características de herramientas - Lista de strings vinculados
 * Relación: N:1 con tools
 */
return new class {
    
    public function up()
    {
        Capsule::schema()->create('tool_features', function ($table) {
            $table->id();
            $table->unsignedBigInteger('tool_id');
            $table->string('feature', 500); // La característica como string
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Foreign key
            $table->foreign('tool_id')
                  ->references('id')
                  ->on('tools')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('tool_id');
            $table->index('display_order');
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('tool_features');
        
        echo "✓ Tabla 'tool_features' creada exitosamente\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('tool_features');
        Capsule::schema()->dropIfExists('tool_features');
        echo "✓ Tabla 'tool_features' eliminada\n";
    }
};

