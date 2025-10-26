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
use Core\Controller\CoreController;
use Flight;

/**
 * Rutas de autenticación modular
 * Patrón: /auth/{group}/{action}
 * Grupos: admin, api, [extensibles]
 */
Flight::route('POST|GET /auth/@group/@accion', fn ($group, $accion) => new AuthGroupsController($group, $accion));

/**
 * Rutas dinámicas de controladores
 * Se mapean automáticamente desde App/Controllers/
 */
$dynamicRoutes = CoreController::getMymapControllers();

foreach ($dynamicRoutes as $keyRoutes => $valRoutes) {
    Flight::route("POST|GET /$keyRoutes/@accion", fn ($accion) => new $valRoutes($accion));
}