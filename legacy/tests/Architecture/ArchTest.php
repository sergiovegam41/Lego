<?php

/*
|--------------------------------------------------------------------------
| Architecture Tests — Contratos del LEGO Framework
|--------------------------------------------------------------------------
|
| Reglas específicas por clase, no por namespace completo.
| Así son precisas y no se rompen por violaciones colaterales.
|
*/

// ─────────────────────────────────────────────────────────────────────────────
// CoreComponent — la clase base del framework
// ─────────────────────────────────────────────────────────────────────────────

arch('CoreComponent es abstracto — no puede instanciarse directamente')
    ->expect('Core\Components\CoreComponent\CoreComponent')
    ->toBeAbstract();

arch('CoreComponent usa ComponentContextTrait')
    ->expect('Core\Components\CoreComponent\CoreComponent')
    ->toUseTrait('Core\Traits\ComponentContextTrait');

// ─────────────────────────────────────────────────────────────────────────────
// Controllers — son abstractos (no instanciables directamente)
// ─────────────────────────────────────────────────────────────────────────────

arch('AbstractCrudController es abstracto')
    ->expect('Core\Controllers\AbstractCrudController')
    ->toBeAbstract();

arch('AbstractGetController es abstracto')
    ->expect('Core\Controllers\AbstractGetController')
    ->toBeAbstract();

// ─────────────────────────────────────────────────────────────────────────────
// Routing — no depende de lógica de App
// ─────────────────────────────────────────────────────────────────────────────

arch('el Router no usa namespaces de App')
    ->expect('Core\Router')
    ->not->toUse('App');

arch('ApiCrudRouter no usa namespaces de App')
    ->expect('Core\Routing\ApiCrudRouter')
    ->not->toUse('App');

arch('ApiGetRouter no usa namespaces de App')
    ->expect('Core\Routing\ApiGetRouter')
    ->not->toUse('App');

// ─────────────────────────────────────────────────────────────────────────────
// Attributes — decoradores puros, sin dependencias de negocio
// ─────────────────────────────────────────────────────────────────────────────

arch('ApiComponent attribute no usa App')
    ->expect('Core\Attributes\ApiComponent')
    ->not->toUse('App');

arch('ApiCrudResource attribute no usa App')
    ->expect('Core\Attributes\ApiCrudResource')
    ->not->toUse('App');

// ─────────────────────────────────────────────────────────────────────────────
// ResponseDTO — sin efectos secundarios ni dependencias
// ─────────────────────────────────────────────────────────────────────────────

arch('ResponseDTO no usa App')
    ->expect('Core\Models\ResponseDTO')
    ->not->toUse('App');

// ─────────────────────────────────────────────────────────────────────────────
// Seguridad — sin código de debug en producción
// ─────────────────────────────────────────────────────────────────────────────

arch('sin var_dump en controllers de App')
    ->expect('App\Controllers')
    ->not->toUse('var_dump');

arch('sin dd() en controllers de App')
    ->expect('App\Controllers')
    ->not->toUse('dd');
