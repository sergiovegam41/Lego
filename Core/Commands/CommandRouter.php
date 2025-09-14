<?php

namespace Core\Commands;

/**
 * CommandRouter - Routes CLI commands to their respective handlers
 *
 * Automatically discovers and loads commands from the Commands directory
 * Similar to how CoreController works but for CLI commands
 */
class CommandRouter
{
    /**
     * Available commands registry
     */
    private array $commands = [];

    /**
     * Constructor - Auto-discover commands
     */
    public function __construct()
    {
        $this->discoverCommands();
    }

    /**
     * Execute a command by name
     *
     * @param string $commandName
     * @param array $arguments
     * @return bool
     */
    public function execute(string $commandName, array $arguments = []): bool
    {
        if (!$this->hasCommand($commandName)) {
            $this->showError("Command '{$commandName}' not found.");
            $this->showAvailableCommands();
            return false;
        }

        try {
            $commandClass = $this->commands[$commandName];
            $command = new $commandClass($arguments);

            return $command->execute();

        } catch (\Exception $e) {
            $this->showError("Error executing command '{$commandName}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if command exists
     */
    public function hasCommand(string $commandName): bool
    {
        return isset($this->commands[$commandName]);
    }

    /**
     * Get all available commands
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Show help for all commands
     */
    public function showHelp(): void
    {
        echo "\n🧱 Lego Framework - CLI Commands\n";
        echo "================================\n\n";

        echo "Usage:\n";
        echo "  php lego <command> [arguments] [options]\n\n";

        echo "Available commands:\n";

        foreach ($this->commands as $name => $class) {
            try {
                $instance = new $class();
                $description = $instance->getDescription() ?: 'No description available';
                echo sprintf("  %-20s %s\n", $name, $description);
            } catch (\Exception $e) {
                echo sprintf("  %-20s %s\n", $name, "Error loading command");
            }
        }

        echo "\nFor help on a specific command:\n";
        echo "  php lego help <command>\n\n";
    }

    /**
     * Show help for specific command
     */
    public function showCommandHelp(string $commandName): void
    {
        if (!$this->hasCommand($commandName)) {
            $this->showError("Command '{$commandName}' not found.");
            return;
        }

        $commandClass = $this->commands[$commandName];
        $command = new $commandClass();

        echo "\n🧱 Lego Framework - Command Help\n";
        echo "===============================\n\n";
        echo "Command: {$commandName}\n";
        echo "Description: " . ($command->getDescription() ?: 'No description available') . "\n";

        if ($command->getSignature()) {
            echo "Signature: " . $command->getSignature() . "\n";
        }

        echo "\n";
    }

    /**
     * Auto-discover commands from Commands directory
     */
    private function discoverCommands(): void
    {
        $commandsPath = __DIR__;

        if (!is_dir($commandsPath)) {
            return;
        }

        $files = scandir($commandsPath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'CoreCommand.php' || $file === 'CommandRouter.php') {
                continue;
            }

            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $className = pathinfo($file, PATHINFO_FILENAME);
            $fullClassName = "Core\\Commands\\{$className}";

            if (!class_exists($fullClassName)) {
                continue;
            }

            // Check if class extends CoreCommand
            if (!is_subclass_of($fullClassName, CoreCommand::class)) {
                continue;
            }

            try {
                $instance = new $fullClassName();
                $commandName = $instance->getName();

                if ($commandName) {
                    $this->commands[$commandName] = $fullClassName;
                }
            } catch (\Exception $e) {
                // Skip commands that can't be instantiated
                continue;
            }
        }
    }

    /**
     * Show available commands when command not found
     */
    private function showAvailableCommands(): void
    {
        echo "\nAvailable commands:\n";
        foreach (array_keys($this->commands) as $command) {
            echo "  - {$command}\n";
        }
        echo "\nRun 'php lego help' for more information.\n";
    }

    /**
     * Show error message
     */
    private function showError(string $message): void
    {
        echo "\033[31m❌ {$message}\033[0m\n";
    }
}