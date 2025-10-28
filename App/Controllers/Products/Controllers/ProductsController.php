<?php

namespace App\Controllers\Products\Controllers;

use Core\Controller\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\Product;

/**
 * ProductsController - API REST para productos
 *
 * ENDPOINTS:
 * GET    /api/products/list      - Listar todos los productos
 * GET    /api/products/get       - Obtener un producto por ID
 * POST   /api/products/create    - Crear nuevo producto
 * POST   /api/products/update    - Actualizar producto
 * POST   /api/products/delete    - Eliminar producto
 */
class ProductsController extends CoreController
{
    const ROUTE = 'products';

    public function __construct($accion)
    {
        try {
            $this->$accion();
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error en el servidor: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/products/list
     * Lista todos los productos
     */
    public function list()
    {
        try {
            $products = Product::orderBy('created_at', 'desc')->get()->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Productos obtenidos correctamente',
                $products
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener productos: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/products/get?id=1
     * Obtiene un producto por ID
     */
    public function get()
    {
        try {
            $id = $_GET['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de producto requerido',
                    null
                ));
                return;
            }

            $product = Product::find($id);

            if (!$product) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Producto no encontrado',
                    null
                ));
                return;
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Producto obtenido correctamente',
                $product->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener producto: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/products/create
     * Crea un nuevo producto
     */
    public function create()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validación básica
            if (empty($data['name'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'El nombre es requerido',
                    null
                ));
                return;
            }

            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                'category' => $data['category'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'is_active' => $data['is_active'] ?? true
            ]);

            Response::json(StatusCodes::HTTP_CREATED, (array)new ResponseDTO(
                true,
                'Producto creado correctamente',
                $product->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear producto: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/products/update
     * Actualiza un producto existente
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de producto requerido',
                    null
                ));
                return;
            }

            $product = Product::find($data['id']);

            if (!$product) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Producto no encontrado',
                    null
                ));
                return;
            }

            $product->update([
                'name' => $data['name'] ?? $product->name,
                'description' => $data['description'] ?? $product->description,
                'price' => $data['price'] ?? $product->price,
                'stock' => $data['stock'] ?? $product->stock,
                'category' => $data['category'] ?? $product->category,
                'image_url' => $data['image_url'] ?? $product->image_url,
                'is_active' => isset($data['is_active']) ? $data['is_active'] : $product->is_active
            ]);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Producto actualizado correctamente',
                $product->fresh()->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar producto: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/products/delete
     * Elimina un producto
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de producto requerido',
                    null
                ));
                return;
            }

            $product = Product::find($data['id']);

            if (!$product) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Producto no encontrado',
                    null
                ));
                return;
            }

            $productName = $product->name;
            $product->delete();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                "Producto '{$productName}' eliminado correctamente",
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar producto: ' . $e->getMessage(),
                null
            ));
        }
    }
}
