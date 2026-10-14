<?php

declare(strict_types=1);

namespace Lego\Console;

use Lego\Application;
use Lego\Console\Commands\RoutesCommand;
use Lego\Console\Commands\ServeCommand;
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
        ]);

        return $console;
    }
}
