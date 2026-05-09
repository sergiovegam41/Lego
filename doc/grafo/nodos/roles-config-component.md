---
tipo: component
capa: components-app
namespace: Components\App\RolesConfig
archivo: components/App/RolesConfig/RolesConfigComponent.php
loc: 172
deps: 12
dependents: 1
responsabilidad: Define un componente de interfaz para gestionar roles, mostrando una tabla con opciones de creación, edición y eliminación.
atributos:
  - ApiComponent
tags:
  - grafo
  - grafo/tipo/component
  - grafo/capa/components-app
  - grafo/atributo/ApiComponent
---
# RolesConfigComponent

`Components\App\RolesConfig\RolesConfigComponent`

📁 [components/App/RolesConfig/RolesConfigComponent.php](../../../components/App/RolesConfig/RolesConfigComponent.php)

> [!abstract] Responsabilidad
> Define un componente de interfaz para gestionar roles, mostrando una tabla con opciones de creación, edición y eliminación.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `RolesConfigComponent` es un componente de interfaz crucial para gestionar roles dentro del sistema de Lego. Su principal función es proporcionar una tabla interactiva que permite a los usuarios ver, crear, editar y eliminar roles de manera eficiente. La necesidad de esta clase surge por la complejidad de la gestión de roles en aplicaciones de admin dashboard, donde se requiere una interfaz clara y funcional para manejar estos permisos.
> 
> ### Métodos principales
> 
> 1. **component()**: Este método es el corazón del componente. Define las columnas y acciones para la tabla que muestra los roles. Utiliza `ColumnCollection` y `RowActionsCollection` para configurar las propiedades de la tabla, como campos visibles, ancho, ordenamiento y filtros. También define acciones como editar y eliminar roles.
> 
> 2. **render()**: Aunque no se muestra explícitamente en el código proporcionado, este método es heredado de `CoreComponent`. Se encarga de renderizar toda la interfaz del componente, incluyendo los estilos y scripts necesarios para su funcionamiento.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class CoreComponent {
>         <<abstract>>
>         +CSS_PATHS
>         +JS_PATHS
>         +component()*
>         +render()
>     }
>     class RolesConfigComponent {
>         +SCREEN_ID
>         +SCREEN_LABEL
>         +SCREEN_ICON
>         +SCREEN_ROUTE
>         +SCREEN_ORDER
>         +SCREEN_VISIBLE
>         +SCREEN_DYNAMIC
>     }
>     CoreComponent <|-- RolesConfigComponent
>     RolesConfigComponent ..|> ScreenInterface
>     RolesConfigComponent o-- ColumnCollection : uses
>     RolesConfigComponent o-- RowActionsCollection : uses
>     RolesConfigComponent o-- TableComponent : uses
> ```
> 
> ### Cómo encaja
> 
> `RolesConfigComponent` se integra dentro del sistema de Lego como una extensión de `CoreComponent`, aprovechando su estructura base para crear una interfaz específica para la gestión de roles. Implementa `ScreenInterface`, lo que asegura que cumpla con los requisitos mínimos para ser considerado una pantalla válida en el framework. Utiliza clases como `ColumnCollection` y `RowActionsCollection` para definir la configuración de la tabla, y `TableComponent` para renderizarla. Este componente se conecta directamente con el modelo `Role`, permitiendo operaciones CRUD sobre los roles desde la interfaz de usuario.

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

- [[role|Role]]

## 👥 Es referenciado por

- [[menu-structure|MenuStructure]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.