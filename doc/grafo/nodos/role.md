---
tipo: model
capa: app-models
namespace: App\Models
archivo: App/Models/Role.php
loc: 110
deps: 1
dependents: 5
responsabilidad: Define y gestiona roles de usuarios por grupo de autenticación, permitiendo la asignación masiva de atributos y consultas filtradas y paginadas.
atributos:
  - ApiGetResource
tags:
  - grafo
  - grafo/tipo/model
  - grafo/capa/app-models
  - grafo/atributo/ApiGetResource
---
# Role

`App\Models\Role`

📁 [App/Models/Role.php](../../../App/Models/Role.php)

> [!abstract] Responsabilidad
> Define y gestiona roles de usuarios por grupo de autenticación, permitiendo la asignación masiva de atributos y consultas filtradas y paginadas.

## 🏷️ Atributos declarativos

- [[api-get-resource|ApiGetResource]]

## 👥 Es referenciado por

- [[menu-config-controller|MenuConfigController]] *(static_call)*
- [[roles-config-component|RolesConfigComponent]] *(const_fetch)*
- [[roles-config-controller|RolesConfigController]] *(static_call)*
- [[users-config-component|UsersConfigComponent]] *(static_call)*
- [[users-config-create-component|UsersConfigCreateComponent]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.