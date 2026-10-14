<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/helpers.php';

/**
 * Migration: Create example_crud table
 * 
 * This table serves as a template/reference for building other CRUDs.
 * Converted from: 20251104_create_example_crud_table.sql
 */
return new class {
    
    public function up()
    {
        Capsule::schema()->create('example_crud', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->string('sku', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('category', 100)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('sku');
            $table->index('category');
            $table->index('is_active');
            $table->index('created_at');
        });
        
        // Create trigger for updated_at
        createUpdateTimestampTrigger('example_crud');
        
        echo "✓ Tabla 'example_crud' creada exitosamente\n";
    }
    
    public function down()
    {
        dropUpdateTimestampTrigger('example_crud');
        Capsule::schema()->dropIfExists('example_crud');
        echo "✓ Tabla 'example_crud' eliminada\n";
    }
};
