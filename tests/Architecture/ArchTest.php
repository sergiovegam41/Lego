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

it('keeps every route closed except an explicit allowlist', function () {
    $public = [];
    foreach (app()->router->routes() as $route) {
        if ($route->access() === Lego\Http\Access::Public) {
            $public[] = $route->method . ' ' . $route->path;
        }
    }
    sort($public);

    // Adding a public route is a security decision: update this list on purpose.
    expect($public)->toBe(['GET /health', 'GET /login', 'POST /login']);
});

it('answers anonymous requests to closed routes with 401 or a login redirect', function () {
    $app = app();
    foreach ($app->router->routes() as $route) {
        if ($route->access() === Lego\Http\Access::Public) {
            continue;
        }

        $path = preg_replace('/\{[^}]+\}/', '1', $route->path);
        $status = $app->handle(Lego\Http\Request::create($route->method, $path))->status;

        expect($status)->toBeIn([302, 401], "{$route->method} {$route->path} answered {$status}");
    }
});

/** @return array<string, string> JS files of the core, path => source */
function coreScripts(): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(basePath('core'), FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'js') {
            $files[str_replace('\\', '/', substr($file->getPathname(), strlen(basePath()) + 1))] = (string) file_get_contents($file->getPathname());
        }
    }
    ksort($files);

    return $files;
}

it('keeps the frontend free of dangerous or global patterns', function () {
    expect(coreScripts())->not->toBeEmpty();

    foreach (coreScripts() as $path => $source) {
        expect($source)
            ->not->toMatch('/\bnew Function\b|\beval\s*\(/', "{$path} evaluates code")
            ->not->toMatch('/\bwindow\.[A-Za-z_$][\w$]*\s*=(?!=)/', "{$path} writes a global")
            ->not->toMatch('/\bdocument\.write\b/', "{$path} uses document.write")
            ->not->toMatch('/\bconsole\.(log|debug)\b/', "{$path} logs to the console")
            ->not->toMatch('/\b(alert|prompt)\s*\(|window\.confirm\s*\(/', "{$path} opens a blocking browser dialog");
        expect(str_starts_with(ltrim($source), '/**'))->toBeTrue("{$path} needs a header comment");
    }
});

it('calls fetch only from lego.js', function () {
    foreach (coreScripts() as $path => $source) {
        if ($path !== 'core/Ui/js/lego.js') {
            expect($source)->not->toMatch('/\bfetch\s*\(/', "{$path} calls fetch directly; use api() or fetchFragment()");
        }
    }
});

it('writes no inline executable scripts in components', function () {
    foreach (phpFilesIn('core') as $file) {
        $source = (string) file_get_contents($file);
        preg_match_all('/<script(?![^>]*\b(src=|type="application\/json"|type="importmap"))[^>]*>/', $source, $matches);

        expect($matches[0])->toBe([], "Inline script in {$file}");
    }
});

it('takes every color and font from the design tokens', function () {
    $sheets = [];
    foreach (['core', 'modules'] as $root) {
        if (!is_dir(basePath($root))) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(basePath($root), FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'css' && $file->getFilename() !== 'tokens.css') {
                $sheets[] = $file->getPathname();
            }
        }
    }
    expect($sheets)->not->toBeEmpty();

    foreach ($sheets as $sheet) {
        $source = (string) preg_replace('#/\*.*?\*/#s', '', (string) file_get_contents($sheet));
        expect($source)
            ->not->toMatch('/#[0-9a-fA-F]{3,8}\b|\b(rgba?|hsla?|oklch)\(/', "{$sheet} writes a color; use a --lego-* token")
            ->not->toMatch('/font-family:\s*+(?!var\()/', "{$sheet} names a font; use --lego-font or --lego-font-mono")
            ->not->toMatch('/--lego-[\w-]+\s*:/', "{$sheet} defines a token; tokens live only in tokens.css");
    }
});
