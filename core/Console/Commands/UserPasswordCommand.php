<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Application;
use Lego\Audit\ActionLog;
use Lego\Auth\Passwords;
use Lego\Auth\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:password',
    description: 'Genera una contraseña nueva para un usuario y cierra todas sus sesiones',
)]
final class UserPasswordCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Correo del usuario');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = User::findByEmail((string) $input->getArgument('email'));
        if ($user === null) {
            $output->writeln('<error>No existe un usuario con ese correo.</error>');

            return Command::FAILURE;
        }

        $password = Passwords::generate();
        $user->password_hash = Passwords::hash($password);
        $user->save();
        $closed = $this->app->sessions->revokeAllFor($user->id);
        $this->app->actionLog->record('users.reset_password', ActionLog::VIA_CLI, ActionLog::OK, null, 'user:' . $user->id);

        $output->writeln("<info>Contraseña nueva para {$user->email}:</info> {$password}");
        $output->writeln("Sesiones cerradas: {$closed}");

        return Command::SUCCESS;
    }
}
