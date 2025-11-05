<?php
/**
 * Barrel Export - Navigation Components
 *
 * Permite importar múltiples componentes de navegación desde un solo namespace:
 * use Components\Shared\Navigation\Navigation\{Breadcrumb};
 */

// Breadcrumb
// NOTA: Solo crear alias si la clase fuente existe para evitar warnings
if (!class_exists('Components\Shared\Navigation\Navigation\Breadcrumb') && class_exists(\Components\Shared\Navigation\BreadcrumbComponent\BreadcrumbComponent::class)) {
    class_alias(
        \Components\Shared\Navigation\BreadcrumbComponent\BreadcrumbComponent::class,
        'Components\Shared\Navigation\Navigation\Breadcrumb'
    );
}
