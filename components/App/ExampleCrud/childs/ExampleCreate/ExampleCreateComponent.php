<?php
namespace Components\App\ExampleCrud\Childs\ExampleCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;

/**
 * ExampleCreateComponent - Formulario de creación (CRUD V3)
 *
 * FILOSOFÍA LEGO:
 * Componente dedicado ÚNICAMENTE a crear registros.
 * No contiene tabla - separación de responsabilidades.
 *
 * MEJORAS vs V1/V2:
 * ✅ Componente separado (no modal, no child page)
 * ✅ Navegación con módulos (closeCurrentModule)
 * ✅ Validación client-side con ValidationEngine
 * ✅ Usa SelectComponent.setValue() sin .click() hack
 * ✅ ApiClient con manejo de errores
 *
 * CONSISTENCIA DIMENSIONAL:
 * Formulario usa misma estructura que ExampleEdit
 * manteniendo "las mismas distancias" visuales.
 */
#[ApiComponent('/example-crud/create', methods: ['GET'])]
class ExampleCreateComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./example-form.css"];
    protected $JS_PATHS = ["./example-create.js"];

    protected function component(): string
    {
        // Categorías de ejemplo (en producción vendrían de BD)
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
            path: "example-crud/images/", // Ruta en MinIO
            maxFiles: 5,
            allowReorder: true,
            allowMultiple: true,
            required: false
        );

        return <<<HTML
        <div class="example-form">
            <!-- Header -->
            <div class="example-form__header">
                <h1 class="example-form__title">Crear Registro</h1>
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
        HTML;
    }
}
