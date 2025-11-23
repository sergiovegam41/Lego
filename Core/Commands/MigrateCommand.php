<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * MigrateCommand - Handle database migrations (PHP-based, Laravel style)
 */
class MigrateCommand extends CoreCommand
{
    protected string $name = 'migrate';
    protected string $description = 'Execute database migrations';
    protected string $signature = 'migrate [--force]';

    private string $migrationsPath;
    private int $currentBatch = 1;

    /**
     * Execute migration command
     */
    public function execute(): bool
    {
        $this->info("🔄 Executing migrations...\n");

        $this->migrationsPath = __DIR__ . '/../../database/migrations/';

        try {
            // Step 1: Ensure migrations table exists
            $this->ensureMigrationsTableExists();

            // Step 2: Get current batch number
            $this->currentBatch = $this->getNextBatchNumber();

            // Step 3: Get all migration files
            $migrationFiles = $this->getMigrationFiles();

            if (empty($migrationFiles)) {
                $this->warning("No migration files found in {$this->migrationsPath}");
                return true;
            }

            // Step 4: Get already executed migrations
            $executedMigrations = $this->getExecutedMigrations();

            // Step 5: Filter pending migrations
            $pendingMigrations = array_diff($migrationFiles, $executedMigrations);

            if (empty($pendingMigrations)) {
                $this->success("✅ Nothing pending to migrate...");
                return true;
            }

            // Step 6: Execute pending migrations
            $this->line("\n📦 Executing " . count($pendingMigrations) . " pending migration(s):\n");
            
            $executed = 0;
            $failed = 0;

            foreach ($pendingMigrations as $migrationFile) {
                if ($this->executeMigration($migrationFile)) {
                    $executed++;
                } else {
                    $failed++;
                }
            }

            // Step 7: Display summary
            $this->line("\n" . str_repeat("=", 50));
            $this->success("✅ Executed: {$executed}");
            
            if ($failed > 0) {
                $this->error("❌ Failed: {$failed}");
                return false;
            }

            $this->success("\n🎉 Migration completed successfully!");
            return true;

        } catch (\Exception $e) {
            $this->error("Migration failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Ensure migrations table exists (self-bootstrapping)
     */
    private function ensureMigrationsTableExists(): void
    {
        if (!Capsule::schema()->hasTable('migrations')) {
            $this->info("Creating migrations table...");
            
            // Execute the migrations table migration directly
            $migrationFile = '2024_01_01_000001_create_migrations_table.php';
            $filePath = $this->migrationsPath . $migrationFile;
            
            if (file_exists($filePath)) {
                $migration = require $filePath;
                $migration->up();
            } else {
                // Fallback: create table manually
                Capsule::schema()->create('migrations', function ($table) {
                    $table->id();
                    $table->string('migration', 255);
                    $table->integer('batch')->default(1);
                    $table->timestamps();
                });
                $this->success("✓ Migrations table created");
            }
        }
    }

    /**
     * Get next batch number
     */
    private function getNextBatchNumber(): int
    {
        try {
            $maxBatch = Capsule::table('migrations')->max('batch');
            return $maxBatch ? $maxBatch + 1 : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Get all migration files from the migrations directory
     */
    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '*.php');
        $migrationFiles = [];

        foreach ($files as $file) {
            $filename = basename($file);
            
            // Skip helpers.php
            if ($filename === 'helpers.php') {
                continue;
            }
            
            $migrationFiles[] = $filename;
        }

        // Sort by filename (which includes timestamp)
        sort($migrationFiles);

        return $migrationFiles;
    }

    /**
     * Get list of already executed migrations
     */
    private function getExecutedMigrations(): array
    {
        try {
            $migrations = Capsule::table('migrations')
                ->orderBy('migration')
                ->pluck('migration')
                ->toArray();
            
            return $migrations;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Execute a single migration file
     */
    private function executeMigration(string $filename): bool
    {
        $filePath = $this->migrationsPath . $filename;

        $this->line("📄 {$filename}");

        try {
            // Load the migration file
            $migration = require $filePath;

            if (!method_exists($migration, 'up')) {
                $this->warning("  ⚠️  No up() method found, skipping...");
                return false;
            }

            // Execute the up() method
            ob_start();
            $migration->up();
            $output = ob_get_clean();

            // Display migration output if any
            if (!empty($output)) {
                $this->line("  " . trim($output));
            }

            // Record migration in database
            $timestamp = date('Y-m-d H:i:s');
            Capsule::table('migrations')->insert([
                'migration' => $filename,
                'batch' => $this->currentBatch,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);

            return true;

        } catch (\Exception $e) {
            $this->error("  ❌ Error: " . $e->getMessage());
            return false;
        }
    }
}