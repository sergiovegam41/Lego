<?php

declare(strict_types=1);

namespace Lego\Console\Commands;

use Lego\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'serve', description: 'Inicia el servidor de desarrollo de PHP')]
final class ServeCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Interfaz de escucha', '127.0.0.1')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Puerto', '8000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $address = $input->getOption('host') . ':' . $input->getOption('port');
        $public = $this->app->basePath . DIRECTORY_SEPARATOR . 'public';

        $output->writeln("<info>Lego en http://{$address}</info>  (Ctrl+C para detener)");

        $command = [PHP_BINARY, '-S', $address, '-t', $public, $public . DIRECTORY_SEPARATOR . 'index.php'];
        $process = proc_open($command, [STDIN, STDOUT, STDERR], $pipes);

        return is_resource($process) ? proc_close($process) : Command::FAILURE;
    }
}
