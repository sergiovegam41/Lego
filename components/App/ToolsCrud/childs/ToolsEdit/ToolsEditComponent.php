<?php
namespace Components\App\ToolsCrud\Childs\ToolsEdit;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;
use Components\App\ToolsCrud\ToolsCrudComponent;

/**
 * ToolsEditComponent - Formulario de edición de herramientas
 *
 * SCREEN PATTERN:
 * - SCREEN_DYNAMIC = true: Este screen se activa por contexto (editar una herramienta)
 * - SCREEN_VISIBLE = false: No aparece en el menú por defecto
 */
#[ApiComponent('/tools-crud/edit', methods: ['GET'])]
class ToolsEditComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'tools-crud-edit';
    public const SCREEN_LABEL = 'Editar Herramienta';
    public const SCREEN_ICON = 'create-outline';
    public const SCREEN_ROUTE = '/component/tools-crud/edit';
    public const SCREEN_PARENT = ToolsCrudComponent::MENU_GROUP_ID;
    public const SCREEN_ORDER = 20;
    public const SCREEN_VISIBLE = false;
    public const SCREEN_DYNAMIC = true;
    
    // ═══════════════════════════════════════════════════════════════════

    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./tools-form.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./tools-edit.js"
    ];

    private ?int $toolId = null;

    public function __construct(array $params = [])
    {
        $id = $params['id'] ?? $_GET['id'] ?? $_REQUEST['id'] ?? null;

        if ($id !== null) {
            $this->toolId = is_numeric($id) ? (int)$id : null;
        }
    }

    protected function component(): string
    {
        $toolId = $this->toolId
            ?? (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null)
            ?? (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ? (int)$_REQUEST['id'] : null);

        $screenId = self::SCREEN_ID;
        $screenLabel = self::SCREEN_LABEL;

        if (!$toolId) {
            return <<<HTML
            <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
                <div class="lego-screen__content">
                    <div class="tools-form tools-form--no-context">
                        <div class="tools-form__empty-state">
                            <ion-icon name="information-circle-outline" class="tools-form__empty-icon"></ion-icon>
                            <h2>Selecciona una herramienta</h2>
                            <p>Para editar una herramienta, primero debes seleccionarla desde la tabla.</p>
                            <button 
                                type="button" 
                                class="tools-form__button tools-form__button--primary"
                                onclick="window.legoWindowManager?.closeCurrentWindow() || history.back()"
                            >
                                <ion-icon name="arrow-back-outline"></ion-icon>
                                Volver a la lista
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow();
                    }
                }, 3000);
            </script>
            HTML;
        }

        $nameInput = new InputTextComponent(
            id: "tool-name",
            label: "Nombre de la Herramienta",
            placeholder: "Ej: Martillo de Carpintero",
            required: true
        );

        $descriptionTextarea = new TextAreaComponent(
            id: "tool-description",
            label: "Descripción",
            placeholder: "Descripción detallada de la herramienta...",
            rows: 4
        );

        $filePondImages = new FilePondComponent(
            id: "tool-images",
            label: "Imágenes de la Herramienta",
            path: "tools/images/",
            maxFiles: 10,
            allowReorder: true,
            allowMultiple: true,
            required: false
        );

        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <div class="tools-form" data-tool-id="{$toolId}">
                    <!-- Header -->
                    <div class="tools-form__header">
                        <h1 class="tools-form__title">{$screenLabel}</h1>
                    </div>

                    <!-- Loading state -->
                    <div class="tools-form__loading" id="tools-form-loading">
                        Cargando herramienta...
                    </div>

                    <!-- Formulario -->
                    <form class="tools-form__form" id="tools-edit-form" style="display: none;">
                        <div class="tools-form__grid">
                            <!-- Nombre -->
                            <div class="tools-form__field tools-form__field--full">
                                {$nameInput->render()}
                            </div>

                            <!-- Descripción -->
                            <div class="tools-form__field tools-form__field--full">
                                {$descriptionTextarea->render()}
                            </div>

                            <!-- Características (lista dinámica) -->
                            <div class="tools-form__field tools-form__field--full">
                                <label class="tools-form__label">Características</label>
                                <div class="tools-form__features" id="tool-features-container">
                                    <!-- Se poblará dinámicamente -->
                                </div>
                                <button type="button" class="tools-form__feature-add" onclick="addFeature()">
                                    <ion-icon name="add-outline"></ion-icon>
                                    Agregar característica
                                </button>
                            </div>

                            <!-- Imágenes -->
                            <div class="tools-form__field tools-form__field--full">
                                {$filePondImages->render()}
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="tools-form__actions">
                            <button
                                type="button"
                                class="tools-form__button tools-form__button--secondary"
                                id="tools-form-cancel-btn"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="tools-form__button tools-form__button--primary"
                                id="tools-form-submit-btn"
                            >
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        HTML;
    }
}

