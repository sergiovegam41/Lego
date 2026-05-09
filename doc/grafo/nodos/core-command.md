---
tipo: abstract-class
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/CoreCommand.php
loc: 182
deps: 0
dependents: 13
responsabilidad: Define la clase abstracta base para todos los comandos CLI en el framework Lego, proporcionando una interfaz estándar para la creación y ejecución de comandos con soporte para argumentos, opciones y mensajes de salida.
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/core-commands
---
# CoreCommand

`Core\Commands\CoreCommand`

📁 [Core/Commands/CoreCommand.php](../../../Core/Commands/CoreCommand.php)

> [!abstract] Responsabilidad
> Define la clase abstracta base para todos los comandos CLI en el framework Lego, proporcionando una interfaz estándar para la creación y ejecución de comandos con soporte para argumentos, opciones y mensajes de salida.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `CoreCommand` es un componente fundamental del framework Lego, diseñado para proporcionar una interfaz estándar y funcionalidades básicas para todos los comandos de línea de comando (CLI). Su principal objetivo es facilitar la creación y ejecución de comandos, asegurando que cada uno tenga una estructura consistente en términos de argumentos, opciones y manejo de mensajes de salida. Esto permite a desarrolladores nuevos o existentes centrarse más en la lógica específica de sus comandos sin preocuparse por detalles técnicos repetitivos.
> 
> ### Métodos principales
> 
> 1. **`execute()`**: Este es el método abstracto que cada comando debe implementar. Define la acción principal del comando y devuelve un booleano indicando si se ejecutó con éxito o no.
> 
> 2. **`parseArguments()`**: Analiza los argumentos pasados al comando, separándolos en opciones y valores. Esto permite a los comandos manejar diferentes tipos de entrada de manera uniforme.
> 
> 3. **`success()`, `error()`, `info()`, `warning()`, `line()`**: Estos métodos facilitan la salida de mensajes formateados al usuario, utilizando colores para mejorar la legibilidad y distinguir entre diferentes tipos de mensajes (éxito, error, información, advertencia).
> 
> 4. **`confirm()`**: Solicita una confirmación del usuario a través de la línea de comando, lo que es útil para comandos que requieren acción manual o validación.
> 
> 5. **`progressBar()`**: Muestra una barra de progreso simple en la consola, útil para operaciones que llevan tiempo y se benefician de visualización de progreso.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class CoreCommand {
>         <<abstract>>
>         +execute() bool
>         +parseArguments()
>         +success(message: string)
>         +error(message: string)
>         +info(message: string)
>         +warning(message: string)
>         +line(message: string)
>         +confirm(question: string): bool
>         +progressBar(current: int, total: int, message: string)
>     }
>     CoreCommand <|-- ConfigResetCommand
>     CoreCommand <|-- DocsGraphCommand
>     CoreCommand <|-- HelpCommand
>     CoreCommand <|-- InitCommand
>     CoreCommand <|-- InitStorageCommand
>     CoreCommand <|-- MakeComponentCommand
>     CoreCommand <|-- MapRoutesCommand
>     CoreCommand <|-- MigrateCommand
>     CoreCommand <|-- MigrateDownCommand
>     CoreCommand <|-- MigrateStatusCommand
>     CoreCommand <|-- MigrateUpCommand
>     CoreCommand <|-- StorageCheckCommand
> ```
> 
> ### Cómo encaja
> 
> `CoreCommand` actúa como una base común para todos los comandos CLI en el framework Lego. Al proporcionar una interfaz estándar y funcionalidades básicas, asegura que cada comando tenga un comportamiento predecible y fácil de mantener. Las clases que extienden `CoreCommand`, como `ConfigResetCommand` o `MigrateCommand`, implementan el método `execute()` para definir su lógica específica, mientras utilizan los métodos proporcionados por `CoreCommand` para manejar argumentos, opciones y mensajes de salida. Esta estructura permite una gestión eficiente del código y facilita la adición de nuevos comandos en el futuro, ya que todos siguen las mismas directrices y convenciones.

## 👥 Es referenciado por

- [[command-router|CommandRouter]] *(const_fetch)*
- [[config-reset-command|ConfigResetCommand]] *(extends)*
- [[docs-graph-command|DocsGraphCommand]] *(extends)*
- [[help-command|HelpCommand]] *(extends)*
- [[init-command|InitCommand]] *(extends)*
- [[init-storage-command|InitStorageCommand]] *(extends)*
- [[make-component-command|MakeComponentCommand]] *(extends)*
- [[map-routes-command|MapRoutesCommand]] *(extends)*
- [[migrate-command|MigrateCommand]] *(extends)*
- [[migrate-down-command|MigrateDownCommand]] *(extends)*
- [[migrate-status-command|MigrateStatusCommand]] *(extends)*
- [[migrate-up-command|MigrateUpCommand]] *(extends)*
- [[storage-check-command|StorageCheckCommand]] *(extends)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.