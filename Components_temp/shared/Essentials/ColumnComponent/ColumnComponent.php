<?php
namespace Components\Shared\Essentials\ColumnComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * ColumnComponent - Layout vertical (Flexbox column)
 *
 * FILOSOFÍA LEGO:
 * Componente fundamental de layout que organiza children verticalmente.
 * Similar a Column en Flutter o flex-direction: column en CSS.
 *
 * CARACTERÍSTICAS:
 * - Organización vertical de children
 * - Gap configurable entre elementos
 * - Alineación horizontal (start, center, end, stretch)
 * - Alineación vertical (start, center, end, space-between, space-around)
 * - Scroll opcional (auto, vertical)
 * - Altura máxima configurable
 * - Padding configurable
 *
 * EJEMPLO BÁSICO:
 * new ColumnComponent(
 *     gap: "1rem",
 *     children: [
 *         new TitleComponent(...),
 *         new TextComponent(...),
 *         new ButtonComponent(...)
 *     ]
 * )
 *
 * EJEMPLO CON SCROLL:
 * new ColumnComponent(
 *     gap: "0.5rem",
 *     scroll: "auto",
 *     maxHeight: "400px",
 *     children: [
 *         ...array_map(fn($item) => new ItemComponent($item), $items)
 *     ]
 * )
 *
 * EJEMPLO CON ALINEACIÓN:
 * new ColumnComponent(
 *     gap: "2rem",
 *     alignItems: "center",      // Horizontalmente centrado
 *     justifyContent: "center",  // Verticalmente centrado
 *     minHeight: "100vh",
 *     children: [
 *         new LoginFormComponent(...)
 *     ]
 * )
 */
class ColumnComponent extends CoreComponent {

    protected $CSS_PATHS = ["./column.css"];

    public function __construct(
        public array $children = [],
        public string $gap = "0",                    // Espacio entre children
        public string $alignItems = "stretch",       // start, center, end, stretch
        public string $justifyContent = "start",     // start, center, end, space-between, space-around, space-evenly
        public string $padding = "0",                // Padding interno
        public string $scroll = "none",              // none, auto, scroll
        public string $maxHeight = "",               // Altura máxima (ej: "400px", "50vh")
        public string $minHeight = "",               // Altura mínima
        public string $width = "100%",               // Ancho (default 100%)
        public string $className = ""                // Clases CSS adicionales
    ) {}

    protected function component(): string {
        $childrenHtml = $this->renderChildren();

        // Construir clases CSS
        $classes = ["lego-column"];

        if ($this->scroll !== "none") {
            $classes[] = "lego-column--scroll-{$this->scroll}";
        }

        if ($this->className) {
            $classes[] = $this->className;
        }

        $classStr = implode(" ", $classes);

        // Construir estilos inline
        $styles = [];
        $styles[] = "gap: {$this->gap}";
        $styles[] = "align-items: {$this->alignItems}";
        $styles[] = "justify-content: {$this->justifyContent}";
        $styles[] = "padding: {$this->padding}";
        $styles[] = "width: {$this->width}";

        if ($this->maxHeight) {
            $styles[] = "max-height: {$this->maxHeight}";
        }

        if ($this->minHeight) {
            $styles[] = "min-height: {$this->minHeight}";
        }

        $styleStr = implode("; ", $styles);

        return <<<HTML
        <div class="{$classStr}" style="{$styleStr}">
            {$childrenHtml}
        </div>
        HTML;
    }
}
