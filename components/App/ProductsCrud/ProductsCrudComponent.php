<?php

namespace Components\App\ProductsCrud;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Forms\Forms\Button;

/**
 * ProductsCrudComponent
 *
 * FILOSOFÍA LEGO:
 * Ejemplo completo de CRUD usando:
 * - TableComponent con AG Grid para visualización
 * - AlertService para UX (modales, confirmaciones, toasts)
 * - ProductsController para operaciones REST
 * - Eloquent ORM para persistencia
 */
#[ApiComponent('/products-crud', methods: ['GET'])]
class ProductsCrudComponent extends CoreComponent
{
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    public function __construct() {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./products-crud.js", [])
        ];

        $this->CSS_PATHS[] = "./products-crud.css";

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
                filter: true,
                editable: false
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
            id: 'products-crud-table',
            columns: $columns,
            rowData: [], // Se cargará dinámicamente desde JavaScript
            pagination: true,
            paginationPageSize: 10,
            paginationPageSizeSelector: [10, 20, 50, 100],
            rowSelection: 'single',
            animateRows: true,
            enableExport: true,
            exportFileName: 'productos'
        ))->render();

        // Botón usando componente LEGO
        $createButton = (new Button(
            text: 'Nuevo Producto',
            type: 'button',
            variant: 'primary',
            icon: 'add-outline',
            onClick: 'createProduct()'
        ))->render();

        return <<<HTML
        <div class="products-crud-container">

            <div class="products-crud-header">
                <div class="products-crud-title">
                    <h1>Gestión de Productos</h1>
                    <p>CRUD completo con TableComponent + AlertService + ProductsController</p>
                </div>
                {$createButton}
            </div>

            <div class="products-crud-stats">
                <!-- <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Productos</div>
                        <div class="stat-value" id="total-products">0</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✓</div>
                    <div class="stat-info">
                        <div class="stat-label">Activos</div>
                        <div class="stat-value" id="active-products">0</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <div class="stat-label">En Stock</div>
                        <div class="stat-value" id="instock-products">0</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <div class="stat-label">Valor Total</div>
                        <div class="stat-value" id="total-value">$0.00</div>
                    </div>
                </div>
            </div> -->

            {$table}

        </div>
        HTML;
    }
}
