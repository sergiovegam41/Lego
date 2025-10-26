<?php
namespace Components\Shared\Forms\TextAreaComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * TextAreaComponent - Campo de texto multilínea con validación
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo para textareas con soporte para contador de caracteres,
 * auto-resize, validación y estados visuales.
 *
 * CARACTERÍSTICAS:
 * - Contador de caracteres opcional
 * - Auto-resize opcional
 * - Validación en tiempo real
 * - Estados: normal, error, disabled
 * - Type-safe con named arguments
 *
 * EJEMPLO:
 * new TextAreaComponent(
 *     id: "description",
 *     label: "Descripción",
 *     placeholder: "Escribe tu descripción",
 *     maxLength: 500,
 *     showCounter: true,
 *     autoResize: true
 * )
 */
class TextAreaComponent extends CoreComponent {

    protected $CSS_PATHS = ["./textarea.css"];
    protected $JS_PATHS = ["./textarea.js"];

    public function __construct(
        public string $id,
        public string $label = "",
        public string $placeholder = "",
        public string $value = "",
        public int $rows = 4,
        public int $maxLength = 0,
        public int $minLength = 0,
        public bool $required = false,
        public bool $disabled = false,
        public bool $readonly = false,
        public bool $showCounter = false,
        public bool $autoResize = false,
        public string $errorMessage = "",
        public string $helpText = ""
    ) {}

    protected function component(): string {
        $containerClass = "lego-textarea";
        if ($this->disabled) $containerClass .= " lego-textarea--disabled";
        if ($this->errorMessage) $containerClass .= " lego-textarea--error";
        if ($this->autoResize) $containerClass .= " lego-textarea--auto-resize";

        $textareaAttrs = [
            "id=\"{$this->id}\"",
            "name=\"{$this->id}\"",
            "class=\"lego-textarea__field\"",
            "placeholder=\"{$this->placeholder}\"",
            "rows=\"{$this->rows}\""
        ];

        if ($this->maxLength > 0) $textareaAttrs[] = "maxlength=\"{$this->maxLength}\"";
        if ($this->minLength > 0) $textareaAttrs[] = "minlength=\"{$this->minLength}\"";
        if ($this->required) $textareaAttrs[] = "required";
        if ($this->disabled) $textareaAttrs[] = "disabled";
        if ($this->readonly) $textareaAttrs[] = "readonly";

        $textareaAttrsStr = implode(" ", $textareaAttrs);

        $counterHtml = "";
        if ($this->showCounter && $this->maxLength > 0) {
            $currentLength = strlen($this->value);
            $counterHtml = "<div class=\"lego-textarea__counter\" data-counter-for=\"{$this->id}\">{$currentLength}/{$this->maxLength}</div>";
        }

        $labelHtml = $this->label ? "<label for=\"{$this->id}\" class=\"lego-textarea__label\">{$this->label}</label>" : "";

        $helpTextHtml = $this->helpText ? "<div class=\"lego-textarea__help\">{$this->helpText}</div>" : "";

        $errorHtml = $this->errorMessage ? "<div class=\"lego-textarea__error\">{$this->errorMessage}</div>" : "";

        return <<<HTML
        <div class="{$containerClass}" data-textarea-id="{$this->id}">
            {$labelHtml}
            <div class="lego-textarea__wrapper">
                <textarea {$textareaAttrsStr}>{$this->value}</textarea>
            </div>
            {$counterHtml}
            {$helpTextHtml}
            {$errorHtml}
        </div>
        HTML;
    }
}
