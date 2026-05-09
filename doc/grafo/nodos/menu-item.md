---
tipo: model
capa: app-models
namespace: App\Models
archivo: App/Models/MenuItem.php
loc: 286
deps: 0
dependents: 13
responsabilidad: Define y gestiona la estructura jerárquica de un menú con múltiples niveles, incluyendo relaciones padre-hijo, ordenamiento y visibilidad, utilizando Eloquent ORM.
tags:
  - grafo
  - grafo/tipo/model
  - grafo/capa/app-models
---
# MenuItem

`App\Models\MenuItem`

📁 [App/Models/MenuItem.php](../../../App/Models/MenuItem.php)

> [!abstract] Responsabilidad
> Define y gestiona la estructura jerárquica de un menú con múltiples niveles, incluyendo relaciones padre-hijo, ordenamiento y visibilidad, utilizando Eloquent ORM.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MenuItem` es fundamental para gestionar la estructura jerárquica de un menú en el sistema de dashboards del framework PHP Lego. Su principal objetivo es proporcionar una representación robusta y flexible de los elementos del menú, permitiendo múltiples niveles de profundidad, ordenamiento y visibilidad. Dado que el menú puede tener una estructura compleja con ramificaciones infinitas, `MenuItem` se encarga de manejar estas relaciones padre-hijo, asegurando que cada elemento pueda ser ubicado correctamente en la jerarquía del menú.
> 
> ### Métodos principales
> 
> 1. **children()**: Define la relación de uno a muchos entre un `MenuItem` y sus hijos (`HasMany`). Esto permite acceder fácilmente a todos los elementos secundarios de un menú específico, ordenados por su posición (`display_order`).
> 
> 2. **parent()**: Establece la relación inversa de uno a uno entre un `MenuItem` y su padre (`BelongsTo`). Esta relación es crucial para navegar hacia arriba en la jerarquía del menú.
> 
> 3. **getTree()**: Este método estático retorna el árbol completo del menú, incluyendo todos los niveles de hijos anidados, pero solo aquellos que son visibles (`visible()`). Es útil para renderizar el menú en la interfaz de usuario.
> 
> 4. **getNextOrder($parentId = null)**: Calcula el siguiente orden disponible para un nuevo elemento dentro de un nivel específico del menú. Esto asegura que los elementos se inserten correctamente en su posición correspondiente.
> 
> 5. **getEffectiveLabelAttribute()**: Este método accesorio retorna el label efectivo de un `MenuItem`. Si el item tiene hijos y un `index_label` definido, este será usado; de lo contrario, se usará el `label` normal.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class MenuItem {
>         +string id
>         +string parent_id
>         +string label
>         +string index_label
>         +string route
>         +string icon
>         +int display_order
>         +int level
>         +bool is_visible
>         +bool is_dynamic
>         +children() HasMany
>         +parent() BelongsTo
>         +getTree(): array
>         +getNextOrder(?string $parentId = null): int
>         +getEffectiveLabelAttribute(): string
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `MenuItem` se integra como un componente central del sistema de gestión de menús. Se utiliza por múltiples partes del códigobase para construir, consultar y manipular la estructura del menú. Dado que no hay otras clases mencionadas en las relaciones entrantes, es probable que esta clase sea utilizada directamente por controladores o servicios que manejan la lógica de negocio relacionada con el menú.

## 👥 Es referenciado por

- [[config-reset-command|ConfigResetCommand]] *(static_call)*
- [[main-component|MainComponent]] *(static_call, type_hint)*
- [[menu-config-component|MenuConfigComponent]] *(static_call)*
- [[menu-config-controller|MenuConfigController]] *(static_call, type_hint)*
- [[menu-helper|MenuHelper]] *(static_call)*
- [[menu-item-hierarchy-controller|MenuItemHierarchyController]] *(static_call, type_hint)*
- [[menu-search-controller|MenuSearchController]] *(static_call)*
- [[menu-structure-controller|MenuStructureController]] *(static_call, type_hint)*
- [[menu-system-items-controller|MenuSystemItemsController]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.