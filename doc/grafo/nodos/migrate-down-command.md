---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/MigrateDownCommand.php
loc: 148
deps: 1
dependents: 0
responsabilidad: Orquesta el rollback de migraciones, permitiendo revertir la última batch o una específica, gestionando la interacción con la base de datos y los archivos de migración.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# MigrateDownCommand

`Core\Commands\MigrateDownCommand`

📁 [Core/Commands/MigrateDownCommand.php](../../../Core/Commands/MigrateDownCommand.php)

> [!abstract] Responsabilidad
> Orquesta el rollback de migraciones, permitiendo revertir la última batch o una específica, gestionando la interacción con la base de datos y los archivos de migración.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MigrateDownCommand` existe para gestionar el proceso de rollback de migraciones en el framework Lego. Este comando es crucial para permitir a los desarrolladores revertir cambios en la base de datos, ya sea deshaciendo la última batch de migraciones o una específica. La necesidad de este comando surge por la complejidad y el riesgo asociado con realizar cambios estructurales en bases de datos, lo que requiere un mecanismo robusto para revertir esos cambios si algo sale mal.
> 
> ### Métodos principales
> 
> 1. **execute()**: Este es el método principal del comando. Se encarga de determinar si se debe deshacer una migración específica o la última batch de migraciones. Dependiendo de los argumentos proporcionados, invoca a `rollbackSpecificMigration()` o `rollbackLastBatch()`.
> 
> 2. **rollbackSpecificMigration(string $filename)**: Este método se ocupa de revertir una migración específica. Primero verifica si el archivo de migración existe y si ha sido ejecutado en la base de datos. Luego, carga la clase de la migración y ejecuta su método `down()`. Después de eso, elimina el registro de la migración de la tabla `migrations` en la base de datos.
> 
> 3. **rollbackLastBatch()**: Este método deshace la última batch de migraciones realizadas. Obtiene el número máximo de batch desde la tabla `migrations`, recupera todas las migraciones de ese batch y las procesa una por una utilizando `rollbackSpecificMigration()`. Muestra un resumen final del proceso, indicando cuántas migraciones se deshicieron con éxito y cuántas fallaron.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant CLI as Command Line Interface
>     participant Command as MigrateDownCommand
>     participant DB as Database
>     participant Capsule as Illuminate\Database\Capsule\Manager
>     
>     CLI->>Command: php lego migrate:down -f migration.php
>     Command->>Command: execute()
>     Command->>Command: rollbackSpecificMigration(migration.php)
>     Command->>DB: Check if migration exists in database
>     DB-->>Command: Migration found
>     Command->>Capsule: require migration file
>     Capsule-->>Command: Migration class loaded
>     Command->>MigrationClass: down()
>     MigrationClass-->>Command: Execute rollback logic
>     Command->>DB: Delete migration record from migrations table
>     DB-->>Command: Record deleted
>     Command-->>CLI: Rollback successful
> ```
> 
> ### Cómo encaja
> 
> La clase `MigrateDownCommand` se integra como parte del sistema de migraciones de Lego, que es un componente crucial para la gestión de cambios estructurales en las bases de datos. Este comando funciona junto con otros comandos relacionados con migraciones (como `MigrateUpCommand`) para proporcionar una herramienta completa y robusta para el manejo de versiones de bases de datos. La clase se extiende de `CoreCommand`, lo que la convierte en un comando ejecutable a través de la interfaz de línea de comandos, permitiendo a los desarrolladores realizar operaciones de rollback con facilidad y control.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.