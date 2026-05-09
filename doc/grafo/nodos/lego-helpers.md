---
tipo: class
capa: core
namespace: Core\Helpers
archivo: Core/Helpers/LegoHelpers.php
loc: 20
deps: 2
dependents: 1
responsabilidad: Proporciona métodos estáticos para redirigir a rutas y verificar la autenticación del usuario, encapsulando operaciones comunes de gestión de sesiones y respuestas.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core
---
# LegoHelpers

`Core\Helpers\LegoHelpers`

📁 [Core/Helpers/LegoHelpers.php](../../../Core/Helpers/LegoHelpers.php)

> [!abstract] Responsabilidad
> Proporciona métodos estáticos para redirigir a rutas y verificar la autenticación del usuario, encapsulando operaciones comunes de gestión de sesiones y respuestas.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `LegoHelpers` existe para encapsular operaciones comunes y frecuentes utilizadas en el framework Lego, como redirigir a rutas específicas y verificar la autenticación de usuarios. Al proporcionar métodos estáticos, facilita su uso en cualquier parte del sistema sin necesidad de instanciar objetos, lo que mejora la eficiencia y la legibilidad del código.
> 
> ### Métodos principales
> 
> 1. **`redirect($route)`**: Este método redirige al usuario a una ruta específica dentro del framework Lego. Utiliza la función `header()` para enviar un encabezado HTTP de redirección y luego finaliza el script con `exit()`, asegurando que no se ejecute ningún código adicional después de la redirección.
> 
> 2. **`isAutenticated(): ResponseDTO`**: Este método verifica si el usuario actual está autenticado. Llama al método `isAutenticated()` del servicio `AuthServicesCore` y devuelve un objeto `ResponseDTO` con el resultado de la verificación.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class LegoHelpers {
>         +redirect($route)
>         +isAutenticated(): ResponseDTO
>     }
>     class AuthServicesCore {
>         +isAuthenticated()
>     }
>     class ResponseDTO {
>         
>     }
>     LegoHelpers --> AuthServicesCore: isAutenticated()
>     LegoHelpers --> ResponseDTO: returns
> ```
> 
> ### Cómo encaja
> 
> La clase `LegoHelpers` se conecta con el resto del sistema a través de su método `isAutenticated()`, que depende del servicio `AuthServicesCore`. Este servicio probablemente maneje la lógica de autenticación, como verificar credenciales y gestionar sesiones. Además, los métodos de `LegoHelpers` devuelven un objeto `ResponseDTO`, lo que sugiere una integración con otros componentes del sistema que esperan respuestas en este formato para procesarlas adecuadamente.

## ⚡ Llamadas estáticas

- [[auth-services-core|AuthServicesCore]]

## 📤 Tipos de retorno

- [[response-dto|ResponseDTO]]

## 👥 Es referenciado por

- [[admin-middlewares|AdminMiddlewares]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.