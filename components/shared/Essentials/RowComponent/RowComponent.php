<?php
namespace Components\Shared\Essentials\RowComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * RowComponent - Layout horizontal (Flexbox row)
 *
 * FILOSOFÍA LEGO:
 * Componente fundamental de layout que organiza children horizontalmente.
 * Similar a Row en Flutter o flex-direction: row en CSS.
 *
 * CARACTERÍSTICAS:
 * - Organización horizontal de children
 * - Gap configurable entre elementos
 * - Alineación vertical (start, center, end, stretch)
 * - Alineación horizontal (start, center, end, space-between, space-around)
 * - Scroll opcional (auto, horizontal)
 * - Wrap configurable (wrap, nowrap)
 * - Ancho máximo configurable
 * - Padding configurable
 *
 * EJEMPLO BÁSICO:
 * new RowComponent(
 *     gap: "1rem",
 *     children: [
 *         new ButtonComponent(...),
 *         new ButtonComponent(...),
 *         new ButtonComponent(...)
 *     ]
 * )
 *
 * EJEMPLO CON SCROLL:
 * new RowComponent(
 *     gap: "0.5rem",
 *     scroll: "auto",
 *     maxWidth: "600px",
 *     children: [
 *         ...array_map(fn($tag) => new TagComponent($tag), $tags)
 *     ]
 * )
 *
 * EJEMPLO CON ALINEACIÓN:
 * new RowComponent(
 *     gap: "2rem",
 *     alignItems: "center",       // Verticalmente centrado
 *     justifyContent: "between",  // Espaciado entre elementos
 *     children: [
 *         new LogoComponent(),
 *         new NavigationComponent()
 *     ]
 * )
 *
 * EJEMPLO CON WRAP:
 * new RowComponent(
 *     gap: "1rem",
 *     wrap: "wrap",
 *     children: [
 *         ...array_map(fn($item) => new CardComponent($item), $items)
 *     ]
 * )
 */
class RowComponent extends CoreComponent {

    protected $CSS_PATHS = ["./row.css"];

    public function __construct(
        public array $children = [],
        public string $gap = "0",                    // Espacio entre children
        public string $alignItems = "stretch",       // start, center, end, stretch, baseline
        public string $justifyContent = "start",     // start, center, end, between, around, evenly
        public string $padding = "0",                // Padding interno
        public string $scroll = "none",              // none, auto, scroll
        public string $wrap = "nowrap",              // wrap, nowrap
        public string $maxWidth = "",                // Ancho máximo (ej: "600px", "100%")
        public string $minWidth = "",                // Ancho mínimo
        public string $height = "auto",              // Altura (default auto)
        public string $className = ""                // Clases CSS adicionales
    ) {}

    protected function component(): string {
        $childrenHtml = $this->renderChildren();

        // Construir clases CSS
        $classes = ["lego-row"];

        if ($this->scroll !== "none") {
            $classes[] = "lego-row--scroll-{$this->scroll}";
        }

        if ($this->wrap === "wrap") {
            $classes[] = "lego-row--wrap";
        }

        if ($this->className) {
            $classes[] = $this->className;
        }

        $classStr = implode(" ", $classes);

        // Construir estilos inline
        $styles = [];
        $styles[] = "gap: {$this->gap}";
        $styles[] = "align-items: {$this->alignItems}";

        // Mapear justifyContent a valores CSS
        $justifyMap = [
            "start" => "flex-start",
            "end" => "flex-end",
            "center" => "center",
            "between" => "space-between",
            "around" => "space-around",
            "evenly" => "space-evenly"
        ];
        $justifyValue = $justifyMap[$this->justifyContent] ?? $this->justifyContent;
        $styles[] = "justify-content: {$justifyValue}";

        $styles[] = "padding: {$this->padding}";
        $styles[] = "height: {$this->height}";

        if ($this->maxWidth) {
            $styles[] = "max-width: {$this->maxWidth}";
        }

        if ($this->minWidth) {
            $styles[] = "min-width: {$this->minWidth}";
        }

        $styleStr = implode("; ", $styles);

        return <<<HTML
        <div class="{$classStr}" style="{$styleStr}">
            {$childrenHtml}
        </div>
        HTML;
    }
}
