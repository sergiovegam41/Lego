<?php

namespace Components\App\ProductsCrudV2;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Forms\Forms\Button;

/**
 * ProductsCrudV2Component
 *
 * FILOSOFÍA LEGO:
 * Nuevo CRUD de productos usando bloques modulares.
 * - Tabla AG Grid para visualización
 * - Páginas hijo para crear/editar (sin modales)
 * - Bloques agnósticos: ApiClient, StateManager, ValidationEngine, TableManager
 *
 * Diferencias con v1:
 * ✓ No usa modales, usa páginas hijo
 * ✓ Código más limpio con bloques modulares
 * ✓ Transición visual natural al abrir/cerrar formulario
 * ✓ La página hijo se ve como parte del flujo, no interrupe
 */
#[ApiComponent('/products-crud-v2', methods: ['GET'])]
class ProductsCrudV2Component extends CoreComponent
{
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    public function __construct() {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./products-crud-v2-page.js", [])
        ];

        $this->CSS_PATHS[] = "./products-crud-v2-page.css";

        // Configurar columnas de la tabla
        $columns = new ColumnCollection(
            new ColumnDto(
                field: 'id',
                headerName: 'ID',
                width: 80,
                sortable: true,
                filter: true,
                pinned: 'left'
            ),
            new ColumnDto(
                field: 'name',
                headerName: 'Nombre',
                width: 200,
                sortable: true,
                filter: true
            ),
            new ColumnDto(
                field: 'sku',
                headerName: 'SKU',
                width: 120,
                sortable: true,
                filter: true
            ),
            new ColumnDto(
                field: 'category',
                headerName: 'Categoría',
                width: 150,
                sortable: true,
                filter: true
            ),
            new ColumnDto(
                field: 'price',
                headerName: 'Precio',
                width: 120,
                sortable: true,
                filter: true
            ),
            new ColumnDto(
                field: 'stock',
                headerName: 'Stock',
                width: 100,
                sortable: true,
                filter: true
            ),
            new ColumnDto(
                field: 'is_active',
                headerName: 'Estado',
                width: 120,
                sortable: true,
                filter: true
            ),
            new ColumnDto(
                field: 'actions',
                headerName: 'Acciones',
                width: 180,
                pinnedPosition: 'right'
            )
        );

        // Renderizar TableComponent
        $table = (new TableComponent(
            id: 'products-crud-v2-table',
            columns: $columns,
            rowData: [],
            pagination: true,
            paginationPageSize: 10,
            paginationPageSizeSelector: [10, 20, 50, 100],
            rowSelection: 'single',
            animateRows: true,
            enableExport: true,
            exportFileName: 'productos'
        ))->render();

        // Botón crear producto
        $createButton = (new Button(
            text: 'Nuevo Producto',
            type: 'button',
            variant: 'primary',
            icon: 'add-outline',
            onClick: 'openCreatePage()'
        ))->render();

        return <<<HTML
        <div class="products-crud-v2-container">

            <div class="products-crud-v2-header">
                <div class="products-crud-v2-title">
                    <h1>Gestión de Productos</h1>
                    <p>CRUD con Bloques Modulares + Páginas Hijo</p>
                </div>
                {$createButton}
            </div>

            <div class="products-crud-v2-content">
                {$table}
            </div>

            <!-- Contenedor para la página hijo del formulario -->
            <div id="products-form-page-container" class="products-form-page-container"></div>

        </div>
        HTML;
    }
}
