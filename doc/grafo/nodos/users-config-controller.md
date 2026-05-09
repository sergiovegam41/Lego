---
tipo: controller
capa: app-controllers
namespace: App\Controllers\UsersConfig\Controllers
archivo: App/Controllers/UsersConfig/Controllers/UsersConfigController.php
loc: 294
deps: 6
dependents: 0
responsabilidad: Orquesta operaciones CRUD para usuarios, gestionando solicitudes HTTP y respondiendo con formatos JSON.
atributos:
  - ApiRoutes
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
  - grafo/atributo/ApiRoutes
---
# UsersConfigController

`App\Controllers\UsersConfig\Controllers\UsersConfigController`

📁 [App/Controllers/UsersConfig/Controllers/UsersConfigController.php](../../../App/Controllers/UsersConfig/Controllers/UsersConfigController.php)

> [!abstract] Responsabilidad
> Orquesta operaciones CRUD para usuarios, gestionando solicitudes HTTP y respondiendo con formatos JSON.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `UsersConfigController` es un controlador específico diseñado para gestionar operaciones CRUD (Crear, Leer, Actualizar y Eliminar) relacionadas con usuarios dentro de una aplicación web. Su principal objetivo es orquestar solicitudes HTTP entrantes que se relacionan con la configuración y gestión de usuarios, respondiendo adecuadamente en formato JSON. Esta clase resuelve el problema de centralizar toda la lógica de negocio relacionada con los usuarios en un solo lugar, lo que facilita el mantenimiento y la escalabilidad del sistema.
> 
> ### Métodos principales
> 
> 1. **`__construct($accion)`**: Este es el constructor de la clase que recibe una acción específica como parámetro (por ejemplo, `list`, `get`, `create`, etc.). Intenta ejecutar el método correspondiente a esa acción y maneja cualquier excepción que pueda ocurrir durante su ejecución, respondiendo con un error 500 en caso de fallo.
> 
> 2. **`list()`**: Este método se encarga de listar todos los usuarios existentes en la base de datos. Ordena los usuarios por fecha de creación descendente y devuelve una respuesta JSON con el listado de usuarios, excluyendo sus contraseñas para seguridad.
> 
> 3. **`get()`**: Obtiene un usuario específico basado en su ID proporcionado en los parámetros de la solicitud. Verifica que se haya proporcionado un ID válido, busca el usuario en la base de datos y devuelve una respuesta JSON con los detalles del usuario, excluyendo la contraseña.
> 
> 4. **`create()`**: Crea un nuevo usuario en la base de datos a partir de los datos proporcionados en el cuerpo de la solicitud HTTP. Verifica que se hayan proporcionado todos los campos requeridos (email, password, name, auth_group_id y role_id), verifica que no exista otro usuario con el mismo email y guarda el nuevo usuario en la base de datos.
> 
> 5. **`update()`**: Actualiza un usuario existente basado en su ID proporcionado en los datos de la solicitud HTTP. Verifica que se haya proporcionado un ID válido, busca el usuario en la base de datos, verifica si se está cambiando el email y si ya existe otro usuario con ese email, actualiza los campos del usuario y guarda los cambios.
> 
> 6. **`delete()`**: Elimina un usuario específico basado en su ID proporcionado en los datos de la solicitud HTTP. Verifica que se haya proporcionado un ID válido, busca el usuario en la base de datos y lo elimina.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant UsersConfigController as UCC
>     participant User as UserModel
>     participant DB as PostgreSQL
>     Client->>UCC: GET /api/users-config/list
>     UCC->>UserModel: fetch all users
>     UserModel-->>DB: SELECT * FROM users ORDER BY created_at DESC
>     DB-->>UserModel: list of users
>     UserModel-->>UCC: user data
>     UCC-->>Client: 200 JSON (list of users)
> 
>     Client->>UCC: GET /api/users-config/get?id=1
>     UCC->>UserModel: find user by ID
>     UserModel-->>DB: SELECT * FROM users WHERE id = 1
>     DB-->>UserModel: user data
>     UserModel-->>UCC: user data
>     UCC-->>Client: 200 JSON (user details)
> 
>     Client->>UCC: POST /api/users-config/create
>     UCC->>UserModel: create new user
>     UserModel-->>DB: INSERT INTO users (email, password, name, auth_group_id, role_id, status)
>     DB-->>UserModel: success
>     UserModel-->>UCC: user data
>     UCC-->>Client: 201 JSON (user created)
> 
>     Client->>UCC: POST /api/users-config/update
>     UCC->>UserModel: update user by ID
>     UserModel-->>DB: UPDATE users SET ... WHERE id = 1
>     DB-->>UserModel: success
>     UserModel-->>UCC: user data
>     UCC-->>Client: 200 JSON (user updated)
> 
>     Client->>UCC: POST /api/users-config/delete
>     UCC->>UserModel: delete user by ID
>     UserModel-->>DB: DELETE FROM users WHERE id = 1
>     DB-->>UserModel: success
>     UserModel-->>UCC: null
>     UCC-->>Client: 200 JSON (user deleted)
> ```
> 
> ### Cómo encaja
> 
> La clase `UsersConfigController` se conecta con el resto del sistema a través de su herencia de la clase `CoreController`, lo que le proporciona funcionalidades básicas y estructura comunes para todos los controladores. Además, interactúa directamente con el modelo `User` para realizar operaciones CRUD en la base de datos, utilizando métodos como `create()`, `find()`, `update()` y `delete()`. La clase también utiliza la clase `Response` para enviar respuestas JSON al cliente, asegurando que todas las solicitudes HTTP sean manejadas de manera consistente y adecuada.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🏷️ Atributos declarativos

- [[api-routes|ApiRoutes]]

## 🆕 Instancia

- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[response|Response]]
- [[user|User]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.