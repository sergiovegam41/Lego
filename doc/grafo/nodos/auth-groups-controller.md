---
tipo: controller
capa: app-controllers
namespace: App\Controllers\AuthGroups\Controllers
archivo: App/Controllers/AuthGroups/Controllers/AuthGroupsController.php
loc: 309
deps: 11
dependents: 0
responsabilidad: Orquesta las operaciones CRUD para la gestión de grupos de autenticación, manejando solicitudes HTTP y respondiendo con formatos JSON.
atributos:
  - ApiRoutes
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
  - grafo/atributo/ApiRoutes
---
# AuthGroupsController

`App\Controllers\AuthGroups\Controllers\AuthGroupsController`

📁 [App/Controllers/AuthGroups/Controllers/AuthGroupsController.php](../../../App/Controllers/AuthGroups/Controllers/AuthGroupsController.php)

> [!abstract] Responsabilidad
> Orquesta las operaciones CRUD para la gestión de grupos de autenticación, manejando solicitudes HTTP y respondiendo con formatos JSON.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `AuthGroupsController` existe para orquestar las operaciones CRUD (Create, Read, Update, Delete) relacionadas con los grupos de autenticación en el sistema. Este controlador es fundamental para gestionar la creación, consulta, actualización y eliminación de grupos de usuarios, lo que es crucial para el manejo de permisos y roles dentro del sistema. La necesidad de esta clase surge de la necesidad de centralizar la lógica de negocio relacionada con los grupos de autenticación en un solo lugar, facilitando su mantenimiento y escalabilidad.
> 
> ### Métodos principales
> 
> 1. **`__construct($accion)`**: Este es el constructor del controlador que recibe una acción como parámetro. Dependiendo de la acción proporcionada, invoca al método correspondiente (como `list`, `get`, `create`, etc.). Si ocurre algún error durante la ejecución de la acción, captura la excepción y responde con un mensaje de error en formato JSON.
> 
> 2. **`list()`**: Este método maneja las solicitudes GET para listar todos los grupos de autenticación disponibles. Consulta la base de datos utilizando el modelo `AuthGroup`, ordena los resultados por ID y devuelve una respuesta JSON con los grupos obtenidos.
> 
> 3. **`get()`**: Este método maneja las solicitudes GET individuales para obtener un grupo específico por su ID. Verifica que se haya proporcionado un ID válido, consulta la base de datos utilizando el modelo `AuthGroup`, y devuelve una respuesta JSON con el grupo encontrado o un mensaje de error si no existe.
> 
> 4. **`create()`**: Este método maneja las solicitudes POST para crear un nuevo grupo de autenticación. Recibe los datos del cuerpo de la solicitud, normaliza el ID, verifica que no exista ya un grupo con ese ID y crea un nuevo registro en la base de datos utilizando el modelo `AuthGroup`. Devuelve una respuesta JSON indicando si la creación fue exitosa o no.
> 
> 5. **`update()`**: Este método maneja las solicitudes POST para actualizar un grupo de autenticación existente. Recibe los datos del cuerpo de la solicitud, verifica que se haya proporcionado un ID válido, consulta la base de datos utilizando el modelo `AuthGroup`, actualiza los campos correspondientes y guarda los cambios en la base de datos. Devuelve una respuesta JSON indicando si la actualización fue exitosa o no.
> 
> 6. **`delete()`**: Este método maneja las solicitudes POST para eliminar un grupo de autenticación. Recibe los datos del cuerpo de la solicitud, verifica que se haya proporcionado un ID válido, consulta la base de datos utilizando el modelo `AuthGroup`, elimina el registro correspondiente y devuelve una respuesta JSON indicando si la eliminación fue exitosa o no.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant AuthGroupsController
>     participant AuthGroup
>     participant ResponseDTO
>     participant StatusCodes
> 
>     Client->>AuthGroupsController: GET /api/auth-groups/list
>     AuthGroupsController->>AuthGroup: orderBy('id')->get()
>     AuthGroup-->>AuthGroupsController: grupos
>     AuthGroupsController->>ResponseDTO: new ResponseDTO(true, 'Grupos obtenidos correctamente', $grupos)
>     AuthGroupsController->>StatusCodes: HTTP_OK
>     AuthGroupsController->>Client: JSON
> 
>     Client->>AuthGroupsController: GET /api/auth-groups/get?id=ADMINS
>     AuthGroupsController->>AuthGroup: find($id)
>     AuthGroup-->>AuthGroupsController: grupo o null
>     alt Grupo encontrado
>         AuthGroupsController->>ResponseDTO: new ResponseDTO(true, 'Grupo obtenido correctamente', $grupo)
>     else Grupo no encontrado
>         AuthGroupsController->>ResponseDTO: new ResponseDTO(false, 'Grupo no encontrado', null)
>     end
>     AuthGroupsController->>StatusCodes: HTTP_OK o HTTP_NOT_FOUND
>     AuthGroupsController->>Client: JSON
> 
>     Client->>AuthGroupsController: POST /api/auth-groups/create
>     AuthGroupsController->>AuthGroup: create($data)
>     alt Creación exitosa
>         AuthGroupsController->>ResponseDTO: new ResponseDTO(true, 'Grupo creado correctamente', $grupo)
>     else Error en creación
>         AuthGroupsController->>ResponseDTO: new ResponseDTO(false, 'Error al crear grupo', null)
>     end
>     AuthGroupsController->>StatusCodes: HTTP_OK o HTTP_INTERNAL_SERVER_ERROR
>     AuthGroupsController->>Client: JSON
> 
>     Client->>AuthGroupsController: POST /api/auth-groups/update
>     AuthGroupsController->>AuthGroup: find($id)
>     alt Grupo encontrado
>         AuthGroupsController->>AuthGroup: fill($data)->save()
>         AuthGroupsController->>ResponseDTO: new ResponseDTO(true, 'Grupo actualizado correctamente', $grupo)
>     else Grupo no encontrado
>         AuthGroupsController->>ResponseDTO: new ResponseDTO(false, 'Grupo no encontrado', null)
>     end
>     AuthGroupsController->>StatusCodes: HTTP_OK o HTTP_NOT_FOUND
>     AuthGroupsController->>Client: JSON
> 
>     Client->>AuthGroupsController: POST /api/auth-groups/delete
>     AuthGroupsController->>AuthGroup: find($id)
>     alt Grupo encontrado
>         AuthGroupsController->>AuthGroup: delete()
>         AuthGroupsController->>ResponseDTO: new ResponseDTO(true, 'Grupo eliminado correctamente', null)
>     else Grupo no encontrado
>         AuthGroupsController->>ResponseDTO: new ResponseDTO(false, 'Grupo no encontrado', null)
>     end
>     AuthGroupsController->>StatusCodes: HTTP_OK o HTTP_NOT_FOUND
>     AuthGroupsController->>Client: JSON
> ```
> 
> ### Cómo encaja
> 
> La clase `AuthGroupsController` se integra dentro del sistema como un controlador específico para la gestión de grupos de autenticación. Hereda de `CoreController`, lo que le proporciona una estructura básica y métodos comunes para manejar solicitudes HTTP. Utiliza el modelo `AuthGroup` para interactuar con la base de datos, permitiendo realizar operaciones CRUD sobre los grupos de autenticación. Además, utiliza las clases `ResponseDTO` y `StatusCodes` para formatear y enviar respuestas JSON adecuadas al cliente. Esta clase es un componente clave en el módulo de gestión de usuarios y permisos del sistema, facilitando la interacción entre el frontend y la base de datos a través de una API REST bien definida.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🏷️ Atributos declarativos

- [[api-routes|ApiRoutes]]

## 🆕 Instancia

- [[auth-groups-provider|AuthGroupsProvider]]
- [[auth-request-dto|AuthRequestDTO]]
- [[request|Request]]
- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[auth-group|AuthGroup]]
- [[response|Response]]

## 🔗 Constantes referenciadas

- [[auth-actions|AuthActions]]
- [[status-codes|StatusCodes]]

## 📥 Type hints (parámetros)

- [[auth-request-dto|AuthRequestDTO]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.