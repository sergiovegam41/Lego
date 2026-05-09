---
tipo: command
capa: core-commands
namespace: Core\Commands
archivo: Core/Commands/StorageCheckCommand.php
loc: 128
deps: 2
dependents: 0
responsabilidad: Verifica el estado del sistema de almacenamiento MinIO, mostrando estadísticas y configuración actual.
tags:
  - grafo
  - grafo/tipo/command
  - grafo/capa/core-commands
---
# StorageCheckCommand

`Core\Commands\StorageCheckCommand`

📁 [Core/Commands/StorageCheckCommand.php](../../../Core/Commands/StorageCheckCommand.php)

> [!abstract] Responsabilidad
> Verifica el estado del sistema de almacenamiento MinIO, mostrando estadísticas y configuración actual.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `StorageCheckCommand` se creó para proporcionar un mecanismo de diagnóstico y monitoreo del sistema de almacenamiento MinIO utilizado por la aplicación. Este comando es crucial para asegurar que el sistema de almacenamiento esté funcionando correctamente, mostrando estadísticas detalladas sobre la conexión, buckets y uploads. La necesidad de esta clase surge debido a la complejidad y los posibles puntos de fallo en un sistema de almacenamiento basado en MinIO, lo cual requiere una herramienta específica para verificar su estado y configuración.
> 
> ### Métodos principales
> 
> 1. **execute()**: Este es el método principal del comando. Se encarga de ejecutar la verificación del sistema de almacenamiento. Realiza las siguientes acciones:
>    - Verifica si MinIO está conectado.
>    - Muestra los endpoints de la API y consola de MinIO.
>    - Oculta parcialmente las credenciales de acceso para seguridad.
>    - Verifica si el bucket especificado existe.
>    - Muestra estadísticas del bucket, incluyendo el número total de archivos, espacio usado y tipos de archivo.
>    - Lista la estructura de carpetas dentro del bucket.
>    - Muestra la configuración de uploads, como el tamaño máximo permitido y las extensiones permitidas.
> 
> 2. **isConnected()**: Este método verifica si MinIO está conectado correctamente. Si no lo está, muestra un mensaje de error y sugiere cómo iniciar MinIO usando Docker Compose.
> 
> 3. **bucketExists($bucketName)**: Verifica si el bucket especificado existe en MinIO. Si no existe, muestra un mensaje de error y sugiere cómo inicializar el sistema de almacenamiento.
> 
> 4. **getConfig()**: Obtiene la configuración actual del servicio de almacenamiento, incluyendo endpoints, credenciales y parámetros de uploads.
> 
> 5. **getStats()**: Recopila estadísticas detalladas sobre el bucket, como el número total de archivos, espacio usado y tipos de archivo presentes en el bucket.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client as CLI
>     participant Command as StorageCheckCommand
>     participant Service as StorageService
>     participant MinIO
> 
>     Client->>Command: php lego storage:check
>     Command->>Service: new StorageService()
>     Service->>MinIO: isConnected()
>     alt Conexión exitosa
>         MinIO-->>Service: true
>         Service->>MinIO: getConfig()
>         MinIO-->>Service: Configuración
>         Service->>MinIO: bucketExists($bucketName)
>         alt Bucket existe
>             MinIO-->>Service: true
>             Service->>MinIO: getStats()
>             MinIO-->>Service: Estadísticas
>             Service->>Command: Resultados de la verificación
>             Command-->>Client: Informe detallado
>         else Bucket no existe
>             MinIO-->>Service: false
>             Service-->>Command: Error de bucket inexistente
>             Command-->>Client: Mensaje de error
>         end
>     else Conexión fallida
>         MinIO-->>Service: false
>         Service-->>Command: Error de conexión
>         Command-->>Client: Mensaje de error
>     end
> ```
> 
> ### Cómo encaja
> 
> La clase `StorageCheckCommand` se integra dentro del sistema como un comando específico que puede ser ejecutado desde la línea de comandos (CLI) para verificar el estado y configuración del sistema de almacenamiento MinIO. Este comando utiliza el servicio `StorageService` para interactuar con MinIO, obteniendo información sobre la conexión, buckets y estadísticas. La clase no es extendida ni implementada por otras clases en el codebase proporcionado, lo que indica que su funcionalidad específica está encapsulada y no se comparte con otros componentes.

## 🔼 Hereda de

- [[core-command|CoreCommand]]

## 🆕 Instancia

- [[storage-service|StorageService]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.