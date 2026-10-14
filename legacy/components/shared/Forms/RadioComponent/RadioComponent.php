<?php
namespace Components\Shared\Forms\RadioComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * RadioComponent - Radio button con label y estados
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo para radio buttons con soporte para grupos,
 * validación y diseño personalizado.
 *
 * CARACTERÍSTICAS:
 * - Agrupación automática por name
 * - Estados: normal, checked, disabled
 * - Descripción opcional
 * - Type-safe con named arguments
 *
 * EJEMPLO:
 * new RadioComponent(
 *     id: "payment-card",
 *     name: "payment-method",
 *     label: "Tarjeta de crédito",
 *     value: "card",
 *     checked: true
 * )
 */
class RadioComponent extends CoreComponent {

    protected $CSS_PATHS = ["./radio.css"];
    protected $JS_PATHS = ["./radio.js"];

    public function __construct(
        public string $id,
        public string $name,
        public string $label,
        public string $value,
        public bool $checked = false,
        public bool $required = false,
        public bool $disabled = false,
        public string $description = "",
        public string $errorMessage = ""
    ) {}

    protected function component(): string {
        $containerClass = "lego-radio";
        if ($this->disabled) $containerClass .= " lego-radio--disabled";
        if ($this->errorMessage) $containerClass .= " lego-radio--error";

        $inputAttrs = [
            "type=\"radio\"",
            "id=\"{$this->id}\"",
            "name=\"{$this->name}\"",
            "class=\"lego-radio__input\"",
            "value=\"{$this->value}\""
        ];

        if ($this->checked) $inputAttrs[] = "checked";
        if ($this->required) $inputAttrs[] = "required";
        if ($this->disabled) $inputAttrs[] = "disabled";

        $inputAttrsStr = implode(" ", $inputAttrs);

        $descriptionHtml = $this->description ? "<div class=\"lego-radio__description\">{$this->description}</div>" : "";

        $errorHtml = $this->errorMessage ? "<div class=\"lego-radio__error\">{$this->errorMessage}</div>" : "";

        return <<<HTML
        <div class="{$containerClass}" data-radio-id="{$this->id}" data-radio-group="{$this->name}">
            <label class="lego-radio__wrapper">
                <input {$inputAttrsStr} />
                <span class="lego-radio__checkmark">
                    <span class="lego-radio__dot"></span>
                </span>
                <div class="lego-radio__content">
                    <span class="lego-radio__label">{$this->label}</span>
                    {$descriptionHtml}
                </div>
            </label>
            {$errorHtml}
        </div>
        HTML;
    }
}
