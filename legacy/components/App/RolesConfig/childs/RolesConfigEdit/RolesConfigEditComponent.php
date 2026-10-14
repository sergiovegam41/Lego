<?php
namespace Components\App\RolesConfig\Childs\RolesConfigEdit;

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
 * RolesConfigEditComponent - Formulario de edición de roles
 *
 * SCREEN PATTERN:
 * - Implementa ScreenInterface para definir su identidad
 * - parent_id se obtiene proceduralmente desde la BD
 * - Visible en el menú como hijo de RolesConfig
 */
#[ApiComponent('/roles-config/edit', methods: ['GET'])]
class RolesConfigEditComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'roles-config-edit';
    public const SCREEN_LABEL = 'Editar Rol';
    public const SCREEN_ICON = 'create-outline';
    public const SCREEN_ROUTE = '/component/roles-config/edit';
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ORDER = 15;
    public const SCREEN_VISIBLE = false; // Oculto: no aparece en el menú por defecto
    public const SCREEN_DYNAMIC = true; // Dinámico: solo aparece cuando se abre desde la tabla
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./example-form.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./roles-config-edit.js"
    ];

    protected function component(): string
    {
        // Obtener el ID del rol desde los parámetros
        $roleId = $_GET['id'] ?? $_REQUEST['id'] ?? null;

        // Obtener grupos de autenticación disponibles (para mostrar el grupo actual)
        $authGroups = AuthGroup::active()->orderBy('id')->get();
        $groupOptions = [];
        foreach ($authGroups as $group) {
            $groupOptions[] = [
                "value" => $group->id,
                "label" => $group->id . " - " . $group->name
            ];
        }
        $groupsJson = json_encode($groupOptions);

        $authGroupSelect = new SelectComponent(
            id: "role-auth-group-id",
            label: "Grupo de Autenticación",
            options: $groupOptions,
            required: true,
            searchable: true,
            disabled: true // El grupo no se puede cambiar después de crear
        );

        $roleIdInput = new InputTextComponent(
            id: "role-role-id",
            label: "ID del Rol",
            placeholder: "Ej: SUPERADMIN, ADMIN, CLIENTE1, etc.",
            required: true,
            disabled: true // El ID no se puede cambiar después de crear
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

        // Si no hay ID, mostrar mensaje
        if (!$roleId) {
            return <<<HTML
            <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
                <div class="lego-screen__content">
                    <div class="example-form example-form--no-context">
                        <div class="example-form__empty-state">
                            <ion-icon name="information-circle-outline" class="example-form__empty-icon"></ion-icon>
                            <h2>Selecciona un rol</h2>
                            <p>Para editar un rol, primero debes seleccionarlo desde la tabla o usar el menú con un ID específico.</p>
                            <button 
                                type="button" 
                                class="example-form__button example-form__button--primary"
                                onclick="window.legoWindowManager?.closeCurrentWindow() || history.back()"
                            >
                                <ion-icon name="arrow-back-outline"></ion-icon>
                                Volver a la lista
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            HTML;
        }

        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <div class="example-form">
                    <!-- Header -->
                    <div class="example-form__header">
                        <h1 class="example-form__title">{$screenLabel}</h1>
                    </div>

                    <!-- Formulario -->
                    <form class="example-form__form" id="role-edit-form" onsubmit="return false;">
                        <input type="hidden" id="role-original-id" value="{$roleId}">
                        
                        <div class="example-form__grid">
                            <!-- Grupo de Autenticación (readonly) -->
                            <div class="example-form__field example-form__field--full">
                                {$authGroupSelect->render()}
                                <small class="example-form__help" style="margin-top: 4px; display: block; font-size: 12px; color: var(--text-secondary);">
                                    El grupo de autenticación no se puede cambiar después de crear
                                </small>
                            </div>

                            <!-- ID del Rol (readonly) -->
                            <div class="example-form__field example-form__field--full">
                                {$roleIdInput->render()}
                                <small class="example-form__help" style="margin-top: 4px; display: block; font-size: 12px; color: var(--text-secondary);">
                                    El ID del rol no se puede cambiar después de crear
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
                                    <input type="checkbox" id="role-is-active">
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
                                Guardar Cambios
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

