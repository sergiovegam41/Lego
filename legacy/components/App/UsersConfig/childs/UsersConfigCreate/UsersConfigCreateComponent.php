<?php
namespace Components\App\UsersConfig\Childs\UsersConfigCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\App\UsersConfig\UsersConfigComponent;
use App\Models\AuthGroup;
use App\Models\Role;

/**
 * UsersConfigCreateComponent - Formulario de creación de usuarios
 *
 * SCREEN PATTERN:
 * - Implementa ScreenInterface para definir su identidad
 * - parent_id se obtiene proceduralmente desde la BD
 * - Visible en el menú como hijo de UsersConfig
 */
#[ApiComponent('/users-config/create', methods: ['GET'])]
class UsersConfigCreateComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'users-config-create';
    public const SCREEN_LABEL = 'Crear Usuario';
    public const SCREEN_ICON = 'add-circle-outline';
    public const SCREEN_ROUTE = '/component/users-config/create';
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ORDER = 10;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./example-form.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./users-config-create.js"
    ];

    protected function component(): string
    {
        // Obtener grupos de autenticación disponibles
        $authGroups = AuthGroup::active()->orderBy('id')->get();
        $groupOptions = [];
        foreach ($authGroups as $group) {
            $groupOptions[] = [
                "value" => $group->id,
                "label" => $group->id . " - " . $group->name
            ];
        }
        // Agregar opción "Otro" para crear nuevo grupo
        $groupOptions[] = [
            "value" => "__OTHER__",
            "label" => "Otro (crear nuevo grupo)"
        ];
        $groupsJson = json_encode($groupOptions);

        // Obtener roles disponibles agrupados por grupo
        $roles = Role::active()->get();
        $rolesByGroup = $roles->groupBy('auth_group_id')->map(function($group) {
            return $group->pluck('role_id', 'role_id')->toArray();
        })->toArray();
        $rolesByGroupJson = json_encode($rolesByGroup);

        $nameInput = new InputTextComponent(
            id: "user-name",
            label: "Nombre",
            placeholder: "Nombre completo",
            required: true
        );

        $emailInput = new InputTextComponent(
            id: "user-email",
            label: "Email",
            type: "email",
            placeholder: "usuario@ejemplo.com",
            required: true
        );

        $passwordInput = new InputTextComponent(
            id: "user-password",
            label: "Contraseña",
            type: "password",
            placeholder: "Mínimo 8 caracteres",
            required: true
        );

        $authGroupSelect = new SelectComponent(
            id: "user-auth-group-id",
            label: "Grupo de Autenticación",
            options: $groupOptions,
            required: true,
            searchable: true
        );

        $statusSelect = new SelectComponent(
            id: "user-status",
            label: "Estado",
            options: [
                ["value" => "active", "label" => "Activo"],
                ["value" => "inactive", "label" => "Inactivo"],
                ["value" => "suspended", "label" => "Suspendido"]
            ],
            required: false
        );

        $screenId = self::SCREEN_ID;
        $screenLabel = self::SCREEN_LABEL;

        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <div class="example-form">
                    <!-- Header -->
                    <div class="example-form__header">
                        <h1 class="example-form__title">{$screenLabel}</h1>
                    </div>

                    <!-- Formulario -->
                    <form class="example-form__form" id="user-create-form" onsubmit="return false;">
                        <div class="example-form__grid">
                            <!-- Nombre -->
                            <div class="example-form__field example-form__field--full">
                                {$nameInput->render()}
                            </div>

                            <!-- Email -->
                            <div class="example-form__field example-form__field--full">
                                {$emailInput->render()}
                            </div>

                            <!-- Contraseña -->
                            <div class="example-form__field example-form__field--full">
                                {$passwordInput->render()}
                            </div>

                            <!-- Grupo de Autenticación -->
                            <div class="example-form__field example-form__field--full">
                                {$authGroupSelect->render()}
                            </div>

                            <!-- Input para nuevo grupo (oculto inicialmente) -->
                            <div class="example-form__field example-form__field--full" id="new-group-container" style="display: none;">
                                <label for="new-group-id" class="example-form__label">
                                    ID del Nuevo Grupo <span class="example-form__required">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="new-group-id"
                                    class="example-form__input"
                                    placeholder="Ej: CUSTOMERS, PARTNERS, etc."
                                >
                                <small class="example-form__help">Ingresa el ID del nuevo grupo de autenticación</small>
                            </div>

                            <div class="example-form__field example-form__field--full" id="new-group-name-container" style="display: none;">
                                <label for="new-group-name" class="example-form__label">
                                    Nombre del Nuevo Grupo <span class="example-form__required">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="new-group-name"
                                    class="example-form__input"
                                    placeholder="Ej: Clientes, Socios, etc."
                                >
                            </div>

                            <!-- Rol (se llenará dinámicamente según el grupo seleccionado) -->
                            <div class="example-form__field example-form__field--full">
                                <label for="user-role-id" class="example-form__label">
                                    Rol <span class="example-form__required">*</span>
                                </label>
                                <select id="user-role-id" class="example-form__input" required>
                                    <option value="">Primero selecciona un grupo</option>
                                </select>
                            </div>

                            <!-- Estado -->
                            <div class="example-form__field example-form__field--full">
                                {$statusSelect->render()}
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="example-form__actions">
                            <button
                                type="button"
                                class="example-form__button example-form__button--secondary"
                                id="user-form-cancel-btn"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="example-form__button example-form__button--primary"
                                id="user-form-submit-btn"
                            >
                                Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            window.USERS_CONFIG_AUTH_GROUPS = {$groupsJson};
            window.USERS_CONFIG_ROLES = {$rolesByGroupJson};
        </script>
        HTML;
    }
}

