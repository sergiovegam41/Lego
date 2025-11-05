<?php
/**
 * StorageService - API Simplificada para el sistema de storage
 *
 * PROPÓSITO:
 * Servicio principal de almacenamiento para Lego Framework.
 * Proporciona una API simple de 3 parámetros: upload($file, $name, $path)
 *
 * FILOSOFÍA LEGO:
 * "Solo pasar el file, nombre y ruta" - API minimalista y fácil de usar.
 *
 * USO BÁSICO:
 * $storage = new StorageService();
 * $url = $storage->upload($_FILES['file'], 'producto.jpg', 'products/images/');
 *
 * FEATURES:
 * - Auto-validación de archivos
 * - Sanitización de nombres
 * - Generación de nombres únicos
 * - URLs públicas automáticas
 * - Manejo de errores granular
 */

namespace Core\Services\Storage;

class StorageService
{
    private MinioClient $client;
    private StorageConfig $config;

    public function __construct()
    {
        $this->config = new StorageConfig();
        $this->client = new MinioClient($this->config);
    }

    /**
     * API PRINCIPAL: Sube un archivo con 3 parámetros simples
     *
     * @param array $file Array de $_FILES (debe contener: name, type, tmp_name, size)
     * @param string|null $customName Nombre personalizado (opcional, usa nombre original si null)
     * @param string $path Ruta dentro del bucket (ej: "products/images/")
     * @return string URL pública del archivo subido
     *
     * @throws StorageException Si ocurre algún error
     */
    public function upload(array $file, ?string $customName = null, string $path = 'temp/'): string
    {
        // Validar archivo
        $this->validateFile($file);

        // Determinar nombre final
        $filename = $customName ?? $file['name'];
        $filename = $this->sanitizeFilename($filename);

        // Generar nombre único si el archivo ya existe
        $filename = $this->generateUniqueFilename($filename, $path);

        // Construir ruta completa (path + filename)
        $fullPath = rtrim($path, '/') . '/' . $filename;

        // Detectar MIME type
        $mimeType = $file['type'] ?? $this->getMimeType($file['tmp_name']);

        // Subir archivo
        $result = $this->client->putObject(
            $this->config->getBucket(),
            $fullPath,
            fopen($file['tmp_name'], 'r'),
            $mimeType
        );

        return $result['url'];
    }

    /**
     * Obtiene información de un archivo
     *
     * @param string $filePath Ruta del archivo en el bucket
     * @return array Información del archivo
     */
    public function get(string $filePath): array
    {
        return $this->client->getObjectInfo($this->config->getBucket(), $filePath);
    }

    /**
     * Obtiene el contenido de un archivo
     *
     * @param string $filePath Ruta del archivo en el bucket
     * @return string Contenido del archivo
     */
    public function getContent(string $filePath): string
    {
        return $this->client->getObject($this->config->getBucket(), $filePath);
    }

    /**
     * Elimina un archivo
     *
     * @param string $filePath Ruta del archivo en el bucket
     * @return bool true si se eliminó correctamente
     */
    public function delete(string $filePath): bool
    {
        return $this->client->deleteObject($this->config->getBucket(), $filePath);
    }

    /**
     * Lista archivos en una carpeta
     *
     * @param string $path Carpeta a listar (ej: "products/images/")
     * @param int $limit Cantidad máxima de archivos
     * @return array Lista de archivos
     */
    public function list(string $path = '', int $limit = 100): array
    {
        return $this->client->listObjects($this->config->getBucket(), $path, $limit);
    }

    /**
     * Verifica si un archivo existe
     */
    public function exists(string $filePath): bool
    {
        return $this->client->objectExists($this->config->getBucket(), $filePath);
    }

    /**
     * Copia un archivo a otra ubicación
     */
    public function copy(string $source, string $destination): bool
    {
        $bucket = $this->config->getBucket();
        return $this->client->copyObject($bucket, $source, $bucket, $destination);
    }

    /**
     * Mueve un archivo (copia + elimina original)
     */
    public function move(string $source, string $destination): bool
    {
        if ($this->copy($source, $destination)) {
            return $this->delete($source);
        }
        return false;
    }

    // ==================== MÉTODOS DE INICIALIZACIÓN ====================

    /**
     * Verifica si MinIO está conectado
     */
    public function isConnected(): bool
    {
        return $this->client->isConnected();
    }

    /**
     * Verifica si el bucket existe
     */
    public function bucketExists(?string $bucket = null): bool
    {
        $bucket = $bucket ?? $this->config->getBucket();
        return $this->client->bucketExists($bucket);
    }

    /**
     * Crea el bucket principal
     */
    public function createBucket(?string $bucket = null): bool
    {
        $bucket = $bucket ?? $this->config->getBucket();
        return $this->client->createBucket($bucket);
    }

    /**
     * Configura el bucket como público
     */
    public function setBucketPublic(?string $bucket = null): bool
    {
        $bucket = $bucket ?? $this->config->getBucket();
        return $this->client->setBucketPublic($bucket);
    }

    /**
     * Crea una carpeta (en realidad sube un archivo .keep vacío)
     */
    public function createFolder(string $folderPath): bool
    {
        $path = rtrim($folderPath, '/') . '/.keep';

        try {
            $this->client->putObject(
                $this->config->getBucket(),
                $path,
                '',
                'text/plain'
            );
            return true;
        } catch (StorageException $e) {
            return false;
        }
    }

    /**
     * Obtiene estadísticas del bucket
     */
    public function getStats(): array
    {
        return $this->client->getBucketStats($this->config->getBucket());
    }

    /**
     * Obtiene la configuración actual
     */
    public function getConfig(): StorageConfig
    {
        return $this->config;
    }

    // ==================== MÉTODOS PRIVADOS ====================

    /**
     * Valida que el archivo sea válido y esté dentro de los límites
     */
    private function validateFile(array $file): void
    {
        // Validar estructura del array
        if (!isset($file['name'], $file['type'], $file['tmp_name'], $file['size'])) {
            throw StorageException::invalidFile('Estructura de archivo inválida');
        }

        // Validar errores de upload
        if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            throw StorageException::invalidFile($this->getUploadErrorMessage($file['error']));
        }

        // Validar que el archivo temporal existe
        if (!file_exists($file['tmp_name'])) {
            throw StorageException::invalidFile('Archivo temporal no encontrado');
        }

        // Validar tamaño
        $maxSize = $this->config->getMaxFileSize();
        if ($file['size'] > $maxSize) {
            throw StorageException::fileTooLarge($file['size'], $maxSize);
        }

        // Validar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!$this->config->isExtensionAllowed($extension)) {
            throw StorageException::invalidExtension($extension, $this->config->getAllowedExtensions());
        }
    }

    /**
     * Sanitiza el nombre de archivo (elimina caracteres peligrosos)
     */
    private function sanitizeFilename(string $filename): string
    {
        // Obtener extensión
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        // Limpiar nombre
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '-', $name); // Reemplazar caracteres especiales
        $name = preg_replace('/-+/', '-', $name); // Eliminar guiones duplicados
        $name = trim($name, '-'); // Eliminar guiones al inicio/fin

        // Si el nombre quedó vacío, generar uno aleatorio
        if (empty($name)) {
            $name = 'file-' . uniqid();
        }

        return $name . '.' . $extension;
    }

    /**
     * Genera un nombre único si el archivo ya existe
     */
    private function generateUniqueFilename(string $filename, string $path): string
    {
        $fullPath = rtrim($path, '/') . '/' . $filename;

        // Si no existe, usar el nombre tal cual
        if (!$this->exists($fullPath)) {
            return $filename;
        }

        // Generar nombre único con timestamp
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        $uniqueName = $name . '-' . time() . '.' . $extension;

        return $uniqueName;
    }

    /**
     * Detecta el MIME type de un archivo
     */
    private function getMimeType(string $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Obtiene mensaje de error de upload de PHP
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir archivo en disco',
            UPLOAD_ERR_EXTENSION => 'Extensión de PHP detuvo el upload',
        ];

        return $errors[$errorCode] ?? 'Error desconocido al subir archivo';
    }
}
