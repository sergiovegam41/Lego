<?php

namespace Components\Core\Screen;

use Core\Components\CoreComponent\CoreComponent;

/**
 * ScreenComponent - Wrapper base para todas las ventanas LEGO
 * 
 * FILOSOFÍA LEGO:
 * Toda ventana que LEGO maneje debe estar envuelta en un Screen.
 * El Screen provee:
 * - Contenedor identificable con data-screen-id
 * - Estructura consistente para el window manager
 * - Hooks para funcionalidad adicional (loading, errors, etc.)
 * - Integración automática con el sistema de módulos
 * 
 * BENEFICIOS:
 * - Consistencia visual y estructural
 * - El DOM tiene elementos identificables
 * - Fácil de extender con funcionalidad cross-cutting
 * - El JS puede encontrar screens confiablemente
 * 
 * USO BÁSICO:
 * ```php
 * $screen = new ScreenComponent(
 *     id: 'products-list',
 *     children: [new ProductsTableComponent()]
 * );
 * ```
 * 
 * USO CON SLOTS:
 * ```php
 * $screen = new ScreenComponent(
 *     id: 'products-detail',
 *     header: new PageHeaderComponent(title: 'Detalle de Producto'),
 *     children: [new ProductFormComponent($productId)],
 *     footer: new ScreenActionsComponent([...])
 * );
 * ```
 */
class ScreenComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./screen.css"];
    protected $JS_PATHS = ["./screen.js"];

    public function __construct(
        /** ID único del screen (debe coincidir con SCREEN_ID del componente padre) */
        public string $id,
        
        /** Contenido principal del screen */
        public array $children = [],
        
        /** Slot opcional para header del screen */
        public ?CoreComponent $header = null,
        
        /** Slot opcional para footer del screen */
        public ?CoreComponent $footer = null,
        
        /** Clase CSS adicional */
        public string $className = '',
        
        /** Si el screen tiene padding interno */
        public bool $padded = true,
        
        /** Si el screen tiene scroll propio */
        public bool $scrollable = true,
    ) {
        $this->children = $children;
    }

    protected function component(): string
    {
        $headerHtml = $this->header?->render() ?? '';
        $footerHtml = $this->footer?->render() ?? '';
        $childrenHtml = $this->renderChildren();
        
        $classes = ['lego-screen'];
        if ($this->className) {
            $classes[] = $this->className;
        }
        if ($this->padded) {
            $classes[] = 'lego-screen--padded';
        }
        if ($this->scrollable) {
            $classes[] = 'lego-screen--scrollable';
        }
        
        $classString = implode(' ', $classes);

        return <<<HTML
        <div class="{$classString}" data-screen-id="{$this->id}">
            {$this->renderHeader($headerHtml)}
            <div class="lego-screen__content">
                {$childrenHtml}
            </div>
            {$this->renderFooter($footerHtml)}
        </div>
        HTML;
    }
    
    private function renderHeader(string $headerHtml): string
    {
        if (empty($headerHtml)) {
            return '';
        }
        
        return <<<HTML
        <div class="lego-screen__header">
            {$headerHtml}
        </div>
        HTML;
    }
    
    private function renderFooter(string $footerHtml): string
    {
        if (empty($footerHtml)) {
            return '';
        }
        
        return <<<HTML
        <div class="lego-screen__footer">
            {$footerHtml}
        </div>
        HTML;
    }
}

