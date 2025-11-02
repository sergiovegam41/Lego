<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migración: Crear tabla entity_files (polimórfica)
 *
 * FILOSOFÍA LEGO - OPCIÓN B:
 * Tabla de asociación UNIVERSAL polimórfica entre entidades y archivos.
 * Permite que CUALQUIER entidad (Product, Article, User, etc.) tenga archivos.
 *
 * ARQUITECTURA:
 * - files: Tabla universal de archivos (independiente)
 * - entity_files: Tabla de asociación polimórfica (esta migración)
 * - products/articles/etc: Solo referencian a entity_files
 *
 * VENTAJAS:
 * ✅ Universal: Cualquier entidad puede tener archivos
 * ✅ Flexible: metadata JSONB para flags personalizados
 * ✅ Eficiente: Queries con JOINs optimizados
 * ✅ Escalable: Agregar nuevas entidades sin cambios en BD
 *
 * CAMPOS:
 * - entity_type: Tipo de entidad ('Product', 'Article', 'User', etc.)
 * - entity_id: ID de la entidad
 * - file_id: FK a tabla 'files'
 * - display_order: Orden de visualización
 * - metadata: JSONB para flags personalizados (is_primary, is_cover, etc.)
 *
 * EJEMPLO DE USO:
 * ```php
 * // Asociar archivo a producto
 * EntityFileAssociation::create([
 *     'entity_type' => 'Product',
 *     'entity_id' => 123,
 *     'file_id' => 45,
 *     'display_order' => 0,
 *     'metadata' => ['is_primary' => true]
 * ]);
 *
 * // Obtener archivos de producto
 * $files = EntityFileAssociation::where('entity_type', 'Product')
 *     ->where('entity_id', 123)
 *     ->with('file')
 *     ->orderBy('display_order')
 *     ->get();
 * ```
 */
return new class {

    public function up()
    {
        Capsule::schema()->create('entity_files', function ($table) {
            $table->id();

            // Relación polimórfica
            $table->string('entity_type', 100); // 'Product', 'Article', 'User', etc.
            $table->bigInteger('entity_id')->unsigned(); // ID de la entidad

            // Referencia al archivo universal
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');

            // Orden y metadata
            $table->integer('display_order')->default(0);
            $table->jsonb('metadata')->nullable(); // {is_primary: true, is_cover: true, etc.}

            // Timestamps
            $table->timestamps();

            // Índices para queries eficientes
            $table->index(['entity_type', 'entity_id']); // Buscar archivos de una entidad
            $table->index('file_id'); // Buscar entidades que usan un archivo
            $table->index('display_order'); // Ordenamiento
        });

        echo "✓ Tabla 'entity_files' creada exitosamente\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('entity_files');
        echo "✓ Tabla 'entity_files' eliminada\n";
    }
};
