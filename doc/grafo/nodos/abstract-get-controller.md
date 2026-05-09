---
tipo: abstract-class
capa: core-controllers
namespace: Core\Controllers
archivo: Core/Controllers/AbstractGetController.php
loc: 393
deps: 2
dependents: 1
responsabilidad: Define un controlador abstracto para operaciones de solo lectura (GET) en recursos, encapsulando listado y obtención con paginación, filtros, búsqueda y manejo de errores.
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/core-controllers
---
# AbstractGetController

`Core\Controllers\AbstractGetController`

📁 [Core/Controllers/AbstractGetController.php](../../../Core/Controllers/AbstractGetController.php)

> [!abstract] Responsabilidad
> Define un controlador abstracto para operaciones de solo lectura (GET) en recursos, encapsulando listado y obtención con paginación, filtros, búsqueda y manejo de errores.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `AbstractGetController` es una clase abstracta diseñada para manejar operaciones de solo lectura (GET) en recursos, especialmente para alimentar componentes de tabla (`TableComponent`). Su principal objetivo es encapsular la lógica común necesaria para listar y obtener recursos con soporte para diferentes tipos de paginación, filtros y búsqueda. Esta abstracción se creó para evitar colisiones con controladores CRUD existentes y proporcionar una API específica para tablas que solo incluye operaciones de lectura.
> 
> ### Métodos principales
> 
> 1. **`list()`**: Este método maneja la solicitud GET para listar recursos con paginación, filtros y búsqueda. Permite configurar varios parámetros a través de query params como `page`, `limit`, `cursor`, `sort`, `order`, `search` y `filter[campo]`. Utiliza métodos auxiliares para aplicar estos filtros, buscar y ordenar los resultados antes de paginarlos.
> 
> 2. **`get($id)`**: Este método maneja la solicitud GET para obtener un recurso específico por su ID. Verifica si el recurso existe y aplica configuraciones adicionales como ocultar o añadir campos según la configuración del modelo. Si el recurso no se encuentra, devuelve una respuesta 404.
> 
> 3. **`applyFilters($query)`**: Este método aplica filtros a la consulta Eloquent basados en los parámetros de query. Soporta diferentes tipos de filtros como `contains`, `equals`, `lessThan`, entre otros. Verifica si cada campo es filtrable según la configuración del modelo.
> 
> 4. **`applySearch($query)`**: Este método aplica una búsqueda global a la consulta Eloquent utilizando los campos especificados en la configuración del modelo. Utiliza el operador `ILIKE` para realizar búsquedas insensibles a mayúsculas y minúsculas.
> 
> 5. **`applySort($query)`**: Este método ordena la consulta Eloquent según los parámetros de query `sort` y `order`. Verifica si el campo especificado es ordenable según la configuración del modelo.
> 
> 6. **`applyPagination($query)`**: Este método aplica paginación a la consulta Eloquent basada en el tipo de paginación configurado (offset o cursor-based). Utiliza métodos auxiliares para manejar cada tipo de paginación.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class AbstractGetController {
>         <<abstract>>
>         +list()
>         +get($id)
>         -applyFilters($query)
>         -applySearch($query)
>         -applySort($query)
>         -applyPagination($query)
>         -offsetPaginate($query, $perPage)
>         -cursorPaginate($query, $perPage)
>         -handleError($e, $action)
>     }
>     AbstractGetController <|-- DefaultGetController
> ```
> 
> ### Cómo encaja
> 
> `AbstractGetController` se conecta con el resto del sistema a través de su extensión por `DefaultGetController`. Esta clase abstracta proporciona una base sólida para controladores específicos que solo manejan operaciones de lectura, encapsulando la lógica común necesaria para listar y obtener recursos. Al ser abstracta, no se puede instanciar directamente; en su lugar, debe ser extendida por clases concretas que implementen funcionalidades específicas según las necesidades del recurso.

## ⚡ Llamadas estáticas

- [[response|Response]]

## 🔗 Constantes referenciadas

- [[api-get-resource|ApiGetResource]]

## 👥 Es referenciado por

- [[default-get-controller|DefaultGetController]] *(extends)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.