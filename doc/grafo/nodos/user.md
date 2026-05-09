---
tipo: model
capa: app-models
namespace: App\Models
archivo: App/Models/User.php
loc: 43
deps: 1
dependents: 3
responsabilidad: Define el modelo de usuario del sistema, gestionando sus atributos, relaciones y exposición a través de una API con filtros y paginación.
atributos:
  - ApiGetResource
tags:
  - grafo
  - grafo/tipo/model
  - grafo/capa/app-models
  - grafo/atributo/ApiGetResource
---
# User

`App\Models\User`

📁 [App/Models/User.php](../../../App/Models/User.php)

> [!abstract] Responsabilidad
> Define el modelo de usuario del sistema, gestionando sus atributos, relaciones y exposición a través de una API con filtros y paginación.

## 🏷️ Atributos declarativos

- [[api-get-resource|ApiGetResource]]

## 👥 Es referenciado por

- [[auth-services-core|AuthServicesCore]] *(static_call)*
- [[users-config-component|UsersConfigComponent]] *(const_fetch)*
- [[users-config-controller|UsersConfigController]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.