---
tipo: controller
capa: app-controllers
namespace: App\Controllers\Storage\Controllers
archivo: App/Controllers/Storage/Controllers/StorageController.php
loc: 286
deps: 6
dependents: 0
responsabilidad: Orquesta endpoints REST para operaciones CRUD de archivos de almacenamiento, utilizando un proveedor y validador específicos.
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
---
# StorageController

`App\Controllers\Storage\Controllers\StorageController`

📁 [App/Controllers/Storage/Controllers/StorageController.php](../../../App/Controllers/Storage/Controllers/StorageController.php)

> [!abstract] Responsabilidad
> Orquesta endpoints REST para operaciones CRUD de archivos de almacenamiento, utilizando un proveedor y validador específicos.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `StorageController` es fundamental para proporcionar una interfaz RESTful que maneje operaciones CRUD básicas sobre archivos de almacenamiento. Su creación se debe a la necesidad de centralizar y abstraer las operaciones relacionadas con el almacenamiento de archivos, permitiendo así una gestión eficiente y modular del sistema. Además, al heredar de `CoreController`, esta clase aprovecha funcionalidades comunes y estructuras predefinidas para mantener un código limpio y coherente.
> 
> ### Métodos principales
> 
> 1. **upload()**: Este método maneja la subida de archivos al almacenamiento. Recibe un archivo en formato multipart/form-data, lo valida utilizando `StorageRules`, y luego utiliza `StorageProvider` para realizar la operación de subida. En caso de éxito, devuelve detalles del archivo subido; en caso de error, retorna una respuesta con el mensaje correspondiente.
> 
> 2. **list()**: Este método lista archivos dentro de un directorio específico. Recibe parámetros como la ruta y el límite de resultados a través de los query params, los valida utilizando `StorageRules`, y utiliza `StorageProvider` para obtener la lista de archivos. Retorna una respuesta con los detalles de los archivos listados.
> 
> 3. **get()**: Este método obtiene información detallada sobre un archivo específico. Recibe el nombre del archivo como parámetro a través de los query params, lo valida utilizando `StorageRules`, y utiliza `StorageProvider` para obtener la información del archivo. Retorna una respuesta con los detalles del archivo.
> 
> 4. **delete()**: Este método elimina un archivo del almacenamiento. Recibe el nombre del archivo en formato JSON en el cuerpo de la solicitud, lo valida utilizando `StorageRules`, y utiliza `StorageProvider` para realizar la operación de eliminación. Retorna una respuesta indicando si el archivo fue eliminado exitosamente.
> 
> 5. **stats()**: Este método proporciona estadísticas generales sobre el sistema de almacenamiento. Utiliza `StorageProvider` para obtener información como el total de archivos, tamaño total, tipos de archivos y detalles del bucket. Retorna una respuesta con estas estadísticas.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant StorageController as SC
>     participant StorageProvider as SP
>     participant StorageRules as SR
>     participant ResponseDTO as RDTO
>     participant Response as Res
> 
>     Client->>SC: POST /api/storage/upload
>     SC->>SR: validateUpload()
>     alt Validation fails
>         SR-->>SC: Errors
>         SC->>Res: json(400, ...)
>     else Validation passes
>         SR-->>SC: Valid
>         SC->>SP: handleUpload(file, customName, path)
>         alt Success
>             SP-->>SC: Upload result
>             SC->>RDTO: new ResponseDTO(true, 'Archivo subido exitosamente', result)
>             RDTO-->>Res: json(200, ...)
>         else Failure
>             SP-->>SC: Exception
>             SC->>Res: json(400, ...)
>         end
>     end
> 
>     Client->>SC: GET /api/storage/list?path=images/&limit=50
>     SC->>SR: validateList(path, limit)
>     alt Validation fails
>         SR-->>SC: Errors
>         SC->>Res: json(400, ...)
>     else Validation passes
>         SR-->>SC: Valid
>         SC->>SP: listFiles(path, limit)
>         alt Success
>             SP-->>SC: List result
>             SC->>RDTO: new ResponseDTO(true, 'Archivos obtenidos', result)
>             RDTO-->>Res: json(200, ...)
>         else Failure
>             SP-->>SC: Exception
>             SC->>Res: json(400, ...)
>         end
>     end
> 
>     Client->>SC: GET /api/storage/get?file=images/foto.jpg
>     SC->>SR: validateGet(file)
>     alt Validation fails
>         SR-->>SC: Errors
>         SC->>Res: json(400, ...)
>     else Validation passes
>         SR-->>SC: Valid
>         SC->>SP: getFileInfo(file)
>         alt Success
>             SP-->>SC: File info
>             SC->>RDTO: new ResponseDTO(true, 'Información del archivo', result)
>             RDTO-->>Res: json(200, ...)
>         else Failure
>             SP-->>SC: Exception
>             SC->>Res: json(400, ...)
>         end
>     end
> 
>     Client->>SC: POST /api/storage/delete
>     SC->>SR: validateDelete(data)
>     alt Validation fails
>         SR-->>SC: Errors
>         SC->>Res: json(400, ...)
>     else Validation passes
>         SR-->>SC: Valid
>         SC->>SP: deleteFile(file)
>         alt Success
>             SP-->>SC: Delete success
>             SC->>RDTO: new ResponseDTO(true, 'Archivo eliminado exitosamente', null)
>             RDTO-->>Res: json(200, ...)
>         else Failure
>             SP-->>SC: Exception
>             SC->>Res: json(400, ...)
>         end
>     end
> 
>     Client->>SC: GET /api/storage/stats
>     SC->>SP: getStats()
>     alt Success
>         SP-->>SC: Stats result
>         SC->>RDTO: new ResponseDTO(true, 'Estadísticas obtenidas', result)
>         RDTO-->>Res: json(200, ...)
>     else Failure
>         SP-->>SC: Exception
>         SC->>Res: json(400, ...)
>     end
> ```
> 
> ### Cómo encaja
> 
> La clase `StorageController` se integra como parte del controlador principal de la aplicación, heredando funcionalidades básicas desde `CoreController`. Su rol es orquestar las solicitudes HTTP relacionadas con el almacenamiento de archivos, utilizando instancias de `StorageProvider` para realizar operaciones de subida, listado, obtención y eliminación de archivos. Además, utiliza `StorageRules` para validar los datos de entrada antes de delegar la ejecución a `StorageProvider`. Esta estructura permite una separación clara de responsabilidades, donde `StorageController` se encarga de la interfaz HTTP, mientras que `StorageProvider` maneja las operaciones de almacenamiento y `StorageRules` asegura la integridad y validez de los datos.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🆕 Instancia

- [[response-dto|ResponseDTO]]
- [[storage-provider|StorageProvider]]
- [[storage-rules|StorageRules]]

## ⚡ Llamadas estáticas

- [[response|Response]]

## 🔗 Constantes referenciadas

- [[storage-exception|StorageException]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.