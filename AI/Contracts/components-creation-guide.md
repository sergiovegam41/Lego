# 🚀 Crear Componentes en Lego

Guía práctica y directa para crear componentes en el framework Lego.

## 🎯 Filosofía Lego: Componentes Declarativos

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

### Ejemplo Completo:

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

**Beneficios:**
- ✅ IDE sabe qué parámetros son válidos
- ✅ Type hints muestran tipos esperados
- ✅ Errores detectados antes de ejecutar
- ✅ Código autodocumentado y claro

---

## 📁 ¿Dónde van los componentes?

```
components/
├── Core/     → Framework Lego (Login, Home, Menu)
└── App/      → Tu aplicación específica
```

**Cada componente = 1 carpeta con 3 archivos:**
```
components/Core/MiComponente/
├── MiComponenteComponent.php  ← Lógica PHP
├── mi-componente.css          ← Estilos
└── mi-componente.js           ← JavaScript
```

## ⚡ Arquitectura SPA con Window Manager

**Lego es una SPA (Single Page Application):**

1. **`/` o `/admin/`** → Carga el `MainComponent` (layout completo)
   - Manejado por `Routes/Web.php`
   - Retorna HTML completo (DOCTYPE, HEAD, BODY)

2. **`MainComponent`** contiene:
   - 📋 `MenuComponent` (sidebar con links)
   - 📦 `HeaderComponent` (barra superior)
   - 🖼️ `<div id="home-page">` (contenedor de módulos)

3. **Los componentes se cargan dinámicamente:**
   - Manejados por `Routes/Component.php`
   - Menú usa: `/component/inicio`, `/component/automation`, etc.
   - Window Manager hace fetch a estos endpoints via Ajax
   - Se renderizan dentro de `#home-page` como módulos
   - Retornan solo HTML parcial (sin DOCTYPE/HEAD/BODY)
   - Assets se sirven desde `/component/nombre/file.css|js`

**Filosofía "Sin Estado en Frontend":**
Los componentes siempre se refrescan desde el servidor, eliminando desfases
y manteniendo el backend como única fuente de verdad.

## 🛠️ Crear tu componente en 5 pasos

### 1️⃣ Componente PHP
**Archivo:** `components/Core/MiComponente/MiComponenteComponent.php`

```php
<?php
namespace Components\Core\MiComponente;
use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

/**
 * MiComponenteComponent - Descripción del componente
 *
 * PARÁMETROS:
 * @param string $title - Título del componente (OBLIGATORIO)
 * @param string $description - Descripción (OBLIGATORIO)
 * @param bool $showIcon - Mostrar icono (OPCIONAL, default: false)
 */
#[ApiComponent('/mi-ruta', methods: ['GET'])]
class MiComponenteComponent extends CoreComponent
{
    // Assets del componente (rutas relativas)
    protected $CSS_PATHS = ["./mi-componente.css"];
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];

    // Constructor con named arguments y tipos específicos
    public function __construct(
        public string $title,
        public string $description,
        public bool $showIcon = false
    ) {}

    protected function component(): string
    {
        // Si quieres enviar datos a JavaScript:
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./mi-componente.js", [
                'title' => $this->title,
                'showIcon' => $this->showIcon
            ])
        ];

        return <<<HTML
        <div class="mi-componente-container">
            <h1>{$this->title}</h1>
            <p>{$this->description}</p>
            {$this->renderIcon()}
        </div>
        HTML;
    }

    private function renderIcon(): string
    {
        if (!$this->showIcon) return '';
        return '<ion-icon name="checkmark-circle"></ion-icon>';
    }
}
```

**Uso del componente:**
```php
// Instanciar con named arguments
$component = new MiComponenteComponent(
    title: "Mi Título",
    description: "Descripción del componente",
    showIcon: true  // Parámetro opcional
);

echo $component->render();
```

### 1.5️⃣ Collections Tipadas (Opcional pero Recomendado)

Si tu componente recibe una **lista de items**, crea una Collection tipada para validación:

**Ejemplo: ItemCollection**
```php
<?php
namespace Components\Core\MiComponente\Collections;

use Components\Core\MiComponente\Dtos\ItemDto;

/**
 * ItemCollection - Colección tipo-safe de ItemDto
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

**Uso en el componente:**
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
        foreach ($this->items as $item) {
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

**Ventajas:**
- ✅ IDE detecta si pasas un tipo incorrecto
- ✅ Validación en runtime automática
- ✅ Métodos útiles: count(), isEmpty(), filter()
- ✅ Type-safe al iterar: `foreach ($items as $item)`

---

### 2️⃣ Ruta (AUTO-DISCOVERY)

**¡El decorador es suficiente!** El sistema de auto-discovery se encarga automáticamente.

**¿Cómo funciona?**
1. `Routes/Component.php` se carga
2. Escanea todos los componentes en `/components`
3. Encuentra tu decorador `#[ApiComponent('/mi-ruta')]`
4. Registra la ruta automáticamente
5. ¡Listo! Tu componente es accesible en `/component/mi-ruta`

**SOLO necesitas registro manual si:**
- Es una página completa (MainComponent, LoginComponent)
- Va en `Routes/Web.php` (entry points)
- Retorna HTML completo (con DOCTYPE/HEAD/BODY)

**Para componentes SPA:** El decorador es TODO lo que necesitas ✅

### 3️⃣ Menú
**Archivo:** `components/Core/Home/Components/MenuComponent/MenuComponent.php`

```php
new MenuItemDto(
    id: "18",
    name: "Mi Componente",
    url: $HOST_NAME . '/component/mi-ruta',
    iconName: "cube-outline"
),
```

### 4️⃣ CSS
**Archivo:** `components/Core/MiComponente/mi-componente.css`

```css
.mi-componente-container {
    padding: var(--spacing-section);
    background: var(--bg-surface);
    border-radius: var(--radius-card);
    box-shadow: var(--shadow-card);
    color: var(--text-primary);
}

/* ✅ SIEMPRE usar variables del sistema */
.mi-card {
    padding: var(--spacing-card);
    margin: var(--space-lg);
    border: var(--border-width) solid var(--border-light);
    border-radius: var(--radius-card);
}
```

### 5️⃣ JavaScript
**Archivo:** `components/Core/MiComponente/mi-componente.js`

```javascript
// 📥 Recibir datos desde PHP
let context = {CONTEXT};


    console.log("Mi componente cargado!");
    
    // Usar datos de PHP si los hay
    if (context && context.arg) {
        console.log("Mensaje desde PHP:", context.arg.mensaje);
    }
    
    // Tu lógica aquí
    const container = document.querySelector('.mi-componente-container');
    container.addEventListener('click', () => {
        alert('¡Funciona!');
    });

```

## 📤 Enviar datos de PHP a JavaScript

**En PHP:** Usa `$JS_PATHS_WITH_ARG` con cualquier array
```php
$this->JS_PATHS_WITH_ARG[] = [
    new ScriptCoreDTO("./mi-componente.js", [
        'usuario' => 'Juan',
        'config' => ['theme' => 'dark'],
        'datos' => $datosDeBD
    ])
];
```

**En JS:** Recibe con `let context = {CONTEXT};`
```javascript
let context = {CONTEXT};

if (context && context.arg) {
    const { usuario, config, datos } = context.arg;
    console.log(`Hola ${usuario}!`, config, datos);
}
```

**Envía cualquier cosa:** arrays, objetos, datos de BD, etc. Se convierte a JSON automáticamente.

## 🚀 Componentes con API (NUEVO)

**¡Un simple decorador los convierte en endpoints API!** Haz cualquier componente actualizable dinámicamente:

### ✅ **Súper simple - Solo un decorador**
```php
<?php
use Core\Attributes\ApiComponent;

// 🚀 Solo esto hace el módulo refrescable dinámicamente
#[ApiComponent('/inicio', methods: ['GET'])]
class HomeComponent extends CoreComponent {
    protected $CSS_PATHS = ["./home.css"];

    // Tu componente normal como siempre
    protected function component(): string {
        return <<<HTML
        <div class="dashboard-container">
            <h1>¡Mi componente actualizable!</h1>
        </div>
        HTML;
    }
}
```

### 🎯 **Funciona en el Window Manager**
- **Carga inicial**: Window Manager hace `fetch('/component/inicio')` → Renderiza en `#home-page`
- **Actualización**: Mismo `fetch('/component/inicio')` → Actualiza el contenido del módulo
- **Una sola ruta `/component/inicio`** para carga y refresh - cero duplicación
- **Assets**: Se sirven desde `/component/inicio/HomeComponent.css|js`

### ⚙️ **Casos de uso perfectos**
- **Refrescar módulos** sin recargar página
- **Dashboards dinámicos** con datos actualizados
- **Pestañas que se actualizan** automáticamente
- **Cargar componentes** bajo demanda

### 🔧 **Configuración para módulos**
```php
// ✅ Módulo que se puede refrescar dinámicamente
#[ApiComponent('/mi-modulo', methods: ['GET'])]

// ✅ Módulo con múltiples acciones
#[ApiComponent('/dashboard', methods: ['GET', 'POST'])]

// ✅ Módulo público sin autenticación
#[ApiComponent('/publico', methods: ['GET'], requiresAuth: false)]
```

**NOTA:** El decorador NO incluye `/component/` - el sistema lo agrega automáticamente.
- Decorador: `#[ApiComponent('/inicio')]`
- Ruta final: `/component/inicio`

### ⚡ **Auto-descubrimiento**
- **Cero configuración** en Routes
- **Escaneo automático** de todos los componentes
- **Registro inteligente** solo si tiene `#[ApiComponent]`

## 🧱 Sistema de Importaciones Relativas

**¡Como en Angular!** Ahora puedes usar rutas relativas que se resuelven automáticamente:

### ✅ **Importaciones simples**
```php
class MiComponenteComponent extends CoreComponent {
    // ✅ Archivo en la misma carpeta del componente
    protected $CSS_PATHS = ["./mi-componente.css"];
    
    // ✅ Con ScriptCoreDTO
    $this->JS_PATHS_WITH_ARG[] = [
        new ScriptCoreDTO("./mi-componente.js", ['data' => $datos])
    ];
}
```

### 📁 **Subcarpetas y rutas complejas**
```php
// Para esta estructura:
// components/App/Dashboard/DashboardComponent.php
// components/App/Dashboard/styles/main.css
// components/App/Dashboard/components/card.css
// components/App/shared/utils.js

class DashboardComponent extends CoreComponent {
    protected $CSS_PATHS = [
        "./styles/main.css",      // → components/App/Dashboard/styles/main.css
        "./components/card.css",  // → components/App/Dashboard/components/card.css
        "../shared/utils.css"     // → components/App/shared/utils.css
    ];
}
```

### 🔄 **Compatibilidad total**
```php
// ✅ Rutas relativas (RECOMENDADO)
protected $CSS_PATHS = ["./mi-componente.css"];

// ✅ Rutas absolutas (sigue funcionando)
protected $CSS_PATHS = ["components/Core/MiComponente/mi-componente.css"];
```

### ⚡ **Cero configuración**
- **Detección automática** de la ubicación del componente
- **Resolución inteligente** de rutas relativas
- **Funciona en cualquier nivel** de carpetas
- **Sin archivos** adicionales que mantener

## 🎨 Variables CSS (siempre úsalas!)

**Colores:**
```css
--bg-surface, --bg-surface-hover
--text-primary, --text-secondary  
--border-light, --accent-primary
```

**Espacios:**
```css
--space-sm, --space-lg, --space-xl
--spacing-card, --spacing-section
```

**Otros:**
```css
--radius-card, --shadow-card
--transition-normal
--font-size-lg, --font-size-xl
```

**❌ NO hagas esto:** `padding: 24px; color: #333;`  
**✅ SÍ haz esto:** `padding: var(--space-xl); color: var(--text-primary);`

## ✅ Reglas importantes

1. **Core/App:** Pon componentes en `components/Core/` o `components/App/`
2. **API opcional:** Usa `#[ApiComponent('/ruta')]` para auto-discovery
3. **Importaciones:** Usa rutas relativas `"./archivo.css"` (RECOMENDADO)
4. **Variables CSS:** SIEMPRE usa `var(--...)` - nunca hardcodees
5. **JavaScript:** Siempre `let context = {CONTEXT}`
6. **Nombres:** `MiComponenteComponent.php`, `mi-componente.css`
7. **Rutas:**
   - Menú: `$HOST_NAME . '/component/mi-ruta'`
   - Decorador: `#[ApiComponent('/mi-ruta')]` (sin `/component/`)

## 🚨 ¿No funciona?

**404 Error:** Revisa que el decorador use `/mi-ruta` (sin `component/`)

**CSS/JS no cargan:** Usa `"./archivo.css"` en lugar de rutas absolutas

**No abre:** Mira la consola del navegador para errores

## 🎯 ¡Ya tienes todo!

Ahora solo sigue los 5 pasos y tendrás tu componente funcionando. 

**Ejemplo completo:** Mira `components/Core/Home/HomeComponent.php` - usa importaciones relativas y decorador API.