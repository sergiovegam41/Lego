<?php
namespace Components\App\ExampleCrud;

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
use App\Models\ExampleCrud;

/**
 * ExampleCrudComponent - Vista de tabla (CRUD de Ejemplo)
 *
 * FILOSOFÍA LEGO:
 * Componente de ejemplo/template que demuestra CRUD completo.
 * Implementa ScreenInterface para definir su identidad en el menú.
 * Envuelto en ScreenComponent para consistencia de ventanas.
 *
 * SCREEN PATTERN:
 * - SCREEN_ID: Identificador único usado por el menú y window manager
 * - SCREEN_LABEL: Texto mostrado en el menú
 * - SCREEN_ICON: Icono ionicon
 * - SCREEN_ROUTE: Ruta del componente
 * - SCREEN_PARENT: ID del screen padre (null = raíz)
 *
 * CARACTERÍSTICAS:
 * ✅ Model-driven con server-side pagination automática
 * ✅ RowActions integradas (edit, delete con callbacks)
 * ✅ Screen wrapper para funcionalidad consistente
 * ✅ Navegación usando módulos (no window.location.href)
 * ✅ Separación clara: 1 componente = 1 responsabilidad
 */
#[ApiComponent('/example-crud', methods: ['GET'])]
class ExampleCrudComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY - Fuente de verdad para el menú
    // ═══════════════════════════════════════════════════════════════════
    
    // El grupo del menú (carpeta)
    public const MENU_GROUP_ID = 'example-crud';
    
    // Esta screen es la LISTA (Ver)
    public const SCREEN_ID = 'example-crud-list';
    public const SCREEN_LABEL = 'Ver';
    public const SCREEN_ICON = 'list-outline';
    public const SCREEN_ROUTE = '/component/example-crud';
    public const SCREEN_PARENT = self::MENU_GROUP_ID; // Hijo del grupo
    public const SCREEN_ORDER = 0;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",  // Screen wrapper
        "./example-crud.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",   // Screen manager
        "./example-crud.js"
    ];

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
                callback: "handleEditRecord",
                variant: "primary",
                tooltip: "Editar registro"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteRecord",
                variant: "danger",
                confirm: false,
                tooltip: "Eliminar registro"
            )
        );

        // Tabla model-driven con server-side pagination
        $table = new TableComponent(
            id: "example-crud-table",
            model: ExampleCrud::class,
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
                <div class="example-crud">
                    <!-- Header con botón crear -->
                    <div class="example-crud__header">
                        <h1 class="example-crud__title">{$this->getScreenLabel()}</h1>
                        <button
                            class="example-crud__create-btn"
                            id="example-crud-create-btn"
                            type="button"
                            onclick="openCreateModule()"
                        >
                            <svg class="example-crud__create-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Crear Registro
                        </button>
                    </div>

                    <!-- Tabla de registros -->
                    <div class="example-crud__table">
                        {$table->render()}
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
    
    /**
     * Helper para obtener el label (usado en el template)
     */
    private function getScreenLabel(): string
    {
        return self::SCREEN_LABEL;
    }
}
