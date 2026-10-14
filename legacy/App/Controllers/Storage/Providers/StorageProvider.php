<?php
/**
 * StorageProvider - Lógica de negocio para operaciones de storage
 *
 * PROPÓSITO:
 * Capa intermedia entre el Controller y el StorageService.
 * Maneja la lógica de negocio y transformación de datos.
 */

namespace App\Controllers\Storage\Providers;

use Core\Services\Storage\StorageService;
use Core\Services\Storage\StorageException;

class StorageProvider
{
    private StorageService $storage;

    public function __construct()
    {
        $this->storage = new StorageService();
    }

    /**
     * Maneja el upload de un archivo
     *
     * @param array $file Archivo desde $_FILES
     * @param string|null $customName Nombre personalizado (opcional)
     * @param string $path Ruta dentro del bucket
     * @return array Resultado con información del archivo
     */
    public function handleUpload(array $file, ?string $customName, string $path): array
    {
        // Asegurar que la ruta termine con /
        $path = rtrim($path, '/') . '/';

        // Subir archivo usando StorageService
        $url = $this->storage->upload($file, $customName, $path);

        // Obtener información adicional del archivo subido
        $filename = $customName ?? $file['name'];
        $filename = $this->sanitizeFilename($filename);

        // Construir path completo
        $fullPath = $path . $filename;

        // Obtener info completa
        try {
            $info = $this->storage->get($fullPath);
        } catch (StorageException $e) {
            // Si falla, devolver info básica
            $info = [
                'size' => $file['size'],
                'contentType' => $file['type'],
            ];
        }

        return [
            'url' => $url,
            'filename' => $filename,
            'path' => $fullPath,
            'size' => $info['size'] ?? $file['size'],
            'mimeType' => $info['contentType'] ?? $file['type'],
            'uploadedAt' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Lista archivos en una carpeta
     */
    public function listFiles(string $path = '', int $limit = 100): array
    {
        $result = $this->storage->list($path, $limit);

        return [
            'files' => $result['files'],
            'count' => $result['count'],
            'truncated' => $result['truncated'],
            'path' => $path,
        ];
    }

    /**
     * Obtiene información de un archivo
     */
    public function getFileInfo(string $filePath): array
    {
        $info = $this->storage->get($filePath);

        return [
            'exists' => $info['exists'],
            'path' => $filePath,
            'url' => $info['url'],
            'size' => $info['size'],
            'sizeMB' => round($info['size'] / 1024 / 1024, 2),
            'mimeType' => $info['contentType'],
            'lastModified' => $info['lastModified'] ? $info['lastModified']->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Elimina un archivo
     */
    public function deleteFile(string $filePath): bool
    {
        return $this->storage->delete($filePath);
    }

    /**
     * Obtiene estadísticas del storage
     */
    public function getStats(): array
    {
        $stats = $this->storage->getStats();
        $config = $this->storage->getConfig();

        return [
            'totalFiles' => $stats['totalFiles'],
            'totalSize' => $stats['totalSize'],
            'totalSizeMB' => $stats['totalSizeMB'],
            'fileTypes' => $stats['fileTypes'],
            'bucket' => $config->getBucket(),
            'endpoint' => $config->getPublicEndpoint(),
        ];
    }

    /**
     * Sanitiza nombre de archivo
     */
    private function sanitizeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        $name = preg_replace('/[^a-zA-Z0-9_-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');

        if (empty($name)) {
            $name = 'file-' . uniqid();
        }

        return $name . '.' . $extension;
    }
}
