---
tipo: component
capa: components-app
namespace: Components\App\ExampleCrud
archivo: components/App/ExampleCrud/ExampleCrudComponent.php
loc: 192
deps: 11
dependents: 1
responsabilidad: Define un componente de interfaz de usuario para operaciones CRUD de ejemplo, implementando ScreenInterface y utilizando TableComponent con columnas y acciones personalizadas.
atributos:
  - ApiComponent
tags:
  - grafo
  - grafo/tipo/component
  - grafo/capa/components-app
  - grafo/atributo/ApiComponent
---
# ExampleCrudComponent

`Components\App\ExampleCrud\ExampleCrudComponent`

📁 [components/App/ExampleCrud/ExampleCrudComponent.php](../../../components/App/ExampleCrud/ExampleCrudComponent.php)

> [!abstract] Responsabilidad
> Define un componente de interfaz de usuario para operaciones CRUD de ejemplo, implementando ScreenInterface y utilizando TableComponent con columnas y acciones personalizadas.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `ExampleCrudComponent` es un componente de interfaz de usuario diseñado para manejar operaciones CRUD (Crear, Leer, Actualizar, Eliminar) de ejemplo dentro del framework Lego. Su principal objetivo es demostrar cómo implementar una vista de tabla funcional con paginación y acciones personalizadas, siguiendo las mejores prácticas de arquitectura de software.
> 
> La clase resuelve el problema de proporcionar un componente reutilizable que pueda ser utilizado como plantilla para otros CRUDs en la aplicación. Al implementar `ScreenInterface` y utilizando `TableComponent`, asegura una estructura consistente y modular, facilitando la integración con el sistema de gestión de pantallas y componentes del framework.
> 
> ### Métodos principales
> 
> 1. **component()**: Este método es responsable de definir la estructura HTML del componente. Crea una tabla utilizando `TableComponent` con columnas personalizadas y acciones de fila (editar y eliminar). También incluye un botón para crear nuevos registros, que abre un módulo de creación.
> 
> 2. **getScreenLabel()**: Un método auxiliar que devuelve el label de la pantalla, utilizado en el template HTML para mostrar el título de la sección.
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
>     class ScreenInterface {
>         <<interface>>
>         +SCREEN_ID
>         +SCREEN_LABEL
>         +SCREEN_ICON
>         +SCREEN_ROUTE
>         +getScreenLabel()*
>     }
>     class ScreenTrait {
>         <<trait>>
>         +getScreenLabel()*
>     }
>     class ExampleCrudComponent {
>         +component()*
>         +getScreenLabel()*
>     }
>     CoreComponent <|-- ExampleCrudComponent
>     ScreenInterface <|.. ExampleCrudComponent
>     ScreenTrait <|- ExampleCrudComponent
> ```
> 
> ### Cómo encaja
> 
> `ExampleCrudComponent` se integra dentro del sistema Lego como un componente de pantalla que implementa `ScreenInterface`. Esto le permite ser gestionado y renderizado por el sistema de pantallas, asegurando una consistencia en la interfaz de usuario. La clase utiliza `CoreComponent` para acceder a recursos básicos como CSS y JavaScript, y `TableComponent` para manejar la tabla de datos con columnas y acciones personalizadas.
> 
> Al implementar `ScreenInterface`, `ExampleCrudComponent` se registra en el sistema de pantallas del framework, permitiendo que sea accesible desde el menú y gestionada por el window manager. Además, el uso de `ScreenTrait` proporciona una implementación predeterminada para métodos como `getScreenLabel()`, facilitando la reutilización de código.
> 
> En resumen, `ExampleCrudComponent` es un componente central que demuestra cómo implementar funcionalidades CRUD dentro del framework Lego, siguiendo patrones de diseño consistentes y aprovechando las capacidades de los componentes base proporcionados por el sistema.

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

- [[example-crud|ExampleCrud]]

## 👥 Es referenciado por

- [[menu-structure|MenuStructure]] *(const_fetch)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.