<?php

declare(strict_types=1);

namespace Lego;

use Lego\Http\ErrorRenderer;
use Lego\Http\Kernel;
use Lego\Http\Request;
use Lego\Http\Response;
use Lego\Http\Router;
use Lego\Http\StaticFiles;
use Lego\Log\Logger;
use Lego\Ui\Welcome\Welcome;
use Lego\View\Assets;
use Lego\View\Document;

/**
 * Wires the framework together for one base path.
 *
 * Asset mounts:
 * - /_lego/    => core/Ui     framework components
 * - /_modules/ => modules     application modules
 */
final class Application
{
    public const VERSION = '2.0.0-dev';

    public readonly Router $router;

    private readonly Kernel $kernel;

    public function __construct(
        public readonly string $basePath,
        public readonly Environment $env,
        public readonly Logger $logger = new Logger(),
    ) {
        $mounts = [
            '/_lego/' => $basePath . '/core/Ui',
            '/_modules/' => $basePath . '/modules',
        ];
        Assets::mount($mounts);

        $this->router = new Router();
        $this->kernel = new Kernel(
            $this->router,
            new StaticFiles($mounts),
            new ErrorRenderer($logger, $env->debug()),
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

    private function registerCoreRoutes(): void
    {
        $this->router->get('/', static fn (): Response => Response::html(
            Document::page('Lego', new Welcome(Application::VERSION)),
        ));

        $this->router->get('/component/welcome', static fn (): Welcome => new Welcome(Application::VERSION));

        $this->router->get('/health', static fn (): array => ['status' => 'ok', 'version' => Application::VERSION]);
    }
}
