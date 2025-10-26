# 🧩 Contrato de Estructura de Componentes - LegoPHP Framework

> **⚠️ OBLIGATORIO:** Revisar antes de crear cualquier componente

## 🎯 Propósito
Este contrato define cómo organizar y estructurar componentes en el framework LegoPHP para mantener consistencia y facilitar el mantenimiento.

## 🚀 Filosofía Lego: Componentes Declarativos

**Lego se inspira en Flutter:** Los componentes son bloques tipo-safe que se ensamblan de forma declarativa.

### Principios Fundamentales:

1. **Named Arguments con Tipos Específicos**
   - Cada parámetro tiene un tipo definido
   - Parámetros obligatorios y opcionales claramente marcados
   - IDE autocomplete y validación

2. **No más `$config` genérico**
   - ❌ Antes: `new Component(['option' => 'value'])`
   - ✅ Ahora: `new Component(option: 'value', title: 'Mi Título')`

3. **Collections Tipadas para Validación**
   - Colecciones específicas validan tipos en runtime
   - Ejemplo: `MenuItemCollection` solo acepta `MenuItemDto`
   - Type-safety sin sacrificar flexibilidad

4. **Composición Declarativa**
   - Los componentes pueden contener otros componentes
   - Construcción de UI clara y predecible
   - Similar a Flutter: `Column(children: [Text(), Button()])`

### Ejemplo de Uso:
```php
// ✅ NUEVA API - Type-safe y declarativa
new MenuComponent(
    options: new MenuItemCollection(
        new MenuItemDto(id: "1", name: "Home", url: "/", iconName: "home"),
        new MenuItemDto(id: "2", name: "Settings", url: "/settings", iconName: "cog")
    ),
    title: "Mi App",              // Obligatorio
    subtitle: "v1.0",              // Obligatorio
    icon: "menu-outline",          // Obligatorio
    searchable: true,              // Opcional
    resizable: true                // Opcional
)
```

---

## ✅ QUE SÍ HACER

### 📁 Estructura de Archivos
- **Cada componente debe tener:**
  - `ComponentName.php` - Lógica del componente
  - `component.css` - Estilos específicos
  - `component.js` - JavaScript del componente

- **Ubicación obligatoria:**
  ```
  components/[Core|App]/[ComponentName]/
  ├── ComponentNameComponent.php
  ├── component-name.css
  └── component-name.js
  ```

### 🏷️ Nomenclatura
- **Componentes:** PascalCase + sufijo `Component` (`MenuSidebarComponent`, `UserProfileComponent`)
- **Carpetas:** PascalCase para componentes
- **Archivos CSS/JS:** kebab-case con nombre del componente (`menu-sidebar.css`, `user-profile.js`)

### 🔧 Organización por Características
```
components/
├── Core/                    → Componentes del framework
│   ├── Login/
│   ├── Home/
│   └── Automation/
└── App/                     → Componentes de tu aplicación
    ├── Dashboard/
    │   ├── StatsCard/
    │   └── ChartWidget/
    ├── Users/
    │   ├── UserList/
    │   └── UserForm/
    └── Settings/
        ├── SettingsPanel/
        └── ConfigForm/
```

---

## ❌ QUE NO HACER

### 🚫 Estructura Incorrecta
```
/* ❌ INCORRECTO */
components/MenuSidebar.php
css/menu-sidebar.css
js/sidebar.js
```

### 🚫 Nomenclatura Inconsistente
```
/* ❌ INCORRECTO */
menu_sidebar_component.php  // Sin PascalCase ni sufijo correcto
MenuSidebar.css            // Debería ser kebab-case
menu-sidebar.js            // Correcto pero sin consistencia
```

### 🚫 Archivos Dispersos
```
/* ❌ INCORRECTO - CSS y JS fuera del componente */
components/MenuSidebar/MenuSidebarComponent.php
assets/css/menu-sidebar.css
assets/js/sidebar.js
```

---

## 📋 CHECKLIST ANTES DEL COMMIT

### ✅ Verificación de Estructura
- [ ] ¿El componente está en `components/Core/` o `components/App/`?
- [ ] ¿Los archivos CSS/JS están en la carpeta del componente?
- [ ] ¿La clase tiene sufijo `Component` en PascalCase?
- [ ] ¿Los archivos CSS/JS usan kebab-case?
- [ ] ¿Usa rutas relativas (`./archivo.css`) para imports?

### ✅ Verificación de API Declarativa (NUEVA)
- [ ] ¿El constructor usa named arguments con tipos específicos?
- [ ] ¿Los parámetros son public properties (`public string $title`)?
- [ ] ¿Los parámetros obligatorios están claramente definidos?
- [ ] ¿Los parámetros opcionales tienen valores por defecto?
- [ ] ¿NO usa `$config` genérico?
- [ ] ¿Las colecciones usan clases tipo-safe (ej: `MenuItemCollection`)?
- [ ] ¿La documentación PHPDoc describe los parámetros?

### ✅ Verificación de Contenido
- [ ] ¿El CSS del componente usa variables globales?
- [ ] ¿El JavaScript es modular y específico del componente?
- [ ] ¿El componente es reutilizable?
- [ ] ¿La documentación interna es clara?
- [ ] ¿Los métodos condicionales son privados y descriptivos?

---

## 📝 EJEMPLO DE COMPONENTE CORRECTO

### Estructura de Archivos:
```
components/Core/Home/
├── HomeComponent.php
├── home.css
└── home.js
```

### Código del Componente PHP:
```php
<?php

namespace Components\Core\Home;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

/**
 * HomeComponent - Página de inicio del dashboard
 *
 * PARÁMETROS:
 * @param string $welcomeTitle - Título de bienvenida (OBLIGATORIO)
 * @param string $subtitle - Subtítulo descriptivo (OBLIGATORIO)
 * @param bool $showStats - Mostrar tarjeta de estadísticas (OPCIONAL, default: true)
 */
#[ApiComponent('/inicio', methods: ['GET'])]
class HomeComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./home.css"];
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];

    // Constructor con named arguments y tipos específicos
    public function __construct(
        public string $welcomeTitle,
        public string $subtitle,
        public bool $showStats = true
    ) {}

    protected function component(): string
    {
        // Enviar datos a JavaScript si es necesario
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./home.js", [
                'showStats' => $this->showStats
            ])
        ];

        return <<<HTML
        <div class="home-container">
            <div class="welcome-header">
                <h1>{$this->welcomeTitle}</h1>
                <p>{$this->subtitle}</p>
            </div>

            {$this->renderStatsCard()}
        </div>
        HTML;
    }

    private function renderStatsCard(): string
    {
        if (!$this->showStats) return '';

        return <<<HTML
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-icon">
                    <ion-icon name="stats-chart-outline"></ion-icon>
                </div>
                <div class="card-content">
                    <h3>Estadísticas</h3>
                    <p>Vista general del sistema</p>
                </div>
            </div>
        </div>
        HTML;
    }
}
```

**Uso del componente:**
```php
// Instanciar con named arguments
$component = new HomeComponent(
    welcomeTitle: "¡Bienvenido a Lego!",
    subtitle: "Dashboard de administración",
    showStats: true  // Parámetro opcional
);

echo $component->render();
```

### ✅ Elementos Clave del Ejemplo:
- **Named Arguments:** Constructor con parámetros tipados y nombres claros
- **Public Properties:** `public string $welcomeTitle` permite acceso directo
- **Parámetros Opcionales:** `public bool $showStats = true` con valor por defecto
- **Type Safety:** IDE detecta tipos incorrectos antes de ejecutar
- **Namespace correcto:** `Components\Core\Home`
- **Nomenclatura:** PascalCase + sufijo `Component` (`HomeComponent`)
- **CSS Path:** Ruta relativa `"./home.css"` (kebab-case)
- **JS Path:** Ruta relativa `"./home.js"` (kebab-case)
- **HTML limpio:** Estructura clara con clases CSS semánticas
- **Documentación:** Atributos claros para routing (`#[ApiComponent]`)
- **Métodos privados:** `renderStatsCard()` para lógica condicional clara

---

## 🧱 COLLECTIONS TIPADAS (Patrón Recomendado)

Si tu componente recibe **listas de items**, crea una Collection tipo-safe:

### Estructura de una Collection:
```
components/Core/MiComponente/
├── MiComponenteComponent.php
├── Collections/
│   └── ItemCollection.php
├── Dtos/
│   └── ItemDto.php
├── mi-componente.css
└── mi-componente.js
```

### Ejemplo de Collection:
```php
<?php
namespace Components\Core\MiComponente\Collections;

use Components\Core\MiComponente\Dtos\ItemDto;

/**
 * ItemCollection - Colección tipo-safe de ItemDto
 * Solo acepta objetos de tipo ItemDto
 */
class ItemCollection implements \IteratorAggregate, \Countable
{
    /** @var ItemDto[] */
    private array $items;

    public function __construct(ItemDto ...$items)
    {
        $this->items = $items;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
```

### Uso en el componente:
```php
use Components\Core\MiComponente\Collections\ItemCollection;
use Components\Core\MiComponente\Dtos\ItemDto;

class MiComponenteComponent extends CoreComponent
{
    public function __construct(
        public ItemCollection $items,  // Type-safe!
        public string $title
    ) {}

    protected function component(): string
    {
        $itemsHtml = "";
        foreach ($this->items as $item) {  // IDE sabe que $item es ItemDto
            $itemsHtml .= "<li>{$item->name}</li>";
        }

        return <<<HTML
        <ul>{$itemsHtml}</ul>
        HTML;
    }
}

// Instanciar:
new MiComponenteComponent(
    items: new ItemCollection(
        new ItemDto(name: "Item 1"),
        new ItemDto(name: "Item 2")
    ),
    title: "Mi Lista"
);
```

### Beneficios:
- ✅ IDE detecta si pasas un tipo incorrecto
- ✅ Validación en runtime automática
- ✅ Métodos útiles: count(), isEmpty(), filter()
- ✅ Type-safe al iterar: IDE autocompleta propiedades de ItemDto

---

## 🆕 CREAR NUEVOS COMPONENTES

### Proceso paso a paso:
1. **Decidir ubicación:** `components/Core/` (framework) o `components/App/` (tu app)
2. **Usar CLI:** `php lego make:component NombreComponente --path=App`
3. **O crear manualmente** la carpeta en `components/[Core|App]/[ComponentName]/`
4. **Crear los tres archivos** básicos siguiendo nomenclatura
5. **Seguir el contrato de CSS** para estilos (variables CSS)
6. **Implementar funcionalidad** manteniendo cohesión
7. **Probar** en diferentes contextos

### Plantilla base:
```
components/[Core|App]/[ComponentName]/
├── ComponentNameComponent.php  # Lógica y renderizado
├── component-name.css          # Estilos específicos
└── component-name.js           # Comportamiento específico
```

---

## 🔄 REUTILIZACIÓN DE COMPONENTES

### Componentes del Framework (Core)
- Para componentes base del framework (Login, Home, etc.)
- Ubicar en `components/Core/`
- Mantener la misma estructura
- No modificar sin entender impacto

### Componentes de Aplicación (App)
- Para funcionalidad específica de tu aplicación
- Ubicar en `components/App/`
- Organizar por feature si es necesario
- Libre para modificar según necesidades

---

## 🚀 RECURSOS RÁPIDOS

### Comandos útiles:
```bash
# Crear componente con CLI (RECOMENDADO)
php lego make:component UserCard --path=App

# O crear manualmente
mkdir -p "components/App/UserCard"
touch "components/App/UserCard/UserCardComponent.php"
touch "components/App/UserCard/user-card.css"
touch "components/App/UserCard/user-card.js"
```

### Archivos clave:
- `components/Core/` - Componentes del framework
- `components/App/` - Tus componentes
- `assets/css/core/base.css` - Variables CSS globales
- `Core/Components/CoreComponent/CoreComponent.php` - Clase base

---

> **💡 Recuerda:** Una estructura consistente hace que cualquier desarrollador pueda encontrar y modificar componentes rápidamente.

**Última actualización:** 25 de Octubre 2025