---
tipo: class
capa: core-services
namespace: Core\Services\File
archivo: Core/Services/File/FileService.php
loc: 375
deps: 0
dependents: 1
responsabilidad: Gestiona la subida, eliminación y obtención de archivos, validando tipos y tamaños, y asocia archivos a entidades de manera polimórfica.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-services
---
# FileValidationException

`Core\Services\File\FileValidationException`

📁 [Core/Services/File/FileService.php](../../../Core/Services/File/FileService.php)

> [!abstract] Responsabilidad
> Gestiona la subida, eliminación y obtención de archivos, validando tipos y tamaños, y asocia archivos a entidades de manera polimórfica.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `FileService` existe para abstraer y centralizar la gestión de archivos en el sistema, proporcionando una interfaz uniforme para subir, eliminar y obtener archivos. Este servicio se creó para resolver varios problemas:
> 
> 1. **Separación de responsabilidades**: Evitar que cada controlador o entidad tenga su propia lógica de manejo de archivos, lo cual dificulta la mantenibilidad y el testing.
> 2. **Validación centralizada**: Centralizar las reglas de validación de archivos (como tipos permitidos y tamaños máximos) para evitar duplicación de código.
> 3. **Polimorfismo en asociaciones**: Permitir que cualquier entidad del sistema pueda asociarse con archivos sin necesidad de reimplementar la lógica de manejo de archivos.
> 
> ### Métodos principales
> 
> 1. **uploadFile()**
>    - Sube un archivo a MinIO y guarda sus metadatos en la base de datos.
>    - Valida el tipo y tamaño del archivo antes de subirlo.
>    - Retorna una instancia de `EntityFile` con el ID asignado.
> 
> 2. **deleteFile()**
>    - Elimina un archivo tanto de MinIO como de la base de datos.
>    - Maneja excepciones si el archivo no existe en MinIO.
> 
> 3. **getFilesByIds()**
>    - Obtiene una colección de archivos por sus IDs.
>    - Utilizado para recuperar múltiples archivos asociados a una entidad.
> 
> 4. **uploadAndAssociateFile()**
>    - Combina la subida de un archivo y su asociación con una entidad en una sola operación.
>    - Determina automáticamente el orden de visualización basado en las existentes asociaciones.
> 
> 5. **getEntityFiles()**
>    - Obtiene todos los archivos asociados a una entidad, incluyendo sus metadatos.
>    - Utiliza relaciones Eloquent para cargar los archivos de manera eficiente.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class FileService {
>         +uploadFile(array $file, string $path, array $options): EntityFile
>         +deleteFile(int $fileId): bool
>         +getFilesByIds(array $fileIds)
>         +uploadAndAssociateFile(array $file, string $entityType, int $entityId, string $path, array $metadata, array $uploadOptions): EntityFileAssociation
>         +getEntityFiles(string $entityType, int $entityId)
>     }
>     class StorageService {
>         +upload(array $file, string $filename, string $path): string
>         +delete(string $key): void
>     }
>     class EntityFile {
>         +id
>         +url
>         +key
>         +original_name
>         +size
>         +mime_type
>     }
>     class EntityFileAssociation {
>         +entity_type
>         +entity_id
>         +file_id
>         +display_order
>         +metadata
>     }
>     FileService --> StorageService: usa
>     FileService --> EntityFile: crea/guarda/consulta
>     FileService --> EntityFileAssociation: crea/guarda/consulta
> ```
> 
> ### Cómo encaja
> 
> La clase `FileService` se integra como un servicio centralizado que es utilizado por varios controladores (`ExampleCrudController`, `FilesController`, `ToolsController`) para manejar la subida, eliminación y obtención de archivos. Este diseño permite una gestión uniforme de los archivos en todo el sistema, facilitando la extensión y mantenimiento futuro. La clase `StorageService` se utiliza internamente por `FileService` para realizar las operaciones de almacenamiento en MinIO, mientras que `EntityFile` y `EntityFileAssociation` son modelos Eloquent que representan las entidades de archivo y sus asociaciones respectivamente.

## 👥 Es referenciado por

- [[file-service|FileService]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.