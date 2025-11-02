<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EntityFileAssociation Model (Polimórfica)
 *
 * FILOSOFÍA LEGO - OPCIÓN B:
 * Asociación UNIVERSAL entre entidades y archivos.
 * Permite que CUALQUIER entidad tenga archivos sin necesidad de tablas intermedias específicas.
 *
 * RELACIONES:
 * - entity_type + entity_id: Relación polimórfica a cualquier entidad
 * - file_id: Relación a EntityFile (tabla 'files')
 *
 * METADATA JSONB:
 * Campo flexible para flags específicos de cada entidad:
 * - Products: {is_primary: true}
 * - Articles: {is_cover: true}
 * - User: {avatar: true}
 * - etc.
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
 * // Obtener archivos de un producto
 * $associations = EntityFileAssociation::forEntity('Product', 123)
 *     ->ordered()
 *     ->with('file')
 *     ->get();
 *
 * foreach ($associations as $assoc) {
 *     echo $assoc->file->url; // URL del archivo
 *     echo $assoc->metadata['is_primary'] ?? false; // Metadata
 * }
 * ```
 */
class EntityFileAssociation extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'entity_files';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'entity_type',
        'entity_id',
        'file_id',
        'display_order',
        'metadata'
    ];

    /**
     * Los atributos que deben ser cast a tipos nativos
     */
    protected $casts = [
        'entity_id' => 'integer',
        'file_id' => 'integer',
        'display_order' => 'integer',
        'metadata' => 'array', // JSONB → Array de PHP
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Valores por defecto para atributos
     */
    protected $attributes = [
        'display_order' => 0,
        'metadata' => '{}'
    ];

    /**
     * Relación: Una asociación pertenece a un archivo
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(EntityFile::class, 'file_id');
    }

    /**
     * Scope: Filtrar por entidad específica
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entityType Tipo de entidad ('Product', 'Article', etc.)
     * @param int $entityId ID de la entidad
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
                     ->where('entity_id', $entityId);
    }

    /**
     * Scope: Ordenar por display_order ascendente
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Scope: Solo archivos primarios (is_primary = true en metadata)
     */
    public function scopePrimary($query)
    {
        return $query->whereRaw("metadata->>'is_primary' = 'true'");
    }

    /**
     * Helper: Verificar si es archivo primario
     */
    public function isPrimary(): bool
    {
        return ($this->metadata['is_primary'] ?? false) === true;
    }

    /**
     * Helper: Marcar como primario
     */
    public function markAsPrimary(): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata['is_primary'] = true;
        $this->metadata = $metadata;
        return $this->save();
    }

    /**
     * Helper: Quitar marca de primario
     */
    public function unmarkAsPrimary(): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata['is_primary'] = false;
        $this->metadata = $metadata;
        return $this->save();
    }

    /**
     * Helper: Obtener valor de metadata
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Helper: Establecer valor de metadata
     */
    public function setMetadataValue(string $key, $value): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        return $this->save();
    }
}
