<?php
namespace Components\App\AuthGroupsConfig\Childs\AuthGroupsConfigEdit;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\App\AuthGroupsConfig\AuthGroupsConfigComponent;

/**
 * AuthGroupsConfigEditComponent - Formulario de edición de grupos de autenticación
 *
 * SCREEN PATTERN:
 * - Implementa ScreenInterface para definir su identidad
 * - parent_id se obtiene proceduralmente desde la BD
 * - Visible en el menú como hijo de "Gestión de Roles"
 */
#[ApiComponent('/auth-groups-config/edit', methods: ['GET'])]
class AuthGroupsConfigEditComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'auth-groups-config-edit';
    public const SCREEN_LABEL = 'Editar';
    public const SCREEN_ICON = 'create-outline';
    public const SCREEN_ROUTE = '/component/auth-groups-config/edit';
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ORDER = 20;
    public const SCREEN_VISIBLE = false; // Oculto: no aparece en el menú por defecto
    public const SCREEN_DYNAMIC = true; // Dinámico: solo aparece cuando se abre desde la tabla
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./example-form.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./auth-groups-config-edit.js"
    ];

    protected function component(): string
    {
        // Obtener el ID del grupo desde los parámetros
        $groupId = $_GET['id'] ?? $_REQUEST['id'] ?? null;

        $groupIdInput = new InputTextComponent(
            id: "auth-group-id",
            label: "ID del Grupo",
            placeholder: "Ej: ADMINS, APIS, CUSTOMERS, etc.",
            required: true,
            disabled: true // El ID no se puede cambiar después de crear
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

        // Si no hay ID, mostrar mensaje
        if (!$groupId) {
            return <<<HTML
            <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
                <div class="lego-screen__content">
                    <div class="example-form example-form--no-context">
                        <div class="example-form__empty-state">
                            <ion-icon name="information-circle-outline" class="example-form__empty-icon"></ion-icon>
                            <h2>Selecciona un grupo</h2>
                            <p>Para editar un grupo, primero debes seleccionarlo desde la tabla o usar el menú con un ID específico.</p>
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
                    <form class="example-form__form" id="auth-group-edit-form" onsubmit="return false;">
                        <input type="hidden" id="auth-group-original-id" value="{$groupId}">
                        
                        <div class="example-form__grid">
                            <!-- ID del Grupo (readonly) -->
                            <div class="example-form__field example-form__field--full">
                                {$groupIdInput->render()}
                                <small class="example-form__help" style="margin-top: 4px; display: block; font-size: 12px; color: var(--text-secondary);">
                                    El ID no se puede cambiar después de crear
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
                                    <input type="checkbox" id="auth-group-is-active">
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
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        HTML;
    }
}

