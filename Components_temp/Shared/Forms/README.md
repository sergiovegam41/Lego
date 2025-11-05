# Sistema de Componentes de Formulario Lego

Sistema completo de componentes de formulario siguiendo la filosofía Lego: declarativos, type-safe y reutilizables.

## Componentes Disponibles

### 1. InputTextComponent
Campo de texto con validación y características avanzadas.

**Características:**
- Validación en tiempo real
- Contador de caracteres opcional
- Soporte para iconos
- Estados: normal, error, disabled
- Tipos: text, email, password, tel, url, etc.

**Ejemplo de uso:**
```php
use Components\Shared\Forms\InputTextComponent\InputTextComponent;

$input = new InputTextComponent(
    id: "username",
    label: "Nombre de usuario",
    placeholder: "Ingresa tu nombre",
    maxLength: 50,
    required: true,
    showCounter: true,
    icon: "person-outline"
);

echo $input->render();
```

**API JavaScript:**
```javascript
// Obtener valor
const value = window.LegoInputText.getValue("username");

// Establecer valor
window.LegoInputText.setValue("username", "nuevo valor");

// Validar
const isValid = window.LegoInputText.validate("username");

// Mostrar error
window.LegoInputText.setError("username", "Usuario ya existe");

// Limpiar error
window.LegoInputText.clearError("username");
```

---

### 2. SelectComponent
Selector dropdown con búsqueda y opciones dinámicas.

**Características:**
- Búsqueda en tiempo real
- Opciones agrupadas
- Selección múltiple
- Navegación con teclado
- Custom dropdown personalizado

**Ejemplo de uso:**
```php
use Components\Shared\Forms\SelectComponent\SelectComponent;

$select = new SelectComponent(
    id: "country",
    label: "País",
    searchable: true,
    options: [
        ["value" => "mx", "label" => "México"],
        ["value" => "us", "label" => "Estados Unidos"],
        // Opciones agrupadas
        [
            "label" => "Europa",
            "options" => [
                ["value" => "es", "label" => "España"],
                ["value" => "fr", "label" => "Francia"]
            ]
        ]
    ]
);

echo $select->render();
```

**API JavaScript:**
```javascript
// Obtener valor
const value = window.LegoSelect.getValue("country");

// Establecer valor
window.LegoSelect.setValue("country", "mx");

// Abrir/cerrar dropdown
window.LegoSelect.open("country");
window.LegoSelect.close("country");
```

---

### 3. TextAreaComponent
Campo de texto multilínea con auto-resize.

**Características:**
- Auto-resize opcional
- Contador de caracteres
- Soporte para Tab (indentación)
- Validación en tiempo real

**Ejemplo de uso:**
```php
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;

$textarea = new TextAreaComponent(
    id: "description",
    label: "Descripción",
    placeholder: "Escribe una descripción...",
    maxLength: 500,
    showCounter: true,
    autoResize: true,
    rows: 4
);

echo $textarea->render();
```

**API JavaScript:**
```javascript
const value = window.LegoTextArea.getValue("description");
window.LegoTextArea.setValue("description", "Nuevo texto");
window.LegoTextArea.validate("description");
```

---

### 4. CheckboxComponent
Checkbox con estados y descripción.

**Características:**
- Estado indeterminate
- Descripción opcional
- Animaciones
- Validación

**Ejemplo de uso:**
```php
use Components\Shared\Forms\CheckboxComponent\CheckboxComponent;

$checkbox = new CheckboxComponent(
    id: "terms",
    label: "Acepto los términos y condiciones",
    description: "Lee nuestros términos antes de continuar",
    required: true
);

echo $checkbox->render();
```

**API JavaScript:**
```javascript
// Verificar estado
const isChecked = window.LegoCheckbox.isChecked("terms");

// Establecer estado
window.LegoCheckbox.setChecked("terms", true);

// Estado indeterminado
window.LegoCheckbox.setIndeterminate("terms", true);

// Toggle
window.LegoCheckbox.toggle("terms");
```

---

### 5. RadioComponent
Radio buttons con navegación por teclado.

**Características:**
- Agrupación automática
- Navegación con arrows
- Descripción opcional
- Validación de grupo

**Ejemplo de uso:**
```php
use Components\Shared\Forms\RadioComponent\RadioComponent;

$radio1 = new RadioComponent(
    id: "payment-card",
    name: "payment-method",
    label: "Tarjeta de crédito",
    value: "card",
    checked: true
);

$radio2 = new RadioComponent(
    id: "payment-cash",
    name: "payment-method",
    label: "Efectivo",
    value: "cash"
);

echo $radio1->render() . $radio2->render();
```

**API JavaScript:**
```javascript
// Obtener valor seleccionado del grupo
const value = window.LegoRadio.getSelectedValue("payment-method");

// Obtener ID seleccionado
const id = window.LegoRadio.getSelectedId("payment-method");

// Seleccionar por valor
window.LegoRadio.setSelectedByValue("payment-method", "cash");

// Limpiar selección
window.LegoRadio.clearSelection("payment-method");
```

---

### 6. ButtonComponent
Botones con múltiples variantes y estados.

**Características:**
- Variantes: primary, secondary, success, danger, ghost
- Estados de loading
- Tamaños: sm, md, lg
- Iconos opcionales
- Efecto ripple

**Ejemplo de uso:**
```php
use Components\Shared\Forms\ButtonComponent\ButtonComponent;

$button = new ButtonComponent(
    text: "Guardar",
    variant: "primary",
    type: "submit",
    icon: "save-outline",
    fullWidth: true
);

echo $button->render();
```

**API JavaScript:**
```javascript
// Estado de loading
window.LegoButton.setLoading("btn-id", true);

// Deshabilitar
window.LegoButton.setDisabled("btn-id", true);

// Cambiar texto
window.LegoButton.setText("btn-id", "Guardando...");

// Click programático
window.LegoButton.click("btn-id");

// Ejecutar con loading automático
await window.LegoButton.withLoading("btn-id", async () => {
    await fetch('/api/save');
});
```

---

### 7. FormComponent
Contenedor de formulario con validación automática.

**Características:**
- Validación automática de todos los campos
- Prevención de doble submit
- Mensajes de éxito/error
- Layouts: vertical, horizontal, inline
- Eventos custom

**Ejemplo de uso:**
```php
use Components\Shared\Forms\FormComponent\FormComponent;

$formContent =
    $nameInput->render() .
    $emailInput->render() .
    $submitButton->render();

$form = new FormComponent(
    id: "contact-form",
    title: "Contáctanos",
    description: "Completa el formulario",
    content: $formContent,
    layout: "vertical"
);

echo $form->render();
```

**API JavaScript:**
```javascript
// Validar formulario
const isValid = window.LegoForm.validate("contact-form");

// Obtener datos
const data = window.LegoForm.getData("contact-form");

// Resetear
window.LegoForm.reset("contact-form");

// Mensajes
window.LegoForm.showSuccess("contact-form", "¡Enviado!");
window.LegoForm.showError("contact-form", "Error al enviar");
window.LegoForm.hideMessages("contact-form");
```

**Eventos custom:**
```javascript
// Escuchar submit
document.getElementById('contact-form').addEventListener('lego:form-submit', async (e) => {
    console.log('Datos:', e.detail.data);

    // Enviar al servidor
    const response = await fetch('/api/contact', {
        method: 'POST',
        body: JSON.stringify(e.detail.data)
    });

    if (response.ok) {
        window.LegoForm.showSuccess('contact-form', '¡Mensaje enviado!');
    }
});

// Validación fallida
document.getElementById('contact-form').addEventListener('lego:form-validation-failed', (e) => {
    console.log('Validación falló para:', e.detail.id);
});
```

---

## Eventos Custom

Todos los componentes emiten eventos personalizados:

```javascript
// Input change
document.addEventListener('lego:input-change', (e) => {
    console.log('Input:', e.detail.id, 'Valor:', e.detail.value);
});

// Select change
document.addEventListener('lego:select-change', (e) => {
    console.log('Select:', e.detail.id, 'Valor:', e.detail.value);
});

// Checkbox change
document.addEventListener('lego:checkbox-change', (e) => {
    console.log('Checkbox:', e.detail.id, 'Checked:', e.detail.checked);
});

// Radio change
document.addEventListener('lego:radio-change', (e) => {
    console.log('Radio:', e.detail.group, 'Valor:', e.detail.value);
});

// TextArea change
document.addEventListener('lego:textarea-change', (e) => {
    console.log('TextArea:', e.detail.id, 'Valor:', e.detail.value);
});

// Button click
document.addEventListener('lego:button-click', (e) => {
    console.log('Button:', e.detail.id);
});
```

---

## Temas y Estilos

Todos los componentes usan las variables CSS de Lego:

- `--accent-primary` - Color primario
- `--bg-surface` - Fondo de superficies
- `--bg-surface-secondary` - Fondo secundario
- `--border-light` - Borde claro
- `--border-dark` - Borde oscuro
- `--text-primary` - Texto primario
- `--text-secondary` - Texto secundario
- `--text-tertiary` - Texto terciario
- `--color-red-600` - Errores
- `--color-green-600` - Éxito
- `--color-orange-600` - Advertencias

Soporte completo para **dark mode** automático.

---

## Página de Demostración

Accede a `/component/forms-showcase` para ver todos los componentes en acción con ejemplos interactivos.

También disponible desde el menú: **Forms Showcase**

---

## Filosofía Lego

✅ **Named Arguments**: Todos los parámetros con nombres claros y tipos
✅ **Type-Safe**: Uso estricto de tipos en PHP
✅ **Declarativo**: Componentes se declaran con configuración clara
✅ **Reutilizable**: Cada componente es independiente
✅ **Composable**: Los componentes se pueden anidar
✅ **Auto-loading**: CSS y JS se cargan automáticamente
✅ **Event-Driven**: Comunicación via eventos custom

---

## Soporte de Navegadores

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Navegadores modernos con soporte para ES6+

---

## Creado con Lego Framework
Todos los componentes siguen el patrón `CoreComponent` y están listos para usar.
