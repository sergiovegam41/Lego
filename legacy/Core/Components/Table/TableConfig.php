<?php

namespace Core\Components\Table;

use Core\Attributes\ApiGetResource;

/**
 * TableConfig - DTO para configuración simplificada de TableComponent
 *
 * FILOSOFÍA LEGO:
 * Detección automática de configuración desde modelos decorados con #[ApiGetResource].
 * Permite uso "mágico" de TableComponent pasando solo el modelo.
 *
 * PROPÓSITO:
 * - Detectar endpoint API desde modelo
 * - Configurar paginación según decorador
 * - Generar columnas automáticamente
 * - Mapear callbacks a funciones scoped del componente
 *
 * USO:
 * ```php
 * $config = TableConfig::fromModel(
 *     modelClass: Product::class,
 *     componentId: 'products-table'
 * );
 *
 * // Acceder a configuración detectada:
 * $config->getApiEndpoint();      // → '/api/get/products'
 * $config->getPaginationType();   // → 'offset'
 * $config->getPerPage();          // → 20
 * ```
 */
class TableConfig
{
    private ?ApiGetResource $apiConfig = null;
    private string $modelClass;
    private string $componentId;

    /**
     * Constructor privado - usar fromModel() o fromManual()
     */
    private function __construct(
        string $modelClass,
        string $componentId,
        ?ApiGetResource $apiConfig = null
    ) {
        $this->modelClass = $modelClass;
        $this->componentId = $componentId;
        $this->apiConfig = $apiConfig;
    }

    /**
     * Crear configuración desde modelo con #[ApiGetResource]
     *
     * @param string $modelClass Nombre completo de la clase del modelo
     * @param string $componentId ID único del componente tabla
     * @return self
     * @throws \InvalidArgumentException Si el modelo no tiene #[ApiGetResource]
     */
    public static function fromModel(string $modelClass, string $componentId): self
    {
        // Verificar que la clase existe
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException(
                "Model class not found: {$modelClass}. " .
                "Make sure the class exists and is autoloaded."
            );
        }

        // Obtener configuración del atributo ApiGetResource
        try {
            $reflection = new \ReflectionClass($modelClass);
            $attributes = $reflection->getAttributes(ApiGetResource::class);

            if (empty($attributes)) {
                throw new \InvalidArgumentException(
                    "Model {$modelClass} must have #[ApiGetResource] attribute to use with TableComponent. " .
                    "Add the attribute to your model:\n\n" .
                    "use Core\\Attributes\\ApiGetResource;\n\n" .
                    "#[ApiGetResource(\n" .
                    "    pagination: 'offset',\n" .
                    "    perPage: 20,\n" .
                    "    sortable: ['id', 'name'],\n" .
                    "    filterable: ['status'],\n" .
                    "    searchable: ['name']\n" .
                    ")]\n" .
                    "class " . $reflection->getShortName() . " extends Model {}"
                );
            }

            $apiConfig = $attributes[0]->newInstance();

            return new self($modelClass, $componentId, $apiConfig);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException(
                "Failed to reflect on model class {$modelClass}: " . $e->getMessage()
            );
        }
    }

    /**
     * Crear configuración manual (sin modelo)
     *
     * @param string $apiEndpoint Endpoint completo (ej: '/api/get/products')
     * @param string $componentId ID único del componente tabla
     * @param array $options Opciones adicionales
     * @return self
     */
    public static function fromManual(
        string $apiEndpoint,
        string $componentId,
        array $options = []
    ): self {
        // Por ahora retornamos configuración básica
        // TODO: Implementar si es necesario
        $config = new self('', $componentId, null);
        return $config;
    }

    /**
     * Obtener endpoint completo de la API
     *
     * @return string Endpoint completo (ej: '/api/get/products')
     */
    public function getApiEndpoint(): string
    {
        if (!$this->apiConfig) {
            throw new \RuntimeException("No API configuration available");
        }

        // El endpoint de ApiGetResource ya incluye /get/
        // pero necesitamos agregar el prefijo /api/ para el cliente
        $endpoint = $this->apiConfig->getEndpoint($this->modelClass);

        // Si el endpoint no empieza con /api/, agregarlo
        if (!str_starts_with($endpoint, '/api/')) {
            $endpoint = '/api' . $endpoint;
        }

        return $endpoint;
    }

    /**
     * Obtener tipo de paginación
     *
     * @return string 'offset' | 'cursor' | 'page'
     */
    public function getPaginationType(): string
    {
        return $this->apiConfig?->pagination ?? 'offset';
    }

    /**
     * Obtener cantidad de elementos por página
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->apiConfig?->perPage ?? 20;
    }

    /**
     * Obtener campos ordenables
     *
     * @return array
     */
    public function getSortableFields(): array
    {
        return $this->apiConfig?->sortable ?? [];
    }

    /**
     * Obtener campos filtrables
     *
     * @return array
     */
    public function getFilterableFields(): array
    {
        return $this->apiConfig?->filterable ?? [];
    }

    /**
     * Obtener campos buscables
     *
     * @return array
     */
    public function getSearchableFields(): array
    {
        return $this->apiConfig?->searchable ?? [];
    }

    /**
     * Verificar si un campo es ordenable
     *
     * @param string $field
     * @return bool
     */
    public function isSortable(string $field): bool
    {
        if (!$this->apiConfig) {
            return false;
        }

        return $this->apiConfig->isSortable($field);
    }

    /**
     * Verificar si un campo es filtrable
     *
     * @param string $field
     * @return bool
     */
    public function isFilterable(string $field): bool
    {
        if (!$this->apiConfig) {
            return false;
        }

        return $this->apiConfig->isFilterable($field);
    }

    /**
     * Verificar si tiene búsqueda habilitada
     *
     * @return bool
     */
    public function hasSearch(): bool
    {
        return !empty($this->getSearchableFields());
    }

    /**
     * Obtener ID del componente
     *
     * @return string
     */
    public function getComponentId(): string
    {
        return $this->componentId;
    }

    /**
     * Obtener clase del modelo
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Convertir a array para pasar a JavaScript
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'apiEndpoint' => $this->getApiEndpoint(),
            'paginationType' => $this->getPaginationType(),
            'perPage' => $this->getPerPage(),
            'sortableFields' => $this->getSortableFields(),
            'filterableFields' => $this->getFilterableFields(),
            'searchableFields' => $this->getSearchableFields(),
            'hasSearch' => $this->hasSearch(),
        ];
    }

    /**
     * Obtener configuración como JSON para JavaScript
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
