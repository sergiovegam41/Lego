<?php

namespace Core\Interfaces;

/**
 * DynamicComponentInterface - Interface para componentes renderizables dinámicamente
 *
 * FILOSOFÍA LEGO:
 * Los componentes que implementan esta interface pueden ser renderizados
 * desde JavaScript vía API, manteniendo PHP como única fuente de verdad.
 *
 * CARACTERÍSTICAS:
 * - ID único por tipo de componente (validado en runtime)
 * - Renderizado con parámetros dinámicos
 * - Soporte para batch rendering (múltiples instancias en una petición)
 *
 * EJEMPLO:
 * ```php
 * class IconButtonComponent extends CoreComponent implements DynamicComponentInterface {
 *     public const COMPONENT_ID = 'icon-button';
 *
 *     public static function getComponentId(): string {
 *         return self::COMPONENT_ID;
 *     }
 *
 *     public function renderWithParams(array $params): string {
 *         // Renderizar con $params
 *     }
 * }
 * ```
 *
 * USO DESDE JAVASCRIPT:
 * ```javascript
 * // Batch rendering
 * const buttons = await window.lego.components
 *     .get('icon-button')
 *     .params([
 *         { action: 'edit', entityId: 1 },
 *         { action: 'delete', entityId: 1 }
 *     ]);
 * ```
 */
interface DynamicComponentInterface
{
    /**
     * Obtener el ID único del tipo de componente
     *
     * Este ID se usa para solicitar el componente desde JavaScript.
     * Debe ser único en toda la aplicación.
     *
     * IMPORTANTE: Si dos componentes tienen el mismo ID, se lanzará
     * ComponentIdCollisionException en el primer request.
     *
     * @return string ID único del componente (ej: 'icon-button', 'data-table', etc)
     */
    public static function getComponentId(): string;

    /**
     * Renderizar el componente con parámetros específicos
     *
     * Este método recibe parámetros dinámicos y retorna el HTML renderizado.
     *
     * @param array $params Parámetros para el renderizado
     *                      Ejemplo: ['action' => 'edit', 'entityId' => 14]
     *
     * @return string HTML renderizado del componente
     *
     * @throws \InvalidArgumentException Si faltan parámetros requeridos
     */
    public function renderWithParams(array $params): string;
}
