<?php

namespace Components\Shared\Buttons\IconButtonComponent;

use Core\Components\CoreComponent\CoreComponent;

class IconButtonComponent extends CoreComponent
{
    public function __construct(
        public string $icon = "",                    // Ion icon name (e.g., "reload-outline")
        public string $size = "medium",              // small, medium, large
        public string $variant = "ghost",            // primary, secondary, ghost, danger
        public string $onClick = "",                 // JavaScript function to call
        public string $title = "",                   // Tooltip text
        public bool $disabled = false,               // Disabled state
        public string $className = "",               // Additional CSS classes
        public string $id = "",                      // Optional ID
        public string $ariaLabel = "",              // Accessibility label
    ) {
        $this->CSS_PATHS = ["./icon-button.css"];
        $this->JS_PATHS = ["./icon-button.js"];
    }

    protected function component(): string
    {
        $classes = ["lego-icon-button"];
        $classes[] = "lego-icon-button--{$this->size}";
        $classes[] = "lego-icon-button--{$this->variant}";

        if ($this->disabled) {
            $classes[] = "lego-icon-button--disabled";
        }

        if ($this->className) {
            $classes[] = $this->className;
        }

        $classStr = implode(" ", $classes);
        $idAttr = $this->id ? "id=\"{$this->id}\"" : "";
        $disabledAttr = $this->disabled ? "disabled" : "";
        $onClickAttr = $this->onClick && !$this->disabled ? "onclick=\"{$this->onClick}\"" : "";
        $titleAttr = $this->title ? "title=\"{$this->title}\"" : "";
        $ariaLabelAttr = $this->ariaLabel ?: $this->title;
        $ariaAttr = $ariaLabelAttr ? "aria-label=\"{$ariaLabelAttr}\"" : "";

        return <<<HTML
<button
    type="button"
    class="{$classStr}"
    {$idAttr}
    {$onClickAttr}
    {$titleAttr}
    {$ariaAttr}
    {$disabledAttr}
>
    <ion-icon name="{$this->icon}"></ion-icon>
</button>
HTML;
    }
}
