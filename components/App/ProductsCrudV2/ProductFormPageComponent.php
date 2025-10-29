<?php

namespace Components\App\ProductsCrudV2;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Essentials\ImageGalleryComponent\ImageGalleryComponent;
use Components\Shared\Forms\Forms\Button;

/**
 * ProductFormPageComponent
 *
 * FILOSOFÍA LEGO:
 * Página hijo para crear/editar productos.
 * - Se abre como página independiente (no modal)
 * - Se cierra y vuelve a la tabla automáticamente
 * - Transición visual natural: slide in/out
 * - No interrumpe el flujo del usuario
 *
 * Se accede vía:
 * GET /component/products-crud-v2/product-form-page?product_id=123&action=edit
 * GET /component/products-crud-v2/product-form-page?action=create
 */
#[ApiComponent('/products-crud-v2/product-form-page', methods: ['GET'])]
class ProductFormPageComponent extends CoreComponent
{
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    public function __construct() {}

    protected function component(): string
    {
        $this->CSS_PATHS[] = "./product-form-page.css";

        // Obtener parámetros de la query string
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        $action = $_GET['action'] ?? 'create';

        // Determinar si es crear o editar
        $isEdit = !is_null($productId) && $action === 'edit';
        $title = $isEdit ? "Editar Producto #$productId" : "Crear Nuevo Producto";
        $submitText = $isEdit ? "Guardar Cambios" : "Crear Producto";

        // Pre-llenar valores si es edición (obtener del servidor o dejar vacío para el cliente)
        $name = '';
        $sku = '';
        $price = '';
        $stock = '';
        $minStock = 5;
        $category = '';
        $description = '';
        $isActive = true;

        // Campos del formulario usando componentes LEGO
        $nameField = (new InputTextComponent(
            name: 'name',
            label: 'Nombre del Producto',
            placeholder: 'Ej: Laptop Dell XPS 13',
            value: $name,
            required: true
        ))->render();

        $skuField = (new InputTextComponent(
            name: 'sku',
            label: 'SKU',
            placeholder: 'Ej: LAPTOP-001',
            value: $sku,
            required: true
        ))->render();

        $priceField = (new InputTextComponent(
            name: 'price',
            label: 'Precio ($)',
            placeholder: '999.99',
            type: 'number',
            step: '0.01',
            value: $price,
            required: true
        ))->render();

        $stockField = (new InputTextComponent(
            name: 'stock',
            label: 'Stock Disponible',
            placeholder: '0',
            type: 'number',
            value: $stock,
            required: true
        ))->render();

        $minStockField = (new InputTextComponent(
            name: 'min_stock',
            label: 'Stock Mínimo',
            placeholder: '5',
            type: 'number',
            value: $minStock,
            required: true
        ))->render();

        $categoryField = (new SelectComponent(
            name: 'category',
            label: 'Categoría',
            options: [
                'electronics' => 'Electrónica',
                'computers' => 'Computadoras',
                'accessories' => 'Accesorios',
                'software' => 'Software',
                'other' => 'Otros'
            ],
            value: $category,
            required: true
        ))->render();

        $descriptionField = (new TextAreaComponent(
            name: 'description',
            label: 'Descripción',
            placeholder: 'Descripción detallada del producto...',
            rows: 4,
            value: $description
        ))->render();

        $isActiveField = (new SelectComponent(
            name: 'is_active',
            label: 'Estado',
            options: [
                '1' => '✓ Activo',
                '0' => '✗ Inactivo'
            ],
            value: $isActive ? '1' : '0',
            required: true
        ))->render();

        // Botones
        $submitButton = (new Button(
            text: $submitText,
            type: 'submit',
            variant: 'primary'
        ))->render();

        $cancelButton = (new Button(
            text: 'Cancelar',
            type: 'button',
            variant: 'secondary',
            onClick: 'closeFormPage()'
        ))->render();

        // ID para el form
        $formId = $isEdit ? "product-form-edit-$productId" : "product-form-create";

        return <<<HTML
        <div class="product-form-page">
            <div class="product-form-page-overlay" onclick="closeFormPage()"></div>

            <div class="product-form-page-content">
                <!-- Header de la página con botón cerrar -->
                <div class="product-form-page-header">
                    <div class="product-form-page-title">
                        <h2>$title</h2>
                    </div>
                    <button class="product-form-page-close" onclick="closeFormPage()" title="Cerrar">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>

                <!-- Formulario -->
                <form id="$formId" class="product-form-page-form">
                    <div class="product-form-page-scroll">
                        <!-- Sección básica -->
                        <fieldset class="product-form-section">
                            <legend>Información Básica</legend>
                            <div class="product-form-grid">
                                {$nameField}
                                {$skuField}
                            </div>
                        </fieldset>

                        <!-- Sección precios y stock -->
                        <fieldset class="product-form-section">
                            <legend>Precios y Stock</legend>
                            <div class="product-form-grid">
                                {$priceField}
                                {$stockField}
                                {$minStockField}
                            </div>
                        </fieldset>

                        <!-- Sección categoría y estado -->
                        <fieldset class="product-form-section">
                            <legend>Clasificación</legend>
                            <div class="product-form-grid">
                                {$categoryField}
                                {$isActiveField}
                            </div>
                        </fieldset>

                        <!-- Sección descripción -->
                        <fieldset class="product-form-section">
                            <legend>Descripción</legend>
                            {$descriptionField}
                        </fieldset>

                        <!-- Sección imágenes -->
                        <fieldset class="product-form-section">
                            <legend>Imágenes del Producto</legend>
                            <p class="product-form-help-text">
                                Arrastra imágenes o haz clic para seleccionar. Máximo 5 imágenes.
                            </p>
        HTML;

        // Agregar galería de imágenes solo si es edición (ya existe el producto)
        if ($isEdit && $productId) {
            $imageGallery = (new ImageGalleryComponent(
                id: "product-gallery-$productId",
                maxFiles: 5,
                maxFileSizeMB: 5,
                acceptedTypes: ['image/jpeg', 'image/png', 'image/webp']
            ))->render();

            $html .= $imageGallery;
        } else {
            $html .= <<<HTML
                            <div class="product-form-gallery-notice">
                                <ion-icon name="information-circle-outline"></ion-icon>
                                <p>Las imágenes se pueden agregar después de crear el producto</p>
                            </div>
            HTML;
        }

        $html .= <<<HTML
                        </fieldset>
                    </div>

                    <!-- Botones de acción -->
                    <div class="product-form-page-actions">
                        {$cancelButton}
                        {$submitButton}
                    </div>
                </form>
            </div>
        </div>
        HTML;

        return $html;
    }
}
