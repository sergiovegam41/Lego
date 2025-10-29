<?php
namespace Components\Shared\Essentials\TableComponent\Renderers;

/**
 * DateRenderer - Renderer para fechas con formato personalizable
 *
 * FILOSOFÍA LEGO:
 * Formatea fechas de forma consistente y configurable.
 * Soporta diferentes formatos y locales.
 *
 * CARACTERÍSTICAS:
 * - Múltiples formatos predefinidos (short, medium, long, relative)
 * - Formato personalizado
 * - Fechas relativas ("hace 2 días")
 * - Tooltip con fecha completa
 * - Manejo de valores nulos
 *
 * EJEMPLO BÁSICO:
 * ```php
 * DateRenderer::create(format: 'medium')
 * // Output: "15 Oct 2024"
 * ```
 *
 * EJEMPLO CON FECHA RELATIVA:
 * ```php
 * DateRenderer::create(format: 'relative')
 * // Output: "hace 2 días"
 * ```
 */
class DateRenderer extends CellRenderer
{
    private string $format;
    private bool $showTooltip;
    private string $emptyText;

    private function __construct(
        string $format = 'medium',
        bool $showTooltip = true,
        string $emptyText = '-'
    ) {
        $this->format = $format;
        $this->showTooltip = $showTooltip;
        $this->emptyText = $emptyText;
    }

    /**
     * Factory method con named arguments
     */
    public static function create(
        string $format = 'medium',
        bool $showTooltip = true,
        string $emptyText = '-'
    ): self {
        return new self($format, $showTooltip, $emptyText);
    }

    public function toJavaScript(): string
    {
        $format = $this->escapeJs($this->format);
        $showTooltip = $this->showTooltip ? 'true' : 'false';
        $emptyText = $this->escapeJs($this->emptyText);

        return <<<JS
(params) => {
    const value = params.value;

    if (!value) {
        return '<span class="text-gray-400 dark:text-gray-500">{$emptyText}</span>';
    }

    const date = new Date(value);

    if (isNaN(date.getTime())) {
        return '<span class="text-gray-400 dark:text-gray-500">Fecha inválida</span>';
    }

    const format = '{$format}';
    const showTooltip = {$showTooltip};

    // Formatear según el tipo
    let formatted;
    let tooltip = date.toLocaleString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    if (format === 'relative') {
        // Fecha relativa
        const now = new Date();
        const diffMs = now - date;
        const diffSecs = Math.floor(diffMs / 1000);
        const diffMins = Math.floor(diffSecs / 60);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        const diffMonths = Math.floor(diffDays / 30);
        const diffYears = Math.floor(diffDays / 365);

        if (diffSecs < 60) {
            formatted = 'hace unos segundos';
        } else if (diffMins < 60) {
            formatted = `hace \${diffMins} minuto\${diffMins !== 1 ? 's' : ''}`;
        } else if (diffHours < 24) {
            formatted = `hace \${diffHours} hora\${diffHours !== 1 ? 's' : ''}`;
        } else if (diffDays < 30) {
            formatted = `hace \${diffDays} día\${diffDays !== 1 ? 's' : ''}`;
        } else if (diffMonths < 12) {
            formatted = `hace \${diffMonths} mes\${diffMonths !== 1 ? 'es' : ''}`;
        } else {
            formatted = `hace \${diffYears} año\${diffYears !== 1 ? 's' : ''}`;
        }
    } else if (format === 'short') {
        // Formato corto: 15/10/24
        formatted = date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: '2-digit'
        });
    } else if (format === 'medium') {
        // Formato medio: 15 Oct 2024
        formatted = date.toLocaleDateString('es-ES', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    } else if (format === 'long') {
        // Formato largo: 15 de octubre de 2024
        formatted = date.toLocaleDateString('es-ES', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    } else if (format === 'datetime') {
        // Con hora: 15/10/24 14:30
        formatted = date.toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    } else if (format === 'time') {
        // Solo hora: 14:30
        formatted = date.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    } else {
        // Formato personalizado o default
        formatted = date.toLocaleDateString('es-ES');
    }

    const tooltipAttr = showTooltip ? `title="\${tooltip}"` : '';

    return `<span class="text-gray-900 dark:text-gray-100" \${tooltipAttr}>\${formatted}</span>`;
}
JS;
    }
}
