<?php

namespace App\Controllers\Products\Controllers;

use Core\Controller\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\EntityFile;
use Core\Services\Storage\StorageService;
use Core\Services\File\FileService;

/**
 * ProductsController - API REST para productos
 *
 * ENDPOINTS REST (ProductsCrudV3):
 * GET    /api/products                   - Listar todos los productos
 * GET    /api/products/{id}              - Obtener un producto por ID
 * POST   /api/products                   - Crear nuevo producto
 * PUT    /api/products/{id}              - Actualizar producto
 * DELETE /api/products/{id}              - Eliminar producto
 *
 * ENDPOINTS LEGACY (ProductsCrud V1/V2):
 * GET    /api/products/list              - Listar todos (LEGACY)
 * GET    /api/products/get               - Obtener por ID (LEGACY)
 * POST   /api/products/create            - Crear (LEGACY)
 * POST   /api/products/update            - Actualizar (LEGACY)
 * POST   /api/products/delete            - Eliminar (LEGACY)
 * POST   /api/products/upload_image      - Subir imagen
 * POST   /api/products/delete_image      - Eliminar imagen
 * POST   /api/products/reorder_images    - Reordenar imágenes
 * POST   /api/products/set_primary       - Marcar imagen como principal
 */
class ProductsController extends CoreController
{

    const ROUTE = 'products';
    private FileService $fileService;

    public function __construct($accion)
    {
        $this->fileService = new FileService();

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
     * Obtiene un producto por ID (incluye imágenes vía entity_files)
     */
    public function get()
    {
        try {
            // Obtener ID desde query params para GET request
            $id = $_GET['id'] ?? $_REQUEST['id'] ?? null;

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

            $productData = $product->toArray();

            // Obtener archivos asociados usando FileService (OPCIÓN B: entity_files)
            $fileAssociations = $this->fileService->getEntityFiles('Product', $id);

            // Formatear imágenes
            $productData['images'] = $fileAssociations->map(function($assoc) {
                $file = $assoc->file;
                return [
                    'id' => $file->id,
                    'url' => $file->url,
                    'key' => $file->key,
                    'original_name' => $file->original_name,
                    'size' => $file->size,
                    'size_formatted' => $this->formatBytes($file->size ?? 0),
                    'mime_type' => $file->mime_type,
                    'display_order' => $assoc->display_order,
                    'is_primary' => $assoc->isPrimary(),
                    'created_at' => $file->created_at
                ];
            })->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Producto obtenido correctamente',
                $productData
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

            // Asociar imágenes usando OPCIÓN B: entity_files (polimórfica)
            if (!empty($data['image_ids']) && is_array($data['image_ids'])) {
                foreach ($data['image_ids'] as $index => $imageId) {
                    // Usar FileService para asociar el archivo a la entidad Product
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'Product',
                        $product->id,
                        $index,
                        ['is_primary' => $index === 0] // Primera imagen es la principal
                    );
                }
            }

            Response::json(StatusCodes::HTTP_CREATED, (array)new ResponseDTO(
                true,
                'Producto creado correctamente',
                $product->fresh()->toArray()
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
                'sku' => $data['sku'] ?? $product->sku,
                'description' => $data['description'] ?? $product->description,
                'price' => $data['price'] ?? $product->price,
                'stock' => $data['stock'] ?? $product->stock,
                'min_stock' => $data['min_stock'] ?? $product->min_stock,
                'category' => $data['category'] ?? $product->category,
                'image_url' => $data['image_url'] ?? $product->image_url,
                'is_active' => isset($data['is_active']) ? $data['is_active'] : $product->is_active
            ]);

            // Actualizar asociación de imágenes usando OPCIÓN B: entity_files
            if (isset($data['image_ids']) && is_array($data['image_ids'])) {
                // Eliminar todas las asociaciones actuales de este producto
                \App\Models\EntityFileAssociation::forEntity('Product', $product->id)->delete();

                // Crear nuevas asociaciones
                foreach ($data['image_ids'] as $index => $imageId) {
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'Product',
                        $product->id,
                        $index,
                        ['is_primary' => $index === 0]
                    );
                }
            }

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

    /**
     * POST /api/products/upload_image
     * Sube una imagen de producto a MinIO
     */
    public function upload_image()
    {
        try {
            // Validar que se envió un archivo (FilePond usa 'file' como nombre)
            if (!isset($_FILES['file'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'No se envió ninguna imagen',
                    null
                ));
                return;
            }

            $file = $_FILES['file'];
            $productId = $_POST['product_id'] ?? null;

            // Validar errores de carga
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'Error al cargar el archivo',
                    null
                ));
                return;
            }

            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, WEBP, GIF)',
                    null
                ));
                return;
            }

            // Validar tamaño (5MB máx)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'El archivo excede el tamaño máximo de 5MB',
                    null
                ));
                return;
            }

            // Validar que el producto existe (si se proporciona ID)
            if ($productId) {
                $product = Product::find($productId);
                if (!$product) {
                    Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                        false,
                        'Producto no encontrado',
                        null
                    ));
                    return;
                }
            }

            // Generar nombre único para el archivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('product_', true) . '.' . $extension;

            // Subir a MinIO usando StorageService
            // Firma: upload(array $file, ?string $customName, string $path): string
            $storage = new StorageService();
            $url = $storage->upload($file, $filename, 'products/images/');

            // Construir la key para referencia
            $key = 'products/images/' . $filename;

            // SIEMPRE guardar en BD (con o sin product_id)
            // Si no hay product_id, se guarda con NULL para asociar después

            $maxOrder = 0;
            $isPrimary = false;

            if ($productId) {
                // Determinar el orden (último + 1)
                $maxOrder = ProductImage::where('product_id', $productId)->max('display_order') ?? -1;
                $maxOrder++;

                // Determinar si es la primera imagen (será primary)
                $isPrimary = ProductImage::where('product_id', $productId)->count() === 0;
            }

            $productImage = ProductImage::create([
                'product_id' => $productId, // Puede ser NULL
                'url' => $url,
                'key' => $key,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'mime_type' => $mimeType,
                'display_order' => $maxOrder,
                'is_primary' => $isPrimary
            ]);

            // FilePond espera solo el ID del archivo como texto plano
            header('Content-Type: text/plain');
            echo $productImage->id;
            exit();
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al subir imagen: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/products/delete_image
     * Elimina una imagen de producto
     */
    public function delete_image()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $imageIdentifier = $data['image_id'] ?? $data['id'] ?? null;

            if (!$imageIdentifier) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de imagen requerido',
                    null
                ));
                return;
            }

            // Buscar por ID numérico o por URL
            if (is_numeric($imageIdentifier)) {
                $image = ProductImage::find($imageIdentifier);
            } else {
                // Si es una URL, buscar por el campo 'url'
                $image = ProductImage::where('url', $imageIdentifier)->first();
            }

            if (!$image) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Imagen no encontrada',
                    null
                ));
                return;
            }

            // Eliminar de MinIO
            try {
                $storage = new StorageService();
                $storage->delete($image->key);
            } catch (\Exception $e) {
                // Log error pero continuar con eliminación de BD
                error_log("Error al eliminar imagen de MinIO: " . $e->getMessage());
            }

            // Si era primary, marcar la siguiente como primary
            if ($image->is_primary) {
                $nextImage = ProductImage::where('product_id', $image->product_id)
                    ->where('id', '!=', $image->id)
                    ->orderBy('display_order')
                    ->first();

                if ($nextImage) {
                    $nextImage->update(['is_primary' => true]);
                }
            }

            // Eliminar de BD
            $image->delete();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Imagen eliminada correctamente',
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar imagen: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/products/reorder_images
     * Reordena las imágenes de un producto
     */
    public function reorder_images()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $images = $data['images'] ?? [];

            if (empty($images)) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'Datos de ordenamiento requeridos',
                    null
                ));
                return;
            }

            // Actualizar orden de cada imagen
            foreach ($images as $imageData) {
                $imageId = $imageData['id'] ?? null;
                $order = $imageData['order'] ?? null;

                if ($imageId !== null && $order !== null) {
                    ProductImage::where('id', $imageId)->update(['order' => $order]);
                }
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Orden actualizado correctamente',
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al reordenar imágenes: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/products/set_primary
     * Marca una imagen como principal
     */
    public function set_primary()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $imageId = $data['image_id'] ?? null;

            if (!$imageId) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de imagen requerido',
                    null
                ));
                return;
            }

            $image = ProductImage::find($imageId);

            if (!$image) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Imagen no encontrada',
                    null
                ));
                return;
            }

            // Desmarcar todas las imágenes del producto como primary
            ProductImage::where('product_id', $image->product_id)
                ->update(['is_primary' => false]);

            // Marcar esta imagen como primary
            $image->update(['is_primary' => true]);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Imagen principal actualizada',
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar imagen principal: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * Helper: Formatea bytes a formato legible
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
