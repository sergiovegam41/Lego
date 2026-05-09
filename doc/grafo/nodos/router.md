---
tipo: class
capa: core
namespace: Core
archivo: Core/Router.php
loc: 135
deps: 0
dependents: 0
responsabilidad: Centraliza la lógica de routing en tres capas para manejar API, componentes SPA y páginas web completas, permitiendo que public/index.php sea prescindible según el entorno.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# Router

`Core\Router`

📁 [Core/Router.php](../../../Core/Router.php)

> [!abstract] Responsabilidad
> Centraliza la lógica de routing en tres capas para manejar API, componentes SPA y páginas web completas, permitiendo que public/index.php sea prescindible según el entorno.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `Router` centraliza la lógica de routing en tres capas para manejar diferentes tipos de solicitudes HTTP: API, componentes SPA y páginas web completas. Su propósito principal es permitir que el archivo `public/index.php` sea prescindible según el entorno (nginx, Apache, etc.), facilitando una configuración más flexible y mantenible del sistema.
> 
> ### Métodos principales
> 
> 1. **dispatch()**
>    - **Qué hace**: Enruta la solicitud HTTP al archivo de rutas apropiado según el primer segmento de la URI. Determina si la solicitud es para API, componentes SPA o páginas web completas, ajustando la `REQUEST_URI` y requiriendo el archivo de rutas correspondiente.
> 
> 2. **serveStaticFile()**
>    - **Qué hace**: Sirve un archivo estático con headers de caché apropiados. Verifica si el archivo existe, detecta su tipo MIME, establece encabezados de caché para mejorar la eficiencia y verifica si el cliente tiene una versión cacheada antes de servir el archivo.
> 
> ### Diagrama
> 
> ```mermaid
> flowchart TD
>     Request[HTTP Request] --> Check{URI prefix}
>     Check -->|/api/| ApiRoutes[Routes/Api.php]
>     Check -->|/component/| CompRoutes[Routes/Component.php]
>     Check -->|otra| WebRoutes[Routes/Web.php]
> ```
> 
> ### Cómo encaja
> 
> La clase `Router` se conecta con el resto del sistema mediante su método `dispatch()`, que determina la capa de routing correspondiente según el prefijo de la URI y requiere el archivo de rutas adecuado. Esto permite una estructura modular donde cada tipo de solicitud (API, componentes SPA, páginas web) es manejada por su propio archivo de rutas, facilitando la mantenibilidad y escalabilidad del sistema.

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.