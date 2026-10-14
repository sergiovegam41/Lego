<?php

use Core\Components\CoreComponent\CoreComponent;

// ─────────────────────────────────────────────────────────────────────────────
// Stub sin atributo #[ApiComponent] — usa el fallback de namespace
// ─────────────────────────────────────────────────────────────────────────────

class ContextStubComponent extends CoreComponent
{
    protected function component(): string { return ''; }

    public function getContext(): array    { return $this->getComponentContext(); }
    public function getId(): string        { return $this->getContextId(); }
    public function getApiRoute(): string  { return $this->getContextApiRoute(); }
}

// ─────────────────────────────────────────────────────────────────────────────
// Tests de contexto
// ─────────────────────────────────────────────────────────────────────────────

test('getComponentContext() devuelve las claves requeridas', function () {
    $context = (new ContextStubComponent())->getContext();

    expect($context)
        ->toHaveKey('id')
        ->toHaveKey('route')
        ->toHaveKey('apiRoute')
        ->toHaveKey('parentMenuId')
        ->toHaveKey('className')
        ->toHaveKey('namespace');
});

test('getComponentContext() se cachea entre llamadas', function () {
    $component = new ContextStubComponent();

    expect($component->getContext())->toBe($component->getContext());
});

test('className en el contexto coincide con el nombre de la clase', function () {
    $context = (new ContextStubComponent())->getContext();

    expect($context['className'])->toBe('ContextStubComponent');
});

test('apiRoute empieza con /api/ cuando tiene valor', function () {
    $context = (new ContextStubComponent())->getContext();

    if ($context['apiRoute'] !== '') {
        expect($context['apiRoute'])->toStartWith('/api/');
    } else {
        expect($context['apiRoute'])->toBe('');
    }
});

test('render() no lanza excepciones al generar el contexto', function () {
    expect(fn () => (new ContextStubComponent())->render())->not->toThrow(Exception::class);
});
