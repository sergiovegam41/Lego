---
tipo: class
capa: core
namespace: Core\Models
archivo: Core/Models/ResponseDTO.php
loc: 22
deps: 0
dependents: 22
responsabilidad: Encapsula la estructura de respuesta HTTP con campos para éxito, mensaje, datos y código de estado.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# ResponseDTO

`Core\Models\ResponseDTO`

📁 [Core/Models/ResponseDTO.php](../../../Core/Models/ResponseDTO.php)

> [!abstract] Responsabilidad
> Encapsula la estructura de respuesta HTTP con campos para éxito, mensaje, datos y código de estado.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ResponseDTO` existe para encapsular y normalizar la estructura de respuesta HTTP en toda la aplicación. Este diseño facilita una comunicación consistente entre el servidor y los clientes, asegurando que todas las respuestas sigan un formato predefinido. Esto es especialmente útil en aplicaciones que utilizan múltiples controladores y servicios, ya que permite a diferentes partes del sistema responder de manera uniforme sin tener que preocuparse por la estructura detallada de cada respuesta.
> 
> ### Métodos principales
> 
> - **`__construct($success, $msj, $data, $status_code = null)`**: Este es el constructor principal de la clase. Inicializa los atributos `$success`, `$msj`, `$data` y `$status_code`. El parámetro `$status_code` es opcional, lo que permite crear respuestas sin necesidad de especificar un código de estado.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ResponseDTO {
>         +bool $success
>         +string $msj
>         +$data
>         +$status_code
>         +__construct($success, $msj, $data, $status_code = null)
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ResponseDTO` se utiliza ampliamente en el sistema, siendo instanciada por varios controladores y servicios. Esto incluye `CoreController`, `RestfulController`, `AuthServicesCore`, entre otros. Al encapsular la estructura de respuesta, esta clase facilita que estos componentes devuelvan respuestas HTTP de manera consistente, lo que simplifica el manejo de errores y la interpretación de las respuestas en el lado del cliente.

## 👥 Es referenciado por

- [[abstract-auth-core-contract|AbstractAuthCoreContract]] *(returns)*
- [[admin-auth-group-provider|AdminAuthGroupProvider]] *(instantiates, returns)*
- [[api-auth-group-provider|ApiAuthGroupProvider]] *(instantiates, returns)*
- [[auth-groups-controller|AuthGroupsController]] *(instantiates)*
- [[auth-groups-provider|AuthGroupsProvider]] *(instantiates, returns)*
- [[auth-services-core|AuthServicesCore]] *(instantiates, returns)*
- [[core-controller|CoreController]] *(instantiates)*
- [[example-crud-controller|ExampleCrudController]] *(instantiates)*
- [[files-controller|FilesController]] *(instantiates)*
- [[lego-helpers|LegoHelpers]] *(returns)*
- [[menu-config-controller|MenuConfigController]] *(instantiates)*
- [[menu-item-hierarchy-controller|MenuItemHierarchyController]] *(instantiates)*
- [[menu-structure-controller|MenuStructureController]] *(instantiates)*
- [[restful-controller|RestfulController]] *(instantiates)*
- [[roles-config-controller|RolesConfigController]] *(instantiates)*
- [[storage-controller|StorageController]] *(instantiates)*
- [[tools-controller|ToolsController]] *(instantiates)*
- [[users-config-controller|UsersConfigController]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.