<?php
namespace Components\Shared\Forms\FormActionsComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * FormActionsComponent - Wrapper para botones de acción del formulario
 *
 * FILOSOFÍA LEGO:
 * Contenedor declarativo para organizar botones de acción (submit, cancel, etc.)
 * con diferentes layouts predefinidos.
 *
 * CARACTERÍSTICAS:
 * - Acepta array de children (típicamente ButtonComponents)
 * - Múltiples layouts: end, start, between, center, stretch
 * - Responsive por defecto
 * - Elimina concatenación manual de botones
 *
 * EJEMPLO:
 * new FormActionsComponent(
 *     layout: "between",
 *     children: [
 *         new ButtonComponent(text: "Cancelar", variant: "secondary"),
 *         new ButtonComponent(text: "Guardar", variant: "primary", type: "submit")
 *     ]
 * )
 */
class FormActionsComponent extends CoreComponent {

    public function __construct(
        public string $layout = "end", // end, start, between, center, stretch
        public array $children = []
    ) {}

    protected function component(): string {
        $childrenHtml = $this->renderChildren();

        return <<<HTML
        <div class="lego-form__actions lego-form__actions--{$this->layout}">
            {$childrenHtml}
        </div>
        HTML;
    }
}
