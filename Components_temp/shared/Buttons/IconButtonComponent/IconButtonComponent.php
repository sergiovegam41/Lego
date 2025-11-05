<?php

namespace Components\Shared\Buttons\IconButtonComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Interfaces\DynamicComponentInterface;
use Core\Components\ComponentRegistry;

/**
 * IconButtonComponent - Botón de ícono reutilizable con soporte dinámico
 *
 * FILOSOFÍA LEGO:
 * Componente atómico para botones de acción con íconos.
 * Soporta renderizado dinámico desde JavaScript vía API.
 *
 * USO ESTÁTICO (PHP):
 * ```php
 * $button = new IconButtonComponent(
 *     icon: "create-outline",
 *     variant: "primary",
 *     onClick: "editProduct(14)",
 *     title: "Editar producto"
 * );
 * echo $button->render();
 * ```
 *
 * USO DINÁMICO (JavaScript):
 * ```javascript
 * const buttons = await window.lego.components
 *     .get('icon-button')
 *     .params([
 *         { icon: 'create-outline', variant: 'primary', onClick: 'editProduct(1)' },
 *         { icon: 'trash-outline', variant: 'danger', onClick: 'deleteProduct(1)' }
 *     ]);
 * ```
 */
class IconButtonComponent extends CoreComponent implements DynamicComponentInterface
{
    /**
     * ID único para renderizado dinámico
     */
    public const COMPONENT_ID = 'icon-button';
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

        // Auto-registro en ComponentRegistry (primera instancia)
        if (!ComponentRegistry::isRegistered(self::COMPONENT_ID)) {
            ComponentRegistry::register(self::COMPONENT_ID, self::class);
        }
    }

    /**
     * Implementación de DynamicComponentInterface
     */
    public static function getComponentId(): string
    {
        return self::COMPONENT_ID;
    }

    /**
     * Renderizar con parámetros dinámicos (llamado por ComponentRegistry)
     *
     * @param array $params Parámetros del componente
     * @return string HTML renderizado
     */
    public function renderWithParams(array $params): string
    {
        // Mapear parámetros a propiedades
        $this->icon = $params['icon'] ?? $this->icon;
        $this->size = $params['size'] ?? $this->size;
        $this->variant = $params['variant'] ?? $this->variant;
        $this->onClick = $params['onClick'] ?? $this->onClick;
        $this->title = $params['title'] ?? $this->title;
        $this->disabled = $params['disabled'] ?? $this->disabled;
        $this->className = $params['className'] ?? $this->className;
        $this->id = $params['id'] ?? $this->id;
        $this->ariaLabel = $params['ariaLabel'] ?? $this->ariaLabel;

        // Renderizar usando el método component() existente
        return $this->component();
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
