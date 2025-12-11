<?php
namespace Components\App\ToolsCrud\Childs\ToolsCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;
use Components\App\ToolsCrud\ToolsCrudComponent;

/**
 * ToolsCreateComponent - Formulario de creación de herramientas
 *
 * SCREEN PATTERN:
 * - Implementa ScreenInterface para definir su identidad
 * - parent_id se obtiene proceduralmente desde la BD
 * - Visible en el menú como hijo de ToolsCrud
 */
#[ApiComponent('/tools-crud/create', methods: ['GET'])]
class ToolsCreateComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'tools-crud-create';
    public const SCREEN_LABEL = 'Nueva Herramienta';
    public const SCREEN_ICON = 'add-circle-outline';
    public const SCREEN_ROUTE = '/component/tools-crud/create';
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ORDER = 10;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./tools-form.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./tools-create.js"
    ];

    protected function component(): string
    {
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

        $screenId = self::SCREEN_ID;
        $screenLabel = self::SCREEN_LABEL;

        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <div class="tools-form">
                    <!-- Header -->
                    <div class="tools-form__header">
                        <h1 class="tools-form__title">{$screenLabel}</h1>
                    </div>

                    <!-- Formulario -->
                    <form class="tools-form__form" id="tools-create-form" onsubmit="return false;">
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
                                    <div class="tools-form__feature-item" data-index="0">
                                        <input 
                                            type="text" 
                                            class="tools-form__feature-input" 
                                            name="features[]" 
                                            placeholder="Ej: Material de acero inoxidable"
                                        >
                                        <button type="button" class="tools-form__feature-remove" onclick="removeFeature(this)" title="Eliminar">
                                            <ion-icon name="close-outline"></ion-icon>
                                        </button>
                                    </div>
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
                                Crear Herramienta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        HTML;
    }
}

