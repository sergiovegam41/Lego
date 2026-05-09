---
tipo: model
capa: app-models
namespace: App\Models
archivo: App/Models/AuthGroup.php
loc: 76
deps: 1
dependents: 5
responsabilidad: Define el modelo de grupos de autenticación, gestionando sus atributos y proporcionando métodos para filtrar y buscar grupos activos.
atributos:
  - ApiGetResource
tags:
  - grafo
  - grafo/tipo/model
  - grafo/capa/app-models
  - grafo/atributo/ApiGetResource
---
# AuthGroup

`App\Models\AuthGroup`

📁 [App/Models/AuthGroup.php](../../../App/Models/AuthGroup.php)

> [!abstract] Responsabilidad
> Define el modelo de grupos de autenticación, gestionando sus atributos y proporcionando métodos para filtrar y buscar grupos activos.

## 🏷️ Atributos declarativos

- [[api-get-resource|ApiGetResource]]

## 👥 Es referenciado por

- [[auth-groups-config-component|AuthGroupsConfigComponent]] *(const_fetch)*
- [[auth-groups-controller|AuthGroupsController]] *(static_call)*
- [[roles-config-create-component|RolesConfigCreateComponent]] *(static_call)*
- [[roles-config-edit-component|RolesConfigEditComponent]] *(static_call)*
- [[users-config-create-component|UsersConfigCreateComponent]] *(static_call)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.