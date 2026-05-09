---
tipo: trait
capa: core-traits
namespace: Core\Traits
archivo: Core/Traits/ComponentContextTrait.php
loc: 255
deps: 2
dependents: 1
responsabilidad: Proporciona un contexto automático para componentes UI, derivando información como rutas y relaciones desde atributos y estructura de carpetas, y expone esta información al JavaScript para uso en el frontend.
tags:
  - grafo
  - grafo/tipo/trait
  - grafo/capa/core-traits
---
# ComponentContextTrait

`Core\Traits\ComponentContextTrait`

📁 [Core/Traits/ComponentContextTrait.php](../../../Core/Traits/ComponentContextTrait.php)

> [!abstract] Responsabilidad
> Proporciona un contexto automático para componentes UI, derivando información como rutas y relaciones desde atributos y estructura de carpetas, y expone esta información al JavaScript para uso en el frontend.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> El `ComponentContextTrait` es un componente clave del framework **Lego** que se encarga de proporcionar un contexto automático para los componentes UI. Su principal objetivo es eliminar la necesidad de "magic strings" y configuraciones manuales al exponer información relevante como rutas, IDs y relaciones a través de JavaScript. Este trait facilita el desarrollo al centralizar la lógica de derivación de contextos en un solo lugar, lo que mejora la mantenibilidad y reduce errores.
> 
> ### Métodos principales
> 
> 1. **getComponentContext()**: Calcula y devuelve el contexto completo del componente basado en atributos y estructura de carpetas. Utiliza reflexión para obtener información sobre la clase y luego deriva rutas, IDs y relaciones automáticamente.
>   
> 2. **extractRouteFromAttribute()**: Extrae la ruta del atributo `#[ApiComponent]` y ajusta el prefijo si es necesario. Si no existe el atributo, deriva la ruta desde el namespace de la clase.
> 
> 3. **deriveRouteFromNamespace()**: Deriva la ruta del componente a partir del namespace de la clase, convirtiendo nombres CamelCase a kebab-case para mantener una consistencia en las rutas.
> 
> 4. **renderContext()**: Renderiza un script HTML que expone el contexto completo al JavaScript del frontend. Esto permite que el JavaScript acceda automáticamente a información como rutas y IDs sin necesidad de hardcoding.
> 
> 5. **findParentMenuId()**: Determina el ID del menú padre para el componente. Si la clase implementa `ScreenInterface`, obtiene el ID desde la base de datos; de lo contrario, deriva el ID desde la ruta del componente.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ComponentContextTrait {
>         <<trait>>
>         +getComponentContext() array
>         +renderContext() string
>         +getContextId() string
>         +getContextApiRoute() string
>         +getContextParentMenuId() ?string
>     }
> ```
> 
> ### Cómo encaja
> 
> El `ComponentContextTrait` se utiliza dentro de la clase `CoreComponent`, que es una abstracción central para todos los componentes UI en el framework **Lego**. Al incluir este trait, `CoreComponent` obtiene automáticamente un contexto completo que expone información relevante al JavaScript del frontend. Esto facilita el desarrollo y mantiene una estructura coherente entre diferentes componentes, asegurando que cada componente tenga acceso a su propia información sin necesidad de configuraciones manuales.

## ⚡ Llamadas estáticas

- [[menu-helper|MenuHelper]]

## 🔗 Constantes referenciadas

- [[api-component|ApiComponent]]

## 👥 Es referenciado por

- [[core-component|CoreComponent]] *(uses_trait)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.