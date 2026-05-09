---
tipo: class
capa: core-services
namespace: Core\Services\Storage
archivo: Core/Services/Storage/StorageException.php
loc: 96
deps: 0
dependents: 4
responsabilidad: Define y encapsula excepciones personalizadas para errores específicos del sistema de almacenamiento MinIO/S3, permitiendo un manejo granular de problemas como conexiones fallidas, archivos no encontrados o subidas erróneas.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-services
---
# StorageException

`Core\Services\Storage\StorageException`

📁 [Core/Services/Storage/StorageException.php](../../../Core/Services/Storage/StorageException.php)

> [!abstract] Responsabilidad
> Define y encapsula excepciones personalizadas para errores específicos del sistema de almacenamiento MinIO/S3, permitiendo un manejo granular de problemas como conexiones fallidas, archivos no encontrados o subidas erróneas.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `StorageException` existe para manejar errores específicos relacionados con el sistema de almacenamiento MinIO/S3 de forma granular y clara. En lugar de usar excepciones genéricas, esta clase proporciona códigos de error personalizados que facilitan la identificación y depuración de problemas relacionados con el almacenamiento. Esto es crucial para garantizar que los errores se manejen adecuadamente y que se puedan registrar de manera precisa.
> 
> ### Métodos principales
> 
> 1. **connectionFailed**: Crea una excepción cuando no se puede conectar con MinIO, indicando un fallo en la conexión.
> 2. **bucketNotFound**: Lanza una excepción cuando un bucket específico no es encontrado en el almacenamiento.
> 3. **fileNotFound**: Genera una excepción cuando un archivo específico no existe en el bucket.
> 4. **uploadFailed**: Crea una excepción para errores que ocurren durante la subida de archivos, con una opción de agregar una razón específica.
> 5. **deleteFailed**: Lanza una excepción cuando se produce un error al eliminar un archivo.
> 6. **invalidFile**: Genera una excepción cuando un archivo no cumple con las validaciones necesarias.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class StorageException {
>         <<exception>>
>         +connectionFailed() : self
>         +bucketNotFound(string $bucket) : self
>         +fileNotFound(string $file) : self
>         +uploadFailed(string $reason = '') : self
>         +deleteFailed(string $file) : self
>         +invalidFile(string $reason) : self
>         +fileTooLarge(int $size, int $maxSize) : self
>         +invalidExtension(string $extension, array $allowed) : self
>         +bucketCreationFailed(string $bucket, string $reason = '') : self
>         +policyFailed(string $reason) : self
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `StorageException` se utiliza principalmente por el `MinioClient`, que es el componente responsable de interactuar con el sistema de almacenamiento MinIO/S3. Al proporcionar excepciones personalizadas, esta clase facilita la gestión de errores específicos del almacenamiento, permitiendo al `MinioClient` capturar y manejar estos errores de manera adecuada. Esto asegura que los problemas relacionados con el almacenamiento se puedan identificar y resolver de forma eficiente, mejorando la robustez y confiabilidad del sistema.

## 👥 Es referenciado por

- [[minio-client|MinioClient]] *(instantiates, static_call)*
- [[storage-controller|StorageController]] *(const_fetch)*
- [[storage-service|StorageService]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.