<?php

namespace Core\Traits;

/**
 * ScreenTrait - Implementación por defecto de ScreenInterface
 * 
 * FILOSOFÍA LEGO:
 * Define constantes estáticas que representan la identidad del screen.
 * Estas constantes son la "fuente de verdad" que el menú y JS consumen.
 * 
 * FILOSOFÍA LEGO - PROCEDURAL:
 * La BD es la fuente de verdad. Todo se obtiene desde ahí.
 * 
 * CONSTANTES REQUERIDAS:
 * - SCREEN_ID: Identificador único del screen (obligatorio)
 * - SCREEN_ROUTE: Ruta del componente (obligatorio)
 * 
 * CONSTANTES OPCIONALES (con defaults):
 * - SCREEN_LABEL: Texto en menú (default: SCREEN_ID)
 * - SCREEN_ICON: Icono ionicon (default: 'document-outline')
 * - SCREEN_VISIBLE: Si aparece en el menú (default: true)
 * - SCREEN_DYNAMIC: Si es activado por contexto (default: false)
 * - SCREEN_ORDER: Orden de aparición (default: 100)
 * 
 * CONSTANTES OBSOLETAS (NO SE DEBEN USAR):
 * - SCREEN_PARENT: ❌ OBSOLETO - Se obtiene proceduralmente desde la BD
 * - MENU_GROUP_ID: ❌ OBSOLETO - Se obtiene proceduralmente desde la BD
 * 
 * NOTA: parent_id y menu_group_id se obtienen proceduralmente desde la BD
 * usando SCREEN_ID. No se deben definir como constantes.
 */
trait ScreenTrait
{
    /**
     * Obtiene la metadata completa del screen
     * 
     * FILOSOFÍA LEGO:
     * La BD es la fuente de verdad. Siempre intentar obtener parent_id desde BD.
     * SCREEN_PARENT (si existe) es solo un fallback opcional para screens nuevos
     * que aún no están en la BD.
     */
    public static function getScreenMetadata(): array
    {
        $screenId = static::getScreenId();
        
        // Siempre intentar obtener desde BD primero (procedural)
        $parentId = self::getParentIdFromDatabase($screenId);
        
        // Fallback opcional: SCREEN_PARENT (solo si no está en BD)
        if ($parentId === null && defined(static::class . '::SCREEN_PARENT')) {
            $parentId = static::SCREEN_PARENT;
        }
        
        return [
            'id' => $screenId,
            'label' => defined(static::class . '::SCREEN_LABEL') ? static::SCREEN_LABEL : static::getScreenId(),
            'icon' => defined(static::class . '::SCREEN_ICON') ? static::SCREEN_ICON : 'document-outline',
            'route' => static::getScreenRoute(),
            'parent' => $parentId,
            'visible' => defined(static::class . '::SCREEN_VISIBLE') ? static::SCREEN_VISIBLE : true,
            'dynamic' => defined(static::class . '::SCREEN_DYNAMIC') ? static::SCREEN_DYNAMIC : false,
            'order' => defined(static::class . '::SCREEN_ORDER') ? static::SCREEN_ORDER : 100,
        ];
    }
    
    /**
     * Obtiene el parent_id desde la BD usando SCREEN_ID
     * Simple y elegante: consulta directa a la BD
     */
    private static function getParentIdFromDatabase(string $screenId): ?string
    {
        try {
            return \Core\Helpers\MenuHelper::getParentIdFromScreenId($screenId);
        } catch (\Exception $e) {
            return null; // Fallback a SCREEN_PARENT se maneja arriba
        }
    }
    
    /**
     * Obtiene el ID único del screen
     */
    public static function getScreenId(): string
    {
        if (!defined(static::class . '::SCREEN_ID')) {
            throw new \RuntimeException(
                sprintf('La clase %s debe definir la constante SCREEN_ID', static::class)
            );
        }
        return static::SCREEN_ID;
    }
    
    /**
     * Obtiene la ruta del componente
     */
    public static function getScreenRoute(): string
    {
        if (!defined(static::class . '::SCREEN_ROUTE')) {
            throw new \RuntimeException(
                sprintf('La clase %s debe definir la constante SCREEN_ROUTE', static::class)
            );
        }
        return static::SCREEN_ROUTE;
    }
    
    /**
     * Verifica si el screen es visible en el menú
     */
    public static function isVisible(): bool
    {
        return defined(static::class . '::SCREEN_VISIBLE') ? static::SCREEN_VISIBLE : true;
    }
    
    /**
     * Verifica si el screen es dinámico (activado por contexto)
     */
    public static function isDynamic(): bool
    {
        return defined(static::class . '::SCREEN_DYNAMIC') ? static::SCREEN_DYNAMIC : false;
    }
    
    /**
     * Obtiene el ID del screen padre
     * 
     * FILOSOFÍA LEGO:
     * Procedural: siempre consulta la BD primero.
     * SCREEN_PARENT (si existe) es solo un fallback opcional para screens nuevos.
     */
    public static function getParentId(): ?string
    {
        $screenId = static::getScreenId();
        $parentId = self::getParentIdFromDatabase($screenId);
        
        // Fallback opcional
        if ($parentId === null && defined(static::class . '::SCREEN_PARENT')) {
            $parentId = static::SCREEN_PARENT;
        }
        
        return $parentId;
    }
    
    /**
     * Obtiene el ID del grupo del menú (procedural desde BD)
     * 
     * FILOSOFÍA LEGO:
     * El MENU_GROUP_ID es simplemente el parent_id del screen en la BD.
     * Ya no necesitamos hardcodearlo - se obtiene proceduralmente.
     * 
     * Fallback: Si no está en BD, usar MENU_GROUP_ID constante (solo para compatibilidad
     * con screens muy antiguos que aún no han sido migrados)
     */
    public static function getMenuGroupId(): ?string
    {
        $screenId = static::getScreenId();
        
        // Procedural: obtener desde BD
        $menuGroupId = \Core\Helpers\MenuHelper::getMenuGroupIdFromScreenId($screenId);
        
        // Fallback opcional: MENU_GROUP_ID constante (solo si no está en BD)
        if ($menuGroupId === null && defined(static::class . '::MENU_GROUP_ID')) {
            $menuGroupId = static::MENU_GROUP_ID;
        }
        
        return $menuGroupId;
    }
}

