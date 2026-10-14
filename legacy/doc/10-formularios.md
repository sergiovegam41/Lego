# Formularios

## InputTextComponent

```php
use Components\Shared\Forms\InputTextComponent\InputTextComponent;

$input = new InputTextComponent(
    id: "nombre",
    label: "Nombre",
    placeholder: "Ingrese nombre",
    required: true,
    type: "text"  // text, email, password, number, tel
);
```

## TextAreaComponent

```php
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;

$textarea = new TextAreaComponent(
    id: "descripcion",
    label: "Descripción",
    placeholder: "Descripción detallada...",
    rows: 4,
    required: false
);
```

## SelectComponent

```php
use Components\Shared\Forms\SelectComponent\SelectComponent;

$options = [
    ["value" => "cat1", "label" => "Categoría 1"],
    ["value" => "cat2", "label" => "Categoría 2"],
];

$select = new SelectComponent(
    id: "categoria",
    label: "Categoría",
    options: $options,
    required: true,
    searchable: true
);
```

### JS API

```javascript
// Obtener instancia
const select = window.LegoSelect?.instances?.['categoria'];

// Setear valor
select.setValue('cat1');

// Obtener valor
const value = select.getValue();

// Reset
select.reset();
```

## FilePondComponent

```php
use Components\Shared\Forms\FilePondComponent\FilePondComponent;

$uploader = new FilePondComponent(
    id: "imagenes",
    label: "Imágenes",
    path: "productos/images/",  // Ruta en storage
    maxFiles: 5,
    maxFileSize: 5242880,  // 5MB
    allowMultiple: true,
    allowReorder: true,
    acceptedFileTypes: ["image/jpeg", "image/png", "image/webp"]
);
```

### JS API

```javascript
// Obtener instancia
const pond = window.LegoFilePond?.instances?.['imagenes'];

// Obtener archivos
const files = pond.getFiles();

// Obtener IDs de imágenes
const imageIds = document.getElementById('imagenes-image-ids')?.value;
```

## Formulario Completo

```php
protected function component(): string
{
    $nombre = new InputTextComponent(id: "nombre", label: "Nombre", required: true);
    $descripcion = new TextAreaComponent(id: "descripcion", label: "Descripción");
    $precio = new InputTextComponent(id: "precio", label: "Precio", type: "number");
    $categoria = new SelectComponent(id: "categoria", label: "Categoría", options: $this->categorias);
    $imagenes = new FilePondComponent(id: "imagenes", label: "Imágenes", path: "productos/");

    return <<<HTML
    <form id="producto-form" onsubmit="return false;">
        {$nombre->render()}
        {$descripcion->render()}
        {$precio->render()}
        {$categoria->render()}
        {$imagenes->render()}
        
        <button type="button" onclick="cancelar()">Cancelar</button>
        <button type="submit" onclick="guardar()">Guardar</button>
    </form>
    HTML;
}
```

## Recolectar Datos en JS

```javascript
function getFormData() {
    return {
        name: document.getElementById('nombre')?.value || '',
        description: document.getElementById('descripcion')?.value || '',
        price: parseFloat(document.getElementById('precio')?.value) || 0,
        category: window.LegoSelect?.instances?.['categoria']?.getValue() || '',
        image_ids: document.getElementById('imagenes-image-ids')?.value || ''
    };
}

async function guardar() {
    const data = getFormData();
    
    const response = await fetch('/api/productos/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.success) {
        AlertService.toast('Guardado', 'success');
        window.legoWindowManager.closeCurrentWindow({ refresh: true });
    } else {
        AlertService.error(result.message);
    }
}
```

## Validación

```javascript
function validateForm(data) {
    const errors = {};
    
    if (!data.name?.trim()) {
        errors.name = 'El nombre es requerido';
    }
    
    if (!data.price || data.price <= 0) {
        errors.price = 'El precio debe ser mayor a 0';
    }
    
    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}
```

