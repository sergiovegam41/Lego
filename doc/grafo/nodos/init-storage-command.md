---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/InitStorageCommand.php
loc: 108
deps: 2
dependents: 1
responsabilidad: Inicializa el sistema de almacenamiento MinIO, creando buckets, aplicando políticas y estructurando carpetas automáticamente.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# InitStorageCommand

`Core\Commands\InitStorageCommand`

📁 [Core/Commands/InitStorageCommand.php](../../../Core/Commands/InitStorageCommand.php)

> [!abstract] Responsabilidad
> Inicializa el sistema de almacenamiento MinIO, creando buckets, aplicando políticas y estructurando carpetas automáticamente.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `InitStorageCommand` es un comando específico diseñado para automatizar la configuración inicial del sistema de almacenamiento MinIO dentro del framework Lego. Su principal objetivo es asegurar que el entorno de almacenamiento esté correctamente establecido al ejecutar el comando `php lego init:storage`, eliminando la necesidad de realizar configuraciones manuales tediosas y propensas a errores. Este comando resuelve problemas comunes como verificar la conexión con MinIO, crear buckets si no existen, aplicar políticas de acceso adecuadas y estructurar las carpetas necesarias para el funcionamiento del sistema.
> 
> ### Métodos principales
> 
> 1. **`execute()`**: Es el método principal que se ejecuta cuando se invoca el comando `init:storage`. Este método lleva a cabo toda la configuración inicial del almacenamiento, desde verificar la conexión con MinIO hasta crear y estructurar buckets y carpetas.
> 2. **`isConnected()`**: Verifica si hay una conexión activa con el servidor MinIO. Si no está conectado, muestra un mensaje de error y detiene la ejecución del comando.
> 3. **`bucketExists($bucketName)`**: Comprueba si un bucket específico ya existe en MinIO. Esto ayuda a evitar errores al intentar crear un bucket que ya está presente.
> 4. **`createBucket($bucketName)`**: Crea un nuevo bucket en MinIO con el nombre especificado.
> 5. **`setBucketPublic($bucketName)`**: Configura un bucket para que sea público, permitiendo el acceso directo a sus contenidos sin necesidad de autenticación adicional.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant Command as InitStorageCommand
>     participant Service as StorageService
>     participant MinIO as MinIO Server
>     participant Env as Environment Variables
> 
>     Client->>Command: php lego init:storage
>     Command->>Command: validate command signature
>     Command->>Service: new StorageService()
>     Service->>Env: getConfig()
>     Command->>MinIO: isConnected()
>     alt MinIO is connected
>         Command->>MinIO: bucketExists($bucketName)
>         alt Bucket exists
>             Command-->>Client: Bucket already exists
>         else Bucket does not exist
>             Command->>MinIO: createBucket($bucketName)
>             Command-->>Client: Bucket created
>         end
>         Command->>MinIO: setBucketPublic($bucketName)
>         Command-->>Client: Public policy applied
>         loop Create folders
>             Command->>MinIO: createFolder($folder)
>             Command-->>Client: Folder created
>         end
>         Command-->>Client: Storage initialized successfully
>     else MinIO is not connected
>         Command-->>Client: MinIO connection failed
>     end
> ```
> 
> ### Cómo encaja
> 
> `InitStorageCommand` se conecta directamente con el sistema de almacenamiento MinIO a través del `StorageService`. Este comando es instanciado por `InitCommand`, que probablemente gestiona la ejecución de varios comandos de inicialización del sistema. La clase `InitStorageCommand` no tiene extensores, implementaciones ni traits adicionales, lo que la hace una entidad autónoma y específica para su tarea de configuración del almacenamiento. Su rol es fundamental en el proceso de setup automático del framework Lego, asegurando que todas las dependencias de almacenamiento estén correctamente configuradas antes de que el sistema comience a operar.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

## 🆕 Instancia

- [[storage-service|StorageService]]

## 👥 Es referenciado por

- [[init-command|InitCommand]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.