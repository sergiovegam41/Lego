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
 * Ruta raíz - Redirige a admin
 */
Flight::route('GET /', function () {
    LegoHelpers::redirect('admin');
});

