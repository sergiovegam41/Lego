<?php
/**
 * Debug script para verificar registro de rutas ApiGetRouter
 */

require_once __DIR__ . '/vendor/autoload.php';

// Cargar config
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Inicializar bootstrap
require_once __DIR__ . '/Core/bootstrap.php';

echo "=== DEBUG: ApiGetRouter ===\n\n";

// 1. Verificar que la clase existe
echo "1. Verificar clase ApiGetRouter:\n";
if (class_exists('Core\Routing\ApiGetRouter')) {
    echo "   ✓ ApiGetRouter existe\n";
} else {
    echo "   ✗ ApiGetRouter NO existe\n";
    exit(1);
}

// 2. Verificar que Product tiene el atributo
echo "\n2. Verificar Product model:\n";
if (class_exists('App\Models\Product')) {
    echo "   ✓ Product model existe\n";

    $reflection = new \ReflectionClass('App\Models\Product');
    $attributes = $reflection->getAttributes('Core\Attributes\ApiGetResource');

    if (!empty($attributes)) {
        echo "   ✓ Product tiene #[ApiGetResource]\n";
        $config = $attributes[0]->newInstance();
        echo "   - Endpoint: " . $config->getEndpoint('App\Models\Product') . "\n";
        echo "   - Pagination: {$config->pagination}\n";
        echo "   - Per page: {$config->perPage}\n";
    } else {
        echo "   ✗ Product NO tiene #[ApiGetResource]\n";

        // Verificar si tiene ApiCrudResource
        $crudAttr = $reflection->getAttributes('Core\Attributes\ApiCrudResource');
        if (!empty($crudAttr)) {
            echo "   ⚠ Product tiene #[ApiCrudResource] (debería ser ApiGetResource)\n";
        }
    }
} else {
    echo "   ✗ Product model NO existe\n";
}

// 3. Verificar discovery de modelos
echo "\n3. Verificar discovery de modelos:\n";
$models = (function() {
    $modelPaths = [__DIR__ . '/App/Models/'];
    $models = [];

    foreach ($modelPaths as $path) {
        if (!is_dir($path)) {
            continue;
        }

        $files = glob($path . '*.php');

        foreach ($files as $file) {
            // Extraer clase desde archivo
            $content = file_get_contents($file);

            $namespace = null;
            if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
                $namespace = $matches[1];
            }

            $className = null;
            if (preg_match('/class\s+(\w+)/', $content, $matches)) {
                $className = $matches[1];
            }

            if (!$className) {
                continue;
            }

            $modelClass = $namespace ? "{$namespace}\\{$className}" : $className;

            try {
                $reflection = new \ReflectionClass($modelClass);
                $attributes = $reflection->getAttributes('Core\Attributes\ApiGetResource');

                if (!empty($attributes)) {
                    $config = $attributes[0]->newInstance();
                    $models[$modelClass] = $config;
                }
            } catch (\ReflectionException $e) {
                continue;
            }
        }
    }

    return $models;
})();

echo "   Modelos encontrados con #[ApiGetResource]: " . count($models) . "\n";
foreach ($models as $modelClass => $config) {
    $shortName = (new \ReflectionClass($modelClass))->getShortName();
    $endpoint = $config->getEndpoint($modelClass);
    echo "   - {$shortName}: {$endpoint}\n";
}

// 4. Verificar Flight
echo "\n4. Verificar Flight PHP:\n";
if (class_exists('Flight')) {
    echo "   ✓ Flight existe\n";
} else {
    echo "   ✗ Flight NO existe\n";
}

// 5. Registrar rutas y verificar
echo "\n5. Intentar registrar rutas:\n";
try {
    \Core\Routing\ApiGetRouter::registerRoutes();
    echo "   ✓ registerRoutes() ejecutado sin errores\n";

    $registeredRoutes = \Core\Routing\ApiGetRouter::getRegisteredRoutes();
    echo "   Rutas registradas: " . count($registeredRoutes) . "\n";
    foreach ($registeredRoutes as $route) {
        echo "   - {$route}\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// 6. Verificar rutas de Flight
echo "\n6. Verificar rutas de Flight:\n";
$flightRoutes = Flight::router()->getRoutes();
echo "   Total rutas Flight: " . count($flightRoutes) . "\n";

$getRoutes = array_filter($flightRoutes, function($route) {
    return strpos($route->pattern, '/api/get/') === 0;
});

echo "   Rutas /api/get/*: " . count($getRoutes) . "\n";
foreach ($getRoutes as $route) {
    echo "   - {$route->methods[0]} {$route->pattern}\n";
}

echo "\n=== FIN DEBUG ===\n";
