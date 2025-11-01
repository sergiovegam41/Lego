<?php
namespace Components\App\ProductsCrudV3;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;

/**
 * ProductEditComponent - Formulario de edición (CRUD V3)
 *
 * FILOSOFÍA LEGO:
 * Componente dedicado ÚNICAMENTE a editar productos.
 * Mantiene "las mismas distancias" que ProductCreate.
 *
 * MEJORAS vs V1/V2:
 * ✅ Componente separado (no modal, no child page)
 * ✅ Navegación con módulos
 * ✅ Datos pre-cargados del producto
 * ✅ Usa ApiClient para fetch y update
 *
 * CONSISTENCIA DIMENSIONAL:
 * Formulario idéntico a ProductCreate,
 * solo difiere en valores iniciales y endpoint.
 */
#[ApiComponent('/products-crud-v3/edit', methods: ['GET'])]
class ProductEditComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./product-form.css"];
    protected $JS_PATHS = ["./product-edit.js"];

    public function __construct(
        public readonly ?int $productId = null
    ) {}

    protected function component(): string
    {
        // En producción, cargar datos del producto desde BD
        // Por ahora, los datos se cargarán via JS usando ApiClient
        $productId = $this->productId ?? $_GET['id'] ?? null;

        if (!$productId) {
            return <<<HTML
            <div class="product-form">
                <div class="product-form__error">
                    <h2>Error</h2>
                    <p>ID de producto no especificado</p>
                </div>
            </div>
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
            id: "product-name",
            label: "Nombre del Producto",
            placeholder: "Ej: Laptop Dell XPS 15",
            required: true
        );

        $descriptionTextarea = new TextAreaComponent(
            id: "product-description",
            label: "Descripción",
            placeholder: "Descripción detallada del producto...",
            rows: 4
        );

        $priceInput = new InputTextComponent(
            id: "product-price",
            label: "Precio",
            type: "number",
            placeholder: "0.00",
            required: true
        );

        $stockInput = new InputTextComponent(
            id: "product-stock",
            label: "Stock",
            type: "number",
            placeholder: "0",
            required: true
        );

        $categorySelect = new SelectComponent(
            id: "product-category",
            label: "Categoría",
            options: $categories,
            required: true,
            searchable: true
        );

        return <<<HTML
        <div class="product-form" data-product-id="{$productId}">
            <!-- Header -->
            <div class="product-form__header">
                <h1 class="product-form__title">Editar Producto</h1>
            </div>

            <!-- Loading state (mientras carga datos) -->
            <div class="product-form__loading" id="product-form-loading">
                Cargando producto...
            </div>

            <!-- Formulario (oculto hasta que carguen datos) -->
            <form class="product-form__form" id="product-edit-form" style="display: none;">
                <div class="product-form__grid">
                    <!-- Nombre -->
                    <div class="product-form__field product-form__field--full">
                        {$nameInput->render()}
                    </div>

                    <!-- Descripción -->
                    <div class="product-form__field product-form__field--full">
                        {$descriptionTextarea->render()}
                    </div>

                    <!-- Precio -->
                    <div class="product-form__field">
                        {$priceInput->render()}
                    </div>

                    <!-- Stock -->
                    <div class="product-form__field">
                        {$stockInput->render()}
                    </div>

                    <!-- Categoría -->
                    <div class="product-form__field product-form__field--full">
                        {$categorySelect->render()}
                    </div>
                </div>

                <!-- Acciones -->
                <div class="product-form__actions">
                    <button
                        type="button"
                        class="product-form__button product-form__button--secondary"
                        id="product-form-cancel-btn"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="product-form__button product-form__button--primary"
                        id="product-form-submit-btn"
                    >
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
