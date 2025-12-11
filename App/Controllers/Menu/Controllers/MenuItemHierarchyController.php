<?php

namespace App\Controllers\Menu\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\MenuItem;
use Components\Core\Home\Dtos\MenuItemDto;
use Flight;

/**
 * MenuItemHierarchyController
 * 
 * Obtiene la jerarquía completa de un item de menú:
 * - Todos sus ancestros (padres, abuelos, etc.)
 * - El item mismo
 * - Todos sus hijos (recursivo)
 * 
 * Útil para mostrar items ocultos en el menú cuando se abren
 */
class MenuItemHierarchyController extends CoreController
{
    public function __construct()
    {
        $this->getHierarchy();
    }

    /**
     * GET /api/menu/item-hierarchy/{id}
     * Obtiene la jerarquía completa de un item de menú
     */
    private function getHierarchy(): void
    {
        try {
            $HOST_NAME = env('HOST_NAME');
            $itemId = Flight::request()->query['id'] ?? null;
            
            if (!$itemId) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'ID del item requerido',
                    null
                ));
                return;
            }

            // Buscar el item con sus relaciones cargadas
            $item = MenuItem::with('parent')->find($itemId);
            
            if (!$item) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    'Item no encontrado',
                    null
                ));
                return;
            }

            // Cargar recursivamente todos los ancestros
            $ancestors = [];
            $current = $item->parent;
            while ($current) {
                array_unshift($ancestors, $current);
                // Cargar el siguiente padre
                $current = MenuItem::with('parent')->find($current->parent_id);
            }
            
            // Obtener todos los hijos (recursivo, incluyendo ocultos)
            $children = $this->getAllChildren($item, $HOST_NAME);
            
            // Obtener todos los hermanos (otros hijos del mismo padre) con sus hijos recursivamente
            $siblings = [];
            if ($item->parent_id) {
                $siblings = MenuItem::where('parent_id', $item->parent_id)
                    ->where('id', '!=', $item->id) // Excluir el item actual
                    ->orderBy('display_order')
                    ->get()
                    ->map(function($sibling) use ($HOST_NAME) {
                        $siblingData = $this->buildItemData($sibling, $HOST_NAME);
                        // Incluir hijos recursivamente (igual que para el item principal)
                        $siblingData['children'] = $this->getAllChildren($sibling, $HOST_NAME);
                        return $siblingData;
                    })
                    ->toArray();
            }
            
            // Construir la estructura completa
            $hierarchy = [
                'item' => $this->buildItemData($item, $HOST_NAME),
                'ancestors' => array_map(function($ancestor) use ($HOST_NAME) {
                    return $this->buildItemData($ancestor, $HOST_NAME);
                }, $ancestors),
                'children' => $children,
                'siblings' => $siblings
            ];

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Jerarquía obtenida correctamente',
                $hierarchy
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener jerarquía: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * Obtener todos los hijos recursivamente (incluyendo ocultos)
     */
    private function getAllChildren(MenuItem $item, string $hostName): array
    {
        $children = $item->children()
            ->orderBy('display_order')
            ->get();
        
        $result = [];
        foreach ($children as $child) {
            $childData = $this->buildItemData($child, $hostName);
            $childData['children'] = $this->getAllChildren($child, $hostName);
            $result[] = $childData;
        }
        
        return $result;
    }

    /**
     * Construir datos del item
     */
    private function buildItemData(MenuItem $item, string $hostName): array
    {
        $displayName = $item->hasChildren() && $item->index_label 
            ? $item->index_label 
            : $item->label;
        
        $url = null;
        if (!$item->hasChildren() && !empty($item->route)) {
            $url = $hostName . $item->route;
        }
        
        return [
            'id' => $item->id,
            'label' => $item->label,
            'index_label' => $item->index_label,
            'display_name' => $displayName,
            'route' => $item->route,
            'url' => $url,
            'icon' => $item->icon,
            'level' => $item->level,
            'parent_id' => $item->parent_id,
            'display_order' => $item->display_order,
            'is_visible' => $item->is_visible,
            'is_dynamic' => $item->is_dynamic,
            'has_children' => $item->hasChildren(),
            'default_child_id' => $item->default_child_id
        ];
    }
}

