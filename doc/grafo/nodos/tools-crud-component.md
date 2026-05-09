---
tipo: component
capa: components-app
namespace: Components\App\ToolsCrud
archivo: components/App/ToolsCrud/ToolsCrudComponent.php
loc: 174
deps: 11
dependents: 1
responsabilidad: Define un componente de tabla CRUD para herramientas, implementando ScreenInterface y gestionando columnas, filas y acciones con server-side pagination.
atributos:
  - ApiComponent
tags:
  - grafo
  - grafo/tipo/component
  - grafo/capa/components-app
  - grafo/atributo/ApiComponent
---
# ToolsCrudComponent

`Components\App\ToolsCrud\ToolsCrudComponent`

📁 [components/App/ToolsCrud/ToolsCrudComponent.php](../../../components/App/ToolsCrud/ToolsCrudComponent.php)

> [!abstract] Responsabilidad
> Define un componente de tabla CRUD para herramientas, implementando ScreenInterface y gestionando columnas, filas y acciones con server-side pagination.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `ToolsCrudComponent` es un componente específico diseñado para gestionar y visualizar herramientas en una interfaz de usuario basada en componentes. Su principal objetivo es proporcionar una vista de tabla con funcionalidades CRUD (Crear, Leer, Actualizar, Eliminar) utilizando paginación del lado del servidor. La implementación de `ScreenInterface` asegura que el componente se integre correctamente en la estructura de menú y navegación del sistema, mientras que el uso de traits como `ScreenTrait` facilita la gestión de propiedades y métodos comunes relacionados con pantallas.
> 
> ### Métodos principales
> 
> 1. **component()**: Este método es responsable de definir la estructura HTML y las configuraciones de la tabla. Define columnas para mostrar datos de herramientas, acciones de fila para editar y eliminar registros, y configura una instancia de `TableComponent` con paginación del lado del servidor.
> 
> 2. **getScreenLabel()**: Este método retorna el nombre de la pantalla, utilizado en la interfaz de usuario para identificar y etiquetar esta vista.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class ToolsCrudComponent {
>         +component()* : string
>         +getScreenLabel()* : string
>     }
>     class CoreComponent {
>         <<abstract>>
>         +CSS_PATHS
>         +JS_PATHS
>         +component()*
>     }
>     class ScreenInterface {
>         <<interface>>
>     }
>     class ScreenTrait {
>         <<trait>>
>     }
>     ToolsCrudComponent <|-- CoreComponent
>     ToolsCrudComponent ..|> ScreenInterface
>     ToolsCrudComponent o-- ScreenTrait
> ```
> 
> ### Cómo encaja
> 
> `ToolsCrudComponent` se integra dentro del sistema como una extensión de `CoreComponent`, aprovechando su estructura base para definir componentes personalizados. Al implementar `ScreenInterface`, asegura que esté correctamente registrada y accesible desde el menú de navegación. El uso de `ScreenTrait` facilita la gestión de propiedades y métodos relacionados con pantallas, proporcionando una abstracción común para todas las pantallas del sistema. Este componente se utiliza en conjunción con `TableComponent`, que maneja la presentación y interactividad de la tabla de herramientas, permitiendo una gestión eficiente de datos a través de paginación del lado del servidor.

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

- [[dimension-value|DimensionValue]]

## 🔗 Constantes referenciadas

- [[tool|Tool]]

## 👥 Es referenciado por

- [[menu-structure|MenuStructure]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.