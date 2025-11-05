<?php
namespace Components\Shared\Forms\CheckboxComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * CheckboxComponent - Checkbox con label y estados
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo para checkboxes con soporte para estados,
 * validación y diseño personalizado.
 *
 * CARACTERÍSTICAS:
 * - Estados: normal, checked, disabled, indeterminate
 * - Label personalizable
 * - Descripción opcional
 * - Type-safe con named arguments
 *
 * EJEMPLO:
 * new CheckboxComponent(
 *     id: "terms",
 *     label: "Acepto los términos y condiciones",
 *     required: true
 * )
 */
class CheckboxComponent extends CoreComponent {

    protected $CSS_PATHS = ["./checkbox.css"];
    protected $JS_PATHS = ["./checkbox.js"];

    public function __construct(
        public string $id,
        public string $label,
        public string $value = "1",
        public bool $checked = false,
        public bool $required = false,
        public bool $disabled = false,
        public bool $indeterminate = false,
        public string $description = "",
        public string $errorMessage = ""
    ) {}

    protected function component(): string {
        $containerClass = "lego-checkbox";
        if ($this->disabled) $containerClass .= " lego-checkbox--disabled";
        if ($this->errorMessage) $containerClass .= " lego-checkbox--error";
        if ($this->indeterminate) $containerClass .= " lego-checkbox--indeterminate";

        $inputAttrs = [
            "type=\"checkbox\"",
            "id=\"{$this->id}\"",
            "name=\"{$this->id}\"",
            "class=\"lego-checkbox__input\"",
            "value=\"{$this->value}\""
        ];

        if ($this->checked) $inputAttrs[] = "checked";
        if ($this->required) $inputAttrs[] = "required";
        if ($this->disabled) $inputAttrs[] = "disabled";

        $inputAttrsStr = implode(" ", $inputAttrs);

        $descriptionHtml = $this->description ? "<div class=\"lego-checkbox__description\">{$this->description}</div>" : "";

        $errorHtml = $this->errorMessage ? "<div class=\"lego-checkbox__error\">{$this->errorMessage}</div>" : "";

        return <<<HTML
        <div class="{$containerClass}" data-checkbox-id="{$this->id}">
            <label class="lego-checkbox__wrapper">
                <input {$inputAttrsStr} />
                <span class="lego-checkbox__checkmark">
                    <svg class="lego-checkbox__icon lego-checkbox__icon--check" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M13.5 4L6 11.5L2.5 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg class="lego-checkbox__icon lego-checkbox__icon--indeterminate" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <div class="lego-checkbox__content">
                    <span class="lego-checkbox__label">{$this->label}</span>
                    {$descriptionHtml}
                </div>
            </label>
            {$errorHtml}
        </div>
        HTML;
    }
}
