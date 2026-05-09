---
tipo: class
capa: core
namespace: Core\Providers
archivo: Core/providers/Request.php
loc: 58
deps: 1
dependents: 2
responsabilidad: Encapsula la gestión de solicitudes HTTP, incluyendo la carga de datos, validación de reglas y manejo de errores.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# Request

`Core\Providers\Request`

📁 [Core/providers/Request.php](../../../Core/providers/Request.php)

> [!abstract] Responsabilidad
> Encapsula la gestión de solicitudes HTTP, incluyendo la carga de datos, validación de reglas y manejo de errores.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `Request` se creó para encapsular la lógica de manejo y validación de solicitudes HTTP dentro del framework Lego. Su principal objetivo es proporcionar un acceso uniforme a los datos de entrada de las solicitudes, independientemente del método utilizado (GET, POST, PUT, etc.), y aplicar reglas de validación definidas para asegurar que los datos cumplan con ciertos criterios antes de ser procesados por el sistema. Esto es crucial para mantener la integridad y seguridad de los datos recibidos en las operaciones realizadas por el framework.
> 
> ### Métodos principales
> 
> 1. **`__construct()`**: Este método inicializa una instancia de `Request`. Lee los datos de entrada del cuerpo de la solicitud HTTP utilizando `file_get_contents('php://input')`, que captura cualquier contenido enviado en el cuerpo de la solicitud. Luego, estos datos se combinan con los datos de la superglobal `$_REQUEST`, que contiene tanto los parámetros GET como POST. Además, verifica si los datos JSON decodificados son un array y los fusiona con los datos existentes. Finalmente, llama al método `validateMake()` para validar los datos según las reglas definidas.
> 
> 2. **`all()`**: Este método estático devuelve todos los datos de la solicitud HTTP combinados, similar a lo que hace el constructor. Es útil cuando se necesita acceder a los datos de entrada sin instanciar una nueva clase `Request`.
> 
> 3. **`rules()`**: Este método retorna las reglas de validación definidas para la instancia actual de `Request`. Las reglas son un array que especifica qué campos deben cumplir ciertos criterios (por ejemplo, ser requeridos, tener un formato específico, etc.).
> 
> 4. **`setRules($rules)`**: Este método permite establecer las reglas de validación para la instancia actual de `Request`. Recibe un array de reglas y lo asigna a la propiedad `$rules`. Retorna la propia instancia para permitir el encadenamiento de métodos.
> 
> 5. **`validateMake()`**: Este método es responsable de validar los datos de entrada según las reglas definidas. Utiliza la clase `Validator` del paquete Rakit/Validation para realizar la validación. Si la validación falla, captura los errores y envía una respuesta JSON con un estado 400 (Bad Request) y los detalles de los errores. Si la validación es exitosa, retorna `true`.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class Request {
>         +__construct()
>         +all() : array
>         +rules() : array
>         +setRules(array $rules) : Request
>         +validateMake() : bool
>     }
> ```
> 
> ### Cómo encaja
> 
> La clase `Request` se utiliza principalmente por el controlador `AuthGroupsController`. Este controlador depende de la clase `Request` para manejar y validar las solicitudes HTTP que reciben. El flujo típico sería que `AuthGroupsController` instancie una clase `Request`, establezca las reglas de validación necesarias, valide los datos de entrada y luego procese estos datos según sea necesario. Este enfoque permite a `AuthGroupsController` centrarse en la lógica de negocio sin preocuparse por el manejo y validación de solicitudes HTTP, delegando estas responsabilidades a la clase `Request`.

## ⚡ Llamadas estáticas

- [[response|Response]]

## 👥 Es referenciado por

- [[auth-groups-controller|AuthGroupsController]] *(instantiates)*
- [[auth-request-dto|AuthRequestDTO]] *(type_hint)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.