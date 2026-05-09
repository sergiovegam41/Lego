---
tipo: class
capa: core
namespace: Core\Helpers
archivo: Core/Helpers/MenuHelper.php
loc: 104
deps: 1
dependents: 2
responsabilidad: Proporciona métodos estáticos para obtener información de menú desde la base de datos, como parent_id, metadata y verificación de existencia de screen_ids.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# MenuHelper

`Core\Helpers\MenuHelper`

📁 [Core/Helpers/MenuHelper.php](../../../Core/Helpers/MenuHelper.php)

> [!abstract] Responsabilidad
> Proporciona métodos estáticos para obtener información de menú desde la base de datos, como parent_id, metadata y verificación de existencia de screen_ids.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MenuHelper` existe para abstraer y centralizar la lógica relacionada con la obtención de información proceduralmente del menú desde la base de datos. Su principal objetivo es facilitar el acceso a datos como `parent_id`, `metadata` y la existencia de un `SCREEN_ID`. Esto se debe a que en el framework LEGO, la base de datos es considerada la fuente única de verdad para toda la información del menú, lo que permite una consistencia y mantenibilidad más altas.
> 
> ### Métodos principales
> 
> 1. **getParentIdFromScreenId(string $screenId): ?string**
>    - Este método obtiene el `parent_id` de un elemento del menú utilizando su `SCREEN_ID`. Si el elemento no existe o es raíz, retorna `null`.
> 
> 2. **getMenuMetadataFromScreenId(string $screenId): ?array**
>    - Proporciona toda la metadata disponible para un elemento del menú basado en su `SCREEN_ID`, incluyendo detalles como `label`, `icon`, `route` y otros atributos relevantes.
> 
> 3. **screenExists(string $screenId): bool**
>    - Verifica si un determinado `SCREEN_ID` existe en la base de datos, retornando `true` si existe o `false` en caso contrario.
> 
> 4. **getMenuGroupIdFromScreenId(string $screenId): ?string**
>    - Obtiene el ID del grupo del menú asociado a un `SCREEN_ID`. Este método es una adaptación para mantener la compatibilidad con código antiguo, ya que conceptualmente es similar a `getParentIdFromScreenId()`.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class MenuHelper {
>         +static getParentIdFromScreenId(string $screenId): ?string
>         +static getMenuMetadataFromScreenId(string $screenId): ?array
>         +static screenExists(string $screenId): bool
>         +static getMenuGroupIdFromScreenId(string $screenId): ?string
>     }
>     class MenuItem {
>         +find($id)
>         +where($column, $value)
>         +exists()
>     }
>     MenuHelper --> MenuItem: usa
> ```
> 
> ### Cómo encaja
> 
> La clase `MenuHelper` se integra como una herramienta de soporte para otras partes del sistema que necesitan acceder a información del menú. Dado que no hay clases que extiendan, implementen o utilicen esta clase como trait, su uso se limita principalmente a métodos estáticos llamados directamente desde otros componentes del sistema. Esto la hace una clase de utilidad centralizada, facilitando el acceso a datos del menú de manera consistente y eficiente.

## ⚡ Llamadas estáticas

- [[menu-item|MenuItem]]

## 👥 Es referenciado por

- [[component-context-trait|ComponentContextTrait]] *(static_call)*
- [[screen-trait|ScreenTrait]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.