<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate:rollback', description: 'Revierte el último lote de migraciones')]
final class MigrateRollbackCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('batches', 'b', InputOption::VALUE_REQUIRED, 'Cuántos lotes revertir', '1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reverted = $this->app->migrator()->rollback((int) $input->getOption('batches'));

        if ($reverted === []) {
            $output->writeln('No hay migraciones aplicadas.');
        }
        foreach ($reverted as $name) {
            $output->writeln("<comment>Revertida</comment>  {$name}");
        }

        return Command::SUCCESS;
    }
}
