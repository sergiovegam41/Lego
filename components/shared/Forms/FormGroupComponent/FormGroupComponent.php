<?php
namespace Components\Shared\Forms\FormGroupComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * FormGroupComponent - Wrapper para agrupar campos relacionados
 *
 * FILOSOFÍA LEGO:
 * Contenedor declarativo que agrupa campos relacionados con título y descripción.
 * Acepta children components, eliminando la necesidad de concatenación manual.
 *
 * CARACTERÍSTICAS:
 * - Acepta array de children components
 * - Título y descripción opcionales
 * - Renderiza automáticamente todos sus hijos
 * - Estilo visual consistente con bordes y padding
 *
 * EJEMPLO:
 * new FormGroupComponent(
 *     title: "Género",
 *     description: "Selecciona tu género",
 *     children: [
 *         new RadioComponent(id: "male", name: "gender", label: "Masculino"),
 *         new RadioComponent(id: "female", name: "gender", label: "Femenino"),
 *         new RadioComponent(id: "other", name: "gender", label: "Otro")
 *     ]
 * )
 */
class FormGroupComponent extends CoreComponent {

    public function __construct(
        public string $title = "",
        public string $description = "",
        public array $children = []
    ) {}

    protected function component(): string {
        $titleHtml = $this->title
            ? "<div class=\"lego-form__group-title\">{$this->title}</div>"
            : "";

        $descriptionHtml = $this->description
            ? "<div class=\"lego-form__group-description\">{$this->description}</div>"
            : "";

        $childrenHtml = $this->renderChildren();

        return <<<HTML
        <div class="lego-form__group">
            {$titleHtml}
            {$descriptionHtml}
            {$childrenHtml}
        </div>
        HTML;
    }
}
