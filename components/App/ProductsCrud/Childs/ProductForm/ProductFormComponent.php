<?php

namespace Components\App\ProductsCrud\Childs\ProductForm;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;

use Components\Shared\Forms\Forms\{
    Form,
    InputText,
    TextArea,
    Select,
    Checkbox
};

use Components\Shared\Essentials\Essentials\{
    Row,
    Column
};

/**
 * ProductFormComponent - Formulario para crear/editar productos
 *
 * FILOSOFÍA LEGO:
 * Componente hijo de ProductsCrud que renderiza el formulario de producto
 * usando componentes LEGO nativos. Se carga dinámicamente via AlertService.componentModal()
 */
#[ApiComponent('/products-crud/product-form', methods: ['GET'])]
class ProductFormComponent extends CoreComponent
{
    public function __construct($params = []) {
        // Constructor acepta parámetros opcionales para compatibilidad con ApiRouteDiscovery
    }

    protected function component(): string
    {
        $productId = $_GET['id'] ?? null;
        $isEdit = !empty($productId);

        // En producción, cargarías los datos del producto desde la BD
        $product = [];
        if ($isEdit) {
            // TODO: Cargar producto desde BD
            // $product = Product::find($productId);
        }

        // Categorías disponibles
        $categoryOptions = [
            ["value" => "electronics", "label" => "Electrónica"],
            ["value" => "clothing", "label" => "Ropa"],
            ["value" => "food", "label" => "Alimentos"],
            ["value" => "books", "label" => "Libros"],
            ["value" => "toys", "label" => "Juguetes"],
            ["value" => "sports", "label" => "Deportes"],
            ["value" => "other", "label" => "Otros"]
        ];

        // Construir formulario usando componentes LEGO
        return (new Form(
            id: 'product-form',
            title: $isEdit ? 'Editar Producto' : 'Nuevo Producto',
            description: $isEdit ? 'Actualiza la información del producto' : 'Completa los datos del nuevo producto',
            onSubmit: 'return false;', // Manejado por AlertService
            children: [
                // Campo oculto para el ID (solo en modo edición)
                $isEdit ? "<input type=\"hidden\" name=\"id\" value=\"{$productId}\" />" : null,

                // Layout: Row con gap para nombre y SKU lado a lado
                new Row(
                    gap: "1rem",
                    children: [
                        new InputText(
                            id: 'name',
                            label: 'Nombre del Producto',
                            placeholder: 'Ej: Laptop HP Pavilion',
                            value: $product['name'] ?? '',
                            required: true,
                            icon: 'cube-outline',
                            maxLength: 100,
                            showCounter: true
                        ),

                        new InputText(
                            id: 'sku',
                            label: 'SKU',
                            placeholder: 'Ej: PROD-001',
                            value: $product['sku'] ?? '',
                            required: true,
                            icon: 'barcode-outline',
                            helpText: 'Código único del producto'
                        )
                    ]
                ),

                // Descripción (ancho completo)
                new TextArea(
                    id: 'description',
                    label: 'Descripción',
                    placeholder: 'Descripción detallada del producto...',
                    value: $product['description'] ?? '',
                    rows: 3,
                    maxLength: 500,
                    showCounter: true,
                    autoResize: true
                ),

                // Layout: Row para categoría y precio
                new Row(
                    gap: "1rem",
                    children: [
                        new Select(
                            id: 'category',
                            label: 'Categoría',
                            placeholder: 'Selecciona una categoría',
                            options: $categoryOptions,
                            selected: $product['category'] ?? '',
                            required: true,
                            searchable: true
                        ),

                        new InputText(
                            id: 'price',
                            label: 'Precio',
                            type: 'number',
                            placeholder: '0.00',
                            value: $product['price'] ?? '',
                            required: true,
                            icon: 'cash-outline',
                            helpText: 'Precio en USD'
                        )
                    ]
                ),

                // Layout: Row para stock y stock mínimo
                new Row(
                    gap: "1rem",
                    children: [
                        new InputText(
                            id: 'stock',
                            label: 'Stock',
                            type: 'number',
                            placeholder: '0',
                            value: $product['stock'] ?? '',
                            required: true,
                            icon: 'layers-outline',
                            helpText: 'Cantidad disponible'
                        ),

                        new InputText(
                            id: 'min_stock',
                            label: 'Stock Mínimo',
                            type: 'number',
                            placeholder: '0',
                            value: $product['min_stock'] ?? '5',
                            required: true,
                            icon: 'alert-circle-outline',
                            helpText: 'Alerta de stock bajo'
                        )
                    ]
                ),

                // Checkbox para producto activo
                new Checkbox(
                    id: 'is_active',
                    label: 'Producto activo',
                    description: 'Los productos activos son visibles para los clientes',
                    checked: $product['is_active'] ?? true
                )
            ]
        ))->render();
    }
}
