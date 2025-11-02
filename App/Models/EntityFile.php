<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * EntityFile Model (Universal)
 *
 * FILOSOFÍA LEGO:
 * Modelo UNIVERSAL para gestionar archivos de CUALQUIER entidad.
 * No tiene relaciones con productos, artículos, usuarios, etc.
 *
 * PROPÓSITO:
 * - Representar archivos almacenados en MinIO
 * - Cada archivo es independiente con su propio ID
 * - Las entidades guardan listas de IDs, no relaciones directas
 * - Permite reutilización total sin acoplamiento
 *
 * CAMPOS:
 * - id: PK auto-increment
 * - url: URL completa de MinIO
 * - key: Key/path en MinIO (para eliminación)
 * - original_name: Nombre original del archivo
 * - size: Tamaño en bytes
 * - mime_type: Tipo MIME
 * - created_at: Fecha de creación
 * - updated_at: Fecha de actualización
 *
 * USO:
 * - FileService::uploadFile() → crea EntityFile
 * - ProductsController recibe IDs → guarda en products.file_ids
 * - Al consultar: EntityFile::whereIn('id', $fileIds)->get()
 */
class EntityFile extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'files';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'url',
        'key',
        'original_name',
        'size',
        'mime_type'
    ];

    /**
     * Los atributos que deben ser cast a tipos nativos
     */
    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope: Solo imágenes
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope: Por tipo MIME específico
     */
    public function scopeByMimeType($query, $mimeType)
    {
        return $query->where('mime_type', $mimeType);
    }

    /**
     * Accessor: Tamaño formateado (KB/MB)
     */
    public function getSizeFormattedAttribute(): string
    {
        if (!$this->size) return 'N/A';

        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    /**
     * Accessor: Extensión del archivo
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name ?? '', PATHINFO_EXTENSION);
    }

    /**
     * Accessor: Es imagen
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Accessor: Es documento
     */
    public function getIsDocumentAttribute(): bool
    {
        $documentTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument'];
        foreach ($documentTypes as $type) {
            if (str_starts_with($this->mime_type ?? '', $type)) {
                return true;
            }
        }
        return false;
    }
}
