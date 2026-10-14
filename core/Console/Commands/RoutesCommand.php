<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'routes', description: 'Lista las rutas registradas')]
final class RoutesCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = array_map(
            static fn (array $route): array => [$route['method'], $route['path']],
            $this->app->router->routes(),
        );

        (new Table($output))->setHeaders(['Método', 'Ruta'])->setRows($rows)->render();

        return Command::SUCCESS;
    }
}
