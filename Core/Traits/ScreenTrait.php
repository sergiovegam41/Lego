<?php

namespace Core\Traits;

/**
 * ScreenTrait - Implementación por defecto de ScreenInterface
 * 
 * FILOSOFÍA LEGO:
 * Define constantes estáticas que representan la identidad del screen.
 * Estas constantes son la "fuente de verdad" que el menú y JS consumen.
 * 
 * PATRÓN SCREEN vs MENU_GROUP:
 * 
 * Para screens que son hijos de un grupo de menú (carpeta), usar:
 * - MENU_GROUP_ID: El ID del grupo en el menú (carpeta)
 * - SCREEN_ID: El ID real del screen
 * - SCREEN_PARENT: Apunta a MENU_GROUP_ID
 * 
 * ```php
 * class ProductsListScreen extends CoreComponent implements ScreenInterface
 * {
 *     use ScreenTrait;
 *     
 *     // Grupo del menú (carpeta)
 *     public const MENU_GROUP_ID = 'products';
 *     
 *     // Este screen ES la lista
 *     public const SCREEN_ID = 'products-list';
 *     public const SCREEN_LABEL = 'Ver';
 *     public const SCREEN_ICON = 'list-outline';
 *     public const SCREEN_ROUTE = '/component/products';
 *     public const SCREEN_PARENT = self::MENU_GROUP_ID;
 *     public const SCREEN_ORDER = 0;
 *     public const SCREEN_VISIBLE = true;
 *     public const SCREEN_DYNAMIC = false;
 * }
 * 
 * class ProductsCreateScreen extends CoreComponent implements ScreenInterface
 * {
 *     use ScreenTrait;
 *     
 *     public const SCREEN_ID = 'products-create';
 *     public const SCREEN_LABEL = 'Crear';
 *     // Apunta al grupo del padre, no al SCREEN_ID del padre
 *     public const SCREEN_PARENT = ProductsListScreen::MENU_GROUP_ID;
 * }
 * ```
 * 
 * CONSTANTES REQUERIDAS:
 * - SCREEN_ID: Identificador único del screen
 * - SCREEN_ROUTE: Ruta del componente
 * 
 * CONSTANTES OPCIONALES (con defaults):
 * - SCREEN_LABEL: Texto en menú (default: SCREEN_ID)
 * - SCREEN_ICON: Icono ionicon (default: 'document-outline')
 * - SCREEN_PARENT: ID del grupo padre (default: null = raíz)
 * - SCREEN_VISIBLE: Si aparece en el menú (default: true)
 * - SCREEN_DYNAMIC: Si es activado por contexto (default: false)
 * - SCREEN_ORDER: Orden de aparición (default: 100)
 * - MENU_GROUP_ID: Para screens que definen un grupo (opcional)
 */
trait ScreenTrait
{
    /**
     * Obtiene la metadata completa del screen
     */
    public static function getScreenMetadata(): array
    {
        return [
            'id' => static::getScreenId(),
            'label' => defined(static::class . '::SCREEN_LABEL') ? static::SCREEN_LABEL : static::getScreenId(),
            'icon' => defined(static::class . '::SCREEN_ICON') ? static::SCREEN_ICON : 'document-outline',
            'route' => static::getScreenRoute(),
            'parent' => defined(static::class . '::SCREEN_PARENT') ? static::SCREEN_PARENT : null,
            'visible' => defined(static::class . '::SCREEN_VISIBLE') ? static::SCREEN_VISIBLE : true,
            'dynamic' => defined(static::class . '::SCREEN_DYNAMIC') ? static::SCREEN_DYNAMIC : false,
            'order' => defined(static::class . '::SCREEN_ORDER') ? static::SCREEN_ORDER : 100,
        ];
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
     */
    public static function getParentId(): ?string
    {
        return defined(static::class . '::SCREEN_PARENT') ? static::SCREEN_PARENT : null;
    }
    
    /**
     * Obtiene el ID del grupo del menú (si este screen define uno)
     * Útil para screens que son "raíz" de una sección del menú
     */
    public static function getMenuGroupId(): ?string
    {
        return defined(static::class . '::MENU_GROUP_ID') ? static::MENU_GROUP_ID : null;
    }
}

