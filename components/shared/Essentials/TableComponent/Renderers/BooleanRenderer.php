<?php
namespace Components\Shared\Essentials\TableComponent\Renderers;

/**
 * BooleanRenderer - Renderer para valores booleanos con iconos
 *
 * FILOSOFÍA LEGO:
 * Renderiza valores true/false con iconos visuales claros.
 * Útil para campos activo/inactivo, habilitado/deshabilitado, etc.
 *
 * CARACTERÍSTICAS:
 * - Iconos check/x para true/false
 * - Colores personalizables
 * - Textos personalizables
 * - Soporte para valores 1/0, "true"/"false", "yes"/"no"
 * - Responsive a temas
 *
 * EJEMPLO BÁSICO:
 * ```php
 * BooleanRenderer::create()
 * // true → ✓ verde, false → ✗ rojo
 * ```
 *
 * EJEMPLO PERSONALIZADO:
 * ```php
 * BooleanRenderer::create(
 *     trueText: 'Activo',
 *     falseText: 'Inactivo',
 *     showText: true
 * )
 * ```
 */
class BooleanRenderer extends CellRenderer
{
    private string $trueText;
    private string $falseText;
    private bool $showText;
    private bool $showIcon;
    private string $style; // 'icon', 'badge', 'text'

    private function __construct(
        string $trueText = 'Sí',
        string $falseText = 'No',
        bool $showText = false,
        bool $showIcon = true,
        string $style = 'icon'
    ) {
        $this->trueText = $trueText;
        $this->falseText = $falseText;
        $this->showText = $showText;
        $this->showIcon = $showIcon;
        $this->style = $style;
    }

    /**
     * Factory method con named arguments
     */
    public static function create(
        string $trueText = 'Sí',
        string $falseText = 'No',
        bool $showText = false,
        bool $showIcon = true,
        string $style = 'icon'
    ): self {
        return new self($trueText, $falseText, $showText, $showIcon, $style);
    }

    public function toJavaScript(): string
    {
        $trueText = $this->escapeJs($this->trueText);
        $falseText = $this->escapeJs($this->falseText);
        $showText = $this->showText ? 'true' : 'false';
        $showIcon = $this->showIcon ? 'true' : 'false';
        $style = $this->escapeJs($this->style);

        return <<<JS
(params) => {
    const value = params.value;

    // Convertir diferentes formatos a boolean
    let boolValue;
    if (typeof value === 'boolean') {
        boolValue = value;
    } else if (typeof value === 'number') {
        boolValue = value === 1;
    } else if (typeof value === 'string') {
        const lowerValue = value.toLowerCase();
        boolValue = lowerValue === 'true' || lowerValue === 'yes' || lowerValue === 'si' || lowerValue === '1';
    } else {
        return '<span class="text-gray-400 dark:text-gray-500">-</span>';
    }

    const isDark = document.documentElement.classList.contains('dark');
    const trueText = '{$trueText}';
    const falseText = '{$falseText}';
    const showText = {$showText};
    const showIcon = {$showIcon};
    const style = '{$style}';

    // Iconos SVG
    const checkIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
    const xIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

    if (style === 'badge') {
        // Estilo badge completo
        const badgeClasses = boolValue
            ? (isDark ? 'bg-green-900/30 text-green-400 border-green-700' : 'bg-green-100 text-green-800 border-green-300')
            : (isDark ? 'bg-red-900/30 text-red-400 border-red-700' : 'bg-red-100 text-red-800 border-red-300');

        const icon = boolValue ? checkIcon : xIcon;
        const text = boolValue ? trueText : falseText;

        return `
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border \${badgeClasses}">
                \${showIcon ? icon : ''}
                \${text}
            </span>
        `;
    } else if (style === 'text') {
        // Solo texto con color
        const textClass = boolValue
            ? 'text-green-600 dark:text-green-400'
            : 'text-red-600 dark:text-red-400';

        const text = boolValue ? trueText : falseText;

        return `<span class="\${textClass} font-medium">\${text}</span>`;
    } else {
        // Estilo icon (default)
        const colorClass = boolValue
            ? 'text-green-600 dark:text-green-400'
            : 'text-red-600 dark:text-red-400';

        const icon = boolValue ? checkIcon : xIcon;
        const text = boolValue ? trueText : falseText;

        if (showIcon && showText) {
            return `
                <div class="flex items-center gap-1.5 \${colorClass}">
                    \${icon}
                    <span>\${text}</span>
                </div>
            `;
        } else if (showIcon) {
            return `<div class="flex items-center justify-center \${colorClass}">\${icon}</div>`;
        } else {
            return `<span class="\${colorClass}">\${text}</span>`;
        }
    }
}
JS;
    }
}
