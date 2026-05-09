---
tipo: model
capa: app-models
namespace: App\Models
archivo: App/Models/ExampleCrud.php
loc: 201
deps: 2
dependents: 3
responsabilidad: Define un modelo Eloquent para gestionar operaciones de lectura CRUD con paginación, filtros y búsqueda, expone automáticamente endpoints API GET-ONLY y encapsula lógica de acceso a datos y relaciones.
atributos:
  - ApiGetResource
tags:
  - grafo
  - grafo/tipo/model
  - grafo/capa/app-models
  - grafo/atributo/ApiGetResource
---
# ExampleCrud

`App\Models\ExampleCrud`

📁 [App/Models/ExampleCrud.php](../../../App/Models/ExampleCrud.php)

> [!abstract] Responsabilidad
> Define un modelo Eloquent para gestionar operaciones de lectura CRUD con paginación, filtros y búsqueda, expone automáticamente endpoints API GET-ONLY y encapsula lógica de acceso a datos y relaciones.

## 🏷️ Atributos declarativos

- [[api-get-resource|ApiGetResource]]

## 🔗 Constantes referenciadas

- [[example-crud-image|ExampleCrudImage]]

## 👥 Es referenciado por

- [[example-crud-component|ExampleCrudComponent]] *(const_fetch)*
- [[example-crud-controller|ExampleCrudController]] *(static_call)*
- [[example-crud-image|ExampleCrudImage]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.