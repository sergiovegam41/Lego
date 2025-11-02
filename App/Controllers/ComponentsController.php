<?php

namespace App\Controllers;

use Core\Controllers\CoreController;
use Core\Components\ComponentRegistry;
use Core\Response;
use Core\Providers\Request;

/**
 * ComponentsController - API para renderizado dinámico de componentes
 *
 * FILOSOFÍA LEGO:
 * Permite solicitar componentes renderizados desde JavaScript,
 * manteniendo PHP como única fuente de verdad.
 *
 * ENDPOINTS:
 *
 * 1. GET /api/components/render
 *    Renderiza un componente único
 *    Query: ?id=icon-button&params={"action":"edit","entityId":14}
 *
 * 2. POST /api/components/batch
 *    Renderiza múltiples instancias en una petición (batch)
 *    Body: {"component":"icon-button","renders":[{...},{...}]}
 *
 * EJEMPLO DE USO (JavaScript):
 * ```javascript
 * // Batch rendering (recomendado para tablas)
 * const buttons = await window.lego.components
 *     .get('icon-button')
 *     .params([
 *         { action: 'edit', entityId: 1 },
 *         { action: 'delete', entityId: 1 }
 *     ]);
 * ```
 */
class ComponentsController extends CoreController
{
    /**
     * Renderizar un componente único
     *
     * GET /api/components/render?id=icon-button&params={"action":"edit","entityId":14}
     */
    public function render()
    {
        try {
            // Obtener parámetros de query string
            $componentId = $_GET['id'] ?? null;
            $paramsJson = $_GET['params'] ?? '{}';

            // Validar componente ID
            if (!$componentId) {
                return Response::json(400, [
                    'success' => false,
                    'message' => 'Missing required parameter: id',
                    'example' => '/api/components/render?id=icon-button&params={"action":"edit","entityId":14}'
                ]);
            }

            // Parsear parámetros JSON
            $params = json_decode($paramsJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return Response::json(400, [
                    'success' => false,
                    'message' => 'Invalid JSON in params parameter: ' . json_last_error_msg(),
                    'received' => $paramsJson
                ]);
            }

            // Renderizar componente
            $html = ComponentRegistry::render($componentId, $params);

            return Response::json(200, [
                'success' => true,
                'html' => $html,
                'componentId' => $componentId,
                'params' => $params
            ]);

        } catch (\InvalidArgumentException $e) {
            return Response::json(404, [
                'success' => false,
                'message' => $e->getMessage()
            ]);

        } catch (\Exception $e) {
            error_log('[ComponentsController] Error rendering component: ' . $e->getMessage());

            return Response::json(500, [
                'success' => false,
                'message' => 'Error rendering component: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Renderizar múltiples componentes en batch
     *
     * POST /api/components/batch
     * Body: {
     *   "component": "icon-button",
     *   "renders": [
     *     {"action": "edit", "entityId": 1},
     *     {"action": "delete", "entityId": 1}
     *   ]
     * }
     *
     * Response: {
     *   "success": true,
     *   "html": ["<button>...</button>", "<button>...</button>"]
     * }
     */
    public function batch()
    {
        try {
            // Parsear body JSON
            $body = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return Response::json(400, [
                    'success' => false,
                    'message' => 'Invalid JSON in request body: ' . json_last_error_msg()
                ]);
            }

            // Validar parámetros requeridos
            $componentId = $body['component'] ?? null;
            $renders = $body['renders'] ?? null;

            if (!$componentId) {
                return Response::json(400, [
                    'success' => false,
                    'message' => 'Missing required parameter: component',
                    'example' => [
                        'component' => 'icon-button',
                        'renders' => [
                            ['action' => 'edit', 'entityId' => 1],
                            ['action' => 'delete', 'entityId' => 1]
                        ]
                    ]
                ]);
            }

            if (!is_array($renders)) {
                return Response::json(400, [
                    'success' => false,
                    'message' => 'Parameter "renders" must be an array',
                    'received' => gettype($renders)
                ]);
            }

            // Validar que no esté vacío
            if (empty($renders)) {
                return Response::json(400, [
                    'success' => false,
                    'message' => 'Parameter "renders" cannot be empty'
                ]);
            }

            // Renderizar batch
            $htmlList = ComponentRegistry::renderBatch($componentId, $renders);

            return Response::json(200, [
                'success' => true,
                'html' => $htmlList,
                'componentId' => $componentId,
                'count' => count($htmlList)
            ]);

        } catch (\InvalidArgumentException $e) {
            return Response::json(400, [
                'success' => false,
                'message' => $e->getMessage()
            ]);

        } catch (\Exception $e) {
            error_log('[ComponentsController] Error in batch rendering: ' . $e->getMessage());

            return Response::json(500, [
                'success' => false,
                'message' => 'Error in batch rendering: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Listar todos los componentes registrados
     *
     * GET /api/components/list
     *
     * Útil para debugging y descubrimiento de componentes disponibles
     */
    public function list()
    {
        try {
            $components = ComponentRegistry::getAll();

            return Response::json(200, [
                'success' => true,
                'components' => array_keys($components),
                'count' => count($components),
                'details' => array_map(fn($id, $class) => [
                    'id' => $id,
                    'class' => $class
                ], array_keys($components), $components)
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error listing components: ' . $e->getMessage()
            ]);
        }
    }
}
