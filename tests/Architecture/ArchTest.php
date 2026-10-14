<?php

declare(strict_types=1);

/*
 * Rules that keep Lego consistent (principles P2, P4 and P6). Each rule fails
 * the build instead of living only in documentation.
 */

arch('no debugging leftovers')
    ->expect(['dd', 'dump', 'var_dump', 'var_export', 'print_r', 'phpinfo', 'debug_print_backtrace'])
    ->not->toBeUsed();

arch('the core uses strict types')
    ->expect('Lego')
    ->toUseStrictTypes();

arch('the core never depends on application modules')
    ->expect('Lego')
    ->not->toUse('Modules');

arch('HTTP value objects and services are final')
    ->expect([
        'Lego\Http\Request',
        'Lego\Http\Response',
        'Lego\Http\Router',
        'Lego\Http\Kernel',
        'Lego\Http\StaticFiles',
        'Lego\Http\ErrorRenderer',
    ])
    ->toBeFinal();

arch('components extend the base component')
    ->expect('Lego\Ui')
    ->classes()
    ->toExtend('Lego\View\Component');

/** @return list<string> PHP files under a repository folder. */
function phpFilesIn(string $folder): array
{
    $root = basePath($folder);
    if (!is_dir($root)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    sort($files);

    return $files;
}

it('maps every class to a path with exactly the same case as its namespace', function (string $folder, string $prefix) {
    expect(is_dir(basePath($folder)))->toBeTrue("Missing folder {$folder}");

    foreach (phpFilesIn($folder) as $file) {
        $source = (string) file_get_contents($file);
        if (preg_match('/^namespace\s+([^;]+);/m', $source, $ns) !== 1
            || preg_match('/^(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|trait|enum)\s+(\w+)/m', $source, $type) !== 1) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($ns[1] . '\\' . $type[1], strlen($prefix)));
        $actual = str_replace('\\', '/', substr($file, strlen(basePath($folder)) + 1));

        expect($actual)->toBe($relative . '.php', "Case or path mismatch in {$file}");
    }
})->with([
    ['core', 'Lego\\'],
    ['modules', 'Modules\\'],
]);

it('reads superglobals only in Request and Environment', function () {
    foreach (phpFilesIn('core') as $file) {
        if (in_array(basename($file), ['Request.php', 'Environment.php'], true)) {
            continue;
        }

        expect((string) file_get_contents($file))
            ->not->toMatch('/\$_(SERVER|GET|POST|COOKIE|ENV|REQUEST|FILES|SESSION)\b/', "Superglobal used in {$file}");
    }
});

it('writes output only from Response::send', function () {
    foreach (phpFilesIn('core') as $file) {
        if (basename($file) === 'Response.php') {
            continue;
        }

        expect((string) file_get_contents($file))
            ->not->toMatch('/^\s*(echo|print|exit|die)\b/m', "Direct output or exit in {$file}");
    }
});
