<?php
/**
 * Barrel Export - Buttons Components
 *
 * Permite importar múltiples componentes de botones desde un solo namespace:
 * use Components\Shared\Buttons\Buttons\{IconButton};
 */

// IconButton
// NOTA: Solo crear alias si la clase fuente existe para evitar warnings
if (!class_exists('Components\Shared\Buttons\Buttons\IconButton') && class_exists(\Components\Shared\Buttons\IconButtonComponent\IconButtonComponent::class)) {
    class_alias(
        \Components\Shared\Buttons\IconButtonComponent\IconButtonComponent::class,
        'Components\Shared\Buttons\Buttons\IconButton'
    );
}
