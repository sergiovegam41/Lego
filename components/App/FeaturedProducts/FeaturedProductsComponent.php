<?php
namespace Components\App\FeaturedProducts;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Core\Types\DimensionValue;
use App\Models\FeaturedProduct;

#[ApiComponent('/featured-products', methods: ['GET'])]
class FeaturedProductsComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./featured-products.css"];
    protected $JS_PATHS = ["./featured-products.js"];

    protected function component(): string
    {
        $columns = new ColumnCollection(
            new ColumnDto(
                field: "id",
                headerName: "ID",
                width: DimensionValue::px(80),
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            new ColumnDto(
                field: "product_name",
                headerName: "Producto",
                width: DimensionValue::px(200),
                sortable: false,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "tag",
                headerName: "Tag",
                width: DimensionValue::px(150),
                sortable: true,
                filter: true,
                filterType: "text",
                cellRenderer: 'params => {
                    const tagMap = {
                        "most-popular": "Más Popular",
                        "best-seller": "Más Vendido",
                        "free-shipping": "Envío Gratis",
                        "new-arrival": "Nuevo",
                        "limited-edition": "Edición Limitada",
                        "discount": "En Descuento",
                        "featured": "Destacado",
                        "seasonal": "Temporada"
                    };
                    const tagLabel = tagMap[params.value] || params.value;
                    const colors = {
                        "most-popular": "#f59e0b",
                        "best-seller": "#10b981",
                        "free-shipping": "#3b82f6",
                        "new-arrival": "#8b5cf6",
                        "limited-edition": "#ec4899",
                        "discount": "#ef4444",
                        "featured": "#6366f1",
                        "seasonal": "#14b8a6"
                    };
                    const color = colors[params.value] || "#6b7280";
                    return `<span style="background: ${color}20; color: ${color}; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">${tagLabel}</span>`;
                }'
            ),
            new ColumnDto(
                field: "description",
                headerName: "Descripción",
                width: DimensionValue::flex(1),
                sortable: false,
                filter: true,
                filterType: "text",
                cellRenderer: 'params => {
                    const desc = params.value || "";
                    return `<div style="white-space: normal; line-height: 1.4;">${desc}</div>`;
                }'
            ),
            new ColumnDto(
                field: "sort_order",
                headerName: "Orden",
                width: DimensionValue::px(100),
                sortable: true,
                filter: true,
                filterType: "number",
                cellRenderer: 'params => `<div style="text-align: center; font-weight: 500;">${params.value || 0}</div>`'
            ),
            new ColumnDto(
                field: "is_active",
                headerName: "Estado",
                width: DimensionValue::px(120),
                sortable: true,
                filter: true,
                cellRenderer: 'params => params.value ? \'<span style="color: #10b981; font-weight: 500;">Activo</span>\' : \'<span style="color: #ef4444; font-weight: 500;">Inactivo</span>\''
            ),
            new ColumnDto(
                field: "created_at",
                headerName: "Fecha",
                width: DimensionValue::px(120),
                sortable: true,
                filter: true,
                filterType: "date",
                valueFormatter: 'params => {
                    if (!params.value) return "";
                    const date = new Date(params.value);
                    return date.toLocaleDateString("es-ES", {day: "2-digit", month: "2-digit", year: "numeric"});
                }'
            )
        );

        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditFeaturedProduct",
                variant: "primary",
                tooltip: "Editar producto destacado"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteFeaturedProduct",
                variant: "danger",
                confirm: false,
                tooltip: "Eliminar producto destacado"
            )
        );

        $table = new TableComponent(
            id: "featured-products-table",
            model: FeaturedProduct::class,
            columns: $columns,
            rowActions: $actions,
            height: "600px",
            pagination: true,
            rowSelection: "multiple",
            noRowsMessage: "No hay productos destacados registrados"
        );

        return <<<HTML
        <div class="featured-products">
            <div class="featured-products__header">
                <h1 class="featured-products__title">Productos Destacados</h1>
                <button
                    class="featured-products__create-btn"
                    type="button"
                    onclick="openCreateFeaturedProductModule()"
                >
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Agregar Producto Destacado</span>
                </button>
            </div>
            <div class="featured-products__table">
                {$table->render()}
            </div>
        </div>
        HTML;
    }
}
