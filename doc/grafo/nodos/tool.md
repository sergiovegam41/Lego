---
tipo: model
capa: app-models
namespace: App\Models
archivo: App/Models/Tool.php
loc: 114
deps: 2
dependents: 3
responsabilidad: Define el modelo Eloquent para herramientas, gestionando sus atributos, relaciones y proporcionando métodos de acceso y alcances personalizados.
atributos:
  - ApiGetResource
tags:
  - grafo
  - grafo/tipo/model
  - grafo/capa/app-models
  - grafo/atributo/ApiGetResource
---
# Tool

`App\Models\Tool`

📁 [App/Models/Tool.php](../../../App/Models/Tool.php)

> [!abstract] Responsabilidad
> Define el modelo Eloquent para herramientas, gestionando sus atributos, relaciones y proporcionando métodos de acceso y alcances personalizados.

## 🏷️ Atributos declarativos

- [[api-get-resource|ApiGetResource]]

## 🔗 Constantes referenciadas

- [[tool-feature|ToolFeature]]

## 👥 Es referenciado por

- [[tool-feature|ToolFeature]] *(const_fetch)*
- [[tools-controller|ToolsController]] *(static_call)*
- [[tools-crud-component|ToolsCrudComponent]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.