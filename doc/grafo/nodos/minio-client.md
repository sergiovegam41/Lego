---
tipo: class
capa: core-services
namespace: Core\Services\Storage
archivo: Core/Services/Storage/MinioClient.php
loc: 330
deps: 3
dependents: 1
responsabilidad: Proporciona un wrapper simplificado del SDK de AWS S3 para interactuar con MinIO, encapsulando operaciones comunes de almacenamiento como subir, descargar y listar archivos.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-services
---
# MinioClient

`Core\Services\Storage\MinioClient`

📁 [Core/Services/Storage/MinioClient.php](../../../Core/Services/Storage/MinioClient.php)

> [!abstract] Responsabilidad
> Proporciona un wrapper simplificado del SDK de AWS S3 para interactuar con MinIO, encapsulando operaciones comunes de almacenamiento como subir, descargar y listar archivos.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MinioClient` existe para encapsular y simplificar las operaciones de almacenamiento en MinIO utilizando el SDK de AWS S3. Este abstracción es necesaria porque MinIO, aunque compatible con la API de S3, requiere configuración específica (como `use_path_style_endpoint`) que no está presente en un cliente genérico de S3. Además, proporciona una interfaz más amigable y predecible para las operaciones comunes de almacenamiento, como crear buckets, subir archivos, listar objetos y gestionar políticas de acceso.
> 
> ### Métodos principales
> 
> 1. **`initializeClient()`**: Configura el cliente S3 compatible con MinIO utilizando la configuración proporcionada por `StorageConfig`. Maneja posibles errores al establecer la conexión.
> 2. **`isConnected()`**: Verifica si el cliente puede conectarse con MinIO listando los buckets existentes.
> 3. **`putObject(string $bucket, string $key, $body, string $contentType)`**: Sube un archivo al bucket especificado y devuelve información sobre el archivo subido.
> 4. **`getObjectInfo(string $bucket, string $key)`**: Obtiene información detallada de un archivo en el bucket, incluyendo tamaño, tipo MIME y URL pública.
> 5. **`listObjects(string $bucket, string $prefix = '', int $maxKeys = 1000)`**: Lista archivos en un bucket con un prefijo específico, limitando la cantidad máxima de archivos a listar.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class MinioClient {
>         +__construct(StorageConfig $config)
>         +initializeClient(): void
>         +isConnected(): bool
>         +bucketExists(string $bucket): bool
>         +createBucket(string $bucket): bool
>         +setBucketPublic(string $bucket): bool
>         +putObject(string $bucket, string $key, $body, string $contentType): array
>         +getObjectInfo(string $bucket, string $key): array
>         +getObject(string $bucket, string $key): string
>         +deleteObject(string $bucket, string $key): bool
>         +listObjects(string $bucket, string $prefix = '', int $maxKeys = 1000): array
>         +objectExists(string $bucket, string $key): bool
>         +copyObject(string $sourceBucket, string $sourceKey, string $destBucket, string $destKey): bool
>         +getBucketStats(string $bucket): array
>         +getNativeClient(): S3Client
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `MinioClient` se conecta con el resto del sistema a través de la instancia que es creada por `StorageService`. Esta clase proporciona una interfaz simplificada y robusta para interactuar con MinIO, lo que facilita su uso en diferentes partes del códigobase sin tener que lidiar directamente con los detalles de configuración y manejo de excepciones del SDK de AWS S3.

## 🆕 Instancia

- [[storage-exception|StorageException]]

## ⚡ Llamadas estáticas

- [[storage-exception|StorageException]]

## 📥 Type hints (parámetros)

- [[storage-config|StorageConfig]]

## 👥 Es referenciado por

- [[storage-service|StorageService]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.