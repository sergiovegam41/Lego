<?php

namespace App\Controllers\Categories\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\Category;
use Core\Services\Storage\StorageService;
use Core\Services\File\FileService;

/**
 * CategoriesController - API REST para categories
 *
 * ENDPOINTS:
 * GET    /api/categories/list       - Listar todas las categorías
 * GET    /api/categories/get        - Obtener una categoría por ID
 * POST   /api/categories/create     - Crear nueva categoría
 * POST   /api/categories/update     - Actualizar categoría
 * POST   /api/categories/delete     - Eliminar categoría
 * POST   /api/categories/upload_image - Subir imagen de categoría
 */
class CategoriesController extends CoreController
{
    const ROUTE = 'categories';

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
     * GET /api/categories/list
     * Lista todas las categorías
     */
    public function list()
    {
        try {
            $categories = Category::withCount('flowers')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Categorías obtenidas correctamente',
                $categories
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener categorías: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/categories/get?id=1
     * Obtiene una categoría por ID
     */
    public function get()
    {
        try {
            $id = $_GET['id'] ?? $_REQUEST['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de categoría requerido',
                    null
                ));
                return;
            }

            $category = Category::with('flowers')->find($id);

            if (!$category) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Categoría no encontrada',
                    null
                ));
                return;
            }

            // Obtener datos de la categoría sin appends para evitar errores
            $categoryData = $category->makeHidden(['primary_image'])->toArray();

            // Cargar imágenes asociadas usando FileService
            $categoryData['images'] = [];
            try {
                if (class_exists('Core\Services\File\FileService')) {
                    $fileService = new FileService();
                    $fileAssociations = $fileService->getEntityFiles('Category', $id);

                    if ($fileAssociations && !$fileAssociations->isEmpty()) {
                        // Formatear imágenes
                        $categoryData['images'] = $fileAssociations->map(function($assoc) {
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
                error_log("Error loading images for category {$id}: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $categoryData['images'] = [];
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Categoría obtenida correctamente',
                $categoryData
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener categoría: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/categories/create
     * Crea una nueva categoría
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

            $category = Category::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'is_active' => $data['is_active'] ?? true
            ]);

            // Asociar imágenes usando FileService (patrón polimórfico)
            if (!empty($data['image_ids']) && is_array($data['image_ids'])) {
                foreach ($data['image_ids'] as $index => $imageId) {
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'Category',
                        $category->id,
                        $index,
                        ['is_primary' => $index === 0]
                    );
                }
            }

            Response::json(StatusCodes::HTTP_CREATED, (array)new ResponseDTO(
                true,
                'Categoría creada correctamente',
                $category->fresh()->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear categoría: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/categories/update
     * Actualiza una categoría existente
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de categoría requerido',
                    null
                ));
                return;
            }

            $category = Category::find($data['id']);

            if (!$category) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Categoría no encontrada',
                    null
                ));
                return;
            }

            $category->update([
                'name' => $data['name'] ?? $category->name,
                'description' => $data['description'] ?? $category->description,
                'image_url' => $data['image_url'] ?? $category->image_url,
                'is_active' => isset($data['is_active']) ? $data['is_active'] : $category->is_active
            ]);

            // Actualizar imágenes usando FileService (patrón polimórfico)
            if (isset($data['image_ids']) && is_array($data['image_ids'])) {
                // Eliminar todas las asociaciones actuales
                \App\Models\EntityFileAssociation::forEntity('Category', $category->id)->delete();

                // Crear nuevas asociaciones con el orden correcto
                foreach ($data['image_ids'] as $index => $imageId) {
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'Category',
                        $category->id,
                        $index,
                        ['is_primary' => $index === 0]
                    );
                }
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Categoría actualizada correctamente',
                $category->fresh()->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar categoría: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/categories/delete
     * Elimina una categoría
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de categoría requerido',
                    null
                ));
                return;
            }

            $category = Category::find($data['id']);

            if (!$category) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Categoría no encontrada',
                    null
                ));
                return;
            }

            // Verificar si tiene flores asociadas
            $flowerCount = $category->flowers()->count();
            if ($flowerCount > 0) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    "No se puede eliminar la categoría porque tiene {$flowerCount} flores asociadas",
                    null
                ));
                return;
            }

            // Eliminar todas las imágenes asociadas usando FileService
            try {
                $fileAssociations = $this->fileService->getEntityFiles('Category', $category->id);
                $storage = new StorageService();

                if ($fileAssociations && !$fileAssociations->isEmpty()) {
                    foreach ($fileAssociations as $assoc) {
                        if ($assoc && isset($assoc->file)) {
                            try {
                                // Extraer la key de la URL
                                $key = parse_url($assoc->file->url, PHP_URL_PATH);
                                $key = ltrim($key, '/');
                                $storage->delete($key);

                                // Eliminar el registro del archivo
                                $assoc->file->delete();
                            } catch (\Exception $e) {
                                error_log("Error al eliminar imagen de categoría: " . $e->getMessage());
                            }
                        }
                    }
                }

                // Eliminar asociaciones de archivos
                \App\Models\EntityFileAssociation::forEntity('Category', $category->id)->delete();
            } catch (\Exception $e) {
                error_log("Error al eliminar imágenes de categoría {$category->id}: " . $e->getMessage());
            }

            $categoryName = $category->name;
            $category->delete();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                "Categoría '{$categoryName}' eliminada correctamente",
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar categoría: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/categories/upload_image
     * Sube una imagen de categoría a MinIO
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
            $filename = uniqid('category_', true) . '.' . $extension;

            // Subir a MinIO
            $storage = new StorageService();
            $url = $storage->upload($file, $filename, 'categories/images/');

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Imagen subida correctamente',
                ['url' => $url]
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al subir imagen: ' . $e->getMessage(),
                null
            ));
        }
    }
}
