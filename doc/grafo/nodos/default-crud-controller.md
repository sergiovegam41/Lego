---
tipo: controller
capa: core-controllers
namespace: Core\Controllers
archivo: Core/Controllers/DefaultCrudController.php
loc: 12
deps: 1
dependents: 1
responsabilidad: Sirve como implementación concreta vacía de AbstractCrudController, usada por ApiCrudRouter cuando el modelo no especifica un controlador custom.
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/core-controllers
---
# DefaultCrudController

`Core\Controllers\DefaultCrudController`

📁 [Core/Controllers/DefaultCrudController.php](../../../Core/Controllers/DefaultCrudController.php)

> [!abstract] Responsabilidad
> Sirve como implementación concreta vacía de AbstractCrudController, usada por ApiCrudRouter cuando el modelo no especifica un controlador custom.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `DefaultCrudController` existe para proporcionar una implementación concreta vacía de `AbstractCrudController`. Su principal propósito es ser utilizada por `ApiCrudRouter` cuando un modelo no especifica un controlador personalizado a través del atributo `#[ApiCrudResource]`. Este enfoque permite que la aplicación tenga una estructura flexible y modular, ya que el router puede utilizar esta clase como una opción predeterminada sin necesidad de definir un controlador específico para cada modelo.
> 
> ### Métodos principales
> 
> Dado que `DefaultCrudController` es una clase vacía que hereda toda su funcionalidad de `AbstractCrudController`, no tiene métodos propios. Sin embargo, los métodos clave que se ejecutan a través de esta clase son:
> 
> 1. **index()**: Este método maneja las solicitudes GET para listar todos los recursos del modelo.
> 2. **show($id)**: Maneja las solicitudes GET individuales para un recurso específico identificado por su ID.
> 3. **store(Request $request)**: Procesa las solicitudes POST para crear nuevos recursos basados en los datos proporcionados en la solicitud.
> 4. **update(Request $request, $id)**: Gestiona las solicitudes PUT/PATCH para actualizar un recurso existente con el ID especificado.
> 5. **destroy($id)**: Maneja las solicitudes DELETE para eliminar un recurso específico.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant Router as ApiCrudRouter
>     participant Controller as DefaultCrudController
>     participant Model as ModeloGenerico
>     participant DB as PostgreSQL
>     
>     Client->>Router: GET /api/modelos
>     Router->>Controller: index()
>     Controller->>Model: all()
>     Model-->>DB: SELECT
>     DB-->>Model: resultados
>     Model-->>Controller: data
>     Controller-->>Client: 200 JSON
>     
>     Client->>Router: POST /api/modelos
>     Router->>Controller: store(Request)
>     Controller->>Model: create(data)
>     Model->>DB: INSERT
>     DB-->>Model: id
>     Model-->>Controller: instance
>     Controller-->>Client: 201 JSON
>     
>     Client->>Router: GET /api/modelos/{id}
>     Router->>Controller: show(id)
>     Controller->>Model: find(id)
>     Model-->>DB: SELECT WHERE id = {id}
>     DB-->>Model: resultado
>     Model-->>Controller: data
>     Controller-->>Client: 200 JSON
>     
>     Client->>Router: PUT /api/modelos/{id}
>     Router->>Controller: update(Request, id)
>     Controller->>Model: find(id)
>     Model-->>DB: SELECT WHERE id = {id}
>     DB-->>Model: resultado
>     Model->>DB: UPDATE
>     DB-->>Model: success
>     Model-->>Controller: instance
>     Controller-->>Client: 200 JSON
>     
>     Client->>Router: DELETE /api/modelos/{id}
>     Router->>Controller: destroy(id)
>     Controller->>Model: find(id)
>     Model-->>DB: SELECT WHERE id = {id}
>     DB-->>Model: resultado
>     Model->>DB: DELETE
>     DB-->>Model: success
>     Model-->>Controller: instance
>     Controller-->>Client: 204 No Content
> ```
> 
> ### Cómo encaja
> 
> `DefaultCrudController` se integra como una parte esencial del sistema de ruteo y controlador de la aplicación. Es utilizada por `ApiCrudRouter` cuando no hay un controlador personalizado especificado para un modelo particular. Esta clase permite que el router maneje solicitudes CRUD básicas sin necesidad de definir un controlador específico, lo que facilita la creación de APIs RESTful rápidas y sencillas. Al heredar de `AbstractCrudController`, `DefaultCrudController` aprovecha toda la lógica implementada en la clase abstracta, asegurando una funcionalidad coherente y estandarizada para todas las operaciones CRUD.

## 🔼 Hereda de

- [[abstract-crud-controller|AbstractCrudController]]

## 👥 Es referenciado por

- [[api-crud-resource|ApiCrudResource]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.