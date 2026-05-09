---
tipo: component
capa: components-app
namespace: Components\App\AuthGroupsConfig
archivo: components/App/AuthGroupsConfig/AuthGroupsConfigComponent.php
loc: 112
deps: 12
dependents: 1
responsabilidad: Define y renderiza la interfaz de lista para grupos de autenticación, incluyendo columnas y acciones de tabla.
atributos:
  - ApiComponent
tags:
  - grafo
  - grafo/tipo/component
  - grafo/capa/components-app
  - grafo/atributo/ApiComponent
---
# AuthGroupsConfigComponent

`Components\App\AuthGroupsConfig\AuthGroupsConfigComponent`

📁 [components/App/AuthGroupsConfig/AuthGroupsConfigComponent.php](../../../components/App/AuthGroupsConfig/AuthGroupsConfigComponent.php)

> [!abstract] Responsabilidad
> Define y renderiza la interfaz de lista para grupos de autenticación, incluyendo columnas y acciones de tabla.

## 🔼 Hereda de

- [[core-component|CoreComponent]]

## 📐 Implementa

- [[screen-interface|ScreenInterface]]

## 🧩 Usa traits

- [[screen-trait|ScreenTrait]]

## 🏷️ Atributos declarativos

- [[api-component|ApiComponent]]

## 🆕 Instancia

- [[column-collection|ColumnCollection]]
- [[column-dto|ColumnDto]]
- [[row-action-dto|RowActionDto]]
- [[row-actions-collection|RowActionsCollection]]
- [[table-component|TableComponent]]

## ⚡ Llamadas estáticas

- [[boolean-renderer|BooleanRenderer]]
- [[dimension-value|DimensionValue]]

## 🔗 Constantes referenciadas

- [[auth-group|AuthGroup]]

## 👥 Es referenciado por

- [[menu-structure|MenuStructure]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.