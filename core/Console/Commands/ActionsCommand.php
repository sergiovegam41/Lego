<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Action\ActionDefinition;
use Lego\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'actions', description: 'Lista las acciones de los módulos')]
final class ActionsCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'Solo las de un módulo')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Salida JSON con el esquema de entrada');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module = $input->getOption('module');
        $actions = is_string($module) ? $this->app->actions->forModule($module) : array_values($this->app->actions->all());

        if ($input->getOption('json')) {
            $output->writeln((string) json_encode(
                array_map(static fn (ActionDefinition $a): array => $a->toArray(), $actions),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ));

            return Command::SUCCESS;
        }

        $rows = array_map(static fn (ActionDefinition $a): array => [
            $a->name,
            $a->permission,
            $a->scope,
            implode(' ', array_keys(array_filter([
                'solo-lectura' => $a->readOnly,
                'destructiva' => $a->destructive,
                'idempotente' => $a->idempotent,
                'confirma' => $a->confirm,
            ]))),
        ], $actions);

        (new Table($output))->setHeaders(['Acción', 'Permiso', 'Alcance', 'Anotaciones'])->setRows($rows)->render();

        return Command::SUCCESS;
    }
}
