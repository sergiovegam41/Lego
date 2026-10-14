<?php

declare(strict_types=1);

namespace Lego\Console;

use Lego\Application;
use Lego\Console\Commands\ActionsCommand;
use Lego\Console\Commands\InstallCommand;
use Lego\Console\Commands\MakeModuleCommand;
use Lego\Console\Commands\MigrateCommand;
use Lego\Console\Commands\MigrateRollbackCommand;
use Lego\Console\Commands\MigrateStatusCommand;
use Lego\Console\Commands\RoutesCommand;
use Lego\Console\Commands\RunCommand;
use Lego\Console\Commands\ServeCommand;
use Lego\Console\Commands\UserPasswordCommand;
use Symfony\Component\Console\Application as SymfonyConsole;

/** The `php lego` command line. */
final class Console
{
    public static function create(Application $app): SymfonyConsole
    {
        $console = new SymfonyConsole('Lego', Application::VERSION);
        $console->addCommands([
            new ServeCommand($app),
            new RoutesCommand($app),
            new MigrateCommand($app),
            new MigrateRollbackCommand($app),
            new MigrateStatusCommand($app),
            new InstallCommand($app),
            new UserPasswordCommand($app),
            new ActionsCommand($app),
            new RunCommand($app),
            new MakeModuleCommand($app),
        ]);

        return $console;
    }
}
