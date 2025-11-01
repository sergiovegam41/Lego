<?php
namespace Components\App\ProductsCrudV3;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Core\Types\DimensionValue;
use Core\Helpers\ActionButtons;
use App\Models\Product;

/**
 * ProductsCrudV3Component - Vista de tabla (CRUD V3)
 *
 * FILOSOFÍA LEGO:
 * Componente enfocado ÚNICAMENTE en mostrar la tabla de productos.
 * No contiene lógica de formularios - separación de responsabilidades.
 *
 * MEJORAS vs V1/V2:
 * ✅ Anchos con DimensionValue (proporciones consistentes)
 * ✅ Sin duplicación de definiciones
 * ✅ Navegación usando módulos (no window.location.href)
 * ✅ Theming correcto (html.dark, no @media)
 * ✅ Separación clara: 1 componente = 1 responsabilidad
 * ✅ ActionButtons helper (eliminó 50+ líneas de HTML inline)
 * ✅ Componentes dinámicos (batch rendering desde JavaScript)
 *
 * CONSISTENCIA DIMENSIONAL:
 * "Las distancias importan" - columnas usan flex/percent
 * para mantener proporciones visuales consistentes.
 *
 * EJEMPLO DE BOTONES DE ACCIÓN:
 * ANTES: 50+ líneas de HTML/CSS hardcoded en cellRenderer
 * AHORA: ActionButtons::dynamic(['edit', 'delete'])
 */
#[ApiComponent('/products-crud-v3', methods: ['GET'])]
class ProductsCrudV3Component extends CoreComponent
{
    protected $CSS_PATHS = ["./products-crud-v3.css"];
    protected $JS_PATHS = ["./products-crud-v3.js"];

    protected function component(): string
    {
        // Cargar productos desde la base de datos
        $products = Product::orderBy('created_at', 'desc')->get()->toArray();

        // Definición de columnas con anchos porcentuales
        // Sistema: las columnas sin width se calculan automáticamente para sumar 100%
        $columns = new ColumnCollection(
            new ColumnDto(
                field: "id",
                headerName: "ID",
                width: DimensionValue::percent(8),  // 8%
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            new ColumnDto(
                field: "name",
                headerName: "Nombre",
                width: DimensionValue::percent(20),  // 20%
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "description",
                headerName: "Descripción",
                width: DimensionValue::percent(30),  // 30% - la más grande
                sortable: false,
                filter: false
            ),
            new ColumnDto(
                field: "price",
                headerName: "Precio",
                width: DimensionValue::percent(12),  // 12%
                sortable: true,
                filter: true,
                filterType: "number",
                valueFormatter: "params => '$' + parseFloat(params.value).toFixed(2)"
            ),
            new ColumnDto(
                field: "stock",
                headerName: "Stock",
                width: DimensionValue::percent(10),  // 10%
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            new ColumnDto(
                field: "actions",
                headerName: "Acciones",
                width: DimensionValue::percent(20),  // 20%
                sortable: false,
                filter: false,
                // NOTA: Usando .static() porque AG-Grid no soporta cellRenderers async
                // Para usar .dynamic() necesitamos pre-renderizar en PHP o usar wrapper
                cellRenderer: ActionButtons::static(['edit', 'delete'])
            )
            // Total: 8 + 20 + 30 + 12 + 10 + 20 = 100%
        );

        // Crear tabla con ColumnCollection (single source of truth)
        $table = new TableComponent(
            id: "products-table-v3",
            columns: $columns,
            rowData: $products,
            pagination: true,
            paginationPageSize: 20,
            rowSelection: "multiple"
        );

        return <<<HTML
        <div class="products-crud-v3">
            <!-- Header con botón crear -->
            <div class="products-crud-v3__header">
                <h1 class="products-crud-v3__title">Productos</h1>
                <button
                    class="products-crud-v3__create-btn"
                    id="products-crud-v3-create-btn"
                    type="button"
                    onclick="openCreateModule()"
                >
                    <svg class="products-crud-v3__create-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Crear Producto
                </button>
            </div>

            <!-- Tabla de productos -->
            <div class="products-crud-v3__table">
                {$table->render()}
            </div>
        </div>
        HTML;
    }
}
