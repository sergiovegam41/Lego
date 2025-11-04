<?php
namespace Components\App\Flowers;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Core\Types\DimensionValue;
use App\Models\Flower;

#[ApiComponent('/flowers', methods: ['GET'])]
class FlowersComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./flowers.css"];
    protected $JS_PATHS = ["./flowers.js"];

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
                field: "primary_image",
                headerName: "Imagen",
                width: DimensionValue::px(100),
                sortable: false,
                filter: false,
                cellRenderer: 'params => {
                    if (!params.value) return \'<span style="color:#999;font-size:12px;">Sin imagen</span>\';
                    const allImages = params.data.all_images || [];
                    const imagesJson = JSON.stringify(allImages).replace(/"/g, "&quot;");
                    const clickHandler = allImages.length > 0 ? `onclick="window.openImageCarousel(${imagesJson}, \'${params.data.name}\')"` : "";
                    const cursor = allImages.length > 0 ? "cursor:pointer;" : "";
                    return `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;overflow:hidden;${cursor}" ${clickHandler}><img src="${params.value}" style="max-width:60px;max-height:60px;width:auto;height:auto;object-fit:contain;border-radius:8px;" alt="" /></div>`;
                }'
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
                field: "category_name",
                headerName: "Categoría",
                width: DimensionValue::px(150),
                sortable: true,
                filter: true,
                filterType: "text"
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
                field: "is_active",
                headerName: "Estado",
                width: DimensionValue::px(100),
                sortable: true,
                filter: true,
                cellRenderer: 'params => params.value ? \'<span style="color: #10b981; font-weight: 500;">Activo</span>\' : \'<span style="color: #ef4444; font-weight: 500;">Inactivo</span>\''
            )
        );

        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditFlower",
                variant: "primary",
                tooltip: "Editar flor"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteFlower",
                variant: "danger",
                confirm: false,
                tooltip: "Eliminar flor"
            )
        );

        $table = new TableComponent(
            id: "flowers-table",
            model: Flower::class,
            columns: $columns,
            rowActions: $actions,
            height: "600px",
            pagination: true,
            rowSelection: "multiple"
        );

        return <<<HTML
        <div class="flowers">
            <div class="flowers__header">
                <h1 class="flowers__title">Catálogo de Flores</h1>
                <button
                    class="flowers__create-btn"
                    type="button"
                    onclick="openCreateFlowerModule()"
                >
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Crear Flor</span>
                </button>
            </div>
            <div class="flowers__table">
                {$table->render()}
            </div>
        </div>
        HTML;
    }
}
