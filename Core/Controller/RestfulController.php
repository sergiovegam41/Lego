<?php

namespace Core\Controller;

use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;

/**
 * RestfulController - Controlador base para APIs REST
 *
 * FILOSOFÍA LEGO:
 * Abstrae la lógica común de controladores REST para que los desarrolladores
 * solo implementen la lógica de negocio sin preocuparse por:
 * - Métodos HTTP (PUT, DELETE, POST)
 * - Manejo de errores
 * - Formato de respuestas
 * - Validaciones básicas
 *
 * CONVENCIONES REST:
 * GET    /list       - Listar todos los registros
 * GET    /get?id=X   - Obtener un registro por ID
 * POST   /create     - Crear nuevo registro
 * PUT    /update     - Actualizar registro (fallback a POST)
 * POST   /update     - Actualizar registro (alternativa)
 * DELETE /delete     - Eliminar registro (fallback a POST)
 * POST   /delete     - Eliminar registro (alternativa)
 *
 * EJEMPLO DE USO:
 * ```php
 * class ProductsController extends RestfulController
 * {
 *     const ROUTE = 'products';
 *
 *     protected function list() {
 *         $products = Product::all()->toArray();
 *         $this->success('Productos obtenidos', $products);
 *     }
 *
 *     protected function get() {
 *         $product = Product::find($_GET['id']);
 *         $this->success('Producto obtenido', $product);
 *     }
 *
 *     protected function create() {
 *         $data = $this->getJsonInput();
 *         $product = Product::create($data);
 *         $this->success('Producto creado', $product, StatusCodes::HTTP_CREATED);
 *     }
 *
 *     protected function update() {
 *         $data = $this->getJsonInput();
 *         $product = Product::find($data['id']);
 *         $product->update($data);
 *         $this->success('Producto actualizado', $product);
 *     }
 *
 *     protected function delete() {
 *         $data = $this->getJsonInput();
 *         Product::destroy($data['id']);
 *         $this->success('Producto eliminado');
 *     }
 * }
 * ```
 */
abstract class RestfulController extends CoreController
{
    /**
     * Mapa de métodos HTTP a acciones
     */
    private const HTTP_METHOD_MAP = [
        'GET' => ['list', 'get'],
        'POST' => ['create', 'update', 'delete'],
        'PUT' => ['update'],
        'PATCH' => ['update'],
        'DELETE' => ['delete']
    ];

    /**
     * Constructor - Enruta la petición a la acción correcta
     *
     * @param string $action Acción solicitada (list, get, create, update, delete)
     */
    public function __construct($action)
    {
        try {
            // Validar que el método HTTP es permitido para la acción
            $httpMethod = $_SERVER['REQUEST_METHOD'];
            $this->validateHttpMethod($httpMethod, $action);

            // Ejecutar la acción
            if (method_exists($this, $action)) {
                $this->$action();
            } else {
                $this->error(
                    "Acción '$action' no implementada",
                    null,
                    StatusCodes::HTTP_NOT_FOUND
                );
            }

        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Validar que el método HTTP es apropiado para la acción
     *
     * @param string $httpMethod Método HTTP (GET, POST, etc)
     * @param string $action Acción (list, create, etc)
     * @throws \Exception Si el método no está permitido
     */
    private function validateHttpMethod($httpMethod, $action)
    {
        // Obtener métodos permitidos para esta acción
        $allowedMethods = [];
        foreach (self::HTTP_METHOD_MAP as $method => $actions) {
            if (in_array($action, $actions)) {
                $allowedMethods[] = $method;
            }
        }

        // Si no hay restricciones o el método está permitido, ok
        if (empty($allowedMethods) || in_array($httpMethod, $allowedMethods)) {
            return;
        }

        // Método no permitido
        $allowedStr = implode(', ', $allowedMethods);
        throw new \Exception(
            "Método HTTP '$httpMethod' no permitido para la acción '$action'. Métodos permitidos: $allowedStr",
            StatusCodes::HTTP_METHOD_NOT_ALLOWED
        );
    }

    /**
     * Obtener datos JSON del input
     *
     * @return array Datos parseados
     * @throws \Exception Si el JSON es inválido
     */
    protected function getJsonInput()
    {
        $json = file_get_contents('php://input');

        if (empty($json)) {
            return [];
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON inválido: ' . json_last_error_msg(), StatusCodes::HTTP_BAD_REQUEST);
        }

        return $data;
    }

    /**
     * Responder con éxito
     *
     * @param string $message Mensaje de éxito
     * @param mixed $data Datos a retornar
     * @param int $statusCode Código HTTP (default: 200)
     */
    protected function success($message, $data = null, $statusCode = StatusCodes::HTTP_OK)
    {
        Response::json($statusCode, (array)new ResponseDTO(
            true,
            $message,
            $data
        ));
    }

    /**
     * Responder con error
     *
     * @param string $message Mensaje de error
     * @param mixed $data Datos adicionales (opcional)
     * @param int $statusCode Código HTTP (default: 400)
     */
    protected function error($message, $data = null, $statusCode = StatusCodes::HTTP_BAD_REQUEST)
    {
        Response::json($statusCode, (array)new ResponseDTO(
            false,
            $message,
            $data
        ));
    }

    /**
     * Validar que un campo existe
     *
     * @param array $data Datos a validar
     * @param string $field Nombre del campo
     * @param string $message Mensaje de error personalizado
     * @return bool
     */
    protected function requireField($data, $field, $message = null)
    {
        if (empty($data[$field])) {
            $message = $message ?? "El campo '$field' es requerido";
            $this->error($message, null, StatusCodes::HTTP_BAD_REQUEST);
            return false;
        }
        return true;
    }

    /**
     * Validar múltiples campos requeridos
     *
     * @param array $data Datos a validar
     * @param array $fields Campos requeridos
     * @return bool
     */
    protected function requireFields($data, $fields)
    {
        $missing = [];

        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->error(
                'Campos requeridos faltantes: ' . implode(', ', $missing),
                ['missing_fields' => $missing],
                StatusCodes::HTTP_BAD_REQUEST
            );
            return false;
        }

        return true;
    }

    /**
     * Manejar excepciones de forma consistente
     *
     * @param \Exception $e Excepción
     */
    protected function handleException(\Exception $e)
    {
        $statusCode = $e->getCode() ?: StatusCodes::HTTP_INTERNAL_SERVER_ERROR;

        // Asegurar que es un código HTTP válido
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = StatusCodes::HTTP_INTERNAL_SERVER_ERROR;
        }

        // Log del error
        error_log("[RestfulController] Error: " . $e->getMessage());
        error_log("[RestfulController] Trace: " . $e->getTraceAsString());

        // Respuesta
        $this->error(
            $e->getMessage(),
            null,
            $statusCode
        );
    }

    // Métodos abstractos que deben implementar las subclases

    /**
     * GET /list - Listar todos los registros
     */
    abstract protected function list();

    /**
     * GET /get?id=X - Obtener un registro por ID
     */
    abstract protected function get();

    /**
     * POST /create - Crear nuevo registro
     */
    abstract protected function create();

    /**
     * PUT|POST /update - Actualizar registro
     */
    abstract protected function update();

    /**
     * DELETE|POST /delete - Eliminar registro
     */
    abstract protected function delete();
}
