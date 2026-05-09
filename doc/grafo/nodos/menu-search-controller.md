---
tipo: controller
capa: app-controllers
namespace: App\Controllers\Menu\Controllers
archivo: App/Controllers/Menu/Controllers/MenuSearchController.php
loc: 104
deps: 2
dependents: 0
responsabilidad: Orquesta la búsqueda de items del menú desde la base de datos, aplicando filtros y formateando resultados para devolverlos como respuesta JSON.
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
---
# MenuSearchController

`App\Controllers\Menu\Controllers\MenuSearchController`

📁 [App/Controllers/Menu/Controllers/MenuSearchController.php](../../../App/Controllers/Menu/Controllers/MenuSearchController.php)

> [!abstract] Responsabilidad
> Orquesta la búsqueda de items del menú desde la base de datos, aplicando filtros y formateando resultados para devolverlos como respuesta JSON.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MenuSearchController` existe para proporcionar un endpoint específico que permite buscar items de menú en la base de datos, aplicando filtros y formateando los resultados con una ruta de navegación (breadcrumb). Este controlador es crucial para permitir a los usuarios realizar búsquedas eficientes dentro del sistema de menús, facilitando la localización rápida de elementos tanto visibles como ocultos.
> 
> ### Métodos principales
> 
> 1. **`__construct()`**: Es el constructor de la clase que inicia el proceso de búsqueda al llamar al método `search()`. Este diseño asegura que cada instancia de `MenuSearchController` ejecute una búsqueda inmediatamente al ser creada.
> 
> 2. **`search()`**: Este es el método principal que maneja la lógica de búsqueda. Recibe un parámetro de consulta (`q`) a través del objeto `Flight::request()`. Realiza validaciones básicas, como asegurarse de que la consulta tenga al menos un carácter. Luego, utiliza el modelo `MenuItem` para buscar items de menú que coincidan con la consulta, aplicando filtros específicos (excluyendo items dinámicos y buscando en los campos `label` e `index_label`). Los resultados se ordenan por nivel y orden de visualización, limitándose a 10 elementos. Finalmente, formatea cada resultado para incluir su breadcrumb y devuelve una respuesta JSON con los datos obtenidos.
> 
> 3. **`jsonResponse()`**: Este método auxiliar envía una respuesta en formato JSON al cliente. Acepta un array de datos y un código de estado HTTP opcional (por defecto 200). Utiliza el objeto `Flight::json()` para enviar la respuesta, lo que simplifica el manejo de respuestas JSON dentro del controlador.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant MenuSearchController as MSC
>     participant MenuItem
>     participant Flight
> 
>     Client->>MSC: GET /api/menu/search?q=texto
>     MSC->>Flight: request()->query['q']
>     alt query too short
>         MSC-->>Client: JSON {success: false, message: 'Query too short'}
>     else valid query
>         MSC->>MenuItem: searchable()
>         MenuItem->>MenuItem: whereRaw('LOWER(label) LIKE ?', ["%{$lowerQuery}%"])
>         MenuItem->>MenuItem: orWhereRaw('LOWER(index_label) LIKE ?', ["%{$lowerQuery}%"])
>         MenuItem-->>MSC: items
>         loop format each item
>             MSC->>item: getBreadcrumb()
>             MSC->>MSC: map breadcrumb labels
>         end
>         MSC-->>Client: JSON {success: true, data: results}
>     end
> ```
> 
> ### Cómo encaja
> 
> La clase `MenuSearchController` se integra dentro del sistema como un controlador específico para manejar solicitudes de búsqueda de menú. Hereda de `CoreController`, lo que le proporciona una base funcional común con otros controladores del sistema, incluyendo métodos y atributos básicos necesarios para el funcionamiento de los endpoints RESTful. Sin embargo, no tiene extensores, implementaciones ni traits adicionales, lo que indica que su propósito es específico y autónomo dentro del módulo de menús.
> 
> La clase se conecta con `MenuItem`, el modelo responsable de interactuar con la base de datos para recuperar los items de menú. También depende del objeto `Flight` para manejar las solicitudes HTTP, lo que sugiere una integración estrecha con el framework o biblioteca utilizada para gestionar rutas y respuestas en el sistema.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## ⚡ Llamadas estáticas

- [[menu-item|MenuItem]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.