<?php

namespace App\Controllers\ExampleCrud\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\ExampleCrud;
use App\Models\ExampleCrudImage;
use App\Models\EntityFile;
use Core\Services\Storage\StorageService;
use Core\Services\File\FileService;

/**
 * ExampleCrudController - API REST para example_crud
 *
 * FILOSOFÍA LEGO:
 * Controlador de ejemplo/template que demuestra implementación completa de CRUD.
 * Sirve como referencia para construir otros controladores en el framework.
 *
 * ENDPOINTS REST:
 * GET    /api/example-crud              - Listar todos los registros
 * GET    /api/example-crud/{id}         - Obtener un registro por ID
 * POST   /api/example-crud              - Crear nuevo registro
 * PUT    /api/example-crud/{id}         - Actualizar registro
 * DELETE /api/example-crud/{id}         - Eliminar registro
 *
 * ENDPOINTS LEGACY (compatibilidad):
 * GET    /api/example-crud/list         - Listar todos (LEGACY)
 * GET    /api/example-crud/get          - Obtener por ID (LEGACY)
 * POST   /api/example-crud/create       - Crear (LEGACY)
 * POST   /api/example-crud/update       - Actualizar (LEGACY)
 * POST   /api/example-crud/delete       - Eliminar (LEGACY)
 * POST   /api/example-crud/upload_image - Subir imagen
 * POST   /api/example-crud/delete_image - Eliminar imagen
 * POST   /api/example-crud/reorder_images - Reordenar imágenes
 * POST   /api/example-crud/set_primary  - Marcar imagen como principal
 */
class ExampleCrudController extends CoreController
{

    const ROUTE = 'example-crud';
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
     * GET /api/example-crud/list
     * Lista todos los registros
     */
    public function list()
    {
        try {
            $records = ExampleCrud::orderBy('created_at', 'desc')->get()->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Registros obtenidos correctamente',
                $records
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener registros: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/example-crud/get?id=1
     * Obtiene un registro por ID (incluye imágenes vía entity_files)
     */
    public function get()
    {
        try {
            // Obtener ID desde query params para GET request
            $id = $_GET['id'] ?? $_REQUEST['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de registro requerido',
                    null
                ));
                return;
            }

            $record = ExampleCrud::find($id);

            if (!$record) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Registro no encontrado',
                    null
                ));
                return;
            }

            $recordData = $record->toArray();

            // Obtener archivos asociados usando FileService (OPCIÓN B: entity_files)
            $fileAssociations = $this->fileService->getEntityFiles('ExampleCrud', $id);

            // Formatear imágenes
            $recordData['images'] = $fileAssociations->map(function($assoc) {
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
                'Registro obtenido correctamente',
                $recordData
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener registro: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/example-crud/create
     * Crea un nuevo registro
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

            $record = ExampleCrud::create([
                'name' => $data['name'],
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'price' => $data['price'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                'min_stock' => $data['min_stock'] ?? 5,
                'category' => $data['category'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'is_active' => $data['is_active'] ?? true
            ]);

            // Asociar imágenes usando OPCIÓN B: entity_files (polimórfica)
            if (!empty($data['image_ids']) && is_array($data['image_ids'])) {
                foreach ($data['image_ids'] as $index => $imageId) {
                    // Usar FileService para asociar el archivo a la entidad ExampleCrud
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'ExampleCrud',
                        $record->id,
                        $index,
                        ['is_primary' => $index === 0] // Primera imagen es la principal
                    );
                }
            }

            Response::json(StatusCodes::HTTP_CREATED, (array)new ResponseDTO(
                true,
                'Registro creado correctamente',
                $record->fresh()->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear registro: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/example-crud/update
     * Actualiza un registro existente
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de registro requerido',
                    null
                ));
                return;
            }

            $record = ExampleCrud::find($data['id']);

            if (!$record) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Registro no encontrado',
                    null
                ));
                return;
            }

            $record->update([
                'name' => $data['name'] ?? $record->name,
                'sku' => $data['sku'] ?? $record->sku,
                'description' => $data['description'] ?? $record->description,
                'price' => $data['price'] ?? $record->price,
                'stock' => $data['stock'] ?? $record->stock,
                'min_stock' => $data['min_stock'] ?? $record->min_stock,
                'category' => $data['category'] ?? $record->category,
                'image_url' => $data['image_url'] ?? $record->image_url,
                'is_active' => isset($data['is_active']) ? $data['is_active'] : $record->is_active
            ]);

            // Actualizar asociación de imágenes usando OPCIÓN B: entity_files
            if (isset($data['image_ids']) && is_array($data['image_ids'])) {
                // Eliminar todas las asociaciones actuales de este registro
                \App\Models\EntityFileAssociation::forEntity('ExampleCrud', $record->id)->delete();

                // Crear nuevas asociaciones
                foreach ($data['image_ids'] as $index => $imageId) {
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'ExampleCrud',
                        $record->id,
                        $index,
                        ['is_primary' => $index === 0]
                    );
                }
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Registro actualizado correctamente',
                $record->fresh()->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar registro: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/example-crud/delete
     * Elimina un registro
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de registro requerido',
                    null
                ));
                return;
            }

            $record = ExampleCrud::find($data['id']);

            if (!$record) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Registro no encontrado',
                    null
                ));
                return;
            }

            $recordName = $record->name;
            $record->delete();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                "Registro '{$recordName}' eliminado correctamente",
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar registro: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/example-crud/upload_image
     * Sube una imagen de registro a MinIO
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
            $recordId = $_POST['example_crud_id'] ?? null;

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

            // Validar que el registro existe (si se proporciona ID)
            if ($recordId) {
                $record = ExampleCrud::find($recordId);
                if (!$record) {
                    Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                        false,
                        'Registro no encontrado',
                        null
                    ));
                    return;
                }
            }

            // Generar nombre único para el archivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('example-crud_', true) . '.' . $extension;

            // Subir a MinIO usando StorageService
            // Firma: upload(array $file, ?string $customName, string $path): string
            $storage = new StorageService();
            $url = $storage->upload($file, $filename, 'example-crud/images/');

            // Construir la key para referencia
            $key = 'example-crud/images/' . $filename;

            // SIEMPRE guardar en BD (con o sin example_crud_id)
            // Si no hay example_crud_id, se guarda con NULL para asociar después

            $maxOrder = 0;
            $isPrimary = false;

            if ($recordId) {
                // Determinar el orden (último + 1)
                $maxOrder = ExampleCrudImage::where('example_crud_id', $recordId)->max('display_order') ?? -1;
                $maxOrder++;

                // Determinar si es la primera imagen (será primary)
                $isPrimary = ExampleCrudImage::where('example_crud_id', $recordId)->count() === 0;
            }

            $image = ExampleCrudImage::create([
                'example_crud_id' => $recordId, // Puede ser NULL
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
     * POST /api/example-crud/delete_image
     * Elimina una imagen de registro
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
                $image = ExampleCrudImage::find($imageIdentifier);
            } else {
                // Si es una URL, buscar por el campo 'url'
                $image = ExampleCrudImage::where('url', $imageIdentifier)->first();
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
                $nextImage = ExampleCrudImage::where('example_crud_id', $image->example_crud_id)
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
     * POST /api/example-crud/reorder_images
     * Reordena las imágenes de un registro
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
                    ExampleCrudImage::where('id', $imageId)->update(['display_order' => $order]);
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
     * POST /api/example-crud/set_primary
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

            $image = ExampleCrudImage::find($imageId);

            if (!$image) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Imagen no encontrada',
                    null
                ));
                return;
            }

            // Desmarcar todas las imágenes del registro como primary
            ExampleCrudImage::where('example_crud_id', $image->example_crud_id)
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
