<?php
namespace Components\Shared\Forms\ButtonComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * ButtonComponent - Botón con estados y variantes
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo para botones con soporte para loading,
 * diferentes variantes (primary, secondary, danger), iconos y tamaños.
 *
 * CARACTERÍSTICAS:
 * - Estados: normal, loading, disabled
 * - Variantes: primary, secondary, success, danger, ghost
 * - Tamaños: sm, md, lg
 * - Iconos opcionales
 * - Type-safe con named arguments
 *
 * EJEMPLO:
 * new ButtonComponent(
 *     text: "Guardar",
 *     variant: "primary",
 *     type: "submit",
 *     icon: "save-outline"
 * )
 */
class ButtonComponent extends CoreComponent {

    protected $CSS_PATHS = ["./button.css"];
    protected $JS_PATHS = ["./button.js"];

    public function __construct(
        public string $text,
        public string $id = "",
        public string $type = "button",
        public string $variant = "primary",
        public string $size = "md",
        public bool $disabled = false,
        public bool $loading = false,
        public bool $fullWidth = false,
        public string $icon = "",
        public string $iconPosition = "left",
        public string $onClick = "",
        public string $ariaLabel = ""
    ) {
        // Generar ID automático si no se proporciona
        if (empty($this->id)) {
            $this->id = "btn-" . uniqid();
        }
    }

    protected function component(): string {
        $classes = [
            "lego-button",
            "lego-button--{$this->variant}",
            "lego-button--{$this->size}"
        ];

        if ($this->disabled) $classes[] = "lego-button--disabled";
        if ($this->loading) $classes[] = "lego-button--loading";
        if ($this->fullWidth) $classes[] = "lego-button--full-width";

        $classStr = implode(" ", $classes);

        $disabledAttr = ($this->disabled || $this->loading) ? "disabled" : "";
        $ariaLabel = $this->ariaLabel ?: $this->text;

        $iconHtml = "";
        if ($this->icon && !$this->loading) {
            $iconHtml = "<ion-icon name=\"{$this->icon}\" class=\"lego-button__icon\"></ion-icon>";
        }

        $loaderHtml = "";
        if ($this->loading) {
            $loaderHtml = <<<HTML
            <span class="lego-button__loader">
                <span class="lego-button__spinner"></span>
            </span>
            HTML;
        }

        $contentHtml = "";
        if ($this->iconPosition === "left") {
            $contentHtml = $loaderHtml . $iconHtml . "<span class=\"lego-button__text\">{$this->text}</span>";
        } else {
            $contentHtml = $loaderHtml . "<span class=\"lego-button__text\">{$this->text}</span>" . $iconHtml;
        }

        $onClickAttr = $this->onClick ? "onclick=\"{$this->onClick}\"" : "";

        return <<<HTML
        <button
            id="{$this->id}"
            type="{$this->type}"
            class="{$classStr}"
            {$disabledAttr}
            {$onClickAttr}
            aria-label="{$ariaLabel}"
            data-button-id="{$this->id}"
        >
            {$contentHtml}
        </button>
        HTML;
    }
}
