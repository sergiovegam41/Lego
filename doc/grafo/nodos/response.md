---
tipo: class
capa: core
namespace: Core
archivo: Core/Response.php
loc: 23
deps: 0
dependents: 17
responsabilidad: Encapsula la generación de respuestas HTTP, proporcionando métodos estáticos para enviar JSON y HTML con códigos de estado específicos.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# Response

`Core\Response`

📁 [Core/Response.php](../../../Core/Response.php)

> [!abstract] Responsabilidad
> Encapsula la generación de respuestas HTTP, proporcionando métodos estáticos para enviar JSON y HTML con códigos de estado específicos.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `Response` es fundamental para manejar la generación de respuestas HTTP en el sistema Lego. Su principal objetivo es proporcionar una interfaz sencilla y coherente para enviar diferentes tipos de contenido (como JSON y HTML) con códigos de estado específicos. Esto facilita la comunicación entre el servidor y el cliente, asegurando que las respuestas sean consistentes y adecuadas a los requerimientos del sistema.
> 
> ### Métodos principales
> 
> 1. **`json($status, array $json)`**:
>    - Este método se encarga de enviar una respuesta en formato JSON al cliente. Establece el tipo de contenido como `application/json`, establece el código de estado HTTP proporcionado y luego codifica y envía el array `$json` como respuesta.
> 
> 2. **`uri(string $html, string $title = "")`**:
>    - Este método se utiliza para enviar una respuesta HTML al cliente. Simplemente imprime la cadena `$html` y termina la ejecución del script con `die`.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class Response {
>         +json($status, array $json)
>         +uri(string $html, string $title = "")
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `Response` se utiliza directamente por varias partes del sistema que necesitan enviar respuestas HTTP. Aunque no es extendida ni implementada por otras clases, su presencia como una clase estática facilita la reutilización de código y asegura que todas las respuestas sigan un patrón consistente. Esto es especialmente útil en el manejo de solicitudes API y renderizado de páginas web dentro del framework Lego.

## 👥 Es referenciado por

- [[abstract-crud-controller|AbstractCrudController]] *(static_call)*
- [[abstract-get-controller|AbstractGetController]] *(static_call)*
- [[api-route-discovery|ApiRouteDiscovery]] *(static_call)*
- [[auth-groups-controller|AuthGroupsController]] *(static_call)*
- [[components-controller|ComponentsController]] *(static_call)*
- [[core-controller|CoreController]] *(static_call)*
- [[example-crud-controller|ExampleCrudController]] *(static_call)*
- [[files-controller|FilesController]] *(static_call)*
- [[menu-config-controller|MenuConfigController]] *(static_call)*
- [[menu-item-hierarchy-controller|MenuItemHierarchyController]] *(static_call)*
- [[menu-structure-controller|MenuStructureController]] *(static_call)*
- [[request|Request]] *(static_call)*
- [[restful-controller|RestfulController]] *(static_call)*
- [[roles-config-controller|RolesConfigController]] *(static_call)*
- [[storage-controller|StorageController]] *(static_call)*
- [[tools-controller|ToolsController]] *(static_call)*
- [[users-config-controller|UsersConfigController]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.