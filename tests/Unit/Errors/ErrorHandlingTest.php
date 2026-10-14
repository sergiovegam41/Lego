<?php

declare(strict_types=1);

use Lego\Errors\ErrorHandling;
use Lego\Log\Logger;

it('turns reported PHP warnings into exceptions', function () {
    // The test runner changes error_reporting; pin it to the production value.
    $previous = error_reporting(E_ALL);

    try {
        ErrorHandling::convert(E_WARNING, 'Undefined array key "missing"', __FILE__, 10);
    } finally {
        error_reporting($previous);
    }
})->throws(ErrorException::class, 'Undefined array key "missing"');

it('lets silenced errors pass', function () {
    $previous = error_reporting(E_ALL & ~E_WARNING);

    try {
        expect(ErrorHandling::convert(E_WARNING, 'ignored'))->toBeFalse();
    } finally {
        error_reporting($previous);
    }
});

it('logs fatal errors on shutdown and ignores the rest', function () {
    $log = fopen('php://memory', 'w+b');
    $logger = new Logger($log);

    ob_start();
    ErrorHandling::onShutdown($logger, null);
    ErrorHandling::onShutdown($logger, ['type' => E_WARNING, 'message' => 'not fatal', 'file' => 'x.php', 'line' => 1]);
    ErrorHandling::onShutdown($logger, ['type' => E_ERROR, 'message' => 'out of memory', 'file' => 'x.php', 'line' => 2]);
    $output = (string) ob_get_clean();

    rewind($log);
    $lines = array_filter(explode(PHP_EOL, (string) stream_get_contents($log)));

    expect($output)->toBe('Ha ocurrido un error inesperado.')
        ->and($lines)->toHaveCount(1)
        ->and(json_decode(reset($lines), true))->toMatchArray(['level' => 'error', 'message' => 'out of memory', 'fatal' => true]);
});
