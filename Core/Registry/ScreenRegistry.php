<?php

namespace Core\Registry;

use Core\Contracts\ScreenInterface;

/**
 * ScreenRegistry - Registro central de todos los screens LEGO
 * 
 * FILOSOFÍA:
 * Los screens se registran aquí, el menú consume desde aquí.
 * Es la "fuente de verdad" para todas las ventanas disponibles.
 * 
 * BENEFICIOS:
 * - Centralizado: un solo lugar para ver todos los screens
 * - Type-safe: solo acepta clases que implementan ScreenInterface
 * - Auto-discovery: puede escanear directorios para encontrar screens
 * - Menu-ready: genera la estructura del menú automáticamente
 * 
 * USO:
 * ```php
 * // Registrar screens
 * ScreenRegistry::register(ProductsListScreen::class);
 * ScreenRegistry::register(ProductsCreateScreen::class);
 * ScreenRegistry::register(ProductsEditScreen::class);
 * 
 * // Obtener para el menú
 * $menuStructure = ScreenRegistry::getMenuStructure();
 * ```
 */
class ScreenRegistry
{
    /** @var array<string, class-string<ScreenInterface>> */
    private static array $screens = [];
    
    /** @var array<string, array> Cached metadata */
    private static array $metadataCache = [];
    
    /**
     * Registra un screen en el registry
     * 
     * @param class-string<ScreenInterface> $screenClass
     */
    public static function register(string $screenClass): void
    {
        if (!is_subclass_of($screenClass, ScreenInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('La clase %s debe implementar ScreenInterface', $screenClass)
            );
        }
        
        $id = $screenClass::getScreenId();
        self::$screens[$id] = $screenClass;
        
        // Invalidar cache
        unset(self::$metadataCache[$id]);
    }
    
    /**
     * Registra múltiples screens
     * 
     * @param array<class-string<ScreenInterface>> $screenClasses
     */
    public static function registerMany(array $screenClasses): void
    {
        foreach ($screenClasses as $screenClass) {
            self::register($screenClass);
        }
    }
    
    /**
     * Obtiene la metadata de un screen por su ID
     */
    public static function get(string $id): ?array
    {
        if (!isset(self::$screens[$id])) {
            return null;
        }
        
        if (!isset(self::$metadataCache[$id])) {
            self::$metadataCache[$id] = self::$screens[$id]::getScreenMetadata();
        }
        
        return self::$metadataCache[$id];
    }
    
    /**
     * Obtiene la clase de un screen por su ID
     * 
     * @return class-string<ScreenInterface>|null
     */
    public static function getClass(string $id): ?string
    {
        return self::$screens[$id] ?? null;
    }
    
    /**
     * Verifica si un screen está registrado
     */
    public static function has(string $id): bool
    {
        return isset(self::$screens[$id]);
    }
    
    /**
     * Obtiene todos los screens registrados
     * 
     * @return array<string, array> Array de metadata indexado por ID
     */
    public static function all(): array
    {
        $result = [];
        foreach (self::$screens as $id => $class) {
            $result[$id] = self::get($id);
        }
        return $result;
    }
    
    /**
     * Obtiene solo los screens visibles (para el menú)
     * 
     * @return array<string, array>
     */
    public static function getVisible(): array
    {
        return array_filter(self::all(), fn($meta) => $meta['visible'] === true);
    }
    
    /**
     * Obtiene screens por parent ID
     * 
     * @return array<string, array>
     */
    public static function getByParent(?string $parentId): array
    {
        return array_filter(
            self::all(), 
            fn($meta) => $meta['parent'] === $parentId
        );
    }
    
    /**
     * Genera la estructura del menú compatible con MenuStructure
     * 
     * @return array Estructura jerárquica para el menú
     */
    public static function getMenuStructure(): array
    {
        $allScreens = self::all();
        $structure = [];
        
        // Primero, obtener los screens raíz (sin parent)
        $rootScreens = array_filter($allScreens, fn($m) => $m['parent'] === null);
        
        // Ordenar por order
        uasort($rootScreens, fn($a, $b) => $a['order'] <=> $b['order']);
        
        foreach ($rootScreens as $id => $meta) {
            $item = self::buildMenuItem($meta, $allScreens);
            $structure[] = $item;
        }
        
        return $structure;
    }
    
    /**
     * Construye un item de menú con sus hijos
     */
    private static function buildMenuItem(array $meta, array $allScreens): array
    {
        $item = [
            'id' => $meta['id'],
            'label' => $meta['label'],
            'icon' => $meta['icon'],
            'route' => $meta['route'],
            'is_visible' => $meta['visible'],
            'is_dynamic' => $meta['dynamic'],
            'display_order' => $meta['order'],
        ];
        
        // Buscar hijos
        $children = array_filter($allScreens, fn($m) => $m['parent'] === $meta['id']);
        
        if (!empty($children)) {
            uasort($children, fn($a, $b) => $a['order'] <=> $b['order']);
            
            $item['children'] = [];
            foreach ($children as $childMeta) {
                $item['children'][] = self::buildMenuItem($childMeta, $allScreens);
            }
        }
        
        return $item;
    }
    
    /**
     * Limpia el registry (útil para tests)
     */
    public static function clear(): void
    {
        self::$screens = [];
        self::$metadataCache = [];
    }
    
    /**
     * Debug: lista todos los screens registrados
     */
    public static function debug(): array
    {
        return [
            'count' => count(self::$screens),
            'screens' => array_keys(self::$screens),
            'metadata' => self::all(),
        ];
    }
}

