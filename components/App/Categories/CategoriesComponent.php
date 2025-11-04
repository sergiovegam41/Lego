<?php
namespace Components\App\Categories;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Core\Types\DimensionValue;
use App\Models\Category;

#[ApiComponent('/categories', methods: ['GET'])]
class CategoriesComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./categories.css"];
    protected $JS_PATHS = ["./categories.js"];

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
                field: "description",
                headerName: "Descripción",
                width: DimensionValue::px(999),
                sortable: false,
                filter: false
            )
        );

        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditCategory",
                variant: "primary",
                tooltip: "Editar categoría"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteCategory",
                variant: "danger",
                confirm: false,
                tooltip: "Eliminar categoría"
            )
        );

        $table = new TableComponent(
            id: "categories-table",
            model: Category::class,
            columns: $columns,
            rowActions: $actions,
            height: "600px",
            pagination: true,
            rowSelection: "multiple",
            noRowsMessage: "No hay categorías registradas"
        );

        return <<<HTML
        <div class="categories">
            <div class="categories__header">
                <h1 class="categories__title">Categorías de Flores</h1>
                <button
                    class="categories__create-btn"
                    type="button"
                    onclick="openCreateCategoryModule()"
                >
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Crear Categoría</span>
                </button>
            </div>
            <div class="categories__table">
                {$table->render()}
            </div>
        </div>
        HTML;
    }
}
