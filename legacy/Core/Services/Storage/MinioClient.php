<?php
/**
 * MinioClient - Cliente para interactuar con MinIO usando AWS SDK
 *
 * PROPÓSITO:
 * Wrapper del SDK de AWS S3 para MinIO. Proporciona métodos simplificados
 * para operaciones comunes de almacenamiento.
 *
 * FILOSOFÍA LEGO:
 * Abstracción limpia sobre AWS SDK. API simple y predecible.
 *
 * COMPATIBILIDAD:
 * Este cliente funciona con MinIO local Y con AWS S3 en producción.
 * Solo cambia la configuración en .env
 */

namespace Core\Services\Storage;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\AwsException;

class MinioClient
{
    private S3Client $client;
    private StorageConfig $config;

    public function __construct(StorageConfig $config)
    {
        $this->config = $config;
        $this->initializeClient();
    }

    /**
     * Inicializa el cliente S3 compatible con MinIO
     */
    private function initializeClient(): void
    {
        try {
            $this->client = new S3Client([
                'version' => 'latest',
                'region' => $this->config->getRegion(),
                'endpoint' => $this->config->getInternalEndpoint(),
                'use_path_style_endpoint' => true, // Necesario para MinIO
                'credentials' => [
                    'key' => $this->config->getAccessKey(),
                    'secret' => $this->config->getSecretKey(),
                ],
            ]);
        } catch (AwsException $e) {
            throw StorageException::connectionFailed($e->getMessage());
        }
    }

    /**
     * Verifica si el cliente puede conectarse con MinIO
     */
    public function isConnected(): bool
    {
        try {
            $this->client->listBuckets();
            return true;
        } catch (S3Exception $e) {
            return false;
        }
    }

    /**
     * Verifica si un bucket existe
     */
    public function bucketExists(string $bucket): bool
    {
        try {
            return $this->client->doesBucketExist($bucket);
        } catch (S3Exception $e) {
            return false;
        }
    }

    /**
     * Crea un nuevo bucket
     */
    public function createBucket(string $bucket): bool
    {
        try {
            $this->client->createBucket([
                'Bucket' => $bucket,
            ]);
            return true;
        } catch (S3Exception $e) {
            throw StorageException::bucketCreationFailed($bucket, $e->getMessage());
        }
    }

    /**
     * Configura un bucket como público (accesible sin autenticación)
     */
    public function setBucketPublic(string $bucket): bool
    {
        try {
            $policy = [
                'Version' => '2012-10-17',
                'Statement' => [
                    [
                        'Effect' => 'Allow',
                        'Principal' => ['AWS' => ['*']],
                        'Action' => ['s3:GetObject'],
                        'Resource' => ["arn:aws:s3:::{$bucket}/*"],
                    ],
                ],
            ];

            $this->client->putBucketPolicy([
                'Bucket' => $bucket,
                'Policy' => json_encode($policy),
            ]);

            return true;
        } catch (S3Exception $e) {
            throw StorageException::policyFailed($e->getMessage());
        }
    }

    /**
     * Sube un archivo al bucket
     *
     * @param string $bucket Nombre del bucket
     * @param string $key Ruta/nombre del archivo en el bucket (ej: "products/images/foto.jpg")
     * @param mixed $body Contenido del archivo (stream, string, o resource)
     * @param string $contentType MIME type del archivo
     * @return array Resultado con información del archivo subido
     */
    public function putObject(string $bucket, string $key, $body, string $contentType = 'application/octet-stream'): array
    {
        try {
            $result = $this->client->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'Body' => $body,
                'ContentType' => $contentType,
                'ACL' => 'public-read', // Hacer archivo público por defecto
            ]);

            return [
                'success' => true,
                'key' => $key,
                'etag' => $result['ETag'] ?? null,
                'url' => $this->config->getPublicUrl($key),
            ];
        } catch (S3Exception $e) {
            throw StorageException::uploadFailed($e->getMessage());
        }
    }

    /**
     * Obtiene información de un archivo
     */
    public function getObjectInfo(string $bucket, string $key): array
    {
        try {
            $result = $this->client->headObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);

            return [
                'exists' => true,
                'size' => $result['ContentLength'] ?? 0,
                'contentType' => $result['ContentType'] ?? 'unknown',
                'lastModified' => $result['LastModified'] ?? null,
                'etag' => $result['ETag'] ?? null,
                'url' => $this->config->getPublicUrl($key),
            ];
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 404) {
                throw StorageException::fileNotFound($key);
            }
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * Descarga un archivo
     */
    public function getObject(string $bucket, string $key): string
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);

            return $result['Body']->getContents();
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 404) {
                throw StorageException::fileNotFound($key);
            }
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * Elimina un archivo
     */
    public function deleteObject(string $bucket, string $key): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);
            return true;
        } catch (S3Exception $e) {
            throw StorageException::deleteFailed($key);
        }
    }

    /**
     * Lista archivos en un bucket con un prefijo específico
     *
     * @param string $bucket Nombre del bucket
     * @param string $prefix Prefijo/carpeta (ej: "products/images/")
     * @param int $maxKeys Cantidad máxima de archivos a listar
     * @return array Lista de archivos
     */
    public function listObjects(string $bucket, string $prefix = '', int $maxKeys = 1000): array
    {
        try {
            $params = [
                'Bucket' => $bucket,
                'MaxKeys' => $maxKeys,
            ];

            if ($prefix) {
                $params['Prefix'] = ltrim($prefix, '/');
            }

            $result = $this->client->listObjectsV2($params);

            $files = [];
            if (isset($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $files[] = [
                        'key' => $object['Key'],
                        'size' => $object['Size'],
                        'lastModified' => $object['LastModified']->format('Y-m-d H:i:s'),
                        'etag' => trim($object['ETag'], '"'),
                        'url' => $this->config->getPublicUrl($object['Key']),
                    ];
                }
            }

            return [
                'files' => $files,
                'count' => count($files),
                'truncated' => $result['IsTruncated'] ?? false,
            ];
        } catch (S3Exception $e) {
            throw new StorageException("Error al listar archivos: " . $e->getMessage());
        }
    }

    /**
     * Verifica si un archivo existe
     */
    public function objectExists(string $bucket, string $key): bool
    {
        try {
            return $this->client->doesObjectExist($bucket, $key);
        } catch (S3Exception $e) {
            return false;
        }
    }

    /**
     * Copia un archivo dentro del mismo bucket o entre buckets
     */
    public function copyObject(string $sourceBucket, string $sourceKey, string $destBucket, string $destKey): bool
    {
        try {
            $this->client->copyObject([
                'Bucket' => $destBucket,
                'Key' => $destKey,
                'CopySource' => "{$sourceBucket}/{$sourceKey}",
                'ACL' => 'public-read',
            ]);
            return true;
        } catch (S3Exception $e) {
            throw new StorageException("Error al copiar archivo: " . $e->getMessage());
        }
    }

    /**
     * Obtiene estadísticas del bucket
     */
    public function getBucketStats(string $bucket): array
    {
        try {
            $result = $this->listObjects($bucket, '', 10000); // Listar hasta 10k archivos

            $totalSize = 0;
            $fileTypes = [];

            foreach ($result['files'] as $file) {
                $totalSize += $file['size'];

                // Contar por tipo de archivo
                $extension = pathinfo($file['key'], PATHINFO_EXTENSION);
                $fileTypes[$extension] = ($fileTypes[$extension] ?? 0) + 1;
            }

            return [
                'totalFiles' => $result['count'],
                'totalSize' => $totalSize,
                'totalSizeMB' => round($totalSize / 1024 / 1024, 2),
                'fileTypes' => $fileTypes,
            ];
        } catch (S3Exception $e) {
            throw new StorageException("Error al obtener estadísticas: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el cliente S3 nativo (para operaciones avanzadas)
     */
    public function getNativeClient(): S3Client
    {
        return $this->client;
    }
}
