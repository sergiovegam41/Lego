<?php

namespace Core\Exceptions;

/**
 * ComponentIdCollisionException - Exception lanzada cuando dos componentes intentan usar el mismo ID
 *
 * FILOSOFÍA LEGO:
 * Los IDs de componentes deben ser únicos para evitar conflictos.
 * Esta exception se lanza cuando se detecta una colisión en runtime.
 *
 * EJEMPLO DE ERROR:
 * ```
 * Component ID collision detected!
 * ID: 'icon-button'
 * Already registered by: Components\Shared\Actions\IconButtonComponent
 * Attempted by: Components\App\Custom\IconButtonComponent
 *
 * Solution: Change COMPONENT_ID constant in one of these classes.
 * ```
 */
class ComponentIdCollisionException extends \RuntimeException
{
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
