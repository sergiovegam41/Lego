<?php
namespace Components\App\RolesConfig;

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
use App\Models\Role;

/**
 * RolesConfigComponent - Gestión de Roles (Catálogo)
 *
 * FILOSOFÍA LEGO:
 * Componente para gestionar el catálogo de roles disponibles.
 * Permite crear, editar y eliminar roles sin necesidad de tener usuarios con esos roles.
 */
#[ApiComponent('/roles-config', methods: ['GET'])]
class RolesConfigComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    // Esta screen es la LISTA (Ver)
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ID = 'roles-config-list';
    public const SCREEN_LABEL = 'Ver';
    public const SCREEN_ICON = 'list-outline';
    public const SCREEN_ROUTE = '/component/roles-config';
    public const SCREEN_ORDER = 0;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./roles-config.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./roles-config.js"
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
                field: "auth_group_id",
                headerName: "Grupo de Autenticación",
                width: DimensionValue::px(200),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "role_id",
                headerName: "ID del Rol",
                width: DimensionValue::px(180),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "role_name",
                headerName: "Nombre del Rol",
                width: DimensionValue::px(200),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "description",
                headerName: "Descripción",
                width: DimensionValue::flex(1),
                sortable: false,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "is_active",
                headerName: "Activo",
                width: DimensionValue::px(100),
                sortable: true,
                filter: true,
                filterType: "boolean",
                cellRenderer: BooleanRenderer::create()
            )
        );

        // Definir acciones de fila
        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditRole",
                variant: "primary",
                tooltip: "Editar rol"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteRole",
                variant: "danger",
                confirm: true,
                tooltip: "Eliminar rol"
            )
        );

        // Tabla model-driven con server-side pagination
        $table = new TableComponent(
            id: "roles-config-table",
            model: Role::class,
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
                <div class="roles-config">
                    <!-- Header con botón crear -->
                    <div class="roles-config__header">
                        <h1 class="roles-config__title">{$screenLabel}</h1>
                        <button
                            class="roles-config__create-btn"
                            id="roles-config-create-btn"
                            type="button"
                            onclick="openCreateModule()"
                        >
                            <ion-icon name="add-circle-outline"></ion-icon>
                            Crear Rol
                        </button>
                    </div>

                    <!-- Tabla de roles -->
                    <div class="roles-config__table">
                        {$table->render()}
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}

