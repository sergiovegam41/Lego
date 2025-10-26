<?php

namespace Core\Commands;

/**
 * InitCommand - Initialize Lego Framework
 */
class InitCommand extends CoreCommand
{
    protected string $name = 'init';
    protected string $description = 'Initialize Lego Framework (run migrations and map routes)';
    protected string $signature = 'init [--force]';

    /**
     * Execute initialization command
     */
    public function execute(): bool
    {
        $this->line("🚀 Initializing Lego Framework...");
        $this->line("================================\n");

        $success = true;

        // Execute migrations
        $this->info("📦 Executing migrations...");
        $migrateCommand = new MigrateCommand($this->arguments);
        if (!$migrateCommand->execute()) {
            $success = false;
        }

        $this->line("");

        // Map routes
        $this->info("🗺️  Mapping routes...");
        $mapRoutesCommand = new MapRoutesCommand($this->arguments);
        if (!$mapRoutesCommand->execute()) {
            $success = false;
        }

        $this->line("");

        // Final status
        if ($success) {
            $this->success("✅ Lego Framework initialized successfully!");
            $this->displayNextSteps();
        } else {
            $this->error("❌ Some errors occurred during initialization.");
        }

        return $success;
    }

    /**
     * Display next steps after successful initialization
     */
    private function displayNextSteps(): void
    {
        $this->line("");
        $this->info("Next steps:");
        $this->line("  • Start the development server: php -S localhost:8080 -t public/");
        $this->line("  • Or use PM2: pm2 start \"php -S localhost:8080 -t public/\" --name lego");
        $this->line("  • Access your application at: http://localhost:8080");
        $this->line("");
    }
}