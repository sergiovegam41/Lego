<?php
/**
 * Lego Framework - Router Principal
 *
 * PROPÓSITO:
 * Centraliza la lógica de routing en 3 capas, permitiendo que public/index.php
 * sea prescindible según el entorno (nginx, Apache, etc.).
 *
 * SISTEMA DE 3 CAPAS:
 *
 * 1. /api/*       → API Backend (Routes/Api.php)
 *    - Endpoints REST para lógica de negocio
 *    - Sistema de autenticación modular
 *    - Retorna JSON
 *
 * 2. /component/* → Component Routes (Routes/Component.php)
 *    - Componentes SPA con auto-discovery
 *    - Se refrescan dinámicamente vía JavaScript
 *    - Retorna HTML parcial (sin DOCTYPE/HEAD/BODY)
 *    - También sirve assets estáticos (.css, .js) de componentes
 *    - Filosofía: Sin estado en frontend, siempre actualizado desde servidor
 *
 * 3. /*           → Web Routes (Routes/Web.php)
 *    - Páginas completas (entry points)
 *    - Retorna HTML completo (DOCTYPE, HEAD, BODY)
 *    - Ejemplos: /admin, /login, /
 *
 * VENTAJAS DE CENTRALIZACIÓN:
 * - public/index.php puede ser reemplazado según servidor web
 * - Lógica de routing en un solo lugar
 * - Fácil de testear y mantener
 * - Cambios de estructura no afectan entry point
 */

namespace Core;

class Router
{
    /**
     * Enruta la request al archivo de rutas apropiado según el primer segmento de la URI
     *
     * @return void
     */
    public static function dispatch(): void
    {
        // Obtener URI completa incluyendo query string
        $fullUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($fullUri, PHP_URL_PATH);
        $queryString = parse_url($fullUri, PHP_URL_QUERY);
        
        // Normalizar path para comparación (sin slashes al inicio/final)
        $normalizedPath = trim($path, '/');
        
        // Reconstruir query string si existe
        $qs = $queryString ? '?' . $queryString : '';

        // Determinar capa de routing según primer segmento
        if (strpos($normalizedPath . '/', 'api/') === 0) {
            // Capa 1: API Backend
            // Quitar solo el prefijo 'api/' del path, preservar el resto exacto
            $newPath = substr($path, 4); // Quita '/api' del inicio
            if ($newPath === '' || $newPath === false) $newPath = '/';
            $_SERVER['REQUEST_URI'] = $newPath . $qs;
            require __DIR__ . '/../Routes/Api.php';

        } elseif (strpos($normalizedPath . '/', 'component/') === 0) {
            // Capa 2: Component Routes (SPA + Assets estáticos)
            // Quitar solo el prefijo 'component/' del path, preservar el resto exacto
            $newPath = substr($path, 10); // Quita '/component' del inicio
            if ($newPath === '' || $newPath === false) $newPath = '/';
            $_SERVER['REQUEST_URI'] = $newPath . $qs;
            require __DIR__ . '/../Routes/Component.php';

        } else {
            // Capa 3: Web Routes (Páginas completas)
            // Mantener REQUEST_URI original sin modificar
            require __DIR__ . '/../Routes/Web.php';
        }

        // Iniciar Flight framework
        \Flight::start();
    }

    /**
     * Sirve un archivo estático con headers de caché apropiados
     *
     * @param string $filePath Ruta absoluta al archivo
     * @return void
     */
    public static function serveStaticFile(string $filePath): void
    {
        // Verificar que el archivo existe
        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            echo "File not found";
            exit;
        }

        // Detectar MIME type
        $mimeTypes = [
            'css' => 'text/css',
            'js'  => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Headers de caché para eficiencia (1 año)
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        header('Content-Length: ' . filesize($filePath));

        // ETag para validación de caché
        $etag = md5_file($filePath);
        header('ETag: "' . $etag . '"');

        // Verificar si el cliente tiene versión cacheada
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
            trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag) {
            http_response_code(304); // Not Modified
            exit;
        }

        // Servir el archivo (muy eficiente, similar a nginx)
        readfile($filePath);
        exit;
    }
}
