<?php

namespace App\Controllers\Menu\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\MenuItem;
use Components\Core\Home\Dtos\MenuItemDto;
use Core\Services\AuthServicesCore;

/**
 * MenuStructureController - API para obtener estructura del menú
 *
 * FILOSOFÍA LEGO:
 * Devuelve la estructura del menú en el formato que espera el frontend
 * (MenuItemDto[]), solo items visibles, con estructura de árbol.
 */
class MenuStructureController extends CoreController
{
    public function __construct()
    {
        $this->getStructure();
    }

    /**
     * GET /api/menu/structure
     * Obtiene la estructura del menú en formato MenuItemDto[]
     * Filtra items según el rol del usuario (SUPERADMIN ve todo)
     */
    private function getStructure(): void
    {
        try {
            $HOST_NAME = env('HOST_NAME');
            
            // Obtener rol del usuario actual
            $userRole = $this->getCurrentUserRole();
            
            // Obtener items raíz desde DB (solo visibles)
            $rootItems = MenuItem::root()->visible()->orderBy('display_order')->get();
            
            // Filtrar por roles si no es SUPERADMIN
            $filteredRootItems = [];
            foreach ($rootItems as $item) {
                if ($this->isItemAllowedForRole($item, $userRole)) {
                    $filteredRootItems[] = $item;
                }
            }
            
            // Convertir a MenuItemDto
            $menuDtos = [];
            foreach ($filteredRootItems as $item) {
                $menuDtos[] = $this->buildMenuItemDto($item, $HOST_NAME, $userRole);
            }
            
            // Convertir a array para JSON
            $menuArray = array_map(fn($dto) => $dto->toArray(), $menuDtos);

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Estructura del menú obtenida correctamente',
                $menuArray
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener estructura del menú: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * Obtiene el rol del usuario actual desde la sesión
     */
    private function getCurrentUserRole(): ?string
    {
        $authResult = AuthServicesCore::isAutenticated();
        if ($authResult->success && $authResult->data) {
            return $authResult->data['role_id'] ?? null;
        }
        return null;
    }

    /**
     * Verifica si un item de menú está permitido para el rol del usuario
     * SUPERADMIN siempre ve todo
     */
    private function isItemAllowedForRole(MenuItem $item, ?string $userRole): bool
    {
        // Si no hay rol de usuario, no mostrar nada
        if (!$userRole) {
            return false;
        }
        
        // SUPERADMIN siempre ve todo
        if ($userRole === 'SUPERADMIN') {
            return true;
        }
        
        // Si el item no tiene allowed_roles definido, mostrar a todos
        if (empty($item->allowed_roles)) {
            return true;
        }
        
        // Decodificar allowed_roles (JSON)
        $allowedRoles = json_decode($item->allowed_roles, true);
        if (!is_array($allowedRoles) || empty($allowedRoles)) {
            return true; // Si está mal formateado, mostrar por defecto
        }
        
        // Verificar si el rol del usuario está en la lista
        return in_array($userRole, $allowedRoles);
    }

    /**
     * Construye un MenuItemDto desde un MenuItem (recursivo para hijos)
     */
    private function buildMenuItemDto(MenuItem $item, string $hostName, ?string $userRole = null): MenuItemDto
    {
        // Obtener hijos (solo visibles) y filtrar por roles
        $children = $item->children()->visible()->orderBy('display_order')->get();
        $childDtos = [];
        
        foreach ($children as $child) {
            if ($this->isItemAllowedForRole($child, $userRole)) {
                $childDtos[] = $this->buildMenuItemDto($child, $hostName, $userRole);
            }
        }
        
        // Determinar el nombre a mostrar
        $displayName = $item->hasChildren() && $item->index_label 
            ? $item->index_label 
            : $item->label;
        
        // Si no tiene ruta, es un grupo (url = null)
        // Si tiene hijos, es un grupo (url = null)
        // Si tiene ruta y no tiene hijos, es un item con link
        $url = null;
        if (!$item->hasChildren() && !empty($item->route)) {
            $url = $hostName . $item->route;
        }
        
        return new MenuItemDto(
            id: $item->id,
            name: $displayName,
            url: $url,
            iconName: $item->icon,
            childs: $childDtos,
            level: $item->level
        );
    }
}

