<?php
/**
 * Lego Framework - Entry Point
 *
 * PROPÓSITO:
 * Entry point minimalista y prescindible según entorno.
 * Toda la lógica de routing está en Core/Router.php
 *
 * PRESCINDIBLE:
 * Este archivo puede ser reemplazado según el servidor web:
 * - Apache: .htaccess puede reescribir rutas directamente
 * - Nginx: nginx.conf puede enviar requests directamente a Core/Router.php
 * - PHP Built-in Server: Este archivo es necesario
 *
 * ROUTING:
 * Ver Core/Router.php para detalles del sistema de 3 capas:
 * - /api/*       → API Backend
 * - /component/* → Component Routes + Assets
 * - /*           → Web Routes
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Core/bootstrap.php';

// DEBUG: Test autoloader for API routes
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    error_log("=== AUTOLOADER DEBUG ===");
    error_log("Request URI: " . $_SERVER['REQUEST_URI']);
    error_log("Autoload file exists: " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'YES' : 'NO'));
    error_log("Request.php file exists: " . (file_exists(__DIR__ . '/../Core/Providers/Request.php') ? 'YES' : 'NO'));
    error_log("Request class exists: " . (class_exists('Core\\Providers\\Request', false) ? 'YES (loaded)' : 'NO (not loaded)'));
    error_log("Trying to load Request class...");
    if (class_exists('Core\\Providers\\Request')) {
        error_log("Request class loaded successfully!");
    } else {
        error_log("Request class FAILED to load!");
    }
}

// Delegar todo el routing a Core/Router
\Core\Router::dispatch();
