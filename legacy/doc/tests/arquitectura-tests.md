# Tests de Arquitectura

Lego usa Pest para tests unitarios y, más importante, para **tests de arquitectura** — verificaciones automáticas de que el código sigue las convenciones del framework.

Relacionado: [[arquitectura/capas]] · [[componentes/core-component]]

Código: `tests/Architecture/` · `tests/Unit/`

---

## Por Qué Tests de Arquitectura

Los tests unitarios verifican que el código funciona. Los tests de arquitectura verifican que el código está donde debería estar y se comporta según las reglas del framework.

Ejemplos:
- Todos los componentes extienden `CoreComponent`
- Los modelos están en `App\Models`
- Las pantallas implementan `ScreenInterface`
- Los controladores no importan código de `components/`

## Pest Architecture Plugin

```php
// tests/Architecture/ArchitectureTest.php

arch('todos los componentes extienden CoreComponent')
    ->expect('Components')
    ->classes()
    ->toExtend('Core\Components\CoreComponent\CoreComponent');

arch('los modelos están en App\Models')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('las pantallas implementan ScreenInterface')
    ->expect('Components')
    ->classes()
    ->whereName('/Screen$/')
    ->toImplement('Core\Contracts\ScreenInterface');

arch('Core no depende de App ni de components')
    ->expect('Core')
    ->not->toUse(['App', 'Components']);
```

## Tests Unitarios

```php
// tests/Unit/Core/Components/CoreComponentTest.php

it('renderiza HTML del componente', function () {
    $component = new MiComponent(titulo: 'Test');
    expect($component->render())->toContain('<h1>Test</h1>');
});

it('inyecta CSS si CSS_PATHS está definido', function () {
    $component = new ComponenteConCSS();
    expect($component->render())->toContain('<link rel="stylesheet"');
});
```

## Ejecutar Tests

```bash
# Todos los tests
./vendor/bin/pest

# Solo arquitectura
./vendor/bin/pest tests/Architecture

# Solo unitarios
./vendor/bin/pest tests/Unit

# Con coverage
./vendor/bin/pest --coverage
```

## Estructura

```
tests/
├── Architecture/
│   └── ArchitectureTest.php    ← reglas de arquitectura
├── Unit/
│   ├── Core/
│   │   └── Components/
│   │       └── CoreComponentTest.php
│   └── App/
│       └── Models/
└── Pest.php                     ← configuración global
```

## Convenciones

- Un archivo de test por clase a probar
- Mismo namespace que la clase original (con prefijo `Tests\`)
- Nombre de archivo: `{ClassName}Test.php`
- Usar `it()` para casos de uso, `test()` para casos genéricos
- Usar `arch()` para reglas de arquitectura

## Visión

> Los tests de arquitectura formarán una "constitución" del framework: un conjunto de reglas inviolables que se ejecutan en CI antes de cada merge. Cualquier violación falla el build. Esto garantiza que la arquitectura se mantenga consistente sin depender de revisiones manuales en cada PR.
