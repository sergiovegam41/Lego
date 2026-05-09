---
tipo: controller
capa: app-controllers
namespace: App\Controllers\ExampleCrud\Controllers
archivo: App/Controllers/ExampleCrud/Controllers/ExampleCrudController.php
loc: 618
deps: 10
dependents: 0
responsabilidad: Orquesta operaciones CRUD para la entidad ExampleCrud, incluyendo gestión de imágenes y validación de datos, encapsulando las respuestas HTTP con ResponseDTO.
atributos:
  - ApiRoutes
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
  - grafo/atributo/ApiRoutes
---
# ExampleCrudController

`App\Controllers\ExampleCrud\Controllers\ExampleCrudController`

📁 [App/Controllers/ExampleCrud/Controllers/ExampleCrudController.php](../../../App/Controllers/ExampleCrud/Controllers/ExampleCrudController.php)

> [!abstract] Responsabilidad
> Orquesta operaciones CRUD para la entidad ExampleCrud, incluyendo gestión de imágenes y validación de datos, encapsulando las respuestas HTTP con ResponseDTO.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `ExampleCrudController` es un controlador específico diseñado para manejar operaciones CRUD (Create, Read, Update, Delete) relacionadas con la entidad `ExampleCrud`. Su principal objetivo es proporcionar una implementación completa y referencial de cómo gestionar recursos en el framework Lego. Este controlador también se encarga de la gestión de imágenes asociadas a los registros de `ExampleCrud`, lo que incluye subir, eliminar, reordenar e identificar imágenes principales.
> 
> ### Métodos principales
> 
> 1. **list()**: Lista todos los registros de `ExampleCrud` ordenados por fecha de creación descendente. Retorna un array con los datos de los registros.
> 2. **get()**: Obtiene un registro específico por su ID, incluyendo sus imágenes asociadas. Utiliza `FileService` para obtener y formatear la información de las imágenes.
> 3. **create()**: Crea un nuevo registro de `ExampleCrud`, validando que el nombre sea requerido. También permite asociar imágenes al nuevo registro usando `FileService`.
> 4. **update()**: Actualiza un registro existente, permitiendo modificar todos sus campos. Maneja la actualización de las imágenes asociadas, eliminando y creando nuevas asociaciones según los datos proporcionados.
> 5. **delete()**: Elimina un registro específico por su ID, retornando una confirmación de que el registro fue eliminado correctamente.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant Controller as ExampleCrudController
>     participant Model as ExampleCrud
>     participant FileService
>     participant DB as PostgreSQL
>     Client->>Controller: GET /api/example-crud/list
>     Controller->>Model: fetch all records
>     Model-->>DB: SELECT * FROM example_crud ORDER BY created_at DESC
>     DB-->>Model: records
>     Model-->>Controller: records array
>     Controller-->>Client: 200 JSON
> 
>     Client->>Controller: GET /api/example-crud/get?id=1
>     Controller->>Model: fetch record by ID
>     Model-->>DB: SELECT * FROM example_crud WHERE id = 1
>     DB-->>Model: record
>     Model-->>Controller: record object
>     Controller->>FileService: getEntityFiles('ExampleCrud', 1)
>     FileService-->>DB: SELECT * FROM entity_files WHERE entity_type = 'ExampleCrud' AND entity_id = 1
>     DB-->>FileService: file associations
>     FileService-->>Controller: formatted images array
>     Controller-->>Client: 200 JSON
> 
>     Client->>Controller: POST /api/example-crud/create
>     Controller->>Model: create new record
>     Model-->>DB: INSERT INTO example_crud (name, description, price, stock, min_stock, category, image_url, is_active)
>     DB-->>Model: new record ID
>     Model-->>Controller: new record object
>     Controller->>FileService: associateFileToEntity(imageId, 'ExampleCrud', recordId, index, options)
>     FileService-->>DB: INSERT INTO entity_files_associations (file_id, entity_type, entity_id, display_order, is_primary)
>     DB-->>FileService: success
>     FileService-->>Controller: success
>     Controller-->>Client: 201 JSON
> 
>     Client->>Controller: POST /api/example-crud/update
>     Controller->>Model: update record by ID
>     Model-->>DB: UPDATE example_crud SET name = ?, description = ?, price = ?, stock = ?, min_stock = ?, category = ?, image_url = ?, is_active = ? WHERE id = ?
>     DB-->>Model: success
>     Model-->>Controller: updated record object
>     Controller->>FileService: associateFileToEntity(imageId, 'ExampleCrud', recordId, index, options)
>     FileService-->>DB: DELETE FROM entity_files_associations WHERE entity_type = 'ExampleCrud' AND entity_id = ?
>     DB-->>FileService: success
>     FileService-->>Controller: success
>     Controller-->>Client: 200 JSON
> 
>     Client->>Controller: POST /api/example-crud/delete
>     Controller->>Model: delete record by ID
>     Model-->>DB: DELETE FROM example_crud WHERE id = ?
>     DB-->>Model: success
>     Model-->>Controller: success
>     Controller-->>Client: 200 JSON
> ```
> 
> ### Cómo encaja
> 
> `ExampleCrudController` se integra como parte del módulo de gestión de recursos en el framework Lego. Hereda de `CoreController`, lo que le proporciona una estructura base y métodos comunes para manejar solicitudes HTTP. Utiliza `FileService` para gestionar las imágenes asociadas a los registros, lo que permite una separación clara entre la lógica de negocio y el servicio de almacenamiento de archivos. Este controlador es un ejemplo práctico de cómo implementar operaciones CRUD completas en el framework, sirviendo como referencia para otros desarrolladores que necesiten crear nuevos controladores con similar funcionalidad.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🏷️ Atributos declarativos

- [[api-routes|ApiRoutes]]

## 🆕 Instancia

- [[file-service|FileService]]
- [[response-dto|ResponseDTO]]
- [[storage-service|StorageService]]

## ⚡ Llamadas estáticas

- [[entity-file-association|EntityFileAssociation]]
- [[example-crud|ExampleCrud]]
- [[example-crud-image|ExampleCrudImage]]
- [[response|Response]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.