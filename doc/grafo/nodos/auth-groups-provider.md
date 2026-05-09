---
tipo: class
capa: app-controllers
namespace: App\Controllers\Auth\Providers
archivo: App/Controllers/Auth/Providers/AuthGroupsProvider.php
loc: 71
deps: 7
dependents: 1
responsabilidad: Orquesta la autenticación delegando solicitudes a providers específicos según el grupo y acción de autenticación.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/app-controllers
---
# AuthGroupsProvider

`App\Controllers\Auth\Providers\AuthGroupsProvider`

📁 [App/Controllers/Auth/Providers/AuthGroupsProvider.php](../../../App/Controllers/Auth/Providers/AuthGroupsProvider.php)

> [!abstract] Responsabilidad
> Orquesta la autenticación delegando solicitudes a providers específicos según el grupo y acción de autenticación.

## 🆕 Instancia

- [[admin-auth-group-provider|AdminAuthGroupProvider]]
- [[api-auth-group-provider|ApiAuthGroupProvider]]
- [[response-dto|ResponseDTO]]

## 🔗 Constantes referenciadas

- [[auth-actions|AuthActions]]
- [[status-codes|StatusCodes]]

## 📥 Type hints (parámetros)

- [[auth-request-dto|AuthRequestDTO]]

## 📤 Tipos de retorno

- [[response-dto|ResponseDTO]]

## 👥 Es referenciado por

- [[auth-groups-controller|AuthGroupsController]] *(instantiates)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.