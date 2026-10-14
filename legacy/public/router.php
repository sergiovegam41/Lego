<?php
/**
 * Router para PHP Built-in Server
 *
 * PROPÓSITO:
 * Este archivo solo se usa cuando ejecutas `php -S localhost:8080 -t public router.php`
 * Replica el comportamiento de nginx.conf para desarrollo local.
 *
 * NOTA IMPORTANTE:
 * - Los assets de componentes (/component/nombre/file.css|js) ahora se sirven vía PHP
 * - Este router solo maneja assets GLOBALES (/assets/*)
 * - Los assets de componentes son manejados por Core/Router.php y Routes/Component.php
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

function getMimeType($file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    switch ($ext) {
        case 'css': return 'text/css';
        case 'js': return 'application/javascript';
        case 'png': return 'image/png';
        case 'jpg': case 'jpeg': return 'image/jpeg';
        case 'gif': return 'image/gif';
        case 'svg': return 'image/svg+xml';
        case 'ico': return 'image/x-icon';
        case 'webp': return 'image/webp';
        default:
            return function_exists('mime_content_type') ? mime_content_type($file) : 'text/plain';
    }
}

// Servir archivos de assets GLOBALES solamente
// Los assets de componentes (/component/*) se manejan en Core/Router.php
if (preg_match('/^\/assets\/(.*)$/', $path, $matches)) {
    $file = __DIR__ . '/../assets/' . $matches[1];
    if (file_exists($file) && is_file($file)) {
        $mime = getMimeType($file);
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000');
        readfile($file);
        exit;
    }
}

// TODO si es un archivo estático, delegar a Core/Router.php
// Este router es solo para el built-in server, toda la lógica real está en Core/Router.php
require __DIR__ . '/index.php';
?>