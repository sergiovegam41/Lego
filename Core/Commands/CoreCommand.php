<?php

namespace Core\Commands;

/**
 * Abstract CoreCommand - Base class for all CLI commands in Lego Framework
 *
 * Similar to CoreComponent but for CLI operations
 * Provides standardized interface for command creation and execution
 */
abstract class CoreCommand
{
    /**
     * Command name as it appears in CLI
     * Example: 'migrate', 'make:component', 'serve'
     */
    protected string $name;

    /**
     * Command description shown in help
     */
    protected string $description = '';

    /**
     * Command signature with arguments/options
     * Example: 'make:component {name} {--type=default}'
     */
    protected string $signature = '';

    /**
     * Arguments passed to the command
     */
    protected array $arguments = [];

    /**
     * Options passed to the command
     */
    protected array $options = [];

    /**
     * Constructor - Initialize command with arguments
     */
    public function __construct(array $args = [])
    {
        $this->arguments = $args;
        $this->parseArguments();
    }

    /**
     * Main execution method - must be implemented by each command
     *
     * @return bool Success status
     */
    abstract public function execute(): bool;

    /**
     * Get command name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get command signature
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Parse command line arguments and options
     */
    protected function parseArguments(): void
    {
        // Basic argument parsing - can be extended
        foreach ($this->arguments as $arg) {
            if (str_starts_with($arg, '--')) {
                // Parse options like --force, --type=value
                $option = substr($arg, 2);
                if (str_contains($option, '=')) {
                    [$key, $value] = explode('=', $option, 2);
                    $this->options[$key] = $value;
                } else {
                    $this->options[$option] = true;
                }
            }
        }
    }

    /**
     * Get argument by index
     */
    protected function argument(int $index, $default = null)
    {
        return $this->arguments[$index] ?? $default;
    }

    /**
     * Get option value
     */
    protected function option(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * Output success message
     */
    protected function success(string $message): void
    {
        echo "\033[32m✅ {$message}\033[0m\n";
    }

    /**
     * Output error message
     */
    protected function error(string $message): void
    {
        echo "\033[31m❌ {$message}\033[0m\n";
    }

    /**
     * Output info message
     */
    protected function info(string $message): void
    {
        echo "\033[34mℹ️  {$message}\033[0m\n";
    }

    /**
     * Output warning message
     */
    protected function warning(string $message): void
    {
        echo "\033[33m⚠️  {$message}\033[0m\n";
    }

    /**
     * Output regular message
     */
    protected function line(string $message): void
    {
        echo $message . "\n";
    }

    /**
     * Ask for user confirmation
     */
    protected function confirm(string $question): bool
    {
        echo $question . " (y/n): ";
        $handle = fopen("php://stdin", "r");
        $response = trim(fgets($handle));
        fclose($handle);

        return in_array(strtolower($response), ['y', 'yes', '1', 'true']);
    }

    /**
     * Display progress bar (simple implementation)
     */
    protected function progressBar(int $current, int $total, string $message = ''): void
    {
        $percent = round(($current / $total) * 100);
        $bar = str_repeat('█', $percent / 2) . str_repeat('░', 50 - $percent / 2);
        echo "\r{$message} [{$bar}] {$percent}%";

        if ($current === $total) {
            echo "\n";
        }
    }
}