<?php

namespace Core\Services;

use Core\Attributes\ApiComponent;
use Flight;
use ReflectionClass;
use App\Controllers\Auth\Providers\AuthGroups\Admin\Middlewares\AdminMiddlewares;
use Core\Response;

class ApiRouteDiscovery
{
    private static $discovered = false;

    /**
     * Descubre y registra automáticamente todas las rutas API de componentes
     */
    public static function discover(): void
    {
        if (self::$discovered) {
            return;
        }

        $componentsPath = __DIR__ . '/../../components';
        $componentFiles = self::findComponentFiles($componentsPath);

        foreach ($componentFiles as $file) {
            self::registerApiRoute($file);
        }

        self::$discovered = true;
    }

    /**
     * Encuentra todos los archivos de componentes
     */
    private static function findComponentFiles(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = [];

        // Escanear recursivamente todos los subdirectorios
        $directories = [$path];

        while (!empty($directories)) {
            $currentDir = array_shift($directories);

            try {
                if (!is_dir($currentDir) || !is_readable($currentDir)) {
                    continue;
                }

                $items = @scandir($currentDir);
                if ($items === false) {
                    error_log("ApiRouteDiscovery: Cannot read directory: $currentDir");
                    continue;
                }

                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }

                    $itemPath = $currentDir . DIRECTORY_SEPARATOR . $item;

                    if (is_dir($itemPath)) {
                        $directories[] = $itemPath;
                    } elseif (is_file($itemPath) && preg_match('/Component\.php$/i', $item)) {
                        $files[] = $itemPath;
                    }
                }
            } catch (\Exception $e) {
                error_log("ApiRouteDiscovery: Error scanning $currentDir - " . $e->getMessage());
                continue;
            }
        }

        return $files;
    }

    /**
     * Registra ruta API si el componente tiene el atributo
     */
    private static function registerApiRoute(string $filePath): void
    {
        $className = self::extractClassName($filePath);

        if (!$className || !class_exists($className)) {
            return;
        }

        $reflection = new ReflectionClass($className);
        $attributes = $reflection->getAttributes(ApiComponent::class);

        if (empty($attributes)) {
            return;
        }

        $apiConfig = $attributes[0]->newInstance();
        
        foreach ($apiConfig->methods as $method) {
            self::registerMethod($method, $apiConfig, $className);
        }
    }

    /**
     * Registra un método HTTP específico
     */
    private static function registerMethod(string $method, ApiComponent $config, string $className): void
    {
        $httpMethod = strtoupper($method);
        $path = $config->path;


        Flight::route("$httpMethod $path", function() use ($className, $method, $config) {
            
            if ($config->requiresAuth && !AdminMiddlewares::isAutenticated()) {
                http_response_code(401);
                header('Content-Type: application/json');
                return json_encode(['error' => 'Unauthorized']);
            }

            $component = new $className([]);

            return Response::uri( $component->render() );
            // $methodName = 'api' . ucfirst(strtolower($method));
            
            // if (method_exists($component, $methodName)) {
            //     $result = $component->$methodName(Flight::request());
            // } else {
            //     $result = ['error' => "Method $methodName not found"];
            // }

            // header('Content-Type: application/json');
            // return json_encode($result);
        });
    }

    /**
     * Extrae el nombre de la clase desde un archivo PHP
     */
    private static function extractClassName(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        
        if (!preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return null;
        }
        
        if (!preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return null;
        }

        return trim($namespaceMatch[1]) . '\\' . $classMatch[1];
    }
}