---
tipo: controller
capa: app-controllers
namespace: App\Controllers\Tools\Controllers
archivo: App/Controllers/Tools/Controllers/ToolsController.php
loc: 367
deps: 9
dependents: 0
responsabilidad: Gestiona las operaciones CRUD de herramientas, incluyendo listado, obtención, creación, actualización y eliminación, junto con sus características e imágenes asociadas.
atributos:
  - ApiRoutes
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
  - grafo/atributo/ApiRoutes
---
# ToolsController

`App\Controllers\Tools\Controllers\ToolsController`

📁 [App/Controllers/Tools/Controllers/ToolsController.php](../../../App/Controllers/Tools/Controllers/ToolsController.php)

> [!abstract] Responsabilidad
> Gestiona las operaciones CRUD de herramientas, incluyendo listado, obtención, creación, actualización y eliminación, junto con sus características e imágenes asociadas.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ToolsController` existe para gestionar todas las operaciones CRUD (Crear, Leer, Actualizar y Eliminar) relacionadas con herramientas dentro del sistema. Su principal objetivo es proporcionar una interfaz API RESTful que permita a los clientes interactuar con la base de datos de herramientas, incluyendo sus características e imágenes asociadas. Esta clase es crucial para mantener la cohesión entre el frontend y el backend, asegurando que todas las operaciones sobre herramientas se manejen de manera eficiente y segura.
> 
> ### Métodos principales
> 
> 1. **list()**: Este método lista todas las herramientas disponibles en la base de datos, ordenadas por fecha de creación descendente. Incluye información adicional como el número de características y una lista de dichas características para cada herramienta.
> 
> 2. **get()**: Obtiene los detalles de una herramienta específica identificada por su ID. Además de los datos básicos de la herramienta, incluye una lista de sus características y las imágenes asociadas, formateadas con información detallada como el tamaño y el tipo MIME.
> 
> 3. **create()**: Crea una nueva herramienta en la base de datos a partir de los datos proporcionados por el cliente. También maneja la creación de características asociadas y la asociación de imágenes usando el servicio `FileService`.
> 
> 4. **update()**: Actualiza una herramienta existente basada en los datos proporcionados. Este método también se encarga de actualizar las características y las imágenes asociadas, eliminando cualquier dato antiguo que ya no sea relevante.
> 
> 5. **delete()**: Elimina una herramienta específica identificada por su ID. Este método asegura que todas las características asociadas a la herramienta también sean eliminadas para mantener la integridad de los datos.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant ToolsController as TC
>     participant Tool as T
>     participant ToolFeature as TF
>     participant FileService as FS
>     participant DB as PostgreSQL
> 
>     Client->>TC: GET /api/tools/list
>     TC->>T: Tool::with('features')->get()
>     T-->>TC: Records with features
>     TC-->>Client: JSON response
> 
>     Client->>TC: GET /api/tools/get?id=1
>     TC->>T: Tool::with('features')->find(1)
>     T-->>TC: Record with features and images
>     FS->>DB: getEntityFiles('Tool', 1)
>     DB-->>FS: File associations
>     FS-->>TC: Formatted image data
>     TC-->>Client: JSON response
> 
>     Client->>TC: POST /api/tools/create
>     TC->>T: Tool::create(data)
>     T-->>DB: Insert new tool
>     DB-->>T: New tool ID
>     T->>TF: ToolFeature::create(data)
>     TF-->>DB: Insert features
>     DB-->>TF: Success
>     FS->>DB: associateFileToEntity(image_id, 'Tool', tool_id)
>     DB-->>FS: Success
>     TC-->>Client: JSON response
> 
>     Client->>TC: POST /api/tools/update
>     TC->>T: Tool::find(id)
>     T-->>TC: Existing record
>     T->>T: update(data)
>     T-->>DB: Update tool
>     DB-->>T: Success
>     TF->>TF: where('tool_id', id)->delete()
>     DB-->>TF: Success
>     TF->>TF: ToolFeature::create(data)
>     TF-->>DB: Insert new features
>     DB-->>TF: Success
>     FS->>DB: associateFileToEntity(image_id, 'Tool', tool_id)
>     DB-->>FS: Success
>     TC-->>Client: JSON response
> 
>     Client->>TC: POST /api/tools/delete
>     TC->>T: Tool::find(id)
>     T-->>TC: Existing record
>     T->>DB: delete()
>     DB-->>T: Success
>     TC-->>Client: JSON response
> ```
> 
> ### Cómo encaja
> 
> La clase `ToolsController` se integra perfectamente dentro del sistema como una extensión de `CoreController`, lo que le permite aprovechar la funcionalidad y estructura ya definidas para manejar las solicitudes HTTP. Además, utiliza el servicio `FileService` para gestionar las imágenes asociadas a las herramientas, asegurando un manejo centralizado y eficiente de los archivos multimedia. Este controlador se conecta directamente con los modelos `Tool` y `ToolFeature`, permitiéndole interactuar con la base de datos y realizar operaciones CRUD complejas sobre herramientas y sus características.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🏷️ Atributos declarativos

- [[api-routes|ApiRoutes]]

## 🆕 Instancia

- [[file-service|FileService]]
- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[entity-file-association|EntityFileAssociation]]
- [[response|Response]]
- [[tool|Tool]]
- [[tool-feature|ToolFeature]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.