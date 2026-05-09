---
tipo: component
capa: core-attributes
namespace: Core\Attributes
archivo: Core/Attributes/ApiComponent.php
loc: 15
deps: 0
dependents: 20
responsabilidad: Define un atributo para marcar clases como componentes de API, especificando la ruta, métodos HTTP y requerimiento de autenticación.
atributos:
  - Attribute
tags:
  - grafo
  - grafo/tipo/component
  - grafo/capa/core-attributes
  - grafo/atributo/Attribute
---
# ApiComponent

`Core\Attributes\ApiComponent`

📁 [Core/Attributes/ApiComponent.php](../../../Core/Attributes/ApiComponent.php)

> [!abstract] Responsabilidad
> Define un atributo para marcar clases como componentes de API, especificando la ruta, métodos HTTP y requerimiento de autenticación.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `AppComponent` existe para proporcionar una forma declarativa y centralizada de marcar y configurar clases como componentes de API dentro del framework Lego. Este atributo permite especificar fácilmente la ruta, los métodos HTTP soportados y si el acceso a estos endpoints requiere autenticación. La necesidad de esta clase surge de la necesidad de estandarizar y simplificar la definición de rutas y comportamientos en APIs RESTful, facilitando así la mantenibilidad y escalabilidad del código.
> 
> ### Métodos principales
> 
> - **Constructor (`__construct`)**: Este método inicializa el atributo con los valores proporcionados para `path`, `methods` y `requiresAuth`. No tiene una funcionalidad directa visible desde fuera de la clase, pero es crucial para almacenar la configuración del componente API.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ApiComponent {
>         <<attribute>>
>         +string path
>         +array methods
>         +bool requiresAuth
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `AppComponent` se utiliza principalmente como un atributo para decorar clases que representan endpoints de API. Aunque no tiene relaciones directas con otras clases en el input proporcionado, su propósito es facilitar la configuración y gestión de rutas y comportamientos en el sistema de ruteo del framework Lego.

## 👥 Es referenciado por

- [[api-route-discovery|ApiRouteDiscovery]] *(const_fetch, type_hint)*
- [[auth-groups-config-component|AuthGroupsConfigComponent]] *(attribute)*
- [[auth-groups-config-create-component|AuthGroupsConfigCreateComponent]] *(attribute)*
- [[auth-groups-config-edit-component|AuthGroupsConfigEditComponent]] *(attribute)*
- [[automation-component|AutomationComponent]] *(attribute)*
- [[component-context-trait|ComponentContextTrait]] *(const_fetch)*
- [[example-create-component|ExampleCreateComponent]] *(attribute)*
- [[example-crud-component|ExampleCrudComponent]] *(attribute)*
- [[example-edit-component|ExampleEditComponent]] *(attribute)*
- [[home-component|HomeComponent]] *(attribute)*
- [[menu-config-component|MenuConfigComponent]] *(attribute)*
- [[roles-config-component|RolesConfigComponent]] *(attribute)*
- [[roles-config-create-component|RolesConfigCreateComponent]] *(attribute)*
- [[roles-config-edit-component|RolesConfigEditComponent]] *(attribute)*
- [[tools-create-component|ToolsCreateComponent]] *(attribute)*
- [[tools-crud-component|ToolsCrudComponent]] *(attribute)*
- [[tools-edit-component|ToolsEditComponent]] *(attribute)*
- [[users-config-component|UsersConfigComponent]] *(attribute)*
- [[users-config-create-component|UsersConfigCreateComponent]] *(attribute)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.