<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migración: Crear tabla product_images
 *
 * FILOSOFÍA LEGO:
 * Tabla de relación uno-a-muchos: Un producto puede tener múltiples imágenes.
 * Las imágenes se almacenan en MinIO y aquí solo guardamos las URLs y metadatos.
 */
return new class {

    public function up()
    {
        Capsule::schema()->create('product_images', function ($table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('url', 500); // URL completa de MinIO
            $table->string('key', 500); // Key/path en MinIO para operaciones de eliminación
            $table->string('original_name', 255)->nullable(); // Nombre original del archivo
            $table->integer('size')->nullable(); // Tamaño en bytes
            $table->string('mime_type', 100)->nullable(); // Tipo MIME
            $table->integer('order')->default(0); // Orden de visualización
            $table->boolean('is_primary')->default(false); // Imagen principal del producto
            $table->timestamps();

            // Índices
            $table->index('product_id');
            $table->index('is_primary');
            $table->index('order');
        });

        echo "✓ Tabla 'product_images' creada exitosamente\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('product_images');
        echo "✓ Tabla 'product_images' eliminada\n";
    }
};
