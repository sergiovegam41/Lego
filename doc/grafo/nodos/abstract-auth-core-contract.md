---
tipo: abstract-class
capa: app-controllers
namespace: App\Controllers\Auth\Contracts
archivo: App/Controllers/Auth/Contracts/AbstractAuthCoreContract.php
loc: 32
deps: 2
dependents: 2
responsabilidad: Define el contrato base para operaciones de autenticación, especificando métodos abstractos para login, refresh token, logout, registro, inicio de sesión por código y obtención de perfil.
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/app-controllers
---
# AbstractAuthCoreContract

`App\Controllers\Auth\Contracts\AbstractAuthCoreContract`

📁 [App/Controllers/Auth/Contracts/AbstractAuthCoreContract.php](../../../App/Controllers/Auth/Contracts/AbstractAuthCoreContract.php)

> [!abstract] Responsabilidad
> Define el contrato base para operaciones de autenticación, especificando métodos abstractos para login, refresh token, logout, registro, inicio de sesión por código y obtención de perfil.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> La clase `AbstractAuthCoreContract` es un contrato abstracto fundamental para operaciones de autenticación en el sistema. Su principal objetivo es proporcionar una interfaz estándar que define los métodos básicos necesarios para la gestión de usuarios, incluyendo inicio de sesión, refresco de tokens, cierre de sesión, registro y obtención de perfil. Al ser abstracta, no implementa estos métodos concretamente; en cambio, fuerza a las clases que la extienden a proporcionar una implementación específica para cada uno. Esto asegura que todas las operaciones de autenticación sigan un patrón uniforme y cumplan con los requisitos mínimos necesarios.
> 
> ### Métodos principales
> 
> 1. **login(AuthRequestDTO $request): ResponseDTO**
>    - Este método se encarga de procesar una solicitud de inicio de sesión, utilizando el DTO `AuthRequestDTO` para recibir los datos del usuario. Devuelve un objeto `ResponseDTO` con el resultado de la operación.
> 
> 2. **refresh_token(AuthRequestDTO $request): ResponseDTO**
>    - Maneja la solicitud de refresco de tokens de autenticación. Utiliza el DTO `AuthRequestDTO` para recibir los datos necesarios y devuelve un `ResponseDTO` con el nuevo token o el estado de la operación.
> 
> 3. **logout(AuthRequestDTO $request): ResponseDTO**
>    - Procesa una solicitud de cierre de sesión, utilizando el DTO `AuthRequestDTO` para recibir los datos del usuario. Retorna un `ResponseDTO` indicando si la operación fue exitosa o no.
> 
> 4. **register(AuthRequestDTO $request): ResponseDTO**
>    - Se encarga de registrar a un nuevo usuario en el sistema. Recibe los datos mediante el DTO `AuthRequestDTO` y devuelve un `ResponseDTO` con el estado del registro.
> 
> 5. **loginByCode(AuthRequestDTO $request): ResponseDTO**
>    - Implementa la autenticación por código, utilizando el DTO `AuthRequestDTO` para recibir los datos necesarios. Retorna un `ResponseDTO` con el resultado de la operación.
> 
> 6. **getProfile(AuthRequestDTO $request): ResponseDTO**
>    - Obtiene el perfil del usuario autenticado. Utiliza el DTO `AuthRequestDTO` para recibir los datos del usuario y devuelve un `ResponseDTO` con la información del perfil.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class AbstractAuthCoreContract {
>         <<abstract>>
>         +login(AuthRequestDTO $request): ResponseDTO
>         +refresh_token(AuthRequestDTO $request): ResponseDTO
>         +logout(AuthRequestDTO $request): ResponseDTO
>         +register(AuthRequestDTO $request): ResponseDTO
>         +loginByCode(AuthRequestDTO $request): ResponseDTO
>         +getProfile(AuthRequestDTO $request): ResponseDTO
>     }
>     AbstractAuthCoreContract <|-- AdminAuthGroupProvider
>     AbstractAuthCoreContract <|-- ApiAuthGroupProvider
> ```
> 
> ### Cómo encaja
> 
> La clase `AbstractAuthCoreContract` se integra como una abstracción central para las operaciones de autenticación en el sistema. Las clases `AdminAuthGroupProvider` y `ApiAuthGroupProvider` extienden esta clase abstracta, proporcionando implementaciones específicas para los métodos definidos. Esto permite que ambas clases cumplan con la misma interfaz de contrato, asegurando consistencia en las operaciones de autenticación tanto para el panel de administración como para la API.

## 📥 Type hints (parámetros)

- [[auth-request-dto|AuthRequestDTO]]

## 📤 Tipos de retorno

- [[response-dto|ResponseDTO]]

## 👥 Es referenciado por

- [[admin-auth-group-provider|AdminAuthGroupProvider]] *(extends)*
- [[api-auth-group-provider|ApiAuthGroupProvider]] *(extends)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.