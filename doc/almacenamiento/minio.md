# MinIO / S3

MinIO es el servicio de almacenamiento de objetos de Lego. Es compatible con la API de Amazon S3, lo que permite usar el mismo código para desarrollo local y producción en AWS.

Relacionado: [[almacenamiento/archivos]] · [[infraestructura/docker]]

---

## ¿Qué es MinIO?

Un servidor de almacenamiento de objetos de código abierto, compatible con S3. En desarrollo corre como contenedor Docker. En producción puede reemplazarse por AWS S3, Google Cloud Storage, o cualquier servicio compatible con S3 sin cambiar el código.

## Configuración

Variables en `.env`:

```
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=lego
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

En producción con S3 real:

```
AWS_ENDPOINT=          # vacío → usa el endpoint de AWS por defecto
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## Docker

```yaml
# docker-compose.yml
minio:
    image: minio/minio
    ports:
        - "9000:9000"   # API S3
        - "9001:9001"   # Consola web
    command: server /data --console-address ":9001"
```

Consola web en desarrollo: `http://localhost:9001`

## SDK

Lego usa el AWS SDK para PHP (`aws/aws-sdk-php`):

```php
use Aws\S3\S3Client;

$s3 = new S3Client([
    'version'  => 'latest',
    'region'   => env('AWS_DEFAULT_REGION'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],
]);
```

## Operaciones Comunes

```php
// Subir archivo
$s3->putObject([
    'Bucket' => env('AWS_BUCKET'),
    'Key'    => 'uploads/imagen.jpg',
    'Body'   => fopen($rutaLocal, 'r'),
    'ACL'    => 'public-read',
]);

// Obtener URL pública
$url = $s3->getObjectUrl(env('AWS_BUCKET'), 'uploads/imagen.jpg');

// Eliminar
$s3->deleteObject([
    'Bucket' => env('AWS_BUCKET'),
    'Key'    => 'uploads/imagen.jpg',
]);
```

## Buckets

| Bucket | Contenido |
|--------|-----------|
| `lego` | Bucket principal: imágenes, documentos, archivos de usuario |

## Visión

> MinIO tendrá un servicio de transformación de imágenes integrado: al subir una imagen, Lego genera automáticamente las variantes necesarias (thumbnail, medium, large) y las almacena con el mismo nombre base. Los componentes pueden solicitar la variante adecuada sin lógica adicional.
