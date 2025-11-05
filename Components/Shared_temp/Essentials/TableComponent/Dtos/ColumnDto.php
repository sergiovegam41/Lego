<?php
namespace Components\Shared\Essentials\TableComponent\Dtos;

use Core\Types\DimensionValue;

/**
 * ColumnDto - Definición tipo-safe de una columna de AG Grid
 *
 * FILOSOFÍA LEGO:
 * DTO inmutable que representa una columna de tabla con todas
 * las configuraciones disponibles de AG Grid.
 *
 * CONSISTENCIA DIMENSIONAL:
 * "Las distancias importan más que los valores absolutos"
 * - Usa DimensionValue para anchos type-safe
 * - Valida configuraciones en construcción
 * - Mantiene proporciones consistentes
 *
 * EJEMPLO BÁSICO:
 * new ColumnDto(
 *     field: "name",
 *     headerName: "Nombre"
 * )
 *
 * EJEMPLO CON DIMENSIONES:
 * use Core\Types\DimensionValue;
 *
 * new ColumnDto(
 *     field: "price",
 *     headerName: "Precio",
 *     sortable: true,
 *     filter: true,
 *     width: DimensionValue::px(150),
 *     valueFormatter: "params => '$' + params.value.toFixed(2)"
 * )
 *
 * EJEMPLO FLEXIBLE:
 * new ColumnDto(
 *     field: "name",
 *     width: DimensionValue::flex(2)  // Crece 2x comparado con flex(1)
 * )
 *
 * EJEMPLO PORCENTUAL:
 * new ColumnDto(
 *     field: "category",
 *     width: DimensionValue::percent(25)  // 25% del ancho total
 * )
 */
class ColumnDto {
    public function __construct(
        // Básicos
        public readonly string $field,                     // Campo del dato (requerido)
        public readonly string $headerName = "",           // Nombre del header (default: usa field)

        // Dimensiones (REFACTORIZADO - Ahora usa DimensionValue)
        public readonly ?DimensionValue $width = null,     // Ancho (px, %, flex, auto)
        public readonly ?DimensionValue $minWidth = null,  // Ancho mínimo
        public readonly ?DimensionValue $maxWidth = null,  // Ancho máximo

        // Comportamiento
        public readonly bool $sortable = false,            // Activar ordenamiento
        public readonly bool $filter = false,              // Activar filtro
        public readonly string $filterType = "text",       // Tipo de filtro: text, number, date
        public readonly bool $editable = false,            // Celdas editables
        public readonly bool $resizable = true,            // Redimensionable
        public readonly bool $hide = false,                // Ocultar columna
        public readonly bool $pinned = false,              // Fijar: false, 'left', 'right'
        public readonly string $pinnedPosition = "",       // 'left' o 'right'

        // Formato y render
        public readonly string $valueFormatter = "",       // Función para formatear valor
        public readonly string $cellRenderer = "",         // Custom cell renderer
        public readonly string $cellClass = "",            // Clase CSS de celda
        public readonly string $headerClass = "",          // Clase CSS de header

        // Agrupación
        public readonly bool $rowGroup = false,            // Usar como agrupador
        public readonly bool $enableRowGroup = false,      // Permitir agrupar por esta columna

        // Otros
        public readonly bool $checkboxSelection = false,   // Mostrar checkbox
        public readonly bool $headerCheckboxSelection = false, // Checkbox en header
        public readonly string $cellStyle = "",            // Estilos inline de celda
        public readonly string $tooltipField = ""          // Campo para tooltip
    ) {}

    /**
     * Convierte el DTO a array para AG Grid
     *
     * Mantiene las "distancias" (proporciones) al convertir DimensionValue
     * a configuración de AG Grid.
     *
     * IMPORTANTE: valueFormatter y cellRenderer no se incluyen aquí porque
     * contienen código JavaScript que no puede serializarse con JSON.stringify().
     * Se manejan por separado en table.js usando placeholders.
     */
    public function toArray(): array {
        $config = [
            'field' => $this->field,
            'headerName' => $this->headerName ?: $this->field,
            'resizable' => $this->resizable
        ];

        // Dimensiones - Usando DimensionValue (mantiene proporciones)
        if ($this->width) {
            $config = array_merge($config, $this->width->toAgGrid());
        }
        if ($this->minWidth) {
            $minWidthConfig = $this->minWidth->toAgGrid();
            if (isset($minWidthConfig['width'])) {
                $config['minWidth'] = $minWidthConfig['width'];
            }
        }
        if ($this->maxWidth) {
            $maxWidthConfig = $this->maxWidth->toAgGrid();
            if (isset($maxWidthConfig['width'])) {
                $config['maxWidth'] = $maxWidthConfig['width'];
            }
        }

        // Comportamiento
        if ($this->sortable) $config['sortable'] = true;
        if ($this->filter) {
            $config['filter'] = match($this->filterType) {
                'number' => 'agNumberColumnFilter',
                'date' => 'agDateColumnFilter',
                'set' => 'agSetColumnFilter',
                default => 'agTextColumnFilter'
            };
        }
        if ($this->editable) $config['editable'] = true;
        if ($this->hide) $config['hide'] = true;
        if ($this->pinnedPosition) $config['pinned'] = $this->pinnedPosition;

        // Formato y render
        // IMPORTANTE: valueFormatter y cellRenderer se marcan con placeholders
        // El código JavaScript real se pasa por separado para evitar problemas
        // con JSON.stringify() cuando el código contiene comillas
        if ($this->valueFormatter) {
            $config['_valueFormatterCode'] = $this->valueFormatter;
        }
        if ($this->cellRenderer) {
            $config['_cellRendererCode'] = $this->cellRenderer;
        }
        if ($this->cellClass) $config['cellClass'] = $this->cellClass;
        if ($this->headerClass) $config['headerClass'] = $this->headerClass;
        if ($this->cellStyle) $config['cellStyle'] = $this->cellStyle;
        if ($this->tooltipField) $config['tooltipField'] = $this->tooltipField;

        // Agrupación
        if ($this->rowGroup) $config['rowGroup'] = true;
        if ($this->enableRowGroup) $config['enableRowGroup'] = true;

        // Checkboxes
        if ($this->checkboxSelection) $config['checkboxSelection'] = true;
        if ($this->headerCheckboxSelection) $config['headerCheckboxSelection'] = true;

        return $config;
    }
}
