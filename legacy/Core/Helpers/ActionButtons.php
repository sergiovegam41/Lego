<?php

namespace Core\Helpers;

/**
 * ActionButtons - Helper para generar cellRenderers de botones de acción
 *
 * FILOSOFÍA LEGO:
 * Elimina duplicación de código en tablas, generando cellRenderers
 * que usan el sistema de componentes dinámicos.
 *
 * ANTES (50+ líneas de HTML hardcoded):
 * ```php
 * cellRenderer: "params => {
 *     const productId = params.data.id;
 *     return `
 *         <div>
 *             <button onclick=\"edit(${productId})\"...>
 *                 // 20 líneas de HTML/CSS
 *             </button>
 *             <button onclick=\"delete(${productId})\"...>
 *                 // 20 líneas de HTML/CSS
 *             </button>
 *         </div>
 *     `;
 * }"
 * ```
 *
 * AHORA (1 línea):
 * ```php
 * cellRenderer: ActionButtons::dynamic(['edit', 'delete'])
 * ```
 *
 * CARACTERÍSTICAS:
 * - Usa window.lego.components.get() bajo el capó
 * - Batch rendering automático por fila
 * - IDs únicos por fila para evitar colisiones
 * - Configuración flexible de variantes/íconos
 */
class ActionButtons
{
    /**
     * Mapeo de acciones a configuración de botones
     */
    private const ACTION_CONFIG = [
        'edit' => [
            'icon' => 'create-outline',
            'variant' => 'ghost',
            'title' => 'Editar',
            'function' => 'editProduct'
        ],
        'delete' => [
            'icon' => 'trash-outline',
            'variant' => 'danger',
            'title' => 'Eliminar',
            'function' => 'deleteProduct'
        ],
        'view' => [
            'icon' => 'eye-outline',
            'variant' => 'ghost',
            'title' => 'Ver',
            'function' => 'viewProduct'
        ],
        'duplicate' => [
            'icon' => 'copy-outline',
            'variant' => 'ghost',
            'title' => 'Duplicar',
            'function' => 'duplicateProduct'
        ],
    ];

    /**
     * Generar cellRenderer para botones de acción dinámicos
     *
     * @param array $actions Lista de acciones (ej: ['edit', 'delete'])
     * @param array $config Configuración opcional
     *                      - idField: Campo que contiene el ID (default: 'id')
     *                      - size: Tamaño de botones (default: 'medium')
     *                      - gap: Espacio entre botones (default: '4px')
     *                      - customActions: Configuración custom por acción
     *
     * @return string JavaScript cellRenderer function
     */
    public static function dynamic(array $actions, array $config = []): string
    {
        // Configuración por defecto
        $idField = $config['idField'] ?? 'id';
        $size = $config['size'] ?? 'medium';
        $gap = $config['gap'] ?? '4px';
        $customActions = $config['customActions'] ?? [];

        // Generar parámetros para cada acción
        $paramsArray = [];
        foreach ($actions as $action) {
            // Usar configuración custom o default
            $actionConfig = $customActions[$action] ?? self::ACTION_CONFIG[$action] ?? null;

            if (!$actionConfig) {
                throw new \InvalidArgumentException(
                    "Acción desconocida: '{$action}'. " .
                    "Acciones disponibles: " . implode(', ', array_keys(self::ACTION_CONFIG)) .
                    " o define customActions."
                );
            }

            $paramsArray[] = [
                'icon' => $actionConfig['icon'],
                'variant' => $actionConfig['variant'],
                'title' => $actionConfig['title'],
                'size' => $size,
                'onClick' => "{$actionConfig['function']}(ENTITY_ID)",
            ];
        }

        // Convertir array PHP a JSON para JavaScript
        $paramsJson = json_encode($paramsArray, JSON_UNESCAPED_UNICODE);

        // Generar cellRenderer JavaScript
        // IMPORTANTE: Usa async/await y window.lego.components
        return <<<JS
async params => {
    const entityId = params.data.{$idField};

    // Crear contenedor
    const container = document.createElement('div');
    container.style.display = 'flex';
    container.style.gap = '{$gap}';
    container.style.alignItems = 'center';
    container.style.justifyContent = 'center';

    try {
        // Preparar parámetros reemplazando ENTITY_ID
        const buttonParams = {$paramsJson}.map(config => ({
            ...config,
            onClick: config.onClick.replace('ENTITY_ID', entityId)
        }));

        // Batch rendering - 1 request para todos los botones
        const buttons = await window.lego.components
            .get('icon-button')
            .params(buttonParams);

        // Insertar botones en el contenedor
        container.innerHTML = buttons.join('');

    } catch (error) {
        console.error('[ActionButtons] Error rendering buttons:', error);
        container.innerHTML = '<span style="color: #ef4444;">Error</span>';
    }

    return container;
}
JS;
    }

    /**
     * Generar cellRenderer estático (sin componentes dinámicos)
     *
     * Útil para casos donde no se necesita batch rendering
     * o se quiere evitar requests asíncronos.
     *
     * @param array $actions Lista de acciones
     * @param array $config Configuración opcional
     * @return string JavaScript cellRenderer function
     */
    public static function static(array $actions, array $config = []): string
    {
        $idField = $config['idField'] ?? 'id';
        $size = $config['size'] ?? 'medium';
        $gap = $config['gap'] ?? '4px';
        $customActions = $config['customActions'] ?? [];

        $buttonsHtml = [];

        foreach ($actions as $action) {
            $actionConfig = $customActions[$action] ?? self::ACTION_CONFIG[$action] ?? null;

            if (!$actionConfig) {
                throw new \InvalidArgumentException("Acción desconocida: '{$action}'");
            }

            $buttonsHtml[] = self::generateButtonHtml(
                $actionConfig['icon'],
                $actionConfig['variant'],
                $actionConfig['title'],
                $actionConfig['function'],
                $size
            );
        }

        $buttonsStr = implode("\n", $buttonsHtml);

        return <<<JS
params => {
    const entityId = params.data.{$idField};
    return `
        <div style="display: flex; gap: {$gap}; align-items: center; justify-content: center;">
            {$buttonsStr}
        </div>
    `;
}
JS;
    }

    /**
     * Generar HTML inline para un botón (método estático)
     *
     * @param string $icon Ion icon name
     * @param string $variant Variante (ghost, danger, etc)
     * @param string $title Tooltip
     * @param string $function Función JS a llamar
     * @param string $size Tamaño
     * @return string HTML del botón
     */
    private static function generateButtonHtml(
        string $icon,
        string $variant,
        string $title,
        string $function,
        string $size
    ): string {
        // Mapeo de variantes a colores
        $colors = [
            'ghost' => '#6b7280',
            'danger' => '#ef4444',
            'primary' => '#3b82f6',
        ];

        $color = $colors[$variant] ?? $colors['ghost'];

        return <<<HTML
<button
    onclick="{$function}(\${entityId})"
    style="
        padding: 8px;
        background: transparent;
        color: {$color};
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    "
    onmouseover="this.style.background='rgba(0,0,0,0.05)'"
    onmouseout="this.style.background='transparent'"
    title="{$title}"
>
    <ion-icon name="{$icon}" style="font-size: 20px;"></ion-icon>
</button>
HTML;
    }
}
