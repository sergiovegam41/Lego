<?php
/**
 * Essentials Barrel Export
 *
 * PROPÓSITO:
 * Agrupa todos los componentes esenciales de layout y estructura,
 * permitiendo importarlos con un solo use statement.
 *
 * FILOSOFÍA FLUTTER/REACT:
 * Similar a los "barrel exports" de Flutter o los index.js de React,
 * este archivo permite escribir código más limpio y organizado.
 *
 * USO:
 * use Components\Shared\Essentials\Essentials;
 *
 * new Essentials\Column(...)
 * new Essentials\Row(...)
 * new Essentials\Div(...)
 * new Essentials\Grid(...)
 * new Essentials\Fragment(...)
 *
 * VENTAJAS:
 * - Un solo import en lugar de múltiples imports
 * - Namespace claro y organizado
 * - Fácil descubrimiento de componentes (autocomplete)
 * - Código más limpio y legible
 */

// Re-exportar componentes con class_alias para preservar rutas CSS/JS
// Los aliases se crean dinámicamente en runtime
// NOTA: Solo crear alias si la clase fuente existe para evitar warnings
if (!class_exists('Components\Shared\Essentials\Essentials\Column') && class_exists(\Components\Shared\Essentials\ColumnComponent\ColumnComponent::class)) {
    class_alias(\Components\Shared\Essentials\ColumnComponent\ColumnComponent::class, 'Components\Shared\Essentials\Essentials\Column');
}
if (!class_exists('Components\Shared\Essentials\Essentials\Row') && class_exists(\Components\Shared\Essentials\RowComponent\RowComponent::class)) {
    class_alias(\Components\Shared\Essentials\RowComponent\RowComponent::class, 'Components\Shared\Essentials\Essentials\Row');
}
if (!class_exists('Components\Shared\Essentials\Essentials\Div') && class_exists(\Components\Shared\Essentials\DivComponent\DivComponent::class)) {
    class_alias(\Components\Shared\Essentials\DivComponent\DivComponent::class, 'Components\Shared\Essentials\Essentials\Div');
}
if (!class_exists('Components\Shared\Essentials\Essentials\Grid') && class_exists(\Components\Shared\Essentials\GridComponent\GridComponent::class)) {
    class_alias(\Components\Shared\Essentials\GridComponent\GridComponent::class, 'Components\Shared\Essentials\Essentials\Grid');
}
if (!class_exists('Components\Shared\Essentials\Essentials\Fragment') && class_exists(\Components\Shared\FragmentComponent\FragmentComponent::class)) {
    class_alias(\Components\Shared\FragmentComponent\FragmentComponent::class, 'Components\Shared\Essentials\Essentials\Fragment');
}
