<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create example_crud_images table
 * 
 * One-to-many relationship table: One record can have multiple images.
 * Converted from: 20251104_create_example_crud_images_table.sql
 */
return new class {
    
    public function up()
    {
        Capsule::schema()->create('example_crud_images', function ($table) {
            $table->id();
            $table->foreignId('example_crud_id')
                  ->constrained('example_crud')
                  ->onDelete('cascade');
            $table->string('url', 500);
            $table->string('key', 500);
            $table->string('original_name', 255)->nullable();
            $table->integer('size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('example_crud_id');
            $table->index('is_primary');
            $table->index('display_order');
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('example_crud_images');
        
        echo "✓ Tabla 'example_crud_images' creada exitosamente\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('example_crud_images');
        Capsule::schema()->dropIfExists('example_crud_images');
        echo "✓ Tabla 'example_crud_images' eliminada\n";
    }
};
