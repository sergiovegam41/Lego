<?php

namespace App\Controllers\Files\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use Core\Services\File\FileService;
use Core\Services\File\FileValidationException;

/**
 * FilesController - API UNIVERSAL para gestión de archivos
 *
 * FILOSOFÍA LEGO:
 * Controlador GENÉRICO no acoplado a ninguna entidad específica.
 * Cualquier CRUD puede usar estos endpoints para subir/eliminar archivos.
 *
 * ENDPOINTS:
 * POST   /api/files/upload        - Subir archivo (retorna file ID)
 * POST   /api/files/delete        - Eliminar archivo por ID
 * GET    /api/files/{id}          - Obtener información de archivo
 * GET    /api/files               - Listar archivos (con filtros opcionales)
 *
 * PATRÓN DE USO:
 * 1. FilePond → POST /api/files/upload → retorna file ID como text/plain
 * 2. Frontend guarda IDs en memoria
 * 3. Al guardar entidad → envía lista de file IDs
 * 4. ProductsController (u otro) solo guarda IDs, NO sube archivos
 * 5. Al consultar → obtiene file IDs → llama a FileService::getFilesByIds()
 *
 * BENEFICIOS:
 * - Zero reimplementación para nuevos CRUDs
 * - Separación clara de responsabilidades
 * - Reutilizable para productos, artículos, usuarios, etc.
 */
class FilesController extends CoreController
{
    const ROUTE = 'files';

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
     * POST /api/files/upload
     * Sube un archivo y retorna su ID
     *
     * IMPORTANTE: FilePond espera el ID como text/plain (no JSON)
     *
     * Request:
     * - FormData con campo 'file'
     * - Opcional: 'path' (ej: 'products/images/', default: 'general/')
     * - Opcional: 'allowed_types' (JSON array)
     * - Opcional: 'max_size' (en bytes)
     *
     * Response:
     * - 200: ID del archivo como text/plain (ej: "42")
     * - 400: Error de validación
     * - 500: Error del servidor
     */
    public function upload()
    {
        try {
            // Validar que se envió un archivo
            if (!isset($_FILES['file'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'No se envió ningún archivo',
                    null
                ));
                return;
            }

            $file = $_FILES['file'];

            // Obtener path (default: 'general/')
            $path = $_POST['path'] ?? 'general/';

            // Opciones de validación
            $options = [];

            // Allowed types (si se especifica)
            if (isset($_POST['allowed_types'])) {
                $options['allowedTypes'] = json_decode($_POST['allowed_types'], true);
            }

            // Max size (si se especifica)
            if (isset($_POST['max_size'])) {
                $options['maxSize'] = (int)$_POST['max_size'];
            }

            // Subir archivo usando FileService
            $entityFile = $this->fileService->uploadFile($file, $path, $options);

            // FilePond espera el ID como text/plain (NO JSON)
            header('Content-Type: text/plain');
            echo $entityFile->id;
            exit();

        } catch (FileValidationException $e) {
            Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                false,
                $e->getMessage(),
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al subir archivo: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/files/delete
     * Elimina un archivo por ID (de MinIO, BD, y asociaciones entity_files)
     *
     * Request Body (JSON):
     * {
     *   "file_id": 42
     * }
     *
     * Response:
     * {
     *   "success": true,
     *   "msj": "Archivo eliminado exitosamente",
     *   "data": null
     * }
     */
    public function delete()
    {
        try {
            // Obtener datos del request
            $data = json_decode(file_get_contents('php://input'), true);

            error_log('[FilesController] delete() - Request data: ' . json_encode($data));

            if (!isset($data['file_id'])) {
                error_log('[FilesController] delete() - file_id not provided in request');
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de archivo no proporcionado',
                    null
                ));
                return;
            }

            $fileId = (int)$data['file_id'];
            error_log('[FilesController] delete() - Attempting to delete file ID: ' . $fileId);

            // Eliminar usando FileService (incluye asociaciones entity_files por CASCADE)
            // deleteFileAndAssociations elimina de MinIO, files table, y entity_files (CASCADE)
            $this->fileService->deleteFileAndAssociations($fileId);

            error_log('[FilesController] delete() - File deleted successfully');

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Archivo eliminado exitosamente',
                null
            ));

        } catch (\Exception $e) {
            error_log('[FilesController] delete() - Error: ' . $e->getMessage());
            error_log('[FilesController] delete() - Stack trace: ' . $e->getTraceAsString());
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar archivo: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/files/{id}
     * Obtiene información de un archivo por ID
     *
     * Response:
     * {
     *   "success": true,
     *   "msj": "Archivo encontrado",
     *   "data": {
     *     "id": 42,
     *     "url": "http://...",
     *     "key": "products/images/file_xxx.jpg",
     *     "original_name": "foto.jpg",
     *     "size": 102400,
     *     "mime_type": "image/jpeg",
     *     "created_at": "2025-01-30 12:00:00"
     *   }
     * }
     */
    public function get()
    {
        try {
            // Obtener ID del query param
            $id = $_GET['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de archivo no proporcionado',
                    null
                ));
                return;
            }

            $file = $this->fileService->getFileById((int)$id);

            if (!$file) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Archivo no encontrado',
                    null
                ));
                return;
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Archivo encontrado',
                $file->toArray()
            ));

        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener archivo: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/files
     * Lista archivos (con filtros opcionales)
     *
     * Query params:
     * - ids: Lista de IDs separados por coma (ej: "1,2,3")
     * - mime_type: Filtrar por tipo MIME (ej: "image/jpeg")
     * - limit: Cantidad máxima (default: 100)
     *
     * Response:
     * {
     *   "success": true,
     *   "msj": "Archivos encontrados",
     *   "data": [...]
     * }
     */
    public function list()
    {
        try {
            // Si se especifican IDs, obtener solo esos
            if (isset($_GET['ids'])) {
                $ids = array_map('intval', explode(',', $_GET['ids']));
                $files = $this->fileService->getFilesByIds($ids);

                Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                    true,
                    'Archivos encontrados',
                    $files->toArray()
                ));
                return;
            }

            // Obtener solo imágenes (por ahora)
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $files = $this->fileService->getImages($limit);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Archivos encontrados',
                $files->toArray()
            ));

        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al listar archivos: ' . $e->getMessage(),
                null
            ));
        }
    }
}
