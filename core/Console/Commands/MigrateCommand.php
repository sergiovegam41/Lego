<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate', description: 'Aplica las migraciones pendientes')]
final class MigrateCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $applied = $this->app->migrator()->migrate();

        if ($applied === []) {
            $output->writeln('No hay migraciones pendientes.');

            return Command::SUCCESS;
        }

        foreach ($applied as $name) {
            $output->writeln("<info>Aplicada</info>  {$name}");
        }

        return Command::SUCCESS;
    }
}
