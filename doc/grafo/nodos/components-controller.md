---
tipo: controller
capa: app-controllers
namespace: App\Controllers
archivo: App/Controllers/ComponentsController.php
loc: 217
deps: 3
dependents: 0
responsabilidad: Orquesta la renderización de componentes UI a través de endpoints RESTful, validando parámetros y gestionando excepciones.
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/app-controllers
---
# ComponentsController

`App\Controllers\ComponentsController`

📁 [App/Controllers/ComponentsController.php](../../../App/Controllers/ComponentsController.php)

> [!abstract] Responsabilidad
> Orquesta la renderización de componentes UI a través de endpoints RESTful, validando parámetros y gestionando excepciones.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La `ComponentsController` es un controlador central en el framework PHP Lego, diseñado para orquestar la renderización de componentes UI a través de endpoints RESTful. Su principal objetivo es permitir que JavaScript solicite y reciba componentes renderizados desde PHP, manteniendo esta última como única fuente de verdad. Esto facilita la actualización dinámica de interfaces de usuario sin necesidad de recargar páginas completas, mejorando así la experiencia del usuario.
> 
> ### Métodos principales
> 
> 1. **render()**
>    - **Qué hace**: Maneja solicitudes GET a `/api/components/render` para renderizar un componente único basado en el ID proporcionado y los parámetros enviados. Valida que el ID del componente esté presente y que los parámetros JSON sean válidos. Luego, utiliza `ComponentRegistry::render()` para obtener el HTML renderizado del componente y lo devuelve como respuesta JSON.
> 
> 2. **batch()**
>    - **Qué hace**: Gestiona solicitudes POST a `/api/components/batch` para renderizar múltiples instancias de un mismo componente en una sola petición (batch). Parsea el cuerpo JSON, valida los parámetros requeridos, y utiliza `ComponentRegistry::renderBatch()` para obtener una lista de HTMLs renderizados. Devuelve la lista como respuesta JSON.
> 
> 3. **list()**
>    - **Qué hace**: Responde a solicitudes GET a `/api/components/list` proporcionando una lista de todos los componentes registrados en el sistema. Utiliza `ComponentRegistry::getAll()` para obtener esta información y la devuelve como respuesta JSON, incluyendo detalles como el ID y la clase de cada componente.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client as Cliente JavaScript
>     participant Controller as ComponentsController
>     participant Registry as ComponentRegistry
>     participant Response as Core\Response
>     participant Request as Core\Providers\Request
> 
>     Client->>Controller: GET /api/components/render?id=icon-button&params={"action":"edit","entityId":14}
>     Controller->>Request: Obtener parámetros de query string
>     Controller->>Registry: ComponentRegistry::render($componentId, $params)
>     Registry-->>Controller: HTML renderizado
>     Controller->>Response: Response::json(200, ['html' => $html])
> 
>     Client->>Controller: POST /api/components/batch
>     Controller->>Request: Obtener cuerpo JSON de la petición
>     Controller->>Registry: ComponentRegistry::renderBatch($componentId, $renders)
>     Registry-->>Controller: Lista de HTMLs renderizados
>     Controller->>Response: Response::json(200, ['html' => $htmlList])
> 
>     Client->>Controller: GET /api/components/list
>     Controller->>Registry: ComponentRegistry::getAll()
>     Registry-->>Controller: Lista de componentes registrados
>     Controller->>Response: Response::json(200, ['components' => $components])
> ```
> 
> ### Cómo encaja
> 
> La `ComponentsController` se integra como un componente crucial del sistema Lego, facilitando la comunicación entre el frontend (JavaScript) y el backend (PHP). Se conecta con `CoreController` para heredar funcionalidades básicas de controladores, y utiliza `ComponentRegistry` para acceder a los componentes registrados y renderizarlos. Además, depende de `Core\Response` para enviar respuestas JSON al cliente. Este controlador es fundamental para permitir la actualización dinámica de interfaces de usuario en el framework Lego, asegurando que las interacciones sean eficientes y seguras.

## 🔼 Hereda de

- [[core-controller|CoreController]]

## ⚡ Llamadas estáticas

- [[component-registry|ComponentRegistry]]
- [[response|Response]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.