<?php
namespace Components\Shared\Forms\SelectComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * SelectComponent - Selector dropdown con búsqueda y opciones dinámicas
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo para selects con soporte para búsqueda,
 * opciones múltiples, grupos y estilos personalizados.
 *
 * CARACTERÍSTICAS:
 * - Búsqueda en tiempo real
 * - Opciones agrupadas
 * - Selección múltiple opcional
 * - Estados: normal, error, disabled
 * - Type-safe con named arguments
 *
 * EJEMPLO:
 * new SelectComponent(
 *     id: "country",
 *     label: "País",
 *     options: [
 *         ["value" => "mx", "label" => "México"],
 *         ["value" => "us", "label" => "Estados Unidos"]
 *     ],
 *     searchable: true
 * )
 */
class SelectComponent extends CoreComponent {

    protected $CSS_PATHS = ["./select.css"];
    protected $JS_PATHS = ["./select.js"];

    public function __construct(
        public string $id,
        public array $options = [],
        public string $label = "",
        public string $placeholder = "Selecciona una opción",
        public string $selected = "",
        public array $selectedMultiple = [],
        public bool $multiple = false,
        public bool $searchable = false,
        public bool $required = false,
        public bool $disabled = false,
        public string $errorMessage = "",
        public string $helpText = "",
        public int $maxHeight = 300
    ) {}

    protected function component(): string {
        $containerClass = "lego-select";
        if ($this->disabled) $containerClass .= " lego-select--disabled";
        if ($this->errorMessage) $containerClass .= " lego-select--error";
        if ($this->searchable) $containerClass .= " lego-select--searchable";

        $labelHtml = $this->label ? "<label for=\"{$this->id}\" class=\"lego-select__label\">{$this->label}</label>" : "";

        $selectedLabel = $this->getSelectedLabel();
        $displayText = $selectedLabel ?: $this->placeholder;

        $searchHtml = "";
        if ($this->searchable) {
            $searchHtml = <<<HTML
            <input
                type="text"
                class="lego-select__search"
                placeholder="Buscar..."
                autocomplete="off"
            />
            HTML;
        }

        $optionsHtml = $this->renderOptions();

        $helpTextHtml = $this->helpText ? "<div class=\"lego-select__help\">{$this->helpText}</div>" : "";
        $errorHtml = $this->errorMessage ? "<div class=\"lego-select__error\">{$this->errorMessage}</div>" : "";

        $multipleAttr = $this->multiple ? "multiple" : "";
        $disabledAttr = $this->disabled ? "disabled" : "";

        $hiddenSelectOptionsHtml = $this->renderHiddenSelectOptions();

        return <<<HTML
        <div class="{$containerClass}" data-select-id="{$this->id}">
            {$labelHtml}

            <div class="lego-select__wrapper">
                <div class="lego-select__trigger" tabindex="0">
                    <span class="lego-select__value">{$displayText}</span>
                    <svg class="lego-select__arrow" width="12" height="8" viewBox="0 0 12 8" fill="none">
                        <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>

                <div class="lego-select__dropdown" style="max-height: {$this->maxHeight}px">
                    {$searchHtml}
                    <div class="lego-select__options">
                        {$optionsHtml}
                    </div>
                </div>

                <select
                    id="{$this->id}"
                    name="{$this->id}"
                    class="lego-select__native"
                    {$multipleAttr}
                    {$disabledAttr}
                    style="display: none;"
                >
                    {$hiddenSelectOptionsHtml}
                </select>
            </div>

            {$helpTextHtml}
            {$errorHtml}
        </div>
        HTML;
    }

    private function getSelectedLabel(): string {
        if ($this->multiple && !empty($this->selectedMultiple)) {
            $count = count($this->selectedMultiple);
            return "{$count} seleccionado" . ($count > 1 ? "s" : "");
        }

        if (!$this->selected) return "";

        foreach ($this->options as $option) {
            if (isset($option['options'])) {
                // Es un grupo
                foreach ($option['options'] as $subOption) {
                    if ($subOption['value'] === $this->selected) {
                        return $subOption['label'];
                    }
                }
            } else {
                if ($option['value'] === $this->selected) {
                    return $option['label'];
                }
            }
        }

        return "";
    }

    private function renderOptions(): string {
        $html = "";

        foreach ($this->options as $option) {
            if (isset($option['options'])) {
                // Es un grupo
                $html .= "<div class=\"lego-select__group\">";
                $html .= "<div class=\"lego-select__group-label\">{$option['label']}</div>";
                foreach ($option['options'] as $subOption) {
                    $html .= $this->renderOption($subOption);
                }
                $html .= "</div>";
            } else {
                $html .= $this->renderOption($option);
            }
        }

        return $html;
    }

    private function renderOption(array $option): string {
        $isSelected = false;

        if ($this->multiple) {
            $isSelected = in_array($option['value'], $this->selectedMultiple);
        } else {
            $isSelected = $option['value'] === $this->selected;
        }

        $selectedClass = $isSelected ? " lego-select__option--selected" : "";
        $checkmark = $isSelected ? '<span class="lego-select__checkmark">✓</span>' : "";

        return <<<HTML
        <div
            class="lego-select__option{$selectedClass}"
            data-value="{$option['value']}"
            role="option"
            aria-selected="{$isSelected}"
        >
            <span class="lego-select__option-label">{$option['label']}</span>
            {$checkmark}
        </div>
        HTML;
    }

    private function renderHiddenSelectOptions(): string {
        $html = "";

        foreach ($this->options as $option) {
            if (isset($option['options'])) {
                $html .= "<optgroup label=\"{$option['label']}\">";
                foreach ($option['options'] as $subOption) {
                    $selected = ($this->multiple && in_array($subOption['value'], $this->selectedMultiple)) ||
                               (!$this->multiple && $subOption['value'] === $this->selected) ? "selected" : "";
                    $html .= "<option value=\"{$subOption['value']}\" {$selected}>{$subOption['label']}</option>";
                }
                $html .= "</optgroup>";
            } else {
                $selected = ($this->multiple && in_array($option['value'], $this->selectedMultiple)) ||
                           (!$this->multiple && $option['value'] === $this->selected) ? "selected" : "";
                $html .= "<option value=\"{$option['value']}\" {$selected}>{$option['label']}</option>";
            }
        }

        return $html;
    }
}
