---
tipo: class
capa: core-services
namespace: Core\Services\Storage
archivo: Core/Services/Storage/StorageService.php
loc: 318
deps: 4
dependents: 5
responsabilidad: Proporciona una API simplificada para el sistema de almacenamiento, encapsulando operaciones CRUD y validaciones de archivos utilizando MinIO.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-services
---
# StorageService

`Core\Services\Storage\StorageService`

📁 [Core/Services/Storage/StorageService.php](../../../Core/Services/Storage/StorageService.php)

> [!abstract] Responsabilidad
> Proporciona una API simplificada para el sistema de almacenamiento, encapsulando operaciones CRUD y validaciones de archivos utilizando MinIO.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `StorageService` existe para proporcionar una interfaz simplificada y coherente para manejar operaciones de almacenamiento en el framework Lego, utilizando MinIO como proveedor de servicios de almacenamiento. Este servicio encapsula las complejidades del sistema de archivos subyacente, ofreciendo métodos CRUD (Crear, Leer, Actualizar, Borrar) y validaciones de archivos de manera centralizada. La creación de esta abstracción permite a otros componentes del sistema interactuar con el almacenamiento de una manera más sencilla y segura, reduciendo la duplicación de código y mejorando la mantenibilidad.
> 
> ### Métodos principales
> 
> 1. **`upload(array $file, ?string $customName = null, string $path = 'temp/')`:** Este método es el punto de entrada principal para subir archivos al almacenamiento. Se encarga de validar el archivo, sanitizar su nombre, generar un nombre único si es necesario y finalmente subirlo a MinIO. Retorna la URL pública del archivo subido.
> 
> 2. **`get(string $filePath): array`:** Permite obtener información detallada sobre un archivo específico en el almacenamiento. Utiliza el método `getObjectInfo` del cliente de MinIO para recuperar los detalles del archivo.
> 
> 3. **`delete(string $filePath): bool`:** Elimina un archivo del almacenamiento. Retorna `true` si la operación fue exitosa, y `false` en caso contrario.
> 
> 4. **`list(string $path = '', int $limit = 100): array`:** Lista los archivos contenidos en una carpeta específica dentro del bucket de MinIO. Puede limitar el número de resultados devueltos para optimizar el rendimiento.
> 
> 5. **`exists(string $filePath): bool`:** Verifica si un archivo existe en el almacenamiento. Retorna `true` si el archivo está presente, y `false` en caso contrario.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class StorageService {
>         +upload(array $file, ?string $customName = null, string $path = 'temp/') : string
>         +get(string $filePath) : array
>         +delete(string $filePath) : bool
>         +list(string $path = '', int $limit = 100) : array
>         +exists(string $filePath) : bool
>     }
>     class InitStorageCommand {
>         +execute()
>     }
>     class StorageCheckCommand {
>         +check()
>     }
>     class FileService {
>         +handleFileUpload()
>     }
>     class ExampleCrudController {
>         +uploadAction()
>     }
>     class StorageProvider {
>         +provideStorage()
>     }
>     
>     InitStorageCommand --> StorageService
>     StorageCheckCommand --> StorageService
>     FileService --> StorageService
>     ExampleCrudController --> StorageService
>     StorageProvider --> StorageService
> ```
> 
> ### Cómo encaja
> 
> La clase `StorageService` se integra como un componente central del sistema de almacenamiento en el framework Lego. Es utilizada por varias clases y comandos principales, como `InitStorageCommand`, `StorageCheckCommand`, `FileService`, `ExampleCrudController` y `StorageProvider`. Estas entidades dependen de `StorageService` para realizar operaciones de almacenamiento, lo que facilita la gestión de archivos en diferentes partes del sistema. La abstracción proporcionada por `StorageService` permite a estos componentes interactuar con el almacenamiento de una manera uniforme y simplificada, asegurando consistencia y reduciendo la complejidad de las operaciones de archivo.

## 🆕 Instancia

- [[minio-client|MinioClient]]
- [[storage-config|StorageConfig]]

## ⚡ Llamadas estáticas

- [[storage-exception|StorageException]]

## 📤 Tipos de retorno

- [[storage-config|StorageConfig]]

## 👥 Es referenciado por

- [[example-crud-controller|ExampleCrudController]] *(instantiates)*
- [[file-service|FileService]] *(instantiates)*
- [[init-storage-command|InitStorageCommand]] *(instantiates)*
- [[storage-check-command|StorageCheckCommand]] *(instantiates)*
- [[storage-provider|StorageProvider]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.