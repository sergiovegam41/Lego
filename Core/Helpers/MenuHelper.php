<?php

namespace Core\Helpers;

use App\Models\MenuItem;

/**
 * MenuHelper - Helpers para trabajar con el menú proceduralmente
 * 
 * FILOSOFÍA LEGO:
 * La BD es la fuente de verdad. Todo se obtiene desde ahí.
 * 
 * USO:
 * ```php
 * // Obtener parent_id de un screen desde la BD
 * $parentId = MenuHelper::getParentIdFromScreenId('auth-groups-config-edit');
 * 
 * // Obtener metadata completa desde la BD
 * $metadata = MenuHelper::getMenuMetadataFromScreenId('auth-groups-config-edit');
 * ```
 */
class MenuHelper
{
    /**
     * Obtiene el parent_id de un item del menú desde la BD usando su SCREEN_ID
     * 
     * @param string $screenId El SCREEN_ID del componente
     * @return string|null El parent_id o null si no existe o es raíz
     */
    public static function getParentIdFromScreenId(string $screenId): ?string
    {
        $item = MenuItem::find($screenId);
        
        if (!$item) {
            return null;
        }
        
        return $item->parent_id;
    }
    
    /**
     * Obtiene metadata completa del menú desde la BD usando SCREEN_ID
     * 
     * @param string $screenId El SCREEN_ID del componente
     * @return array|null Metadata completa o null si no existe
     */
    public static function getMenuMetadataFromScreenId(string $screenId): ?array
    {
        $item = MenuItem::find($screenId);
        
        if (!$item) {
            return null;
        }
        
        return [
            'id' => $item->id,
            'label' => $item->label,
            'icon' => $item->icon,
            'route' => $item->route,
            'parent_id' => $item->parent_id,
            'display_order' => $item->display_order,
            'level' => $item->level,
            'is_visible' => $item->is_visible,
            'is_dynamic' => $item->is_dynamic,
        ];
    }
    
    /**
     * Verifica si un SCREEN_ID existe en la BD
     * 
     * @param string $screenId El SCREEN_ID del componente
     * @return bool
     */
    public static function screenExists(string $screenId): bool
    {
        return MenuItem::where('id', $screenId)->exists();
    }
    
    /**
     * Obtiene el ID del grupo del menú de un screen desde la BD
     * 
     * FILOSOFÍA LEGO:
     * El "menu group ID" es simplemente el parent_id del screen en la BD.
     * Si el screen tiene un parent_id, ese es su grupo. Si no, es raíz.
     * 
     * NOTA: Este método existe por compatibilidad con código antiguo.
     * Conceptualmente, es lo mismo que getParentIdFromScreenId().
     * 
     * @param string $screenId El SCREEN_ID del componente
     * @return string|null El parent_id (grupo del menú) o null si es raíz
     */
    public static function getMenuGroupIdFromScreenId(string $screenId): ?string
    {
        $item = MenuItem::find($screenId);
        
        if (!$item || !$item->parent_id) {
            return null; // Es raíz o no existe
        }
        
        // El parent_id ES el "menu group ID"
        return $item->parent_id;
    }
}

