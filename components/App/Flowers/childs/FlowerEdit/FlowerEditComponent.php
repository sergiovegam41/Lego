<?php
namespace Components\App\Flowers\Childs\FlowerEdit;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;

#[ApiComponent('/flowers/edit', methods: ['GET'])]
class FlowerEditComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./flower-form.css"];
    protected $JS_PATHS = ["./flower-edit.js"];

    public function __construct(array $params = [])
    {
        $id = $params['id'] ?? $_GET['id'] ?? $_REQUEST['id'] ?? null;
        if ($id !== null) {
            $this->flowerId = is_numeric($id) ? (int)$id : null;
        }
    }

    private ?int $flowerId = null;

    protected function component(): string
    {
        $flowerId = $this->flowerId
            ?? (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null);

        if (!$flowerId) {
            return <<<HTML
            <div class="flower-form">
                <div class="flower-form__error">
                    <h2>Error</h2>
                    <p>ID de flor no especificado</p>
                </div>
            </div>
            HTML;
        }

        // Load flower images to populate FilePond
        $initialImages = [];
        try {
            if (class_exists('\\Core\\Services\\File\\FileService')) {
                $fileService = new \Core\Services\File\FileService();
                $fileAssociations = $fileService->getEntityFiles('Flower', $flowerId);

                error_log("[FlowerEditComponent] Loading images for flower {$flowerId}");
                error_log("[FlowerEditComponent] File associations count: " . ($fileAssociations ? $fileAssociations->count() : 0));

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

                    error_log("[FlowerEditComponent] Initial images prepared: " . json_encode($initialImages));
                }
            }
        } catch (\Exception $e) {
            error_log("[FlowerEditComponent] Error loading images for flower {$flowerId}: " . $e->getMessage());
            error_log("[FlowerEditComponent] Stack trace: " . $e->getTraceAsString());
        }

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
            allowReorder: true,
            allowMultiple: true,
            required: false,
            initialImages: $initialImages
        );

        return <<<HTML
        <div class="flower-form" data-flower-id="{$flowerId}">
            <div class="flower-form__header">
                <h1 class="flower-form__title">Editar Flor</h1>
            </div>

            <div class="flower-form__loading" id="flower-form-loading">
                Cargando flor...
            </div>

            <form class="flower-form__form" id="flower-edit-form" style="display: none;">
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
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
