<?php
/**
 * Barrel Export - Buttons Components
 *
 * Permite importar múltiples componentes de botones desde un solo namespace:
 * use Components\Shared\Buttons\Buttons\{IconButton};
 */

// IconButton
if (!class_exists('Components\Shared\Buttons\Buttons\IconButton')) {
    class_alias(
        \Components\Shared\Buttons\IconButtonComponent\IconButtonComponent::class,
        'Components\Shared\Buttons\Buttons\IconButton'
    );
}
