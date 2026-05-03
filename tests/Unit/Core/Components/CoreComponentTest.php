<?php

use Core\Components\CoreComponent\CoreComponent;

// ─────────────────────────────────────────────────────────────────────────────
// Stub concreto para testear CoreComponent (que es abstracto)
// ─────────────────────────────────────────────────────────────────────────────

class StubComponent extends CoreComponent
{
    public function __construct(private string $content = '<div>stub</div>')
    {}

    protected function component(): string
    {
        return $this->content;
    }

    public function pushChild(mixed $child): void
    {
        $this->children[] = $child;
    }

    public function testRenderChildren(): string
    {
        return $this->renderChildren();
    }

    public function testRenderSlot(array $slot): string
    {
        return $this->renderSlot($slot);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// html() — solo el contenido, sin imports
// ─────────────────────────────────────────────────────────────────────────────

test('html() devuelve solo el contenido del componente', function () {
    $component = new StubComponent('<p>Hola LEGO</p>');

    expect($component->html())->toBe('<p>Hola LEGO</p>');
});

test('render() contiene el contenido del componente', function () {
    $component = new StubComponent('<section>contenido</section>');

    expect($component->render())->toContain('<section>contenido</section>');
});

// ─────────────────────────────────────────────────────────────────────────────
// renderChildren() — composición de componentes
// ─────────────────────────────────────────────────────────────────────────────

test('renderChildren() devuelve string vacío sin children', function () {
    expect((new StubComponent())->testRenderChildren())->toBe('');
});

test('renderChildren() renderiza children de tipo string', function () {
    $component = new StubComponent();
    $component->pushChild('<span>hijo</span>');

    expect($component->testRenderChildren())->toBe('<span>hijo</span>');
});

test('renderChildren() renderiza instancias CoreComponent anidadas', function () {
    $parent = new StubComponent();
    $parent->pushChild(new StubComponent('<b>hijo component</b>'));

    expect($parent->testRenderChildren())->toContain('<b>hijo component</b>');
});

test('renderChildren() filtra null y false', function () {
    $component = new StubComponent();
    $component->pushChild(null);
    $component->pushChild('<p>visible</p>');
    $component->pushChild(false);

    expect($component->testRenderChildren())->toBe('<p>visible</p>');
});

test('renderChildren() respeta el orden de los children', function () {
    $component = new StubComponent();
    $component->pushChild('<div>primero</div>');
    $component->pushChild('<div>segundo</div>');
    $component->pushChild('<div>tercero</div>');

    $result = $component->testRenderChildren();

    expect(strpos($result, 'primero'))->toBeLessThan(strpos($result, 'segundo'));
    expect(strpos($result, 'segundo'))->toBeLessThan(strpos($result, 'tercero'));
});

test('renderChildren() renderiza arrays anidados de children', function () {
    $component = new StubComponent();
    $component->pushChild(['<li>item 1</li>', '<li>item 2</li>']);

    $result = $component->testRenderChildren();

    expect($result)->toContain('<li>item 1</li>')->toContain('<li>item 2</li>');
});

// ─────────────────────────────────────────────────────────────────────────────
// renderSlot() — slots nombrados (Card, Modal, Layout)
// ─────────────────────────────────────────────────────────────────────────────

test('renderSlot() devuelve string vacío para slot vacío', function () {
    expect((new StubComponent())->testRenderSlot([]))->toBe('');
});

test('renderSlot() renderiza un CoreComponent en el slot', function () {
    $component = new StubComponent();
    $slotChild = new StubComponent('<header>encabezado</header>');

    expect($component->testRenderSlot([$slotChild]))->toContain('<header>encabezado</header>');
});

test('renderSlot() renderiza strings en el slot', function () {
    expect((new StubComponent())->testRenderSlot(['<p>texto slot</p>']))->toBe('<p>texto slot</p>');
});

test('renderSlot() filtra null y false en el slot', function () {
    $result = (new StubComponent())->testRenderSlot([null, false, '<p>real</p>', null]);

    expect($result)->toBe('<p>real</p>');
});

test('renderSlot() renderiza múltiples items en orden', function () {
    $component = new StubComponent();
    $result = $component->testRenderSlot([
        new StubComponent('<h1>titulo</h1>'),
        new StubComponent('<p>cuerpo</p>'),
    ]);

    expect($result)->toContain('<h1>titulo</h1>')->toContain('<p>cuerpo</p>');
    expect(strpos($result, 'titulo'))->toBeLessThan(strpos($result, 'cuerpo'));
});
