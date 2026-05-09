---
tipo: abstract-class
capa: core
namespace: Core\Models
archivo: Core/Models/Model.php
loc: 435
deps: 0
dependents: 0
responsabilidad: Define la clase abstracta base para modelos, encapsulando operaciones CRUD y métodos de consulta SQL dinámica.
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/core
---
# Model

`Core\Models\Model`

📁 [Core/Models/Model.php](../../../Core/Models/Model.php)

> [!abstract] Responsabilidad
> Define la clase abstracta base para modelos, encapsulando operaciones CRUD y métodos de consulta SQL dinámica.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `Model` es un componente fundamental del framework Lego, diseñado para abstraer y encapsular operaciones CRUD (Create, Read, Update, Delete) junto con métodos de consulta SQL dinámicos. Su principal objetivo es proporcionar una interfaz uniforme y coherente para interactuar con la base de datos, facilitando el desarrollo de aplicaciones que requieren manipulación de datos persistidos. La abstracción de estas operaciones permite a los desarrolladores centrarse en la lógica de negocio sin preocuparse por detalles específicos de la base de datos.
> 
> ### Métodos principales
> 
> 1. **`getMethod($request, $accion)`**: Este método es el punto de entrada para ejecutar una acción específica sobre un modelo. Verifica si la acción está permitida y, si no lo está, intenta llamar al método correspondiente directamente.
> 
> 2. **`create($request)` y `createGet()`**: Estos métodos se encargan de construir y ejecutar una consulta SQL para insertar nuevos registros en la base de datos. `create()` prepara la consulta, mientras que `createGet()` ejecuta la consulta e informa el resultado.
> 
> 3. **`read($request)` y `readGet()`**: Estos métodos se utilizan para construir y ejecutar consultas SQL SELECT. `read()` prepara la consulta basada en los parámetros proporcionados, mientras que `readGet()` ejecuta la consulta e informa el resultado.
> 
> 4. **`update($request)` y `updateGet()`**: Estos métodos se encargan de construir y ejecutar consultas SQL UPDATE. `update()` prepara la consulta basada en los datos proporcionados, mientras que `updateGet()` ejecuta la consulta e informa el resultado.
> 
> 5. **`delete($request)` y `deleteGet()`**: Estos métodos se utilizan para construir y ejecutar consultas SQL DELETE. `delete()` prepara la consulta basada en el ID proporcionado, mientras que `deleteGet()` ejecuta la consulta e informa el resultado.
> 
> 6. **`get()`**: Este método es un punto de acceso genérico que determina qué método específico debe llamarse para obtener los resultados de una operación CRUD. Utiliza el último método llamado y su versión "Get" correspondiente.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class Model {
>         <<abstract>>
>         +table
>         +filables
>         +arrayMethods
>         +sql
>         +ultimoMetodoLlamado
>         +request
>         +select
>         +join
>         +where
>         +limit
>         +ofset
>         +group
>         +frist
>         +getMethod($request, $accion)
>         +create($request)
>         +createGet()
>         +read($request)
>         +readGet()
>         +update($request)
>         +updateGet()
>         +delete($request)
>         +deleteGet()
>         +get()
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `Model` se integra como una capa de abstracción entre la lógica de negocio y el acceso a la base de datos. Al encapsular operaciones CRUD y métodos de consulta SQL dinámicos, facilita la creación y mantenimiento de modelos que interactúen con diferentes tablas de la base de datos. Dado que no hay clases específicas mencionadas en las relaciones entrantes, se asume que esta clase es utilizada directamente por los controladores o servicios que manejan las solicitudes HTTP y necesitan interactuar con la base de datos.

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.