<?php

declare(strict_types=1);

namespace Lego;

use Lego\Action\ActionExecutor;
use Lego\Action\ActionRegistry;
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
use Lego\Module\Menu;
use Lego\Module\ModuleRegistry;
use Lego\Module\ModuleRoutes;
use Lego\Support\Clock;
use Lego\Support\SystemClock;
use Lego\Ui\Welcome\Welcome;
use Lego\View\Assets;

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

    public readonly Gate $gate;

    public readonly ModuleRegistry $modules;

    public readonly ActionRegistry $actions;

    public readonly ActionExecutor $executor;

    public readonly string $modulesPath;

    private readonly Menu $menu;

    private ModuleRoutes $moduleRoutes;

    private readonly Kernel $kernel;

    public function __construct(
        public readonly string $basePath,
        public readonly Environment $env,
        public readonly Logger $logger = new Logger(),
        ?Database $db = null,
        ?Guard $guard = null,
        public readonly Clock $clock = new SystemClock(),
        ?string $modulesPath = null,
        string $modulesNamespace = 'Modules\\',
    ) {
        $this->modulesPath = $modulesPath ?? $basePath . '/modules';
        $mounts = [
            '/_lego/' => $basePath . '/core/Ui',
            '/_modules/' => $this->modulesPath,
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

        $this->gate = new Gate();
        $this->modules = new ModuleRegistry($this->modulesPath, $modulesNamespace);
        $this->actions = new ActionRegistry($this->modules);
        $this->executor = new ActionExecutor($this->actions, $this->modules, $this->gate, $this->actionLog, $this->db, $clock);
        $this->menu = new Menu($this->modules, $this->gate);

        $this->router = new Router();
        $this->kernel = new Kernel(
            $this->router,
            new StaticFiles($mounts),
            new ErrorRenderer($logger, $env->debug()),
            $guard ?? $this->sessionGuard,
            new Csrf($cookies),
            $this->gate,
        );

        $this->registerCoreRoutes();
        $this->moduleRoutes = new ModuleRoutes($this->modules, $this->actions, $this->executor, $this->gate, $this->menu);
        $this->moduleRoutes->register($this->router);
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
        foreach (glob($this->modulesPath . '/*/migrations', GLOB_ONLYDIR) ?: [] as $path) {
            $paths[] = $path;
        }

        return new Migrator($this->db, $paths);
    }

    private function registerCoreRoutes(): void
    {
        (new LoginController($this->auth, $this->sessionGuard))->register($this->router);

        $home = fn (Request $r): Response => $this->moduleRoutes->respond($r, 'Inicio', new Welcome(self::VERSION, $r->user->name));
        $this->router->get('/', $home);
        $this->router->get('/component/welcome', $home);

        $this->router->get('/health', fn (): Response => $this->db->isReachable()
            ? Response::json(['status' => 'ok', 'version' => self::VERSION, 'database' => 'ok'])
            : Response::json([
                'status' => 'error',
                'error' => ['code' => 'database_unavailable', 'message' => 'La base de datos no responde.'],
            ], 503))->public();
    }
}
