<?php

declare(strict_types=1);

use Lego\Data\Fields\Boolean;
use Lego\Data\Fields\Date;
use Lego\Data\Fields\Email;
use Lego\Data\Fields\InvalidValue;
use Lego\Data\Fields\Money;
use Lego\Data\Fields\Number;
use Lego\Data\Fields\Select;
use Lego\Data\Fields\Text;
use Lego\Data\Fields\TextArea;

it('treats empty input as null, except booleans', function () {
    expect(Text::make('a', 'A')->normalize('   '))->toBeNull()
        ->and(Number::make('a', 'A')->normalize(''))->toBeNull()
        ->and(Boolean::make('a', 'A')->normalize(null))->toBeFalse();
});

it('trims and bounds text', function () {
    $field = Text::make('name', 'Nombre')->min(2)->max(5);

    expect($field->normalize('  Ana '))->toBe('Ana')
        ->and($field->check('A')[0]['rule'])->toBe('min')
        ->and($field->check('Ana María')[0]['rule'])->toBe('max')
        ->and($field->check('Ana'))->toBe([])
        ->and(Text::make('a', 'A')->maxLength())->toBe(255)
        ->and(TextArea::make('a', 'A')->maxLength())->toBeNull();
});

it('lowercases and validates emails', function () {
    $field = Email::make('email', 'Correo');

    expect($field->normalize(' Ana@Example.COM '))->toBe('ana@example.com')
        ->and($field->check('ana@example.com'))->toBe([])
        ->and($field->check('not-an-email')[0]['rule'])->toBe('email');
});

it('casts and bounds numbers', function () {
    $integer = Number::make('visits', 'Visitas')->integer()->min(0)->max(10);

    expect($integer->normalize('7'))->toBe(7)
        ->and($integer->normalize(3.0))->toBe(3)
        ->and(fn () => $integer->normalize('3.5'))->toThrow(InvalidValue::class)
        ->and(fn () => $integer->normalize('abc'))->toThrow(InvalidValue::class)
        ->and(fn () => $integer->normalize(true))->toThrow(InvalidValue::class)
        ->and($integer->check(-1)[0]['rule'])->toBe('min')
        ->and($integer->check(11)[0]['rule'])->toBe('max')
        ->and(Number::make('x', 'X')->normalize('2.5'))->toBe(2.5);
});

it('keeps money exact as a two-decimal string', function (mixed $raw, string $stored) {
    expect(Money::make('credit', 'Crédito')->normalize($raw))->toBe($stored);
})->with([
    ['1500', '1500.00'],
    ['1500,5', '1500.50'],
    [12.25, '12.25'],
    ['007.10', '7.10'],
    ['-0.5', '-0.50'],
]);

it('rejects malformed amounts and enforces money bounds', function () {
    $field = Money::make('credit', 'Crédito')->min(0)->max('1000.50');

    expect(fn () => $field->normalize('1.234'))->toThrow(InvalidValue::class)
        ->and(fn () => $field->normalize('abc'))->toThrow(InvalidValue::class)
        ->and($field->check('-0.01')[0]['rule'])->toBe('min')
        ->and($field->check('1000.51')[0]['rule'])->toBe('max')
        ->and($field->check('1000.50'))->toBe([])
        ->and($field->display('1234567.5'))->toBe('1.234.567,50');
});

it('understands form and JSON booleans', function (mixed $raw, bool $expected) {
    expect(Boolean::make('active', 'Activo')->normalize($raw))->toBe($expected);
})->with([
    ['1', true], ['on', true], ['true', true], [true, true], [1, true],
    ['0', false], ['off', false], ['false', false], [false, false], [0, false], ['', false],
]);

it('accepts only real calendar dates', function () {
    $field = Date::make('since', 'Desde');

    expect($field->normalize('2026-02-28'))->toBe('2026-02-28')
        ->and(fn () => $field->normalize('2026-02-30'))->toThrow(InvalidValue::class)
        ->and(fn () => $field->normalize('28/02/2026'))->toThrow(InvalidValue::class)
        ->and($field->display('2026-02-28'))->toBe('28/02/2026');
});

it('limits selects to their options', function () {
    $field = Select::make('tier', 'Nivel')->options(['basic' => 'Básico', 'gold' => 'Oro']);

    expect($field->check('gold'))->toBe([])
        ->and($field->check('platinum')[0]['rule'])->toBe('in')
        ->and($field->display('gold'))->toBe('Oro');
});

it('describes itself as JSON Schema, nullable unless required', function () {
    expect(Text::make('name', 'Nombre')->required()->max(120)->jsonSchema())
        ->toBe(['title' => 'Nombre', 'type' => 'string', 'maxLength' => 120])
        ->and(Select::make('tier', 'Nivel')->options(['a' => 'A'])->help('Plan')->jsonSchema())
        ->toBe(['title' => 'Nivel', 'description' => 'Plan', 'type' => ['string', 'null'], 'enum' => ['a']])
        ->and(Email::make('email', 'Correo')->required()->jsonSchema()['format'])->toBe('email');
});

it('rejects field names that are not snake_case', function () {
    Text::make('Full Name', 'Nombre');
})->throws(InvalidArgumentException::class);
