---
tipo: class
capa: core-services
namespace: Core\Services
archivo: Core/Services/AuthServicesCore.php
loc: 436
deps: 6
dependents: 5
responsabilidad: Gestiona el proceso de autenticación y autorización, incluyendo login, generación y renovación de tokens JWT, almacenamiento en Redis y validación de sesiones.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/core-services
---
# AuthServicesCore

`Core\Services\AuthServicesCore`

📁 [Core/Services/AuthServicesCore.php](../../../Core/Services/AuthServicesCore.php)

> [!abstract] Responsabilidad
> Gestiona el proceso de autenticación y autorización, incluyendo login, generación y renovación de tokens JWT, almacenamiento en Redis y validación de sesiones.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `AuthServicesCore` es una clase central en el sistema de autenticación y autorización del framework Lego. Su principal objetivo es gestionar todo el proceso relacionado con la autenticación, incluyendo el login, la generación y renovación de tokens JWT (JSON Web Tokens), el almacenamiento de sesiones en Redis y la validación de acceso. La existencia de esta clase se debe a la necesidad de centralizar y modularizar las operaciones de seguridad, facilitando así una gestión más eficiente y mantenible del sistema.
> 
> ### Métodos principales
> 
> 1. **coreLogin($email, $password, $auth_group_id, $device_id, $firebase_token = null): ResponseDTO**
>    - Este método maneja el proceso de inicio de sesión. Valida las credenciales del usuario, genera tokens JWT y almacena la información de la sesión en Redis. También establece una cookie con el token de acceso.
> 
> 2. **coreRefreshToken($refresh_token, $device_id): ResponseDTO**
>    - Este método se encarga de renovar el token de acceso utilizando un refresh token. Verifica que el refresh token sea válido y no haya expirado antes de generar un nuevo token de acceso y actualizar la información en Redis.
> 
> 3. **isAutenticated(): ResponseDTO**
>    - Este método verifica si un usuario está autenticado comprobando su token de acceso almacenado en Redis. Si el token está próximo a expirar, extiende automáticamente su validez para mantener al usuario activo.
> 
> 4. **storeAccessTokenInRedis($access_token, $auth_user_id, $device_id, $auth_group_id, $role_id, $expires_at): void**
>    - Este método almacena el token de acceso en Redis junto con la información del usuario y la sesión. Utiliza Redis como un almacenamiento centralizado para gestionar las sesiones de usuarios.
> 
> 5. **getSessionFromRedis($access_token)**
>    - Este método recupera los datos de la sesión asociados a un token de acceso desde Redis. Es utilizado por otros métodos para verificar la autenticación y extender el tiempo de vida del token.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class AuthServicesCore {
>         +coreLogin($email, $password, $auth_group_id, $device_id, $firebase_token = null): ResponseDTO
>         +coreRefreshToken($refresh_token, $device_id): ResponseDTO
>         +isAutenticated(): ResponseDTO
>         -storeAccessTokenInRedis($access_token, $auth_user_id, $device_id, $auth_group_id, $role_id, $expires_at): void
>         -getSessionFromRedis($access_token)
>     }
>     class AdminAuthGroupProvider
>     class ApiAuthGroupProvider
> 
>     AuthServicesCore <|-- AdminAuthGroupProvider
>     AuthServicesCore <|-- ApiAuthGroupProvider
> ```
> 
> ### Cómo encaja
> 
> `AuthServicesCore` se integra como un componente central en el sistema de autenticación y autorización. Es utilizada por `AdminAuthGroupProvider` y `ApiAuthGroupProvider`, que son responsables de manejar las operaciones específicas de autenticación para diferentes grupos o tipos de usuarios. La clase centraliza todas las operaciones relacionadas con la gestión de tokens, sesiones y validación de acceso, lo que facilita su mantenimiento y escalabilidad en el sistema Lego.

## 🆕 Instancia

- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[redis-client|RedisClient]]
- [[user|User]]
- [[user-session|UserSession]]

## 🔗 Constantes referenciadas

- [[status-codes|StatusCodes]]

## 📤 Tipos de retorno

- [[response-dto|ResponseDTO]]

## 👥 Es referenciado por

- [[admin-auth-group-provider|AdminAuthGroupProvider]] *(instantiates, static_call)*
- [[api-auth-group-provider|ApiAuthGroupProvider]] *(instantiates)*
- [[lego-helpers|LegoHelpers]] *(static_call)*
- [[menu-structure-controller|MenuStructureController]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.