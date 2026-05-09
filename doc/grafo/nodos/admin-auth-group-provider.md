---
tipo: class
capa: app-controllers
namespace: App\Controllers\Auth\Providers\AuthGroups\Admin
archivo: App/Controllers/Auth/Providers/AuthGroups/Admin/AdminAuthGroupProvider.php
loc: 76
deps: 7
dependents: 1
responsabilidad: Orquesta el flujo de autenticación y registro para usuarios del grupo administrativo, utilizando servicios core de autenticación y validando datos de entrada.
tags:
  - grafo
  - grafo/tipo/class
  - grafo/capa/app-controllers
---
# AdminAuthGroupProvider

`App\Controllers\Auth\Providers\AuthGroups\Admin\AdminAuthGroupProvider`

📁 [App/Controllers/Auth/Providers/AuthGroups/Admin/AdminAuthGroupProvider.php](../../../App/Controllers/Auth/Providers/AuthGroups/Admin/AdminAuthGroupProvider.php)

> [!abstract] Responsabilidad
> Orquesta el flujo de autenticación y registro para usuarios del grupo administrativo, utilizando servicios core de autenticación y validando datos de entrada.

## 🔼 Hereda de

- [[abstract-auth-core-contract|AbstractAuthCoreContract]]

## 🆕 Instancia

- [[auth-services-core|AuthServicesCore]]
- [[response-dto|ResponseDTO]]

## ⚡ Llamadas estáticas

- [[auth-services-core|AuthServicesCore]]

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