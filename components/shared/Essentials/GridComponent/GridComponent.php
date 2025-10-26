<?php
namespace Components\Shared\Essentials\GridComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * GridComponent - Layout de cuadrícula (CSS Grid)
 *
 * FILOSOFÍA LEGO:
 * Componente fundamental que organiza children en una cuadrícula flexible
 * usando CSS Grid. Ideal para layouts responsivos y complejos.
 *
 * CARACTERÍSTICAS:
 * - Soporte completo para CSS Grid
 * - Columnas y filas configurables
 * - Gap entre elementos
 * - Auto-fill y auto-fit responsivo
 * - Áreas de grid nombradas opcionales
 * - Alineación flexible de contenido
 *
 * EJEMPLO BÁSICO (Grid de 3 columnas):
 * new GridComponent(
 *     columns: "repeat(3, 1fr)",
 *     gap: "1rem",
 *     children: [
 *         new CardComponent(...),
 *         new CardComponent(...),
 *         new CardComponent(...)
 *     ]
 * )
 *
 * EJEMPLO RESPONSIVO (auto-fill):
 * new GridComponent(
 *     columns: "repeat(auto-fill, minmax(250px, 1fr))",
 *     gap: "1.5rem",
 *     children: [
 *         ...array_map(fn($product) => new ProductCardComponent($product), $products)
 *     ]
 * )
 *
 * EJEMPLO CON FILAS:
 * new GridComponent(
 *     columns: "1fr 2fr",
 *     rows: "100px auto",
 *     gap: "1rem",
 *     children: [
 *         new SidebarComponent(),
 *         new MainContentComponent()
 *     ]
 * )
 *
 * EJEMPLO CON ÁREAS NOMBRADAS:
 * new GridComponent(
 *     columns: "200px 1fr",
 *     rows: "auto 1fr auto",
 *     areas: "'header header' 'sidebar main' 'footer footer'",
 *     gap: "1rem",
 *     children: [
 *         new HeaderComponent(),
 *         new SidebarComponent(),
 *         new MainComponent(),
 *         new FooterComponent()
 *     ]
 * )
 */
class GridComponent extends CoreComponent {

    protected $CSS_PATHS = ["./grid.css"];

    public function __construct(
        public array $children = [],

        // Grid Template
        public string $columns = "",                    // grid-template-columns (ej: "1fr 1fr", "repeat(3, 1fr)")
        public string $rows = "",                       // grid-template-rows
        public string $areas = "",                      // grid-template-areas

        // Gap
        public string $gap = "0",                       // Gap entre elementos
        public string $columnGap = "",                  // Gap específico entre columnas
        public string $rowGap = "",                     // Gap específico entre filas

        // Alineación
        public string $justifyItems = "stretch",        // start, end, center, stretch
        public string $alignItems = "stretch",          // start, end, center, stretch
        public string $justifyContent = "start",        // start, end, center, stretch, space-between, space-around, space-evenly
        public string $alignContent = "start",          // start, end, center, stretch, space-between, space-around, space-evenly

        // Auto flow
        public string $autoFlow = "row",                // row, column, dense, row dense, column dense
        public string $autoColumns = "",                // Tamaño de columnas auto-generadas
        public string $autoRows = "",                   // Tamaño de filas auto-generadas

        // Dimensiones
        public string $width = "100%",
        public string $height = "",
        public string $minHeight = "",
        public string $maxHeight = "",
        public string $padding = "0",

        public string $className = ""
    ) {}

    protected function component(): string {
        $childrenHtml = $this->renderChildren();

        // Construir clases CSS
        $classes = ["lego-grid"];
        if ($this->className) {
            $classes[] = $this->className;
        }
        $classStr = implode(" ", $classes);

        // Construir estilos inline
        $styles = [];

        // Grid Template
        if ($this->columns) {
            $styles[] = "grid-template-columns: {$this->columns}";
        }
        if ($this->rows) {
            $styles[] = "grid-template-rows: {$this->rows}";
        }
        if ($this->areas) {
            $styles[] = "grid-template-areas: {$this->areas}";
        }

        // Gap
        if ($this->gap !== "0") {
            $styles[] = "gap: {$this->gap}";
        }
        if ($this->columnGap) {
            $styles[] = "column-gap: {$this->columnGap}";
        }
        if ($this->rowGap) {
            $styles[] = "row-gap: {$this->rowGap}";
        }

        // Alineación
        $styles[] = "justify-items: {$this->justifyItems}";
        $styles[] = "align-items: {$this->alignItems}";
        $styles[] = "justify-content: {$this->justifyContent}";
        $styles[] = "align-content: {$this->alignContent}";

        // Auto flow
        if ($this->autoFlow !== "row") {
            $styles[] = "grid-auto-flow: {$this->autoFlow}";
        }
        if ($this->autoColumns) {
            $styles[] = "grid-auto-columns: {$this->autoColumns}";
        }
        if ($this->autoRows) {
            $styles[] = "grid-auto-rows: {$this->autoRows}";
        }

        // Dimensiones
        $styles[] = "width: {$this->width}";
        $styles[] = "padding: {$this->padding}";

        if ($this->height) {
            $styles[] = "height: {$this->height}";
        }
        if ($this->minHeight) {
            $styles[] = "min-height: {$this->minHeight}";
        }
        if ($this->maxHeight) {
            $styles[] = "max-height: {$this->maxHeight}";
        }

        $styleStr = implode("; ", $styles);

        return <<<HTML
        <div class="{$classStr}" style="{$styleStr}">
            {$childrenHtml}
        </div>
        HTML;
    }
}
