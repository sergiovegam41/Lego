<?php

namespace App\Controllers\RolesConfig\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\Role;
use Core\Attributes\ApiRoutes;

/**
 * RolesConfigController - API REST para gestión de roles
 *
 * FILOSOFÍA LEGO:
 * Controlador para CRUD de roles (catálogo de roles).
 * Permite crear, editar, eliminar y listar roles sin necesidad de tener usuarios.
 */
#[ApiRoutes('/roles-config', preset: 'crud')]
class RolesConfigController extends CoreController
{
    const ROUTE = 'roles-config';

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
     * GET /api/roles-config/list
     * Lista todos los roles
     */
    public function list()
    {
        try {
            $roles = Role::orderBy('auth_group_id')
                        ->orderBy('role_id')
                        ->get()
                        ->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Roles obtenidos correctamente',
                $roles
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener roles: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/roles-config/get?id=1
     * Obtiene un rol por ID
     */
    public function get()
    {
        try {
            $id = $_GET['id'] ?? $_REQUEST['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de rol requerido',
                    null
                ));
                return;
            }

            $role = Role::find($id);

            if (!$role) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Rol no encontrado',
                    null
                ));
                return;
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Rol obtenido correctamente',
                $role->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener rol: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/roles-config/create
     * Crea un nuevo rol
     */
    public function create()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $authGroupId = $data['auth_group_id'] ?? null;
            $roleId = $data['role_id'] ?? null;
            $roleName = $data['role_name'] ?? null;
            $description = $data['description'] ?? null;
            $isActive = $data['is_active'] ?? true;

            if (!$authGroupId || !$roleId) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'auth_group_id y role_id son requeridos',
                    null
                ));
                return;
            }
            
            // Normalizar IDs (mayúsculas, sin acentos, sin caracteres especiales)
            $authGroupId = $this->normalizeId($authGroupId);
            $roleId = $this->normalizeId($roleId);
            
            // Validar DESPUÉS de normalizar - normalizeId puede devolver cadena vacía
            if (empty($authGroupId) || empty($roleId)) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'auth_group_id y role_id no pueden estar vacíos después de normalizar. Verifica que contengan caracteres válidos.',
                    null
                ));
                return;
            }

            // Verificar que no exista ya
            $existing = Role::where('auth_group_id', $authGroupId)
                           ->where('role_id', $roleId)
                           ->first();

            if ($existing) {
                Response::json(StatusCodes::HTTP_CONFLICT, (array)new ResponseDTO(
                    false,
                    'Ya existe un rol con ese auth_group_id y role_id',
                    null
                ));
                return;
            }

            $role = Role::create([
                'auth_group_id' => $authGroupId,
                'role_id' => $roleId,
                'role_name' => $roleName,
                'description' => $description,
                'is_active' => $isActive
            ]);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Rol creado correctamente',
                $role->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear rol: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/roles-config/update
     * Actualiza un rol existente
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de rol requerido',
                    null
                ));
                return;
            }

            $role = Role::find($id);

            if (!$role) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Rol no encontrado',
                    null
                ));
                return;
            }

            // Si se cambia auth_group_id o role_id, verificar que no exista duplicado
            if (isset($data['auth_group_id']) || isset($data['role_id'])) {
                $newAuthGroupId = $data['auth_group_id'] ?? $role->auth_group_id;
                $newRoleId = $data['role_id'] ?? $role->role_id;

                if ($newAuthGroupId !== $role->auth_group_id || $newRoleId !== $role->role_id) {
                    $existing = Role::where('auth_group_id', $newAuthGroupId)
                                   ->where('role_id', $newRoleId)
                                   ->where('id', '!=', $id)
                                   ->first();

                    if ($existing) {
                        Response::json(StatusCodes::HTTP_CONFLICT, (array)new ResponseDTO(
                            false,
                            'Ya existe otro rol con ese auth_group_id y role_id',
                            null
                        ));
                        return;
                    }
                }
            }

            $role->fill($data);
            $role->save();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Rol actualizado correctamente',
                $role->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar rol: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/roles-config/delete
     * Elimina un rol
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de rol requerido',
                    null
                ));
                return;
            }

            $role = Role::find($id);

            if (!$role) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Rol no encontrado',
                    null
                ));
                return;
            }

            $role->delete();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Rol eliminado correctamente',
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar rol: ' . $e->getMessage(),
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

