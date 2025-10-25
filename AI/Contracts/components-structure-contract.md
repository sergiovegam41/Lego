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

### ✅ Verificación de Contenido
- [ ] ¿El CSS del componente usa variables globales?
- [ ] ¿El JavaScript es modular y específico del componente?
- [ ] ¿El componente es reutilizable?
- [ ] ¿La documentación interna es clara?

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

#[ApiComponent('/inicio', methods: ['GET'])]
class HomeComponent extends CoreComponent
{
    protected $config;
    protected $CSS_PATHS = ["./home.css"];

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./home.js", [])
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
- **Nomenclatura:** PascalCase + sufijo `Component` (`HomeComponent`)
- **CSS Path:** Ruta relativa `"./home.css"` (kebab-case)
- **JS Path:** Ruta relativa `"./home.js"` (kebab-case)
- **HTML limpio:** Estructura clara con clases CSS semánticas
- **Documentación:** Atributos claros para routing (`#[ApiComponent]`)

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

**Última actualización:** 13 de Septiembre 2025