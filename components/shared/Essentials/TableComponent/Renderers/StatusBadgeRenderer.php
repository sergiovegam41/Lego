<?php
namespace Components\Shared\Essentials\TableComponent\Renderers;

/**
 * StatusBadgeRenderer - Renderer para badges de estado
 *
 * FILOSOFÍA LEGO:
 * Genera badges visuales consistentes para campos de estado.
 * Soporta múltiples estados con colores y etiquetas personalizables.
 *
 * CARACTERÍSTICAS:
 * - Mapeo de valores a colores y etiquetas
 * - Estados predefinidos comunes (active, inactive, pending, etc.)
 * - Soporte para estados personalizados
 * - Responsive a cambios de tema
 * - Iconos opcionales
 *
 * EJEMPLO BÁSICO (usa estados predefinidos):
 * ```php
 * StatusBadgeRenderer::create()
 * // Reconoce automáticamente: active, inactive, pending, approved, rejected
 * ```
 *
 * EJEMPLO PERSONALIZADO:
 * ```php
 * StatusBadgeRenderer::create(
 *     statusMap: [
 *         'in_stock' => ['label' => 'En Stock', 'color' => 'green', 'icon' => 'check'],
 *         'low_stock' => ['label' => 'Stock Bajo', 'color' => 'yellow', 'icon' => 'alert'],
 *         'out_of_stock' => ['label' => 'Agotado', 'color' => 'red', 'icon' => 'x']
 *     ]
 * )
 * ```
 */
class StatusBadgeRenderer extends CellRenderer
{
    private array $statusMap;
    private string $defaultColor;
    private bool $showIcon;

    private function __construct(
        array $statusMap = [],
        string $defaultColor = 'gray',
        bool $showIcon = false
    ) {
        // Si no se proporciona mapa, usar estados predefinidos comunes
        $this->statusMap = empty($statusMap) ? $this->getDefaultStatusMap() : $statusMap;
        $this->defaultColor = $defaultColor;
        $this->showIcon = $showIcon;
    }

    /**
     * Factory method con named arguments
     */
    public static function create(
        array $statusMap = [],
        string $defaultColor = 'gray',
        bool $showIcon = false
    ): self {
        return new self($statusMap, $defaultColor, $showIcon);
    }

    public function toJavaScript(): string
    {
        // Convertir statusMap a JavaScript
        $statusMapJson = json_encode($this->statusMap, JSON_UNESCAPED_UNICODE);
        $defaultColor = $this->escapeJs($this->defaultColor);
        $showIcon = $this->showIcon ? 'true' : 'false';

        return <<<JS
(params) => {
    const value = params.value;
    const statusMap = {$statusMapJson};
    const isDark = document.documentElement.classList.contains('dark');

    // Buscar configuración del estado
    const config = statusMap[value] || {
        label: value,
        color: '{$defaultColor}',
        icon: null
    };

    // Clases de color según el tema
    const colorClasses = {
        green: isDark
            ? 'bg-green-900/30 text-green-400 border-green-700'
            : 'bg-green-100 text-green-800 border-green-300',
        red: isDark
            ? 'bg-red-900/30 text-red-400 border-red-700'
            : 'bg-red-100 text-red-800 border-red-300',
        yellow: isDark
            ? 'bg-yellow-900/30 text-yellow-400 border-yellow-700'
            : 'bg-yellow-100 text-yellow-800 border-yellow-300',
        blue: isDark
            ? 'bg-blue-900/30 text-blue-400 border-blue-700'
            : 'bg-blue-100 text-blue-800 border-blue-300',
        purple: isDark
            ? 'bg-purple-900/30 text-purple-400 border-purple-700'
            : 'bg-purple-100 text-purple-800 border-purple-300',
        gray: isDark
            ? 'bg-gray-700 text-gray-300 border-gray-600'
            : 'bg-gray-100 text-gray-800 border-gray-300',
    };

    const classes = colorClasses[config.color] || colorClasses.gray;

    // Iconos SVG
    const icons = {
        check: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
        x: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
        alert: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        clock: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };

    const icon = config.icon && {$showIcon} ? icons[config.icon] || '' : '';

    return `
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border \${classes}">
            \${icon}
            \${config.label}
        </span>
    `;
}
JS;
    }

    /**
     * Estados predefinidos comunes
     */
    private function getDefaultStatusMap(): array
    {
        return [
            // Estados activo/inactivo
            'active' => ['label' => 'Activo', 'color' => 'green', 'icon' => 'check'],
            'inactive' => ['label' => 'Inactivo', 'color' => 'gray', 'icon' => 'x'],

            // Estados de aprobación
            'pending' => ['label' => 'Pendiente', 'color' => 'yellow', 'icon' => 'clock'],
            'approved' => ['label' => 'Aprobado', 'color' => 'green', 'icon' => 'check'],
            'rejected' => ['label' => 'Rechazado', 'color' => 'red', 'icon' => 'x'],

            // Estados booleanos
            'true' => ['label' => 'Sí', 'color' => 'green', 'icon' => 'check'],
            'false' => ['label' => 'No', 'color' => 'red', 'icon' => 'x'],
            '1' => ['label' => 'Sí', 'color' => 'green', 'icon' => 'check'],
            '0' => ['label' => 'No', 'color' => 'red', 'icon' => 'x'],

            // Estados de publicación
            'published' => ['label' => 'Publicado', 'color' => 'green', 'icon' => 'check'],
            'draft' => ['label' => 'Borrador', 'color' => 'yellow', 'icon' => 'clock'],
            'archived' => ['label' => 'Archivado', 'color' => 'gray', 'icon' => null],

            // Estados de stock (pueden ser útiles)
            'in_stock' => ['label' => 'En Stock', 'color' => 'green', 'icon' => 'check'],
            'low_stock' => ['label' => 'Stock Bajo', 'color' => 'yellow', 'icon' => 'alert'],
            'out_of_stock' => ['label' => 'Agotado', 'color' => 'red', 'icon' => 'x'],
        ];
    }
}
