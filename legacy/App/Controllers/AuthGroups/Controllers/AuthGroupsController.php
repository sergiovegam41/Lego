<?php

namespace App\Controllers\AuthGroups\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\AuthGroup;
use Core\Attributes\ApiRoutes;

/**
 * AuthGroupsController - API REST para gestión de grupos de autenticación
 *
 * FILOSOFÍA LEGO:
 * Controlador para CRUD de grupos de autenticación (catálogo).
 */
#[ApiRoutes('/auth-groups', preset: 'crud')]
class AuthGroupsController extends CoreController
{
    const ROUTE = 'auth-groups';

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
     * GET /api/auth-groups/list
     * Lista todos los grupos
     */
    public function list()
    {
        try {
            $groups = AuthGroup::orderBy('id')->get()->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Grupos obtenidos correctamente',
                $groups
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener grupos: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/auth-groups/get?id=ADMINS
     * Obtiene un grupo por ID
     */
    public function get()
    {
        try {
            $id = $_GET['id'] ?? $_REQUEST['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de grupo requerido',
                    null
                ));
                return;
            }

            $group = AuthGroup::find($id);

            if (!$group) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Grupo no encontrado',
                    null
                ));
                return;
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Grupo obtenido correctamente',
                $group->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener grupo: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/auth-groups/create
     * Crea un nuevo grupo
     */
    public function create()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $id = $data['id'] ?? null;
            $name = $data['name'] ?? null;
            $description = $data['description'] ?? null;
            $isActive = $data['is_active'] ?? true;

            if (!$id || !$name) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID y nombre son requeridos',
                    null
                ));
                return;
            }
            
            // Normalizar ID
            $id = $this->normalizeId($id);
            
            // Validar DESPUÉS de normalizar - normalizeId puede devolver cadena vacía
            if (empty($id)) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID no puede estar vacío después de normalizar. Verifica que contenga caracteres válidos.',
                    null
                ));
                return;
            }

            // Verificar que no exista ya
            $existing = AuthGroup::find($id);

            if ($existing) {
                Response::json(StatusCodes::HTTP_CONFLICT, (array)new ResponseDTO(
                    false,
                    'Ya existe un grupo con ese ID',
                    null
                ));
                return;
            }

            $group = AuthGroup::create([
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'is_active' => $isActive
            ]);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Grupo creado correctamente',
                $group->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear grupo: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/auth-groups/update
     * Actualiza un grupo existente
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de grupo requerido',
                    null
                ));
                return;
            }

            $group = AuthGroup::find($id);

            if (!$group) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Grupo no encontrado',
                    null
                ));
                return;
            }

            // No permitir cambiar el ID
            unset($data['id']);

            $group->fill($data);
            $group->save();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Grupo actualizado correctamente',
                $group->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar grupo: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/auth-groups/delete
     * Elimina un grupo
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de grupo requerido',
                    null
                ));
                return;
            }

            $group = AuthGroup::find($id);

            if (!$group) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Grupo no encontrado',
                    null
                ));
                return;
            }

            $group->delete();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Grupo eliminado correctamente',
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar grupo: ' . $e->getMessage(),
                null
            ));
        }
    }
    
    /**
     * Normaliza un ID: mayúsculas, sin acentos, sin caracteres especiales
     * @param string $id - ID a normalizar
     * @return string - ID normalizado
     */
    private function normalizeId(string $id): string
    {
        if (empty($id)) {
            return '';
        }
        
        // Convertir a mayúsculas
        $normalized = mb_strtoupper($id, 'UTF-8');
        
        // Mapa de acentos y caracteres especiales
        $accentMap = [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
            'Â' => 'A', 'Ê' => 'E', 'Î' => 'I', 'Ô' => 'O', 'Û' => 'U',
            'Ã' => 'A', 'Õ' => 'O',
            'Ç' => 'C', 'Ñ' => 'N'
        ];
        
        // Reemplazar acentos
        foreach ($accentMap as $accent => $replacement) {
            $normalized = str_replace($accent, $replacement, $normalized);
        }
        
        // Eliminar caracteres especiales (solo permitir letras, números y guiones bajos)
        $normalized = preg_replace('/[^A-Z0-9_]/', '', $normalized);
        
        // Eliminar espacios y guiones múltiples
        $normalized = preg_replace('/\s+/', '', $normalized);
        $normalized = preg_replace('/_+/', '_', $normalized);
        
        // Eliminar guiones bajos al inicio y final
        $normalized = trim($normalized, '_');
        
        return $normalized;
    }
}

