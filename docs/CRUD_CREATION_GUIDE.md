# Guía para Crear CRUDs en LEGO Framework

Esta guía documenta los patrones correctos y las mejores prácticas para crear CRUDs en el framework LEGO. Sigue estos pasos para garantizar consistencia y evitar problemas comunes.

## 📋 Índice

1. [Estructura de Archivos](#estructura-de-archivos)
2. [Paso 1: Modelo](#paso-1-modelo)
3. [Paso 2: Migration](#paso-2-migration)
4. [Paso 3: Component Principal](#paso-3-component-principal)
5. [Paso 4: Component Create](#paso-4-component-create)
6. [Paso 5: Component Edit](#paso-5-component-edit)
7. [Paso 6: CSS](#paso-6-css)
8. [Paso 7: JavaScript](#paso-7-javascript)
9. [Paso 8: Menú](#paso-8-menú)
10. [Paso 9: Composer Autoload](#paso-9-composer-autoload)
11. [Paso 10: Ejecutar Migration](#paso-10-ejecutar-migration)
12. [Patrones Importantes](#patrones-importantes)
13. [Problemas Comunes](#problemas-comunes)

---

## Estructura de Archivos

Para un CRUD de `Testimonial`, la estructura debe ser:

```
App/Models/
  └── Testimonial.php

database/migrations/
  └── 2025_01_03_000004_create_testimonials_table.php

components/App/Testimonials/
  ├── TestimonialsComponent.php
  ├── testimonials.css
  ├── testimonials.js
  └── childs/
      ├── TestimonialCreate/
      │   ├── TestimonialCreateComponent.php
      │   ├── testimonial-create.js
      │   └── testimonial-form.css
      └── TestimonialEdit/
          ├── TestimonialEditComponent.php
          ├── testimonial-edit.js
          └── testimonial-form.css (compartido con Create)
```

---

## Paso 1: Modelo

**Ubicación:** `App/Models/YourModel.php`

### ✅ Patrón Correcto

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;
use Core\Attributes\ApiCrudResource;

#[ApiGetResource(
    endpoint: 'testimonials',        // ❌ NO incluir /api/ prefix
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'author', 'created_at'],
    filterable: ['id', 'author', 'is_active'],
    searchable: ['author', 'message']
)]
#[ApiCrudResource(
    endpoint: 'testimonials'          // ❌ NO incluir /api/ prefix
)]
class Testimonial extends Model
{
    protected $table = 'testimonials';

    protected $fillable = [
        'author',
        'message',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Scopes útiles
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
```

### ⚠️ Errores Comunes

```php
// ❌ INCORRECTO - NO incluir /api/ en endpoint
#[ApiCrudResource(endpoint: '/api/testimonials')]

// ✅ CORRECTO - Sin prefijo /api
#[ApiCrudResource(endpoint: 'testimonials')]
```

**Por qué:** `Core/Router.php` strip el prefijo `/api/` antes de que Flight procese las rutas. Los atributos `ApiGetResource` y `ApiCrudResource` generan rutas SIN el prefijo `/api`.

---

## Paso 2: Migration

**Ubicación:** `database/migrations/YYYY_MM_DD_HHMMSS_create_tablename_table.php`

### ✅ Patrón Correcto

```php
<?php

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('testimonials', function ($table) {
            $table->id();
            $table->string('author', 255);
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Índices para mejorar rendimiento
            $table->index('is_active');
            $table->index('author');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('testimonials');
    }
};
```

### 📝 Convenciones de Nombres

- Tabla: plural, snake_case (`testimonials`)
- Timestamp format: `YYYY_MM_DD_HHMMSS`
- Nombre archivo: `{timestamp}_create_{table}_table.php`

---

## Paso 3: Component Principal

**Ubicación:** `components/App/Testimonials/TestimonialsComponent.php`

### ✅ Patrón Correcto

```php
<?php
namespace Components\App\Testimonials;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
// ... otros imports

#[ApiComponent('/testimonials', methods: ['GET'])]
class TestimonialsComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./testimonials.css"];
    protected $JS_PATHS = ["./testimonials.js"];

    protected function component(): string
    {
        $columns = new ColumnCollection(
            new ColumnDto(
                field: "id",
                headerName: "ID",
                width: DimensionValue::px(80),
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            // ... más columnas
        );

        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditTestimonial",
                variant: "primary",
                tooltip: "Editar testimonio"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteTestimonial",
                variant: "danger",
                confirm: false,
                tooltip: "Eliminar testimonio"
            )
        );

        $table = new TableComponent(
            id: "testimonials-table",
            model: Testimonial::class,
            columns: $columns,
            rowActions: $actions,
            height: "600px",
            pagination: true,
            rowSelection: "multiple",
            noRowsMessage: "No hay testimonios registrados"
        );

        return <<<HTML
        <div class="testimonials">
            <div class="testimonials__header">
                <h1 class="testimonials__title">Testimonios de Clientes</h1>
                <button
                    class="testimonials__create-btn"
                    type="button"
                    onclick="openCreateTestimonialModule()"
                >
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Crear Testimonio</span>
                </button>
            </div>
            <div class="testimonials__table">
                {$table->render()}
            </div>
        </div>
        HTML;
    }
}
```

---

## Paso 6: CSS

### ✅ Variables Reactivas al Tema

**CRÍTICO:** Usa siempre las variables CSS del tema para garantizar compatibilidad con modo claro/oscuro.

```css
/* ✅ CORRECTO - Variables reactivas */
.testimonials__create-btn {
    background: var(--accent-primary);     /* ✅ */
    color: var(--text-on-primary);         /* ✅ */
}

.testimonials__create-btn:hover {
    background: var(--accent-hover);       /* ✅ */
}

.testimonials {
    background: var(--bg-primary);         /* ✅ */
    color: var(--text-primary);            /* ✅ */
}

/* ❌ INCORRECTO - Colores hardcodeados */
.testimonials__create-btn {
    background: #3b82f6;                   /* ❌ */
    color: white;                          /* ❌ */
}
```

### 📚 Variables de Tema Disponibles

```css
/* Colores de acento */
--accent-primary
--accent-hover
--accent-active

/* Backgrounds */
--bg-primary
--bg-secondary
--bg-tertiary
--bg-hover

/* Textos */
--text-primary
--text-secondary
--text-on-primary

/* Bordes */
--border-light
--border-medium
--border-dark

/* Estados */
--success
--error
--warning
--info
```

---

## Paso 7: JavaScript

### ✅ Patrón para Módulos Dinámicos

**CRÍTICO:** Los módulos LEGO se cargan dinámicamente. NO uses `DOMContentLoaded`.

```javascript
/**
 * Testimonial Create Component JavaScript
 */

console.log('[TestimonialCreate] Script loaded');

const HOST_NAME = window.HOST_NAME || '';

// ✅ CORRECTO - IIFE con setTimeout
(function initTestimonialCreateForm() {
    console.log('[TestimonialCreate] Initializing...');

    setTimeout(() => {
        const form = document.getElementById('testimonial-create-form');
        const submitBtn = document.getElementById('testimonial-form-submit-btn');

        if (!form) {
            console.error('[TestimonialCreate] Form not found');
            return;
        }

        // Form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();           // ✅ CRÍTICO
            e.stopPropagation();          // ✅ CRÍTICO

            const data = {
                author: document.getElementById('testimonial-author').value.trim(),
                message: document.getElementById('testimonial-message').value.trim()
            };

            try {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creando...';

                if (window.legoLoading) {
                    window.legoLoading.show();
                }

                const response = await fetch(`${HOST_NAME}/api/testimonials`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Error al crear');
                }

                // Success
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Creado exitosamente');
                }

                // ✅ Cerrar ventana con window manager
                const activeModule = window.moduleStore?.getActiveModule();
                if (activeModule && window.legoWindowManager) {
                    window.legoWindowManager.closeModule(activeModule);
                }

                // ✅ Refresh tabla
                setTimeout(() => {
                    const refreshFn = window.legoTable_testimonials_table_refresh;
                    if (refreshFn && typeof refreshFn === 'function') {
                        refreshFn();
                    }
                }, 200);

            } catch (error) {
                console.error('[TestimonialCreate] Error:', error);

                if (window.AlertService) {
                    window.AlertService.error('Error', error.message);
                }

                submitBtn.disabled = false;
                submitBtn.textContent = 'Crear Testimonio';
            } finally {
                if (window.legoLoading) {
                    window.legoLoading.hide();
                }
            }
        });

    }, 100); // ✅ Delay para asegurar que DOM está listo

})();
```

### ⚠️ Errores Comunes en JavaScript

```javascript
// ❌ INCORRECTO - DOMContentLoaded no funciona en módulos dinámicos
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form');
    // Este código NUNCA se ejecutará
});

// ❌ INCORRECTO - Sin preventDefault, hace submit nativo
form.addEventListener('submit', async (e) => {
    // Falta e.preventDefault()
    // Resultado: redirect con query params
});

// ❌ INCORRECTO - Redirect manual
window.location.href = '/testimonials';  // NO hacer esto

// ✅ CORRECTO - Usar window manager
window.legoWindowManager.closeModule(activeModule);
```

---

## Paso 8: Menú

**Ubicación:** `components/Core/Home/Components/MainComponent/MainComponent.php`

```php
new MenuItemDto(
    id: "4",
    name: "Testimonios",
    url: null,
    iconName: "chatbubble-ellipses-outline",
    childs: [
        new MenuItemDto(
            id: "4-1",
            name: "Ver Testimonios",
            url: $HOST_NAME . '/component/testimonials',
            iconName: "list-outline"
        ),
        new MenuItemDto(
            id: "4-2",
            name: "Crear Testimonio",
            url: $HOST_NAME . '/component/testimonials/create',
            iconName: "add-circle-outline"
        )
    ]
)
```

---

## Paso 9: Composer Autoload

**Ubicación:** `composer.json`

```json
{
  "autoload": {
    "psr-4": {
      "Components\\App\\Testimonials\\Childs\\": "components/App/Testimonials/childs/"
    }
  }
}
```

Luego ejecutar:

```bash
docker exec lego-php composer dump-autoload
```

---

## Paso 10: Ejecutar Migration

```bash
docker exec lego-php php database/run-eloquent-migrations.php
```

---

## Patrones Importantes

### 🔐 Window Manager (Navegación)

```javascript
// ✅ Abrir ventana modal
window.legoWindowManager.openModuleWithMenu({
    moduleId: 'testimonials-create',
    parentMenuId: '4',
    label: 'Crear Testimonio',
    icon: 'add-circle-outline',
    url: `${HOST_NAME}/component/testimonials/create`
});

// ✅ Cerrar ventana
const activeModule = window.moduleStore?.getActiveModule();
if (activeModule && window.legoWindowManager) {
    window.legoWindowManager.closeModule(activeModule);
}
```

### 🔄 Refresh Tabla

```javascript
// ✅ Patrón con retry
setTimeout(() => {
    let attempts = 0;
    const maxAttempts = 20;
    const checkAndRefresh = () => {
        const refreshFn = window.legoTable_testimonials_table_refresh;
        if (refreshFn && typeof refreshFn === 'function') {
            refreshFn();
        } else if (attempts < maxAttempts) {
            attempts++;
            setTimeout(checkAndRefresh, 100);
        }
    };
    checkAndRefresh();
}, 200);
```

---

## Problemas Comunes

### ❌ Problema: API 404 en POST/PUT/DELETE

**Causa:** `ApiCrudResource` con endpoint incluyendo `/api` prefix

**Solución:**
```php
// ❌ INCORRECTO
#[ApiCrudResource(endpoint: '/api/testimonials')]

// ✅ CORRECTO
#[ApiCrudResource(endpoint: 'testimonials')]
```

### ❌ Problema: Formulario redirige con query params

**Causa:** Falta `e.preventDefault()` y `e.stopPropagation()`

**Solución:**
```javascript
form.addEventListener('submit', async (e) => {
    e.preventDefault();        // ✅ CRÍTICO
    e.stopPropagation();       // ✅ CRÍTICO
    // ... resto del código
});
```

### ❌ Problema: JavaScript no se ejecuta

**Causa:** Usando `DOMContentLoaded` en módulo dinámico

**Solución:**
```javascript
// ❌ INCORRECTO
document.addEventListener('DOMContentLoaded', () => {});

// ✅ CORRECTO
(function init() {
    setTimeout(() => {
        // Código aquí
    }, 100);
})();
```

### ❌ Problema: Botones invisibles / colores no cambian con tema

**Causa:** Usando colores hardcodeados en lugar de variables CSS

**Solución:**
```css
/* ❌ INCORRECTO */
background: #3b82f6;

/* ✅ CORRECTO */
background: var(--accent-primary);
```

### ❌ Problema: Tabla no existe en BD

**Causa:** Migration no ejecutada

**Solución:**
```bash
docker exec lego-php php database/run-eloquent-migrations.php
```

---

## Checklist Final

Antes de considerar un CRUD completo, verifica:

- [ ] Modelo tiene `#[ApiGetResource]` y `#[ApiCrudResource]` SIN prefijo `/api`
- [ ] Migration creada y ejecutada
- [ ] Component principal con tabla y botón crear
- [ ] Components Create y Edit en `childs/`
- [ ] CSS usa variables de tema (`var(--accent-primary)`, etc.)
- [ ] JavaScript usa patrón IIFE con `setTimeout`, NO `DOMContentLoaded`
- [ ] JavaScript usa `e.preventDefault()` y `e.stopPropagation()`
- [ ] Navegación usa `window.legoWindowManager`, NO redirects
- [ ] Entrada en menú agregada
- [ ] Composer autoload actualizado y ejecutado
- [ ] Tabla refresha correctamente después de create/edit/delete

---

## Recursos

- Ejemplos de referencia: `Flowers`, `Categories`, `Testimonials`
- Core Router: `Core/Router.php` - Explica el strip de `/api` prefix
- Window Manager: `assets/js/core/modules/windows-manager/windows-manager.js`
- Generador (en desarrollo): `php scripts/generate-crud.php`

---

**Última actualización:** 2025-01-05
**Versión:** 1.0
