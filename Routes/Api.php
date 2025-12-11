<?php
/**
 * API Routes - Backend API (JSON Responses)
 *
 * PROPÓSITO:
 * Endpoints REST para lógica de negocio, autenticación y operaciones del backend.
 * Retornan JSON para ser consumidos por el frontend o clientes externos.
 *
 * CARACTERÍSTICAS:
 * - Retornan siempre JSON
 * - Sistema de autenticación modular extensible
 * - Rutas dinámicas basadas en controladores
 * - Validación de requests integrada
 *
 * SISTEMA DE AUTENTICACIÓN MODULAR:
 * Soporta múltiples grupos de autenticación con roles independientes:
 *
 * Grupos Disponibles:
 * - Admin: Usuarios administrativos del sistema Lego
 * - Api: Tokens/usuarios para consumo externo de API
 * - [Extensible: Agregar nuevos grupos en AuthGroups/]
 *
 * Cada grupo tiene:
 * - Sus propias reglas de autenticación
 * - Sus propios roles y permisos
 * - Sus propios middlewares
 * - Independencia total entre grupos
 *
 * RUTAS DE AUTENTICACIÓN:
 * POST /api/auth/{group}/{action}
 *
 * Ejemplos:
 * - POST /api/auth/admin/login       → Login para usuarios admin
 * - POST /api/auth/admin/logout      → Logout de sesión admin
 * - POST /api/auth/api/login         → Login para API tokens
 * - POST /api/auth/api/refresh_token → Refresh de token API
 *
 * RUTAS DINÁMICAS:
 * Las rutas de controladores se mapean automáticamente desde:
 * - App/Controllers/ → Registrados en CoreController::getMymapControllers()
 *
 * Patrón: POST|GET /api/{controller}/{action}
 *
 * Ejemplos:
 * - GET  /api/users/list    → UserController->list()
 * - POST /api/users/create  → UserController->create()
 * - GET  /api/products/show → ProductController->show()
 */

namespace Routes;

use App\Controllers\Auth\Controllers\AuthGroupsController;
use App\Controllers\Menu\Controllers\MenuSearchController;
use App\Controllers\ComponentsController;
use Core\Controllers\CoreController;
use Core\Routing\ApiCrudRouter;
use Core\Routing\ApiGetRouter;
use Core\Routing\ApiControllerRouter;
use Flight;

/**
 * ========================================
 * MENU SEARCH API
 * ========================================
 * Búsqueda de items del menú desde la base de datos
 * Incluye: visible=true, visible=false (ocultos pero buscables)
 * Excluye: dynamic=true (requieren contexto)
 */
Flight::route('GET /menu/search', function() {
    new MenuSearchController();
});

/**
 * ========================================
 * MENU STRUCTURE API
 * ========================================
 * Obtiene la estructura completa del menú en formato MenuItemDto[]
 * Solo items visibles, con estructura de árbol
 */
Flight::route('GET /menu/structure', function() {
    new \App\Controllers\Menu\Controllers\MenuStructureController();
});

/**
 * ========================================
 * MENU SYSTEM ITEMS API
 * ========================================
 * Obtiene items ocultos del sistema (is_visible=false, is_dynamic=false)
 * Para mostrar en el dropdown de configuración del header
 */
Flight::route('GET /menu/system-items', function() {
    new \App\Controllers\Menu\Controllers\MenuSystemItemsController();
});

/**
 * ========================================
 * MENU ITEM HIERARCHY API
 * ========================================
 * Obtiene la jerarquía completa de un item (ancestros + item + hijos)
 */
Flight::route('GET /menu/item-hierarchy', function() {
    new \App\Controllers\Menu\Controllers\MenuItemHierarchyController();
});

/**
 * ========================================
 * AUTO-CONTROLLER ROUTES (Atributo #[ApiRoutes])
 * ========================================
 * Rutas de controladores generadas automáticamente desde #[ApiRoutes].
 * Soporta cualquier tipo de controlador: CRUD, reportes, webhooks, etc.
 * Ver: Core/Routing/ApiControllerRouter.php
 *
 * Controladores registrados:
 * - ExampleCrudController → /api/example-crud (crud + imágenes)
 * - ToolsController → /api/tools (crud)
 * - [Agregar más controladores con #[ApiRoutes]]
 */
ApiControllerRouter::registerRoutes();

/**
 * ========================================
 * AUTO-GET ROUTES (Table-Driven API)
 * ========================================
 * Rutas GET de solo lectura generadas automáticamente desde modelos con #[ApiGetResource].
 * Específico para alimentar TableComponent con paginación, filtros y búsqueda.
 * Ver: Core/Routing/ApiGetRouter.php
 *
 * Patrón: /api/get/{resource}
 * Endpoints: GET /api/get/{resource} (list), GET /api/get/{resource}/{id} (get)
 */
ApiGetRouter::registerRoutes();

/**
 * ========================================
 * AUTO-CRUD ROUTES (Model-Driven API)
 * ========================================
 * Rutas CRUD completas generadas automáticamente desde modelos con #[ApiCrudResource].
 * Ver: Core/Routing/ApiCrudRouter.php
 *
 * Patrón: /api/{resource}
 * Endpoints: GET, POST, PUT, DELETE /api/{resource}
 */
ApiCrudRouter::registerRoutes();

/**
 * ========================================
 * AUTHENTICATION ROUTES
 * ========================================
 * Rutas de autenticación modular
 * Patrón: /auth/{group}/{action}
 * Grupos: admin, api, [extensibles]
 */
Flight::route('POST|GET /auth/@group/@accion', fn ($group, $accion) => new AuthGroupsController($group, $accion));

/**
 * ========================================
 * LEGACY PRODUCT ROUTES (DEPRECATED)
 * ========================================
 * NOTA: Estas rutas ahora se generan automáticamente desde Product model.
 * Ver: App/Models/Product.php con #[ApiCrudResource]
 *
 * Las rutas manuales están comentadas. Si necesitas comportamiento custom,
 * puedes especificar un controllerClass custom en el atributo #[ApiCrudResource].
 */

/*
// GET /api/products - Listar todos
Flight::route('GET /products', function() {
    new ProductsController('list');
});

// GET /api/products/{id} - Obtener uno por ID
Flight::route('GET /products/@id', function($id) {
    $_REQUEST['id'] = $id;
    $_GET['id'] = $id;
    new ProductsController('get');
});

// POST /api/products - Crear nuevo
Flight::route('POST /products', function() {
    new ProductsController('create');
});

// PUT /api/products/{id} - Actualizar existente
Flight::route('PUT /products/@id', function($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $input['id'] = $id;
        $_POST = array_merge($_POST, $input);
    }
    new ProductsController('update');
});

// DELETE /api/products/{id} - Eliminar
Flight::route('DELETE /products/@id', function($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = [];
    }
    $input['id'] = $id;
    $_POST = array_merge($_POST, $input);
    new ProductsController('delete');
});
*/

/**
 * Rutas de componentes dinámicos (Sistema LEGO)
 * Renderizado de componentes desde JavaScript
 */

// GET /api/components/render - Renderizar componente único
// Query: ?id=icon-button&params={"action":"edit","entityId":14}
Flight::route('GET /components/render', function() {
    $controller = new ComponentsController();
    $controller->render();
});

// POST /api/components/batch - Renderizar múltiples componentes en batch
// Body: {"component":"icon-button","renders":[{...},{...}]}
Flight::route('POST /components/batch', function() {
    $controller = new ComponentsController();
    $controller->batch();
});

// GET /api/components/list - Listar componentes registrados (debug)
Flight::route('GET /components/list', function() {
    $controller = new ComponentsController();
    $controller->list();
});

/**
 * Rutas dinámicas de controladores (LEGACY para V1/V2)
 * Se mapean automáticamente desde App/Controllers/
 */
$dynamicRoutes = CoreController::getMymapControllers();

foreach ($dynamicRoutes as $keyRoutes => $valRoutes) {
    Flight::route("POST|GET /$keyRoutes/@accion", fn ($accion) => new $valRoutes($accion));
}