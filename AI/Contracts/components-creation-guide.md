# 🚀 Crear Componentes en Lego

Guía práctica y directa para crear componentes en el framework Lego.

## 📁 ¿Dónde van los componentes?

```
Views/
├── Core/     → Framework Lego (Login, Home, Menu)
├── App/      → Tu aplicación específica 
└── Shared/   → Reutilizable por ambos
```

**Cada componente = 1 carpeta con 3 archivos:**
```
Views/Core/MiComponente/
├── MiComponenteComponent.php  ← Lógica PHP
├── mi-componente.css          ← Estilos
└── mi-componente.js           ← JavaScript
```

## ⚡ Arquitectura SPA con Window Manager

**Lego es una SPA (Single Page Application):**

1. **`/` o `/admin/`** → Carga el `MainComponent` (layout completo)
2. **`MainComponent`** contiene:
   - 📋 `MenuComponent` (sidebar con links)  
   - 📦 `HeaderComponent` (barra superior)
   - 🖼️ `<div id="home-page">` (contenedor de módulos)

3. **Los componentes se cargan dinámicamente:**
   - Menú usa: `/view/inicio`, `/view/automation`, etc.
   - Window Manager fetch estos endpoints via Ajax
   - Se renderizan dentro de `#home-page` como módulos

## 🛠️ Crear tu componente en 5 pasos

### 1️⃣ Componente PHP
**Archivo:** `Views/Core/MiComponente/MiComponenteComponent.php`

```php
<?php
namespace Views\Core\MiComponente;
use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

// ✅ OPCIONAL: Decorador para refrescar el módulo dinámicamente
#[ApiComponent('/view/mi-ruta', methods: ['GET'])]
class MiComponenteComponent extends CoreComponent
{
    // ✅ Importaciones relativas (como Angular)
    protected $CSS_PATHS = ["./mi-componente.css"];

    public function __construct($config) {
        $this->config = $config;
    }

    protected function component(): string
    {
        // Si quieres enviar datos a JavaScript:
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./mi-componente.js", [
                'mensaje' => 'Hola desde PHP!'
            ])
        ];
       
        return <<<HTML
        <div class="mi-componente-container">
            <h1>¡Mi nuevo componente!</h1>
        </div>
        HTML;
    }
}
```

### 2️⃣ Ruta
**Archivo:** `Routes/Views.php`

```php
use Views\Core\MiComponente\MiComponenteComponent;

Flight::route('GET /mi-ruta', function () {
    if(AdminMiddlewares::isAutenticated()) {
        $component = new MiComponenteComponent([]);
        return Response::uri($component->render());
    }
});
```

### 3️⃣ Menú  
**Archivo:** `Views/Core/Home/Components/MenuComponent/MenuComponent.php`

```php
new MenuItemDto(
    id: "18",
    name: "Mi Componente",
    url: $HOST_NAME . '/view/mi-ruta',
    iconName: "cube-outline"
),
```

### 4️⃣ CSS 
**Archivo:** `Views/Core/MiComponente/mi-componente.css`

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
**Archivo:** `Views/Core/MiComponente/mi-componente.js`

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
#[ApiComponent('/view/inicio', methods: ['GET'])]
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
- **Carga inicial**: Window Manager hace `fetch('/view/inicio')` → Renderiza en `#home-page`
- **Actualización**: Mismo `fetch('/view/inicio')` → Actualiza el contenido del módulo
- **Una sola ruta `/view/inicio`** para carga y refresh - cero duplicación

### ⚙️ **Casos de uso perfectos**
- **Refrescar módulos** sin recargar página
- **Dashboards dinámicos** con datos actualizados
- **Pestañas que se actualizan** automáticamente
- **Cargar componentes** bajo demanda

### 🔧 **Configuración para módulos**
```php
// ✅ Módulo que se puede refrescar dinámicamente
#[ApiComponent('/view/mi-modulo', methods: ['GET'])]

// ✅ Módulo con múltiples acciones
#[ApiComponent('/view/dashboard', methods: ['GET', 'POST'])]

// ✅ Módulo público sin autenticación
#[ApiComponent('/view/publico', methods: ['GET'], requiresAuth: false)]
```

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
// Views/App/Dashboard/DashboardComponent.php
// Views/App/Dashboard/styles/main.css
// Views/App/Dashboard/components/card.css
// Views/App/shared/utils.js

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

1. **Core/App/Shared:** Pon componentes en la carpeta correcta
2. **API opcional:** Usa `#[ApiComponent('/ruta')]` para hacerlo actualizable
3. **Importaciones:** Usa rutas relativas `"./archivo.css"` (RECOMENDADO)
4. **Variables CSS:** SIEMPRE usa `var(--...)` - nunca hardcodees
5. **JavaScript:** Siempre `let context = {CONTEXT}` 
6. **Nombres:** `MiComponenteComponent.php`, `mi-componente.css`
7. **Rutas:** Menú con `/view/`, Routes sin `/view/`

## 🚨 ¿No funciona?

**404 Error:** Revisa que Routes use `/mi-ruta` (sin `view/`)

**CSS/JS no cargan:** Usa `"./archivo.css"` en lugar de rutas absolutas

**No abre:** Mira la consola del navegador para errores

## 🎯 ¡Ya tienes todo!

Ahora solo sigue los 5 pasos y tendrás tu componente funcionando. 

**Ejemplo completo:** Mira `Views/Core/Home/HomeComponent.php` - usa importaciones relativas y decorador API.