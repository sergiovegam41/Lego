<?php

namespace App\Controllers\Tools\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\Tool;
use App\Models\ToolFeature;
use Core\Services\File\FileService;
use Core\Attributes\ApiRoutes;

/**
 * ToolsController - API REST para herramientas
 *
 * FILOSOFÍA LEGO:
 * Controlador CRUD completo para el módulo de Herramientas.
 * Maneja: nombre, descripción, características (lista de strings), imágenes.
 *
 * AUTO-REGISTRO DE RUTAS:
 * El atributo #[ApiRoutes] registra automáticamente:
 * GET    /api/tools/list         - Listar todas las herramientas
 * GET    /api/tools/get          - Obtener una herramienta por ID
 * POST   /api/tools/create       - Crear nueva herramienta
 * POST   /api/tools/update       - Actualizar herramienta
 * POST   /api/tools/delete       - Eliminar herramienta
 */
#[ApiRoutes('/tools', preset: 'crud')]
class ToolsController extends CoreController
{
    const ROUTE = 'tools';
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
     * GET /api/tools/list
     * Lista todas las herramientas
     */
    public function list()
    {
        try {
            $records = Tool::with('features')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($tool) {
                    $data = $tool->toArray();
                    $data['features_count'] = $tool->features->count();
                    $data['features_list'] = $tool->features->pluck('feature')->toArray();
                    return $data;
                })
                ->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Herramientas obtenidas correctamente',
                $records
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener herramientas: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/tools/get?id=1
     * Obtiene una herramienta por ID (incluye características e imágenes)
     */
    public function get()
    {
        try {
            $id = $_GET['id'] ?? $_REQUEST['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de herramienta requerido',
                    null
                ));
                return;
            }

            $record = Tool::with('features')->find($id);

            if (!$record) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Herramienta no encontrada',
                    null
                ));
                return;
            }

            $recordData = $record->toArray();
            
            // Agregar lista de características como array de strings
            $recordData['features_list'] = $record->features->pluck('feature')->toArray();

            // Obtener archivos asociados usando FileService (entity_files)
            $fileAssociations = $this->fileService->getEntityFiles('Tool', $id);

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
                'Herramienta obtenida correctamente',
                $recordData
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener herramienta: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/tools/create
     * Crea una nueva herramienta
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

            // Crear herramienta
            $record = Tool::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true
            ]);

            // Crear características
            if (!empty($data['features']) && is_array($data['features'])) {
                foreach ($data['features'] as $index => $feature) {
                    if (!empty(trim($feature))) {
                        ToolFeature::create([
                            'tool_id' => $record->id,
                            'feature' => trim($feature),
                            'display_order' => $index
                        ]);
                    }
                }
            }

            // Asociar imágenes usando entity_files
            if (!empty($data['image_ids']) && is_array($data['image_ids'])) {
                foreach ($data['image_ids'] as $index => $imageId) {
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'Tool',
                        $record->id,
                        $index,
                        ['is_primary' => $index === 0]
                    );
                }
            }

            // Recargar con relaciones
            $record = Tool::with('features')->find($record->id);

            Response::json(StatusCodes::HTTP_CREATED, (array)new ResponseDTO(
                true,
                'Herramienta creada correctamente',
                $record->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear herramienta: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/tools/update
     * Actualiza una herramienta existente
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de herramienta requerido',
                    null
                ));
                return;
            }

            $record = Tool::find($data['id']);

            if (!$record) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Herramienta no encontrada',
                    null
                ));
                return;
            }

            // Actualizar campos básicos
            $record->update([
                'name' => $data['name'] ?? $record->name,
                'description' => $data['description'] ?? $record->description,
                'is_active' => isset($data['is_active']) ? $data['is_active'] : $record->is_active
            ]);

            // Actualizar características (reemplazar todas)
            if (isset($data['features']) && is_array($data['features'])) {
                // Eliminar características existentes
                ToolFeature::where('tool_id', $record->id)->delete();
                
                // Crear nuevas características
                foreach ($data['features'] as $index => $feature) {
                    if (!empty(trim($feature))) {
                        ToolFeature::create([
                            'tool_id' => $record->id,
                            'feature' => trim($feature),
                            'display_order' => $index
                        ]);
                    }
                }
            }

            // Actualizar asociación de imágenes
            if (isset($data['image_ids']) && is_array($data['image_ids'])) {
                // Eliminar asociaciones actuales
                \App\Models\EntityFileAssociation::forEntity('Tool', $record->id)->delete();

                // Crear nuevas asociaciones
                foreach ($data['image_ids'] as $index => $imageId) {
                    $this->fileService->associateFileToEntity(
                        $imageId,
                        'Tool',
                        $record->id,
                        $index,
                        ['is_primary' => $index === 0]
                    );
                }
            }

            // Recargar con relaciones
            $record = Tool::with('features')->find($record->id);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Herramienta actualizada correctamente',
                $record->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar herramienta: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/tools/delete
     * Elimina una herramienta
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de herramienta requerido',
                    null
                ));
                return;
            }

            $record = Tool::find($data['id']);

            if (!$record) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Herramienta no encontrada',
                    null
                ));
                return;
            }

            $recordName = $record->name;
            
            // Las características se eliminan automáticamente por CASCADE
            $record->delete();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                "Herramienta '{$recordName}' eliminada correctamente",
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar herramienta: ' . $e->getMessage(),
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

