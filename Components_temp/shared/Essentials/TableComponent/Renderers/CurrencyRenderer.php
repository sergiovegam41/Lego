<?php
namespace Components\Shared\Essentials\TableComponent\Renderers;

/**
 * CurrencyRenderer - Renderer para valores monetarios
 *
 * FILOSOFÍA LEGO:
 * Formatea valores numéricos como moneda con símbolo, separadores y decimales.
 * Elimina la necesidad de escribir valueFormatter manualmente.
 *
 * CARACTERÍSTICAS:
 * - Soporte para múltiples monedas (USD, EUR, MXN, etc.)
 * - Separadores de miles configurables
 * - Decimales configurables
 * - Colores para valores negativos
 * - Alineación automática a la derecha
 *
 * EJEMPLO BÁSICO:
 * ```php
 * new ColumnDto(
 *     field: "price",
 *     headerName: "Precio",
 *     cellRenderer: CurrencyRenderer::create(currency: 'USD')
 * )
 * ```
 *
 * EJEMPLO AVANZADO:
 * ```php
 * CurrencyRenderer::create(
 *     currency: 'MXN',
 *     decimals: 2,
 *     thousandsSeparator: ',',
 *     decimalSeparator: '.',
 *     showNegativeInRed: true
 * )
 * ```
 */
class CurrencyRenderer extends CellRenderer
{
    private string $currency;
    private int $decimals;
    private string $thousandsSeparator;
    private string $decimalSeparator;
    private bool $showNegativeInRed;
    private string $position; // 'before' o 'after'

    private function __construct(
        string $currency = 'USD',
        int $decimals = 2,
        string $thousandsSeparator = ',',
        string $decimalSeparator = '.',
        bool $showNegativeInRed = true,
        string $position = 'before'
    ) {
        $this->currency = $currency;
        $this->decimals = $decimals;
        $this->thousandsSeparator = $thousandsSeparator;
        $this->decimalSeparator = $decimalSeparator;
        $this->showNegativeInRed = $showNegativeInRed;
        $this->position = $position;
    }

    /**
     * Factory method con named arguments
     */
    public static function create(
        string $currency = 'USD',
        int $decimals = 2,
        string $thousandsSeparator = ',',
        string $decimalSeparator = '.',
        bool $showNegativeInRed = true,
        string $position = 'before'
    ): self {
        return new self(
            $currency,
            $decimals,
            $thousandsSeparator,
            $decimalSeparator,
            $showNegativeInRed,
            $position
        );
    }

    public function toJavaScript(): string
    {
        $symbol = $this->getCurrencySymbol();
        $decimals = $this->decimals;
        $thousandsSep = $this->escapeJs($this->thousandsSeparator);
        $decimalSep = $this->escapeJs($this->decimalSeparator);
        $showRed = $this->showNegativeInRed ? 'true' : 'false';
        $position = $this->escapeJs($this->position);

        return <<<JS
(params) => {
    const value = parseFloat(params.value);

    if (isNaN(value)) {
        return '<span class="text-gray-400 dark:text-gray-500">-</span>';
    }

    const isNegative = value < 0;
    const absValue = Math.abs(value);

    // Formatear número
    const parts = absValue.toFixed({$decimals}).split('.');
    const integerPart = parts[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, '{$thousandsSep}');
    const decimalPart = parts[1] || '0'.repeat({$decimals});

    const formattedValue = integerPart + '{$decimalSep}' + decimalPart;

    // Símbolo y posición
    const symbol = '{$symbol}';
    const position = '{$position}';
    const displayValue = position === 'before'
        ? symbol + formattedValue
        : formattedValue + ' ' + symbol;

    // Color para negativos
    const showRed = {$showRed};
    const colorClass = isNegative && showRed
        ? 'text-red-600 dark:text-red-400'
        : 'text-gray-900 dark:text-gray-100';

    const sign = isNegative ? '-' : '';

    return `<span class="font-mono \${colorClass}">\${sign}\${displayValue}</span>`;
}
JS;
    }

    /**
     * Obtiene el símbolo de la moneda
     */
    private function getCurrencySymbol(): string
    {
        return match(strtoupper($this->currency)) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'MXN' => '$',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'INR' => '₹',
            'BRL' => 'R$',
            'ARS' => '$',
            'CLP' => '$',
            'COP' => '$',
            'PEN' => 'S/',
            default => $this->currency
        };
    }
}
