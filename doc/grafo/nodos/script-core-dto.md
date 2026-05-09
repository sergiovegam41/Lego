---
tipo: class
capa: core
namespace: Core\Dtos
archivo: Core/Dtos/ScriptCoreDTO.php
loc: 18
deps: 0
dependents: 8
responsabilidad: Encapsula los datos necesarios para la ejecución de un script, incluyendo su ruta y argumentos.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# ScriptCoreDTO

`Core\Dtos\ScriptCoreDTO`

📁 [Core/Dtos/ScriptCoreDTO.php](../../../Core/Dtos/ScriptCoreDTO.php)

> [!abstract] Responsabilidad
> Encapsula los datos necesarios para la ejecución de un script, incluyendo su ruta y argumentos.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `ScriptCoreDTO` existe para encapsular los datos necesarios para la ejecución de un script, específicamente su ruta y argumentos. Este diseño facilita la transmisión de estos datos entre diferentes componentes del sistema de manera estructurada y coherente. La necesidad de centralizar estos datos en un objeto DTO surge por la complejidad creciente de los sistemas que requieren la ejecución de scripts con múltiples argumentos, lo cual puede volverse propenso a errores si no se maneja adecuadamente.
> 
> ### Métodos principales
> 
> - **`__construct($path, $arg)`**: Este es el constructor de la clase. Inicializa las propiedades `$path` y `$arg` con los valores proporcionados. Esencial para crear una instancia de `ScriptCoreDTO` con los datos necesarios para ejecutar un script.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ScriptCoreDTO {
>         +$path : string
>         +$arg : array
>         +__construct($path, $arg)
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `ScriptCoreDTO` se utiliza como un contenedor de datos estructurados por múltiples componentes del sistema, como `CoreComponent`, `AutomationComponent`, `HeaderComponent`, `MainComponent`, `MenuComponent`, `HomeComponent`, `LoginComponent` y `TableComponent`. Estos componentes instancian `ScriptCoreDTO` para encapsular la ruta y los argumentos de los scripts que necesitan ejecutar. Al centralizar estos datos en un objeto DTO, se mejora la claridad y mantenibilidad del código, ya que cada componente puede trabajar con una interfaz uniforme para acceder a la información necesaria para ejecutar sus respectivos scripts.

## 👥 Es referenciado por

- [[automation-component|AutomationComponent]] *(instantiates)*
- [[core-component|CoreComponent]] *(instantiates)*
- [[header-component|HeaderComponent]] *(instantiates)*
- [[home-component|HomeComponent]] *(instantiates)*
- [[login-component|LoginComponent]] *(instantiates)*
- [[main-component|MainComponent]] *(instantiates)*
- [[menu-component|MenuComponent]] *(instantiates)*
- [[table-component|TableComponent]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.