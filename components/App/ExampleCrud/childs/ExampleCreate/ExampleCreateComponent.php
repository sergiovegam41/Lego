<?php
namespace Components\App\ExampleCrud\Childs\ExampleCreate;

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
 * ExampleCreateComponent - Formulario de creación
 *
 * SCREEN PATTERN:
 * - Implementa ScreenInterface para definir su identidad
 * - SCREEN_PARENT apunta al ID del screen padre (ExampleCrudComponent)
 * - Visible en el menú como hijo de ExampleCrud
 */
#[ApiComponent('/example-crud/create', methods: ['GET'])]
class ExampleCreateComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY - Fuente de verdad para el menú
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'example-crud-create';
    public const SCREEN_LABEL = 'Crear Registro';
    public const SCREEN_ICON = 'add-circle-outline';
    public const SCREEN_ROUTE = '/component/example-crud/create';
    public const SCREEN_PARENT = ExampleCrudComponent::MENU_GROUP_ID; // Hijo del grupo, no de la lista
    public const SCREEN_ORDER = 10;
    public const SCREEN_VISIBLE = true;
    public const SCREEN_DYNAMIC = false;
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",  // Screen wrapper
        "./example-form.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",   // Screen manager
        "./example-create.js"
    ];

    protected function component(): string
    {
        // Categorías de ejemplo
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
                    <form class="example-form__form" id="example-create-form" onsubmit="return false;">
                        <div class="example-form__grid">
                            <!-- Nombre -->
                            <div class="example-form__field example-form__field--full">
                                {$nameInput->render()}
                            </div>

                            <!-- Descripción -->
                            <div class="example-form__field example-form__field--full">
                                {$descriptionTextarea->render()}
                            </div>

                            <!-- Precio -->
                            <div class="example-form__field">
                                {$priceInput->render()}
                            </div>

                            <!-- Stock -->
                            <div class="example-form__field">
                                {$stockInput->render()}
                            </div>

                            <!-- Categoría -->
                            <div class="example-form__field example-form__field--full">
                                {$categorySelect->render()}
                            </div>

                            <!-- Imágenes -->
                            <div class="example-form__field example-form__field--full">
                                {$filePondImages->render()}
                            </div>
                        </div>

                        <!-- Acciones -->
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
                                Crear Registro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        HTML;
    }
}
