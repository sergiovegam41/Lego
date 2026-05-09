---
tipo: abstract-class
capa: core-controllers
namespace: Core\Controllers
archivo: Core/Controllers/CoreViewController.php
loc: 27
deps: 0
dependents: 0
responsabilidad: Define la clase abstracta base para controladores, validando y ejecutando métodos HTTP definidos.
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/core-controllers
---
# CoreViewController

`Core\Controllers\CoreViewController`

📁 [Core/Controllers/CoreViewController.php](../../../Core/Controllers/CoreViewController.php)

> [!abstract] Responsabilidad
> Define la clase abstracta base para controladores, validando y ejecutando métodos HTTP definidos.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `CoreViewController` es una abstracción fundamental en el sistema de controladores del framework Lego. Su principal objetivo es encapsular la lógica común y la validación de métodos HTTP permitidos, lo que facilita la creación de nuevos controladores específicos sin tener que repetir código. Al definir un conjunto estándar de métodos HTTP (`get`, `put`, `delete`, `post`) y proporcionar una estructura para validarlos, esta clase asegura que todos los controladores sigan un patrón consistente y cumplan con las expectativas del sistema.
> 
> ### Métodos principales
> 
> 1. **`getMethod($request, $accion)`**
>    - Este método es el punto de entrada principal para manejar solicitudes HTTP. Recibe una solicitud (`$request`) y una acción (`$accion`). Primero valida si la acción está permitida utilizando el método `validateMethod`. Si la acción no es válida, retorna un error; de lo contrario, ejecuta el método correspondiente.
> 
> 2. **`validateMethod($accion)`**
>    - Este método privado se encarga de validar que la acción proporcionada esté dentro del conjunto de métodos HTTP permitidos (`get`, `put`, `delete`, `post`). Si la acción no está en la lista, retorna un mensaje de error indicando que la acción no es permitida; si está en la lista, retorna `false`.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class CoreViewController {
>         <<abstract>>
>         +arrayMethods
>         +getMethod($request, $accion)
>         -validateMethod($accion)
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `CoreViewController` se integra como una base para la creación de controladores específicos dentro del sistema. Aunque no tiene clases que la extiendan directamente en el input proporcionado, su estructura y lógica servirían como un modelo para cualquier controlador futuro que necesite manejar solicitudes HTTP de manera consistente. Al encapsular la validación de métodos, facilita la mantenibilidad del código y asegura que todas las acciones sean procesadas de acuerdo con los estándares definidos en el sistema.

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.