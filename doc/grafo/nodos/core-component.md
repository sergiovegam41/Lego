---
tipo: abstract-class
capa: core-components
namespace: Core\Components\CoreComponent
archivo: Core/Components/CoreComponent/CoreComponent.php
loc: 386
deps: 2
dependents: 44
responsabilidad: Define la clase abstracta base de todos los componentes Lego, encapsulando la carga de assets CSS/JS y el ciclo de renderizado.
tags:
  - grafo
  - grafo/tipo/abstract-class
  - grafo/capa/core-components
---
# CoreComponent

`Core\Components\CoreComponent\CoreComponent`

📁 [Core/Components/CoreComponent/CoreComponent.php](../../../Core/Components/CoreComponent/CoreComponent.php)

> [!abstract] Responsabilidad
> Define la clase abstracta base de todos los componentes Lego, encapsulando la carga de assets CSS/JS y el ciclo de renderizado.

> [!example]- Análisis detallado
> ### Por qué existe
> 
> `CoreComponent` es una clase abstracta fundamental en el framework Lego, diseñada para servir como base común para todos los componentes del sistema. Su principal objetivo es encapsular funcionalidades comunes necesarias para la creación y renderizado de componentes, como la carga automática de assets CSS/JS y el manejo del ciclo de vida de los componentes. La existencia de esta clase permite una estructura modular y reutilizable, facilitando la creación de nuevos componentes con menos boilerplate y asegurando consistencia en cómo se manejan las dependencias y el renderizado.
> 
> ### Métodos principales
> 
> 1. **`component()`**: Este método abstracto debe ser implementado por todas las clases hijas para definir el HTML específico del componente. Es la parte central de cada componente, donde se define su estructura visual y funcional.
> 
> 2. **`resolveRelativePath($path)`**: Resuelve rutas relativas de archivos CSS/JS basadas en la ubicación del archivo del componente. Esto permite referenciar recursos de manera más sencilla y evitar problemas con las rutas absolutas, asegurando que los assets se carguen correctamente.
> 
> 3. **`css_imports()` y `js_imports()`**: Estos métodos generan etiquetas `<link>` para CSS y `<script>` para JS, respectivamente, incluyendo rutas relativas resueltas y un cache buster para evitar problemas de caché. Facilitan la carga eficiente de los recursos necesarios para el componente.
> 
> 4. **`renderChildren()`**: Renderiza todos los componentes hijos definidos en `$children`, soportando instancias de `CoreComponent`, strings HTML directos, arrays de hijos y valores nulos/falsos. Permite una composición flexible de componentes dentro de otros.
> 
> 5. **`renderSlot(array $slotChildren)`**: Renderiza un slot específico de children, útil para componentes que tienen múltiples áreas de contenido (slots). Facilita la creación de componentes más complejos con estructuras definidas por slots.
> 
> ### Diagrama
> 
> ```mermaid
> classDiagram
>     class CoreComponent {
>         <<abstract>>
>         +resolveRelativePath($path)
>         +css_imports()
>         +js_imports()
>         +renderChildren()
>         +renderSlot(array $slotChildren)
>         +component()*
>         +render()
>     }
>     CoreComponent <|-- AuthGroupsConfigComponent
>     CoreComponent <|-- AuthGroupsConfigCreateComponent
>     CoreComponent <|-- AuthGroupsConfigEditComponent
>     CoreComponent <|-- ExampleCreateComponent
>     CoreComponent <|-- ExampleEditComponent
>     CoreComponent <|-- ExampleCrudComponent
>     CoreComponent <|-- MenuConfigComponent
>     CoreComponent <|-- RolesConfigCreateComponent
>     CoreComponent <|-- RolesConfigEditComponent
>     CoreComponent <|-- RolesConfigComponent
>     CoreComponent <|-- ToolsCreateComponent
>     CoreComponent <|-- ToolsEditComponent
> ```
> 
> ### Cómo encaja
> 
> `CoreComponent` actúa como una abstracción base para todos los componentes del framework Lego, proporcionando funcionalidades comunes necesarias para su funcionamiento. Las clases que la extienden (`AuthGroupsConfigComponent`, `ExampleCreateComponent`, entre otras) heredan estas funcionalidades y deben implementar el método `component()` para definir su HTML específico. Esta estructura permite una creación rápida y consistente de nuevos componentes, asegurando que todos compartan un conjunto común de características y comportamientos.

## 🧩 Usa traits

- [[component-context-trait|ComponentContextTrait]]

## 🆕 Instancia

- [[script-core-dto|ScriptCoreDTO]]

## 👥 Es referenciado por

- [[auth-groups-config-component|AuthGroupsConfigComponent]] *(extends)*
- [[auth-groups-config-create-component|AuthGroupsConfigCreateComponent]] *(extends)*
- [[auth-groups-config-edit-component|AuthGroupsConfigEditComponent]] *(extends)*
- [[automation-component|AutomationComponent]] *(extends)*
- [[breadcrumb-component|BreadcrumbComponent]] *(extends)*
- [[button-component|ButtonComponent]] *(extends)*
- [[checkbox-component|CheckboxComponent]] *(extends)*
- [[column-component|ColumnComponent]] *(extends)*
- [[div-component|DivComponent]] *(extends)*
- [[example-create-component|ExampleCreateComponent]] *(extends)*
- [[example-crud-component|ExampleCrudComponent]] *(extends)*
- [[example-edit-component|ExampleEditComponent]] *(extends)*
- [[file-pond-component|FilePondComponent]] *(extends)*
- [[form-actions-component|FormActionsComponent]] *(extends)*
- [[form-component|FormComponent]] *(extends)*
- [[form-group-component|FormGroupComponent]] *(extends)*
- [[form-row-component|FormRowComponent]] *(extends)*
- [[fragment-component|FragmentComponent]] *(extends)*
- [[grid-component|GridComponent]] *(extends)*
- [[header-component|HeaderComponent]] *(extends)*
- [[home-component|HomeComponent]] *(extends)*
- [[icon-button-component|IconButtonComponent]] *(extends)*
- [[image-gallery-component|ImageGalleryComponent]] *(extends)*
- [[input-text-component|InputTextComponent]] *(extends)*
- [[login-component|LoginComponent]] *(extends)*
- [[main-component|MainComponent]] *(extends)*
- [[menu-component|MenuComponent]] *(extends)*
- [[menu-config-component|MenuConfigComponent]] *(extends)*
- [[menu-item-component|MenuItemComponent]] *(extends)*
- [[radio-component|RadioComponent]] *(extends)*
- [[roles-config-component|RolesConfigComponent]] *(extends)*
- [[roles-config-create-component|RolesConfigCreateComponent]] *(extends)*
- [[roles-config-edit-component|RolesConfigEditComponent]] *(extends)*
- [[row-component|RowComponent]] *(extends)*
- [[screen-component|ScreenComponent]] *(extends, type_hint)*
- [[select-component|SelectComponent]] *(extends)*
- [[table-component|TableComponent]] *(extends)*
- [[text-area-component|TextAreaComponent]] *(extends)*
- [[tools-create-component|ToolsCreateComponent]] *(extends)*
- [[tools-crud-component|ToolsCrudComponent]] *(extends)*
- [[tools-edit-component|ToolsEditComponent]] *(extends)*
- [[users-config-component|UsersConfigComponent]] *(extends)*
- [[users-config-create-component|UsersConfigCreateComponent]] *(extends)*

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.