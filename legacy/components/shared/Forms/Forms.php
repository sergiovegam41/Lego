<?php
/**
 * Forms Barrel Export
 *
 * PROPÓSITO:
 * Agrupa todos los componentes de formulario, permitiendo
 * importarlos con un solo use statement.
 *
 * FILOSOFÍA FLUTTER/REACT:
 * Similar a los "barrel exports" de Flutter o los index.js de React,
 * este archivo permite escribir código más limpio y organizado.
 *
 * USO:
 * use Components\Shared\Forms\Forms;
 *
 * new Forms\InputText(...)
 * new Forms\Select(...)
 * new Forms\Button(...)
 * new Forms\Form(...)
 *
 * VENTAJAS:
 * - Un solo import en lugar de 10+ imports
 * - Namespace claro y organizado
 * - Fácil descubrimiento de componentes (autocomplete)
 * - Código más limpio y legible
 */

// Re-exportar componentes con class_alias para preservar rutas CSS/JS
// Los aliases se crean dinámicamente en runtime
if (!class_exists('Components\Shared\Forms\Forms\Form')) {
    class_alias(\Components\Shared\Forms\FormComponent\FormComponent::class, 'Components\Shared\Forms\Forms\Form');
}
if (!class_exists('Components\Shared\Forms\Forms\FormGroup')) {
    class_alias(\Components\Shared\Forms\FormGroupComponent\FormGroupComponent::class, 'Components\Shared\Forms\Forms\FormGroup');
}
if (!class_exists('Components\Shared\Forms\Forms\FormActions')) {
    class_alias(\Components\Shared\Forms\FormActionsComponent\FormActionsComponent::class, 'Components\Shared\Forms\Forms\FormActions');
}
if (!class_exists('Components\Shared\Forms\Forms\FormRow')) {
    class_alias(\Components\Shared\Forms\FormRowComponent\FormRowComponent::class, 'Components\Shared\Forms\Forms\FormRow');
}
if (!class_exists('Components\Shared\Forms\Forms\InputText')) {
    class_alias(\Components\Shared\Forms\InputTextComponent\InputTextComponent::class, 'Components\Shared\Forms\Forms\InputText');
}
if (!class_exists('Components\Shared\Forms\Forms\TextArea')) {
    class_alias(\Components\Shared\Forms\TextAreaComponent\TextAreaComponent::class, 'Components\Shared\Forms\Forms\TextArea');
}
if (!class_exists('Components\Shared\Forms\Forms\Select')) {
    class_alias(\Components\Shared\Forms\SelectComponent\SelectComponent::class, 'Components\Shared\Forms\Forms\Select');
}
if (!class_exists('Components\Shared\Forms\Forms\Checkbox')) {
    class_alias(\Components\Shared\Forms\CheckboxComponent\CheckboxComponent::class, 'Components\Shared\Forms\Forms\Checkbox');
}
if (!class_exists('Components\Shared\Forms\Forms\Radio')) {
    class_alias(\Components\Shared\Forms\RadioComponent\RadioComponent::class, 'Components\Shared\Forms\Forms\Radio');
}
if (!class_exists('Components\Shared\Forms\Forms\Button')) {
    class_alias(\Components\Shared\Forms\ButtonComponent\ButtonComponent::class, 'Components\Shared\Forms\Forms\Button');
}
