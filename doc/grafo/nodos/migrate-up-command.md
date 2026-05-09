---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/MigrateUpCommand.php
loc: 192
deps: 1
dependents: 0
responsabilidad: Ejecuta migraciones de base de datos hacia arriba, aplicando cambios específicos o pendientes según los parámetros proporcionados.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# MigrateUpCommand

`Core\Commands\MigrateUpCommand`

📁 [Core/Commands/MigrateUpCommand.php](../../../Core/Commands/MigrateUpCommand.php)

> [!abstract] Responsabilidad
> Ejecuta migraciones de base de datos hacia arriba, aplicando cambios específicos o pendientes según los parámetros proporcionados.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `MigrateUpCommand` es un componente crucial del sistema de migraciones en el framework PHP Lego. Su principal función es aplicar cambios a la base de datos, ya sea ejecutando una migración específica o todas las pendientes. Este comando es fundamental para mantener la integridad y actualización de la estructura de la base de datos a lo largo del desarrollo y despliegue de la aplicación.
> 
> ### Métodos principales
> 
> 1. **`execute()`**:
>    - Es el punto de entrada principal del comando. Determina si se debe ejecutar una migración específica o todas las pendientes, basándose en los parámetros proporcionados.
>    
> 2. **`executeSpecificMigration($filename, $direction)`**:
>    - Se encarga de aplicar una migración específica. Verifica la existencia del archivo, carga la migración y ejecuta su método `up()`. También registra el cambio en la tabla de migraciones para evitar duplicados.
>    
> 3. **`executeAllPendingMigrations()`**:
>    - Ejecuta todas las migraciones pendientes que no han sido aplicadas anteriormente. Identifica las migraciones no ejecutadas, las ordena y las aplica secuencialmente.
>    
> 4. **`ensureMigrationsTableExists()`**:
>    - Asegura que la tabla de migraciones exista en la base de datos. Si no existe, se crea con los campos necesarios para registrar cada migración aplicada.
>    
> 5. **`getNextBatchNumber()`**:
>    - Obtiene el número del próximo lote de migraciones. Los lotes son utilizados para agrupar migraciones que se ejecutan en la misma sesión.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant CLI as Command Line Interface
>     participant MigrateUpCommand
>     participant Capsule as Database Capsule
>     participant DB as PostgreSQL
>     
>     CLI->>MigrateUpCommand: php lego migrate:up [-f|--file=]
>     MigrateUpCommand->>MigrateUpCommand: execute()
>     alt Specific file provided?
>         MigrateUpCommand->>MigrateUpCommand: executeSpecificMigration($filename, 'up')
>         MigrateUpCommand->>Capsule: ensureMigrationsTableExists()
>         Capsule-->>DB: CREATE TABLE IF NOT EXISTS migrations
>         MigrateUpCommand->>Capsule: check if migration exists in DB
>         Capsule-->>MigrateUpCommand: migration not executed
>         MigrateUpCommand->>Capsule: load migration file
>         MigrateUpCommand->>MigrationFile: up()
>         MigrationFile-->>MigrateUpCommand: execute SQL changes
>         MigrateUpCommand->>Capsule: insert migration record into DB
>     else All pending migrations?
>         MigrateUpCommand->>MigrateUpCommand: executeAllPendingMigrations()
>         MigrateUpCommand->>Capsule: ensureMigrationsTableExists()
>         Capsule-->>DB: CREATE TABLE IF NOT EXISTS migrations
>         MigrateUpCommand->>Capsule: get executed migrations
>         Capsule-->>MigrateUpCommand: list of executed migrations
>         MigrateUpCommand->>MigrateUpCommand: filter pending migrations
>         loop For each pending migration
>             MigrateUpCommand->>MigrateUpCommand: executeSpecificMigration($file, 'up')
>         end
>     end
> ```
> 
> ### Cómo encaja
> 
> La clase `MigrateUpCommand` se integra como parte del sistema de comandos de Lego, heredando de `CoreCommand`. Este comando es utilizado desde la interfaz de línea de comandos (CLI) para aplicar cambios a la base de datos. Funciona junto con otras clases relacionadas con migraciones y la gestión de la base de datos, como `Capsule` y la tabla `migrations`, asegurando que los cambios estructurales se apliquen correctamente y de manera controlada.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.