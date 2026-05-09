---
tipo: controller
capa: app-controllers
namespace: App\Controllers\RolesConfig\Controllers
archivo: App/Controllers/RolesConfig/Controllers/RolesConfigController.php
loc: 337
deps: 6
dependents: 0
responsabilidad: Orquesta las operaciones CRUD para la gestión de roles mediante endpoints RESTful, validando y normalizando datos antes de interactuar con el modelo Role.
atributos:
  - ApiRoutes
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
  - grafo/atributo/ApiRoutes
---
# RolesConfigController

`App\Controllers\RolesConfig\Controllers\RolesConfigController`

📁 [App/Controllers/RolesConfig/Controllers/RolesConfigController.php](../../../App/Controllers/RolesConfig/Controllers/RolesConfigController.php)

> [!abstract] Responsabilidad
> Orquesta las operaciones CRUD para la gestión de roles mediante endpoints RESTful, validando y normalizando datos antes de interactuar con el modelo Role.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `RolesConfigController` es un controlador específico diseñado para gestionar operaciones CRUD (Crear, Leer, Actualizar y Eliminar) relacionadas con roles dentro de una aplicación. Su principal objetivo es orquestar las solicitudes HTTP que interactúan con el modelo `Role`, validando y normalizando los datos antes de realizar cualquier operación en la base de datos. Este controlador se encarga de manejar todas las interacciones RESTful necesarias para configurar roles, asegurando que los datos sean consistentes y cumplan con las reglas de negocio establecidas.
> 
> ### Métodos principales
> 
> 1. **`__construct($accion)`**: Es el constructor de la clase que recibe una acción como parámetro y la ejecuta. Si ocurre algún error durante la ejecución, captura la excepción y responde con un mensaje de error interno del servidor.
>    
> 2. **`list()`**: Este método maneja las solicitudes GET para listar todos los roles ordenados por `auth_group_id` y `role_id`. Responde con una lista de roles en formato JSON.
> 
> 3. **`get()`**: Gestiona las solicitudes GET individuales para obtener un rol específico basado en su ID. Si el ID no se proporciona o el rol no existe, responde con los respectivos códigos de error HTTP y mensajes descriptivos.
> 
> 4. **`create()`**: Procesa las solicitudes POST para crear nuevos roles. Normaliza los datos recibidos (IDs a mayúsculas sin acentos ni caracteres especiales) y verifica que no exista un rol con los mismos `auth_group_id` y `role_id`. Si todo está correcto, crea el nuevo rol en la base de datos.
> 
> 5. **`update()`**: Maneja las solicitudes POST para actualizar roles existentes. Verifica que el ID del rol esté presente y luego actualiza los campos proporcionados, asegurándose de que no haya duplicidad en `auth_group_id` y `role_id`.
> 
> 6. **`delete()`**: Procesa las solicitudes POST para eliminar un rol basado en su ID. Si el rol no existe, responde con un código de error HTTP 404.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant RolesConfigController as RCC
>     participant Role as RoleModel
>     participant DB as PostgreSQL
>     
>     Client->>RCC: GET /api/roles-config/list
>     RCC->>RoleModel: findAll()
>     RoleModel-->>DB: SELECT * FROM roles ORDER BY auth_group_id, role_id
>     DB-->>RoleModel: Roles data
>     RoleModel-->>RCC: Roles array
>     RCC-->>Client: 200 JSON
>     
>     Client->>RCC: GET /api/roles-config/get?id=1
>     RCC->>RoleModel: findById(1)
>     RoleModel-->>DB: SELECT * FROM roles WHERE id = 1
>     DB-->>RoleModel: Role data or null
>     alt Role found
>         RoleModel-->>RCC: Role object
>         RCC-->>Client: 200 JSON
>     else Role not found
>         RCC-->>Client: 404 JSON
>     end
>     
>     Client->>RCC: POST /api/roles-config/create
>     RCC->>RoleModel: create(data)
>     alt Data valid and unique
>         RoleModel-->>DB: INSERT INTO roles (auth_group_id, role_id, ...)
>         DB-->>RoleModel: Insert success
>         RoleModel-->>RCC: Created role object
>         RCC-->>Client: 200 JSON
>     else Validation or uniqueness error
>         RCC-->>Client: 400 or 409 JSON
>     end
>     
>     Client->>RCC: POST /api/roles-config/update
>     RCC->>RoleModel: update(data)
>     alt Data valid and unique
>         RoleModel-->>DB: UPDATE roles SET ... WHERE id = ...
>         DB-->>RoleModel: Update success
>         RoleModel-->>RCC: Updated role object
>         RCC-->>Client: 200 JSON
>     else Validation or uniqueness error
>         RCC-->>Client: 400 or 409 JSON
>     end
>     
>     Client->>RCC: POST /api/roles-config/delete
>     RCC->>RoleModel: delete(id)
>     alt Role found and deleted
>         RoleModel-->>DB: DELETE FROM roles WHERE id = ...
>         DB-->>RoleModel: Delete success
>         RoleModel-->>RCC: null
>         RCC-->>Client: 200 JSON
>     else Role not found
>         RCC-->>Client: 404 JSON
>     end
> ```
> 
> ### Cómo encaja
> 
> La clase `RolesConfigController` se integra dentro del sistema como un controlador específico que maneja las solicitudes relacionadas con la gestión de roles. Hereda de `CoreController`, lo que le proporciona una estructura básica y funcionalidades comunes necesarias para todos los controladores en el framework. Este controlador es responsable de interactuar con el modelo `Role` para realizar operaciones CRUD, asegurando que todas las solicitudes sean válidas y seguras antes de interactuar con la base de datos.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🏷️ Atributos declarativos

- [[api-routes|ApiRoutes]]

## 🆕 Instancia

- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[response|Response]]
- [[role|Role]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.