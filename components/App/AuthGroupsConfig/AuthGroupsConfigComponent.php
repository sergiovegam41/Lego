<?php
namespace Components\App\AuthGroupsConfig;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Components\Shared\Essentials\TableComponent\Renderers\BooleanRenderer;
use Core\Types\DimensionValue;
use App\Models\AuthGroup;
use Components\App\RolesConfig\RolesConfigComponent;

/**
 * AuthGroupsConfigComponent - Lista de grupos de autenticación
 *
 * SCREEN PATTERN:
 * - Implementa ScreenInterface para definir su identidad
 * - parent_id se obtiene proceduralmente desde la BD
 * - Visible en el menú como hijo de "Gestión de Roles"
 */
#[ApiComponent('/auth-groups-config', methods: ['GET'])]
class AuthGroupsConfigComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    // Esta screen es la LISTA (Ver)
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ID = 'auth-groups-config-list';
    public const SCREEN_LABEL = 'Ver';
    public const SCREEN_ICON = 'list-outline';
    public const SCREEN_ROUTE = '/component/auth-groups-config';
    public const SCREEN_ORDER = 0;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./auth-groups-config.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./auth-groups-config.js"
    ];

    protected function component(): string
    {
        $columns = new ColumnCollection(
            new ColumnDto(field: "id", headerName: "ID", width: DimensionValue::px(150), sortable: true, filter: true),
            new ColumnDto(field: "name", headerName: "Nombre", width: DimensionValue::flex(1), sortable: true, filter: true),
            new ColumnDto(field: "description", headerName: "Descripción", width: DimensionValue::flex(2), filter: true),
            new ColumnDto(field: "is_active", headerName: "Activo", width: DimensionValue::px(100), sortable: true, filter: true, cellRenderer: BooleanRenderer::create()),
            new ColumnDto(field: "created_at", headerName: "Creado", width: DimensionValue::px(150), sortable: true, filter: true, filterType: "date", valueFormatter: "date")
        );

        $actions = new RowActionsCollection(
            new RowActionDto(id: "edit", label: "Editar", icon: "create-outline", callback: "handleEditAuthGroup", variant: "primary", tooltip: "Editar grupo"),
            new RowActionDto(id: "delete", label: "Eliminar", icon: "trash-outline", callback: "handleDeleteAuthGroup", variant: "danger", confirm: true, tooltip: "Eliminar grupo")
        );

        $table = new TableComponent(
            id: "auth-groups-config-table",
            model: AuthGroup::class,
            columns: $columns,
            rowActions: $actions,
            height: "600px",
            pagination: true,
            rowSelection: "multiple"
        );

        $screenId = self::SCREEN_ID;
        $screenLabel = self::SCREEN_LABEL;

        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <div class="auth-groups-config">
                    <!-- Header con botón crear -->
                    <div class="auth-groups-config__header">
                        <h1 class="auth-groups-config__title">{$screenLabel}</h1>
                        <button
                            class="auth-groups-config__create-btn"
                            id="auth-groups-config-create-btn"
                            type="button"
                            onclick="openCreateModule()"
                        >
                            <ion-icon name="add-circle-outline"></ion-icon>
                            Crear Grupo
                        </button>
                    </div>

                    <!-- Tabla de grupos -->
                    <div class="auth-groups-config__table">
                        {$table->render()}
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}

