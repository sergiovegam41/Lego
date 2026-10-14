<?php
namespace Components\App\AuthGroupsConfig\Childs\AuthGroupsConfigCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\App\AuthGroupsConfig\AuthGroupsConfigComponent;

/**
 * AuthGroupsConfigCreateComponent - Formulario de creación de grupos de autenticación
 *
 * SCREEN PATTERN:
 * - Implementa ScreenInterface para definir su identidad
 * - parent_id se obtiene proceduralmente desde la BD
 * - Visible en el menú como hijo de "Gestión de Roles"
 */
#[ApiComponent('/auth-groups-config/create', methods: ['GET'])]
class AuthGroupsConfigCreateComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'auth-groups-config-create';
    public const SCREEN_LABEL = 'Crear';
    public const SCREEN_ICON = 'add-circle-outline';
    public const SCREEN_ROUTE = '/component/auth-groups-config/create';
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
        "./auth-groups-config-create.js"
    ];

    protected function component(): string
    {
        $groupIdInput = new InputTextComponent(
            id: "auth-group-id",
            label: "ID del Grupo",
            placeholder: "Ej: ADMINS, APIS, CUSTOMERS, etc.",
            required: true
        );

        $groupNameInput = new InputTextComponent(
            id: "auth-group-name",
            label: "Nombre del Grupo",
            placeholder: "Ej: Administradores, APIs, Clientes, etc.",
            required: true
        );

        $descriptionTextarea = new TextAreaComponent(
            id: "auth-group-description",
            label: "Descripción",
            placeholder: "Descripción del grupo de autenticación...",
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
                    <form class="example-form__form" id="auth-group-create-form" onsubmit="return false;">
                        <div class="example-form__grid">
                            <!-- ID del Grupo -->
                            <div class="example-form__field example-form__field--full">
                                {$groupIdInput->render()}
                                <small class="example-form__help" style="margin-top: 4px; display: block; font-size: 12px; color: var(--text-secondary);">
                                    El ID se normalizará automáticamente (mayúsculas, sin acentos, sin caracteres especiales)
                                </small>
                            </div>

                            <!-- Nombre del Grupo -->
                            <div class="example-form__field example-form__field--full">
                                {$groupNameInput->render()}
                            </div>

                            <!-- Descripción -->
                            <div class="example-form__field example-form__field--full">
                                {$descriptionTextarea->render()}
                            </div>

                            <!-- Activo -->
                            <div class="example-form__field example-form__field--full">
                                <label class="example-form__checkbox">
                                    <input type="checkbox" id="auth-group-is-active" checked>
                                    <span>Grupo activo</span>
                                </label>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="example-form__actions">
                            <button
                                type="button"
                                class="example-form__button example-form__button--secondary"
                                id="auth-group-form-cancel-btn"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="example-form__button example-form__button--primary"
                                id="auth-group-form-submit-btn"
                            >
                                Crear Grupo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        HTML;
    }
}

