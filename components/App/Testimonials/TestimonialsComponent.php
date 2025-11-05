<?php
namespace Components\App\Testimonials;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\TableComponent\TableComponent;
use Components\Shared\Essentials\TableComponent\Collections\ColumnCollection;
use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;
use Components\Shared\Essentials\TableComponent\Collections\RowActionsCollection;
use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;
use Core\Types\DimensionValue;
use App\Models\Testimonial;

#[ApiComponent('/testimonials', methods: ['GET'])]
class TestimonialsComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./testimonials.css"];
    protected $JS_PATHS = ["./testimonials.js"];

    protected function component(): string
    {
        $columns = new ColumnCollection(
            new ColumnDto(
                field: "id",
                headerName: "ID",
                width: DimensionValue::px(80),
                sortable: true,
                filter: true,
                filterType: "number"
            ),
            new ColumnDto(
                field: "author",
                headerName: "Autor",
                width: DimensionValue::px(200),
                sortable: true,
                filter: true,
                filterType: "text"
            ),
            new ColumnDto(
                field: "message",
                headerName: "Testimonio",
                width: DimensionValue::flex(1),
                sortable: false,
                filter: true,
                filterType: "text",
                cellRenderer: 'params => {
                    const message = params.value || "";
                    const preview = message.length > 150 ? message.substring(0, 150) + "..." : message;
                    return `<div style="white-space: normal; line-height: 1.4; padding: 8px 0;">${preview}</div>`;
                }'
            ),
            new ColumnDto(
                field: "is_active",
                headerName: "Estado",
                width: DimensionValue::px(120),
                sortable: true,
                filter: true,
                cellRenderer: 'params => params.value ? \'<span style="color: #10b981; font-weight: 500;">Activo</span>\' : \'<span style="color: #ef4444; font-weight: 500;">Inactivo</span>\''
            ),
            new ColumnDto(
                field: "created_at",
                headerName: "Fecha",
                width: DimensionValue::px(150),
                sortable: true,
                filter: true,
                filterType: "date",
                valueFormatter: 'params => {
                    if (!params.value) return "";
                    const date = new Date(params.value);
                    return date.toLocaleDateString("es-ES", {day: "2-digit", month: "2-digit", year: "numeric"});
                }'
            )
        );

        $actions = new RowActionsCollection(
            new RowActionDto(
                id: "edit",
                label: "Editar",
                icon: "create-outline",
                callback: "handleEditTestimonial",
                variant: "primary",
                tooltip: "Editar testimonio"
            ),
            new RowActionDto(
                id: "delete",
                label: "Eliminar",
                icon: "trash-outline",
                callback: "handleDeleteTestimonial",
                variant: "danger",
                confirm: false,
                tooltip: "Eliminar testimonio"
            )
        );

        $table = new TableComponent(
            id: "testimonials-table",
            model: Testimonial::class,
            columns: $columns,
            rowActions: $actions,
            height: "600px",
            pagination: true,
            rowSelection: "multiple",
            noRowsMessage: "No hay testimonios registrados"
        );

        return <<<HTML
        <div class="testimonials">
            <div class="testimonials__header">
                <h1 class="testimonials__title">Testimonios de Clientes</h1>
                <button
                    class="testimonials__create-btn"
                    type="button"
                    onclick="openCreateTestimonialModule()"
                >
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Crear Testimonio</span>
                </button>
            </div>
            <div class="testimonials__table">
                {$table->render()}
            </div>
        </div>
        HTML;
    }
}
