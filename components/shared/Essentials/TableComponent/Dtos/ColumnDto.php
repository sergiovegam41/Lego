<?php
namespace Components\Shared\Essentials\TableComponent\Dtos;

/**
 * ColumnDto - Definición tipo-safe de una columna de AG Grid
 *
 * FILOSOFÍA LEGO:
 * DTO inmutable que representa una columna de tabla con todas
 * las configuraciones disponibles de AG Grid.
 *
 * EJEMPLO BÁSICO:
 * new ColumnDto(
 *     field: "name",
 *     headerName: "Nombre"
 * )
 *
 * EJEMPLO AVANZADO:
 * new ColumnDto(
 *     field: "price",
 *     headerName: "Precio",
 *     sortable: true,
 *     filter: true,
 *     width: 150,
 *     valueFormatter: "params => '$' + params.value.toFixed(2)"
 * )
 */
class ColumnDto {
    public function __construct(
        // Básicos
        public readonly string $field,                     // Campo del dato (requerido)
        public readonly string $headerName = "",           // Nombre del header (default: usa field)

        // Dimensiones
        public readonly int $width = 0,                    // Ancho fijo
        public readonly int $minWidth = 0,                 // Ancho mínimo
        public readonly int $maxWidth = 0,                 // Ancho máximo
        public readonly bool $flex = false,                // Auto-tamaño flexible

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
     */
    public function toArray(): array {
        $config = [
            'field' => $this->field,
            'headerName' => $this->headerName ?: $this->field,
            'resizable' => $this->resizable
        ];

        // Dimensiones
        if ($this->width > 0) $config['width'] = $this->width;
        if ($this->minWidth > 0) $config['minWidth'] = $this->minWidth;
        if ($this->maxWidth > 0) $config['maxWidth'] = $this->maxWidth;
        if ($this->flex) $config['flex'] = 1;

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
        if ($this->valueFormatter) $config['valueFormatter'] = $this->valueFormatter;
        if ($this->cellRenderer) $config['cellRenderer'] = $this->cellRenderer;
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
