# 🧩 Contrato de Estructura de Componentes - LegoPHP Framework

> **⚠️ OBLIGATORIO:** Revisar antes de crear cualquier componente

## 🎯 Propósito
Este contrato define cómo organizar y estructurar componentes en el framework LegoPHP para mantener consistencia y facilitar el mantenimiento.

---

## ✅ QUE SÍ HACER

### 📁 Estructura de Archivos
- **Cada componente debe tener:**
  - `ComponentName.php` - Lógica del componente
  - `component.css` - Estilos específicos
  - `component.js` - JavaScript del componente

- **Ubicación obligatoria:**
  ```
  Views/[Feature]/Components/[ComponentName]/
  ├── ComponentName.php
  ├── component.css
  └── component.js
  ```

### 🏷️ Nomenclatura
- **Componentes:** PascalCase (`MenuSidebar`, `UserProfile`)
- **Carpetas:** PascalCase para componentes
- **Archivos CSS/JS:** Siempre `component.css` y `component.js`

### 🔧 Organización por Características
```
Views/
├── Dashboard/
│   └── Components/
│       ├── StatsCard/
│       └── ChartWidget/
├── Users/
│   └── Components/
│       ├── UserList/
│       └── UserForm/
└── Settings/
    └── Components/
        ├── SettingsPanel/
        └── ConfigForm/
```

---

## ❌ QUE NO HACER

### 🚫 Estructura Incorrecta
```
/* ❌ INCORRECTO */
Components/MenuSidebar.php
css/menu-sidebar.css
js/sidebar.js
```

### 🚫 Nomenclatura Inconsistente
```
/* ❌ INCORRECTO */
menu_sidebar.php
MenuSidebar.css
menu-sidebar.js
```

### 🚫 Archivos Dispersos
```
/* ❌ INCORRECTO - CSS y JS fuera del componente */
Views/Components/MenuSidebar/MenuSidebar.php
assets/css/menu-sidebar.css
assets/js/sidebar.js
```

---

## 📋 CHECKLIST ANTES DEL COMMIT

### ✅ Verificación de Estructura
- [ ] ¿El componente sigue la estructura de carpetas correcta?
- [ ] ¿Los archivos CSS/JS están en la carpeta del componente?
- [ ] ¿La nomenclatura es consistente (PascalCase)?
- [ ] ¿El componente está en la característica correcta?
- [ ] ¿Los nombres de archivo son `component.css` y `component.js`?

### ✅ Verificación de Contenido
- [ ] ¿El CSS del componente usa variables globales?
- [ ] ¿El JavaScript es modular y específico del componente?
- [ ] ¿El componente es reutilizable?
- [ ] ¿La documentación interna es clara?

---

## 📝 EJEMPLO DE COMPONENTE CORRECTO

### Estructura de Archivos:
```
Components/Core/Home/
├── HomeComponent.php
├── component.css
└── component.js
```

### Código del Componente PHP:
```php
<?php

namespace Components\Core\Home;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

#[ApiComponent('/inicio', methods: ['GET'])]
class HomeComponent extends CoreComponent
{
    protected $config;
    protected $CSS_PATHS = ["./component.css"];

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./component.js", [])
        ];

        return <<<HTML
        <div class="home-container">
            <div class="welcome-header">
                <h1>¡Bienvenido a Lego!</h1>
                <p>Dashboard de administración</p>
            </div>

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
        </div>
        HTML;
    }
}
```

### ✅ Elementos Clave del Ejemplo:
- **Namespace correcto:** `Components\Core\Home`
- **Nomenclatura:** PascalCase (`HomeComponent`)
- **CSS Path:** Referencia a `./component.css`
- **JS Path:** Referencia a `./component.js`
- **HTML limpio:** Estructura clara con clases CSS semánticas
- **Documentación:** Atributos claros para routing

---

## 🆕 CREAR NUEVOS COMPONENTES

### Proceso paso a paso:
1. **Identificar la característica** donde pertenece
2. **Crear la carpeta** en `Views/[Feature]/Components/[ComponentName]/`
3. **Crear los tres archivos** básicos
4. **Seguir el contrato de CSS** para estilos
5. **Implementar funcionalidad** manteniendo cohesión
6. **Probar** en diferentes contextos

### Plantilla base:
```
Views/[Feature]/Components/[ComponentName]/
├── ComponentName.php      # Lógica y renderizado
├── component.css          # Estilos específicos
└── component.js          # Comportamiento específico
```

---

## 🔄 REUTILIZACIÓN DE COMPONENTES

### Componentes Globales
- Para componentes usados en múltiples características
- Ubicar en `Views/Shared/Components/`
- Mantener la misma estructura

### Componentes Específicos
- Para funcionalidad particular de una característica
- Mantener en la carpeta de la característica
- No reutilizar fuera de contexto sin refactorizar

---

## 🚀 RECURSOS RÁPIDOS

### Comandos útiles:
```bash
# Crear estructura básica de componente
mkdir -p "Views/[Feature]/Components/[ComponentName]"
touch "Views/[Feature]/Components/[ComponentName]/ComponentName.php"
touch "Views/[Feature]/Components/[ComponentName]/component.css"
touch "Views/[Feature]/Components/[ComponentName]/component.js"
```

### Archivos clave:
- `Views/` - Raíz de todas las vistas
- `Views/Shared/Components/` - Componentes globales
- `assets/css/core/base.css` - Variables para CSS de componentes

---

> **💡 Recuerda:** Una estructura consistente hace que cualquier desarrollador pueda encontrar y modificar componentes rápidamente.

**Última actualización:** 13 de Septiembre 2025