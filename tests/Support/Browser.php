<?php

declare(strict_types=1);

namespace Tests\Support;

use Lego\Application;
use Lego\Http\Request;
use Lego\Http\Response;

/** Browser-like helper for feature tests: keeps cookies between requests. */
final class Browser
{
    /** @var array<string, string> */
    public array $cookies = [];

    public function __construct(private readonly Application $app)
    {
    }

    public function get(string $uri): Response
    {
        return $this->send(Request::create('GET', $uri, cookies: $this->cookies));
    }

    /** @param array<string, mixed> $form */
    public function post(string $uri, array $form = [], bool $withCsrf = true): Response
    {
        if ($withCsrf) {
            $form['_csrf'] = $this->cookies['lego_csrf'] ?? '';
        }

        return $this->send(Request::create('POST', $uri, $form, cookies: $this->cookies));
    }

    private function send(Request $request): Response
    {
        $response = $this->app->handle($request);
        foreach (\cookiesFrom($response) as $name => $value) {
            if ($value === '') {
                unset($this->cookies[$name]);
            } else {
                $this->cookies[$name] = $value;
            }
        }

        return $response;
    }
}
