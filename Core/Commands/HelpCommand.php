<?php

namespace Core\Commands;

/**
 * HelpCommand - Display help information
 */
class HelpCommand extends CoreCommand
{
    protected string $name = 'help';
    protected string $description = 'Display help information for commands';
    protected string $signature = 'help [command]';

    /**
     * Execute help command
     */
    public function execute(): bool
    {
        $commandName = $this->argument(1); // First argument after 'help'

        $router = new CommandRouter();

        if ($commandName) {
            // Show help for specific command
            $router->showCommandHelp($commandName);
        } else {
            // Show general help
            $router->showHelp();
        }

        return true;
    }
}