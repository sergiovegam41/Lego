<?php

namespace Core\Routing;

use Core\Attributes\ApiRoutes;
use Flight;

/**
 * ApiControllerRouter - Auto-registro de rutas desde controladores
 *
 * FILOSOFÍA LEGO:
 * "Versatilidad sobre convención" - LEGO es más que CRUDs.
 * Este router descubre controladores con #[ApiRoutes] y registra
 * automáticamente sus rutas, soportando cualquier tipo de API.
 *
 * FUNCIONAMIENTO:
 * 1. Escanea directorios de controladores (recursivamente)
 * 2. Detecta clases con #[ApiRoutes]
 * 3. Registra rutas según el preset + acciones personalizadas
 * 4. Soporta múltiples métodos HTTP por acción
 *
 * USO:
 * ```php
 * // En Routes/Api.php
 * ApiControllerRouter::registerRoutes();
 * ```
 *
 * ESTRUCTURA ESPERADA:
 * ```
 * App/Controllers/
 * ├── Tools/Controllers/
 * │   └── ToolsController.php  ← #[ApiRoutes('/tools')]
 * ├── Reports/Controllers/
 * │   └── ReportsController.php  ← #[ApiRoutes('/reports', preset: 'custom', ...)]
 * └── Integrations/
 *     └── WebhooksController.php  ← #[ApiRoutes('/webhooks', ...)]
 * ```
 */
class ApiControllerRouter
{
    /**
     * Directorios donde buscar controladores
     */
    private static array $controllerPaths = [
        __DIR__ . '/../../App/Controllers/',
    ];

    /**
     * Cache de rutas registradas (para debugging)
     */
    private static array $registeredRoutes = [];

    /**
     * Cache de controladores descubiertos
     */
    private static array $discoveredControllers = [];

    /**
     * Registrar todas las rutas automáticamente
     *
     * @return void
     */
    public static function registerRoutes(): void
    {
        $controllers = self::discoverControllers();

        foreach ($controllers as $controllerClass => $config) {
            if ($config->enabled) {
                self::registerControllerRoutes($controllerClass, $config);
            }
        }

        // Log en desarrollo
        if (self::isDevelopment()) {
            error_log(
                "[ApiControllerRouter] Registered " . count(self::$registeredRoutes) .
                " endpoints for " . count($controllers) . " controllers"
            );
        }
    }

    /**
     * Descubrir controladores con #[ApiRoutes] (recursivo)
     *
     * @return array Array de [controllerClass => ApiRoutes]
     */
    private static function discoverControllers(): array
    {
        if (!empty(self::$discoveredControllers)) {
            return self::$discoveredControllers;
        }

        $controllers = [];

        foreach (self::$controllerPaths as $basePath) {
            if (!is_dir($basePath)) {
                continue;
            }

            $files = self::scanDirectoryRecursive($basePath, '*.php');

            foreach ($files as $file) {
                $controllerClass = self::getClassFromFile($file);

                if (!$controllerClass) {
                    continue;
                }

                // Verificar si tiene el atributo ApiRoutes
                try {
                    $reflection = new \ReflectionClass($controllerClass);
                    $attributes = $reflection->getAttributes(ApiRoutes::class);

                    if (!empty($attributes)) {
                        $config = $attributes[0]->newInstance();
                        $controllers[$controllerClass] = $config;
                    }
                } catch (\ReflectionException $e) {
                    // Clase no existe o error de reflexión, skip
                    continue;
                } catch (\Throwable $e) {
                    // Cualquier otro error, log y skip
                    if (self::isDevelopment()) {
                        error_log("[ApiControllerRouter] Error loading {$controllerClass}: " . $e->getMessage());
                    }
                    continue;
                }
            }
        }

        self::$discoveredControllers = $controllers;
        return $controllers;
    }

    /**
     * Escanear directorio recursivamente
     *
     * @param string $dir
     * @param string $pattern
     * @return array
     */
    private static function scanDirectoryRecursive(string $dir, string $pattern = '*'): array
    {
        $files = [];
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && fnmatch($pattern, $file->getFilename())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Registrar rutas para un controlador específico
     *
     * @param string $controllerClass
     * @param ApiRoutes $config
     * @return void
     */
    private static function registerControllerRoutes(string $controllerClass, ApiRoutes $config): void
    {
        $endpoint = $config->getFullEndpoint();
        $actions = $config->getResolvedActions();

        // IMPORTANTE: El Router ya quita el prefijo /api antes de que Flight procese las rutas
        // Por lo tanto, debemos registrar las rutas SIN el prefijo /api
        $endpointWithoutApi = preg_replace('#^/api/#', '/', $endpoint);

        foreach ($actions as $action => $methods) {
            // Construir patrón de métodos para Flight (ej: "GET|POST")
            $methodPattern = implode('|', $methods);
            $routePath = "{$endpointWithoutApi}/{$action}";

            // Registrar ruta
            Flight::route("{$methodPattern} {$routePath}", function () use ($controllerClass, $action) {
                new $controllerClass($action);
            });

            self::$registeredRoutes[] = "{$methodPattern} {$routePath}";
        }

        // Log individual
        if (self::isDevelopment()) {
            $shortName = self::getShortClassName($controllerClass);
            $actionCount = count($actions);
            error_log("[ApiControllerRouter] ✓ {$shortName} → {$endpoint} ({$actionCount} actions)");
        }
    }

    /**
     * Obtener nombre de clase desde archivo PHP
     *
     * @param string $file Ruta al archivo
     * @return string|null Nombre completo de la clase
     */
    private static function getClassFromFile(string $file): ?string
    {
        $content = file_get_contents($file);

        // Extraer namespace
        $namespace = null;
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];
        }

        // Extraer nombre de clase
        $className = null;
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            $className = $matches[1];
        }

        if (!$className) {
            return null;
        }

        return $namespace ? "{$namespace}\\{$className}" : $className;
    }

    /**
     * Obtener nombre corto de la clase
     *
     * @param string $className
     * @return string
     */
    private static function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }

    /**
     * Verificar si está en modo desarrollo
     *
     * @return bool
     */
    private static function isDevelopment(): bool
    {
        return getenv('APP_ENV') === 'development' ||
               getenv('APP_DEBUG') === 'true' ||
               getenv('APP_DEBUG') === '1';
    }

    /**
     * Obtener rutas registradas (para debugging)
     *
     * @return array
     */
    public static function getRegisteredRoutes(): array
    {
        return self::$registeredRoutes;
    }

    /**
     * Obtener controladores descubiertos
     *
     * @return array
     */
    public static function getDiscoveredControllers(): array
    {
        return self::$discoveredControllers;
    }

    /**
     * Agregar path adicional para escaneo de controladores
     *
     * @param string $path
     * @return void
     */
    public static function addControllerPath(string $path): void
    {
        if (is_dir($path) && !in_array($path, self::$controllerPaths)) {
            self::$controllerPaths[] = $path;
        }
    }

    /**
     * Obtener configuración de un controlador
     *
     * @param string $controllerClass
     * @return ApiRoutes|null
     */
    public static function getControllerConfig(string $controllerClass): ?ApiRoutes
    {
        try {
            $reflection = new \ReflectionClass($controllerClass);
            $attributes = $reflection->getAttributes(ApiRoutes::class);

            if (empty($attributes)) {
                return null;
            }

            return $attributes[0]->newInstance();
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    /**
     * Limpiar cache (útil para tests)
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$registeredRoutes = [];
        self::$discoveredControllers = [];
    }

    /**
     * Obtener resumen de rutas para debugging
     *
     * @return array
     */
    public static function getSummary(): array
    {
        $summary = [];
        
        foreach (self::$discoveredControllers as $class => $config) {
            $summary[] = [
                'controller' => self::getShortClassName($class),
                'endpoint' => $config->getFullEndpoint(),
                'preset' => $config->preset,
                'actions' => array_keys($config->getResolvedActions()),
                'enabled' => $config->enabled,
            ];
        }

        return $summary;
    }
}

