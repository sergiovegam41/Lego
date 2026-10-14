<?php
namespace Components\App\ExampleCrud\Childs\ExampleEdit;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;
use Components\App\ExampleCrud\ExampleCrudComponent;

/**
 * ExampleEditComponent - Formulario de edición
 *
 * SCREEN PATTERN:
 * - SCREEN_DYNAMIC = true: Este screen se activa por contexto (editar un registro)
 * - SCREEN_VISIBLE = false: No aparece en el menú por defecto
 * - Se registra dinámicamente cuando el usuario hace clic en "Editar"
 */
#[ApiComponent('/example-crud/edit', methods: ['GET'])]
class ExampleEditComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY - Fuente de verdad para el menú
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'example-crud-edit';
    public const SCREEN_LABEL = 'Editar Registro';
    public const SCREEN_ICON = 'create-outline';
    public const SCREEN_ROUTE = '/component/example-crud/edit';
    // parent_id se obtiene proceduralmente desde la BD
    public const SCREEN_ORDER = 20;
    public const SCREEN_VISIBLE = false;  // No visible por defecto
    public const SCREEN_DYNAMIC = true;   // Se activa por contexto
    
    // ═══════════════════════════════════════════════════════════════════

    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",  // Screen wrapper
        "./example-form.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",   // Screen manager
        "./example-edit.js"
    ];

    private ?int $exampleId = null;

    public function __construct(array $params = [])
    {
        $id = $params['id'] ?? $_GET['id'] ?? $_REQUEST['id'] ?? null;

        if ($id !== null) {
            $this->exampleId = is_numeric($id) ? (int)$id : null;
        }
    }

    protected function component(): string
    {
        $exampleId = $this->exampleId
            ?? (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null)
            ?? (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ? (int)$_REQUEST['id'] : null);

        $screenId = self::SCREEN_ID;
        $screenLabel = self::SCREEN_LABEL;

        if (!$exampleId) {
            return <<<HTML
            <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
                <div class="lego-screen__content">
                    <div class="example-form example-form--no-context">
                        <div class="example-form__empty-state">
                            <ion-icon name="information-circle-outline" class="example-form__empty-icon"></ion-icon>
                            <h2>Selecciona un registro</h2>
                            <p>Para editar un registro, primero debes seleccionarlo desde la tabla.</p>
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
            <script>
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow();
                    }
                }, 3000);
            </script>
            HTML;
        }

        // Categorías
        $categories = [
            ["value" => "electronics", "label" => "Electrónica"],
            ["value" => "clothing", "label" => "Ropa"],
            ["value" => "food", "label" => "Alimentos"],
            ["value" => "books", "label" => "Libros"],
            ["value" => "toys", "label" => "Juguetes"]
        ];

        $nameInput = new InputTextComponent(
            id: "example-name",
            label: "Nombre del Registro",
            placeholder: "Ej: Laptop Dell XPS 15",
            required: true
        );

        $descriptionTextarea = new TextAreaComponent(
            id: "example-description",
            label: "Descripción",
            placeholder: "Descripción detallada del registro...",
            rows: 4
        );

        $priceInput = new InputTextComponent(
            id: "example-price",
            label: "Precio",
            type: "number",
            placeholder: "0.00",
            required: true
        );

        $stockInput = new InputTextComponent(
            id: "example-stock",
            label: "Stock",
            type: "number",
            placeholder: "0",
            required: true
        );

        $categorySelect = new SelectComponent(
            id: "example-category",
            label: "Categoría",
            options: $categories,
            required: true,
            searchable: true
        );

        $filePondImages = new FilePondComponent(
            id: "example-images",
            label: "Imágenes del Registro",
            path: "example-crud/images/",
            maxFiles: 5,
            allowReorder: true,
            allowMultiple: true,
            required: false
        );

        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <div class="example-form" data-example-id="{$exampleId}">
                    <!-- Header -->
                    <div class="example-form__header">
                        <h1 class="example-form__title">{$screenLabel}</h1>
                    </div>

                    <!-- Loading state -->
                    <div class="example-form__loading" id="example-form-loading">
                        Cargando registro...
                    </div>

                    <!-- Formulario -->
                    <form class="example-form__form" id="example-edit-form" style="display: none;">
                        <div class="example-form__grid">
                            <div class="example-form__field example-form__field--full">
                                {$nameInput->render()}
                            </div>

                            <div class="example-form__field example-form__field--full">
                                {$descriptionTextarea->render()}
                            </div>

                            <div class="example-form__field">
                                {$priceInput->render()}
                            </div>

                            <div class="example-form__field">
                                {$stockInput->render()}
                            </div>

                            <div class="example-form__field example-form__field--full">
                                {$categorySelect->render()}
                            </div>

                            <div class="example-form__field example-form__field--full">
                                {$filePondImages->render()}
                            </div>
                        </div>

                        <div class="example-form__actions">
                            <button
                                type="button"
                                class="example-form__button example-form__button--secondary"
                                id="example-form-cancel-btn"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="example-form__button example-form__button--primary"
                                id="example-form-submit-btn"
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
