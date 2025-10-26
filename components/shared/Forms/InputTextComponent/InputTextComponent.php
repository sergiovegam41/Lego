<?php
namespace Components\Shared\Forms\InputTextComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * InputTextComponent - Campo de texto con validación y estilos Lego
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo para inputs de texto con soporte para validación,
 * contador de caracteres, iconos y diferentes estados visuales.
 *
 * CARACTERÍSTICAS:
 * - Validación en tiempo real
 * - Contador de caracteres opcional
 * - Soporte para iconos
 * - Estados: normal, error, success, disabled
 * - Type-safe con named arguments
 *
 * EJEMPLO:
 * new InputTextComponent(
 *     id: "username",
 *     label: "Nombre de usuario",
 *     placeholder: "Ingresa tu nombre",
 *     maxLength: 50,
 *     required: true,
 *     showCounter: true
 * )
 */
class InputTextComponent extends CoreComponent {

    protected $CSS_PATHS = ["./input-text.css"];
    protected $JS_PATHS = ["./input-text.js"];

    public function __construct(
        public string $id,
        public string $label = "",
        public string $placeholder = "",
        public string $value = "",
        public string $type = "text",
        public int $maxLength = 0,
        public int $minLength = 0,
        public bool $required = false,
        public bool $disabled = false,
        public bool $readonly = false,
        public bool $showCounter = false,
        public string $pattern = "",
        public string $errorMessage = "",
        public string $helpText = "",
        public string $icon = "",
        public string $autocomplete = "off"
    ) {}

    protected function component(): string {
        $containerClass = "lego-input-text";
        if ($this->disabled) $containerClass .= " lego-input-text--disabled";
        if ($this->errorMessage) $containerClass .= " lego-input-text--error";

        $inputAttrs = [
            "type=\"{$this->type}\"",
            "id=\"{$this->id}\"",
            "name=\"{$this->id}\"",
            "class=\"lego-input-text__field\"",
            "placeholder=\"{$this->placeholder}\"",
            "value=\"{$this->value}\"",
            "autocomplete=\"{$this->autocomplete}\""
        ];

        if ($this->maxLength > 0) $inputAttrs[] = "maxlength=\"{$this->maxLength}\"";
        if ($this->minLength > 0) $inputAttrs[] = "minlength=\"{$this->minLength}\"";
        if ($this->required) $inputAttrs[] = "required";
        if ($this->disabled) $inputAttrs[] = "disabled";
        if ($this->readonly) $inputAttrs[] = "readonly";
        if ($this->pattern) $inputAttrs[] = "pattern=\"{$this->pattern}\"";

        $inputAttrsStr = implode(" ", $inputAttrs);

        $iconHtml = $this->icon ? "<span class=\"lego-input-text__icon\"><ion-icon name=\"{$this->icon}\"></ion-icon></span>" : "";

        $counterHtml = "";
        if ($this->showCounter && $this->maxLength > 0) {
            $currentLength = strlen($this->value);
            $counterHtml = "<div class=\"lego-input-text__counter\" data-counter-for=\"{$this->id}\">{$currentLength}/{$this->maxLength}</div>";
        }

        $labelHtml = $this->label ? "<label for=\"{$this->id}\" class=\"lego-input-text__label\">{$this->label}</label>" : "";

        $helpTextHtml = $this->helpText ? "<div class=\"lego-input-text__help\">{$this->helpText}</div>" : "";

        $errorHtml = $this->errorMessage ? "<div class=\"lego-input-text__error\">{$this->errorMessage}</div>" : "";

        return <<<HTML
        <div class="{$containerClass}" data-input-id="{$this->id}">
            {$labelHtml}
            <div class="lego-input-text__wrapper">
                {$iconHtml}
                <input {$inputAttrsStr} />
            </div>
            {$counterHtml}
            {$helpTextHtml}
            {$errorHtml}
        </div>
        HTML;
    }
}
