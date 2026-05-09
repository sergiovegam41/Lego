---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/MigrateStatusCommand.php
loc: 113
deps: 1
dependents: 0
responsabilidad: Muestra el estado de las migraciones, indicando cuáles han sido ejecutadas y cuáles están pendientes.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# MigrateStatusCommand

`Core\Commands\MigrateStatusCommand`

📁 [Core/Commands/MigrateStatusCommand.php](../../../Core/Commands/MigrateStatusCommand.php)

> [!abstract] Responsabilidad
> Muestra el estado de las migraciones, indicando cuáles han sido ejecutadas y cuáles están pendientes.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MigrateStatusCommand` existe para proporcionar un informe detallado sobre el estado de las migraciones en la base de datos del proyecto. Este comando es crucial para los desarrolladores que necesitan verificar rápidamente cuáles migraciones han sido ejecutadas y cuáles aún están pendientes, facilitando así el seguimiento y la gestión de cambios en la estructura de la base de datos.
> 
> ### Métodos principales
> 
> 1. **execute()**: Este es el método principal del comando. Se encarga de obtener todos los archivos de migración, determinar cuáles han sido ejecutados y cuáles están pendientes, y luego mostrar un resumen detallado en la consola.
> 2. **getMigrationFiles()**: Recorre el directorio de migraciones y devuelve una lista de nombres de archivo de migración ordenados alfabéticamente.
> 3. **getExecutedMigrations()**: Consulta la tabla `migrations` en la base de datos para obtener un listado de todas las migraciones que han sido ejecutadas, incluyendo detalles como el batch y la fecha de ejecución.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant Command as MigrateStatusCommand
>     participant Capsule as Illuminate\Database\Capsule\Manager
>     participant DB as PostgreSQL
> 
>     Client->>Command: php lego migrate:status
>     Command->>Command: execute()
>     Command->>Command: getMigrationFiles()
>     Command->>DB: SELECT * FROM migrations
>     DB-->>Capsule: Migration data
>     Capsule-->>Command: Migration data
>     Command->>Command: getExecutedMigrations()
>     Command->>Client: Display migration status
> ```
> 
> ### Cómo encaja
> 
> La clase `MigrateStatusCommand` se integra dentro del sistema de comandos de la aplicación, extendiendo la clase base `CoreCommand`. Este comando es parte de un conjunto de herramientas para gestionar migraciones, lo que lo hace esencial para el desarrollo y mantenimiento de la base de datos. La interacción con la base de datos se realiza a través del componente `Capsule`, que proporciona una interfaz simplificada para acceder a la tabla `migrations`. Este comando no tiene relaciones salientes adicionales, ya que su funcionalidad está centrada en proporcionar un informe detallado sobre el estado de las migraciones.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.