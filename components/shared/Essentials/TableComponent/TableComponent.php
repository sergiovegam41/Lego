<?php
namespace Components\Shared\Essentials\TableComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;

/**
 * TableComponent - Tabla avanzada con AG Grid
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo que integra AG Grid (https://www.ag-grid.com/)
 * para crear tablas potentes con ordenamiento, filtrado, paginación y más.
 *
 * CARACTERÍSTICAS:
 * - Ordenamiento por columnas
 * - Filtros avanzados (texto, número, fecha)
 * - Paginación automática
 * - Selección de filas (simple o múltiple)
 * - Edición en línea
 * - Exportación a CSV/Excel
 * - Agrupación de filas
 * - Columnas redimensionables y reordenables
 * - Temas personalizables
 * - Type-safe con named arguments
 *
 * EJEMPLO BÁSICO:
 * new TableComponent(
 *     id: "users-table",
 *     columns: new ColumnCollection(
 *         new ColumnDto(field: "id", headerName: "ID", width: 80),
 *         new ColumnDto(field: "name", headerName: "Nombre", sortable: true, filter: true),
 *         new ColumnDto(field: "email", headerName: "Email", filter: true),
 *         new ColumnDto(field: "role", headerName: "Rol", sortable: true)
 *     ),
 *     rowData: $users
 * )
 *
 * EJEMPLO CON PAGINACIÓN:
 * new TableComponent(
 *     id: "products-table",
 *     columns: $productColumns,
 *     rowData: $products,
 *     pagination: true,
 *     paginationPageSize: 20
 * )
 *
 * EJEMPLO CON SELECCIÓN:
 * new TableComponent(
 *     id: "items-table",
 *     columns: $columns,
 *     rowData: $items,
 *     rowSelection: "multiple",
 *     onSelectionChanged: "handleSelection"
 * )
 */
class TableComponent extends CoreComponent {

    protected $CSS_PATHS = ["./table.css"];

    public function __construct(
        // Básicos (requeridos)
        public string $id,
        public ColumnCollection $columns,
        public array $rowData = [],

        // Dimensiones
        public string $height = "500px",
        public string $width = "100%",

        // Paginación
        public bool $pagination = false,
        public int $paginationPageSize = 10,
        public array $paginationPageSizeSelector = [10, 20, 50, 100],

        // Selección
        public string $rowSelection = "single",  // 'single', 'multiple', false
        public bool $suppressRowClickSelection = false,

        // Comportamiento
        public bool $sortable = true,            // Activar ordenamiento global
        public bool $filter = true,              // Activar filtros globales
        public bool $resizable = true,           // Columnas redimensionables
        public bool $editable = false,           // Celdas editables
        public bool $enableRangeSelection = false,
        public bool $suppressRowDeselection = false,

        // Tema y apariencia
        public string $theme = "ag-theme-quartz", // ag-theme-alpine, ag-theme-balham, ag-theme-material, ag-theme-quartz
        public bool $animateRows = true,
        public string $rowHeight = "",           // Altura de fila personalizada

        // Agrupación
        public bool $enableRowGroup = false,
        public bool $groupSelectsChildren = false,

        // Callbacks JavaScript (nombres de funciones)
        public string $onSelectionChanged = "",
        public string $onCellValueChanged = "",
        public string $onRowClicked = "",
        public string $onRowDoubleClicked = "",
        public string $onCellClicked = "",
        public string $onGridReady = "",

        // Features adicionales
        public bool $enableExport = false,       // Botones de exportación
        public string $exportFileName = "export",
        public bool $loading = false,            // Mostrar spinner de carga
        public string $noRowsMessage = "No hay datos disponibles",
        public string $loadingMessage = "Cargando...",

        // Estilos
        public string $className = "",
        public string $containerClass = ""
    ) {}

    protected function component(): string {
        // Sanitizar ID para JavaScript (reemplazar guiones por guiones bajos)
        $jsId = str_replace('-', '_', $this->id);

        // Preparar configuración para AG Grid
        $gridOptions = $this->buildGridOptions();
        $columnDefs = $this->columns->toAgGridConfig();

        // Pasar configuración a JavaScript
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./table.js", [
                'id' => $this->id,
                'jsId' => $jsId,
                'columnDefs' => $columnDefs,
                'rowData' => $this->rowData,
                'gridOptions' => $gridOptions,
                'callbacks' => $this->buildCallbacks()
            ])
        ];

        // Construir clases CSS
        $containerClasses = ["lego-table-container"];
        if ($this->containerClass) {
            $containerClasses[] = $this->containerClass;
        }
        $containerClassStr = implode(" ", $containerClasses);

        $gridClasses = [$this->theme, "lego-table"];
        if ($this->className) {
            $gridClasses[] = $this->className;
        }
        $gridClassStr = implode(" ", $gridClasses);

        // Toolbar con exportación si está habilitado
        $toolbarHtml = "";
        if ($this->enableExport) {
            $toolbarHtml = <<<HTML
            <div class="lego-table-toolbar">
                <div class="lego-table-toolbar__left">
                    <button
                        type="button"
                        class="lego-table-toolbar__btn"
                        onclick="legoTable_{$jsId}_exportCSV()"
                        title="Exportar datos a archivo CSV (compatible con Excel)"
                    >
                        <ion-icon name="download-outline"></ion-icon>
                        <span>Exportar CSV</span>
                    </button>
                </div>
                <div class="lego-table-toolbar__right">
                    <span class="lego-table-toolbar__info" id="{$this->id}-row-count">
                        {$this->getRowCountText()}
                    </span>
                </div>
            </div>
            HTML;
        }

        // Loader overlay
        $loaderHtml = "";
        if ($this->loading) {
            $loaderHtml = <<<HTML
            <div class="lego-table-loader" id="{$this->id}-loader">
                <div class="lego-table-loader__spinner"></div>
                <p class="lego-table-loader__message">{$this->loadingMessage}</p>
            </div>
            HTML;
        }

        // Estilos inline para dimensiones
        $containerStyles = "width: {$this->width};";

        return <<<HTML
        <div class="{$containerClassStr}" style="{$containerStyles}">
            {$toolbarHtml}
            <div
                id="{$this->id}"
                class="{$gridClassStr}"
                style="height: {$this->height};"
                data-table-id="{$this->id}"
            ></div>
            {$loaderHtml}
        </div>
        HTML;
    }

    /**
     * Construye las opciones de configuración para AG Grid
     */
    private function buildGridOptions(): array {
        $options = [
            'defaultColDef' => [
                'sortable' => $this->sortable,
                'filter' => $this->filter,
                'resizable' => $this->resizable,
                'editable' => $this->editable
            ],
            'animateRows' => $this->animateRows,
            'suppressRowClickSelection' => $this->suppressRowClickSelection,
            'suppressRowDeselection' => $this->suppressRowDeselection,
            'enableRangeSelection' => $this->enableRangeSelection,
            'noRowsOverlayComponent' => null,
            'noRowsOverlayComponentParams' => [
                'message' => $this->noRowsMessage
            ]
        ];

        // Paginación
        if ($this->pagination) {
            $options['pagination'] = true;
            $options['paginationPageSize'] = $this->paginationPageSize;
            $options['paginationPageSizeSelector'] = $this->paginationPageSizeSelector;
        }

        // Selección
        if ($this->rowSelection) {
            $options['rowSelection'] = $this->rowSelection;
        }

        // Altura de fila
        if ($this->rowHeight) {
            $options['rowHeight'] = (int)$this->rowHeight;
        }

        // Agrupación
        if ($this->enableRowGroup) {
            $options['groupSelectsChildren'] = $this->groupSelectsChildren;
            $options['autoGroupColumnDef'] = [
                'headerName' => 'Grupo',
                'minWidth' => 200
            ];
        }

        return $options;
    }

    /**
     * Construye los callbacks de JavaScript
     */
    private function buildCallbacks(): array {
        $callbacks = [];

        if ($this->onSelectionChanged) {
            $callbacks['onSelectionChanged'] = $this->onSelectionChanged;
        }
        if ($this->onCellValueChanged) {
            $callbacks['onCellValueChanged'] = $this->onCellValueChanged;
        }
        if ($this->onRowClicked) {
            $callbacks['onRowClicked'] = $this->onRowClicked;
        }
        if ($this->onRowDoubleClicked) {
            $callbacks['onRowDoubleClicked'] = $this->onRowDoubleClicked;
        }
        if ($this->onCellClicked) {
            $callbacks['onCellClicked'] = $this->onCellClicked;
        }
        if ($this->onGridReady) {
            $callbacks['onGridReady'] = $this->onGridReady;
        }

        return $callbacks;
    }

    /**
     * Genera el texto de conteo de filas
     */
    private function getRowCountText(): string {
        $count = count($this->rowData);
        return $count === 1 ? "1 registro" : "{$count} registros";
    }
}
