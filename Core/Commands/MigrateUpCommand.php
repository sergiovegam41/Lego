<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * MigrateUpCommand - Execute specific migration(s) UP
 * 
 * Usage:
 *   php lego migrate:up                    # Run all pending migrations
 *   php lego migrate:up -f migration.php   # Run specific migration file
 */
class MigrateUpCommand extends CoreCommand
{
    protected string $name = 'migrate:up';
    protected string $description = 'Execute migration(s) UP';
    protected string $signature = 'migrate:up [-f|--file=]';

    private string $migrationsPath;
    private int $currentBatch = 1;

    public function execute(): bool
    {
        $this->migrationsPath = __DIR__ . '/../../database/migrations/';
        
        // Get specific file if provided
        $specificFile = $this->option('file') ?? $this->option('f');
        
        if ($specificFile) {
            return $this->executeSpecificMigration($specificFile, 'up');
        }
        
        // Otherwise, run all pending migrations (same as migrate)
        return $this->executeAllPendingMigrations();
    }

    /**
     * Execute a specific migration file
     */
    private function executeSpecificMigration(string $filename, string $direction): bool
    {
        $filePath = $this->migrationsPath . $filename;
        
        if (!file_exists($filePath)) {
            $this->error("❌ Migration file not found: {$filename}");
            return false;
        }
        
        $this->info("🔼 Executing UP: {$filename}\n");
        
        try {
            // Ensure migrations table exists
            $this->ensureMigrationsTableExists();
            
            // Load migration
            $migration = require $filePath;
            
            if (!method_exists($migration, 'up')) {
                $this->error("❌ No up() method found in migration");
                return false;
            }
            
            // Check if already executed
            $exists = Capsule::table('migrations')
                ->where('migration', $filename)
                ->exists();
            
            if ($exists) {
                $this->warning("⚠️  Migration already executed. Use migrate:down first to rollback.");
                return false;
            }
            
            // Execute up()
            ob_start();
            $migration->up();
            $output = ob_get_clean();
            
            if (!empty($output)) {
                $this->line($output);
            }
            
            // Record in database
            $this->currentBatch = $this->getNextBatchNumber();
            $timestamp = date('Y-m-d H:i:s');
            Capsule::table('migrations')->insert([
                'migration' => $filename,
                'batch' => $this->currentBatch,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);
            
            $this->success("✅ Migration executed successfully!");
            return true;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute all pending migrations
     */
    private function executeAllPendingMigrations(): bool
    {
        $this->info("🔄 Executing all pending migrations...\n");
        
        try {
            $this->ensureMigrationsTableExists();
            $this->currentBatch = $this->getNextBatchNumber();
            
            $migrationFiles = $this->getMigrationFiles();
            $executedMigrations = $this->getExecutedMigrations();
            $pendingMigrations = array_diff($migrationFiles, $executedMigrations);
            
            if (empty($pendingMigrations)) {
                $this->success("✅ Nothing pending to migrate");
                return true;
            }
            
            $this->line("📦 Executing " . count($pendingMigrations) . " migration(s):\n");
            
            $executed = 0;
            $failed = 0;
            
            foreach ($pendingMigrations as $file) {
                if ($this->executeSpecificMigration($file, 'up')) {
                    $executed++;
                } else {
                    $failed++;
                }
            }
            
            $this->line("\n" . str_repeat("=", 50));
            $this->success("✅ Executed: {$executed}");
            
            if ($failed > 0) {
                $this->error("❌ Failed: {$failed}");
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            $this->error("Migration failed: " . $e->getMessage());
            return false;
        }
    }

    private function ensureMigrationsTableExists(): void
    {
        if (!Capsule::schema()->hasTable('migrations')) {
            Capsule::schema()->create('migrations', function ($table) {
                $table->id();
                $table->string('migration', 255);
                $table->integer('batch')->default(1);
                $table->timestamps();
            });
        }
    }

    private function getNextBatchNumber(): int
    {
        $maxBatch = Capsule::table('migrations')->max('batch');
        return $maxBatch ? $maxBatch + 1 : 1;
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '*.php');
        $migrationFiles = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            if ($filename !== 'helpers.php') {
                $migrationFiles[] = $filename;
            }
        }
        
        sort($migrationFiles);
        return $migrationFiles;
    }

    private function getExecutedMigrations(): array
    {
        return Capsule::table('migrations')
            ->orderBy('migration')
            ->pluck('migration')
            ->toArray();
    }
}
