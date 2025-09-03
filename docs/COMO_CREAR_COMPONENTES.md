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

## ⚡ Cómo funciona el routing

**Simple:** 
- Menú usa: `/view/mi-ruta`
- Routes/Views.php usa: `/mi-ruta` 
- (El sistema quita automáticamente `view/`)

## 🛠️ Crear tu componente en 5 pasos

### 1️⃣ Componente PHP
**Archivo:** `Views/Core/MiComponente/MiComponenteComponent.php`

```php
<?php
namespace Views\Core\MiComponente;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class MiComponenteComponent extends CoreComponent
{
    protected $CSS_PATHS = ["components/Core/MiComponente/mi-componente.css"];

    public function __construct($config) {
        $this->config = $config;
    }

    protected function component(): string
    {
        // Si quieres enviar datos a JavaScript:
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("components/Core/MiComponente/mi-componente.js", [
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

document.addEventListener('DOMContentLoaded', function() {
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
});
```

## 📤 Enviar datos de PHP a JavaScript

**En PHP:** Usa `$JS_PATHS_WITH_ARG` con cualquier array
```php
$this->JS_PATHS_WITH_ARG[] = [
    new ScriptCoreDTO("components/Core/MiComponente/mi-componente.js", [
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
2. **Variables CSS:** SIEMPRE usa `var(--...)` - nunca hardcodees
3. **JavaScript:** Siempre `let context = {CONTEXT}` y `DOMContentLoaded`  
4. **Nombres:** `MiComponenteComponent.php`, `mi-componente.css`
5. **Rutas:** Menú con `/view/`, Routes sin `/view/`

## 🚨 ¿No funciona?

**404 Error:** Revisa que Routes use `/mi-ruta` (sin `view/`)

**CSS/JS no cargan:** Verifica los paths en el componente PHP

**No abre:** Mira la consola del navegador para errores

## 🎯 ¡Ya tienes todo!

Ahora solo sigue los 5 pasos y tendrás tu componente funcionando. 

**Ejemplo completo:** Mira `Views/Core/Home/` para ver cómo está hecho el dashboard.