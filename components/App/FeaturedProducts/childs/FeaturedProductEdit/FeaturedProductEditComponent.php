<?php
namespace Components\App\FeaturedProducts\Childs\FeaturedProductEdit;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use App\Models\Flower;
use App\Models\FeaturedProduct;

#[ApiComponent('/featured-products/edit', methods: ['GET'])]
class FeaturedProductEditComponent extends CoreComponent
{
    protected $CSS_PATHS = ["../FeaturedProductCreate/featured-product-form.css"];
    protected $JS_PATHS = ["./featured-product-edit.js"];

    public function __construct(array $params = [])
    {
        $id = $params['id'] ?? $_GET['id'] ?? $_REQUEST['id'] ?? null;
        if ($id !== null) {
            $this->featuredProductId = is_numeric($id) ? (int)$id : null;
        }
    }

    private ?int $featuredProductId = null;

    protected function component(): string
    {
        $featuredProductId = $this->featuredProductId
            ?? (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null);

        if (!$featuredProductId) {
            return <<<HTML
            <div class="featured-product-form">
                <div class="featured-product-form__error">
                    <h2>Error</h2>
                    <p>ID de producto destacado no especificado</p>
                </div>
            </div>
            HTML;
        }

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

        // Agregar opción "Ninguno" al inicio
        array_unshift($tagOptions, ['value' => '', 'label' => 'Ninguno']);

        // Crear componentes del formulario SIN valores (JavaScript los cargará)
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
        <div class="featured-product-form" data-featured-product-id="{$featuredProductId}">
            <div class="featured-product-form__header">
                <h2 class="featured-product-form__title">Editar Producto Destacado</h2>
            </div>

            <div class="featured-product-form__loading" id="featured-product-form-loading">
                ⭐ Cargando arreglo floral destacado...
            </div>

            <form id="featured-product-edit-form" class="featured-product-form__form" style="display: none;">
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
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
