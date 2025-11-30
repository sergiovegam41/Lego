<?php

namespace Core\Contracts;

/**
 * ScreenInterface - Contrato para componentes que son ventanas/pantallas LEGO
 * 
 * FILOSOFÍA:
 * El componente define su identidad, el menú la consume.
 * Esto crea una "fuente de verdad" desde el componente, no desde el menú.
 * 
 * BENEFICIOS:
 * - El ID existe en el componente, no es una promesa del menú
 * - Consistencia: si el componente existe, el menú puede mostrarlo
 * - No hay magic strings duplicados
 * - Autodocumentado: el componente sabe todo sobre sí mismo
 * 
 * USO:
 * ```php
 * class ProductsScreen extends CoreComponent implements ScreenInterface
 * {
 *     public const SCREEN_ID = 'products-list';
 *     public const SCREEN_LABEL = 'Productos';
 *     public const SCREEN_ICON = 'cube-outline';
 *     public const SCREEN_PARENT = 'inventory'; // null si es raíz
 *     public const SCREEN_ROUTE = '/component/products';
 *     public const SCREEN_VISIBLE = true;
 *     public const SCREEN_DYNAMIC = false;
 *     
 *     public static function getScreenMetadata(): array { ... }
 * }
 * ```
 */
interface ScreenInterface
{
    /**
     * Obtiene la metadata completa del screen
     * 
     * @return array{
     *     id: string,
     *     label: string,
     *     icon: string,
     *     route: string,
     *     parent: string|null,
     *     visible: bool,
     *     dynamic: bool,
     *     order: int
     * }
     */
    public static function getScreenMetadata(): array;
    
    /**
     * Obtiene el ID único del screen
     * Usado para: data-menu-item-id, moduleId, etc.
     */
    public static function getScreenId(): string;
    
    /**
     * Obtiene la ruta del componente
     * Usado para: navegación, href del menú
     */
    public static function getScreenRoute(): string;
}

