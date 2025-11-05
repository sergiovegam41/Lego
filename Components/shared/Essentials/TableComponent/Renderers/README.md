# TableComponent Renderers

Los **Renderers** son clases PHP que generan cell renderers de AG Grid de forma declarativa, eliminando la necesidad de escribir JavaScript manual para cada tabla.

## Filosofía LEGO

Estas clases siguen la filosofía LEGO de composición declarativa:
- Configuración type-safe desde PHP
- Reutilización de componentes visuales comunes
- Consistencia con el sistema de temas
- Reducción de código boilerplate (~75 líneas por CRUD)

## Renderers Disponibles

### 1. ActionButtonsRenderer

Genera botones de acción (Editar, Eliminar, personalizados) con iconos SVG.

**Ejemplo Básico:**
```php
use Components\Shared\Essentials\TableComponent\Renderers\ActionButtonsRenderer;

new ColumnDto(
    field: "actions",
    headerName: "Acciones",
    cellRenderer: ActionButtonsRenderer::create(
        editFunction: 'editProduct',
        deleteFunction: 'deleteProduct'
    )
)
```

**Ejemplo con Botones Personalizados:**
```php
ActionButtonsRenderer::create(
    editFunction: 'editProduct',
    deleteFunction: 'deleteProduct',
    customButtons: [
        [
            'icon' => 'eye',
            'function' => 'viewProduct',
            'tooltip' => 'Ver detalles',
            'color' => 'blue'
        ]
    ]
)
```

**Parámetros:**
- `editFunction`: Nombre de la función global para editar (string)
- `deleteFunction`: Nombre de la función global para eliminar (string)
- `customButtons`: Array de botones personalizados (array)
- `showEdit`: Mostrar botón de editar (bool, default: true)
- `showDelete`: Mostrar botón de eliminar (bool, default: true)
- `idField`: Campo que contiene el ID (string, default: 'id')

---

### 2. StatusBadgeRenderer

Renderiza badges de estado con colores y etiquetas configurables.

**Ejemplo Básico (Estados Predefinidos):**
```php
use Components\Shared\Essentials\TableComponent\Renderers\StatusBadgeRenderer;

new ColumnDto(
    field: "status",
    headerName: "Estado",
    cellRenderer: StatusBadgeRenderer::create()
)
// Reconoce automáticamente: active, inactive, pending, approved, rejected
```

**Ejemplo Personalizado:**
```php
StatusBadgeRenderer::create(
    statusMap: [
        'in_stock' => ['label' => 'En Stock', 'color' => 'green', 'icon' => 'check'],
        'low_stock' => ['label' => 'Stock Bajo', 'color' => 'yellow', 'icon' => 'alert'],
        'out_of_stock' => ['label' => 'Agotado', 'color' => 'red', 'icon' => 'x']
    ],
    showIcon: true
)
```

**Parámetros:**
- `statusMap`: Mapeo de valores a configuración (array)
- `defaultColor`: Color por defecto (string, default: 'gray')
- `showIcon`: Mostrar iconos (bool, default: false)

**Colores Disponibles:** green, red, yellow, blue, purple, gray

---

### 3. CurrencyRenderer

Formatea valores monetarios con símbolo, separadores y decimales.

**Ejemplo Básico:**
```php
use Components\Shared\Essentials\TableComponent\Renderers\CurrencyRenderer;

new ColumnDto(
    field: "price",
    headerName: "Precio",
    cellRenderer: CurrencyRenderer::create(currency: 'USD')
)
```

**Ejemplo Avanzado:**
```php
CurrencyRenderer::create(
    currency: 'MXN',
    decimals: 2,
    thousandsSeparator: ',',
    decimalSeparator: '.',
    showNegativeInRed: true
)
```

**Parámetros:**
- `currency`: Código de moneda ISO (string, default: 'USD')
- `decimals`: Número de decimales (int, default: 2)
- `thousandsSeparator`: Separador de miles (string, default: ',')
- `decimalSeparator`: Separador decimal (string, default: '.')
- `showNegativeInRed`: Resaltar negativos en rojo (bool, default: true)
- `position`: Posición del símbolo: 'before' o 'after' (string, default: 'before')

**Monedas Soportadas:** USD, EUR, GBP, JPY, MXN, CAD, AUD, CHF, CNY, INR, BRL, ARS, CLP, COP, PEN

---

### 4. StockLevelRenderer

Renderiza niveles de stock con indicadores visuales de color y barras de progreso opcionales.

**Ejemplo Básico:**
```php
use Components\Shared\Essentials\TableComponent\Renderers\StockLevelRenderer;

new ColumnDto(
    field: "stock",
    headerName: "Stock",
    cellRenderer: StockLevelRenderer::create(
        lowThreshold: 10,
        mediumThreshold: 50
    )
)
```

**Ejemplo con Barra de Progreso:**
```php
StockLevelRenderer::create(
    lowThreshold: 20,
    mediumThreshold: 100,
    maxValue: 500,
    showProgressBar: true,
    unit: 'uds'
)
```

**Parámetros:**
- `lowThreshold`: Umbral de stock bajo (int, default: 10)
- `mediumThreshold`: Umbral de stock medio (int, default: 50)
- `maxValue`: Valor máximo para barra de progreso (int|null, default: null)
- `showProgressBar`: Mostrar barra de progreso (bool, default: false)
- `showBadge`: Mostrar badge con valor (bool, default: true)
- `unit`: Unidad de medida (string, default: '')

**Niveles de Color:**
- **Rojo (empty)**: 0 unidades
- **Naranja (low)**: ≤ lowThreshold
- **Amarillo (medium)**: ≤ mediumThreshold
- **Verde (high)**: > mediumThreshold

---

### 5. DateRenderer

Formatea fechas con múltiples formatos predefinidos y fechas relativas.

**Ejemplo Básico:**
```php
use Components\Shared\Essentials\TableComponent\Renderers\DateRenderer;

new ColumnDto(
    field: "created_at",
    headerName: "Fecha Creación",
    cellRenderer: DateRenderer::create(format: 'medium')
)
```

**Ejemplo con Fecha Relativa:**
```php
DateRenderer::create(format: 'relative')
// Output: "hace 2 días"
```

**Parámetros:**
- `format`: Formato de fecha (string, default: 'medium')
- `showTooltip`: Mostrar tooltip con fecha completa (bool, default: true)
- `emptyText`: Texto para valores vacíos (string, default: '-')

**Formatos Disponibles:**
- `short`: 15/10/24
- `medium`: 15 Oct 2024
- `long`: 15 de octubre de 2024
- `datetime`: 15/10/24 14:30
- `time`: 14:30
- `relative`: hace 2 días

---

### 6. ImageRenderer

Renderiza imágenes como thumbnails con preview opcional al hacer clic.

**Ejemplo Básico:**
```php
use Components\Shared\Essentials\TableComponent\Renderers\ImageRenderer;

new ColumnDto(
    field: "image_url",
    headerName: "Imagen",
    cellRenderer: ImageRenderer::create(
        size: 'medium',
        shape: 'rounded'
    )
)
```

**Ejemplo para Avatares:**
```php
ImageRenderer::create(
    size: 'small',
    shape: 'circle',
    showPreview: false
)
```

**Parámetros:**
- `size`: Tamaño del thumbnail: 'small', 'medium', 'large' (string, default: 'medium')
- `shape`: Forma: 'circle', 'square', 'rounded' (string, default: 'rounded')
- `showPreview`: Habilitar preview al hacer clic (bool, default: true)
- `placeholderUrl`: URL de imagen placeholder (string, default: '')
- `lazyLoad`: Lazy loading de imágenes (bool, default: true)

**Tamaños:**
- `small`: 32x32px
- `medium`: 48x48px
- `large`: 64x64px

---

### 7. BooleanRenderer

Renderiza valores booleanos con iconos y/o texto.

**Ejemplo Básico:**
```php
use Components\Shared\Essentials\TableComponent\Renderers\BooleanRenderer;

new ColumnDto(
    field: "is_active",
    headerName: "Activo",
    cellRenderer: BooleanRenderer::create()
)
// true → ✓ verde, false → ✗ rojo
```

**Ejemplo con Badge:**
```php
BooleanRenderer::create(
    trueText: 'Activo',
    falseText: 'Inactivo',
    showText: true,
    style: 'badge'
)
```

**Parámetros:**
- `trueText`: Texto para valor verdadero (string, default: 'Sí')
- `falseText`: Texto para valor falso (string, default: 'No')
- `showText`: Mostrar texto junto al icono (bool, default: false)
- `showIcon`: Mostrar icono (bool, default: true)
- `style`: Estilo de renderizado: 'icon', 'badge', 'text' (string, default: 'icon')

**Valores Reconocidos como True:** true, 1, "true", "yes", "si", "1"

---

## Uso en TableComponent

Todos los renderers se usan como valores de `cellRenderer` en `ColumnDto`:

```php
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Renderers\{
    ActionButtonsRenderer,
    StatusBadgeRenderer,
    CurrencyRenderer,
    StockLevelRenderer
};

$columns = new ColumnCollection(
    new ColumnDto(
        field: "name",
        headerName: "Producto",
        sortable: true,
        filter: true
    ),
    new ColumnDto(
        field: "price",
        headerName: "Precio",
        cellRenderer: CurrencyRenderer::create(currency: 'USD')
    ),
    new ColumnDto(
        field: "stock",
        headerName: "Stock",
        cellRenderer: StockLevelRenderer::create(lowThreshold: 10)
    ),
    new ColumnDto(
        field: "status",
        headerName: "Estado",
        cellRenderer: StatusBadgeRenderer::create()
    ),
    new ColumnDto(
        field: "actions",
        headerName: "Acciones",
        cellRenderer: ActionButtonsRenderer::create(
            editFunction: 'editProduct',
            deleteFunction: 'deleteProduct'
        )
    )
);
```

## Ventajas de los Renderers

1. **Type-Safe**: PHP valida los parámetros en tiempo de desarrollo
2. **Reutilizables**: Misma configuración en múltiples tablas
3. **Theme-Aware**: Se adaptan automáticamente al tema claro/oscuro
4. **Menos Código**: ~75 líneas menos de JavaScript por CRUD
5. **Consistencia**: Mismo look & feel en toda la aplicación
6. **Mantenibilidad**: Cambios centralizados en lugar de distribuidos

## Creando Renderers Personalizados

Para crear un renderer personalizado, extiende `CellRenderer`:

```php
namespace Components\Shared\Essentials\TableComponent\Renderers;

class MyCustomRenderer extends CellRenderer
{
    private string $config;

    private function __construct(string $config)
    {
        $this->config = $config;
    }

    public static function create(string $config): self
    {
        return new self($config);
    }

    public function toJavaScript(): string
    {
        $config = $this->escapeJs($this->config);

        return <<<JS
(params) => {
    const value = params.value;
    const config = '{$config}';

    // Tu lógica aquí

    return `<div>HTML Renderizado</div>`;
}
JS;
    }
}
```

## Best Practices

1. **Usa los renderers predefinidos** siempre que sea posible
2. **Combina renderers** con otras opciones de ColumnDto (sortable, filter, etc.)
3. **Configura umbrales** según tus necesidades de negocio
4. **Usa named arguments** para claridad: `create(currency: 'USD')`
5. **Mantén la consistencia** usando los mismos renderers para datos similares
6. **Aprovecha el sistema de temas** - los renderers ya son responsive

## Roadmap

Próximos renderers planeados:
- TagsRenderer (para arrays de tags)
- ProgressBarRenderer (barras de progreso genéricas)
- RatingRenderer (estrellas/ratings)
- LinkRenderer (enlaces con iconos)
- ColorSwatchRenderer (muestras de color)

---

**Contribuir**: Si necesitas un renderer común que no existe, considera agregarlo al CORE siguiendo el patrón existente.
