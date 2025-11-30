# Crear Botón

## Botón Simple (HTML)

```php
protected function component(): string
{
    return <<<HTML
    <button 
        type="button" 
        class="mi-boton"
        onclick="miFuncion()"
    >
        Hacer algo
    </button>
    HTML;
}
```

```css
.mi-boton {
    padding: var(--space-sm) var(--space-md);
    background: var(--primary);
    color: white;
    border: none;
    border-radius: var(--radius-button);
    cursor: pointer;
}

.mi-boton:hover {
    background: var(--primary-hover);
}
```

```javascript
function miFuncion() {
    console.log('Clic!');
}
window.miFuncion = miFuncion;
```

## Usando ButtonComponent

```php
use Components\Shared\Buttons\ButtonComponent\ButtonComponent;

$boton = new ButtonComponent(
    label: "Guardar",
    variant: "primary",      // primary, secondary, danger, ghost
    size: "medium",          // small, medium, large
    onClick: "guardar()",
    type: "button",
    disabled: false
);

echo $boton->render();
```

## Usando IconButtonComponent

```php
use Components\Shared\Buttons\IconButtonComponent\IconButtonComponent;

$boton = new IconButtonComponent(
    icon: "add-outline",     // Nombre ionicon
    size: "medium",
    variant: "primary",
    onClick: "agregar()",
    title: "Agregar item"
);

echo $boton->render();
```

## Botón con Confirmación

```javascript
async function eliminar() {
    const confirmed = await ConfirmationService.confirm({
        title: '¿Eliminar?',
        message: 'Esta acción no se puede deshacer',
        type: 'danger',
        confirmText: 'Sí, eliminar'
    });
    
    if (confirmed) {
        // Proceder con eliminación
        await fetch('/api/items/delete', { method: 'POST', ... });
        AlertService.toast('Eliminado', 'success');
    }
}
```

## Botón con Loading

```javascript
async function guardar() {
    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    
    try {
        await fetch('/api/items/create', { ... });
        AlertService.toast('Guardado', 'success');
    } catch (e) {
        AlertService.error('Error al guardar');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar';
    }
}
```

## Botón que Abre Módulo

```javascript
function abrirCrear() {
    window.legoWindowManager.openModuleWithMenu({
        moduleId: 'items-create',
        parentMenuId: 'items',
        label: 'Nuevo Item',
        url: '/component/items/create',
        icon: 'add-circle-outline'
    });
}
```

