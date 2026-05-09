---
tipo: class
capa: core-attributes
namespace: Core\Attributes
archivo: Core/Attributes/ApiGetResource.php
loc: 255
deps: 1
dependents: 11
responsabilidad: Define un atributo para modelos que expone endpoints de API de solo lectura, gestionando rutas GET con paginación, filtros y búsqueda.
atributos:
  - Attribute
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-attributes
  - grafo/atributo/Attribute
---
# ApiGetResource

`Core\Attributes\ApiGetResource`

📁 [Core/Attributes/ApiGetResource.php](../../../Core/Attributes/ApiGetResource.php)

> [!abstract] Responsabilidad
> Define un atributo para modelos que expone endpoints de API de solo lectura, gestionando rutas GET con paginación, filtros y búsqueda.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ApiGetResource` es un atributo diseñado para simplificar la creación de endpoints de API de solo lectura (GET) en modelos del framework Lego. Su principal objetivo es proporcionar una forma declarativa y flexible de definir cómo se deben exponer los datos de un modelo a través de una API RESTful, con soporte para paginación, filtros y búsqueda avanzados. Esta clase resuelve el problema de tener que escribir manualmente controladores y rutas para cada recurso, permitiendo una configuración centralizada y reutilizable.
> 
> ### Métodos principales
> 
> 1. **`__construct()`**: Este es el constructor de la clase. Permite definir varios parámetros como el endpoint, tipo de paginación, cantidad de elementos por página, campos ordenables, filtrables y buscables, middlewares, campos ocultos y adicionales. También incluye validaciones para asegurar que los valores proporcionados sean correctos.
> 
> 2. **`getEndpoint()`**: Genera la URL del endpoint basada en el nombre del modelo o en un endpoint personalizado definido al aplicar el atributo. Esta función es crucial para determinar cómo se accederá a los datos del modelo a través de la API.
> 
> 3. **`getControllerClass()`**: Retorna la clase de controlador que manejará las solicitudes para este recurso. Permite especificar un controlador personalizado o utilizar uno por defecto si no se proporciona uno.
> 
> 4. **`hasMiddleware()`**: Verifica si se ha definido un middleware específico para el recurso, lo cual es útil para aplicar autorizaciones o transformaciones específicas a las solicitudes.
> 
> 5. **`toArray()`**: Convierte la configuración del atributo en un array asociativo, facilitando su serialización y uso en otras partes del sistema.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ApiGetResource {
>         +__construct($endpoint, $pagination, $perPage, $sortable, $filterable, $searchable, $middleware, $hidden, $appends, $controllerClass)
>         +getEndpoint($modelClass): string
>         +getControllerClass(): string
>         +hasMiddleware($middleware): bool
>         +toArray(): array
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ApiGetResource` se integra en el sistema Lego como parte del módulo de rutas y controladores. Se utiliza principalmente en la definición de modelos para exponer sus datos a través de una API RESTful. Al aplicar este atributo a un modelo, se configura automáticamente cómo se deben acceder y filtrar los datos del modelo, lo que facilita el desarrollo de APIs complejas con características avanzadas de paginación y búsqueda sin necesidad de escribir código adicional para cada recurso.

## 🔗 Constantes referenciadas

- [[default-get-controller|DefaultGetController]]

## 👥 Es referenciado por

- [[abstract-get-controller|AbstractGetController]] *(const_fetch)*
- [[api-get-router|ApiGetRouter]] *(const_fetch, type_hint, returns)*
- [[auth-group|AuthGroup]] *(attribute)*
- [[example-crud|ExampleCrud]] *(attribute)*
- [[role|Role]] *(attribute)*
- [[table-config|TableConfig]] *(const_fetch, type_hint)*
- [[tool|Tool]] *(attribute)*
- [[user|User]] *(attribute)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.