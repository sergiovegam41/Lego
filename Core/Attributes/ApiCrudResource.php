<?php

namespace Core\Attributes;

use Attribute;

/**
 * ApiCrudResource - Attribute para modelos que exponen API REST automático
 *
 * FILOSOFÍA LEGO:
 * "Model-Driven API" - Agregar este atributo a un modelo automáticamente:
 * - Genera rutas REST (GET, POST, PUT, DELETE)
 * - Configura paginación
 * - Habilita filtros y ordenamiento
 * - Se integra con TableComponent
 *
 * USO BÁSICO:
 * ```php
 * #[ApiCrudResource]
 * class Product extends Model {
 *     // Automáticamente expone:
 *     // GET    /api/products
 *     // GET    /api/products/{id}
 *     // POST   /api/products
 *     // PUT    /api/products/{id}
 *     // DELETE /api/products/{id}
 * }
 * ```
 *
 * USO AVANZADO:
 * ```php
 * #[ApiCrudResource(
 *     endpoint: '/api/v2/products',
 *     pagination: 'cursor',
 *     perPage: 50,
 *     sortable: ['name', 'price', 'created_at'],
 *     filterable: ['category_id', 'status'],
 *     searchable: ['name', 'description'],
 *     middleware: ['auth:api'],
 *     softDeletes: true
 * )]
 * class Product extends Model {}
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ApiCrudResource
{
    /**
     * @param string $endpoint Endpoint sin prefijo /api (ej: 'products' o 'catalog/items')
     * @param string $pagination Tipo de paginación: 'offset', 'cursor', 'page'
     * @param int $perPage Elementos por página
     * @param array $sortable Campos ordenables
     * @param array $filterable Campos filtrables
     * @param array $searchable Campos buscables (para búsqueda global)
     * @param array $middleware Middlewares a aplicar
     * @param bool $softDeletes Soporte para soft deletes
     * @param array $hidden Campos ocultos en respuesta
     * @param array $appends Campos adicionales a incluir
     * @param string|null $controllerClass Controlador custom (opcional)
     */
    public function __construct(
        public ?string $endpoint = null,
        public string $pagination = 'offset',
        public int $perPage = 20,
        public array $sortable = [],
        public array $filterable = [],
        public array $searchable = [],
        public array $middleware = [],
        public bool $softDeletes = false,
        public array $hidden = [],
        public array $appends = [],
        public ?string $controllerClass = null,
    ) {
        // Validar que endpoint NO incluya /api (se agrega automáticamente)
        if ($this->endpoint !== null && str_starts_with($this->endpoint, '/api')) {
            throw new \InvalidArgumentException(
                "Endpoint should not include '/api' prefix. It's added automatically.\n" .
                "Instead of: '{$this->endpoint}'\n" .
                "Use: '" . substr($this->endpoint, 4) . "'"
            );
        }

        // Validar tipo de paginación
        if (!in_array($this->pagination, ['offset', 'cursor', 'page'])) {
            throw new \InvalidArgumentException(
                "Pagination type must be 'offset', 'cursor', or 'page'. Got: {$this->pagination}"
            );
        }

        // Validar perPage
        if ($this->perPage < 1 || $this->perPage > 100) {
            throw new \InvalidArgumentException(
                "perPage must be between 1 and 100. Got: {$this->perPage}"
            );
        }
    }

    /**
     * Obtener endpoint completo con prefijo /api
     *
     * @param string $modelClass Nombre completo de la clase
     * @return string Endpoint completo (ej: '/api/products')
     */
    public function getEndpoint(string $modelClass): string
    {
        // Si endpoint está definido, agregar prefijo /api
        if ($this->endpoint !== null) {
            $path = ltrim($this->endpoint, '/');
            return "/api/{$path}";
        }

        // Auto-generar desde nombre del modelo
        // Extraer nombre corto de la clase (ej: App\Models\Product → Product)
        $shortName = (new \ReflectionClass($modelClass))->getShortName();

        // Pluralizar y convertir a kebab-case
        $plural = $this->pluralize($shortName);
        $kebab = $this->toKebabCase($plural);

        return "/api/{$kebab}";
    }

    /**
     * Obtener nombre del controlador
     *
     * @return string Nombre completo de la clase del controlador
     */
    public function getControllerClass(): string
    {
        return $this->controllerClass ?? \Core\Controllers\AbstractCrudController::class;
    }

    /**
     * Verificar si tiene middleware específico
     *
     * @param string $middleware
     * @return bool
     */
    public function hasMiddleware(string $middleware): bool
    {
        return in_array($middleware, $this->middleware);
    }

    /**
     * Verificar si un campo es sortable
     *
     * @param string $field
     * @return bool
     */
    public function isSortable(string $field): bool
    {
        return empty($this->sortable) || in_array($field, $this->sortable);
    }

    /**
     * Verificar si un campo es filterable
     *
     * @param string $field
     * @return bool
     */
    public function isFilterable(string $field): bool
    {
        return empty($this->filterable) || in_array($field, $this->filterable);
    }

    /**
     * Verificar si un campo es buscable
     *
     * @param string $field
     * @return bool
     */
    public function isSearchable(string $field): bool
    {
        return empty($this->searchable) || in_array($field, $this->searchable);
    }

    /**
     * Obtener configuración como array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'pagination' => $this->pagination,
            'perPage' => $this->perPage,
            'sortable' => $this->sortable,
            'filterable' => $this->filterable,
            'searchable' => $this->searchable,
            'middleware' => $this->middleware,
            'softDeletes' => $this->softDeletes,
            'hidden' => $this->hidden,
            'appends' => $this->appends,
            'controllerClass' => $this->controllerClass,
        ];
    }

    /**
     * Pluralizar nombre (simple)
     *
     * @param string $word
     * @return string
     */
    private function pluralize(string $word): string
    {
        // Reglas simples de pluralización en inglés
        $irregulars = [
            'person' => 'people',
            'child' => 'children',
            'man' => 'men',
            'woman' => 'women',
        ];

        $lower = strtolower($word);
        if (isset($irregulars[$lower])) {
            return $irregulars[$lower];
        }

        // Reglas comunes
        if (str_ends_with($lower, 'y')) {
            return substr($word, 0, -1) . 'ies';
        }

        if (str_ends_with($lower, 's') ||
            str_ends_with($lower, 'x') ||
            str_ends_with($lower, 'z') ||
            str_ends_with($lower, 'ch') ||
            str_ends_with($lower, 'sh')) {
            return $word . 'es';
        }

        return $word . 's';
    }

    /**
     * Convertir a kebab-case
     *
     * @param string $string
     * @return string
     */
    private function toKebabCase(string $string): string
    {
        // PascalCase o camelCase → kebab-case
        $result = preg_replace('/([a-z])([A-Z])/', '$1-$2', $string);
        return strtolower($result ?? $string);
    }
}
