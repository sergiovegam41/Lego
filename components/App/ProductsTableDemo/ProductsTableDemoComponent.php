<?php

namespace Components\App\ProductsTableDemo;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Core\Types\DimensionValue;
use App\Models\Product;

/**
 * ProductsTableDemoComponent - Demo de TableComponent Model-Driven
 *
 * PROPÓSITO:
 * Demostrar el nuevo sistema model-driven de TableComponent.
 * Pasando solo Product::class, todo se configura automáticamente:
 * - Endpoint API: /api/get/products
 * - Paginación server-side
 * - Filtros y búsqueda desde el modelo
 */
#[ApiComponent('/products-table-demo', methods: ['GET'])]
class ProductsTableDemoComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./products-table-demo.css"];

    public function __construct(array $params = [])
    {
        // No llamar parent::__construct() - CoreComponent no tiene constructor
    }

    protected function component(): string
    {
        // Definir columnas para la tabla
        $columns = new ColumnCollection(
            new ColumnDto(
                field: "id",
                headerName: "ID",
                width: DimensionValue::px(80)
            ),
            new ColumnDto(
                field: "name",
                headerName: "Nombre",
                sortable: true,
                filter: true,
                width: DimensionValue::px(200)
            ),
            new ColumnDto(
                field: "sku",
                headerName: "SKU",
                width: DimensionValue::px(120)
            ),
            new ColumnDto(
                field: "price",
                headerName: "Precio",
                sortable: true,
                filter: true,
                width: DimensionValue::px(120),
                valueFormatter: 'params => "$" + parseFloat(params.value).toFixed(2)'
            ),
            new ColumnDto(
                field: "stock",
                headerName: "Stock",
                sortable: true,
                filter: true,
                width: DimensionValue::px(100)
            ),
            new ColumnDto(
                field: "category",
                headerName: "Categoría",
                sortable: true,
                filter: true,
                width: DimensionValue::px(150)
            ),
            new ColumnDto(
                field: "is_active",
                headerName: "Estado",
                width: DimensionValue::px(100),
                cellRenderer: 'params => params.value ?
                    "<span class=\"badge badge-success\">Activo</span>" :
                    "<span class=\"badge badge-inactive\">Inactivo</span>"'
            )
        );

        // Definir acciones personalizables para cada fila
        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEdit",
                variant: "primary",
                tooltip: "Editar producto"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDelete",
                variant: "danger",
                confirm: true,
                confirmMessage: "¿Eliminar este producto?",
                tooltip: "Eliminar producto"
            )
        );

        // ✨ MAGIA: Solo pasamos el modelo y todo se configura automáticamente
        $table = new TableComponent(
            id: "products-demo-table",
            model: Product::class,  // ← Aquí está la magia!
            columns: $columns,
            rowActions: $actions,    // ← Acciones personalizables
            height: "600px",
            pagination: true  // Se configura automáticamente según el modelo
        );

        return <<<HTML
        <div class="products-table-demo">
            <div class="products-table-demo__header">
                <h1 class="products-table-demo__title">
                    <ion-icon name="grid-outline"></ion-icon>
                    Products Table - Model-Driven Demo
                </h1>
                <p class="products-table-demo__subtitle">
                    Tabla con paginación server-side automática desde <code>Product::class</code>
                </p>
            </div>

            <div class="products-table-demo__info">
                <div class="info-card">
                    <h3>✨ Configuración Automática</h3>
                    <ul>
                        <li><strong>Endpoint:</strong> /api/get/products</li>
                        <li><strong>Paginación:</strong> Server-side (offset)</li>
                        <li><strong>Per Page:</strong> 20 elementos</li>
                        <li><strong>Sortable:</strong> id, name, price, stock, created_at</li>
                        <li><strong>Filterable:</strong> category, is_active</li>
                        <li><strong>Searchable:</strong> name, description, sku</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h3>🎯 Código PHP</h3>
                    <pre><code>new TableComponent(
        id: "products-demo-table",
        model: Product::class,  // ← Magia!
        columns: \$columns
        );</code></pre>
                </div>
            </div>

            <div class="products-table-demo__table-container">
                {$table->render()}
            </div>

            <div class="products-table-demo__footer">
                <p><strong>Nota:</strong> Los datos se cargan directamente desde la API <code>/api/get/products</code></p>
                <p>El modelo <code>Product</code> tiene el atributo <code>#[ApiGetResource]</code> que configura todo automáticamente.</p>
            </div>
        </div>
        HTML;
    }
}
