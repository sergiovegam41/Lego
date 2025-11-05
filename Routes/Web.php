<?php
/**
 * Web Routes - Páginas Completas (Entry Points)
 *
 * PROPÓSITO:
 * Rutas que renderizan HTML completo (DOCTYPE, HEAD, BODY).
 * Son los puntos de entrada iniciales a la aplicación.
 *
 * CARACTERÍSTICAS:
 * - Retornan documentos HTML completos
 * - Incluyen MainComponent (layout SPA) o LoginComponent
 * - Registro manual de rutas
 * - Se acceden directamente desde el navegador
 *
 * DIFERENCIA CON Component.php:
 * - Web: Páginas completas → Layout inicial con menu/header
 * - Component: Módulos SPA → Solo contenido para #home-page
 *
 * EJEMPLOS:
 * - GET /admin  → MainComponent (Layout SPA con sidebar/header)
 * - GET /login  → LoginComponent (Página de autenticación)
 * - GET /       → Redirect a /admin
 */

namespace Routes;

use App\Controllers\Auth\Providers\AuthGroups\Admin\Middlewares\AdminMiddlewares;
use Core\Helpers\LegoHelpers;
use Core\Response;
use Flight;
use Components\Core\Home\Components\MainComponent\MainComponent;
use Components\Core\Login\LoginComponent;
use Components\App\FormsShowcase\FormsShowcaseComponent;

/**
 * Ruta principal de la aplicación
 * Renderiza el layout SPA completo (sidebar, header, contenedor de módulos)
 */
Flight::route('GET /admin/', function () {
    if (AdminMiddlewares::isAutenticated()) {
        $component = new MainComponent();
        return Response::uri($component->render());
    }
});

/**
 * Página de autenticación
 * Renderiza el formulario de login
 */
Flight::route('GET /login', function () {
    $component = new LoginComponent();
    Response::uri($component->render());
});

/**
 * Página de showcase de formularios
 * Renderiza la demostración de componentes de formulario
 */
Flight::route('GET /forms-showcase', function () {
    $component = new FormsShowcaseComponent();
    Response::uri($component->render());
});

/**
 * Ruta raíz - Redirige a admin
 */
Flight::route('GET /', function () {
    LegoHelpers::redirect('admin');
});

/**
 * Proxy para archivos de MinIO
 * Sirve archivos desde MinIO a través del backend
 * Ejemplo: /storage/lego-uploads/categories/images/file.jpg
 */
Flight::route('GET /storage/@path+', function ($path) {
    try {
        $storageService = new \Core\Services\Storage\StorageService();

        // La ruta viene como array, unirla
        $fullPath = is_array($path) ? implode('/', $path) : $path;

        // Obtener el archivo de MinIO
        $fileContent = $storageService->get($fullPath);

        if (!$fileContent) {
            http_response_code(404);
            echo '404 - File not found';
            return;
        }

        // Detectar tipo MIME
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Headers para caché
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

        echo $fileContent;

    } catch (\Exception $e) {
        http_response_code(500);
        echo '500 - Error: ' . $e->getMessage();
    }
});

