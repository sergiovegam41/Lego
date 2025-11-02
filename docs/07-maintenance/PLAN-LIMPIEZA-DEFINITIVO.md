# PLAN DE LIMPIEZA DEFINITIVO - PROYECTO LEGO

**Fecha:** 2 de Noviembre, 2025
**Versión:** 2.0 - Consolidación de Todos los Análisis
**Estado:** Listo para Ejecución

---

## 📊 RESUMEN EJECUTIVO CONSOLIDADO

Este plan integra **4 análisis diferentes** realizados por múltiples instancias de IA, consolidando la información en un plan único, definitivo y ejecutable dividido en fases incrementales.

### Hallazgos Totales

| Categoría | Cantidad | Impacto |
|-----------|----------|---------|
| **Clases PHP muertas** | 16 clases | 11% del total |
| **Archivos JavaScript obsoletos** | 4 archivos | Backups y ejemplos |
| **Archivos CSS huérfanos** | 35 archivos | 95% del CSS total |
| **Variables CSS sin usar** | 135 variables | 45% del total |
| **Componentes no usados** | 10 componentes | Listos pero sin implementar |
| **Inconsistencias estructurales** | 5 casos | Naming y organización |
| **Errores críticos** | 2 bugs | CSS hex + decorador faltante |
| **Código duplicado** | 2 casos | ApiClient + Forms |

### Impacto Esperado

- **Reducción de código:** ~150-200KB (-4%)
- **Mejora en mantenibilidad:** +25%
- **Reducción tiempo búsqueda:** -20%
- **Eliminación bugs imports:** -30%
- **Tiempo total estimado:** 18-24 horas (distribuidas en 7 fases)

---

## 🎯 ESTRUCTURA DEL PLAN POR FASES

El plan se divide en **7 fases** con diferentes niveles de riesgo y tiempo estimado:

```
FASE 1: Limpieza Segura (BAJO RIESGO)        → 2-3 horas
FASE 2: Corrección de Typos (BAJO RIESGO)    → 30 min
FASE 3: Consolidación Duplicados (MEDIO)     → 4-6 horas
FASE 4: Limpieza CSS (ALTO RIESGO)           → 6-8 horas
FASE 5: Componentes LEGO (MEDIO)             → 3-4 horas
FASE 6: Refactoring Estructura (ALTO)        → 1-2 horas
FASE 7: Optimizaciones Finales (BAJO)        → 2-3 horas
```

---

## ═══════════════════════════════════════════
## FASE 1: LIMPIEZA SEGURA - ARCHIVOS MUERTOS
## ═══════════════════════════════════════════

**⏱️ Tiempo:** 2-3 horas
**🎯 Riesgo:** BAJO
**📊 Archivos afectados:** 18 archivos a eliminar

### 1.1 Archivos JavaScript Sin Uso (3 archivos)

```bash
# Backups explícitos
rm assets/js/core/base-lego-framework-backup.js

# Versiones antiguas
rm components/shared/Forms/SelectComponent/select-old.js

# Archivos de ejemplo
rm assets/js/core/api/ApiClient.example.js
```

**Justificación:**
- `base-lego-framework-backup.js`: Backup del archivo principal, no se importa
- `select-old.js`: Versión anterior a refactorización MVC
- `ApiClient.example.js`: Solo ejemplos de documentación (285 líneas)

### 1.2 Clases PHP Definitivamente Muertas (6 archivos)

```bash
# CustomErrorCodes (0 usos confirmados)
rm Core/Models/CustomErrorCodes.php

# Auth Groups sin usar (sistema preparado pero no implementado)
rm App/Controllers/Auth/Providers/AuthGroups/Admin/Constants/AdminRoles.php
rm App/Controllers/Auth/Providers/AuthGroups/Admin/Rules/AdminRules.php
rm App/Controllers/Auth/Providers/AuthGroups/Api/Constants/ApiRoles.php
rm App/Controllers/Auth/Providers/AuthGroups/Api/Middlewares/ApiMiddlewares.php
rm App/Controllers/Auth/Providers/AuthGroups/Api/Rules/ApiRules.php
```

**Justificación:**
- Análisis detectó 0 instanciaciones, 0 extensiones
- CustomErrorCodes: Nunca usado
- Auth Groups: Estructura preparatoria sin implementación real

### 1.3 Comandos CLI Sin Uso (4 archivos - VERIFICAR PRIMERO)

**⚠️ IMPORTANTE:** Verificar que no se usen en scripts de deployment antes de eliminar.

```bash
# Buscar referencias primero
grep -r "MakeComponentCommand" . --include="*.sh" --include="*.json"
grep -r "StorageCheckCommand" . --include="*.sh" --include="*.json"
grep -r "HelpCommand" . --include="*.sh" --include="*.json"
grep -r "InitCommand" . --include="*.sh" --include="*.json"

# Si NO hay resultados, es seguro eliminar:
rm Core/Commands/MakeComponentCommand.php
rm Core/Commands/StorageCheckCommand.php
rm Core/Commands/HelpCommand.php
rm Core/Commands/InitCommand.php
```

### 1.4 IDE Helpers y Archivos de Desarrollo (4+ archivos)

```bash
# IDE helpers (generados automáticamente, no deben estar en git)
rm components/shared/Buttons/Buttons/_ide_helper.php
rm components/shared/Essentials/Essentials/_ide_helper.php
rm components/shared/Forms/Forms/_ide_helper.php
rm components/shared/Navigation/Navigation/_ide_helper.php

# Archivos de testing local
rm cookies.txt

# Script de debug (mover a /scripts/)
mkdir -p scripts
mv debug_routes.php scripts/
```

### 1.5 Actualizar .gitignore

Agregar estas líneas para evitar que vuelvan a entrar archivos generados:

```gitignore
# IDE Helpers
*_ide_helper.php
_ide_helper.php

# Testing files
cookies.txt

# Generated files
routeMap.json

# Backups
*.backup.js
*-backup.js
*-backup.*
*.bak
*-old.js
*-old.*

# Temporales
*.tmp
*.log
```

### ✅ Checklist Fase 1

- [ ] Verificar que archivos JS no tienen imports
- [ ] Verificar clases PHP con grep
- [ ] Buscar comandos CLI en scripts externos
- [ ] Eliminar todos los archivos listados
- [ ] Actualizar .gitignore
- [ ] Ejecutar `git status`
- [ ] Probar aplicación completa:
  - [ ] Login/logout funciona
  - [ ] Menú de navegación carga
  - [ ] Formularios funcionan
  - [ ] CRUD básico funciona
- [ ] Commit: `"fase-1: eliminar archivos muertos y actualizar .gitignore"`

---

## ═══════════════════════════════════════════
## FASE 2: CORRECCIÓN DE TYPOS Y NOMENCLATURA
## ═══════════════════════════════════════════

**⏱️ Tiempo:** 30 minutos
**🎯 Riesgo:** BAJO
**📊 Archivos afectados:** 1 renombrado + 1 modificación

### 2.1 Corregir SidebarScrtipt.js (typo en nombre)

```bash
# Renombrar archivo
mv assets/js/core/modules/sidebar/SidebarScrtipt.js \
   assets/js/core/modules/sidebar/SidebarScript.js

# Actualizar import en base-lego-framework.js
# En macOS:
sed -i '' 's/SidebarScrtipt\.js/SidebarScript.js/g' assets/js/core/base-lego-framework.js

# En Linux:
sed -i 's/SidebarScrtipt\.js/SidebarScript.js/g' assets/js/core/base-lego-framework.js
```

**Justificación:**
- Typo evidente: dice "Scrtipt" en lugar de "Script"
- Archivo sí se usa (importado en base-lego-framework.js)
- Simple de corregir sin riesgo

### ✅ Checklist Fase 2

- [ ] Renombrar archivo SidebarScrtipt.js → SidebarScript.js
- [ ] Actualizar import en base-lego-framework.js
- [ ] Verificar que no hay más referencias al nombre antiguo
- [ ] Probar que sidebar funciona correctamente:
  - [ ] Sidebar se despliega
  - [ ] Menús desplegables (toggleSubMenu) funcionan
  - [ ] No hay errores 404 en consola
- [ ] Commit: `"fase-2: corregir typo SidebarScrtipt → SidebarScript"`

---

## ═══════════════════════════════════════════
## FASE 3: CONSOLIDACIÓN DE CÓDIGO DUPLICADO
## ═══════════════════════════════════════════

**⏱️ Tiempo:** 4-6 horas
**🎯 Riesgo:** MEDIO
**📊 Archivos afectados:** 2 eliminaciones + múltiples verificaciones

### 3.1 Consolidar ApiClient (CRÍTICO)

**Problema:** Existen 2 versiones de ApiClient con comportamiento diferente:

| Aspecto | Versión 1 (/api/) | Versión 2 (/services/) |
|---------|-------------------|------------------------|
| **Ubicación** | `/assets/js/core/api/ApiClient.js` | `/assets/js/core/services/ApiClient.js` |
| **Líneas** | 361 | 133 |
| **Manejo errores** | ✅ ApiError class | ❌ Simple try-catch |
| **Métodos HTTP** | ✅ GET, POST, PUT, DELETE correctos | ❌ POST para GET (antipatrón) |
| **Exportable** | ✅ export class | ❌ class sin export |
| **Referencias** | 1 (archivo .example) | 0 |
| **Estado** | **MANTENER** | **ELIMINAR** |

**Plan de acción:**

```bash
# Paso 1: Verificar que services/ApiClient NO se usa
grep -r "from.*services/ApiClient" . --include="*.js" | grep -v node_modules
grep -r "services/ApiClient" . --include="*.html" --include="*.php" | grep -v vendor

# Si NO hay output, es seguro eliminar

# Paso 2: Eliminar versión duplicada
rm assets/js/core/services/ApiClient.js

# Paso 3: Verificar si carpeta services está vacía
ls -la assets/js/core/services/

# Si está vacía, eliminar carpeta
rmdir assets/js/core/services/
```

### 3.2 Consolidar Componentes Forms

**Problema:** Componentes duplicados entre `/components/shared/Forms/` y `/components/Core/Forms/`

```bash
# Verificar que Core/Forms esté vacío
find components/Core/Forms/ -type f

# Si está vacío (solo directorios), eliminar
rm -rf components/Core/Forms/

# Verificar que no hay referencias a Core/Forms
grep -r "Core/Forms" . --include="*.js" --include="*.php" | grep -v vendor
```

### 3.3 Eliminar Directorios Vacíos

```bash
# Buscar y eliminar directorios vacíos
find components -type d -empty -delete
```

### ✅ Checklist Fase 3

- [ ] Buscar referencias a services/ApiClient (debe dar 0)
- [ ] Eliminar services/ApiClient.js
- [ ] Verificar que Core/Forms está vacío
- [ ] Eliminar Core/Forms/
- [ ] Eliminar directorios vacíos
- [ ] Probar aplicación completa:
  - [ ] Login/logout (usa API)
  - [ ] Carga de productos (API calls)
  - [ ] CRUD productos (todas las operaciones)
  - [ ] Formularios funcionan
  - [ ] No hay errores en consola
- [ ] Commit: `"fase-3: consolidar ApiClient y eliminar Forms duplicados"`

---

## ═══════════════════════════════════════════
## FASE 4: LIMPIEZA MASIVA DE CSS (CRÍTICA)
## ═══════════════════════════════════════════

**⏱️ Tiempo:** 6-8 horas
**🎯 Riesgo:** ALTO
**📊 Archivos afectados:** 35 archivos CSS + 135 variables

### 4.1 Corregir Error Crítico (PRIMERO)

**⚠️ HACER ESTO ANTES QUE NADA**

```bash
# Error hexadecimal inválido en base.css línea 151
# Cambiar: --color-gray-800: #120120120;  (9 dígitos - INVÁLIDO)
# Por:     --color-gray-800: #121212;     (6 dígitos - VÁLIDO)
```

**Archivo:** `assets/css/core/base.css` línea 151

### 4.2 Consolidar Sistema de Theming (DECISIÓN CRÍTICA)

**Problema:** Variables duplicadas entre `theme-variables.css` y `base.css` con valores diferentes

**OPCIÓN A: Usar solo theme-variables.css (RECOMENDADO)**

```bash
# 1. Verificar que theme-variables.css tiene todas las variables necesarias
cat assets/css/core/theme-variables.css

# 2. Eliminar base.css completamente
rm assets/css/core/base.css

# 3. Actualizar referencias en HTML/PHP que importen base.css
grep -r "base.css" . --include="*.html" --include="*.php"

# 4. Probar tema claro/oscuro funciona
```

**OPCIÓN B: Usar solo base.css**

```bash
# 1. Eliminar theme-variables.css
rm assets/css/core/theme-variables.css

# 2. Actualizar referencias
grep -r "theme-variables.css" . --include="*.html" --include="*.php"
```

**⚠️ DECISIÓN REQUERIDA:** Elegir Opción A o B antes de continuar

### 4.3 Eliminar 135 Variables CSS Sin Usar

**Referencia:** Ver archivo `plan-clean-code/UNUSED_CSS_VARIABLES.txt`

**Estrategia por grupos (eliminar conservadoramente):**

#### Ronda 1: Variables de componentes nunca usados (BAJO RIESGO)

```css
/* Button (16 variables) */
--button-bg-primary, --button-bg-secondary, --button-bg-danger
--button-text-primary, --button-text-secondary, --button-text-danger
--button-border-primary, --button-border-secondary, --button-border-danger
--button-hover-primary, --button-hover-secondary, --button-hover-danger
--button-padding-sm, --button-padding-md, --button-padding-lg
--button-font-size, --button-border-radius, --button-transition

/* Input (13 variables) */
--input-bg, --input-text, --input-border, --input-border-focus
--input-padding, --input-height, --input-font-size
--input-border-radius, --input-placeholder-color
--input-disabled-bg, --input-disabled-text, --input-disabled-border
--input-error-border

/* Modal (4 variables) */
--modal-bg, --modal-text, --modal-border, --modal-shadow

/* Dropdown (5 variables) */
--dropdown-bg, --dropdown-text, --dropdown-border
--dropdown-shadow, --dropdown-item-hover

/* Table (3 variables) */
--table-header-bg, --table-row-hover, --table-border

/* Badge (6 variables) */
--badge-success-bg, --badge-success-text
--badge-warning-bg, --badge-warning-text
--badge-danger-bg, --badge-danger-text
```

**Total Ronda 1:** 47 variables

#### Ronda 2: Paleta de colores no usada (MEDIO RIESGO)

```css
/* Blues */
--blue-50, --blue-200, --blue-300, --blue-400, --blue-600, --blue-900

/* Greens */
--green-50, --green-200, --green-500, --green-900

/* Reds */
--red-50, --red-500, --red-800

/* Oranges */
--orange-50, --orange-100, --orange-200, --orange-300
--orange-400, --orange-600, --orange-800

/* Grays */
--color-gray-500, --color-gray-800, --color-gray-900
```

**Total Ronda 2:** 20 variables

#### Ronda 3: Sistema Z-index completo sin usar (BAJO RIESGO)

```css
--z-dropdown, --z-sticky, --z-fixed
--z-modal-backdrop, --z-modal
--z-popover, --z-tooltip
```

**Total Ronda 3:** 7 variables

#### Ronda 4: Otros sin usar (MEDIO RIESGO)

```css
/* Typography */
--font-family-base
--font-weight-thin, --font-weight-light, --font-weight-normal
--font-weight-medium, --font-weight-semibold, --font-weight-bold
--line-height-tight, --line-height-normal, --line-height-relaxed

/* States */
--state-disabled, --state-focus, --state-hover

/* Borders */
--border-width-2, --border-width-4

/* Spacing */
--space-0, --space-3xl, --space-4xl
```

**Total Ronda 4:** 19 variables

**TOTAL VARIABLES A ELIMINAR:** ~93 variables mínimo

### 4.4 Eliminar 35 Archivos CSS Huérfanos

**Referencia:** Ver archivo `plan-clean-code/ORPHANED_CSS_FILES.txt`

**Estrategia:** Verificar cada archivo antes de eliminar

```bash
# Para cada archivo en ORPHANED_CSS_FILES.txt:
# 1. Abrir archivo y revisar contenido
# 2. Buscar si tiene estilos únicos necesarios
# 3. Si tiene estilos útiles, migrar a archivo activo
# 4. Si no se usa, eliminar

# Ejemplo de verificación:
grep -r "nombre-archivo.css" . --include="*.html" --include="*.php" --include="*.js"
```

**Categorización:**

**Core Assets (3 archivos):**
- `assets/css/core/alert-service.css`
- `assets/css/core/windows-manager.css`
- `assets/css/core/sidebar/menu-style.css`

**Componentes Core (7 archivos):**
- `components/Core/Home/home.css`
- `components/Core/Login/login.css`
- `components/Core/Automation/automation.css`
- Y 4 más de componentes Home...

**Componentes Shared (15 archivos):**
- Forms: button.css, input-text.css, select.css, textarea.css, radio.css, checkbox.css, form.css, form-row.css, filepondcomponent.css
- Essentials: div.css, row.css, column.css, grid.css, table.css, image-gallery.css
- Navigation: breadcrumb.css
- Buttons: icon-button.css

**Componentes App (6 archivos):**
- `components/App/FormsShowcase/forms-showcase.css`
- `components/App/ProductsCrudV3/products-crud-v3.css`
- Y 4 más...

### ✅ Checklist Fase 4

- [ ] ⚠️ **CRÍTICO:** Corregir error hexadecimal en base.css
- [ ] Decidir: Opción A o B para sistema de theming
- [ ] Implementar consolidación de theming elegida
- [ ] Probar tema claro/oscuro funciona
- [ ] Eliminar variables CSS Ronda 1 (bajo riesgo)
- [ ] Probar aplicación visualmente
- [ ] Eliminar variables CSS Ronda 2 (medio riesgo)
- [ ] Probar aplicación exhaustivamente
- [ ] Eliminar variables CSS Ronda 3 y 4
- [ ] Probar de nuevo
- [ ] Revisar lista de archivos CSS huérfanos
- [ ] Categorizar archivos: obsoletos vs útiles
- [ ] Migrar estilos necesarios si hay
- [ ] Eliminar archivos huérfanos
- [ ] **PRUEBAS VISUALES COMPLETAS:**
  - [ ] Todos los componentes se ven correctos
  - [ ] Tema claro funciona
  - [ ] Tema oscuro funciona
  - [ ] Responsive funciona
  - [ ] No hay estilos rotos
- [ ] Commits por ronda:
  - `"fase-4.1: corregir error hexadecimal CSS"`
  - `"fase-4.2: consolidar sistema de theming"`
  - `"fase-4.3: eliminar variables CSS sin usar (ronda 1)"`
  - `"fase-4.3: eliminar variables CSS sin usar (ronda 2-4)"`
  - `"fase-4.4: eliminar archivos CSS huérfanos"`

---

## ═══════════════════════════════════════════
## FASE 5: LIMPIEZA DE COMPONENTES LEGO
## ═══════════════════════════════════════════

**⏱️ Tiempo:** 3-4 horas
**🎯 Riesgo:** MEDIO
**📊 Archivos afectados:** 3-13 componentes

### 5.1 Corregir AutomationComponent (BUG CRÍTICO)

**Problema:** Componente sin decorador `#[ApiComponent]`

```php
// Archivo: components/Core/Automation/AutomationComponent.php
// AGREGAR ANTES DE LA CLASE:

<?php
namespace Components\Core\Automation;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;  // ← AGREGAR ESTE IMPORT

#[ApiComponent('/automation', methods: ['GET'])]  // ← AGREGAR ESTA LÍNEA
class AutomationComponent extends CoreComponent
{
    // ...
}
```

**Justificación:**
- Componente mencionado en rutas pero no registrado
- Falta decorador para auto-discovery
- Ruta `/component/automation` no funciona sin esto

### 5.2 Decidir sobre Componentes No Usados (10 componentes)

**Componentes listos pero no implementados:**

1. CheckboxComponent
2. RadioComponent
3. ImageGalleryComponent
4. GridComponent
5. RowComponent
6. ColumnComponent
7. DivComponent
8. FragmentComponent
9. FormGroupComponent
10. FormActionsComponent

**OPCIÓN A: CONSERVAR (RECOMENDADO)**
- Son funcionales y reutilizables
- Parte del sistema de diseño
- Útiles para features futuras
- Bajo costo de mantenimiento
- **Acción:** Documentar en inventario, NO eliminar

**OPCIÓN B: ELIMINAR**
- Reducir superficie de código
- Solo mantener lo que se usa ahora
- Re-implementar cuando sea necesario
- **Acción:** Hacer backup y eliminar

**⚠️ DECISIÓN REQUERIDA:** Elegir Opción A o B

### 5.3 Decidir sobre Componentes Showcase

**Componentes de demostración:**
- ProductsTableDemoComponent (tiene decorador ✅)
- TableShowcaseComponent (tiene decorador ✅)

**OPCIÓN A: MANTENER**
- Útiles para demos y documentación
- Muestran capacidades del sistema
- Útiles para onboarding
- **Recomendado si es proyecto opensource o con múltiples devs**

**OPCIÓN B: ELIMINAR**
- No son funcionalidad para usuarios finales
- Solo demostraciones
- **Recomendado si es proyecto privado pequeño**

```bash
# Si decides ELIMINAR:
rm -rf components/App/ProductsTableDemo/
rm -rf components/App/TableShowcase/
```

### 5.4 Verificar StorageController

**Análisis:** Detectado como "posible clase muerta" pero es un controlador

```bash
# Verificar que StorageController SÍ se usa
grep -r "StorageController" . --include="*.php" | grep -v vendor

# Verificar rutas
grep -r "storage" Routes/ --include="*.php"
```

**Acción:** Probablemente SÍ se usa. NO eliminar sin verificar.

### ✅ Checklist Fase 5

- [ ] Agregar decorador #[ApiComponent] a AutomationComponent
- [ ] Verificar imports de atributos
- [ ] Probar ruta /component/automation
- [ ] Decidir sobre 10 componentes no usados (Opción A o B)
- [ ] Decidir sobre componentes showcase (Opción A o B)
- [ ] Si se eliminan componentes, hacer backup
- [ ] Actualizar documentación de componentes disponibles
- [ ] Verificar StorageController está en uso
- [ ] Probar aplicación completa:
  - [ ] Todos los componentes LEGO usados funcionan
  - [ ] Rutas de componentes responden
  - [ ] No hay errores de componentes faltantes
  - [ ] Menú no tiene enlaces rotos
- [ ] Commit: `"fase-5: corregir AutomationComponent y limpiar componentes"`

---

## ═══════════════════════════════════════════
## FASE 6: REFACTORING DE ESTRUCTURA (ALTO RIESGO)
## ═══════════════════════════════════════════

**⏱️ Tiempo:** 1-2 horas
**🎯 Riesgo:** ALTO
**📊 Archivos afectados:** 3 movidos + 7-10 modificados

### 6.1 Consolidar Controller → Controllers

**Problema:** Inconsistencia entre singular y plural

**Estado actual:**
```
/Core/
├── Controller/          (singular - legacy)
│   ├── CoreController.php
│   ├── CoreViewController.php
│   └── RestfulController.php
└── Controllers/         (plural - nuevo estándar)
    ├── AbstractCrudController.php
    └── AbstractGetController.php
```

**Plan de acción:**

```bash
# PASO 1: Mover archivos de Controller/ a Controllers/
mv Core/Controller/CoreController.php Core/Controllers/
mv Core/Controller/CoreViewController.php Core/Controllers/
mv Core/Controller/RestfulController.php Core/Controllers/

# PASO 2: Actualizar TODOS los imports usando sed
# En macOS:
find . -name "*.php" -not -path "./vendor/*" -exec sed -i '' 's/Core\\Controller\\/Core\\Controllers\\/g' {} +

# En Linux:
find . -name "*.php" -not -path "./vendor/*" -exec sed -i 's/Core\\Controller\\/Core\\Controllers\\/g' {} +

# ALTERNATIVA: Usar IDE para buscar/reemplazar (RECOMENDADO)
# Buscar: Core\Controller\
# Reemplazar: Core\Controllers\

# PASO 3: Verificar que no quedan referencias antiguas
grep -r "Core\\\\Controller\\\\" . --include="*.php" | grep -v vendor | grep -v Controllers

# PASO 4: Eliminar carpeta vacía
ls Core/Controller/  # Verificar que está vacía
rmdir Core/Controller/

# PASO 5: Regenerar autoload de Composer (CRÍTICO)
composer dump-autoload -o

# PASO 6: Verificar que clases se encuentran
php -r "require 'vendor/autoload.php'; echo class_exists('Core\\Controllers\\CoreController') ? 'OK' : 'ERROR';"
```

**Archivos que se deben modificar (al menos 7):**

1. `/Core/Commands/MapRoutesCommand.php`
2. `/App/Controllers/Products/Controllers/ProductsController.php`
3. `/App/Controllers/Auth/Controllers/AuthGroupsController.php`
4. `/App/Controllers/ComponentsController.php`
5. `/App/Controllers/Storage/Controllers/StorageController.php`
6. `/App/Controllers/Files/Controllers/FilesController.php`
7. `/Routes/Api.php`

### 6.2 Renombrar providers → Providers (PascalCase)

**Problema:** Inconsistencia en capitalización

```bash
# Verificar estructura actual
ls -la Core/ | grep -i provider

# Si existe Core/providers/ (lowercase)
mv Core/providers Core/Providers

# Actualizar namespaces en archivos PHP
find . -name "*.php" -not -path "./vendor/*" -exec sed -i 's/Core\\providers\\/Core\\Providers\\/g' {} +

# Regenerar autoload
composer dump-autoload -o
```

### 6.3 Eliminar Traits/Helpers Sin Uso

```bash
# TimeSet.php - Trait nunca usado
rm Core/Providers/TimeSet.php

# Verificar ActionButtons antes de eliminar
grep -r "ActionButtons::" . --include="*.php" | grep -v vendor
grep -r "new ActionButtons" . --include="*.php" | grep -v vendor

# Si NO hay resultados, eliminar:
rm Core/Helpers/ActionButtons.php
```

### ✅ Checklist Fase 6 (CRÍTICA)

- [ ] **BACKUP:** Crear backup antes de empezar
- [ ] Mover 3 archivos de Controller/ a Controllers/
- [ ] Verificar que se movieron correctamente
- [ ] Actualizar todos los imports (sed o IDE)
- [ ] Verificar que NO quedan referencias a Controller\ (singular)
- [ ] Verificar sintaxis PHP de archivos modificados
- [ ] Eliminar carpeta Controller/ vacía
- [ ] **CRÍTICO:** Ejecutar `composer dump-autoload -o`
- [ ] Verificar que clases se encuentran (comando php -r)
- [ ] Renombrar providers → Providers si es necesario
- [ ] Eliminar TimeSet.php
- [ ] Verificar y eliminar ActionButtons.php si no se usa
- [ ] **PRUEBAS EXHAUSTIVAS:**
  - [ ] Aplicación carga sin errores
  - [ ] Login/logout funciona
  - [ ] Navegación por todos los módulos
  - [ ] CRUD productos (todas las operaciones)
  - [ ] Subida de archivos (FilesController)
  - [ ] Autenticación y grupos (AuthGroupsController)
  - [ ] No hay errores de clase no encontrada en logs
  - [ ] No hay errores 500 en consola del navegador
- [ ] Revisar TODOS los cambios con `git diff`
- [ ] Commit: `"fase-6: unificar nomenclatura Controllers y providers"`

**⚠️ SI ALGO FALLA:**
```bash
git reset --hard HEAD~1
composer dump-autoload
# Verificar que volvió al estado anterior
```

---

## ═══════════════════════════════════════════
## FASE 7: OPTIMIZACIONES FINALES
## ═══════════════════════════════════════════

**⏱️ Tiempo:** 2-3 horas
**🎯 Riesgo:** BAJO
**📊 Archivos afectados:** 1 modificado + documentación

### 7.1 Actualizar .gitignore (Mejoras Adicionales)

Agregar patrones adicionales identificados en análisis:

```gitignore
# (Ya agregado en Fase 1, verificar que esté)
/vendor
.env

# IDE
.vscode/
.idea/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db
desktop.ini

# Node
node_modules/
npm-debug.log
yarn-error.log

# PHP
composer.lock  # Solo si es una library
phpunit.xml
.phpunit.result.cache

# Temporales
*.tmp
*.log
*.cache

# Backups y versiones antiguas
*.backup.*
*-backup.*
*.bak
*-old.*
*-old/

# IDE Helpers
*_ide_helper.php
_ide_helper.php

# Testing
cookies.txt
test-*.txt

# Generated
routeMap.json
storage/logs/*.log
```

### 7.2 Ejecutar Composer Dump-Autoload Optimizado

```bash
composer dump-autoload -o
```

### 7.3 Crear/Actualizar CHANGELOG.md

```markdown
# CHANGELOG

## Limpieza de Código - Noviembre 2025

### Eliminado

**Archivos JavaScript (4):**
- base-lego-framework-backup.js (backup)
- select-old.js (versión antigua)
- ApiClient.example.js (archivo de ejemplo)
- services/ApiClient.js (versión duplicada obsoleta)

**Clases PHP (6-10):**
- CustomErrorCodes.php (nunca usado)
- AdminRoles.php, AdminRules.php (Auth sin implementar)
- ApiRoles.php, ApiMiddlewares.php, ApiRules.php (Auth sin implementar)
- [Si se eliminaron] MakeComponentCommand, StorageCheckCommand, HelpCommand, InitCommand

**Archivos CSS (35):**
- [Listar archivos eliminados específicos]

**Variables CSS (93-135):**
- Variables de componentes sin usar (button, input, modal, etc.)
- Paleta de colores no usada
- Sistema z-index completo sin usar
- Variables de tipografía, states, borders, spacing sin usar

**Componentes (0-13):**
- [Si se eliminaron] ProductsTableDemo, TableShowcase
- [Si se eliminaron] 10 componentes preparados pero no usados

**Helpers/Traits (2):**
- TimeSet.php (trait sin uso)
- ActionButtons.php (helper obsoleto)

**IDE Helpers (4):**
- Todos los archivos _ide_helper.php

### Corregido

**Bugs Críticos:**
- Error hexadecimal inválido en base.css (--color-gray-800: #120120120 → #121212)
- AutomationComponent sin decorador #[ApiComponent]

**Typos:**
- SidebarScrtipt.js → SidebarScript.js

**Duplicaciones:**
- ApiClient consolidado a versión única (/api/)
- Forms duplicadas eliminadas (Core/Forms/)

### Refactorizado

**Estructura:**
- Core/Controller/ → Core/Controllers/ (estandarizado a plural)
- Core/providers/ → Core/Providers/ (estandarizado a PascalCase)

**CSS:**
- Sistema de theming consolidado [especificar cuál se mantuvo]
- Archivos CSS huérfanos eliminados
- Variables CSS sin usar eliminadas

### Optimizado

- Autoload de Composer optimizado
- .gitignore mejorado con más patrones
- Estructura de carpetas consistente
- Imports actualizados

### Métricas

| Métrica | Antes | Después | Mejora |
|---------|-------|---------|--------|
| Clases PHP | 147 | ~137 | -7% |
| Archivos CSS | 37 | ~5 | -87% |
| Variables CSS | 297 | ~178 | -40% |
| Archivos JS huérfanos | 4 | 0 | -100% |
| Inconsistencias | 5 | 0 | -100% |
| Código muerto | ~200KB | 0 | -100% |
```

### 7.4 Crear Documentación de Componentes

Crear archivo: `docs/COMPONENTES_DISPONIBLES.md`

```markdown
# Componentes LEGO Disponibles

## Componentes Activos (en uso)

### Páginas (8)
- HomeComponent
- LoginComponent
- FormsShowcaseComponent
- TableShowcaseComponent (si se mantuvo)
- ProductsCrudV2Component
- ProductsCrudV3Component
- ProductsTableDemoComponent (si se mantuvo)
- AutomationComponent

### Layout (4)
- MainComponent
- MenuComponent
- HeaderComponent
- MenuItemComponent

### Formularios (7)
- InputTextComponent
- TextAreaComponent
- SelectComponent
- ButtonComponent
- FilePondComponent
- FormComponent
- FormRowComponent

### Tablas (1)
- TableComponent

### Navegación (1)
- BreadcrumbComponent

### Botones (1)
- IconButtonComponent

## Componentes Preparados (listos pero no usados)

- CheckboxComponent (si se mantuvo)
- RadioComponent (si se mantuvo)
- ImageGalleryComponent (si se mantuvo)
- GridComponent (si se mantuvo)
- RowComponent (si se mantuvo)
- ColumnComponent (si se mantuvo)
- DivComponent (si se mantuvo)
- FragmentComponent (si se mantuvo)
- FormGroupComponent (si se mantuvo)
- FormActionsComponent (si se mantuvo)
```

### 7.5 Crear Scripts de Validación

Crear archivo: `scripts/validate-css.sh`

```bash
#!/bin/bash
# Validar que no hay archivos CSS sin referencia

echo "Validando CSS..."

# Buscar archivos CSS
CSS_FILES=$(find . -name "*.css" -not -path "./vendor/*" -not -path "./node_modules/*")

ORPHANS=0

for css_file in $CSS_FILES; do
    filename=$(basename "$css_file")

    # Buscar referencias
    references=$(grep -r "$filename" . --include="*.html" --include="*.php" --include="*.js" --exclude="*.css" | wc -l)

    if [ $references -eq 0 ]; then
        echo "⚠️  Archivo sin referencias: $css_file"
        ORPHANS=$((ORPHANS + 1))
    fi
done

echo ""
echo "Archivos huérfanos encontrados: $ORPHANS"

if [ $ORPHANS -gt 0 ]; then
    exit 1
else
    echo "✅ Todos los archivos CSS tienen referencias"
    exit 0
fi
```

```bash
chmod +x scripts/validate-css.sh
```

### ✅ Checklist Fase 7

- [ ] Actualizar .gitignore con patrones adicionales
- [ ] Ejecutar `composer dump-autoload -o`
- [ ] Crear CHANGELOG.md con todos los cambios
- [ ] Crear docs/COMPONENTES_DISPONIBLES.md
- [ ] Crear scripts/validate-css.sh
- [ ] Dar permisos de ejecución a script
- [ ] Probar script de validación
- [ ] Commit: `"fase-7: optimizaciones finales y documentación"`

---

## ═══════════════════════════════════════════
## ORDEN DE EJECUCIÓN RECOMENDADO
## ═══════════════════════════════════════════

**Para minimizar riesgo, seguir este orden:**

1. **FASE 1** → Limpieza segura (BAJO RIESGO) ✅
2. **FASE 2** → Typos (BAJO RIESGO) ✅
3. **FASE 3** → Duplicados (MEDIO RIESGO) ⚠️
4. **FASE 4** → CSS (ALTO RIESGO) 🔴 - La más larga
5. **FASE 5** → Componentes (MEDIO RIESGO) ⚠️
6. **FASE 6** → Refactoring (ALTO RIESGO) 🔴
7. **FASE 7** → Optimizaciones (BAJO RIESGO) ✅

**⚠️ IMPORTANTE:**
- Hacer commit después de cada fase exitosa
- Probar la aplicación después de cada fase
- NO avanzar si hay errores
- Tener backup o trabajar en rama separada

---

## ═══════════════════════════════════════════
## PROCEDIMIENTO DE PRUEBAS POR FASE
## ═══════════════════════════════════════════

### Después de CADA fase:

**Pruebas Básicas:**
- [ ] Aplicación carga sin errores
- [ ] No hay errores en consola del navegador (F12)
- [ ] No hay errores en logs de PHP

**Pruebas Funcionales:**
- [ ] Login/logout funciona
- [ ] Navegación por menú funciona
- [ ] Formularios cargan y funcionan
- [ ] CRUD básico funciona

### Después de FASE 4 (CSS):

**Pruebas Visuales Adicionales:**
- [ ] Todos los componentes se ven correctos
- [ ] Colores son correctos
- [ ] Espaciado es correcto
- [ ] Tema claro funciona
- [ ] Tema oscuro funciona
- [ ] Responsive funciona
- [ ] Probar en Chrome, Firefox, Safari

### Después de FASE 6 (Refactoring):

**Pruebas Exhaustivas:**
- [ ] Todas las rutas funcionan
- [ ] Todos los controladores responden
- [ ] CRUD completo (crear, leer, actualizar, eliminar)
- [ ] Subida de archivos funciona
- [ ] Autenticación funciona
- [ ] Permisos/roles funcionan
- [ ] No hay warnings de PHP sobre clases

### Prueba Final (Después de FASE 7):

**Prueba End-to-End Completa:**
- [ ] Flujo completo de usuario: desde login hasta logout
- [ ] Todas las funcionalidades principales
- [ ] Performance es aceptable
- [ ] No hay regresiones
- [ ] Logs están limpios

---

## ═══════════════════════════════════════════
## COMANDOS ÚTILES DE REFERENCIA
## ═══════════════════════════════════════════

### Búsqueda y Validación

```bash
# Buscar referencias a un archivo
grep -r "nombre-archivo" . --include="*.php" --include="*.js" --include="*.html"

# Buscar uso de una clase PHP
grep -r "ClassName" . --include="*.php" | grep -v vendor

# Buscar imports rotos
find . -name "*.js" -exec grep -H "import.*from" {} \; | grep -v node_modules

# Verificar sintaxis PHP
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; | grep -v "No syntax errors"

# Ver tamaño de directorios
du -sh assets/css/
du -sh components/
```

### Git

```bash
# Ver estado
git status

# Ver cambios
git diff

# Ver cambios de un archivo específico
git diff archivo.php

# Rollback a commit anterior
git reset --hard HEAD~1

# Ver log
git log --oneline -10
```

### Composer

```bash
# Regenerar autoload optimizado
composer dump-autoload -o

# Verificar que clase existe
php -r "require 'vendor/autoload.php'; echo class_exists('Core\\Controllers\\CoreController') ? 'OK' : 'ERROR';"
```

### Backup

```bash
# Crear backup antes de fase riesgosa
mkdir -p backups/pre-fase-$(date +%Y%m%d-%H%M%S)
cp -r Core/ backups/pre-fase-$(date +%Y%m%d-%H%M%S)/
cp -r assets/ backups/pre-fase-$(date +%Y%m%d-%H%M%S)/
```

---

## ═══════════════════════════════════════════
## MÉTRICAS DE ÉXITO ESPERADAS
## ═══════════════════════════════════════════

### Antes de la Limpieza

| Métrica | Valor |
|---------|-------|
| Clases PHP totales | 147 |
| Clases muertas | 16 (11%) |
| Archivos CSS totales | 37 |
| Archivos CSS huérfanos | 35 (95%) |
| Variables CSS totales | 297 |
| Variables CSS sin usar | 135 (45%) |
| Archivos JS obsoletos | 4 |
| Inconsistencias estructurales | 5 |
| Bugs críticos | 2 |
| Código duplicado | 2 casos |
| Tamaño código muerto | ~200KB |

### Después de la Limpieza (Esperado)

| Métrica | Valor | Mejora |
|---------|-------|--------|
| Clases PHP totales | ~137 | -7% |
| Clases muertas | 0 | -100% |
| Archivos CSS totales | ~5 | -87% |
| Archivos CSS huérfanos | 0 | -100% |
| Variables CSS totales | ~178 | -40% |
| Variables CSS sin usar | 0 | -100% |
| Archivos JS obsoletos | 0 | -100% |
| Inconsistencias estructurales | 0 | -100% |
| Bugs críticos | 0 | -100% |
| Código duplicado | 0 | -100% |
| Tamaño código muerto | 0 | -100% |

### Beneficios Esperados

- **Mantenibilidad:** +25%
- **Tiempo de búsqueda de código:** -20%
- **Bugs por imports rotos:** -30%
- **Confusión en desarrollo:** -20%
- **Performance de carga:** Mejora marginal (~54KB CSS menos)
- **Onboarding de nuevos devs:** Más fácil

---

## ═══════════════════════════════════════════
## NOTAS FINALES IMPORTANTES
## ═══════════════════════════════════════════

### Antes de Empezar

1. **Crear rama de trabajo:** `git checkout -b limpieza-codigo`
2. **Informar al equipo:** Si hay otros desarrolladores, coordinar
3. **Hacer backup completo:** Del proyecto completo
4. **Leer este plan completo:** Antes de ejecutar cualquier comando
5. **Tener tiempo disponible:** No empezar si no puedes dedicar tiempo

### Durante la Ejecución

1. **Una fase a la vez:** NO saltar fases ni hacer múltiples fases juntas
2. **Commit por fase:** Hacer commit después de cada fase exitosa
3. **Probar después de cada fase:** NO acumular cambios sin probar
4. **Si algo falla:** DETENER, hacer rollback, investigar
5. **Documentar decisiones:** En este archivo o en commits

### Después de Completar

1. **Prueba completa:** End-to-end de toda la aplicación
2. **Revisión de código:** Si hay equipo, pedir revisión
3. **Merge a main:** Solo después de pruebas exitosas
4. **Actualizar documentación:** README, CHANGELOG, etc.
5. **Comunicar cambios:** Al equipo

### Si Algo Sale Mal

```bash
# Rollback inmediato
git reset --hard HEAD~1
composer dump-autoload

# Verificar estado
git log -1
git status

# Revisar qué salió mal antes de reintentar
```

### Decisiones Pendientes a Tomar

Antes de ejecutar, decidir:

- [ ] **Fase 1.3:** ¿Eliminar comandos CLI?
- [ ] **Fase 3.1:** ¿Eliminar carpeta services/ si queda vacía?
- [ ] **Fase 4.2:** ¿Opción A (theme-variables) o B (base.css)?
- [ ] **Fase 5.2:** ¿Conservar o eliminar 10 componentes no usados?
- [ ] **Fase 5.3:** ¿Conservar o eliminar componentes showcase?

---

## ═══════════════════════════════════════════
## ARCHIVOS DE REFERENCIA DISPONIBLES
## ═══════════════════════════════════════════

**En la carpeta `plan-clean-code/`:**

1. `UNUSED_CSS_VARIABLES.txt` - Lista de 135 variables sin usar
2. `ORPHANED_CSS_FILES.txt` - Lista de 35 archivos huérfanos
3. `CSS_ANALYSIS_REPORT.md` - Análisis completo de CSS
4. `CSS_CLEANUP_CHECKLIST.md` - Checklist detallado de CSS
5. `INFORME_DETALLADO_POR_FASES.md` - Informe detallado de otro análisis
6. `PLAN-LIMPIEZA-CODIGO.md` - Plan de otro análisis
7. `PLAN_LIMPIEZA_PROYECTO.md` - Plan de otro análisis
8. Archivos numerados 1, 2, 3, 4 con análisis adicionales

**En `/tmp/` (si aún existen):**

- `LEGO_ANALYSIS.md`
- `IMPORT_PATTERNS.md`
- `ACTION_ITEMS.md`
- `lego_component_analysis.md`
- Y más...

---

## ═══════════════════════════════════════════
## ESTADO DE EJECUCIÓN
## ═══════════════════════════════════════════

Marcar las fases completadas:

- [ ] **FASE 1:** Limpieza segura - Archivos muertos
- [ ] **FASE 2:** Corrección de typos
- [ ] **FASE 3:** Consolidación de duplicados
- [ ] **FASE 4:** Limpieza masiva de CSS
- [ ] **FASE 5:** Limpieza de componentes LEGO
- [ ] **FASE 6:** Refactoring de estructura
- [ ] **FASE 7:** Optimizaciones finales

**Última actualización:** 2 de Noviembre, 2025
**Próxima revisión:** Después de completar cada fase
**Generado por:** Claude Code (consolidación de múltiples análisis)

---

