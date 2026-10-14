<?php

declare(strict_types=1);

/*
 * Runs the real entry point under `php -S`, the development server of decision
 * D12, on every operating system of the CI matrix. No database is needed.
 */

beforeAll(function () {
    $probe = stream_socket_server('tcp://127.0.0.1:0');
    $address = stream_socket_get_name($probe, false);
    fclose($probe);

    $public = basePath('public');
    $GLOBALS['legoServer'] = [
        'url' => 'http://' . $address,
        'process' => proc_open(
            [PHP_BINARY, '-S', $address, '-t', $public, $public . DIRECTORY_SEPARATOR . 'index.php'],
            [1 => ['file', basePath('tests/.server.log'), 'w'], 2 => ['file', basePath('tests/.server.log'), 'a']],
            $pipes,
        ),
    ];

    for ($i = 0; $i < 50; $i++) {
        if (@file_get_contents($GLOBALS['legoServer']['url'] . '/login') !== false) {
            return;
        }
        usleep(100_000);
    }
});

afterAll(function () {
    proc_terminate($GLOBALS['legoServer']['process']);
    proc_close($GLOBALS['legoServer']['process']);
    @unlink(basePath('tests/.server.log'));
});

/** @return array{status: int, body: string, headers: list<string>} */
function fetchFromServer(string $path): array
{
    $body = @file_get_contents($GLOBALS['legoServer']['url'] . $path, false, stream_context_create([
        'http' => ['ignore_errors' => true, 'timeout' => 10, 'follow_location' => 0],
    ]));
    $headers = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?? []) : ($http_response_header ?? []);
    preg_match('#HTTP/\S+ (\d{3})#', $headers[0] ?? '', $m);

    return ['status' => (int) ($m[1] ?? 0), 'body' => (string) $body, 'headers' => $headers];
}

it('redirects anonymous visitors to the login', function () {
    $response = fetchFromServer('/');

    $location = array_values(preg_grep('/^location:/i', $response['headers']));

    expect($response['status'])->toBe(302)
        ->and($location[0] ?? '')->toMatch('#^location: /login\?next=%2F$#i');
});

it('serves the login page with a CSRF cookie', function () {
    $response = fetchFromServer('/login');
    $cookies = array_filter($response['headers'], fn (string $h) => stripos($h, 'Set-Cookie: lego_csrf=') === 0);

    expect($response['status'])->toBe(200)
        ->and($response['body'])->toContain('lego-login')
        ->and($cookies)->toHaveCount(1);
});

it('serves component assets without symlinks', function () {
    $response = fetchFromServer('/_lego/Login/login.css');

    expect($response['status'])->toBe(200)->and($response['body'])->toContain('.lego-login');
});

it('answers health checks with the status envelope', function () {
    $response = fetchFromServer('/health');

    expect($response['status'])->toBeIn([200, 503])
        ->and(json_decode($response['body'], true))->toHaveKey('status');
});

it('returns JSON errors on the API layer', function () {
    $response = fetchFromServer('/api/nope');

    expect($response['status'])->toBe(404)->and(json_decode($response['body'], true)['status'])->toBe('error');
});
