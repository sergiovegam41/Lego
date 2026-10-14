<?php

declare(strict_types=1);

use Lego\Errors\NotFound;
use Lego\Http\Request;
use Lego\Http\StaticFiles;

function staticFiles(): StaticFiles
{
    return new StaticFiles(['/_assets/' => fixture('assets')]);
}

function expectNotFound(string $uri): void
{
    try {
        staticFiles()->serve(Request::create('GET', $uri));
        test()->fail("Expected 404 for {$uri}");
    } catch (NotFound $e) {
        expect($e->status())->toBe(404);
    }
}

it('serves a file with its MIME type and an ETag', function () {
    $response = staticFiles()->serve(Request::create('GET', '/_assets/app.css'));

    expect($response->status)->toBe(200)
        ->and($response->body)->toBe(".app { color: red; }\n")
        ->and($response->header('content-type'))->toBe('text/css; charset=utf-8')
        ->and($response->header('etag'))->toMatch('/^"[0-9a-f]+-[0-9a-f]+"$/')
        ->and($response->header('cache-control'))->toBe('no-cache');
});

it('serves files in subfolders', function () {
    $response = staticFiles()->serve(Request::create('GET', '/_assets/sub/x.js'));

    expect($response->header('content-type'))->toBe('text/javascript; charset=utf-8');
});

it('caches versioned URLs for a year', function () {
    $response = staticFiles()->serve(Request::create('GET', '/_assets/app.css?v=abc'));

    expect($response->header('cache-control'))->toBe('public, max-age=31536000, immutable');
});

it('answers 304 when the ETag matches', function () {
    $etag = staticFiles()->serve(Request::create('GET', '/_assets/app.css'))->header('etag');
    $response = staticFiles()->serve(Request::create('GET', '/_assets/app.css', headers: ['If-None-Match' => $etag]));

    expect($response->status)->toBe(304)->and($response->body)->toBe('');
});

it('never serves PHP sources', function () {
    expectNotFound('/_assets/secret.php');
});

it('returns 404 for missing files', function () {
    expectNotFound('/_assets/missing.css');
});

it('blocks path traversal outside the mount', function (string $uri) {
    expectNotFound($uri);
})->with([
    '/_assets/../outside.css',
    '/_assets/%2e%2e/outside.css',
    '/_assets/sub/../../outside.css',
    '/_assets/..%2foutside.css',
]);

it('ignores paths outside its mounts and non-GET methods', function () {
    expect(staticFiles()->serve(Request::create('GET', '/users')))->toBeNull()
        ->and(staticFiles()->serve(Request::create('POST', '/_assets/app.css')))->toBeNull()
        ->and(staticFiles()->serve(Request::create('GET', '/_assets')))->toBeNull();
});

it('skips mounts whose folder does not exist', function () {
    expect((new StaticFiles(['/_x/' => fixture('does-not-exist')]))->mounts())->toBe([]);
});
