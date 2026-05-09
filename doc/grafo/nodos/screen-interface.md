---
tipo: interface
capa: core-contracts
namespace: Core\Contracts
archivo: Core/Contracts/ScreenInterface.php
loc: 64
deps: 0
dependents: 16
responsabilidad: Define el contrato para componentes de pantalla en LEGO, especificando métodos para obtener metadata y propiedades únicas como ID y ruta.
tags:
  - grafo
  - grafo/tipo/interface
  - grafo/capa/core-contracts
---
# ScreenInterface

`Core\Contracts\ScreenInterface`

📁 [Core/Contracts/ScreenInterface.php](../../../Core/Contracts/ScreenInterface.php)

> [!abstract] Responsabilidad
> Define el contrato para componentes de pantalla en LEGO, especificando métodos para obtener metadata y propiedades únicas como ID y ruta.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La `ScreenInterface` es un contrato fundamental en el framework Lego, diseñado para garantizar que los componentes de interfaz de usuario que representan pantallas o ventanas cumplan con una serie de requisitos básicos y consistentes. Su principal objetivo es proporcionar una "fuente de verdad" desde el componente mismo, asegurando que la información sobre la pantalla esté centralizada y accesible de manera uniforme. Esto facilita la gestión del menú, evita duplicación de strings mágicos y mejora la documentación interna del sistema.
> 
> ### Métodos principales
> 
> 1. **getScreenMetadata()**
>    - Este método retorna una matriz asociativa con toda la metadata relevante de la pantalla, incluyendo su ID, etiqueta, icono, ruta, padre (si existe), visibilidad, dinamismo y orden. Esta información es crucial para que el menú pueda renderizar correctamente cada pantalla.
> 
> 2. **getScreenId()**
>    - Proporciona el identificador único de la pantalla. Este ID se utiliza en varios contextos, como atributos HTML (`data-menu-item-id`) y módulos, asegurando una consistencia en cómo se manejan las pantallas dentro del sistema.
> 
> 3. **getScreenRoute()**
>    - Retorna la ruta URL asociada a la pantalla. Esta información es vital para la navegación y el enrutamiento, permitiendo que los usuarios accedan a la pantalla correcta desde el menú o directamente a través de URLs específicas.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ScreenInterface {
>         <<interface>>
>         +getScreenMetadata() : array
>         +getScreenId() : string
>         +getScreenRoute() : string
>     }
>     AuthGroupsConfigComponent ..|> ScreenInterface
>     AuthGroupsConfigCreateComponent ..|> ScreenInterface
>     AuthGroupsConfigEditComponent ..|> ScreenInterface
>     ExampleCreateComponent ..|> ScreenInterface
>     ExampleEditComponent ..|> ScreenInterface
>     ExampleCrudComponent ..|> ScreenInterface
>     MenuConfigComponent ..|> ScreenInterface
>     RolesConfigCreateComponent ..|> ScreenInterface
>     RolesConfigEditComponent ..|> ScreenInterface
>     RolesConfigComponent ..|> ScreenInterface
>     ToolsCreateComponent ..|> ScreenInterface
>     ToolsEditComponent ..|> ScreenInterface
> ```
> 
> ### Cómo encaja
> 
> La `ScreenInterface` se integra como un componente central en el sistema Lego, asegurando que todos los componentes de pantalla cumplan con una estructura y comportamiento uniforme. Esto facilita la gestión del menú, ya que cualquier componente que implemente esta interfaz proporcionará automáticamente toda la información necesaria para ser renderizado correctamente. Además, al centralizar la definición de metadata en el propio componente, se evita la duplicación de strings mágicos y se mejora la documentación interna, facilitando el mantenimiento y la comprensión del código.

## 👥 Es referenciado por

- [[auth-groups-config-component|AuthGroupsConfigComponent]] *(implements)*
- [[auth-groups-config-create-component|AuthGroupsConfigCreateComponent]] *(implements)*
- [[auth-groups-config-edit-component|AuthGroupsConfigEditComponent]] *(implements)*
- [[example-create-component|ExampleCreateComponent]] *(implements)*
- [[example-crud-component|ExampleCrudComponent]] *(implements)*
- [[example-edit-component|ExampleEditComponent]] *(implements)*
- [[menu-config-component|MenuConfigComponent]] *(implements)*
- [[roles-config-component|RolesConfigComponent]] *(implements)*
- [[roles-config-create-component|RolesConfigCreateComponent]] *(implements)*
- [[roles-config-edit-component|RolesConfigEditComponent]] *(implements)*
- [[screen-registry|ScreenRegistry]] *(const_fetch)*
- [[tools-create-component|ToolsCreateComponent]] *(implements)*
- [[tools-crud-component|ToolsCrudComponent]] *(implements)*
- [[tools-edit-component|ToolsEditComponent]] *(implements)*
- [[users-config-component|UsersConfigComponent]] *(implements)*
- [[users-config-create-component|UsersConfigCreateComponent]] *(implements)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.