<?php

namespace Core\Controllers;

use Core\Response;
use Core\Attributes\ApiCrudResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * AbstractCrudController - Controlador genérico para API REST
 *
 * FILOSOFÍA LEGO:
 * Controlador universal que funciona con cualquier modelo decorado con #[ApiCrudResource].
 * No requiere código específico por modelo - configuración sobre código.
 *
 * FUNCIONAMIENTO:
 * 1. ApiCrudRouter detecta modelo con #[ApiCrudResource]
 * 2. Registra rutas apuntando a este controlador
 * 3. Este controlador lee configuración del atributo
 * 4. Ejecuta operación CRUD correspondiente
 *
 * ENDPOINTS GENERADOS:
 * - GET    /api/resource        → list()
 * - GET    /api/resource/{id}   → get($id)
 * - POST   /api/resource        → create()
 * - PUT    /api/resource/{id}   → update($id)
 * - DELETE /api/resource/{id}   → delete($id)
 */
class AbstractCrudController
{
    protected string $modelClass;
    protected ApiCrudResource $config;
    protected Model $model;

    /**
     * Constructor
     *
     * @param string $modelClass Nombre completo de la clase del modelo
     * @throws \InvalidArgumentException Si el modelo no tiene #[ApiCrudResource]
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;

        // Verificar que existe el modelo
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class not found: {$modelClass}");
        }

        // Obtener configuración del atributo
        $reflection = new \ReflectionClass($modelClass);
        $attributes = $reflection->getAttributes(ApiCrudResource::class);

        if (empty($attributes)) {
            throw new \InvalidArgumentException(
                "Model {$modelClass} must have #[ApiCrudResource] attribute"
            );
        }

        $this->config = $attributes[0]->newInstance();
        $this->model = new $modelClass();
    }

    /**
     * GET /api/resource - Listar recursos con paginación
     *
     * Query params:
     * - page: Número de página (offset pagination)
     * - limit: Elementos por página
     * - cursor: Cursor para paginación cursor-based
     * - sort: Campo para ordenar
     * - order: asc|desc
     * - search: Búsqueda global
     * - filter[campo]: Filtro específico
     *
     * @return void (envía JSON response)
     */
    public function list(): void
    {
        try {
            $query = $this->modelClass::query();

            // Aplicar filtros
            $this->applyFilters($query);

            // Aplicar búsqueda global
            $this->applySearch($query);

            // Aplicar ordenamiento
            $this->applySort($query);

            // Aplicar soft deletes
            if (!$this->config->softDeletes) {
                // Por defecto Eloquent ya excluye soft deletes
                // Solo si queremos incluirlos explícitamente
            }

            // Aplicar paginación
            $result = $this->applyPagination($query);

            Response::json(200, [
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination'] ?? null,
            ]);

        } catch (\Exception $e) {
            $this->handleError($e, 'list');
        }
    }

    /**
     * GET /api/resource/{id} - Obtener recurso por ID
     *
     * @param int|string $id
     * @return void
     */
    public function get($id): void
    {
        try {
            $resource = $this->modelClass::findOrFail($id);

            // Aplicar hidden/appends
            if (!empty($this->config->hidden)) {
                $resource->makeHidden($this->config->hidden);
            }
            if (!empty($this->config->appends)) {
                $resource->append($this->config->appends);
            }

            Response::json(200, [
                'success' => true,
                'data' => $resource,
            ]);

        } catch (ModelNotFoundException $e) {
            Response::json(404, [
                'success' => false,
                'message' => 'Resource not found',
                'error' => "No resource found with ID: {$id}",
            ]);

        } catch (\Exception $e) {
            $this->handleError($e, 'get');
        }
    }

    /**
     * POST /api/resource - Crear nuevo recurso
     *
     * Body: JSON con datos del recurso
     *
     * @return void
     */
    public function create(): void
    {
        try {
            // Obtener datos del request
            $data = $this->getRequestData();

            // Validar datos (básico - puede extenderse)
            if (empty($data)) {
                Response::json(400, [
                    'success' => false,
                    'message' => 'No data provided',
                ]);
                return;
            }

            // Crear recurso
            $resource = $this->modelClass::create($data);

            Response::json(201, [
                'success' => true,
                'message' => 'Resource created successfully',
                'data' => $resource,
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Response::json(422, [
                'success' => false,
                'message' => 'Validation error',
                'error' => $e->getMessage(),
            ]);

        } catch (\Exception $e) {
            $this->handleError($e, 'create');
        }
    }

    /**
     * PUT /api/resource/{id} - Actualizar recurso
     *
     * @param int|string $id
     * @return void
     */
    public function update($id): void
    {
        try {
            $resource = $this->modelClass::findOrFail($id);

            // Obtener datos del request
            $data = $this->getRequestData();

            if (empty($data)) {
                Response::json(400, [
                    'success' => false,
                    'message' => 'No data provided',
                ]);
                return;
            }

            // Actualizar recurso
            $resource->update($data);

            Response::json(200, [
                'success' => true,
                'message' => 'Resource updated successfully',
                'data' => $resource->fresh(),
            ]);

        } catch (ModelNotFoundException $e) {
            Response::json(404, [
                'success' => false,
                'message' => 'Resource not found',
                'error' => "No resource found with ID: {$id}",
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Response::json(422, [
                'success' => false,
                'message' => 'Validation error',
                'error' => $e->getMessage(),
            ]);

        } catch (\Exception $e) {
            $this->handleError($e, 'update');
        }
    }

    /**
     * DELETE /api/resource/{id} - Eliminar recurso
     *
     * @param int|string $id
     * @return void
     */
    public function delete($id): void
    {
        try {
            $resource = $this->modelClass::findOrFail($id);

            // Soft delete o hard delete
            $resource->delete();

            Response::json(200, [
                'success' => true,
                'message' => 'Resource deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            Response::json(404, [
                'success' => false,
                'message' => 'Resource not found',
                'error' => "No resource found with ID: {$id}",
            ]);

        } catch (\Exception $e) {
            $this->handleError($e, 'delete');
        }
    }

    /**
     * Aplicar filtros desde query params
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    protected function applyFilters($query): void
    {
        $filters = $_GET['filter'] ?? [];

        if (!is_array($filters)) {
            return;
        }

        foreach ($filters as $field => $value) {
            if ($this->config->isFilterable($field)) {
                $query->where($field, $value);
            }
        }
    }

    /**
     * Aplicar búsqueda global
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    protected function applySearch($query): void
    {
        $search = $_GET['search'] ?? null;

        if (!$search || empty($this->config->searchable)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $first = true;
            foreach ($this->config->searchable as $field) {
                if ($first) {
                    $q->where($field, 'ILIKE', "%{$search}%");
                    $first = false;
                } else {
                    $q->orWhere($field, 'ILIKE', "%{$search}%");
                }
            }
        });
    }

    /**
     * Aplicar ordenamiento
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    protected function applySort($query): void
    {
        $sort = $_GET['sort'] ?? 'created_at';
        $order = $_GET['order'] ?? 'desc';

        // Validar orden
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        // Validar que el campo sea sortable
        if ($this->config->isSortable($sort)) {
            $query->orderBy($sort, $order);
        }
    }

    /**
     * Aplicar paginación según configuración
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return array
     */
    protected function applyPagination($query): array
    {
        $perPage = (int) ($_GET['limit'] ?? $this->config->perPage);
        $perPage = max(1, min($perPage, 100)); // Entre 1 y 100

        switch ($this->config->pagination) {
            case 'cursor':
                return $this->cursorPaginate($query, $perPage);

            case 'page':
            case 'offset':
            default:
                return $this->offsetPaginate($query, $perPage);
        }
    }

    /**
     * Paginación offset (page/limit)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $perPage
     * @return array
     */
    protected function offsetPaginate($query, int $perPage): array
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * Paginación cursor-based
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $perPage
     * @return array
     */
    protected function cursorPaginate($query, int $perPage): array
    {
        $cursor = $_GET['cursor'] ?? null;

        if ($cursor) {
            // Decodificar cursor (simple base64)
            $decoded = json_decode(base64_decode($cursor), true);
            if ($decoded && isset($decoded['id'])) {
                $query->where('id', '>', $decoded['id']);
            }
        }

        $items = $query->limit($perPage + 1)->get();

        $hasMore = $items->count() > $perPage;
        if ($hasMore) {
            $items->pop();
        }

        $nextCursor = null;
        if ($hasMore && $items->isNotEmpty()) {
            $lastItem = $items->last();
            $nextCursor = base64_encode(json_encode(['id' => $lastItem->id]));
        }

        return [
            'data' => $items->toArray(),
            'pagination' => [
                'next_cursor' => $nextCursor,
                'has_more' => $hasMore,
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * Obtener datos del request (POST/PUT)
     *
     * @return array
     */
    protected function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Si no es JSON, intentar $_POST
            return $_POST;
        }

        return $data ?? [];
    }

    /**
     * Manejar errores
     *
     * @param \Exception $e
     * @param string $action
     * @return void
     */
    protected function handleError(\Exception $e, string $action): void
    {
        error_log("[AbstractCrudController] Error in {$action}: " . $e->getMessage());
        error_log("[AbstractCrudController] Trace: " . $e->getTraceAsString());

        Response::json(500, [
            'success' => false,
            'message' => 'Internal server error',
            'error' => $e->getMessage(),
            'action' => $action,
        ]);
    }
}
