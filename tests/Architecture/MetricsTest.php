<?php

declare(strict_types=1);

/*
 * The measurable goals of doc/plan-lego-2.md, checked on every run so they
 * cannot drift silently. A failing budget is a design conversation, not a
 * number to bump in passing.
 */

/** Lines of code in files with an extension, without blank lines or comment lines. */
function codeLines(string $folder, string $extension): int
{
    $lines = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(basePath($folder), FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->getExtension() !== $extension) {
            continue;
        }
        foreach (file($file->getPathname()) ?: [] as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '' && preg_match('#^(//|\*|/\*|\*/|\#)#', $trimmed) !== 1) {
                $lines++;
            }
        }
    }

    return $lines;
}

it('keeps the core within its size budget', function () {
    // Plan goal was 3,500 PHP lines; the real budget after phase 5 is pending
    // the owner's decision (doc/lego2/pendientes.md, U6).
    expect(codeLines('core', 'php'))->toBeLessThanOrEqual(5_300)
        ->and(codeLines('core', 'js'))->toBeLessThanOrEqual(2_000)
        ->and(codeLines('core', 'css'))->toBeLessThanOrEqual(1_800);
});

it('depends on at most five runtime packages', function () {
    $require = json_decode((string) file_get_contents(basePath('composer.json')), true)['require'];
    unset($require['php']);

    expect(count($require))->toBeLessThanOrEqual(5)
        ->and(array_keys($require))->toBe(['illuminate/database', 'symfony/console', 'vlucas/phpdotenv']);
});

it('deploys with exactly three services', function () {
    preg_match_all('/^  ([a-z][a-z0-9-]*):\s*$/m', explode("\nvolumes:", (string) file_get_contents(basePath('docker-compose.yml')))[0], $services);

    expect($services[1])->toBe(['app', 'web', 'db']);
});

it('keeps one way to register a route: modules and the core, never route files', function () {
    expect(is_dir(basePath('Routes')))->toBeFalse()
        ->and(glob(basePath('modules/*/routes*.php')) ?: [])->toBe([]);
});
