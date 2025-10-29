<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migración: Agregar columnas sku y min_stock a tabla products
 *
 * FILOSOFÍA LEGO:
 * Agrega campos faltantes para control de inventario.
 */
return new class {

    public function up()
    {
        Capsule::schema()->table('products', function ($table) {
            // Agregar columna SKU (código único del producto)
            $table->string('sku', 100)->nullable()->after('name');

            // Agregar columna de stock mínimo para alertas
            $table->integer('min_stock')->default(5)->after('stock');

            // Índice para búsqueda rápida por SKU
            $table->index('sku');
        });

        echo "✓ Columnas 'sku' y 'min_stock' agregadas a tabla 'products'\n";
    }

    public function down()
    {
        Capsule::schema()->table('products', function ($table) {
            $table->dropIndex(['sku']);
            $table->dropColumn(['sku', 'min_stock']);
        });

        echo "✓ Columnas 'sku' y 'min_stock' eliminadas de tabla 'products'\n";
    }
};
