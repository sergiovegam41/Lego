<?php

declare(strict_types=1);

use Lego\View\Assets;
use Lego\View\Document;
use Tests\Fixtures\Components\Card;

beforeEach(function () {
    Assets::mount(['/_fx/' => fixture('Components')]);
});

it('escapes values by default', function () {
    $html = (string) new Card('<script>alert(1)</script>', '"x"');

    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->and($html)->toContain('&quot;x&quot;')
        ->and($html)->not->toContain('<script>');
});

it('renders without a collector', function () {
    expect((string) new Card('Hola'))->toContain('<h2>Hola</h2>');
});

it('collects the assets of nested components once', function () {
    [, $assets] = Assets::collect(fn (): string => new Card('a') . new Card('b'));

    expect($assets->styles())->toHaveCount(2)
        ->and($assets->styles()[0])->toStartWith('/_fx/card.css?v=')
        ->and($assets->styles()[1])->toStartWith('/_fx/Badge/badge.css?v=')
        ->and($assets->scripts())->toHaveCount(1)
        ->and($assets->scripts()[0])->toStartWith('/_fx/card.js?v=');
});

it('restores the outer collector after a nested collection', function () {
    [, $outer] = Assets::collect(function (): string {
        Assets::collect(fn (): string => (string) new Card('inner'));

        return '';
    });

    expect($outer->styles())->toBe([]);
});

it('builds a page with the collected assets in the head', function () {
    $html = Document::page('Título <x>', new Card('a'));

    expect($html)->toStartWith('<!doctype html>')
        ->and($html)->toContain('<title>Título &lt;x&gt;</title>')
        ->and($html)->toMatch('#<link rel="stylesheet" href="/_fx/card.css\?v=[0-9a-f]+">#')
        ->and($html)->toMatch('#<script type="module" src="/_fx/card.js\?v=[0-9a-f]+"></script>#')
        ->and(substr_count($html, 'badge.css'))->toBe(1);
});

it('builds a fragment without a document wrapper', function () {
    $html = Document::fragment(new Card('a'));

    expect($html)->not->toContain('<!doctype')
        ->and($html)->toContain('<link rel="stylesheet"')
        ->and($html)->toContain('<article class="card">');
});

it('rejects assets outside every mount', function () {
    Assets::url(fixture('outside.css'));
})->throws(LogicException::class);
