<?php
namespace Components\App\ToolsCrud;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Core\Types\DimensionValue;
use App\Models\Tool;

/**
 * ToolsCrudComponent - Vista de tabla (CRUD de Herramientas)
 *
 * FILOSOFÍA LEGO:
 * Componente para gestión de herramientas con características e imágenes.
 * Implementa ScreenInterface para definir su identidad en el menú.
 *
 * CARACTERÍSTICAS:
 * ✅ Model-driven con server-side pagination automática
 * ✅ RowActions integradas (edit, delete con callbacks)
 * ✅ Screen wrapper para funcionalidad consistente
 * ✅ Navegación usando módulos (no window.location.href)
 */
#[ApiComponent('/tools-crud', methods: ['GET'])]
class ToolsCrudComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY - Fuente de verdad para el menú
    // ═══════════════════════════════════════════════════════════════════
    
    // El grupo del menú (carpeta)
    public const MENU_GROUP_ID = 'tools-crud';
    
    // Esta screen es la LISTA (Ver)
    public const SCREEN_ID = 'tools-crud-list';
    public const SCREEN_LABEL = 'Ver';
    public const SCREEN_ICON = 'list-outline';
    public const SCREEN_ROUTE = '/component/tools-crud';
    public const SCREEN_PARENT = self::MENU_GROUP_ID;
    public const SCREEN_ORDER = 0;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./tools-crud.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./tools-crud.js"
    ];

    protected function component(): string
    {
        // Definición de columnas
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
                field: "name",
                headerName: "Nombre",
                width: DimensionValue::px(250),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "description",
                headerName: "Descripción",
                width: DimensionValue::px(350),
                sortable: false,
                filter: false
            ),
            new ColumnDto(
                field: "features_count",
                headerName: "Características",
                width: DimensionValue::px(130),
                sortable: true,
                filter: true,
                filterType: "number",
                valueFormatter: "params => params.value + ' items'"
            ),
            new ColumnDto(
                field: "is_active",
                headerName: "Estado",
                width: DimensionValue::px(999),
                sortable: true,
                filter: true,
                valueFormatter: "params => params.value ? 'Activo' : 'Inactivo'"
            )
        );

        // Definir acciones de fila
        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditTool",
                variant: "primary",
                tooltip: "Editar herramienta"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteTool",
                variant: "danger",
                confirm: false,
                tooltip: "Eliminar herramienta"
            )
        );

        // Tabla model-driven con server-side pagination
        $table = new TableComponent(
            id: "tools-crud-table",
            model: Tool::class,
            columns: $columns,
            rowActions: $actions,
            height: "600px",
            pagination: true,
            rowSelection: "multiple"
        );

        $screenId = self::SCREEN_ID;

        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <div class="tools-crud">
                    <!-- Header con botón crear -->
                    <div class="tools-crud__header">
                        <h1 class="tools-crud__title">{$this->getScreenLabel()}</h1>
                        <button
                            class="tools-crud__create-btn"
                            id="tools-crud-create-btn"
                            type="button"
                            onclick="openToolsCreateModule()"
                        >
                            <svg class="tools-crud__create-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Nueva Herramienta
                        </button>
                    </div>

                    <!-- Tabla de registros -->
                    <div class="tools-crud__table">
                        {$table->render()}
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
    
    private function getScreenLabel(): string
    {
        return self::SCREEN_LABEL;
    }
}

