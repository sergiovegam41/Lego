<?php

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('featured_products', function ($table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('tag', 50); // most-popular, best-seller, free-shipping, etc.
            $table->string('description', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key
            $table->foreign('product_id')
                  ->references('id')
                  ->on('flowers')
                  ->onDelete('cascade');

            // Índices para mejorar rendimiento
            $table->index('product_id');
            $table->index('tag');
            $table->index('sort_order');
            $table->index('is_active');
            $table->index(['tag', 'sort_order']); // Índice compuesto para queries comunes

            // Índice único: Un producto solo puede tener un tag específico
            $table->unique(['product_id', 'tag']);
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('featured_products');
    }
};
