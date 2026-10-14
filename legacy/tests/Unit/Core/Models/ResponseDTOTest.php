<?php

use Core\Models\ResponseDTO;

test('se crea correctamente con éxito', function () {
    $dto = new ResponseDTO(true, 'Operación exitosa', ['id' => 1]);

    expect($dto->success)->toBeTrue()
        ->and($dto->msj)->toBe('Operación exitosa')
        ->and($dto->data)->toBe(['id' => 1])
        ->and($dto->status_code)->toBeNull();
});

test('se crea correctamente con error y status code', function () {
    $dto = new ResponseDTO(false, 'No encontrado', null, 404);

    expect($dto->success)->toBeFalse()
        ->and($dto->msj)->toBe('No encontrado')
        ->and($dto->data)->toBeNull()
        ->and($dto->status_code)->toBe(404);
});

test('data acepta string, int, array y null', function () {
    expect((new ResponseDTO(true, 'ok', 'texto'))->data)->toBe('texto');
    expect((new ResponseDTO(true, 'ok', 42))->data)->toBe(42);
    expect((new ResponseDTO(true, 'ok', ['a', 'b']))->data)->toBe(['a', 'b']);
    expect((new ResponseDTO(true, 'ok', null))->data)->toBeNull();
});

test('status_code es null por defecto', function () {
    expect((new ResponseDTO(true, 'ok', []))->status_code)->toBeNull();
});
