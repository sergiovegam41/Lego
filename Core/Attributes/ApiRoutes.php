<?php

namespace Core\Attributes;

use Attribute;

/**
 * ApiRoutes - Atributo para auto-registro de rutas de controladores
 *
 * FILOSOFÍA LEGO:
 * "Versatilidad sobre convención" - LEGO es más que solo CRUDs.
 * Este atributo permite definir cualquier tipo de rutas para controladores,
 * desde APIs REST hasta webhooks, reportes, integraciones, etc.
 *
 * USO BÁSICO (CRUD):
 * ```php
 * #[ApiRoutes('/tools', preset: 'crud')]
 * class ToolsController extends CoreController { ... }
 * // Genera:
 * // GET  /api/tools/list
 * // GET  /api/tools/get
 * // POST /api/tools/create
 * // POST /api/tools/update
 * // POST /api/tools/delete
 * ```
 *
 * USO PERSONALIZADO:
 * ```php
 * #[ApiRoutes('/reports', actions: [
 *     'generate' => ['POST'],
 *     'download' => ['GET'],
 *     'schedule' => ['POST'],
 *     'status' => ['GET'],
 * ])]
 * class ReportsController extends CoreController { ... }
 * ```
 *
 * USO MIXTO:
 * ```php
 * #[ApiRoutes('/inventory', preset: 'crud', actions: [
 *     'export' => ['GET'],      // Acción adicional
 *     'import' => ['POST'],     // Otra acción adicional
 *     'sync' => ['POST', 'GET'] // Múltiples métodos HTTP
 * ])]
 * class InventoryController extends CoreController { ... }
 * ```
 *
 * PRESETS DISPONIBLES:
 * - 'crud': list(GET), get(GET), create(POST), update(POST), delete(POST)
 * - 'readonly': list(GET), get(GET)
 * - 'custom': Solo las acciones definidas manualmente
 *
 * OPCIONES AVANZADAS:
 * - middleware: Array de middlewares a aplicar
 * - prefix: Prefijo adicional después de /api
 * - methods: Métodos HTTP permitidos globalmente
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ApiRoutes
{
    /**
     * Presets predefinidos de acciones
     */
    public const PRESETS = [
        'crud' => [
            'list' => ['GET'],
            'get' => ['GET'],
            'create' => ['POST'],
            'update' => ['POST'],
            'delete' => ['POST'],
        ],
        'crud-rest' => [
            'list' => ['GET'],
            'get' => ['GET'],
            'create' => ['POST'],
            'update' => ['PUT'],
            'delete' => ['DELETE'],
        ],
        'readonly' => [
            'list' => ['GET'],
            'get' => ['GET'],
        ],
        'writeonly' => [
            'create' => ['POST'],
            'update' => ['POST'],
            'delete' => ['POST'],
        ],
        'custom' => [],
    ];

    /**
     * @param string $endpoint Ruta base del controlador (sin /api)
     * @param string $preset Preset de acciones: 'crud', 'crud-rest', 'readonly', 'writeonly', 'custom'
     * @param array $actions Acciones personalizadas ['action' => ['GET', 'POST'], ...]
     * @param array $exclude Acciones del preset a excluir
     * @param array $middleware Middlewares a aplicar a todas las rutas
     * @param string|null $prefix Prefijo adicional (ej: 'v2' -> /api/v2/endpoint)
     * @param bool $enabled Habilitar/deshabilitar el auto-registro
     */
    public function __construct(
        public string $endpoint,
        public string $preset = 'crud',
        public array $actions = [],
        public array $exclude = [],
        public array $middleware = [],
        public ?string $prefix = null,
        public bool $enabled = true,
    ) {
        // Validar endpoint
        if (str_starts_with($this->endpoint, '/api')) {
            throw new \InvalidArgumentException(
                "Endpoint should not include '/api' prefix. It's added automatically.\n" .
                "Instead of: '{$this->endpoint}'\n" .
                "Use: '" . substr($this->endpoint, 4) . "'"
            );
        }

        // Validar preset
        if (!isset(self::PRESETS[$this->preset])) {
            $validPresets = implode(', ', array_keys(self::PRESETS));
            throw new \InvalidArgumentException(
                "Invalid preset: '{$this->preset}'. Valid presets are: {$validPresets}"
            );
        }
    }

    /**
     * Obtener todas las acciones resueltas (preset + custom - exclude)
     *
     * @return array ['action' => ['GET', 'POST'], ...]
     */
    public function getResolvedActions(): array
    {
        // Empezar con el preset
        $actions = self::PRESETS[$this->preset] ?? [];

        // Agregar acciones personalizadas
        foreach ($this->actions as $action => $methods) {
            // Normalizar: si es string, convertir a array
            if (is_string($methods)) {
                $methods = [$methods];
            }
            
            // Normalizar métodos a mayúsculas
            $methods = array_map('strtoupper', $methods);
            
            // Si la acción ya existe, mergear métodos
            if (isset($actions[$action])) {
                $actions[$action] = array_unique(array_merge($actions[$action], $methods));
            } else {
                $actions[$action] = $methods;
            }
        }

        // Excluir acciones
        foreach ($this->exclude as $excludeAction) {
            unset($actions[$excludeAction]);
        }

        return $actions;
    }

    /**
     * Obtener el endpoint completo con prefijo /api
     *
     * @return string
     */
    public function getFullEndpoint(): string
    {
        $endpoint = ltrim($this->endpoint, '/');
        
        if ($this->prefix) {
            $prefix = ltrim($this->prefix, '/');
            return "/api/{$prefix}/{$endpoint}";
        }
        
        return "/api/{$endpoint}";
    }

    /**
     * Verificar si una acción está habilitada
     *
     * @param string $action
     * @return bool
     */
    public function hasAction(string $action): bool
    {
        return isset($this->getResolvedActions()[$action]);
    }

    /**
     * Obtener métodos HTTP para una acción
     *
     * @param string $action
     * @return array
     */
    public function getMethodsForAction(string $action): array
    {
        return $this->getResolvedActions()[$action] ?? [];
    }

    /**
     * Obtener configuración como array (para debugging)
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'fullEndpoint' => $this->getFullEndpoint(),
            'preset' => $this->preset,
            'actions' => $this->getResolvedActions(),
            'exclude' => $this->exclude,
            'middleware' => $this->middleware,
            'prefix' => $this->prefix,
            'enabled' => $this->enabled,
        ];
    }
}

