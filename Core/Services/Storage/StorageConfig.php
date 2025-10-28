<?php
/**
 * StorageConfig - Configuración centralizada del sistema de storage
 *
 * PROPÓSITO:
 * Lee y valida la configuración de MinIO/S3 desde variables de entorno.
 * Proporciona acceso centralizado a todas las configuraciones de storage.
 *
 * FILOSOFÍA LEGO:
 * Configuración inmutable y type-safe. Una sola fuente de verdad.
 */

namespace Core\Services\Storage;

class StorageConfig
{
    private string $host;
    private string $port;
    private string $accessKey;
    private string $secretKey;
    private string $bucket;
    private bool $useSSL;
    private string $region;
    private int $maxFileSize;
    private array $allowedExtensions;

    public function __construct()
    {
        // Cargar configuración desde .env
        $this->host = env('MINIO_HOST', 'minio');
        $this->port = env('MINIO_PORT', '9000');
        $this->accessKey = env('MINIO_ROOT_USER', 'minioadmin');
        $this->secretKey = env('MINIO_ROOT_PASSWORD', 'minioadmin123');
        $this->bucket = env('MINIO_BUCKET', 'lego-uploads');
        $this->useSSL = env('MINIO_USE_SSL', 'false') === 'true';
        $this->region = env('MINIO_REGION', 'us-east-1');

        // Configuración de uploads
        $this->maxFileSize = (int) env('STORAGE_MAX_FILE_SIZE', 10485760); // 10MB default

        $extensions = env('STORAGE_ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip');
        $this->allowedExtensions = array_map('trim', explode(',', $extensions));
    }

    /**
     * Obtiene el endpoint completo de MinIO
     */
    public function getEndpoint(): string
    {
        $protocol = $this->useSSL ? 'https' : 'http';
        return "{$protocol}://{$this->host}:{$this->port}";
    }

    /**
     * Obtiene el endpoint para uso interno (dentro de Docker)
     */
    public function getInternalEndpoint(): string
    {
        return "http://{$this->host}:{$this->port}";
    }

    /**
     * Obtiene el endpoint público (para URLs accesibles desde navegador)
     */
    public function getPublicEndpoint(): string
    {
        // En desarrollo, usar localhost. En producción, usar el dominio real
        $publicHost = env('MINIO_PUBLIC_HOST', 'localhost');
        $protocol = $this->useSSL ? 'https' : 'http';
        return "{$protocol}://{$publicHost}:{$this->port}";
    }

    /**
     * Genera URL pública completa para un archivo
     */
    public function getPublicUrl(string $path): string
    {
        $endpoint = $this->getPublicEndpoint();
        $bucket = $this->bucket;
        $cleanPath = ltrim($path, '/');

        return "{$endpoint}/{$bucket}/{$cleanPath}";
    }

    // Getters
    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function useSSL(): bool
    {
        return $this->useSSL;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * Valida si una extensión está permitida
     */
    public function isExtensionAllowed(string $extension): bool
    {
        return in_array(strtolower($extension), $this->allowedExtensions);
    }

    /**
     * Convierte la configuración a array (útil para debugging)
     */
    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'bucket' => $this->bucket,
            'useSSL' => $this->useSSL,
            'region' => $this->region,
            'endpoint' => $this->getEndpoint(),
            'publicEndpoint' => $this->getPublicEndpoint(),
            'maxFileSize' => $this->maxFileSize,
            'maxFileSizeMB' => round($this->maxFileSize / 1024 / 1024, 2),
            'allowedExtensions' => $this->allowedExtensions,
        ];
    }
}
