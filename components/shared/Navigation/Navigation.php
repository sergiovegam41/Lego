<?php
/**
 * Barrel Export - Navigation Components
 *
 * Permite importar múltiples componentes de navegación desde un solo namespace:
 * use Components\Shared\Navigation\Navigation\{Breadcrumb};
 */

// Breadcrumb
if (!class_exists('Components\Shared\Navigation\Navigation\Breadcrumb')) {
    class_alias(
        \Components\Shared\Navigation\BreadcrumbComponent\BreadcrumbComponent::class,
        'Components\Shared\Navigation\Navigation\Breadcrumb'
    );
}
