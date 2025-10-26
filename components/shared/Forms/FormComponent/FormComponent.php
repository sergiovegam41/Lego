<?php
namespace Components\Shared\Forms\FormComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * FormComponent - Contenedor de formulario con validación
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo que envuelve elementos de formulario,
 * proporciona validación y manejo de eventos.
 *
 * CARACTERÍSTICAS:
 * - Validación automática
 * - Prevención de doble submit
 * - Manejo de loading state
 * - Eventos custom (submit, validation)
 * - Type-safe con named arguments
 *
 * EJEMPLO:
 * new FormComponent(
 *     id: "contact-form",
 *     action: "/api/contact",
 *     method: "POST",
 *     content: $inputsAndButtons
 * )
 */
class FormComponent extends CoreComponent {

    protected $CSS_PATHS = ["./form.css"];
    protected $JS_PATHS = ["./form.js"];

    public function __construct(
        public string $id,
        public string $content,
        public string $action = "",
        public string $method = "POST",
        public string $title = "",
        public string $description = "",
        public bool $noValidate = false,
        public bool $autocomplete = true,
        public string $onSubmit = "",
        public string $layout = "vertical" // vertical, horizontal, inline
    ) {}

    protected function component(): string {
        $formClass = "lego-form lego-form--{$this->layout}";

        $formAttrs = [
            "id=\"{$this->id}\"",
            "class=\"{$formClass}\"",
            "method=\"{$this->method}\"",
            "data-form-id=\"{$this->id}\""
        ];

        if ($this->action) $formAttrs[] = "action=\"{$this->action}\"";
        if ($this->noValidate) $formAttrs[] = "novalidate";
        if (!$this->autocomplete) $formAttrs[] = "autocomplete=\"off\"";
        if ($this->onSubmit) $formAttrs[] = "onsubmit=\"{$this->onSubmit}\"";

        $formAttrsStr = implode(" ", $formAttrs);

        $headerHtml = "";
        if ($this->title || $this->description) {
            $titleHtml = $this->title ? "<h2 class=\"lego-form__title\">{$this->title}</h2>" : "";
            $descriptionHtml = $this->description ? "<p class=\"lego-form__description\">{$this->description}</p>" : "";
            $headerHtml = <<<HTML
            <div class="lego-form__header">
                {$titleHtml}
                {$descriptionHtml}
            </div>
            HTML;
        }

        return <<<HTML
        <form {$formAttrsStr}>
            {$headerHtml}
            <div class="lego-form__body">
                {$this->content}
            </div>
            <div class="lego-form__messages" style="display: none;">
                <div class="lego-form__success" style="display: none;"></div>
                <div class="lego-form__error" style="display: none;"></div>
            </div>
        </form>
        HTML;
    }
}
