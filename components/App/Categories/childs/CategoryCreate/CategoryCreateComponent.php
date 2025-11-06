<?php
namespace Components\App\Categories\Childs\CategoryCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;

#[ApiComponent('/categories/create', methods: ['GET'])]
class CategoryCreateComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./category-form.css"];
    protected $JS_PATHS = ["./category-create.js"];

    protected function component(): string
    {
        $nameInput = new InputTextComponent(
            id: "category-name",
            label: "Nombre de la Categoría",
            placeholder: "Ej: Rosas, Tulipanes",
            required: true
        );

        $descriptionTextarea = new TextAreaComponent(
            id: "category-description",
            label: "Descripción",
            placeholder: "Descripción de la categoría...",
            rows: 4
        );

        $filePondImage = new FilePondComponent(
            id: "category-image",
            label: "Imagen de la Categoría",
            path: "categories/images/",
            maxFiles: 1,
            maxFileSize: 26214400, // 25MB para imágenes HD
            allowReorder: false,
            allowMultiple: false,
            required: false
        );

        return <<<HTML
        <div class="category-form">
            <div class="category-form__header">
                <h1 class="category-form__title">Crear Categoría</h1>
            </div>

            <form class="category-form__form" id="category-create-form" onsubmit="return false;">
                <div class="category-form__grid">
                    <div class="category-form__field category-form__field--full">
                        {$nameInput->render()}
                    </div>

                    <div class="category-form__field category-form__field--full">
                        {$descriptionTextarea->render()}
                    </div>

                    <div class="category-form__field category-form__field--full">
                        {$filePondImage->render()}
                    </div>
                </div>

                <div class="category-form__actions">
                    <button type="button" class="category-form__button category-form__button--secondary" id="category-form-cancel-btn">
                        Cancelar
                    </button>
                    <button type="submit" class="category-form__button category-form__button--primary" id="category-form-submit-btn">
                        Crear Categoría
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
