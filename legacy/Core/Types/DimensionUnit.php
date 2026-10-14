<?php

namespace Core\Types;

/**
 * DimensionUnit - Unidades de medida para dimensiones
 *
 * Define las unidades permitidas para anchos/altos de componentes.
 * Cada unidad representa una forma diferente de definir tamaños.
 *
 * @package Core\Types
 */
enum DimensionUnit: string
{
    /**
     * Pixels - Unidad fija absoluta
     * Ejemplo: 80px, 200px
     */
    case PIXELS = 'px';

    /**
     * Porcentaje - Relativo al contenedor
     * Ejemplo: 25%, 50%, 100%
     */
    case PERCENT = '%';

    /**
     * Flex - Flexible, se expande según espacio disponible
     * Ejemplo: flex: 1, flex: 2
     */
    case FLEX = 'flex';

    /**
     * Auto - Tamaño automático basado en contenido
     */
    case AUTO = 'auto';
}

