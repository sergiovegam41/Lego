<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Application;
use Lego\Auth\Installer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'install',
    description: 'Aplica migraciones y crea el administrador inicial si no hay usuarios',
)]
final class InstallCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Correo del administrador', 'admin@lego.local')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Nombre del administrador', 'Administrador');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->app->migrator()->migrate();

        $result = (new Installer())->install(
            (string) $input->getOption('email'),
            (string) $input->getOption('name'),
            $this->app->env->get('LEGO_ADMIN_PASSWORD'),
        );

        if ($result === null) {
            $output->writeln('Lego ya está instalado: existen usuarios. No se creó nada.');

            return Command::SUCCESS;
        }

        $output->writeln("<info>Administrador creado:</info> {$result['email']}");
        if ($result['generated']) {
            $output->writeln("<comment>Contraseña generada (se muestra solo esta vez):</comment> {$result['password']}");
        }

        return Command::SUCCESS;
    }
}
