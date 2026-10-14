<?php

declare(strict_types=1);

namespace Lego;

use Lego\Audit\ActionLog;
use Lego\Auth\Authenticator;
use Lego\Auth\Gate;
use Lego\Auth\Guard;
use Lego\Auth\LoginController;
use Lego\Auth\SessionGuard;
use Lego\Auth\SessionStore;
use Lego\Database\Database;
use Lego\Database\Migrator;
use Lego\Http\CookiePolicy;
use Lego\Http\Csrf;
use Lego\Http\ErrorRenderer;
use Lego\Http\Kernel;
use Lego\Http\Request;
use Lego\Http\Response;
use Lego\Http\Router;
use Lego\Http\StaticFiles;
use Lego\Log\Logger;
use Lego\Support\Clock;
use Lego\Support\SystemClock;
use Lego\Ui\Welcome\Welcome;
use Lego\View\Assets;
use Lego\View\Document;

/**
 * Wires the framework together for one base path.
 *
 * Asset mounts:
 * - /_lego/    => core/Ui     framework components
 * - /_modules/ => modules     application modules
 *
 * Environment:
 * - APP_URL                      https:// enables Secure and __Host- cookies
 * - LEGO_SESSION_IDLE_MINUTES    idle session lifetime, default 480
 * - LEGO_SESSION_ABSOLUTE_DAYS   maximum session lifetime, default 30
 */
final class Application
{
    public const VERSION = '2.0.0-dev';

    public readonly Router $router;

    public readonly Database $db;

    public readonly SessionStore $sessions;

    public readonly Authenticator $auth;

    public readonly ActionLog $actionLog;

    public readonly SessionGuard $sessionGuard;

    private readonly Kernel $kernel;

    public function __construct(
        public readonly string $basePath,
        public readonly Environment $env,
        public readonly Logger $logger = new Logger(),
        ?Database $db = null,
        ?Guard $guard = null,
        public readonly Clock $clock = new SystemClock(),
    ) {
        $mounts = [
            '/_lego/' => $basePath . '/core/Ui',
            '/_modules/' => $basePath . '/modules',
        ];
        Assets::mount($mounts);

        $cookies = CookiePolicy::fromAppUrl($env->get('APP_URL'));
        $this->db = $db ?? new Database($env);
        $this->sessions = new SessionStore(
            $this->db,
            $clock,
            (int) $env->get('LEGO_SESSION_IDLE_MINUTES', '480'),
            (int) $env->get('LEGO_SESSION_ABSOLUTE_DAYS', '30'),
        );
        $this->actionLog = new ActionLog($this->db, $clock);
        $this->auth = new Authenticator($this->db, $this->sessions, $this->actionLog, $clock);
        $this->sessionGuard = new SessionGuard($this->sessions, $cookies);

        $this->router = new Router();
        $this->kernel = new Kernel(
            $this->router,
            new StaticFiles($mounts),
            new ErrorRenderer($logger, $env->debug()),
            $guard ?? $this->sessionGuard,
            new Csrf($cookies),
            new Gate(),
        );

        $this->registerCoreRoutes();
    }

    public static function boot(string $basePath): self
    {
        return new self($basePath, Environment::load($basePath));
    }

    public function handle(Request $request): Response
    {
        return $this->kernel->handle($request);
    }

    /** Core migrations plus every modules/<Module>/migrations folder. */
    public function migrator(): Migrator
    {
        $paths = [$this->basePath . '/core/Database/migrations'];
        foreach (glob($this->basePath . '/modules/*/migrations', GLOB_ONLYDIR) ?: [] as $path) {
            $paths[] = $path;
        }

        return new Migrator($this->db, $paths);
    }

    private function registerCoreRoutes(): void
    {
        (new LoginController($this->auth, $this->sessionGuard))->register($this->router);

        $this->router->get('/', static fn (Request $r): Response => Response::html(
            Document::page('Lego', new Welcome(self::VERSION, $r->user, $r->csrfToken), csrfToken: $r->csrfToken),
        ));

        $this->router->get('/component/welcome', static fn (Request $r): Welcome => new Welcome(self::VERSION, $r->user, $r->csrfToken));

        $this->router->get('/health', fn (): Response => $this->db->isReachable()
            ? Response::json(['status' => 'ok', 'version' => self::VERSION, 'database' => 'ok'])
            : Response::json([
                'status' => 'error',
                'error' => ['code' => 'database_unavailable', 'message' => 'La base de datos no responde.'],
            ], 503))->public();
    }
}
