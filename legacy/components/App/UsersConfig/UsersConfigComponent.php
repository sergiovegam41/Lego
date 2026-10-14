<?php
namespace Components\App\UsersConfig;

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
use App\Models\User;
use App\Models\Role;

/**
 * UsersConfigComponent - Gestión de Usuarios
 *
 * FILOSOFÍA LEGO:
 * Componente para gestionar usuarios del sistema.
 * Permite crear, editar y eliminar usuarios.
 */
#[ApiComponent('/users-config', methods: ['GET'])]
class UsersConfigComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    // Esta screen es la LISTA (Ver)
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ID = 'users-config-list';
    public const SCREEN_LABEL = 'Ver';
    public const SCREEN_ICON = 'list-outline';
    public const SCREEN_ROUTE = '/component/users-config';
    public const SCREEN_ORDER = 0;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./users-config.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./users-config.js"
    ];

    protected function component(): string
    {
        // Obtener roles disponibles para el select
        $roles = Role::active()->get();
        $rolesByGroup = $roles->groupBy('auth_group_id')->map(function($group) {
            return $group->pluck('role_id', 'role_id')->toArray();
        })->toArray();
        $rolesJson = json_encode($rolesByGroup);

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
                width: DimensionValue::px(200),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "email",
                headerName: "Email",
                width: DimensionValue::px(250),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "auth_group_id",
                headerName: "Grupo",
                width: DimensionValue::px(150),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "role_id",
                headerName: "Rol",
                width: DimensionValue::px(150),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "status",
                headerName: "Estado",
                width: DimensionValue::px(120),
                sortable: true,
                filter: true,
                filterType: "text"
            )
        );

        // Definir acciones de fila
        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditUser",
                variant: "primary",
                tooltip: "Editar usuario"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteUser",
                variant: "danger",
                confirm: true,
                tooltip: "Eliminar usuario"
            )
        );

        // Tabla model-driven con server-side pagination
        $table = new TableComponent(
            id: "users-config-table",
            model: User::class,
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
                <div class="users-config">
                    <!-- Header con botón crear -->
                    <div class="users-config__header">
                        <h1 class="users-config__title">{$screenLabel}</h1>
                        <button
                            class="users-config__create-btn"
                            id="users-config-create-btn"
                            type="button"
                            onclick="openCreateModule()"
                        >
                            <ion-icon name="add-circle-outline"></ion-icon>
                            Crear Usuario
                        </button>
                    </div>

                    <!-- Tabla de usuarios -->
                    <div class="users-config__table">
                        {$table->render()}
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}

