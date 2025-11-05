<?php
namespace Components\Shared\Essentials\DivComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * DivComponent - Contenedor genérico con estilos inline flexibles
 *
 * FILOSOFÍA LEGO:
 * Componente fundamental que actúa como contenedor genérico con soporte
 * completo para estilos inline. Es el equivalente a un <div> pero con
 * la composición de children del sistema Lego.
 *
 * CARACTERÍSTICAS:
 * - Acepta estilos CSS inline como propiedades individuales
 * - Soporte para children array
 * - Clases CSS personalizables
 * - ID opcional para targeting específico
 * - Eventos onclick opcionales
 *
 * PROPIEDADES DE ESTILO SOPORTADAS:
 * - Layout: display, position, top, right, bottom, left, zIndex
 * - Dimensiones: width, height, minWidth, maxWidth, minHeight, maxHeight
 * - Espaciado: margin, padding (y sus variantes: marginTop, paddingLeft, etc.)
 * - Flexbox: flex, flexDirection, justifyContent, alignItems, gap, flexWrap
 * - Grid: gridTemplateColumns, gridTemplateRows, gridGap
 * - Bordes: border, borderRadius, borderColor, borderWidth
 * - Fondo: backgroundColor, backgroundImage, backgroundSize, backgroundPosition
 * - Texto: color, fontSize, fontWeight, textAlign, lineHeight
 * - Efectos: opacity, boxShadow, transform, transition, cursor
 * - Overflow: overflow, overflowX, overflowY
 *
 * EJEMPLO BÁSICO:
 * new DivComponent(
 *     width: "100%",
 *     padding: "1rem",
 *     backgroundColor: "var(--bg-surface)",
 *     children: [
 *         new TextComponent(...)
 *     ]
 * )
 *
 * EJEMPLO FLEXBOX:
 * new DivComponent(
 *     display: "flex",
 *     justifyContent: "center",
 *     alignItems: "center",
 *     gap: "1rem",
 *     padding: "2rem",
 *     children: [
 *         new ButtonComponent(...),
 *         new ButtonComponent(...)
 *     ]
 * )
 *
 * EJEMPLO CON ESTILO COMPLEJO:
 * new DivComponent(
 *     position: "relative",
 *     width: "300px",
 *     height: "200px",
 *     borderRadius: "0.5rem",
 *     boxShadow: "0 2px 8px rgba(0,0,0,0.1)",
 *     overflow: "hidden",
 *     children: [
 *         new ImageComponent(...)
 *     ]
 * )
 *
 * EJEMPLO ONCLICK:
 * new DivComponent(
 *     padding: "1rem",
 *     cursor: "pointer",
 *     onclick: "handleClick()",
 *     children: [...]
 * )
 */
class DivComponent extends CoreComponent {

    protected $CSS_PATHS = ["./div.css"];

    public function __construct(
        public array $children = [],
        public string $className = "",
        public string $id = "",

        // Estilos inline - Layout
        public string $display = "",
        public string $position = "",
        public string $top = "",
        public string $right = "",
        public string $bottom = "",
        public string $left = "",
        public string $zIndex = "",

        // Dimensiones
        public string $width = "",
        public string $height = "",
        public string $minWidth = "",
        public string $maxWidth = "",
        public string $minHeight = "",
        public string $maxHeight = "",

        // Espaciado
        public string $margin = "",
        public string $marginTop = "",
        public string $marginRight = "",
        public string $marginBottom = "",
        public string $marginLeft = "",
        public string $padding = "",
        public string $paddingTop = "",
        public string $paddingRight = "",
        public string $paddingBottom = "",
        public string $paddingLeft = "",

        // Flexbox
        public string $flex = "",
        public string $flexDirection = "",
        public string $justifyContent = "",
        public string $alignItems = "",
        public string $gap = "",
        public string $flexWrap = "",
        public string $alignSelf = "",
        public string $flexGrow = "",
        public string $flexShrink = "",

        // Grid
        public string $gridTemplateColumns = "",
        public string $gridTemplateRows = "",
        public string $gridGap = "",
        public string $gridColumn = "",
        public string $gridRow = "",

        // Bordes
        public string $border = "",
        public string $borderRadius = "",
        public string $borderColor = "",
        public string $borderWidth = "",
        public string $borderTop = "",
        public string $borderRight = "",
        public string $borderBottom = "",
        public string $borderLeft = "",

        // Fondo
        public string $backgroundColor = "",
        public string $backgroundImage = "",
        public string $backgroundSize = "",
        public string $backgroundPosition = "",
        public string $backgroundRepeat = "",

        // Texto
        public string $color = "",
        public string $fontSize = "",
        public string $fontWeight = "",
        public string $textAlign = "",
        public string $lineHeight = "",

        // Efectos
        public string $opacity = "",
        public string $boxShadow = "",
        public string $transform = "",
        public string $transition = "",
        public string $cursor = "",

        // Overflow
        public string $overflow = "",
        public string $overflowX = "",
        public string $overflowY = "",

        // Eventos
        public string $onclick = ""
    ) {}

    protected function component(): string {
        $childrenHtml = $this->renderChildren();

        // Construir clases CSS
        $classes = ["lego-div"];
        if ($this->className) {
            $classes[] = $this->className;
        }
        $classStr = implode(" ", $classes);

        // Construir estilos inline
        $styles = [];

        // Helper para agregar estilos solo si tienen valor
        $addStyle = function($property, $value) use (&$styles) {
            if ($value !== "") {
                $styles[] = "{$property}: {$value}";
            }
        };

        // Layout
        $addStyle("display", $this->display);
        $addStyle("position", $this->position);
        $addStyle("top", $this->top);
        $addStyle("right", $this->right);
        $addStyle("bottom", $this->bottom);
        $addStyle("left", $this->left);
        $addStyle("z-index", $this->zIndex);

        // Dimensiones
        $addStyle("width", $this->width);
        $addStyle("height", $this->height);
        $addStyle("min-width", $this->minWidth);
        $addStyle("max-width", $this->maxWidth);
        $addStyle("min-height", $this->minHeight);
        $addStyle("max-height", $this->maxHeight);

        // Espaciado
        $addStyle("margin", $this->margin);
        $addStyle("margin-top", $this->marginTop);
        $addStyle("margin-right", $this->marginRight);
        $addStyle("margin-bottom", $this->marginBottom);
        $addStyle("margin-left", $this->marginLeft);
        $addStyle("padding", $this->padding);
        $addStyle("padding-top", $this->paddingTop);
        $addStyle("padding-right", $this->paddingRight);
        $addStyle("padding-bottom", $this->paddingBottom);
        $addStyle("padding-left", $this->paddingLeft);

        // Flexbox
        $addStyle("flex", $this->flex);
        $addStyle("flex-direction", $this->flexDirection);
        $addStyle("justify-content", $this->justifyContent);
        $addStyle("align-items", $this->alignItems);
        $addStyle("gap", $this->gap);
        $addStyle("flex-wrap", $this->flexWrap);
        $addStyle("align-self", $this->alignSelf);
        $addStyle("flex-grow", $this->flexGrow);
        $addStyle("flex-shrink", $this->flexShrink);

        // Grid
        $addStyle("grid-template-columns", $this->gridTemplateColumns);
        $addStyle("grid-template-rows", $this->gridTemplateRows);
        $addStyle("grid-gap", $this->gridGap);
        $addStyle("grid-column", $this->gridColumn);
        $addStyle("grid-row", $this->gridRow);

        // Bordes
        $addStyle("border", $this->border);
        $addStyle("border-radius", $this->borderRadius);
        $addStyle("border-color", $this->borderColor);
        $addStyle("border-width", $this->borderWidth);
        $addStyle("border-top", $this->borderTop);
        $addStyle("border-right", $this->borderRight);
        $addStyle("border-bottom", $this->borderBottom);
        $addStyle("border-left", $this->borderLeft);

        // Fondo
        $addStyle("background-color", $this->backgroundColor);
        $addStyle("background-image", $this->backgroundImage);
        $addStyle("background-size", $this->backgroundSize);
        $addStyle("background-position", $this->backgroundPosition);
        $addStyle("background-repeat", $this->backgroundRepeat);

        // Texto
        $addStyle("color", $this->color);
        $addStyle("font-size", $this->fontSize);
        $addStyle("font-weight", $this->fontWeight);
        $addStyle("text-align", $this->textAlign);
        $addStyle("line-height", $this->lineHeight);

        // Efectos
        $addStyle("opacity", $this->opacity);
        $addStyle("box-shadow", $this->boxShadow);
        $addStyle("transform", $this->transform);
        $addStyle("transition", $this->transition);
        $addStyle("cursor", $this->cursor);

        // Overflow
        $addStyle("overflow", $this->overflow);
        $addStyle("overflow-x", $this->overflowX);
        $addStyle("overflow-y", $this->overflowY);

        $styleStr = implode("; ", $styles);

        // Construir atributos
        $attrs = [];
        $attrs[] = "class=\"{$classStr}\"";
        if ($styleStr) {
            $attrs[] = "style=\"{$styleStr}\"";
        }
        if ($this->id) {
            $attrs[] = "id=\"{$this->id}\"";
        }
        if ($this->onclick) {
            $attrs[] = "onclick=\"{$this->onclick}\"";
        }
        $attrsStr = implode(" ", $attrs);

        return <<<HTML
        <div {$attrsStr}>
            {$childrenHtml}
        </div>
        HTML;
    }
}
