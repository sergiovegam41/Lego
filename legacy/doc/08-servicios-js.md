# Servicios JS

## AlertService

Notificaciones y modales.

```javascript
// Éxito
window.AlertService.success('Guardado correctamente');

// Error
window.AlertService.error('No se pudo guardar');

// Toast (no bloqueante)
window.AlertService.toast('Operación completada', 'success');
window.AlertService.toast('Hubo un problema', 'error');

// Loading
window.AlertService.loading('Procesando...');
window.AlertService.close(); // Cierra el loading

// Con promesa
const result = await window.AlertService.success('¿Continuar?');
```

## ConfirmationService

Diálogos de confirmación.

```javascript
// Confirmación simple
const confirmed = await window.ConfirmationService.confirm({
    title: '¿Eliminar?',
    message: 'Esta acción no se puede deshacer',
    confirmText: 'Sí, eliminar',
    cancelText: 'Cancelar',
    type: 'danger'
});

if (confirmed) {
    // Proceder
}

// Tipos: 'danger', 'warning', 'info'
```

## ThemeManager

Control de tema claro/oscuro.

```javascript
// Obtener tema actual
const theme = window.ThemeManager.getTheme(); // 'light' | 'dark'

// Cambiar tema
window.ThemeManager.setTheme('dark');

// Toggle
window.ThemeManager.toggleTheme();

// Escuchar cambios
window.ThemeManager.subscribe((theme) => {
    console.log('Nuevo tema:', theme);
});
```

## LegoScreenManager

Gestión de screens.

```javascript
// Obtener screen por ID
const screen = window.legoScreenManager.get('productos-list');

// Screen activo
const active = window.legoScreenManager.getActive();

// Listar todos
const screens = window.legoScreenManager.list();

// Métodos del screen
screen.setLoading(true);
screen.setError('Mensaje de error');
screen.clearError();
screen.reload();
screen.close();
```

## Eventos Globales

```javascript
// Screen listo
window.addEventListener('lego:screen:ready', (e) => {
    console.log('Screen listo:', e.detail.id);
});

// Tabla lista
window.addEventListener('lego:table:ready', (e) => {
    const { tableId, api } = e.detail;
});

// Filtros cambiados
window.addEventListener('lego:table:filterChanged', (e) => {
    const { tableId, filterModel } = e.detail;
});

// Módulo activado
window.addEventListener('lego:module:activated', (e) => {
    const { moduleId } = e.detail;
});
```

