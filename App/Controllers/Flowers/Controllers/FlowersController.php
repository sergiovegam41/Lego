<?php

namespace App\Controllers\Flowers\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\Flower;
use App\Models\FlowerImage;
use Core\Services\Storage\StorageService;
use Core\Services\File\FileService;

/**
 * FlowersController - API REST para flowers
 *
 * ENDPOINTS:
 * GET    /api/flowers/list          - Listar todas las flores
 * GET    /api/flowers/get           - Obtener una flor por ID
 * POST   /api/flowers/create        - Crear nueva flor
 * POST   /api/flowers/update        - Actualizar flor
 * POST   /api/flowers/delete        - Eliminar flor
 * POST   /api/flowers/upload_image  - Subir imagen de flor
 * POST   /api/flowers/delete_image  - Eliminar imagen de flor
 * POST   /api/flowers/reorder_images - Reordenar imágenes
 * POST   /api/flowers/set_primary   - Marcar imagen como principal
 */
class FlowersController extends CoreController
{
    const ROUTE = 'flowers';

    private $fileService;

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
     * GET /api/flowers/list
     * Lista todas las flores
     */
    public function list()
    {
        try {
            $flowers = Flower::with('category')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Flores obtenidas correctamente',
                $flowers
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener flores: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/flowers/get?id=1
     * Obtiene una flor por ID (incluye imágenes)
     */
    public function get()
    {
        try {
            $id = $_GET['id'] ?? $_REQUEST['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de flor requerido',
                    null
                ));
                return;
            }

            $flower = Flower::with('category')->find($id);

            if (!$flower) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Flor no encontrada',
                    null
                ));
                return;
            }

            // Obtener datos de la flor sin appends para evitar errores
            $flowerData = $flower->makeHidden(['primary_image'])->toArray();

            // Cargar imágenes asociadas usando FileService
            $flowerData['images'] = [];
            try {
                if (class_exists('Core\Services\File\FileService')) {
                    $fileService = new FileService();
                    $fileAssociations = $fileService->getEntityFiles('Flower', $id);

                    if ($fileAssociations && !$fileAssociations->isEmpty()) {
                        // Formatear imágenes
                        $flowerData['images'] = $fileAssociations->map(function($assoc) {
                            if (!$assoc || !isset($assoc->file)) {
                                return null;
                            }
                            $file = $assoc->file;
                            return [
                                'id' => $file->id ?? null,
                                'url' => $file->url ?? null,
                                'original_name' => $file->original_name ?? 'image.jpg',
                                'size' => $file->size ?? 0,
                                'mime_type' => $file->mime_type ?? 'image/jpeg',
                                'is_primary' => ($assoc->metadata['is_primary'] ?? false) === true
                            ];
                        })->filter()->values()->toArray();
                    }
                }
            } catch (\Exception $e) {
                // Si hay error cargando imágenes, usar array vacío
                error_log("Error loading images for flower {$id}: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $flowerData['images'] = [];
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Flor obtenida correctamente',
                $flowerData
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener flor: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/flowers/create
     * Crea una nueva flor
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

            if (empty($data['category_id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'La categoría es requerida',
                    null
                ));
                return;
            }

            if (!isset($data['price']) || $data['price'] < 0) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'El precio es requerido y debe ser mayor o igual a 0',
                    null
                ));
                return;
            }

            $flower = Flower::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'category_id' => $data['category_id'],
                'is_active' => $data['is_active'] ?? true
            ]);

            // Asociar imágenes usando FileService (patrón polimórfico)
            if (!empty($data['image_ids']) && is_array($data['image_ids'])) {
                foreach ($data['image_ids'] as $index => $imageId) {
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'Flower',
                        $flower->id,
                        $index,
                        ['is_primary' => $index === 0]
                    );
                }
            }

            Response::json(StatusCodes::HTTP_CREATED, (array)new ResponseDTO(
                true,
                'Flor creada correctamente',
                $flower->fresh()->load('category')->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear flor: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/flowers/update
     * Actualiza una flor existente
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de flor requerido',
                    null
                ));
                return;
            }

            $flower = Flower::find($data['id']);

            if (!$flower) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Flor no encontrada',
                    null
                ));
                return;
            }

            $flower->update([
                'name' => $data['name'] ?? $flower->name,
                'description' => $data['description'] ?? $flower->description,
                'price' => $data['price'] ?? $flower->price,
                'category_id' => $data['category_id'] ?? $flower->category_id,
                'is_active' => isset($data['is_active']) ? $data['is_active'] : $flower->is_active
            ]);

            // Actualizar imágenes usando FileService (patrón polimórfico)
            if (isset($data['image_ids']) && is_array($data['image_ids'])) {
                // Eliminar todas las asociaciones actuales
                \App\Models\EntityFileAssociation::forEntity('Flower', $flower->id)->delete();

                // Crear nuevas asociaciones con el orden correcto
                foreach ($data['image_ids'] as $index => $imageId) {
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'Flower',
                        $flower->id,
                        $index,
                        ['is_primary' => $index === 0]
                    );
                }
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Flor actualizada correctamente',
                $flower->fresh()->load('category')->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar flor: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/flowers/delete
     * Elimina una flor
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de flor requerido',
                    null
                ));
                return;
            }

            $flower = Flower::find($data['id']);

            if (!$flower) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Flor no encontrada',
                    null
                ));
                return;
            }

            // Eliminar todas las imágenes asociadas de MinIO
            $images = $flower->images;
            $storage = new StorageService();

            foreach ($images as $image) {
                try {
                    // Extraer la key de la URL
                    $key = parse_url($image->image_url, PHP_URL_PATH);
                    $key = ltrim($key, '/');
                    $storage->delete($key);
                } catch (\Exception $e) {
                    error_log("Error al eliminar imagen de flor: " . $e->getMessage());
                }
            }

            $flowerName = $flower->name;
            $flower->delete(); // Las imágenes se eliminan en cascada

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                "Flor '{$flowerName}' eliminada correctamente",
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar flor: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/flowers/upload_image
     * Sube una imagen de flor a MinIO
     */
    public function upload_image()
    {
        try {
            if (!isset($_FILES['file'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'No se envió ninguna imagen',
                    null
                ));
                return;
            }

            $file = $_FILES['file'];
            $flowerId = $_POST['flower_id'] ?? null;

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
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'El archivo excede el tamaño máximo de 5MB',
                    null
                ));
                return;
            }

            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('flower_', true) . '.' . $extension;

            // Subir a MinIO
            $storage = new StorageService();
            $url = $storage->upload($file, $filename, 'flowers/images/');

            // Crear registro en BD
            $maxOrder = 0;
            $isPrimary = false;

            if ($flowerId) {
                $maxOrder = FlowerImage::where('flower_id', $flowerId)->max('sort_order') ?? -1;
                $maxOrder++;
                $isPrimary = FlowerImage::where('flower_id', $flowerId)->count() === 0;
            }

            $image = FlowerImage::create([
                'flower_id' => $flowerId, // Puede ser NULL
                'image_url' => $url,
                'sort_order' => $maxOrder,
                'is_primary' => $isPrimary
            ]);

            // Respuesta para FilePond (solo ID)
            header('Content-Type: text/plain');
            echo $image->id;
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
     * POST /api/flowers/delete_image
     * Elimina una imagen de flor
     */
    public function delete_image()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $imageId = $data['image_id'] ?? $data['id'] ?? null;

            if (!$imageId) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de imagen requerido',
                    null
                ));
                return;
            }

            $image = FlowerImage::find($imageId);

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
                $key = parse_url($image->image_url, PHP_URL_PATH);
                $key = ltrim($key, '/');
                $storage->delete($key);
            } catch (\Exception $e) {
                error_log("Error al eliminar imagen de MinIO: " . $e->getMessage());
            }

            // Si era primary, marcar la siguiente como primary
            if ($image->is_primary && $image->flower_id) {
                $nextImage = FlowerImage::where('flower_id', $image->flower_id)
                    ->where('id', '!=', $image->id)
                    ->orderBy('sort_order')
                    ->first();

                if ($nextImage) {
                    $nextImage->update(['is_primary' => true]);
                }
            }

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
     * POST /api/flowers/reorder_images
     * Reordena las imágenes de una flor
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

            foreach ($images as $imageData) {
                $imageId = $imageData['id'] ?? null;
                $order = $imageData['order'] ?? null;

                if ($imageId !== null && $order !== null) {
                    FlowerImage::where('id', $imageId)->update(['sort_order' => $order]);
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
     * POST /api/flowers/set_primary
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

            $image = FlowerImage::find($imageId);

            if (!$image) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Imagen no encontrada',
                    null
                ));
                return;
            }

            // Desmarcar todas las imágenes de la flor
            if ($image->flower_id) {
                FlowerImage::where('flower_id', $image->flower_id)
                    ->update(['is_primary' => false]);
            }

            // Marcar esta como primary
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
}
