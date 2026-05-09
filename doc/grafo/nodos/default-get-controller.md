---
tipo: controller
capa: core-controllers
namespace: Core\Controllers
archivo: Core/Controllers/DefaultGetController.php
loc: 12
deps: 1
dependents: 1
responsabilidad: Sirve como implementación concreta vacía de AbstractGetController, usada por ApiGetRouter cuando el modelo no especifica un controlador custom.
tags:
  - grafo
  - grafo/tipo/controller
  - grafo/capa/core-controllers
---
# DefaultGetController

`Core\Controllers\DefaultGetController`

📁 [Core/Controllers/DefaultGetController.php](../../../Core/Controllers/DefaultGetController.php)

> [!abstract] Responsabilidad
> Sirve como implementación concreta vacía de AbstractGetController, usada por ApiGetRouter cuando el modelo no especifica un controlador custom.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `DefaultGetController` existe para proporcionar una implementación concreta vacía de `AbstractGetController`. Su principal propósito es ser utilizada por `ApiGetRouter` cuando un modelo no especifica un controlador personalizado en su atributo `#[ApiGetResource]`. Esta abstracción permite que el sistema tenga un comportamiento predeterminado para las solicitudes GET, garantizando que siempre haya una clase controladora disponible sin necesidad de definir una implementación específica cada vez.
> 
> ### Métodos principales
> 
> Dado que `DefaultGetController` es una clase vacía que hereda toda su funcionalidad de `AbstractGetController`, no tiene métodos propios. Sin embargo, los métodos clave que se ejecutan cuando se utiliza esta clase son:
> 
> 1. **`__construct()`**: Este método inicializa la instancia de `DefaultGetController`. Aunque no está definido explícitamente en el código proporcionado, hereda la implementación del constructor de `AbstractGetController`.
> 
> 2. **`handle(Request $request)`**: Este método procesa la solicitud HTTP GET. Heredado de `AbstractGetController`, se encarga de manejar la lógica específica para responder a solicitudes GET.
> 
> 3. **`render($data)`**: Este método se encarga de renderizar los datos obtenidos en una respuesta HTTP adecuada. También hereda su implementación de `AbstractGetController`.
> 
> ### Diagrama
> 
> ```mermaid
> sequenceDiagram
>     participant Client
>     participant Router as ApiGetRouter
>     participant Controller as DefaultGetController
>     participant Model as Producto
>     participant DB as PostgreSQL
>     
>     Client->>Router: GET /api/productos
>     Router->>Model: buscar modelo con #[ApiGetResource]
>     alt Modelo no especifica controlador custom
>         Router->>Controller: new DefaultGetController()
>     else Modelo especifica controlador custom
>         Router->>CustomController: new CustomController()
>     end
>     Controller->>Model: obtener datos
>     Model->>DB: SELECT
>     DB-->>Model: datos
>     Model-->>Controller: datos
>     Controller->>Router: datos procesados
>     Router-->>Client: 200 JSON
> ```
> 
> ### Cómo encaja
> 
> `DefaultGetController` se conecta con el resto del sistema a través de `ApiGetRouter`. Cuando `ApiGetRouter` recibe una solicitud GET y no encuentra un controlador personalizado especificado en el modelo, instancía `DefaultGetController`. Esta clase, aunque vacía, permite que la cadena de procesamiento continúe sin interrupciones. Posteriormente, `ApiGetRouter` utiliza los datos obtenidos para generar una respuesta HTTP adecuada y devolverla al cliente.

## 🔼 Hereda de

- [[abstract-get-controller|AbstractGetController]]

## 👥 Es referenciado por

- [[api-get-resource|ApiGetResource]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.