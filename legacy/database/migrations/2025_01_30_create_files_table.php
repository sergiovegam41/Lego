<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migración: Crear tabla files (universal)
 *
 * FILOSOFÍA LEGO:
 * Tabla UNIVERSAL para gestionar archivos/imágenes de CUALQUIER entidad.
 * No está atada a productos, artículos, usuarios, etc.
 *
 * PROPÓSITO:
 * - Almacenar metadatos de archivos subidos a MinIO
 * - Cada archivo tiene su propio ID
 * - Las entidades (productos, etc.) solo guardan listas de IDs
 * - Patrón de dos consultas: entidad → file IDs → file details
 *
 * USO:
 * - FilePond → POST /api/files/upload → retorna file ID
 * - ProductsController recibe lista de file IDs
 * - Al consultar producto: primero IDs, luego details
 */
return new class {

    public function up()
    {
        Capsule::schema()->create('files', function ($table) {
            $table->id();

            // Datos del archivo en MinIO
            $table->string('url', 500); // URL completa de MinIO
            $table->string('key', 500); // Key/path en MinIO para operaciones de eliminación

            // Metadatos del archivo
            $table->string('original_name', 255)->nullable(); // Nombre original del archivo
            $table->integer('size')->nullable(); // Tamaño en bytes
            $table->string('mime_type', 100)->nullable(); // Tipo MIME (image/jpeg, etc.)

            // Timestamps
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('mime_type');
            $table->index('created_at');
        });

        echo "✓ Tabla 'files' creada exitosamente\n";
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('files');
        echo "✓ Tabla 'files' eliminada\n";
    }
};
