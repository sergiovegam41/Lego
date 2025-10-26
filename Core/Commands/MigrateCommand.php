<?php

namespace Core\Commands;

/**
 * MigrateCommand - Handle database migrations
 */
class MigrateCommand extends CoreCommand
{
    protected string $name = 'migrate';
    protected string $description = 'Execute database migrations';
    protected string $signature = 'migrate [--force]';

    /**
     * Execute migration command
     */
    public function execute(): bool
    {
        $this->info("Executing migrations...\n");

        // Migration logic extracted from original lego file
        $filePath = __DIR__ . '/../../database/migrate.php';

        if (!file_exists($filePath)) {
            $this->error("Migration file not found at: {$filePath}");
            return false;
        }

        try {
            // Capture the output from migration file
            ob_start();
            $output = require_once $filePath;
            ob_end_clean();

            // Try to decode JSON output
            $data = json_decode($output, true);

            if (!$data) {
                $this->error("Invalid migration output format");
                return false;
            }

            $this->displayMigrationResults($data);
            $this->success("Migration completed successfully!");

            return true;

        } catch (\Exception $e) {
            $this->error("Migration failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Display migration results
     */
    private function displayMigrationResults(array $data): void
    {
        $this->line("## SCHEMA BASE ##\n");

        if (empty($data['base_execute_migrations'])) {
            $this->success("Nothing pending to migrate...");
        } else {
            foreach ($data['base_execute_migrations'] as $migration) {
                $this->displayMigration($migration);
            }
        }

        $this->line("\n## MIGRATIONS ##\n");

        if (empty($data['execute_migrations'])) {
            $this->success("Nothing pending to migrate...");
        } else {
            foreach ($data['execute_migrations'] as $migration) {
                $this->displayMigration($migration);
            }
        }
    }

    /**
     * Display individual migration details
     */
    private function displayMigration(array $migration): void
    {
        $this->success("File: " . $migration['archivo']);
        $this->line(str_repeat("-", 50));

        foreach ($migration['comandos'] as $comando) {
            $this->line("Command: " . $comando['comando']);
            $this->line("Date: " . $comando['date']);

            if ($comando['success']) {
                $this->success("Status: Success");
            } else {
                $this->error("Status: Error");
                if (!empty($comando['message'])) {
                    $this->error("Error message: " . $comando['message']);
                }
            }

            $this->line(str_repeat("-", 50));
        }
    }
}