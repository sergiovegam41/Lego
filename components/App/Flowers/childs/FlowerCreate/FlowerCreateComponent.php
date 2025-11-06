<?php
namespace Components\App\Flowers\Childs\FlowerCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;

#[ApiComponent('/flowers/create', methods: ['GET'])]
class FlowerCreateComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./flower-form.css"];
    protected $JS_PATHS = ["./flower-create.js"];

    protected function component(): string
    {
        // Load categories from database
        $categoriesData = \App\Models\Category::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get(['id', 'name'])
            ->toArray();

        $categories = array_map(function($cat) {
            return ["value" => (string)$cat['id'], "label" => $cat['name']];
        }, $categoriesData);

        $nameInput = new InputTextComponent(
            id: "flower-name",
            label: "Nombre de la Flor",
            placeholder: "Ej: Rosa Roja, Tulipán Blanco",
            required: true
        );

        $priceInput = new InputTextComponent(
            id: "flower-price",
            label: "Precio",
            placeholder: "0.00",
            required: true,
            type: "number"
        );

        $categorySelect = new SelectComponent(
            id: "flower-category",
            label: "Categoría",
            options: $categories,
            placeholder: "Selecciona una categoría",
            required: true,
            searchable: true
        );

        $descriptionTextarea = new TextAreaComponent(
            id: "flower-description",
            label: "Descripción",
            placeholder: "Descripción de la flor...",
            rows: 4
        );

        $filePondImages = new FilePondComponent(
            id: "flower-images",
            label: "Imágenes de la Flor",
            path: "flowers/images/",
            maxFiles: 10,
            maxFileSize: 26214400, // 25MB para imágenes HD
            allowReorder: true,
            allowMultiple: true,
            required: false
        );

        return <<<HTML
        <div class="flower-form">
            <div class="flower-form__header">
                <h1 class="flower-form__title">Nueva Flor</h1>
            </div>

            <form class="flower-form__form" id="flower-create-form">
                <div class="flower-form__grid">
                    <div class="flower-form__field">
                        {$nameInput->render()}
                    </div>

                    <div class="flower-form__field">
                        {$priceInput->render()}
                    </div>

                    <div class="flower-form__field flower-form__field--full">
                        {$categorySelect->render()}
                    </div>

                    <div class="flower-form__field flower-form__field--full">
                        {$filePondImages->render()}
                    </div>

                    <div class="flower-form__field flower-form__field--full">
                        {$descriptionTextarea->render()}
                    </div>
                </div>

                <div class="flower-form__actions">
                    <button type="button" class="flower-form__button flower-form__button--secondary" id="flower-form-cancel-btn">
                        Cancelar
                    </button>
                    <button type="submit" class="flower-form__button flower-form__button--primary" id="flower-form-submit-btn">
                        Crear Flor
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
