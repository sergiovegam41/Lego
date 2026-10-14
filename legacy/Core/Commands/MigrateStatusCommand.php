<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * MigrateStatusCommand - Show migration status
 * 
 * Usage:
 *   php lego migrate:status
 */
class MigrateStatusCommand extends CoreCommand
{
    protected string $name = 'migrate:status';
    protected string $description = 'Show migration status';
    protected string $signature = 'migrate:status';

    private string $migrationsPath;

    public function execute(): bool
    {
        $this->migrationsPath = __DIR__ . '/../../database/migrations/';
        
        $this->info("📊 Migration Status\n");
        
        try {
            // Get all migration files
            $allFiles = $this->getMigrationFiles();
            
            // Get executed migrations
            $executed = $this->getExecutedMigrations();
            
            // Separate into executed and pending
            $pending = array_diff($allFiles, array_column($executed, 'migration'));
            
            // Display summary
            $this->line("Total migrations: " . count($allFiles));
            $this->success("✅ Executed: " . count($executed));
            $this->warning("⏳ Pending: " . count($pending));
            $this->line("");
            
            // Display executed migrations
            if (!empty($executed)) {
                $this->info("Executed Migrations:");
                $this->line(str_repeat("-", 80));
                $this->line(sprintf("%-50s %-10s %-20s", "Migration", "Batch", "Executed At"));
                $this->line(str_repeat("-", 80));
                
                foreach ($executed as $migration) {
                    $this->line(sprintf(
                        "%-50s %-10s %-20s",
                        substr($migration['migration'], 0, 47) . (strlen($migration['migration']) > 47 ? '...' : ''),
                        $migration['batch'],
                        $migration['created_at']
                    ));
                }
                $this->line("");
            }
            
            // Display pending migrations
            if (!empty($pending)) {
                $this->warning("Pending Migrations:");
                $this->line(str_repeat("-", 80));
                foreach ($pending as $file) {
                    $this->line("  ⏳ {$file}");
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return false;
        }
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
        if (!Capsule::schema()->hasTable('migrations')) {
            return [];
        }
        
        return Capsule::table('migrations')
            ->orderBy('batch')
            ->orderBy('migration')
            ->get()
            ->map(function($row) {
                return [
                    'migration' => $row->migration,
                    'batch' => $row->batch,
                    'created_at' => $row->created_at
                ];
            })
            ->toArray();
    }
}
