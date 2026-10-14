<?php

namespace App\Controllers\UsersConfig\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\User;
use Core\Attributes\ApiRoutes;

/**
 * UsersConfigController - API REST para gestión de usuarios
 *
 * FILOSOFÍA LEGO:
 * Controlador para CRUD de usuarios.
 * Permite crear, editar, eliminar y listar usuarios.
 */
#[ApiRoutes('/users-config', preset: 'crud')]
class UsersConfigController extends CoreController
{
    const ROUTE = 'users-config';

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
     * GET /api/users-config/list
     * Lista todos los usuarios
     */
    public function list()
    {
        try {
            $users = User::orderBy('created_at', 'desc')->get()->toArray();

            // No retornar passwords
            $users = array_map(function($user) {
                unset($user['password']);
                return $user;
            }, $users);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Usuarios obtenidos correctamente',
                $users
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener usuarios: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/users-config/get?id=1
     * Obtiene un usuario por ID
     */
    public function get()
    {
        try {
            $id = $_GET['id'] ?? $_REQUEST['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de usuario requerido',
                    null
                ));
                return;
            }

            $user = User::find($id);

            if (!$user) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Usuario no encontrado',
                    null
                ));
                return;
            }

            $userData = $user->toArray();
            unset($userData['password']);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Usuario obtenido correctamente',
                $userData
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener usuario: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/users-config/create
     * Crea un nuevo usuario
     */
    public function create()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $email = $data['email'] ?? null;
            $password = $data['password'] ?? null;
            $name = $data['name'] ?? null;
            $authGroupId = $data['auth_group_id'] ?? null;
            $roleId = $data['role_id'] ?? null;
            $status = $data['status'] ?? 'active';

            if (!$email || !$password || !$name || !$authGroupId || !$roleId) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'Email, password, name, auth_group_id y role_id son requeridos',
                    null
                ));
                return;
            }

            // Verificar que el email no exista
            $existing = User::where('email', $email)->first();

            if ($existing) {
                Response::json(StatusCodes::HTTP_CONFLICT, (array)new ResponseDTO(
                    false,
                    'Ya existe un usuario con ese email',
                    null
                ));
                return;
            }

            $user = User::create([
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'name' => $name,
                'auth_group_id' => $authGroupId,
                'role_id' => $roleId,
                'status' => $status
            ]);

            $userData = $user->toArray();
            unset($userData['password']);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Usuario creado correctamente',
                $userData
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear usuario: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/users-config/update
     * Actualiza un usuario existente
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de usuario requerido',
                    null
                ));
                return;
            }

            $user = User::find($id);

            if (!$user) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Usuario no encontrado',
                    null
                ));
                return;
            }

            // Si se cambia el email, verificar que no exista duplicado
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $existing = User::where('email', $data['email'])
                               ->where('id', '!=', $id)
                               ->first();

                if ($existing) {
                    Response::json(StatusCodes::HTTP_CONFLICT, (array)new ResponseDTO(
                        false,
                        'Ya existe otro usuario con ese email',
                        null
                    ));
                    return;
                }
            }

            // Si se proporciona password, hashearlo
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']); // No actualizar si está vacío
            }

            $user->fill($data);
            $user->save();

            $userData = $user->toArray();
            unset($userData['password']);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Usuario actualizado correctamente',
                $userData
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar usuario: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/users-config/delete
     * Elimina un usuario
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID de usuario requerido',
                    null
                ));
                return;
            }

            $user = User::find($id);

            if (!$user) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Usuario no encontrado',
                    null
                ));
                return;
            }

            $user->delete();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Usuario eliminado correctamente',
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar usuario: ' . $e->getMessage(),
                null
            ));
        }
    }
}

