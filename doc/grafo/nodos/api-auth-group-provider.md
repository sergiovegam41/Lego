---
tipo: class
capa: app-controllers
namespace: App\Controllers\Auth\Providers\AuthGroups\Api
archivo: App/Controllers/Auth/Providers/AuthGroups/Api/ApiAuthGroupProvider.php
loc: 69
deps: 6
dependents: 1
responsabilidad: Define el proveedor de autenticación para APIs, gestionando operaciones de login, registro y gestión de tokens específicas para usuarios externos.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/app-controllers
---
# ApiAuthGroupProvider

`App\Controllers\Auth\Providers\AuthGroups\Api\ApiAuthGroupProvider`

📁 [App/Controllers/Auth/Providers/AuthGroups/Api/ApiAuthGroupProvider.php](../../../App/Controllers/Auth/Providers/AuthGroups/Api/ApiAuthGroupProvider.php)

> [!abstract] Responsabilidad
> Define el proveedor de autenticación para APIs, gestionando operaciones de login, registro y gestión de tokens específicas para usuarios externos.

## 🔼 Hereda de

- [[abstract-auth-core-contract|AbstractAuthCoreContract]]

## 🆕 Instancia

- [[auth-services-core|AuthServicesCore]]
- [[response-dto|ResponseDTO]]

## 🔗 Constantes referenciadas

- [[auth-groups-ids|AuthGroupsIDs]]

## 📥 Type hints (parámetros)

- [[auth-request-dto|AuthRequestDTO]]

## 📤 Tipos de retorno

- [[response-dto|ResponseDTO]]

## 👥 Es referenciado por

- [[auth-groups-provider|AuthGroupsProvider]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.