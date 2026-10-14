<?php

/**
 * FileService - Servicio UNIVERSAL de gestión de archivos
 *
 * FILOSOFÍA LEGO:
 * Servicio genérico NO acoplado a ninguna entidad específica.
 * Cualquier CRUD puede usar este servicio para gestionar archivos.
 *
 * PROPÓSITO:
 * - Upload de archivos → retorna EntityFile con ID
 * - Delete de archivos → elimina de MinIO y BD
 * - Obtener archivos por IDs
 * - Validación centralizada
 *
 * PATRÓN DE USO:
 * 1. FilePond → POST /api/files/upload → FileService::uploadFile() → retorna file ID
 * 2. ProductsController recibe lista de file IDs y los guarda
 * 3. Al consultar: Product tiene file_ids → FileService::getFilesByIds() → retorna files
 *
 * BENEFICIOS:
 * - Zero reimplementación para nuevos CRUDs
 * - Separación de responsabilidades
 * - Validación centralizada
 * - Fácil testing y mantenimiento
 */

namespace Core\Services\File;

use App\Models\EntityFile;
use App\Models\EntityFileAssociation;
use Core\Services\Storage\StorageService;
use Core\Services\Storage\StorageException;

class FileService
{
    private StorageService $storage;

    /**
     * Tipos de archivo permitidos por defecto
     */
    private const DEFAULT_ALLOWED_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    /**
     * Tamaño máximo por defecto: 10MB
     */
    private const DEFAULT_MAX_SIZE = 10 * 1024 * 1024;

    public function __construct()
    {
        $this->storage = new StorageService();
    }

    /**
     * Upload un archivo y guarda metadatos en BD
     *
     * @param array $file Array de $_FILES (name, type, tmp_name, size)
     * @param string $path Ruta en MinIO (ej: 'products/images/', 'documents/pdf/')
     * @param array $options Opciones: allowedTypes, maxSize
     * @return EntityFile Modelo del archivo guardado (con ID)
     *
     * @throws FileValidationException Si la validación falla
     * @throws StorageException Si el upload a MinIO falla
     */
    public function uploadFile(array $file, string $path = 'general/', array $options = []): EntityFile
    {
        // Validar archivo
        $this->validateFile($file, $options);

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('file_', true) . '.' . $extension;

        // Detectar MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Subir a MinIO usando StorageService
        $url = $this->storage->upload($file, $filename, $path);

        // Construir la key para referencia
        $key = rtrim($path, '/') . '/' . $filename;

        // Guardar metadatos en BD
        $entityFile = EntityFile::create([
            'url' => $url,
            'key' => $key,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'mime_type' => $mimeType
        ]);

        return $entityFile;
    }

    /**
     * Elimina un archivo por ID (de MinIO y BD)
     *
     * @param int $fileId ID del archivo en BD
     * @return bool true si se eliminó correctamente
     *
     * @throws \Exception Si el archivo no existe
     */
    public function deleteFile(int $fileId): bool
    {
        $file = EntityFile::find($fileId);

        if (!$file) {
            throw new \Exception("Archivo con ID {$fileId} no encontrado");
        }

        // Eliminar de MinIO
        try {
            $this->storage->delete($file->key);
        } catch (StorageException $e) {
            // Si no existe en MinIO, continuar (puede haber sido eliminado manualmente)
            // Log el error pero no fallar
        }

        // Eliminar de BD
        return $file->delete();
    }

    /**
     * Obtiene archivos por lista de IDs
     *
     * @param array $fileIds Lista de IDs
     * @return \Illuminate\Support\Collection Colección de EntityFile
     */
    public function getFilesByIds(array $fileIds)
    {
        return EntityFile::whereIn('id', $fileIds)->get();
    }

    /**
     * Obtiene un archivo por ID
     *
     * @param int $fileId ID del archivo
     * @return EntityFile|null
     */
    public function getFileById(int $fileId): ?EntityFile
    {
        return EntityFile::find($fileId);
    }

    /**
     * Obtiene todas las imágenes (filtradas por MIME type)
     *
     * @param int $limit Cantidad máxima
     * @return \Illuminate\Support\Collection
     */
    public function getImages(int $limit = 100)
    {
        return EntityFile::images()->limit($limit)->get();
    }

    /**
     * Verifica si un archivo existe
     *
     * @param int $fileId ID del archivo
     * @return bool
     */
    public function fileExists(int $fileId): bool
    {
        return EntityFile::where('id', $fileId)->exists();
    }

    /**
     * Valida que el archivo sea válido
     *
     * @param array $file Array de $_FILES
     * @param array $options Opciones: allowedTypes, maxSize
     * @throws FileValidationException
     */
    private function validateFile(array $file, array $options = []): void
    {
        // Obtener opciones o usar defaults
        $allowedTypes = $options['allowedTypes'] ?? self::DEFAULT_ALLOWED_TYPES;
        $maxSize = $options['maxSize'] ?? self::DEFAULT_MAX_SIZE;

        // Validar estructura
        if (!isset($file['name'], $file['type'], $file['tmp_name'], $file['size'])) {
            throw new FileValidationException('Estructura de archivo inválida');
        }

        // Validar errores de upload
        if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            throw new FileValidationException('Error al cargar el archivo: ' . $this->getUploadErrorMessage($file['error']));
        }

        // Validar que el archivo temporal existe
        if (!file_exists($file['tmp_name'])) {
            throw new FileValidationException('Archivo temporal no encontrado');
        }

        // Detectar MIME type real (no confiar en el enviado por el cliente)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Validar tipo de archivo
        if (!in_array($mimeType, $allowedTypes)) {
            $allowed = implode(', ', $allowedTypes);
            throw new FileValidationException("Tipo de archivo no permitido: {$mimeType}. Permitidos: {$allowed}");
        }

        // Validar tamaño
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 2);
            $fileSizeMB = round($file['size'] / 1024 / 1024, 2);
            throw new FileValidationException("El archivo ({$fileSizeMB}MB) excede el tamaño máximo de {$maxSizeMB}MB");
        }
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

    /**
     * ========================================
     * MÉTODOS DE ASOCIACIÓN POLIMÓRFICA (Opción B)
     * ========================================
     */

    /**
     * Asocia un archivo existente a una entidad
     *
     * @param int $fileId ID del archivo en tabla 'files'
     * @param string $entityType Tipo de entidad ('Product', 'Article', etc.)
     * @param int $entityId ID de la entidad
     * @param int $displayOrder Orden de visualización
     * @param array $metadata Metadata JSONB (ej: ['is_primary' => true])
     * @return EntityFileAssociation
     */
    public function associateFileToEntity(
        int $fileId,
        string $entityType,
        int $entityId,
        int $displayOrder = 0,
        array $metadata = []
    ): EntityFileAssociation {
        return EntityFileAssociation::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'file_id' => $fileId,
            'display_order' => $displayOrder,
            'metadata' => $metadata
        ]);
    }

    /**
     * Sube un archivo Y lo asocia a una entidad en una sola operación
     *
     * @param array $file Array de $_FILES
     * @param string $entityType Tipo de entidad
     * @param int $entityId ID de la entidad
     * @param string $path Ruta en MinIO
     * @param array $metadata Metadata JSONB
     * @param array $uploadOptions Opciones de upload (allowedTypes, maxSize)
     * @return EntityFileAssociation
     */
    public function uploadAndAssociateFile(
        array $file,
        string $entityType,
        int $entityId,
        string $path = 'general/',
        array $metadata = [],
        array $uploadOptions = []
    ): EntityFileAssociation {
        // Upload file to MinIO and save to 'files' table
        $entityFile = $this->uploadFile($file, $path, $uploadOptions);

        // Get next display order
        $maxOrder = EntityFileAssociation::forEntity($entityType, $entityId)->max('display_order');
        $displayOrder = ($maxOrder ?? -1) + 1;

        // Associate to entity
        return $this->associateFileToEntity(
            $entityFile->id,
            $entityType,
            $entityId,
            $displayOrder,
            $metadata
        );
    }

    /**
     * Obtiene todos los archivos asociados a una entidad (con relación eager)
     *
     * @param string $entityType Tipo de entidad
     * @param int $entityId ID de la entidad
     * @return \Illuminate\Support\Collection Colección de EntityFileAssociation con 'file' cargado
     */
    public function getEntityFiles(string $entityType, int $entityId)
    {
        return EntityFileAssociation::forEntity($entityType, $entityId)
            ->ordered()
            ->with('file')
            ->get();
    }

    /**
     * Disocia un archivo de una entidad (elimina la asociación, NO el archivo)
     *
     * @param int $fileId ID del archivo
     * @param string $entityType Tipo de entidad
     * @param int $entityId ID de la entidad
     * @return bool
     */
    public function dissociateFileFromEntity(int $fileId, string $entityType, int $entityId): bool
    {
        return EntityFileAssociation::forEntity($entityType, $entityId)
            ->where('file_id', $fileId)
            ->delete();
    }

    /**
     * Elimina un archivo Y todas sus asociaciones (de MinIO, BD, y entity_files)
     *
     * @param int $fileId ID del archivo
     * @return bool
     */
    public function deleteFileAndAssociations(int $fileId): bool
    {
        // Las asociaciones se eliminan automáticamente por CASCADE en FK
        return $this->deleteFile($fileId);
    }

    /**
     * Actualiza el orden de display de archivos de una entidad
     *
     * @param string $entityType Tipo de entidad
     * @param int $entityId ID de la entidad
     * @param array $fileIdsInOrder Lista de file_ids en el orden deseado
     * @return void
     */
    public function reorderEntityFiles(string $entityType, int $entityId, array $fileIdsInOrder): void
    {
        foreach ($fileIdsInOrder as $order => $fileId) {
            EntityFileAssociation::forEntity($entityType, $entityId)
                ->where('file_id', $fileId)
                ->update(['display_order' => $order]);
        }
    }
}

/**
 * Excepción para errores de validación de archivos
 */
class FileValidationException extends \Exception
{
}
