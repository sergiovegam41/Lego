<?php
namespace Components\App\{{COMPONENT_NAME}};

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Core\Types\DimensionValue;
use App\Models\{{MODEL_NAME}};

#[ApiComponent('/{{COMPONENT_PATH}}', methods: ['GET'])]
class {{COMPONENT_NAME}}Component extends CoreComponent
{
    protected $CSS_PATHS = ["./{{COMPONENT_KEBAB}}.css"];
    protected $JS_PATHS = ["./{{COMPONENT_KEBAB}}.js"];

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
            {{COLUMNS}}
        );

        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEdit{{MODEL_NAME}}",
                variant: "primary",
                tooltip: "Editar {{MODEL_NAME_LOWER}}"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDelete{{MODEL_NAME}}",
                variant: "danger",
                confirm: false,
                tooltip: "Eliminar {{MODEL_NAME_LOWER}}"
            )
        );

        $table = new TableComponent(
            id: "{{TABLE_ID}}",
            model: {{MODEL_NAME}}::class,
            columns: $columns,
            rowActions: $actions,
            height: "600px",
            pagination: true,
            rowSelection: "multiple",
            noRowsMessage: "No hay {{MODEL_NAME_LOWER_PLURAL}} registrados"
        );

        return <<<HTML
        <div class="{{COMPONENT_KEBAB}}">
            <div class="{{COMPONENT_KEBAB}}__header">
                <h1 class="{{COMPONENT_KEBAB}}__title">{{TITLE}}</h1>
                <button
                    class="{{COMPONENT_KEBAB}}__create-btn"
                    type="button"
                    onclick="openCreate{{MODEL_NAME}}Module()"
                >
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Crear {{MODEL_NAME}}</span>
                </button>
            </div>
            <div class="{{COMPONENT_KEBAB}}__table">
                {$table->render()}
            </div>
        </div>
        HTML;
    }
}
