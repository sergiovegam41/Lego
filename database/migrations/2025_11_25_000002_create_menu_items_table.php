<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create menu_items table
 * 
 * Sistema de menú jerárquico con relaciones padre-hijo ilimitadas
 */
return new class {
    
    public function up()
    {
        Capsule::schema()->create('menu_items', function ($table) {
            $table->string('id', 50)->primary();
            $table->string('parent_id', 50)->nullable();
            $table->string('label', 100);
            $table->string('index_label', 100)->nullable();  // Nombre cuando tiene hijos
            $table->string('route', 255);
            $table->string('icon', 50)->default('ellipse-outline');
            $table->integer('display_order')->default(0);
            $table->integer('level')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            
            // Indexes para performance (sin foreign key por ahora)
            $table->index('parent_id');
            $table->index('level');
            $table->index(['parent_id', 'display_order']);
            $table->index('is_visible');
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('menu_items');
        
        echo "✓ Tabla 'menu_items' creada exitosamente\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('menu_items');
        Capsule::schema()->dropIfExists('menu_items');
        echo "✓ Tabla 'menu_items' eliminada\n";
    }
};
