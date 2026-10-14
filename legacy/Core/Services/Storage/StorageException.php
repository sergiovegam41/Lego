<?php
/**
 * StorageException - Excepciones personalizadas para el sistema de storage
 *
 * PROPÓSITO:
 * Manejo de errores específicos del sistema de almacenamiento MinIO/S3.
 * Permite capturar y manejar errores de forma granular.
 *
 * FILOSOFÍA LEGO:
 * Excepciones claras y específicas para debugging y logging.
 */

namespace Core\Services\Storage;

use Exception;

class StorageException extends Exception
{
    /**
     * Códigos de error personalizados
     */
    const CONNECTION_FAILED = 1001;
    const BUCKET_NOT_FOUND = 1002;
    const FILE_NOT_FOUND = 1003;
    const UPLOAD_FAILED = 1004;
    const DELETE_FAILED = 1005;
    const INVALID_FILE = 1006;
    const FILE_TOO_LARGE = 1007;
    const INVALID_EXTENSION = 1008;
    const BUCKET_CREATION_FAILED = 1009;
    const POLICY_FAILED = 1010;

    /**
     * Crea una excepción con código personalizado
     */
    public static function connectionFailed(string $message = 'No se pudo conectar con MinIO'): self
    {
        return new self($message, self::CONNECTION_FAILED);
    }

    public static function bucketNotFound(string $bucket): self
    {
        return new self("Bucket '{$bucket}' no encontrado", self::BUCKET_NOT_FOUND);
    }

    public static function fileNotFound(string $file): self
    {
        return new self("Archivo '{$file}' no encontrado", self::FILE_NOT_FOUND);
    }

    public static function uploadFailed(string $reason = ''): self
    {
        $message = 'Error al subir archivo';
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message, self::UPLOAD_FAILED);
    }

    public static function deleteFailed(string $file): self
    {
        return new self("Error al eliminar archivo '{$file}'", self::DELETE_FAILED);
    }

    public static function invalidFile(string $reason): self
    {
        return new self("Archivo inválido: {$reason}", self::INVALID_FILE);
    }

    public static function fileTooLarge(int $size, int $maxSize): self
    {
        $sizeMB = round($size / 1024 / 1024, 2);
        $maxSizeMB = round($maxSize / 1024 / 1024, 2);
        return new self("Archivo muy grande ({$sizeMB}MB). Máximo permitido: {$maxSizeMB}MB", self::FILE_TOO_LARGE);
    }

    public static function invalidExtension(string $extension, array $allowed): self
    {
        $allowedStr = implode(', ', $allowed);
        return new self("Extensión '{$extension}' no permitida. Permitidas: {$allowedStr}", self::INVALID_EXTENSION);
    }

    public static function bucketCreationFailed(string $bucket, string $reason = ''): self
    {
        $message = "Error al crear bucket '{$bucket}'";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message, self::BUCKET_CREATION_FAILED);
    }

    public static function policyFailed(string $reason): self
    {
        return new self("Error al configurar política: {$reason}", self::POLICY_FAILED);
    }
}
