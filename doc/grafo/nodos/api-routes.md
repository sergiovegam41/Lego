---
tipo: class
capa: core-attributes
namespace: Core\Attributes
archivo: Core/Attributes/ApiRoutes.php
loc: 221
deps: 0
dependents: 9
responsabilidad: Define y registra automáticamente rutas de controladores API con presets y acciones personalizables, aplicando middlewares y prefijos según configuración.
atributos:
  - Attribute
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-attributes
  - grafo/atributo/Attribute
---
# ApiRoutes

`Core\Attributes\ApiRoutes`

📁 [Core/Attributes/ApiRoutes.php](../../../Core/Attributes/ApiRoutes.php)

> [!abstract] Responsabilidad
> Define y registra automáticamente rutas de controladores API con presets y acciones personalizables, aplicando middlewares y prefijos según configuración.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ApiRoutes` es fundamental para el framework **Lego**, ya que facilita la definición y registro automático de rutas de controladores API con presets y acciones personalizadas, aplicando middlewares y prefijos según la configuración. Su principal objetivo es reducir la verbosidad en la declaración de rutas, permitiendo a los desarrolladores definir rápidamente endpoints para APIs REST, webhooks, reportes e integraciones. Esto no solo mejora la productividad sino que también mantiene un código más limpio y mantenible.
> 
> ### Métodos principales
> 
> 1. **`__construct()`**: Este es el constructor de la clase que inicializa los atributos necesarios para definir las rutas. Valida el endpoint y el preset, asegurándose de que no contengan errores básicos como incluir el prefijo `/api`.
> 
> 2. **`getResolvedActions()`**: Este método combina las acciones del preset seleccionado con cualquier acción personalizada definida por el usuario, excluyendo las acciones especificadas en `exclude`. Es crucial para determinar qué rutas y métodos HTTP están disponibles.
> 
> 3. **`getFullEndpoint()`**: Genera la ruta completa de la API incluyendo el prefijo `/api` y cualquier subprefijo adicional definido por el usuario. Esto asegura que todas las rutas sigan una estructura consistente y predecible.
> 
> 4. **`hasAction(string $action)`**: Verifica si una acción específica está habilitada para la ruta actual. Este método es útil para controlar el acceso a diferentes endpoints de manera dinámica.
> 
> 5. **`getMethodsForAction(string $action)`**: Devuelve los métodos HTTP permitidos para una acción específica. Esto es fundamental para definir correctamente las rutas y garantizar que solo se acepten solicitudes con los métodos correctos.
> 
> 6. **`toArray()`**: Este método devuelve la configuración de la ruta como un array, lo cual es útil para el debugging y la introspección del sistema. Permite ver rápidamente todos los atributos y valores asociados a una instancia de `ApiRoutes`.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ApiRoutes {
>         +string $endpoint
>         +string $preset
>         +array $actions
>         +array $exclude
>         +array $middleware
>         +?string $prefix
>         +bool $enabled
>         +__construct(string $endpoint, string $preset, array $actions, array $exclude, array $middleware, ?string $prefix, bool $enabled)
>         +getResolvedActions() : array
>         +getFullEndpoint() : string
>         +hasAction(string $action) : bool
>         +getMethodsForAction(string $action) : array
>         +toArray() : array
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ApiRoutes` se integra como parte del sistema de ruteo y controladores de **Lego**. Funciona junto con otros componentes no mencionados aquí, pero su principal conexión es con los controladores que la utilizan para definir sus rutas API. Al permitir la declaración de rutas de manera declarativa y automática, `ApiRoutes` facilita la creación de APIs complejas con una mínima configuración. Esto hace que sea un componente clave en el desarrollo rápido y eficiente de aplicaciones basadas en **Lego**, donde se requiere una alta flexibilidad y escalabilidad en la definición de endpoints.

## 👥 Es referenciado por

- [[api-controller-router|ApiControllerRouter]] *(const_fetch, type_hint, returns)*
- [[auth-groups-controller|AuthGroupsController]] *(attribute)*
- [[example-crud-controller|ExampleCrudController]] *(attribute)*
- [[menu-config-controller|MenuConfigController]] *(attribute)*
- [[roles-config-controller|RolesConfigController]] *(attribute)*
- [[tools-controller|ToolsController]] *(attribute)*
- [[users-config-controller|UsersConfigController]] *(attribute)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.