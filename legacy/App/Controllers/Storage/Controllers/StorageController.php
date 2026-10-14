<?php
/**
 * StorageController - API Controller para operaciones de storage
 *
 * PROPÓSITO:
 * Endpoints REST para upload, list, get y delete de archivos.
 * SIN AUTENTICACIÓN (para testing en Postman).
 *
 * NOTA: En producción, agregar middlewares de autenticación.
 *
 * ENDPOINTS:
 * POST /api/storage/upload      - Subir archivo
 * GET  /api/storage/list         - Listar archivos
 * GET  /api/storage/get          - Obtener info de archivo
 * POST /api/storage/delete       - Eliminar archivo
 * GET  /api/storage/stats        - Estadísticas del storage
 */

namespace App\Controllers\Storage\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use App\Controllers\Storage\Providers\StorageProvider;
use App\Controllers\Storage\Rules\StorageRules;
use Core\Services\Storage\StorageException;

class StorageController extends CoreController
{
    const ROUTE = 'storage'; // Define la ruta /api/storage

    private StorageProvider $provider;
    private StorageRules $rules;

    public function __construct($accion)
    {
        $this->provider = new StorageProvider();
        $this->rules = new StorageRules();

        // Ejecutar la acción solicitada
        $this->$accion();
    }

    /**
     * POST /api/storage/upload
     *
     * Sube un archivo al storage
     *
     * Body (multipart/form-data):
     * - file: archivo a subir (requerido)
     * - name: nombre personalizado (opcional, usa nombre original si no se pasa)
     * - path: ruta dentro del bucket (opcional, default: "temp/")
     *
     * Response:
     * {
     *   "success": true,
     *   "message": "Archivo subido exitosamente",
     *   "data": {
     *     "url": "http://localhost:9000/lego-uploads/images/foto.jpg",
     *     "filename": "foto.jpg",
     *     "path": "images/foto.jpg",
     *     "size": 245678,
     *     "mimeType": "image/jpeg",
     *     "uploadedAt": "2025-10-27 14:30:00"
     *   }
     * }
     */
    public function upload()
    {
        try {
            // Validar que se envió un archivo
            if (empty($_FILES['file'])) {
                Response::json(400, (array)new ResponseDTO(false, 'No se envió ningún archivo', null));
                return;
            }

            $file = $_FILES['file'];

            // Obtener parámetros opcionales
            $customName = $_POST['name'] ?? null;
            $path = $_POST['path'] ?? 'temp/';

            // Validar parámetros
            $validation = $this->rules->validateUpload(['path' => $path]);
            if ($validation->fails()) {
                Response::json(400, (array)new ResponseDTO(false, 'Validación fallida', $validation->errors()->toArray()));
                return;
            }

            // Subir archivo usando el provider
            $result = $this->provider->handleUpload($file, $customName, $path);

            Response::json(200, (array)new ResponseDTO(true, 'Archivo subido exitosamente', $result));

        } catch (StorageException $e) {
            Response::json(400, (array)new ResponseDTO(false, $e->getMessage(), [
                'code' => $e->getCode()
            ]));
        } catch (\Exception $e) {
            Response::json(500, (array)new ResponseDTO(false, 'Error al subir archivo: ' . $e->getMessage(), null));
        }
    }

    /**
     * GET /api/storage/list?path=images/&limit=50
     *
     * Lista archivos en una carpeta
     *
     * Query params:
     * - path: carpeta a listar (opcional, default: raíz)
     * - limit: cantidad máxima de archivos (opcional, default: 100, max: 1000)
     *
     * Response:
     * {
     *   "success": true,
     *   "message": "Archivos obtenidos",
     *   "data": {
     *     "files": [
     *       {
     *         "key": "images/foto.jpg",
     *         "size": 245678,
     *         "lastModified": "2025-10-27 14:30:00",
     *         "url": "http://localhost:9000/lego-uploads/images/foto.jpg"
     *       }
     *     ],
     *     "count": 1,
     *     "truncated": false,
     *     "path": "images/"
     *   }
     * }
     */
    public function list()
    {
        try {
            $path = $_GET['path'] ?? '';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

            // Validar parámetros
            $validation = $this->rules->validateList(['path' => $path, 'limit' => $limit]);
            if ($validation->fails()) {
                Response::json(400, (array)new ResponseDTO(false, 'Validación fallida', $validation->errors()->toArray()));
                return;
            }

            // Listar archivos
            $result = $this->provider->listFiles($path, $limit);

            Response::json(200, (array)new ResponseDTO(true, 'Archivos obtenidos', $result));

        } catch (StorageException $e) {
            Response::json(400, (array)new ResponseDTO(false, $e->getMessage(), null));
        } catch (\Exception $e) {
            Response::json(500, (array)new ResponseDTO(false, 'Error al listar archivos: ' . $e->getMessage(), null));
        }
    }

    /**
     * GET /api/storage/get?file=images/foto.jpg
     *
     * Obtiene información de un archivo específico
     *
     * Query params:
     * - file: ruta del archivo (requerido)
     *
     * Response:
     * {
     *   "success": true,
     *   "message": "Información del archivo",
     *   "data": {
     *     "exists": true,
     *     "path": "images/foto.jpg",
     *     "url": "http://localhost:9000/lego-uploads/images/foto.jpg",
     *     "size": 245678,
     *     "sizeMB": 0.23,
     *     "mimeType": "image/jpeg",
     *     "lastModified": "2025-10-27 14:30:00"
     *   }
     * }
     */
    public function get()
    {
        try {
            $file = $_GET['file'] ?? null;

            // Validar parámetros
            $validation = $this->rules->validateGet(['file' => $file]);
            if ($validation->fails()) {
                Response::json(400, (array)new ResponseDTO(false, 'Validación fallida', $validation->errors()->toArray()));
                return;
            }

            // Obtener información del archivo
            $result = $this->provider->getFileInfo($file);

            Response::json(200, (array)new ResponseDTO(true, 'Información del archivo', $result));

        } catch (StorageException $e) {
            $statusCode = $e->getCode() === StorageException::FILE_NOT_FOUND ? 404 : 400;
            Response::json($statusCode, (array)new ResponseDTO(false, $e->getMessage(), null));
        } catch (\Exception $e) {
            Response::json(500, (array)new ResponseDTO(false, 'Error al obtener archivo: ' . $e->getMessage(), null));
        }
    }

    /**
     * POST /api/storage/delete
     *
     * Elimina un archivo
     *
     * Body (JSON):
     * {
     *   "file": "images/foto.jpg"
     * }
     *
     * Response:
     * {
     *   "success": true,
     *   "message": "Archivo eliminado exitosamente"
     * }
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                Response::json(400, (array)new ResponseDTO(false, 'Datos inválidos', null));
                return;
            }

            // Validar parámetros
            $validation = $this->rules->validateDelete($data);
            if ($validation->fails()) {
                Response::json(400, (array)new ResponseDTO(false, 'Validación fallida', $validation->errors()->toArray()));
                return;
            }

            // Eliminar archivo
            $this->provider->deleteFile($data['file']);

            Response::json(200, (array)new ResponseDTO(true, 'Archivo eliminado exitosamente', null));

        } catch (StorageException $e) {
            Response::json(400, (array)new ResponseDTO(false, $e->getMessage(), null));
        } catch (\Exception $e) {
            Response::json(500, (array)new ResponseDTO(false, 'Error al eliminar archivo: ' . $e->getMessage(), null));
        }
    }

    /**
     * GET /api/storage/stats
     *
     * Obtiene estadísticas del sistema de storage
     *
     * Response:
     * {
     *   "success": true,
     *   "message": "Estadísticas obtenidas",
     *   "data": {
     *     "totalFiles": 342,
     *     "totalSize": 153427968,
     *     "totalSizeMB": 146.32,
     *     "fileTypes": {
     *       "jpg": 125,
     *       "png": 89,
     *       "pdf": 128
     *     },
     *     "bucket": "lego-uploads",
     *     "endpoint": "http://localhost:9000"
     *   }
     * }
     */
    public function stats()
    {
        try {
            $result = $this->provider->getStats();

            Response::json(200, (array)new ResponseDTO(true, 'Estadísticas obtenidas', $result));

        } catch (StorageException $e) {
            Response::json(400, (array)new ResponseDTO(false, $e->getMessage(), null));
        } catch (\Exception $e) {
            Response::json(500, (array)new ResponseDTO(false, 'Error al obtener estadísticas: ' . $e->getMessage(), null));
        }
    }
}
