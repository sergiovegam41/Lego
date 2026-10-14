<?php
namespace Components\Shared\Forms\FormRowComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * FormRowComponent - Wrapper para layout horizontal de campos
 *
 * FILOSOFÍA LEGO:
 * Contenedor que organiza campos en una fila horizontal con distribución
 * automática del espacio. Útil para formularios compactos.
 *
 * CARACTERÍSTICAS:
 * - Acepta array de children
 * - Gap configurable entre elementos
 * - Responsive (se apila en móviles)
 * - Distribución equitativa o proporcional
 *
 * EJEMPLO:
 * new FormRowComponent(
 *     gap: "1rem",
 *     children: [
 *         new InputTextComponent(id: "firstName", label: "Nombre"),
 *         new InputTextComponent(id: "lastName", label: "Apellido")
 *     ]
 * )
 */
class FormRowComponent extends CoreComponent {

    protected $CSS_PATHS = ["./form-row.css"];

    public function __construct(
        public string $gap = "1rem",
        public array $children = []
    ) {}

    protected function component(): string {
        $childrenHtml = $this->renderChildren();
        $style = "gap: {$this->gap}";

        return <<<HTML
        <div class="lego-form__row" style="{$style}">
            {$childrenHtml}
        </div>
        HTML;
    }
}
