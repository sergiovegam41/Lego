<?php
namespace Components\App\RolesConfig\Childs\RolesConfigCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\App\RolesConfig\RolesConfigComponent;
use App\Models\AuthGroup;
use App\Models\Role;

/**
 * RolesConfigCreateComponent - Formulario de creación de roles
 *
 * SCREEN PATTERN:
 * - Implementa ScreenInterface para definir su identidad
 * - parent_id se obtiene proceduralmente desde la BD
 * - Visible en el menú como hijo de RolesConfig
 */
#[ApiComponent('/roles-config/create', methods: ['GET'])]
class RolesConfigCreateComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'roles-config-create';
    public const SCREEN_LABEL = 'Crear Rol';
    public const SCREEN_ICON = 'add-circle-outline';
    public const SCREEN_ROUTE = '/component/roles-config/create';
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
        "./roles-config-create.js"
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

        $authGroupSelect = new SelectComponent(
            id: "role-auth-group-id",
            label: "Grupo de Autenticación",
            options: $groupOptions,
            required: true,
            searchable: true
        );

        $roleIdInput = new InputTextComponent(
            id: "role-role-id",
            label: "ID del Rol",
            placeholder: "Ej: SUPERADMIN, ADMIN, CLIENTE1, etc.",
            required: true
        );

        $roleNameInput = new InputTextComponent(
            id: "role-name",
            label: "Nombre del Rol",
            placeholder: "Ej: Super Administrador"
        );

        $descriptionTextarea = new TextAreaComponent(
            id: "role-description",
            label: "Descripción",
            placeholder: "Descripción del rol...",
            rows: 3
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
                    <form class="example-form__form" id="role-create-form" onsubmit="return false;">
                        <div class="example-form__grid">
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
                                    Nombre del Nuevo Grupo (opcional)
                                </label>
                                <input
                                    type="text"
                                    id="new-group-name"
                                    class="example-form__input"
                                    placeholder="Ej: Clientes, Socios, etc. (se usará el ID si no se proporciona)"
                                >
                                <small class="example-form__help">Si no se proporciona, se usará el ID como nombre</small>
                            </div>

                            <!-- ID del Rol -->
                            <div class="example-form__field example-form__field--full">
                                {$roleIdInput->render()}
                                <small class="example-form__help" style="margin-top: 4px; display: block; font-size: 12px; color: var(--text-secondary);">
                                    El ID se normalizará automáticamente (mayúsculas, sin acentos, sin caracteres especiales)
                                </small>
                            </div>

                            <!-- Nombre del Rol -->
                            <div class="example-form__field example-form__field--full">
                                {$roleNameInput->render()}
                            </div>

                            <!-- Descripción -->
                            <div class="example-form__field example-form__field--full">
                                {$descriptionTextarea->render()}
                            </div>

                            <!-- Activo -->
                            <div class="example-form__field example-form__field--full">
                                <label class="example-form__checkbox">
                                    <input type="checkbox" id="role-is-active" checked>
                                    <span>Rol activo</span>
                                </label>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="example-form__actions">
                            <button
                                type="button"
                                class="example-form__button example-form__button--secondary"
                                id="role-form-cancel-btn"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="example-form__button example-form__button--primary"
                                id="role-form-submit-btn"
                            >
                                Crear Rol
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            window.ROLES_CONFIG_AUTH_GROUPS = {$groupsJson};
        </script>
        HTML;
    }
}

