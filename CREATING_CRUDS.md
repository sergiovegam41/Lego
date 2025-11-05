# 🚀 Creación Rápida de CRUDs en LEGO

> **IMPORTANTE:** Lee esto ANTES de crear cualquier CRUD nuevo para evitar problemas comunes.

## ⚡ Inicio Rápido

```bash
# Ver la guía completa
cat docs/CRUD_CREATION_GUIDE.md

# Generador automático (en desarrollo)
php scripts/generate-crud.php ModelName field1:type field2:type
```

## 🎯 Problemas Comunes y Soluciones

### 🔴 API 404 en POST/PUT/DELETE

**Síntoma:** `POST /api/testimonials` retorna 404

**Causa:** Endpoint con prefijo `/api` en el atributo

```php
// ❌ INCORRECTO
#[ApiCrudResource(endpoint: '/api/testimonials')]

// ✅ CORRECTO
#[ApiCrudResource(endpoint: 'testimonials')]
```

**Por qué:** `Core/Router.php` ya quita el prefijo `/api/` antes de que Flight procese las rutas.

---

### 🔴 Formulario redirige con query params en URL

**Síntoma:** Al enviar form, URL muestra `?field1=value&field2=value`

**Causa:** JavaScript no intercepta el submit

```javascript
// ❌ INCORRECTO - Falta preventDefault
form.addEventListener('submit', async (e) => {
    // Código sin e.preventDefault()
});

// ✅ CORRECTO
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    e.stopPropagation();
    // Resto del código
});
```

---

### 🔴 JavaScript no se ejecuta / Módulos comparten estado

**Síntoma:**
- Logs no aparecen en consola, eventos no funcionan
- Al abrir Create después de Edit, aparecen datos del Edit
- Formularios se cruzan entre sí
- Event listeners duplicados

**Causa:** No aislar el código por módulo activo

```javascript
// ❌ INCORRECTO - Busca en TODO el documento (sin aislamiento)
(function init() {
    setTimeout(() => {
        const form = document.getElementById('my-form');
        const input = document.getElementById('my-input');
        // ¡Problema! Puede encontrar elementos de OTRO módulo
    }, 100);
})();

// ✅ CORRECTO - Patrón de Flowers con aislamiento por módulo
function initializeForm() {
    console.log('[ItemCreate] Initializing form...');

    // 1. Obtener el módulo ACTIVO
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[ItemCreate] No active module');
        return;
    }

    // 2. Obtener el CONTAINER del módulo activo
    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[ItemCreate] Module container not found');
        return;
    }

    // 3. Buscar elementos SOLO dentro del container
    const form = activeModuleContainer.querySelector('#item-create-form');
    const input = activeModuleContainer.querySelector('#item-name');

    if (!form) {
        console.warn('[ItemCreate] Form not found');
        return;
    }

    // Ahora sí, agregar event listeners
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        // ...
    });
}

// 4. Sistema de reintentos hasta que el módulo esté listo
let attempts = 0;
const maxAttempts = 50;

function tryInitialize() {
    const activeModuleId = window.moduleStore?.getActiveModule();

    if (!activeModuleId) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInitialize, 50);
        }
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInitialize, 50);
        }
        return;
    }

    const form = activeModuleContainer.querySelector('#item-create-form');

    if (form) {
        console.log('[ItemCreate] Form found, initializing...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();
```

**¿Por qué este patrón?**
- Cada módulo (Create, Edit) está en su propio `<div id="module-X">`
- Si usas `document.getElementById()`, busca en TODO el DOM
- Resultado: Puede encontrar el formulario de OTRO módulo abierto
- Solución: Usar `activeModuleContainer.querySelector()` para buscar SOLO en el módulo activo

**Referencias:** Ver `flower-create.js` y `flower-edit.js` como ejemplos correctos

---

### 🔴 Edit Component retorna 404 / ID not found

**Síntoma:** Al abrir edición, JavaScript dice "ID not found" o ruta retorna 404

**Causa:** El componente Edit no captura el parámetro de ruta correctamente

```php
// ❌ INCORRECTO - Solo lee $_GET en component()
#[ApiComponent('/items/edit', methods: ['GET'])]
class ItemEditComponent extends CoreComponent
{
    protected function component(): string
    {
        $id = $_GET['id'] ?? null; // Puede fallar
    }
}

// ✅ CORRECTO - Constructor captura el parámetro (patrón de Flowers)
#[ApiComponent('/items/edit', methods: ['GET'])]  // Query param, no @id
class ItemEditComponent extends CoreComponent
{
    public function __construct(array $params = [])
    {
        $id = $params['id'] ?? $_GET['id'] ?? $_REQUEST['id'] ?? null;
        if ($id !== null) {
            $this->itemId = is_numeric($id) ? (int)$id : null;
        }
    }

    private ?int $itemId = null;

    protected function component(): string
    {
        $itemId = $this->itemId
            ?? (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null);

        if (!$itemId) {
            return '<div class="error">ID no especificado</div>';
        }
        // ... resto del código
    }
}
```

**JavaScript:** Llamar con query parameter `?id=X`:
```javascript
// ✅ CORRECTO - Query parameter como en Flowers
url: `${HOST_NAME}/component/items/edit?id=${itemData.id}`
```

**PHP:** Incluir data attribute en el HTML:
```php
return <<<HTML
<div class="item-form" data-item-id="{$itemId}">
    <!-- form aquí -->
</div>
HTML;
```

---

### 🔴 Confirmaciones usando confirm() básico

**Síntoma:** Eliminaciones usan `confirm()` nativo en lugar de SweetAlert2

**Causa:** No usar el ConfirmationService del framework o usar método incorrecto

```javascript
// ❌ INCORRECTO - confirm() nativo
async function handleDelete(data) {
    const confirmed = confirm('¿Eliminar?');
    if (!confirmed) return;
    // delete logic
}

// ❌ INCORRECTO - confirm() no existe en ConfirmationService
async function handleDelete(data) {
    const confirmed = await window.ConfirmationService.confirm({
        title: '¿Eliminar?',
        message: 'Mensaje'
    });
}

// ✅ CORRECTO - Usar preset delete()
async function handleDelete(data) {
    if (!window.ConfirmationService) {
        console.error('ConfirmationService not available');
        return;
    }

    const confirmed = await window.ConfirmationService.delete(`el item "${data.name}"`, {
        title: '¿Eliminar item?',
        description: 'Esta acción no se puede deshacer.'
    });

    if (!confirmed) return;

    // delete logic

    // Usar AlertService para éxito/error
    if (window.AlertService) {
        window.AlertService.success('Éxito', 'Item eliminado exitosamente');
    }
}

// ✅ ALTERNATIVA - Usar custom() para más control
async function handleDelete(data) {
    const confirmed = await window.ConfirmationService.custom({
        title: '¿Eliminar item?',
        message: `¿Estás seguro de que deseas eliminar <strong>"${data.name}"</strong>?`,
        description: 'Esta acción no se puede deshacer.',
        confirmText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        icon: 'warning',
        variant: 'danger'
    });
}
```

**Presets disponibles en ConfirmationService:**
- `delete(itemName, options)` - Confirmación de eliminación
- `warning(message, options)` - Advertencia genérica
- `danger(message, options)` - Acción peligrosa
- `custom(config)` - Totalmente personalizable

**También reemplaza `alert()` por `AlertService`:**
```javascript
// ❌ alert('Error al eliminar')
// ✅ window.AlertService.error('Error', 'Error al eliminar')

// Para validación en formularios:
if (!field.value) {
    window.AlertService.error('Campos requeridos', 'Por favor completa todos los campos obligatorios');
    return;
}
```

---

### 🔴 Botones invisibles / Colores no cambian con tema

**Síntoma:** Botones transparentes, colores fijos en modo oscuro

**Causa:** Colores hardcodeados en CSS

```css
/* ❌ INCORRECTO */
.button {
    background: #3b82f6;
    color: white;
}

/* ✅ CORRECTO - Variables reactivas */
.button {
    background: var(--accent-primary);
    color: var(--text-on-primary);
}

.button:hover {
    background: var(--accent-hover);
}
```

**Variables disponibles:**
- `--accent-primary`, `--accent-hover`
- `--bg-primary`, `--bg-secondary`, `--bg-tertiary`
- `--text-primary`, `--text-secondary`
- `--border-light`, `--border-medium`

---

### 🔴 Tabla no carga datos (500 error)

**Síntoma:** `SQLSTATE[42P01]: Undefined table`

**Causa:** Migration no ejecutada

```bash
# ✅ SOLUCIÓN
docker exec lego-php php database/run-eloquent-migrations.php
```

---

## 📚 Patrones Esenciales

### Navegación (Window Manager)

```javascript
// ✅ Abrir modal window
window.legoWindowManager.openModuleWithMenu({
    moduleId: 'my-module-create',
    parentMenuId: '4',
    label: 'Crear Item',
    icon: 'add-circle-outline',
    url: `${HOST_NAME}/component/mymodule/create`
});

// ✅ Cerrar window
const activeModule = window.moduleStore?.getActiveModule();
if (activeModule && window.legoWindowManager) {
    window.legoWindowManager.closeModule(activeModule);
}
```

### Refresh Tabla

```javascript
// ✅ Patrón correcto con retry
setTimeout(() => {
    let attempts = 0;
    const maxAttempts = 20;
    const checkAndRefresh = () => {
        const refreshFn = window.legoTable_my_table_refresh;
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

## ✅ Checklist CRUD Completo

Verifica esto ANTES de considerar un CRUD terminado:

- [ ] **Modelo:** `#[ApiGetResource]` y `#[ApiCrudResource]` SIN `/api` prefix
- [ ] **Migration:** Creada y ejecutada con `docker exec lego-php php database/run-eloquent-migrations.php`
- [ ] **CSS:** Usa variables de tema (`var(--accent-primary)`, NO colores hardcodeados)
- [ ] **JavaScript:** Patrón IIFE con `setTimeout`, NO `DOMContentLoaded`
- [ ] **Form Submit:** `e.preventDefault()` y `e.stopPropagation()` presentes
- [ ] **Edit Component:** Usa constructor para capturar `@id`, incluye `data-item-id` en HTML
- [ ] **Delete Function:** Usa `ConfirmationService.confirm()`, NO `confirm()` nativo
- [ ] **Alerts:** Usa `AlertService.success/error()`, NO `alert()` nativo
- [ ] **Navegación:** Usa `window.legoWindowManager`, NO `window.location.href`
- [ ] **Composer:** Autoload actualizado en `composer.json` y ejecutado `composer dump-autoload`
- [ ] **Menú:** Entrada agregada en `MainComponent.php`

---

## 🎓 Ejemplos de Referencia

Revisa estos CRUDs para ver los patrones correctos:

- `components/App/Flowers/` - CRUD completo con imágenes
- `components/App/Categories/` - CRUD simple
- `components/App/Testimonials/` - CRUD con edit component usando constructor
- `components/App/FeaturedProducts/` - CRUD con relaciones, ConfirmationService, y edit pattern correcto

---

## 🔧 Herramientas

```bash
# Ejecutar migrations
docker exec lego-php php database/run-eloquent-migrations.php

# Actualizar composer autoload
docker exec lego-php composer dump-autoload

# Debug de rutas registradas
docker exec lego-php php debug_routes.php

# Test de endpoint
curl -X POST http://localhost:8080/api/testimonials \
  -H "Content-Type: application/json" \
  -d '{"field":"value"}'
```

---

## 📖 Documentación Completa

Ver: [docs/CRUD_CREATION_GUIDE.md](docs/CRUD_CREATION_GUIDE.md)

---

**💡 Recuerda:** Estos son patrones del FRAMEWORK, no solo de una implementación. Si encuentras un problema en un CRUD nuevo, probablemente sea uno de los listados arriba.
