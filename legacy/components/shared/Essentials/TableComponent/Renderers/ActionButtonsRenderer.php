<?php
namespace Components\Shared\Essentials\TableComponent\Renderers;

/**
 * ActionButtonsRenderer - Renderer para botones de acción (editar, eliminar, etc.)
 *
 * FILOSOFÍA LEGO:
 * Genera botones de acción consistentes con el tema del framework.
 * Elimina la necesidad de escribir JavaScript manual para cada CRUD.
 *
 * CARACTERÍSTICAS:
 * - Botones Edit y Delete automáticos
 * - Soporte para botones personalizados
 * - Responsive a cambios de tema
 * - Tooltips informativos
 * - Iconos SVG inline (sin dependencias)
 *
 * EJEMPLO BÁSICO:
 * ```php
 * ActionButtonsRenderer::create(
 *     editFunction: 'editProduct',
 *     deleteFunction: 'deleteProduct'
 * )
 * ```
 *
 * EJEMPLO CON BOTONES PERSONALIZADOS:
 * ```php
 * ActionButtonsRenderer::create(
 *     editFunction: 'editProduct',
 *     deleteFunction: 'deleteProduct',
 *     customButtons: [
 *         [
 *             'icon' => 'eye',
 *             'function' => 'viewProduct',
 *             'tooltip' => 'Ver detalles',
 *             'color' => 'blue'
 *         ]
 *     ]
 * )
 * ```
 */
class ActionButtonsRenderer extends CellRenderer
{
    private string $editFunction;
    private string $deleteFunction;
    private array $customButtons;
    private bool $showEdit;
    private bool $showDelete;
    private string $idField;

    private function __construct(
        string $editFunction = '',
        string $deleteFunction = '',
        array $customButtons = [],
        bool $showEdit = true,
        bool $showDelete = true,
        string $idField = 'id'
    ) {
        $this->editFunction = $editFunction;
        $this->deleteFunction = $deleteFunction;
        $this->customButtons = $customButtons;
        $this->showEdit = $showEdit;
        $this->showDelete = $showDelete;
        $this->idField = $idField;
    }

    /**
     * Factory method con named arguments
     */
    public static function create(
        string $editFunction = '',
        string $deleteFunction = '',
        array $customButtons = [],
        bool $showEdit = true,
        bool $showDelete = true,
        string $idField = 'id'
    ): self {
        return new self(
            $editFunction,
            $deleteFunction,
            $customButtons,
            $showEdit,
            $showDelete,
            $idField
        );
    }

    public function toJavaScript(): string
    {
        $editFn = $this->escapeJs($this->editFunction);
        $deleteFn = $this->escapeJs($this->deleteFunction);
        $idField = $this->escapeJs($this->idField);

        // Generar botones personalizados
        $customButtonsCode = '';
        foreach ($this->customButtons as $btn) {
            $icon = $btn['icon'] ?? 'circle';
            $function = $this->escapeJs($btn['function'] ?? '');
            $tooltip = $this->escapeJs($btn['tooltip'] ?? '');
            $color = $btn['color'] ?? 'gray';

            $customButtonsCode .= $this->generateCustomButton($icon, $function, $tooltip, $color);
        }

        $editButton = $this->showEdit && $this->editFunction ?
            $this->generateEditButton() : '';

        $deleteButton = $this->showDelete && $this->deleteFunction ?
            $this->generateDeleteButton() : '';

        return <<<JS
(params) => {
    const id = params.data.{$idField};
    const isDark = document.documentElement.classList.contains('dark');

    const buttonClass = isDark
        ? 'px-2 py-1 rounded hover:bg-gray-700 transition-colors duration-200'
        : 'px-2 py-1 rounded hover:bg-gray-100 transition-colors duration-200';

    return `
        <div class="flex gap-1 items-center justify-center">
            {$customButtonsCode}
            {$editButton}
            {$deleteButton}
        </div>
    `;
}
JS;
    }

    private function generateEditButton(): string
    {
        return <<<HTML
<button
    onclick="{$this->editFunction}(\${id})"
    class="\${buttonClass} text-blue-600 dark:text-blue-400"
    title="Editar"
    type="button">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
</button>
HTML;
    }

    private function generateDeleteButton(): string
    {
        return <<<HTML
<button
    onclick="{$this->deleteFunction}(\${id})"
    class="\${buttonClass} text-red-600 dark:text-red-400"
    title="Eliminar"
    type="button">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
    </svg>
</button>
HTML;
    }

    private function generateCustomButton(string $icon, string $function, string $tooltip, string $color): string
    {
        $iconSvg = $this->getIconSvg($icon);
        $colorClasses = $this->getColorClasses($color);

        return <<<HTML
<button
    onclick="{$function}(\${id})"
    class="\${buttonClass} {$colorClasses}"
    title="{$tooltip}"
    type="button">
    {$iconSvg}
</button>
HTML;
    }

    private function getIconSvg(string $icon): string
    {
        return match($icon) {
            'eye' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
            'download' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>',
            'check' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            'x' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
            'copy' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
            default => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>'
        };
    }

    private function getColorClasses(string $color): string
    {
        return match($color) {
            'blue' => 'text-blue-600 dark:text-blue-400',
            'green' => 'text-green-600 dark:text-green-400',
            'red' => 'text-red-600 dark:text-red-400',
            'yellow' => 'text-yellow-600 dark:text-yellow-400',
            'purple' => 'text-purple-600 dark:text-purple-400',
            'gray' => 'text-gray-600 dark:text-gray-400',
            default => 'text-gray-600 dark:text-gray-400'
        };
    }
}
