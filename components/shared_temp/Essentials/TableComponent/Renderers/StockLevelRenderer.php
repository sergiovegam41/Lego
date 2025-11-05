<?php
namespace Components\Shared\Essentials\TableComponent\Renderers;

/**
 * StockLevelRenderer - Renderer para niveles de stock con indicadores visuales
 *
 * FILOSOFÍA LEGO:
 * Renderiza niveles de stock con colores y badges según umbrales configurables.
 * Útil para inventarios, disponibilidad, capacidad, etc.
 *
 * CARACTERÍSTICAS:
 * - Umbrales configurables (bajo, medio, alto)
 * - Indicadores visuales de color
 * - Barra de progreso opcional
 * - Badge con el valor
 * - Alertas visuales para stock crítico
 *
 * EJEMPLO BÁSICO:
 * ```php
 * StockLevelRenderer::create(
 *     lowThreshold: 10,
 *     mediumThreshold: 50
 * )
 * ```
 *
 * EJEMPLO CON BARRA DE PROGRESO:
 * ```php
 * StockLevelRenderer::create(
 *     lowThreshold: 20,
 *     mediumThreshold: 100,
 *     maxValue: 500,
 *     showProgressBar: true
 * )
 * ```
 */
class StockLevelRenderer extends CellRenderer
{
    private int $lowThreshold;
    private int $mediumThreshold;
    private ?int $maxValue;
    private bool $showProgressBar;
    private bool $showBadge;
    private string $unit;

    private function __construct(
        int $lowThreshold = 10,
        int $mediumThreshold = 50,
        ?int $maxValue = null,
        bool $showProgressBar = false,
        bool $showBadge = true,
        string $unit = ''
    ) {
        $this->lowThreshold = $lowThreshold;
        $this->mediumThreshold = $mediumThreshold;
        $this->maxValue = $maxValue;
        $this->showProgressBar = $showProgressBar;
        $this->showBadge = $showBadge;
        $this->unit = $unit;
    }

    /**
     * Factory method con named arguments
     */
    public static function create(
        int $lowThreshold = 10,
        int $mediumThreshold = 50,
        ?int $maxValue = null,
        bool $showProgressBar = false,
        bool $showBadge = true,
        string $unit = ''
    ): self {
        return new self(
            $lowThreshold,
            $mediumThreshold,
            $maxValue,
            $showProgressBar,
            $showBadge,
            $unit
        );
    }

    public function toJavaScript(): string
    {
        $lowThreshold = $this->lowThreshold;
        $mediumThreshold = $this->mediumThreshold;
        $maxValue = $this->maxValue ?? 'null';
        $showProgressBar = $this->showProgressBar ? 'true' : 'false';
        $showBadge = $this->showBadge ? 'true' : 'false';
        $unit = $this->escapeJs($this->unit);

        return <<<JS
(params) => {
    const value = parseInt(params.value);

    if (isNaN(value)) {
        return '<span class="text-gray-400 dark:text-gray-500">-</span>';
    }

    const isDark = document.documentElement.classList.contains('dark');
    const lowThreshold = {$lowThreshold};
    const mediumThreshold = {$mediumThreshold};
    const maxValue = {$maxValue};
    const showProgressBar = {$showProgressBar};
    const showBadge = {$showBadge};
    const unit = '{$unit}';

    // Determinar nivel y colores
    let level, badgeClasses, barColor, dotColor, label;

    if (value === 0) {
        level = 'empty';
        label = 'Agotado';
        badgeClasses = isDark
            ? 'bg-red-900/30 text-red-400 border-red-700'
            : 'bg-red-100 text-red-800 border-red-300';
        barColor = isDark ? 'bg-red-500' : 'bg-red-600';
        dotColor = isDark ? 'bg-red-500' : 'bg-red-600';
    } else if (value <= lowThreshold) {
        level = 'low';
        label = 'Stock Bajo';
        badgeClasses = isDark
            ? 'bg-orange-900/30 text-orange-400 border-orange-700'
            : 'bg-orange-100 text-orange-800 border-orange-300';
        barColor = isDark ? 'bg-orange-500' : 'bg-orange-600';
        dotColor = isDark ? 'bg-orange-500' : 'bg-orange-600';
    } else if (value <= mediumThreshold) {
        level = 'medium';
        label = 'Stock Medio';
        badgeClasses = isDark
            ? 'bg-yellow-900/30 text-yellow-400 border-yellow-700'
            : 'bg-yellow-100 text-yellow-800 border-yellow-300';
        barColor = isDark ? 'bg-yellow-500' : 'bg-yellow-600';
        dotColor = isDark ? 'bg-yellow-500' : 'bg-yellow-600';
    } else {
        level = 'high';
        label = 'Stock Alto';
        badgeClasses = isDark
            ? 'bg-green-900/30 text-green-400 border-green-700'
            : 'bg-green-100 text-green-800 border-green-300';
        barColor = isDark ? 'bg-green-500' : 'bg-green-600';
        dotColor = isDark ? 'bg-green-500' : 'bg-green-600';
    }

    // Construir HTML
    let html = '<div class="flex items-center gap-2">';

    // Indicador de punto
    html += `<span class="w-2 h-2 rounded-full \${dotColor}"></span>`;

    // Badge con el valor
    if (showBadge) {
        const displayValue = unit ? value + ' ' + unit : value;
        html += `
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border \${badgeClasses}">
                \${displayValue}
            </span>
        `;
    } else {
        // Solo el número sin badge
        html += `<span class="text-sm font-medium">\${value}\${unit ? ' ' + unit : ''}</span>`;
    }

    // Barra de progreso (si está activada y hay maxValue)
    if (showProgressBar && maxValue) {
        const percentage = Math.min((value / maxValue) * 100, 100);
        const bgBar = isDark ? 'bg-gray-700' : 'bg-gray-200';

        html += `
            <div class="flex-1 min-w-[60px]">
                <div class="w-full \${bgBar} rounded-full h-2">
                    <div class="\${barColor} h-2 rounded-full transition-all duration-300" style="width: \${percentage}%"></div>
                </div>
            </div>
        `;
    }

    html += '</div>';

    return html;
}
JS;
    }
}
