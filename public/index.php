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

// Delegar todo el routing a Core/Router
\Core\Router::dispatch();
