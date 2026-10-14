<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate:status', description: 'Muestra qué migraciones están aplicadas')]
final class MigrateStatusCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = array_map(
            static fn (array $row): array => [$row['name'], $row['batch'] === null ? 'pendiente' : 'lote ' . $row['batch']],
            $this->app->migrator()->status(),
        );

        (new Table($output))->setHeaders(['Migración', 'Estado'])->setRows($rows)->render();

        return Command::SUCCESS;
    }
}
