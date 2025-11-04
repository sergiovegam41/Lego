<?php
namespace Components\App\ProductsCrudV3;

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
 * ProductsCrudV3Component - Vista de tabla (CRUD V3)
 *
 * FILOSOFÍA LEGO:
 * Componente enfocado ÚNICAMENTE en mostrar la tabla de productos.
 * No contiene lógica de formularios - separación de responsabilidades.
 *
 * MEJORAS vs V1/V2:
 * ✅ Model-driven con server-side pagination automática
 * ✅ RowActions integradas (edit, delete con callbacks)
 * ✅ Anchos con DimensionValue (proporciones consistentes)
 * ✅ Sin duplicación de definiciones
 * ✅ Navegación usando módulos (no window.location.href)
 * ✅ Theming correcto (html.dark, no @media)
 * ✅ Separación clara: 1 componente = 1 responsabilidad
 *
 * NUEVO EN V3 (Model-Driven):
 * - Pasa Product::class y todo se configura automáticamente
 * - Server-side pagination desde /api/get/products
 * - RowActions con callbacks personalizados (handleEdit, handleDelete)
 * - Sin necesidad de cargar productos en PHP (lazy loading)
 */
#[ApiComponent('/products-crud-v3', methods: ['GET'])]
class ProductsCrudV3Component extends CoreComponent
{
    protected $CSS_PATHS = ["./products-crud-v3.css"];
    protected $JS_PATHS = ["./products-crud-v3.js"];

    protected function component(): string
    {
        // Definición de columnas
        $columns = new ColumnCollection(
            new ColumnDto(
                field: "id",
                headerName: "ID",
                width: DimensionValue::px(100),
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            new ColumnDto(
                field: "name",
                headerName: "Nombre",
                width: DimensionValue::px(200),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "description",
                headerName: "Descripción",
                width: DimensionValue::px(250),
                sortable: false,
                filter: false
            ),
            new ColumnDto(
                field: "price",
                headerName: "Precio",
                width: DimensionValue::px(120),
                sortable: true,
                filter: true,
                filterType: "number",
                valueFormatter: "params => '$' + parseFloat(params.value).toFixed(2)"
            ),
            new ColumnDto(
                field: "stock",
                headerName: "Stock",
                width: DimensionValue::px(100),
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            new ColumnDto(
                field: "category",
                headerName: "Categoría",
                width: DimensionValue::px(999),
                sortable: true,
                filter: true
            )
        );

        // Definir acciones de fila con callbacks personalizados
        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditProduct",
                variant: "primary",
                tooltip: "Editar producto"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteProduct",
                variant: "danger",
                confirm: true,
                confirmMessage: "¿Estás seguro de eliminar este producto?",
                tooltip: "Eliminar producto"
            )
        );

        // ✨ MAGIA: Tabla model-driven con server-side pagination
        $table = new TableComponent(
            id: "products-table-v3",
            model: Product::class,  // ← Auto-configura API y paginación
            columns: $columns,
            rowActions: $actions,   // ← Acciones integradas
            height: "600px",
            pagination: true,       // Server-side automático
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
                    onclick="openModule('products-crud-v3-create', '/component/products-crud-v3/create', 'Crear Producto', null)"
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
