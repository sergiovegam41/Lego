# ConfirmationService

Servicio versátil y abstracto para confirmaciones en el framework LEGO.

## Filosofía

ConfirmationService proporciona una API simple y consistente para mostrar confirmaciones al usuario. Incluye presets para casos comunes (delete, logout, warning) y total personalización para casos específicos.

## Características

✅ **Presets para casos comunes**: delete, logout, warning, danger, info, unsavedChanges
✅ **Totalmente personalizable** para casos específicos
✅ **Integración con AlertService** (respeta dark/light theme automáticamente)
✅ **API simple y consistente**
✅ **Soporte para HTML** en mensajes
✅ **Callbacks opcionales** (onConfirm, onCancel)
✅ **Confirmación con input** para casos críticos

---

## Instalación

El servicio se carga automáticamente en `MainComponent.php`:

```html
<link rel="stylesheet" href="./assets/css/core/confirmation-service.css">
<script src="./assets/js/services/ConfirmationService.js"></script>
```

Asegúrate de que `AlertService.js` esté cargado antes de `ConfirmationService.js`.

---

## Uso Básico

### 1. Confirmación de Eliminación (PRESET)

El preset más común para eliminar elementos:

```javascript
// Ejemplo básico
const confirmed = await ConfirmationService.delete('el producto #123');
if (confirmed) {
    // Proceder con eliminación
    await deleteProduct(123);
}

// Con nombre dinámico del elemento
const itemName = `<strong>${product.name}</strong>`;
const confirmed = await ConfirmationService.delete(itemName);
```

**Salida visual:**
- Título: "¿Eliminar?"
- Mensaje: "¿Estás seguro de eliminar **[itemName]**?"
- Descripción: "Esta acción no se puede deshacer."
- Botón confirmar: "Sí, eliminar" (color rojo)
- Botón cancelar: "Cancelar"

**Implementación real** (ExampleCrud):

```javascript
window.handleDeleteRecord = async function(rowData, tableId) {
    const itemName = `<strong>${rowData.name || 'ID: ' + rowData.id}</strong>`;
    const confirmed = await ConfirmationService.delete(itemName);

    if (!confirmed) return;

    // Proceder con eliminación...
    const response = await fetch('/api/example-crud/delete', {
        method: 'POST',
        body: JSON.stringify({ id: rowData.id })
    });

    // Mostrar resultado
    if (response.ok) {
        await AlertService.success('Registro eliminado correctamente');
        window.legoWindowManager?.reloadActive();
    }
};
```

---

### 2. Confirmación de Logout (PRESET)

Confirmación para cerrar sesión:

```javascript
const confirmed = await ConfirmationService.logout();
if (confirmed) {
    // Proceder con logout
    await logoutUser();
}
```

**Salida visual:**
- Título: "¿Cerrar sesión?"
- Mensaje: "¿Estás seguro de que deseas cerrar sesión?"
- Descripción: "Tendrás que volver a iniciar sesión para acceder."
- Botón confirmar: "Sí, cerrar sesión" (color warning)
- Botón cancelar: "Cancelar"

**Implementación real** (Header):

```javascript
async function handleLogout() {
    const confirmed = await ConfirmationService.logout();

    if (!confirmed) {
        console.log('Logout cancelado por el usuario');
        return;
    }

    // Mostrar loading
    AlertService.loading('Cerrando sesión...');

    // Llamar al endpoint
    const response = await fetch('/api/auth/admin/logout', {
        method: 'POST'
    });

    AlertService.close();

    if (response.ok) {
        await AlertService.success('Sesión cerrada correctamente');
        window.location.href = '/login';
    }
}
```

---

### 3. Confirmación de Advertencia (PRESET)

Para acciones que requieren confirmación:

```javascript
const confirmed = await ConfirmationService.warning(
    '¿Continuar con esta acción?'
);

if (confirmed) {
    // Ejecutar acción
}
```

---

### 4. Confirmación de Acción Peligrosa (PRESET)

Para acciones críticas:

```javascript
const confirmed = await ConfirmationService.danger(
    'Esto reiniciará el sistema completo'
);

if (confirmed) {
    // Ejecutar acción peligrosa
}
```

**Salida visual:**
- Título: "¡Atención!"
- Descripción: "Esta acción puede tener consecuencias importantes."
- Botón confirmar: color rojo

---

### 5. Confirmación Informativa (PRESET)

Para confirmaciones de información:

```javascript
const confirmed = await ConfirmationService.info(
    'Este proceso puede tomar varios minutos'
);
```

---

### 6. Cambios Sin Guardar (PRESET)

Para advertir sobre cambios sin guardar:

```javascript
const confirmed = await ConfirmationService.unsavedChanges();

if (confirmed) {
    // Usuario decide salir sin guardar
    closeModule();
} else {
    // Usuario quiere continuar editando
    return;
}
```

**Salida visual:**
- Título: "¿Salir sin guardar?"
- Mensaje: "Tienes cambios sin guardar."
- Descripción: "Si sales ahora, perderás los cambios realizados."
- Botón confirmar: "Salir sin guardar"
- Botón cancelar: "Continuar editando"

---

## Uso Avanzado

### Confirmación Personalizada

Para casos específicos que no encajan en los presets:

```javascript
const confirmed = await ConfirmationService.custom({
    title: '¿Publicar artículo?',
    message: 'El artículo será visible para todos los usuarios.',
    description: 'Podrás despublicarlo más tarde si lo necesitas.',
    confirmText: 'Publicar',
    cancelText: 'Cancelar',
    icon: 'question',
    variant: 'primary', // primary, warning, danger, info
    onConfirm: () => console.log('Artículo publicado'),
    onCancel: () => console.log('Publicación cancelada')
});
```

**Parámetros disponibles:**

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `title` | string | '¿Estás seguro?' | Título del modal |
| `message` | string | '' | Mensaje principal |
| `description` | string | '' | Descripción adicional (en cursiva) |
| `confirmText` | string | 'Confirmar' | Texto del botón confirmar |
| `cancelText` | string | 'Cancelar' | Texto del botón cancelar |
| `icon` | string | 'question' | Icono (success, error, warning, info, question) |
| `variant` | string | 'primary' | Color del botón confirmar |
| `html` | string | null | HTML personalizado (sobrescribe message + description) |
| `showCancelButton` | boolean | true | Mostrar botón cancelar |
| `reverseButtons` | boolean | true | Invertir orden de botones |
| `focusCancel` | boolean | true | Enfocar botón cancelar |
| `onConfirm` | function | null | Callback al confirmar |
| `onCancel` | function | null | Callback al cancelar |
| `width` | string | '500px' | Ancho del modal |
| `allowOutsideClick` | boolean | true | Cerrar al hacer clic fuera |
| `allowEscapeKey` | boolean | true | Cerrar con tecla ESC |

---

### Confirmación con Input

Para casos donde se requiere que el usuario escriba algo para confirmar:

```javascript
const { confirmed, value } = await ConfirmationService.withInput({
    title: 'Confirmar eliminación',
    message: 'Escribe "ELIMINAR" para confirmar',
    placeholder: 'ELIMINAR',
    expectedValue: 'ELIMINAR',
    caseSensitive: false
});

if (confirmed) {
    console.log('Usuario confirmó escribiendo:', value);
    // Proceder con acción crítica
}
```

**Ejemplo práctico** (eliminar base de datos):

```javascript
const { confirmed, value } = await ConfirmationService.withInput({
    title: 'Eliminar Base de Datos',
    message: 'Esta acción es IRREVERSIBLE. Escribe el nombre de la base de datos para confirmar:',
    placeholder: 'nombre-database',
    expectedValue: databaseName,
    caseSensitive: true,
    variant: 'danger',
    confirmText: 'Eliminar Permanentemente',
    cancelText: 'Cancelar'
});

if (confirmed && value === databaseName) {
    await deleteDatabase(databaseName);
}
```

---

### HTML Personalizado

Puedes pasar HTML personalizado para casos complejos:

```javascript
const confirmed = await ConfirmationService.custom({
    title: 'Confirmar Compra',
    html: `
        <div style="text-align: center;">
            <p>Estás a punto de comprar:</p>
            <ul style="list-style: none; padding: 0;">
                <li><strong>Producto:</strong> ${product.name}</li>
                <li><strong>Precio:</strong> $${product.price}</li>
                <li><strong>Cantidad:</strong> ${quantity}</li>
            </ul>
            <p><strong>Total: $${product.price * quantity}</strong></p>
        </div>
    `,
    confirmText: 'Confirmar Compra',
    cancelText: 'Cancelar',
    icon: 'info',
    variant: 'primary'
});
```

---

### Callbacks Asíncronos

Los callbacks pueden ser funciones asíncronas:

```javascript
const confirmed = await ConfirmationService.custom({
    title: 'Guardar cambios',
    message: '¿Deseas guardar los cambios realizados?',
    confirmText: 'Guardar',
    onConfirm: async () => {
        await saveChanges();
        await AlertService.success('Cambios guardados correctamente');
    },
    onCancel: async () => {
        await AlertService.info('Cambios descartados');
    }
});
```

---

## Variantes de Botón

Las variantes disponibles para el botón de confirmación:

| Variante | Color | Uso recomendado |
|----------|-------|-----------------|
| `primary` | Azul | Acciones normales |
| `warning` | Naranja | Advertencias |
| `danger` | Rojo | Eliminaciones y acciones críticas |
| `info` | Azul | Información |

---

## Integración con Dark Theme

ConfirmationService hereda automáticamente el tema de AlertService. No requiere configuración adicional.

Los modales se adaptan automáticamente al tema actual (dark/light) usando las variables CSS del framework.

---

## Ejemplos de Implementación

### Ejemplo 1: Confirmación en TableComponent

```javascript
// En la definición de RowAction
new RowActionDto(
    id: "delete",
    label: "Eliminar",
    icon: "trash-outline",
    callback: "handleDeleteRecord",
    variant: "danger",
    confirm: false, // Desactivar confirmación automática (manejamos con ConfirmationService)
    tooltip: "Eliminar registro"
)

// Callback personalizado
window.handleDeleteRecord = async function(rowData, tableId) {
    const confirmed = await ConfirmationService.delete(
        `<strong>${rowData.name}</strong>`
    );

    if (confirmed) {
        await deleteRecord(rowData.id);
    }
};
```

### Ejemplo 2: Confirmación en Formulario

```javascript
async function handleFormSubmit(event) {
    event.preventDefault();

    const confirmed = await ConfirmationService.custom({
        title: '¿Guardar cambios?',
        message: 'Los cambios se aplicarán inmediatamente.',
        confirmText: 'Guardar',
        cancelText: 'Cancelar',
        icon: 'question',
        variant: 'primary'
    });

    if (confirmed) {
        const formData = new FormData(event.target);
        await submitForm(formData);
    }
}
```

### Ejemplo 3: Confirmación con Validación

```javascript
async function handleCriticalAction() {
    // Primera confirmación
    const firstConfirm = await ConfirmationService.danger(
        'Esto eliminará TODOS los registros de la tabla'
    );

    if (!firstConfirm) return;

    // Segunda confirmación con input
    const { confirmed, value } = await ConfirmationService.withInput({
        title: 'Confirmación Final',
        message: 'Escribe "CONFIRMAR" para proceder',
        placeholder: 'CONFIRMAR',
        expectedValue: 'CONFIRMAR',
        caseSensitive: false,
        variant: 'danger'
    });

    if (confirmed) {
        await executeCriticalAction();
    }
}
```

---

## Mejores Prácticas

### ✅ DO

- Usa presets cuando sea posible (`delete`, `logout`, `warning`, etc.)
- Proporciona nombres claros y descriptivos de los elementos a eliminar
- Usa HTML para resaltar información importante (`<strong>`, `<em>`)
- Implementa confirmaciones con input para acciones críticas irreversibles
- Mantén los mensajes concisos y claros
- Usa callbacks para acciones post-confirmación

### ❌ DON'T

- No uses `confirm()` nativo de JavaScript (inconsistente con el diseño)
- No uses `AlertService.confirm()` directamente (usa ConfirmationService en su lugar)
- No hagas confirmaciones innecesarias para acciones triviales
- No uses mensajes técnicos o crípticos
- No omitas confirmaciones en eliminaciones o acciones destructivas

---

## Fallback

ConfirmationService incluye fallback a `confirm()` nativo si no está disponible:

```javascript
const confirmed = window.ConfirmationService
    ? await window.ConfirmationService.delete(itemName)
    : confirm('¿Estás seguro de eliminar este elemento?');
```

---

## Troubleshooting

### "ConfirmationService is not defined"

**Causa**: El script no se cargó o AlertService no está disponible.

**Solución**: Verifica que ambos scripts estén cargados en el orden correcto:

```html
<script src="./assets/js/services/AlertService.js"></script>
<script src="./assets/js/services/ConfirmationService.js"></script>
```

### El modal no respeta el dark theme

**Causa**: AlertService no está correctamente suscrito al ThemeManager.

**Solución**: Asegúrate de que AlertService esté inicializado:

```javascript
await AlertService.init();
```

### Los botones no tienen el color correcto

**Causa**: La variante no está especificada o es inválida.

**Solución**: Usa una variante válida (`primary`, `warning`, `danger`, `info`):

```javascript
ConfirmationService.custom({
    variant: 'danger' // ✅
});
```

---

## API Reference

### Presets

```typescript
ConfirmationService.delete(itemName: string, options?: Object): Promise<boolean>
ConfirmationService.logout(options?: Object): Promise<boolean>
ConfirmationService.warning(message: string, options?: Object): Promise<boolean>
ConfirmationService.danger(message: string, options?: Object): Promise<boolean>
ConfirmationService.info(message: string, options?: Object): Promise<boolean>
ConfirmationService.unsavedChanges(options?: Object): Promise<boolean>
```

### Custom

```typescript
ConfirmationService.custom(config: {
    title?: string,
    message?: string,
    description?: string,
    confirmText?: string,
    cancelText?: string,
    icon?: 'success' | 'error' | 'warning' | 'info' | 'question',
    variant?: 'primary' | 'warning' | 'danger' | 'info',
    html?: string,
    showCancelButton?: boolean,
    reverseButtons?: boolean,
    focusCancel?: boolean,
    onConfirm?: () => void | Promise<void>,
    onCancel?: () => void | Promise<void>,
    width?: string,
    allowOutsideClick?: boolean,
    allowEscapeKey?: boolean
}): Promise<boolean>
```

### With Input

```typescript
ConfirmationService.withInput(config: {
    title?: string,
    message?: string,
    placeholder?: string,
    expectedValue?: string,
    caseSensitive?: boolean,
    inputType?: string,
    confirmText?: string,
    cancelText?: string,
    variant?: string
}): Promise<{confirmed: boolean, value: string | null}>
```

---

## Changelog

### v1.0.0 (2025-01-04)
- ✨ Versión inicial
- ✅ Presets: delete, logout, warning, danger, info, unsavedChanges
- ✅ Custom confirmations con full customización
- ✅ Confirmación con input
- ✅ Dark theme support
- ✅ Integración con AlertService
- ✅ Callbacks asíncronos

---

## Licencia

Parte del framework LEGO. Uso interno.
