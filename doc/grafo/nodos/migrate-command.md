---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/MigrateCommand.php
loc: 218
deps: 1
dependents: 1
responsabilidad: Orquesta la ejecución de migraciones de base de datos, gestionando el orden y registro de cambios estructurales.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# MigrateCommand

`Core\Commands\MigrateCommand`

📁 [Core/Commands/MigrateCommand.php](../../../Core/Commands/MigrateCommand.php)

> [!abstract] Responsabilidad
> Orquesta la ejecución de migraciones de base de datos, gestionando el orden y registro de cambios estructurales.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MigrateCommand` existe para gestionar y ejecutar las migraciones de la base de datos en el framework PHP Lego. Este comando es crucial porque asegura que los cambios estructurales en la base de datos se apliquen correctamente, siguiendo un orden basado en timestamps. Esto permite mantener una evolución controlada y organizada de la estructura de la base de datos a medida que el proyecto crece y evoluciona.
> 
> ### Métodos principales
> 
> 1. **execute()**: Este es el método principal del comando. Se encarga de ejecutar todas las migraciones pendientes en orden, asegurando que cada archivo de migración se procese correctamente. Si ocurre algún error durante la ejecución de una migración, el proceso se detiene y se informa el error.
> 
> 2. **ensureMigrationsTableExists()**: Este método verifica si existe la tabla `migrations` en la base de datos. Si no existe, intenta crearla utilizando un archivo de migración específico o creándola manualmente. Esta tabla es fundamental para rastrear qué migraciones han sido ejecutadas.
> 
> 3. **getNextBatchNumber()**: Obtiene el número del próximo lote de migraciones. Esto se utiliza para agrupar las migraciones que se ejecutan en la misma sesión, facilitando su seguimiento y gestión.
> 
> 4. **getMigrationFiles()**: Recopila todos los archivos de migración desde el directorio especificado, ordenándolos por nombre (que incluye un timestamp). Esta lista de archivos es utilizada para determinar qué migraciones aún no han sido ejecutadas.
> 
> 5. **getExecutedMigrations()**: Obtiene la lista de todas las migraciones que ya han sido ejecutadas desde la tabla `migrations`. Esto permite identificar qué migraciones pendientes aún necesitan ser procesadas.
> 
> 6. **executeMigration(string $filename)**: Ejecuta un archivo de migración específico. Carga el archivo, verifica si contiene el método `up()`, lo ejecuta y registra la migración en la tabla `migrations`. Si ocurre algún error durante la ejecución, se informa y se retorna un estado de fallo.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant MigrateCommand as MigrateCommand
>     participant Capsule as Capsule
>     participant DB as PostgreSQL
>     Client->>MigrateCommand: CLI migrate
>     MigrateCommand->>MigrateCommand: ensureMigrationsTableExists()
>     MigrateCommand->>Capsule: schema()->hasTable('migrations')
>     alt Table exists
>         MigrateCommand->>MigrateCommand: getNextBatchNumber()
>     else Table does not exist
>         MigrateCommand->>MigrateCommand: create migrations table
>         MigrateCommand->>Capsule: schema()->create('migrations', ...)
>     end
>     MigrateCommand->>MigrateCommand: getMigrationFiles()
>     MigrateCommand->>MigrateCommand: getExecutedMigrations()
>     MigrateCommand->>MigrateCommand: filter pending migrations
>     loop foreach pending migration
>         MigrateCommand->>MigrateCommand: executeMigration($filename)
>         alt Migration successful
>             MigrateCommand->>Capsule: table('migrations')->insert(...)
>         else Migration fails
>             MigrateCommand->>Client: error message
>         end
>     end
>     MigrateCommand-->>Client: migration summary
> ```
> 
> ### Cómo encaja
> 
> La clase `MigrateCommand` se integra como parte del sistema de gestión de comandos del framework PHP Lego. Es instanciada y ejecutada por la clase `InitCommand`, que es responsable de inicializar el entorno y ejecutar los comandos necesarios para configurar y migrar la base de datos. Este comando se conecta con la capa de acceso a datos a través del componente `Capsule`, que interactúa directamente con la base de datos PostgreSQL. La tabla `migrations` es utilizada para rastrear el estado de las migraciones, asegurando que cada cambio estructural en la base de datos se aplique de manera controlada y ordenada.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

## 👥 Es referenciado por

- [[init-command|InitCommand]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.