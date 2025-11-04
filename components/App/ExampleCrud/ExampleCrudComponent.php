<?php
namespace Components\App\ExampleCrud;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Core\Types\DimensionValue;
use App\Models\ExampleCrud;

/**
 * ExampleCrudComponent - Vista de tabla (CRUD de Ejemplo)
 *
 * FILOSOFÍA LEGO:
 * Componente de ejemplo/template que demuestra CRUD completo.
 * Enfocado ÚNICAMENTE en mostrar la tabla - separación de responsabilidades.
 * Sirve como referencia para construir otros CRUDs en el framework.
 *
 * CARACTERÍSTICAS:
 * ✅ Model-driven con server-side pagination automática
 * ✅ RowActions integradas (edit, delete con callbacks)
 * ✅ Anchos con DimensionValue (proporciones consistentes)
 * ✅ Sin duplicación de definiciones
 * ✅ Navegación usando módulos (no window.location.href)
 * ✅ Theming correcto (html.dark, no @media)
 * ✅ Separación clara: 1 componente = 1 responsabilidad
 *
 * MODEL-DRIVEN:
 * - Pasa ExampleCrud::class y todo se configura automáticamente
 * - Server-side pagination desde /api/get/example-crud
 * - RowActions con callbacks personalizados (handleEdit, handleDelete)
 * - Sin necesidad de cargar datos en PHP (lazy loading)
 */
#[ApiComponent('/example-crud', methods: ['GET'])]
class ExampleCrudComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./example-crud.css"];
    protected $JS_PATHS = ["./example-crud.js"];

    protected function component(): string
    {
        // Definición de columnas
        $columns = new ColumnCollection(
            new ColumnDto(
                field: "id",
                headerName: "ID",
                width: DimensionValue::px(100),
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            new ColumnDto(
                field: "name",
                headerName: "Nombre",
                width: DimensionValue::px(200),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "sku",
                headerName: "SKU",
                width: DimensionValue::px(120),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "description",
                headerName: "Descripción",
                width: DimensionValue::px(250),
                sortable: false,
                filter: false
            ),
            new ColumnDto(
                field: "price",
                headerName: "Precio",
                width: DimensionValue::px(120),
                sortable: true,
                filter: true,
                filterType: "number",
                valueFormatter: "params => '$' + parseFloat(params.value).toFixed(2)"
            ),
            new ColumnDto(
                field: "stock",
                headerName: "Stock",
                width: DimensionValue::px(100),
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            new ColumnDto(
                field: "category",
                headerName: "Categoría",
                width: DimensionValue::px(999),
                sortable: true,
                filter: true
            )
        );

        // Definir acciones de fila con callbacks personalizados
        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditRecord",
                variant: "primary",
                tooltip: "Editar registro"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteRecord",
                variant: "danger",
                confirm: false, // Desactivado: usamos ConfirmationService en el callback
                tooltip: "Eliminar registro"
            )
        );

        // ✨ MAGIA: Tabla model-driven con server-side pagination
        $table = new TableComponent(
            id: "example-crud-table",
            model: ExampleCrud::class,  // ← Auto-configura API y paginación
            columns: $columns,
            rowActions: $actions,       // ← Acciones integradas
            height: "600px",
            pagination: true,           // Server-side automático
            rowSelection: "multiple"
        );

        return <<<HTML
        <div class="example-crud">
            <!-- Header con botón crear -->
            <div class="example-crud__header">
                <h1 class="example-crud__title">Example CRUD</h1>
                <button
                    class="example-crud__create-btn"
                    id="example-crud-create-btn"
                    type="button"
                    onclick="openModule('example-crud-create', '/component/example-crud/create', 'Crear Registro', null)"
                >
                    <svg class="example-crud__create-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Crear Registro
                </button>
            </div>

            <!-- Tabla de registros -->
            <div class="example-crud__table">
                {$table->render()}
            </div>
        </div>
        HTML;
    }
}
