<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * MigrateDownCommand - Rollback migration(s) DOWN
 * 
 * Usage:
 *   php lego migrate:down                    # Rollback last batch
 *   php lego migrate:down -f migration.php   # Rollback specific migration
 */
class MigrateDownCommand extends CoreCommand
{
    protected string $name = 'migrate:down';
    protected string $description = 'Rollback migration(s) DOWN';
    protected string $signature = 'migrate:down [-f|--file=]';

    private string $migrationsPath;

    public function execute(): bool
    {
        $this->migrationsPath = __DIR__ . '/../../database/migrations/';
        
        // Get specific file if provided
        $specificFile = $this->option('file') ?? $this->option('f');
        
        if ($specificFile) {
            return $this->rollbackSpecificMigration($specificFile);
        }
        
        // Otherwise, rollback last batch
        return $this->rollbackLastBatch();
    }

    /**
     * Rollback a specific migration file
     */
    private function rollbackSpecificMigration(string $filename): bool
    {
        $filePath = $this->migrationsPath . $filename;
        
        if (!file_exists($filePath)) {
            $this->error("❌ Migration file not found: {$filename}");
            return false;
        }
        
        $this->info("🔽 Rolling back: {$filename}\n");
        
        try {
            // Check if migration was executed
            $migration = Capsule::table('migrations')
                ->where('migration', $filename)
                ->first();
            
            if (!$migration) {
                $this->warning("⚠️  Migration not found in database. Nothing to rollback.");
                return false;
            }
            
            // Load migration file
            $migrationClass = require $filePath;
            
            if (!method_exists($migrationClass, 'down')) {
                $this->error("❌ No down() method found in migration");
                return false;
            }
            
            // Execute down()
            ob_start();
            $migrationClass->down();
            $output = ob_get_clean();
            
            if (!empty($output)) {
                $this->line($output);
            }
            
            // Remove from database
            Capsule::table('migrations')
                ->where('migration', $filename)
                ->delete();
            
            $this->success("✅ Migration rolled back successfully!");
            return true;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rollback last batch of migrations
     */
    private function rollbackLastBatch(): bool
    {
        $this->info("🔄 Rolling back last batch...\n");
        
        try {
            // Get last batch number
            $lastBatch = Capsule::table('migrations')->max('batch');
            
            if (!$lastBatch) {
                $this->warning("⚠️  No migrations to rollback");
                return true;
            }
            
            // Get migrations from last batch
            $migrations = Capsule::table('migrations')
                ->where('batch', $lastBatch)
                ->orderBy('migration', 'desc')
                ->get();
            
            if ($migrations->isEmpty()) {
                $this->warning("⚠️  No migrations found in batch {$lastBatch}");
                return true;
            }
            
            $this->line("📦 Rolling back " . count($migrations) . " migration(s) from batch {$lastBatch}:\n");
            
            $rolledBack = 0;
            $failed = 0;
            
            foreach ($migrations as $migration) {
                if ($this->rollbackSpecificMigration($migration->migration)) {
                    $rolledBack++;
                } else {
                    $failed++;
                }
            }
            
            $this->line("\n" . str_repeat("=", 50));
            $this->success("✅ Rolled back: {$rolledBack}");
            
            if ($failed > 0) {
                $this->error("❌ Failed: {$failed}");
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            $this->error("Rollback failed: " . $e->getMessage());
            return false;
        }
    }
}
