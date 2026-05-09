---
tipo: class
capa: core
namespace: Core\Models
archivo: Core/Models/StatusCodes.php
loc: 89
deps: 0
dependents: 12
responsabilidad: Define constantes y métodos para manejar códigos de estado HTTP, incluyendo mensajes asociados y validaciones.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# StatusCodes

`Core\Models\StatusCodes`

📁 [Core/Models/StatusCodes.php](../../../Core/Models/StatusCodes.php)

> [!abstract] Responsabilidad
> Define constantes y métodos para manejar códigos de estado HTTP, incluyendo mensajes asociados y validaciones.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `StatusCodes` existe para centralizar y facilitar el manejo de códigos de estado HTTP dentro del framework Lego. Proporciona una única fuente de verdad para los códigos y mensajes asociados, lo que mejora la consistencia y legibilidad del código. Además, ofrece métodos útiles para validar y generar encabezados HTTP basados en estos códigos, simplificando el proceso de manejo de respuestas en diferentes partes del sistema.
> 
> ### Métodos principales
> 
> 1. **httpHeaderFor($code)**: Este método genera una cadena que representa un encabezado HTTP válido a partir de un código de estado dado. Utiliza la constante `$messages` para obtener el mensaje correspondiente al código y lo combina con la versión del protocolo HTTP.
> 
> 2. **getMessageForCode($code)**: Retorna el mensaje asociado a un código de estado específico, consultando el array `$messages`. Esto es útil cuando se necesita proporcionar una descripción más detallada o personalizada de un error o respuesta.
> 
> 3. **isError($code)**: Determina si un código de estado pertenece al rango de errores (4xx y 5xx). Esta validación es crucial para distinguir entre respuestas exitosas y aquellos que indican algún tipo de problema, permitiendo una lógica de manejo adecuada en diferentes partes del sistema.
> 
> 4. **canHaveBody($code)**: Verifica si un código de estado permite tener un cuerpo (body) en la respuesta HTTP. Esto es importante para asegurar que las respuestas se construyan correctamente y no se envíen datos innecesarios o incorrectos, especialmente para códigos como 204 No Content o 304 Not Modified.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class StatusCodes {
>         +httpHeaderFor($code)
>         +getMessageForCode($code)
>         +isError($code)
>         +canHaveBody($code)
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `StatusCodes` se integra como una herramienta centralizada para manejar códigos de estado HTTP, siendo utilizada por múltiples partes del sistema que requieren generar o validar respuestas HTTP. Aunque no está extendida ni implementada por otras clases, sus métodos son invocados directamente en diferentes contextos donde se necesitan operaciones relacionadas con los códigos de estado, como la generación de encabezados, mensajes de error y validaciones de respuesta.

## 👥 Es referenciado por

- [[auth-groups-controller|AuthGroupsController]] *(const_fetch)*
- [[auth-groups-provider|AuthGroupsProvider]] *(const_fetch)*
- [[auth-services-core|AuthServicesCore]] *(const_fetch)*
- [[example-crud-controller|ExampleCrudController]] *(const_fetch)*
- [[files-controller|FilesController]] *(const_fetch)*
- [[menu-config-controller|MenuConfigController]] *(const_fetch)*
- [[menu-item-hierarchy-controller|MenuItemHierarchyController]] *(const_fetch)*
- [[menu-structure-controller|MenuStructureController]] *(const_fetch)*
- [[restful-controller|RestfulController]] *(const_fetch)*
- [[roles-config-controller|RolesConfigController]] *(const_fetch)*
- [[tools-controller|ToolsController]] *(const_fetch)*
- [[users-config-controller|UsersConfigController]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.