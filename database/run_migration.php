<?php
/**
 * Script para ejecutar migraciones PHP manualmente
 * Uso: php database/run_migration.php
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Core/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;

echo "=== Ejecutando Migraciones de LEGO Framework ===\n\n";

// Buscar archivos de migración
$migrationsPath = __DIR__ . '/migrations/';
$migrationFiles = glob($migrationsPath . '*.php');

// Ordenar por nombre (fecha)
sort($migrationFiles);

// Verificar si existe la tabla migrations
try {
    $tables = Capsule::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'migrations'");
    $migrationsTableExists = count($tables) > 0;
} catch (\Exception $e) {
    echo "⚠️  No se pudo verificar tabla de migraciones: " . $e->getMessage() . "\n";
    $migrationsTableExists = false;
}

$executed = 0;
$skipped = 0;
$failed = 0;

foreach ($migrationFiles as $file) {
    $filename = basename($file);

    echo "Procesando: $filename\n";

    // Verificar si ya fue ejecutada (si la tabla existe)
    if ($migrationsTableExists) {
        try {
            $exists = Capsule::table('migrations')
                ->where('migration', $filename)
                ->exists();

            if ($exists) {
                echo "  ⏭️  Ya ejecutada anteriormente\n\n";
                $skipped++;
                continue;
            }
        } catch (\Exception $e) {
            echo "  ⚠️  Error verificando migración: " . $e->getMessage() . "\n\n";
        }
    }

    // Ejecutar migración
    try {
        $migration = require $file;

        if (method_exists($migration, 'up')) {
            $migration->up();
            echo "  ✅ Ejecutada exitosamente\n";
            $executed++;

            // Registrar en tabla migrations (si existe)
            if ($migrationsTableExists) {
                try {
                    Capsule::table('migrations')->insert([
                        'migration' => $filename,
                        'batch' => 1
                    ]);
                } catch (\Exception $e) {
                    echo "  ⚠️  No se pudo registrar en tabla migrations: " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "  ⚠️  Migración no tiene método up()\n";
            $skipped++;
        }
    } catch (\Exception $e) {
        echo "  ❌ Error ejecutando migración: " . $e->getMessage() . "\n";
        $failed++;
    }

    echo "\n";
}

// Resumen
echo "=== Resumen ===\n";
echo "✅ Ejecutadas: $executed\n";
echo "⏭️  Omitidas: $skipped\n";
echo "❌ Fallidas: $failed\n";
echo "\n";

if ($failed > 0) {
    echo "⚠️  Algunas migraciones fallaron. Revisa los errores arriba.\n";
    exit(1);
} else {
    echo "🎉 Proceso completado exitosamente!\n";
    exit(0);
}
