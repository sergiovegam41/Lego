<?php
/**
 * Run Eloquent Migrations
 *
 * This script executes PHP migrations in the database/migrations directory
 * Usage: php database/run-eloquent-migrations.php
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Core/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;

echo "🔍 Scanning for migrations...\n\n";

$migrationsDir = __DIR__ . '/migrations';
$migrationFiles = glob($migrationsDir . '/*.php');

// Sort migrations by filename (which includes timestamp)
sort($migrationFiles);

$executed = 0;
$errors = 0;

foreach ($migrationFiles as $file) {
    $fileName = basename($file);

    try {
        echo "📄 Processing: {$fileName}\n";

        // Load the migration
        $migration = require $file;

        if (!is_object($migration) || !method_exists($migration, 'up')) {
            echo "   ⚠️  Skipped: No 'up' method found\n\n";
            continue;
        }

        // Check if table already exists (simple check for idempotency)
        // Extract table name from migration filename
        $tableName = null;
        if (preg_match('/create_(.+)_table\.php$/', $fileName, $matches)) {
            $tableName = $matches[1];

            if (Capsule::schema()->hasTable($tableName)) {
                echo "   ✅ Skipped: Table '{$tableName}' already exists\n\n";
                continue;
            }
        }

        // Execute the migration
        $migration->up();

        echo "   ✅ Success\n\n";
        $executed++;

    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n\n";
        $errors++;

        // Continue with next migration instead of stopping
        continue;
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Summary:\n";
echo "   • Executed: {$executed}\n";
echo "   • Errors: {$errors}\n";
echo "   • Total: " . count($migrationFiles) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($errors > 0) {
    echo "\n⚠️  Some migrations failed. Please check the errors above.\n";
    exit(1);
}

echo "\n✅ All migrations completed successfully!\n";
exit(0);
