<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migración: Crear tabla products
 *
 * FILOSOFÍA LEGO:
 * Tabla de ejemplo para demostrar CRUD completo.
 */
return new class {

    public function up()
    {
        Capsule::schema()->create('products', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->string('category', 100)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Índices
            $table->index('category');
            $table->index('is_active');
            $table->index('created_at');
        });

        echo "✓ Tabla 'products' creada exitosamente\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('products');
        echo "✓ Tabla 'products' eliminada\n";
    }
};
