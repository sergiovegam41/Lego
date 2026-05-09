---
tipo: class
capa: core-services
namespace: Core\Services\Storage
archivo: Core/Services/Storage/StorageConfig.php
loc: 157
deps: 0
dependents: 3
responsabilidad: Define y encapsula la configuración de almacenamiento para MinIO/S3, proporcionando acceso centralizado a parámetros como host, credenciales y restricciones de archivos.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-services
---
# StorageConfig

`Core\Services\Storage\StorageConfig`

📁 [Core/Services/Storage/StorageConfig.php](../../../Core/Services/Storage/StorageConfig.php)

> [!abstract] Responsabilidad
> Define y encapsula la configuración de almacenamiento para MinIO/S3, proporcionando acceso centralizado a parámetros como host, credenciales y restricciones de archivos.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `StorageConfig` existe para centralizar y encapsular toda la configuración relacionada con el almacenamiento de archivos en MinIO/S3. Este abstracción es crucial porque permite que los parámetros del almacenamiento, como credenciales, endpoints y restricciones de archivos, sean gestionados de manera uniforme y segura. Además, al leer estos valores desde variables de entorno, facilita la configuración y el despliegue en diferentes entornos (desarrollo, pruebas, producción) sin necesidad de modificar el código fuente.
> 
> ### Métodos principales
> 
> 1. **`getEndpoint()`**: Retorna el endpoint completo del servicio MinIO/S3 utilizando el protocolo HTTP o HTTPS según la configuración de SSL.
> 2. **`getPublicUrl(string $path)`**: Genera una URL pública completa para un archivo específico, combinando el endpoint público con el nombre del bucket y la ruta del archivo.
> 3. **`isExtensionAllowed(string $extension)`**: Valida si una extensión de archivo está permitida según la configuración establecida.
> 4. **`toArray()`**: Convierte toda la configuración en un array, lo cual es útil para fines de depuración y registro.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class StorageConfig {
>         +getEndpoint() : string
>         +getPublicUrl(string $path) : string
>         +isExtensionAllowed(string $extension) : bool
>         +toArray() : array
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `StorageConfig` se utiliza por el `StorageService`, que es responsable de las operaciones con el almacenamiento. Al encapsular toda la configuración necesaria para interactuar con MinIO/S3, `StorageConfig` proporciona una interfaz clara y segura para acceder a los parámetros de almacenamiento, lo que facilita el mantenimiento y la escalabilidad del sistema.

## 👥 Es referenciado por

- [[minio-client|MinioClient]] *(type_hint)*
- [[storage-service|StorageService]] *(instantiates, returns)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.