---
tipo: controller
capa: app-controllers
namespace: App\Controllers\Files\Controllers
archivo: App/Controllers/Files/Controllers/FilesController.php
loc: 299
deps: 5
dependents: 0
responsabilidad: Gestiona las operaciones CRUD de archivos, incluyendo subir, eliminar, obtener y listar archivos, utilizando el servicio FileService para encapsular la lógica de negocio.
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
---
# FilesController

`App\Controllers\Files\Controllers\FilesController`

📁 [App/Controllers/Files/Controllers/FilesController.php](../../../App/Controllers/Files/Controllers/FilesController.php)

> [!abstract] Responsabilidad
> Gestiona las operaciones CRUD de archivos, incluyendo subir, eliminar, obtener y listar archivos, utilizando el servicio FileService para encapsular la lógica de negocio.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `FilesController` es un controlador genérico diseñado para gestionar operaciones CRUD de archivos a través de endpoints RESTful. Su principal objetivo es proporcionar una interfaz uniforme y reutilizable para subir, eliminar, obtener y listar archivos, sin estar acoplada a ninguna entidad específica del sistema. Esto permite que cualquier otro controlador o módulo del sistema utilice estos endpoints para manejar sus respectivos archivos, lo que facilita la implementación de nuevos CRUDs sin necesidad de reimplementar funcionalidades comunes relacionadas con el manejo de archivos.
> 
> ### Métodos principales
> 
> 1. **upload()**
>    - **Qué hace**: Maneja la subida de archivos a través del endpoint `POST /api/files/upload`. Recibe un archivo en formato FormData y opcionalmente parámetros como `path`, `allowed_types` y `max_size`. Utiliza el servicio `FileService` para procesar la subida y retorna el ID del archivo subido como texto plano.
> 
> 2. **delete()**
>    - **Qué hace**: Elimina un archivo específico identificado por su ID a través del endpoint `POST /api/files/delete`. Recibe el ID en el cuerpo de la solicitud JSON y utiliza el servicio `FileService` para eliminar el archivo, incluyendo sus asociaciones en la base de datos.
> 
> 3. **get()**
>    - **Qué hace**: Obtiene información detallada de un archivo específico a través del endpoint `GET /api/files/{id}`. Recibe el ID como parámetro de consulta y retorna los detalles del archivo en formato JSON.
> 
> 4. **list()**
>    - **Qué hace**: Lista archivos con filtros opcionales a través del endpoint `GET /api/files`. Puede filtrar por IDs específicos o por tipo MIME, y limita la cantidad de resultados devueltos. Utiliza el servicio `FileService` para obtener los archivos y retorna la lista en formato JSON.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant FilesController as FC
>     participant FileService as FS
>     participant DB as PostgreSQL
>     participant MinIO as Storage
> 
>     Client->>FC: POST /api/files/upload
>     FC->>FS: uploadFile(file, path, options)
>     FS-->>MinIO: Upload file to storage
>     FS-->>DB: Save file metadata
>     FS-->>FC: Return file ID
>     FC-->>Client: 200 text/plain (file ID)
> 
>     Client->>FC: POST /api/files/delete
>     FC->>FS: deleteFileAndAssociations(file_id)
>     FS-->>MinIO: Delete file from storage
>     FS-->>DB: Delete file metadata and associations
>     FS-->>FC: Confirm deletion
>     FC-->>Client: 200 JSON (success message)
> 
>     Client->>FC: GET /api/files/{id}
>     FC->>FS: getFileById(file_id)
>     FS-->>DB: Retrieve file metadata
>     FS-->>FC: Return file details
>     FC-->>Client: 200 JSON (file details)
> 
>     Client->>FC: GET /api/files
>     FC->>FS: getFilesByIds(ids) or getImages(limit)
>     FS-->>DB: Retrieve files based on filters
>     FS-->>FC: Return list of files
>     FC-->>Client: 200 JSON (list of files)
> ```
> 
> ### Cómo encaja
> 
> La clase `FilesController` se integra como parte del controlador principal de la aplicación, heredando de `CoreController`. Su rol es facilitar el manejo de archivos a través de endpoints RESTful, lo que permite una separación clara de responsabilidades entre la lógica de negocio y las operaciones CRUD. Este controlador se utiliza directamente por el cliente (frontend) para realizar operaciones sobre archivos, interactuando con el servicio `FileService` para procesar las solicitudes y acceder a la base de datos (`PostgreSQL`) y al almacenamiento de objetos (`MinIO`). La estructura modular y desacoplada de esta clase facilita su reutilización en diferentes partes del sistema, como en el manejo de productos, artículos o usuarios, donde se requiera gestionar archivos asociados.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🆕 Instancia

- [[file-service|FileService]]
- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[response|Response]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.