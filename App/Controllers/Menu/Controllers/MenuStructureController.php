<?php

namespace App\Controllers\Menu\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\MenuItem;
use Components\Core\Home\Dtos\MenuItemDto;

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
     */
    private function getStructure(): void
    {
        try {
            $HOST_NAME = env('HOST_NAME');
            
            // Obtener items raíz desde DB (solo visibles)
            $rootItems = MenuItem::root()->visible()->orderBy('display_order')->get();
            
            // Convertir a MenuItemDto
            $menuDtos = [];
            foreach ($rootItems as $item) {
                $menuDtos[] = $this->buildMenuItemDto($item, $HOST_NAME);
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
     * Construye un MenuItemDto desde un MenuItem (recursivo para hijos)
     */
    private function buildMenuItemDto(MenuItem $item, string $hostName): MenuItemDto
    {
        // Obtener hijos (solo visibles)
        $children = $item->children()->visible()->orderBy('display_order')->get();
        $childDtos = [];
        
        foreach ($children as $child) {
            $childDtos[] = $this->buildMenuItemDto($child, $hostName);
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

