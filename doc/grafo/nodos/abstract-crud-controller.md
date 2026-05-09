---
tipo: abstract-class
capa: core-controllers
namespace: Core\Controllers
archivo: Core/Controllers/AbstractCrudController.php
loc: 470
deps: 2
dependents: 1
responsabilidad: "Define un controlador abstracto genérico para API REST, que gestiona operaciones CRUD automáticamente para modelos decorados con #[ApiCrudResource], aplicando filtros, búsqueda, ordenamiento y paginación según la configuración del atributo."
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/core-controllers
---
# AbstractCrudController

`Core\Controllers\AbstractCrudController`

📁 [Core/Controllers/AbstractCrudController.php](../../../Core/Controllers/AbstractCrudController.php)

> [!abstract] Responsabilidad
> Define un controlador abstracto genérico para API REST, que gestiona operaciones CRUD automáticamente para modelos decorados con #[ApiCrudResource], aplicando filtros, búsqueda, ordenamiento y paginación según la configuración del atributo.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `AbstractCrudController` es una clase abstracta diseñada para simplificar y centralizar la implementación de controladores CRUD (Create, Read, Update, Delete) en aplicaciones web basadas en Laravel. Su principal objetivo es proporcionar una estructura genérica que pueda ser reutilizada para diferentes modelos Eloquent, evitando la duplicación de código y facilitando el mantenimiento. La clase se centra en operaciones CRUD estándar, permitiendo configurar filtros, búsqueda y paginación a través del atributo `#[ApiCrudResource]`, lo que permite una mayor flexibilidad sin necesidad de escribir código específico para cada modelo.
> 
> ### Métodos principales
> 
> 1. **`__construct(string $modelClass)`**: Este es el constructor de la clase. Recibe el nombre completo de la clase del modelo Eloquent y verifica si existe y está decorado con `#[ApiCrudResource]`. Luego, inicializa los atributos `$modelClass`, `$config` y `$model`.
> 
> 2. **`list()`**: Maneja la solicitud GET para listar recursos con paginación. Aplica filtros, búsqueda global, ordenamiento y paginación según los parámetros de consulta. Envía una respuesta JSON con los datos listados y la información de paginación.
> 
> 3. **`get($id)`**: Maneja la solicitud GET para obtener un recurso específico por su ID. Verifica si el recurso existe, aplica configuraciones como `hidden` y `appends`, y envía una respuesta JSON con los datos del recurso.
> 
> 4. **`create()`**: Maneja la solicitud POST para crear un nuevo recurso. Valida los datos recibidos en el cuerpo de la solicitud, crea el recurso en la base de datos y envía una respuesta JSON indicando el éxito o fracaso de la operación.
> 
> 5. **`update($id)`**: Maneja la solicitud PUT para actualizar un recurso existente. Verifica si el recurso existe, obtiene los datos del cuerpo de la solicitud, actualiza el recurso y envía una respuesta JSON con los datos actualizados.
> 
> 6. **`delete($id)`**: Maneja la solicitud DELETE para eliminar un recurso. Verifica si el recurso existe, lo elimina (soft delete o hard delete según la configuración) y envía una respuesta JSON indicando el éxito de la operación.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class AbstractCrudController {
>         <<abstract>>
>         +list()*
>         +get($id)*
>         +create()*
>         +update($id)*
>         +delete($id)*
>     }
>     class DefaultCrudController
>     AbstractCrudController <|-- DefaultCrudController
> ```
> 
> ### Cómo encaja
> 
> `AbstractCrudController` se conecta con el resto del sistema a través de su extensión por `DefaultCrudController`. Esta clase abstracta proporciona una implementación genérica para operaciones CRUD, que luego puede ser especializada o extendida según las necesidades específicas del proyecto. La clase no tiene relaciones directas con otras clases mencionadas en el input, ya que se centra en la lógica de controlador y no interactúa directamente con otros componentes del sistema como rutas o vistas.

## ⚡ Llamadas estáticas

- [[response|Response]]

## 🔗 Constantes referenciadas

- [[api-crud-resource|ApiCrudResource]]

## 👥 Es referenciado por

- [[default-crud-controller|DefaultCrudController]] *(extends)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.