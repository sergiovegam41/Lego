---
tipo: abstract-class
capa: core-controllers
namespace: Core\Controllers
archivo: Core/Controllers/RestfulController.php
loc: 291
deps: 4
dependents: 0
responsabilidad: Abstrae la lógica común de controladores REST, gestionando métodos HTTP, validaciones y respuestas consistentes para que las subclases solo implementen la lógica de negocio.
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/core-controllers
---
# RestfulController

`Core\Controllers\RestfulController`

📁 [Core/Controllers/RestfulController.php](../../../Core/Controllers/RestfulController.php)

> [!abstract] Responsabilidad
> Abstrae la lógica común de controladores REST, gestionando métodos HTTP, validaciones y respuestas consistentes para que las subclases solo implementen la lógica de negocio.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `RestfulController` existe para abstraer la lógica común asociada a los controladores REST en un framework de dashboards orientado a componentes como Lego. Este controlador centraliza aspectos cruciales como la gestión de métodos HTTP, validaciones básicas y el formato de respuestas, permitiendo que los desarrolladores se centren únicamente en implementar la lógica de negocio específica de cada recurso. La abstracción facilita la creación de APIs RESTful coherentes y reduce la duplicación de código, ya que proporciona una estructura predefinida para manejar operaciones CRUD (Crear, Leer, Actualizar, Eliminar).
> 
> ### Métodos principales
> 
> 1. **`__construct($action)`**: Este es el constructor principal de la clase. Se encarga de enrutar la petición a la acción correcta basada en el método HTTP y la acción solicitada. También valida que el método HTTP sea apropiado para la acción y maneja excepciones globales.
> 
> 2. **`validateHttpMethod($httpMethod, $action)`**: Este método verifica si el método HTTP utilizado en la petición es permitido para la acción específica. Utiliza una mapeo de métodos HTTP a acciones definida en `HTTP_METHOD_MAP`.
> 
> 3. **`getJsonInput()`**: Este método obtiene los datos JSON del cuerpo de la petición y los parsea. Si el JSON es inválido, lanza una excepción.
> 
> 4. **`success($message, $data = null, $statusCode = StatusCodes::HTTP_OK)`**: Este método envía una respuesta exitosa en formato JSON. Puede incluir un mensaje, datos adicionales y un código de estado HTTP personalizado.
> 
> 5. **`error($message, $data = null, $statusCode = StatusCodes::HTTP_BAD_REQUEST)`**: Este método envía una respuesta de error en formato JSON. Similar al método `success`, permite especificar un mensaje, datos adicionales y un código de estado HTTP personalizado.
> 
> 6. **`handleException(\Exception $e)`**: Este método maneja excepciones globales que pueden ocurrir durante el procesamiento de la petición. Registra los detalles del error en el log y envía una respuesta de error consistente al cliente.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class CoreController {
>         <<abstract>>
>         +handleRequest()
>     }
>     class RestfulController {
>         <<abstract>>
>         -HTTP_METHOD_MAP
>         +__construct($action)
>         +validateHttpMethod($httpMethod, $action)
>         +getJsonInput()
>         +success($message, $data = null, $statusCode = StatusCodes::HTTP_OK)
>         +error($message, $data = null, $statusCode = StatusCodes::HTTP_BAD_REQUEST)
>         +requireField($data, $field, $message = null)
>         +requireFields($data, $fields)
>         +handleException(\Exception $e)
>     }
>     CoreController <|-- RestfulController
> ```
> 
> ### Cómo encaja
> 
> La clase `RestfulController` se integra como una abstracción central en el sistema de controladores REST del framework Lego. Hereda de `CoreController`, lo que le permite acceder a funcionalidades básicas de manejo de solicitudes HTTP. Aunque no hay clases reales que la extiendan directamente en este ejemplo, su propósito es servir como una plantilla para otros controladores específicos (como `ProductsController`), donde se implementarán las acciones concretas de negocio (`list`, `get`, `create`, `update`, `delete`). Esta estructura permite que los desarrolladores creen nuevos controladores RESTful de manera rápida y consistente, aprovechando la lógica común definida en `RestfulController`.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## 🆕 Instancia

- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[response|Response]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.