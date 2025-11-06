<?php
namespace Components\App\FeaturedProducts\Childs\FeaturedProductCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use App\Models\Flower;
use App\Models\FeaturedProduct;

#[ApiComponent('/featured-products/create', methods: ['GET'])]
class FeaturedProductCreateComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./featured-product-form.css"];
    protected $JS_PATHS = ["./featured-product-create.js"];

    protected function component(): string
    {
        // Obtener todos los productos (flowers) para el select
        $productsData = Flower::active()
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'price'])
            ->toArray();

        $productOptions = array_map(function($product) {
            $price = number_format($product['price'], 0, ',', '.');
            return [
                'value' => (string)$product['id'],
                'label' => $product['name'] . ' - $' . $price
            ];
        }, $productsData);

        // Obtener tags disponibles
        $tags = FeaturedProduct::getAvailableTags();
        $tagOptions = array_map(function($key, $label) {
            return ['value' => $key, 'label' => $label];
        }, array_keys($tags), array_values($tags));

        // Crear componentes del formulario usando los componentes compartidos
        $productSelect = new SelectComponent(
            id: "featured-product-product-id",
            label: "Producto",
            options: $productOptions,
            placeholder: "Selecciona un producto",
            required: true,
            searchable: true
        );

        $tagSelect = new SelectComponent(
            id: "featured-product-tag",
            label: "Tag/Etiqueta (Opcional)",
            options: $tagOptions,
            placeholder: "Selecciona un tag (opcional)",
            required: false,
            searchable: false
        );

        $descriptionInput = new InputTextComponent(
            id: "featured-product-description",
            label: "Descripción",
            placeholder: "Describe por qué este producto es destacado",
            required: false
        );

        $sortOrderInput = new InputTextComponent(
            id: "featured-product-sort-order",
            label: "Orden",
            placeholder: "0",
            required: false,
            type: "number"
        );

        return <<<HTML
        <div class="featured-product-form">
            <div class="featured-product-form__header">
                <h2 class="featured-product-form__title">Agregar Producto Destacado</h2>
            </div>

            <form id="featured-product-create-form" class="featured-product-form__form">
                <div class="featured-product-form__grid">
                    <div class="featured-product-form__field">
                        {$productSelect->render()}
                    </div>

                    <div class="featured-product-form__field">
                        {$tagSelect->render()}
                    </div>
                </div>

                <div class="featured-product-form__grid">
                    <div class="featured-product-form__field">
                        {$descriptionInput->render()}
                    </div>

                    <div class="featured-product-form__field">
                        {$sortOrderInput->render()}
                    </div>
                </div>

                <div class="featured-product-form__field">
                    <label class="featured-product-form__checkbox">
                        <input
                            type="checkbox"
                            id="featured-product-is-active"
                            checked
                        />
                        <span>Activo</span>
                    </label>
                </div>

                <div class="featured-product-form__actions">
                    <button
                        type="button"
                        id="featured-product-form-cancel-btn"
                        class="featured-product-form__button featured-product-form__button--secondary"
                    >
                        <ion-icon name="close-outline"></ion-icon>
                        <span>Cancelar</span>
                    </button>
                    <button
                        type="submit"
                        id="featured-product-form-submit-btn"
                        class="featured-product-form__button featured-product-form__button--primary"
                    >
                        <ion-icon name="checkmark-outline"></ion-icon>
                        <span>Crear Producto Destacado</span>
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
