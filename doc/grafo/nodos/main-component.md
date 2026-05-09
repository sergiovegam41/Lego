---
tipo: component
capa: components-core
namespace: Components\Core\Home\Components\MainComponent
archivo: components/Core/Home/Components/MainComponent/MainComponent.php
loc: 276
deps: 11
dependents: 0
responsabilidad: Renderiza el layout principal de la aplicación SPA, incluyendo MenuComponent y HeaderComponent, y carga dinámicamente el menú desde la base de datos o usa un menú por defecto si falla.
tags:
  - grafo
  - grafo/tipo/component
  - grafo/capa/components-core
---
# MainComponent

`Components\Core\Home\Components\MainComponent\MainComponent`

📁 [components/Core/Home/Components/MainComponent/MainComponent.php](../../../components/Core/Home/Components/MainComponent/MainComponent.php)

> [!abstract] Responsabilidad
> Renderiza el layout principal de la aplicación SPA, incluyendo MenuComponent y HeaderComponent, y carga dinámicamente el menú desde la base de datos o usa un menú por defecto si falla.

## 🔼 Hereda de

- [[core-component|CoreComponent]]

## 🧩 Usa traits

- [[string-methods|StringMethods]]

## 🆕 Instancia

- [[header-component|HeaderComponent]]
- [[menu-component|MenuComponent]]
- [[menu-item-collection|MenuItemCollection]]
- [[menu-item-dto|MenuItemDto]]
- [[script-core-dto|ScriptCoreDTO]]

## ⚡ Llamadas estáticas

- [[menu-item|MenuItem]]

## 📥 Type hints (parámetros)

- [[menu-item|MenuItem]]

## 📤 Tipos de retorno

- [[menu-item-collection|MenuItemCollection]]
- [[menu-item-dto|MenuItemDto]]

---

> [!info] Nota generada
> Esta nota fue generada automáticamente por `php lego docs:graph`. No editar manualmente — los cambios se perderán en la próxima ejecución.