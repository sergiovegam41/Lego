<?php

namespace Core\Routing;

use Core\Attributes\ApiCrudResource;
use Flight;

/**
 * ApiCrudRouter - Registro automático de rutas CRUD desde modelos
 *
 * FILOSOFÍA LEGO:
 * "Convention over Configuration" - Escanea modelos con #[ApiCrudResource]
 * y registra rutas REST automáticamente.
 *
 * FUNCIONAMIENTO:
 * 1. Escanea directorios de modelos
 * 2. Detecta clases con #[ApiCrudResource]
 * 3. Registra 5 rutas por modelo (list, get, create, update, delete)
 * 4. Conecta con AbstractCrudController
 *
 * USO:
 * ```php
 * // En Core/bootstrap.php
 * ApiCrudRouter::registerRoutes();
 * ```
 */
class ApiCrudRouter
{
    /**
     * Directorios donde buscar modelos
     */
    private static array $modelPaths = [
        __DIR__ . '/../../App/Models/',
    ];

    /**
     * Cache de rutas registradas (para debugging)
     */
    private static array $registeredRoutes = [];

    /**
     * Registrar todas las rutas CRUD automáticamente
     *
     * @return void
     */
    public static function registerRoutes(): void
    {
        $models = self::discoverModels();

        foreach ($models as $modelClass => $config) {
            self::registerModelRoutes($modelClass, $config);
        }

        // Log en desarrollo
        if (self::isDevelopment()) {
            error_log(
                "[ApiCrudRouter] Registered " . count(self::$registeredRoutes) .
                " CRUD endpoints for " . count($models) . " models"
            );
        }
    }

    /**
     * Descubrir modelos con #[ApiCrudResource]
     *
     * @return array Array de [modelClass => ApiCrudResource]
     */
    private static function discoverModels(): array
    {
        $models = [];

        foreach (self::$modelPaths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = glob($path . '*.php');

            foreach ($files as $file) {
                $modelClass = self::getClassFromFile($file);

                if (!$modelClass) {
                    continue;
                }

                // Verificar si tiene el atributo ApiCrudResource
                try {
                    $reflection = new \ReflectionClass($modelClass);
                    $attributes = $reflection->getAttributes(ApiCrudResource::class);

                    if (!empty($attributes)) {
                        $config = $attributes[0]->newInstance();
                        $models[$modelClass] = $config;
                    }
                } catch (\ReflectionException $e) {
                    // Clase no existe, skip
                    continue;
                }
            }
        }

        return $models;
    }

    /**
     * Registrar rutas para un modelo específico
     *
     * @param string $modelClass
     * @param ApiCrudResource $config
     * @return void
     */
    private static function registerModelRoutes(string $modelClass, ApiCrudResource $config): void
    {
        $endpoint = $config->getEndpoint($modelClass);
        $controllerClass = $config->getControllerClass();

        // GET /api/resource - List
        Flight::route("GET {$endpoint}", function () use ($controllerClass, $modelClass) {
            $controller = new $controllerClass($modelClass);
            $controller->list();
        });
        self::$registeredRoutes[] = "GET {$endpoint}";

        // GET /api/resource/{id} - Get
        Flight::route("GET {$endpoint}/@id", function ($id) use ($controllerClass, $modelClass) {
            $controller = new $controllerClass($modelClass);
            $controller->get($id);
        });
        self::$registeredRoutes[] = "GET {$endpoint}/{id}";

        // POST /api/resource - Create
        Flight::route("POST {$endpoint}", function () use ($controllerClass, $modelClass) {
            $controller = new $controllerClass($modelClass);
            $controller->create();
        });
        self::$registeredRoutes[] = "POST {$endpoint}";

        // PUT /api/resource/{id} - Update
        Flight::route("PUT {$endpoint}/@id", function ($id) use ($controllerClass, $modelClass) {
            $controller = new $controllerClass($modelClass);
            $controller->update($id);
        });
        self::$registeredRoutes[] = "PUT {$endpoint}/{id}";

        // DELETE /api/resource/{id} - Delete
        Flight::route("DELETE {$endpoint}/@id", function ($id) use ($controllerClass, $modelClass) {
            $controller = new $controllerClass($modelClass);
            $controller->delete($id);
        });
        self::$registeredRoutes[] = "DELETE {$endpoint}/{id}";

        // Log individual
        if (self::isDevelopment()) {
            $shortName = (new \ReflectionClass($modelClass))->getShortName();
            error_log("[ApiCrudRouter] ✓ Registered CRUD for {$shortName} at {$endpoint}");
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
     * Agregar path adicional para escaneo de modelos
     *
     * @param string $path
     * @return void
     */
    public static function addModelPath(string $path): void
    {
        if (is_dir($path) && !in_array($path, self::$modelPaths)) {
            self::$modelPaths[] = $path;
        }
    }

    /**
     * Obtener configuración de un modelo
     *
     * @param string $modelClass
     * @return ApiCrudResource|null
     */
    public static function getModelConfig(string $modelClass): ?ApiCrudResource
    {
        try {
            $reflection = new \ReflectionClass($modelClass);
            $attributes = $reflection->getAttributes(ApiCrudResource::class);

            if (empty($attributes)) {
                return null;
            }

            return $attributes[0]->newInstance();
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
