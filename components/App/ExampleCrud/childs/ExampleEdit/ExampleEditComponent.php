<?php
namespace Components\App\ExampleCrud\Childs\ExampleEdit;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;

/**
 * ExampleEditComponent - Formulario de edición (CRUD V3)
 *
 * FILOSOFÍA LEGO:
 * Componente dedicado ÚNICAMENTE a editar registros.
 * Mantiene "las mismas distancias" que ExampleCreate.
 *
 * MEJORAS vs V1/V2:
 * ✅ Componente separado (no modal, no child page)
 * ✅ Navegación con módulos
 * ✅ Datos pre-cargados del registro
 * ✅ Usa ApiClient para fetch y update
 *
 * CONSISTENCIA DIMENSIONAL:
 * Formulario idéntico a ExampleCreate,
 * solo difiere en valores iniciales y endpoint.
 */
#[ApiComponent('/example-crud/edit', methods: ['GET'])]
class ExampleEditComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./example-form.css"];
    protected $JS_PATHS = ["./example-edit.js"];

    public function __construct(array $params = [])
    {
        // Obtener ID del registro desde parámetros o query string
        // Intentar obtener de múltiples fuentes
        $id = $params['id'] ?? $_GET['id'] ?? $_REQUEST['id'] ?? null;

        // Convertir a int si es string numérico
        if ($id !== null) {
            $this->exampleId = is_numeric($id) ? (int)$id : null;
        }

        // Debug log
        error_log('[ExampleEditComponent] Constructor - params: ' . json_encode($params));
        error_log('[ExampleEditComponent] Constructor - $_GET: ' . json_encode($_GET));
        error_log('[ExampleEditComponent] Constructor - exampleId: ' . ($this->exampleId ?? 'NULL'));
    }

    private ?int $exampleId = null;

    protected function component(): string
    {
        // Obtener example ID con múltiples fallbacks
        $exampleId = $this->exampleId
            ?? (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null)
            ?? (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ? (int)$_REQUEST['id'] : null);

        error_log('[ExampleEditComponent] component() - exampleId final: ' . ($exampleId ?? 'NULL'));

        if (!$exampleId) {
            // Sin contexto: redirigir automáticamente a la lista
            // Este caso no debería ocurrir si el menú está correctamente configurado
            // (is_dynamic=true para la opción "Editar")
            return <<<HTML
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
            <script>
                // Auto-redirigir después de 3 segundos si el usuario no hace nada
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow();
                    }
                }, 3000);
            </script>
            HTML;
        }

        // Categorías (igual que en Create)
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
        <div class="example-form" data-example-id="{$exampleId}">
            <!-- Header -->
            <div class="example-form__header">
                <h1 class="example-form__title">Editar Registro</h1>
            </div>

            <!-- Loading state (mientras carga datos) -->
            <div class="example-form__loading" id="example-form-loading">
                Cargando registro...
            </div>

            <!-- Formulario (oculto hasta que carguen datos) -->
            <form class="example-form__form" id="example-edit-form" style="display: none;">
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
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
