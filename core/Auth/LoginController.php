<?php

declare(strict_types=1);

namespace Lego\Auth;

use Lego\Errors\HttpError;
use Lego\Http\Request;
use Lego\Http\Response;
use Lego\Http\Router;
use Lego\Ui\Login\Login;
use Lego\View\Document;

/**
 * GET /login, POST /login and POST /logout.
 *
 * Login is a plain form POST: on success it redirects (303) to `next`, only if
 * `next` is a local path; on failure it renders the form again with the error.
 */
final class LoginController
{
    public function __construct(
        private readonly Authenticator $auth,
        private readonly SessionGuard $guard,
    ) {
    }

    public function register(Router $router): void
    {
        $router->get('/login', $this->show(...))->public();
        $router->post('/login', $this->login(...))->public();
        $router->post('/logout', $this->logout(...));
    }

    public function show(Request $request): Response
    {
        $next = self::safeNext($request->query('next'));
        if ($request->user !== null) {
            return Response::redirect($next);
        }

        return $this->form($request, $next);
    }

    public function login(Request $request): Response
    {
        $next = self::safeNext($request->input('next'));
        $email = (string) $request->input('email', '');

        try {
            $result = $this->auth->attempt(
                $email,
                (string) $request->input('password', ''),
                $request->ip,
                $request->header('user-agent'),
                $request->traceId,
            );
        } catch (HttpError $e) {
            return $this->form($request, $next, $email, $e->getMessage(), $e->status());
        }

        return Response::redirect($next, 303)->withCookie($this->guard->cookieFor($result['token']));
    }

    public function logout(Request $request): Response
    {
        $token = $this->guard->token($request);
        if ($token !== null && $request->user !== null) {
            $this->auth->logout($request->user, $token, $request->ip, $request->traceId);
        }

        return Response::redirect('/login', 303)->withCookie($this->guard->forgetCookie());
    }

    /** Only local absolute paths are accepted, never another host. */
    public static function safeNext(mixed $next): string
    {
        if (!is_string($next) || !str_starts_with($next, '/') || str_starts_with($next, '//') || str_contains($next, '\\')) {
            return '/';
        }

        return $next;
    }

    private function form(Request $request, string $next, string $email = '', ?string $error = null, int $status = 200): Response
    {
        $html = Document::page(
            'Iniciar sesión',
            new Login($request->csrfToken, $next, $email, $error),
            csrfToken: $request->csrfToken,
        );

        return Response::html($html, $status);
    }
}
