<?php
namespace Components\Shared\Essentials\TableComponent\Renderers;

/**
 * ImageRenderer - Renderer para imágenes con thumbnail y preview
 *
 * FILOSOFÍA LEGO:
 * Renderiza imágenes como thumbnails con opción de preview al hacer clic.
 * Útil para productos, avatares, galerías, etc.
 *
 * CARACTERÍSTICAS:
 * - Thumbnail con tamaño configurable
 * - Forma configurable (circle, square, rounded)
 * - Preview modal al hacer clic (opcional)
 * - Placeholder para imágenes faltantes
 * - Lazy loading
 *
 * EJEMPLO BÁSICO:
 * ```php
 * ImageRenderer::create(
 *     size: 'medium',
 *     shape: 'rounded'
 * )
 * ```
 *
 * EJEMPLO PARA AVATARES:
 * ```php
 * ImageRenderer::create(
 *     size: 'small',
 *     shape: 'circle',
 *     showPreview: false
 * )
 * ```
 */
class ImageRenderer extends CellRenderer
{
    private string $size;
    private string $shape;
    private bool $showPreview;
    private string $placeholderUrl;
    private bool $lazyLoad;

    private function __construct(
        string $size = 'medium',
        string $shape = 'rounded',
        bool $showPreview = true,
        string $placeholderUrl = '',
        bool $lazyLoad = true
    ) {
        $this->size = $size;
        $this->shape = $shape;
        $this->showPreview = $showPreview;
        $this->placeholderUrl = $placeholderUrl;
        $this->lazyLoad = $lazyLoad;
    }

    /**
     * Factory method con named arguments
     */
    public static function create(
        string $size = 'medium',
        string $shape = 'rounded',
        bool $showPreview = true,
        string $placeholderUrl = '',
        bool $lazyLoad = true
    ): self {
        return new self($size, $shape, $showPreview, $placeholderUrl, $lazyLoad);
    }

    public function toJavaScript(): string
    {
        $size = $this->escapeJs($this->size);
        $shape = $this->escapeJs($this->shape);
        $showPreview = $this->showPreview ? 'true' : 'false';
        $placeholderUrl = $this->escapeJs($this->placeholderUrl);
        $lazyLoad = $this->lazyLoad ? 'true' : 'false';

        return <<<JS
(params) => {
    const value = params.value;

    if (!value) {
        return '<div class="flex items-center justify-center bg-gray-200 dark:bg-gray-700 w-10 h-10 rounded"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>';
    }

    const size = '{$size}';
    const shape = '{$shape}';
    const showPreview = {$showPreview};
    const placeholderUrl = '{$placeholderUrl}';
    const lazyLoad = {$lazyLoad};

    // Tamaños
    const sizes = {
        small: 'w-8 h-8',
        medium: 'w-12 h-12',
        large: 'w-16 h-16'
    };

    // Formas
    const shapes = {
        circle: 'rounded-full',
        square: 'rounded-none',
        rounded: 'rounded-lg'
    };

    const sizeClass = sizes[size] || sizes.medium;
    const shapeClass = shapes[shape] || shapes.rounded;

    const lazyAttr = lazyLoad ? 'loading="lazy"' : '';
    const cursorClass = showPreview ? 'cursor-pointer hover:opacity-80' : '';

    const onClickAttr = showPreview
        ? `onclick="window.previewImage ? window.previewImage('\${value}') : window.open('\${value}', '_blank')"`
        : '';

    return `
        <img
            src="\${value}"
            alt="Imagen"
            class="\${sizeClass} \${shapeClass} \${cursorClass} object-cover transition-opacity duration-200"
            \${lazyAttr}
            \${onClickAttr}
            onerror="this.src='\${placeholderUrl || 'data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'100\\' height=\\'100\\'%3E%3Crect fill=\\'%23ddd\\' width=\\'100\\' height=\\'100\\'/%3E%3Ctext fill=\\'%23999\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\'%3EN/A%3C/text%3E%3C/svg%3E'}'; this.onerror=null;"
        />
    `;
}
JS;
    }
}
