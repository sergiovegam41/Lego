<?php
namespace Components\App\ProductsCrudV3;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\FilePondComponent\FilePondComponent;

/**
 * ProductCreateComponent - Formulario de creación (CRUD V3)
 *
 * FILOSOFÍA LEGO:
 * Componente dedicado ÚNICAMENTE a crear productos.
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
 * Formulario usa misma estructura que ProductEdit
 * manteniendo "las mismas distancias" visuales.
 */
#[ApiComponent('/products-crud-v3/create', methods: ['GET'])]
class ProductCreateComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./product-form.css"];
    protected $JS_PATHS = ["./product-create.js"];

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

        $filePondImages = new FilePondComponent(
            id: "product-images",
            label: "Imágenes del Producto",
            productId: null, // No hay product_id aún (se creará al submit)
            maxFiles: 5,
            allowReorder: true,
            allowMultiple: true,
            required: false
        );

        return <<<HTML
        <div class="product-form">
            <!-- Header -->
            <div class="product-form__header">
                <h1 class="product-form__title">Crear Producto</h1>
            </div>

            <!-- Formulario -->
            <form class="product-form__form" id="product-create-form" onsubmit="return false;">
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

                    <!-- Imágenes -->
                    <div class="product-form__field product-form__field--full">
                        {$filePondImages->render()}
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
                        Crear Producto
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
