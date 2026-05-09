---
tipo: class
capa: core
namespace: Core\Config
archivo: Core/Config/MenuStructure.php
loc: 433
deps: 16
dependents: 1
responsabilidad: Define la estructura del menú de navegación para el dashboard, utilizando constantes de los componentes y calculando automáticamente propiedades como parent_id y level basado en la jerarquía anidada.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# MenuStructure

`Core\Config\MenuStructure`

📁 [Core/Config/MenuStructure.php](../../../Core/Config/MenuStructure.php)

> [!abstract] Responsabilidad
> Define la estructura del menú de navegación para el dashboard, utilizando constantes de los componentes y calculando automáticamente propiedades como parent_id y level basado en la jerarquía anidada.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MenuStructure` existe para centralizar y gestionar la configuración del menú de navegación del dashboard de la aplicación. Su principal objetivo es eliminar duplicaciones de strings y garantizar que todas las referencias al menú estén sincronizadas con los componentes del sistema. Al utilizar constantes definidas en cada componente, `MenuStructure` asegura que cualquier cambio en el identificador o etiqueta de un componente se refleje automáticamente en la estructura del menú. Además, calcula automáticamente propiedades como `parent_id` y `level` basado en la jerarquía anidada de los elementos del menú, lo que simplifica la gestión y mantiene una estructura coherente.
> 
> ### Métodos principales
> 
> 1. **get()**: Este método es el punto de entrada para obtener la estructura completa del menú. Se encarga de construir un array que representa toda la jerarquía del menú, incluyendo grupos y subelementos. Utiliza constantes definidas en los componentes para asegurar que todas las propiedades estén sincronizadas.
> 
> 2. **getGroupIdFromRoute()**: Este método calcula el `id` de un grupo basado en la ruta (`route`) del componente. Es utilizado para generar identificadores únicos y coherentes para los grupos de menú, lo que facilita la gestión y la navegación.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class MenuStructure {
>         +get(): array
>         +getGroupIdFromRoute(route: string): string
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `MenuStructure` se integra como una fuente única de verdad para la configuración del menú. Se utiliza por otras partes del sistema que necesitan acceder a la estructura del menú, como los controladores que renderizan el dashboard o los componentes que manejan la navegación. Al centralizar la definición del menú en esta clase, se garantiza una coherencia y un mantenimiento más sencillo, ya que cualquier cambio en la configuración del menú se realiza en un solo lugar.

## ⚡ Llamadas estáticas

- [[screen-registry|ScreenRegistry]]

## 🔗 Constantes referenciadas

- [[auth-groups-config-component|AuthGroupsConfigComponent]]
- [[auth-groups-config-create-component|AuthGroupsConfigCreateComponent]]
- [[auth-groups-config-edit-component|AuthGroupsConfigEditComponent]]
- [[example-create-component|ExampleCreateComponent]]
- [[example-crud-component|ExampleCrudComponent]]
- [[example-edit-component|ExampleEditComponent]]
- [[menu-config-component|MenuConfigComponent]]
- [[roles-config-component|RolesConfigComponent]]
- [[roles-config-create-component|RolesConfigCreateComponent]]
- [[roles-config-edit-component|RolesConfigEditComponent]]
- [[tools-create-component|ToolsCreateComponent]]
- [[tools-crud-component|ToolsCrudComponent]]
- [[tools-edit-component|ToolsEditComponent]]
- [[users-config-component|UsersConfigComponent]]
- [[users-config-create-component|UsersConfigCreateComponent]]

## 👥 Es referenciado por

- [[config-reset-command|ConfigResetCommand]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.