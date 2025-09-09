<?php
// Router para archivos estáticos - replicando nginx.conf

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
        default: 
            return function_exists('mime_content_type') ? mime_content_type($file) : 'text/plain';
    }
}

// Servir archivos de assets
if (preg_match('/^\/assets\/(.*)$/', $path, $matches)) {
    $file = __DIR__ . '/../assets/' . $matches[1];
    if (file_exists($file) && is_file($file)) {
        $mime = getMimeType($file);
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
}

// Servir archivos .js y .css de components
if (preg_match('/^\/components\/(.+\.(js|css))$/', $path, $matches)) {
    $file = __DIR__ . '/../components/' . $matches[1];
    if (file_exists($file) && is_file($file)) {
        $mime = getMimeType($file);
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
}

// Si no es un archivo estático, usar el router normal
require __DIR__ . '/index.php';
?>