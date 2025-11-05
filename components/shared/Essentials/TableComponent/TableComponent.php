<?php
namespace Components\Shared\Essentials\TableComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\Components\Table\TableConfig;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;

/**
 * TableComponent - Tabla avanzada con AG Grid
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo que integra AG Grid (https://www.ag-grid.com/)
 * para crear tablas potentes con ordenamiento, filtrado, paginación y más.
 *
 * NUEVO EN V2 (Model-Driven):
 * Ahora soporta modo "mágico" donde pasas un modelo con #[ApiGetResource]
 * y todo se configura automáticamente (endpoint, paginación server-side, etc.)
 *
 * CARACTERÍSTICAS:
 * - Ordenamiento por columnas
 * - Filtros avanzados (texto, número, fecha)
 * - Paginación automática (client-side o server-side)
 * - Selección de filas (simple o múltiple)
 * - Edición en línea
 * - Exportación a CSV/Excel
 * - Agrupación de filas
 * - Columnas redimensionables y reordenables
 * - Temas personalizables
 * - Type-safe con named arguments
 *
 * EJEMPLO BÁSICO (V1 - Compatible):
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
 * EJEMPLO MODEL-DRIVEN (V2 - Nuevo):
 * new TableComponent(
 *     id: "products-table",
 *     model: Product::class,  // ← Magia! Auto-configura todo
 *     columns: new ColumnCollection(
 *         new ColumnDto(field: "id", headerName: "ID"),
 *         new ColumnDto(field: "name", headerName: "Nombre"),
 *         new ColumnDto(field: "price", headerName: "Precio")
 *     )
 *     // rowData se omite - se carga desde API
 *     // pagination server-side automática
 * )
 *
 * EJEMPLO CON PAGINACIÓN CLIENT-SIDE:
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

    // TableConfig para modo model-driven
    private ?TableConfig $tableConfig = null;

    public function __construct(
        // Básicos (requeridos)
        public string $id,
        public ?ColumnCollection $columns = null,
        public array $rowData = [],

        // NUEVO: Model-Driven Mode
        public ?string $model = null,  // Clase del modelo (ej: Product::class)

        // NUEVO: Row Actions (Acciones de fila)
        public ?RowActionsCollection $rowActions = null,  // Acciones personalizables (edit, delete, etc.)

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
        public string $containerClass = "",

        // NUEVO: Server-Side Pagination Mode
        public bool $serverSide = false  // Si true, usa paginación server-side
    ) {
        // Inicializar modo model-driven si se especificó un modelo
        if ($this->model !== null) {
            $this->initializeModelDrivenMode();
        }

        // Validar que se proporcionaron columns (excepto en modo model-driven por ahora)
        if ($this->columns === null && $this->model === null) {
            throw new \InvalidArgumentException(
                "TableComponent requires either 'columns' or 'model' parameter"
            );
        }
    }

    /**
     * Inicializar modo model-driven desde modelo con #[ApiGetResource]
     */
    private function initializeModelDrivenMode(): void
    {
        // Crear TableConfig desde el modelo
        $this->tableConfig = TableConfig::fromModel(
            modelClass: $this->model,
            componentId: $this->id
        );

        // Habilitar servidor-side mode automáticamente
        $this->serverSide = true;

        // Configurar paginación desde modelo
        $this->pagination = true;
        $this->paginationPageSize = $this->tableConfig->getPerPage();

        // Si no se especificaron rowData, inicializar vacío (se cargará desde API)
        if (empty($this->rowData)) {
            $this->rowData = [];
        }
    }

    protected function component(): string {
        // Sanitizar ID para JavaScript (reemplazar guiones por guiones bajos)
        $jsId = str_replace('-', '_', $this->id);

        // Preparar configuración para AG Grid
        $gridOptions = $this->buildGridOptions();
        $columnDefs = $this->columns ? $this->columns->toAgGridConfig() : [];

        // Construir configuración para JavaScript
        $jsConfig = [
            'id' => $this->id,
            'jsId' => $jsId,
            'columnDefs' => $columnDefs,
            'rowData' => $this->rowData,
            'gridOptions' => $gridOptions,
            'callbacks' => $this->buildCallbacks(),
            'serverSide' => $this->serverSide,
        ];

        // Si es modo model-driven, agregar configuración de API
        if ($this->tableConfig !== null) {
            $jsConfig['apiConfig'] = $this->tableConfig->toArray();
        }

        // Si hay acciones de fila, agregarlas
        if ($this->rowActions !== null && !$this->rowActions->isEmpty()) {
            $jsConfig['rowActions'] = $this->rowActions->toArray();
        }

        // Pasar configuración a JavaScript
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./table.js", $jsConfig)
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
