<?php
namespace Components\App\Categories\Childs\CategoryEdit;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;

#[ApiComponent('/categories/edit', methods: ['GET'])]
class CategoryEditComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./category-form.css"];
    protected $JS_PATHS = ["./category-edit.js"];

    public function __construct(array $params = [])
    {
        $id = $params['id'] ?? $_GET['id'] ?? $_REQUEST['id'] ?? null;
        if ($id !== null) {
            $this->categoryId = is_numeric($id) ? (int)$id : null;
        }
    }

    private ?int $categoryId = null;

    protected function component(): string
    {
        $categoryId = $this->categoryId
            ?? (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null);

        if (!$categoryId) {
            return <<<HTML
            <div class="category-form">
                <div class="category-form__error">
                    <h2>Error</h2>
                    <p>ID de categoría no especificado</p>
                </div>
            </div>
            HTML;
        }

        // Load category images to populate FilePond
        $initialImages = [];
        try {
            if (class_exists('\\Core\\Services\\File\\FileService')) {
                $fileService = new \Core\Services\File\FileService();
                $fileAssociations = $fileService->getEntityFiles('Category', $categoryId);

                if ($fileAssociations && !$fileAssociations->isEmpty()) {
                    $initialImages = $fileAssociations->map(function($assoc) {
                        if (!$assoc || !isset($assoc->file)) {
                            return null;
                        }
                        $file = $assoc->file;
                        return [
                            'id' => $file->id ?? null,
                            'url' => $file->url ?? null,
                            'original_name' => $file->original_name ?? 'image.jpg',
                            'size' => $file->size ?? 0,
                            'mime_type' => $file->mime_type ?? 'image/jpeg'
                        ];
                    })->filter()->values()->toArray();
                }
            }
        } catch (\Exception $e) {
            error_log("Error loading images for category {$categoryId}: " . $e->getMessage());
        }

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
            required: false,
            initialImages: $initialImages
        );

        return <<<HTML
        <div class="category-form" data-category-id="{$categoryId}">
            <div class="category-form__header">
                <h1 class="category-form__title">Editar Categoría</h1>
            </div>

            <div class="category-form__loading" id="category-form-loading">
                🌺 Cargando categoría de flores...
            </div>

            <form class="category-form__form" id="category-edit-form" style="display: none;">
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
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
