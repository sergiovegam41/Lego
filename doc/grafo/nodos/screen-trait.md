---
tipo: trait
capa: core-traits
namespace: Core\Traits
archivo: Core/Traits/ScreenTrait.php
loc: 167
deps: 1
dependents: 15
responsabilidad: Define y gestiona las propiedades de un screen, proporcionando métodos para obtener metadata desde la base de datos o constantes predeterminadas.
tags:
  - grafo
  - grafo/tipo/trait
  - grafo/capa/core-traits
---
# ScreenTrait

`Core\Traits\ScreenTrait`

📁 [Core/Traits/ScreenTrait.php](../../../Core/Traits/ScreenTrait.php)

> [!abstract] Responsabilidad
> Define y gestiona las propiedades de un screen, proporcionando métodos para obtener metadata desde la base de datos o constantes predeterminadas.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ScreenTrait` existe para proporcionar un marco consistente y centralizado para gestionar la metadata de pantallas dentro del sistema Lego. Este trait define constantes estáticas que representan la identidad del screen, asegurando que la información del menú y rutas se obtenga desde la base de datos o valores por defecto. La filosofía de Lego enfatiza que la base de datos es la fuente de verdad para todas las configuraciones, lo que facilita una gestión centralizada y mantenible de los componentes.
> 
> ### Métodos principales
> 
> 1. **getScreenMetadata()**
>    - Obtiene toda la metadata del screen en un array, incluyendo el ID, etiqueta, icono, ruta, visibilidad, dinamismo y orden. Utiliza constantes estáticas definidas por las clases que usan este trait, con valores predeterminados si no están definidos.
> 
> 2. **getParentIdFromDatabase()**
>    - Consulta la base de datos para obtener el `parent_id` del screen utilizando su `SCREEN_ID`. Este método es crucial para asegurar que la jerarquía del menú se obtenga de manera consistente y actualizada desde la base de datos.
> 
> 3. **getScreenId()**
>    - Retorna el identificador único del screen, verificando primero si está definido en la clase que usa este trait. Lanza una excepción si no está definido, lo cual es una medida preventiva para garantizar la integridad de los datos.
> 
> 4. **getScreenRoute()**
>    - Similar a `getScreenId()`, pero retorna la ruta del componente asociado al screen. También verifica que esté definida y lanza una excepción si no lo está.
> 
> 5. **isVisible()**
>    - Verifica si el screen es visible en el menú, utilizando la constante `SCREEN_VISIBLE` si está definida, o retornando `true` por defecto si no lo está.
> 
> 6. **isDynamic()**
>    - Determina si el screen es dinámico (activado por contexto), utilizando la constante `SCREEN_DYNAMIC` si está definida, o retornando `false` por defecto si no lo está.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ScreenTrait {
>         <<trait>>
>         +getScreenMetadata() array
>         +getParentIdFromDatabase(string) ?string
>         +getScreenId() string
>         +getScreenRoute() string
>         +isVisible() bool
>         +isDynamic() bool
>         +getParentId() ?string
>         +getMenuGroupId() ?string
>     }
>     
>     class AuthGroupsConfigComponent {
>         use ScreenTrait
>     }
>     
>     class AuthGroupsConfigCreateComponent {
>         use ScreenTrait
>     }
>     
>     class AuthGroupsConfigEditComponent {
>         use ScreenTrait
>     }
>     
>     class ExampleCreateComponent {
>         use ScreenTrait
>     }
>     
>     class ExampleEditComponent {
>         use ScreenTrait
>     }
>     
>     class ExampleCrudComponent {
>         use ScreenTrait
>     }
>     
>     class MenuConfigComponent {
>         use ScreenTrait
>     }
>     
>     class RolesConfigCreateComponent {
>         use ScreenTrait
>     }
>     
>     class RolesConfigEditComponent {
>         use ScreenTrait
>     }
>     
>     class RolesConfigComponent {
>         use ScreenTrait
>     }
>     
>     class ToolsCreateComponent {
>         use ScreenTrait
>     }
>     
>     class ToolsEditComponent {
>         use ScreenTrait
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ScreenTrait` se utiliza como un trait por 15 componentes diferentes, todos relacionados con la configuración y gestión de pantallas. Estos componentes incluyen configuraciones de grupos de autenticación, roles, menús y herramientas. Al usar este trait, cada componente obtiene una serie de métodos y constantes que facilitan la obtención de metadata del screen desde la base de datos o valores por defecto. Esto asegura una consistencia en cómo se manejan las pantallas y su metadata, simplificando el mantenimiento y la escalabilidad del sistema Lego.

## ⚡ Llamadas estáticas

- [[menu-helper|MenuHelper]]

## 👥 Es referenciado por

- [[auth-groups-config-component|AuthGroupsConfigComponent]] *(uses_trait)*
- [[auth-groups-config-create-component|AuthGroupsConfigCreateComponent]] *(uses_trait)*
- [[auth-groups-config-edit-component|AuthGroupsConfigEditComponent]] *(uses_trait)*
- [[example-create-component|ExampleCreateComponent]] *(uses_trait)*
- [[example-crud-component|ExampleCrudComponent]] *(uses_trait)*
- [[example-edit-component|ExampleEditComponent]] *(uses_trait)*
- [[menu-config-component|MenuConfigComponent]] *(uses_trait)*
- [[roles-config-component|RolesConfigComponent]] *(uses_trait)*
- [[roles-config-create-component|RolesConfigCreateComponent]] *(uses_trait)*
- [[roles-config-edit-component|RolesConfigEditComponent]] *(uses_trait)*
- [[tools-create-component|ToolsCreateComponent]] *(uses_trait)*
- [[tools-crud-component|ToolsCrudComponent]] *(uses_trait)*
- [[tools-edit-component|ToolsEditComponent]] *(uses_trait)*
- [[users-config-component|UsersConfigComponent]] *(uses_trait)*
- [[users-config-create-component|UsersConfigCreateComponent]] *(uses_trait)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.