<?php
/**
 * StorageCheckCommand - Verifica el estado del sistema de storage
 *
 * PROPÓSITO:
 * Comando de diagnóstico para verificar que MinIO esté funcionando correctamente.
 * Muestra estadísticas y configuración actual.
 *
 * USO:
 * php lego storage:check
 */

namespace Core\Commands;

use Core\Services\Storage\StorageService;
use Core\Services\Storage\StorageException;

class StorageCheckCommand extends CoreCommand
{
    protected string $name = 'storage:check';
    protected string $description = 'Check storage system status and statistics';
    protected string $signature = 'storage:check';

    /**
     * Execute the command
     */
    public function execute(): bool
    {
        $this->info("📦 Estado del Sistema de Storage\n");
        $this->line(str_repeat("─", 60));

        try {
            $storage = new StorageService();
            $config = $storage->getConfig();

            // 1. Estado de conexión
            $this->line("\n🔌 Conexión:");
            if ($storage->isConnected()) {
                $this->success("  ✓ MinIO conectado");
            } else {
                $this->error("  ✗ MinIO no disponible");
                $this->line("    Ejecuta: docker-compose up -d minio");
                return false;
            }

            // 2. Información de endpoint
            $this->line("\n📍 Endpoints:");
            $this->line("  API:      " . $config->getPublicEndpoint());
            $this->line("  Consola:  http://localhost:" . env('MINIO_CONSOLE_PORT', '9001'));

            // 3. Credenciales (ocultadas parcialmente)
            $this->line("\n🔐 Credenciales:");
            $user = env('MINIO_ROOT_USER', 'minioadmin');
            $pass = env('MINIO_ROOT_PASSWORD', 'minioadmin123');
            $this->line("  Usuario:  " . $user);
            $this->line("  Password: " . str_repeat('*', strlen($pass) - 4) . substr($pass, -4));

            // 4. Estado del bucket
            $bucketName = $config->getBucket();
            $this->line("\n📦 Bucket:");
            if ($storage->bucketExists($bucketName)) {
                $this->success("  ✓ '{$bucketName}' (público)");

                // 5. Estadísticas del bucket
                $this->line("\n📊 Estadísticas:");
                $stats = $storage->getStats();

                $this->line("  Archivos totales: " . number_format($stats['totalFiles']));
                $this->line("  Espacio usado:    " . $stats['totalSizeMB'] . " MB");

                if (!empty($stats['fileTypes'])) {
                    $this->line("\n  Tipos de archivo:");
                    foreach ($stats['fileTypes'] as $type => $count) {
                        $extension = $type ?: 'sin extensión';
                        $this->line("    • {$extension}: {$count} archivo(s)");
                    }
                }

                // 6. Listar estructura de carpetas
                $this->line("\n📁 Estructura de carpetas:");
                $folders = [
                    'images',
                    'documents',
                    'videos',
                    'audio',
                    'users/avatars',
                    'temp',
                ];

                foreach ($folders as $folder) {
                    $result = $storage->list($folder, 10);
                    $count = $result['count'];
                    $icon = $count > 0 ? '📄' : '📁';
                    $this->line("  {$icon} {$folder}/ ({$count} archivo(s))");
                }

            } else {
                $this->error("  ✗ Bucket '{$bucketName}' no existe");
                $this->line("    Ejecuta: php lego init:storage");
                return false;
            }

            // 7. Configuración de uploads
            $this->line("\n⚙️  Configuración de uploads:");
            $this->line("  Tamaño máximo:     " . $config->getMaxFileSize() . " bytes (" . round($config->getMaxFileSize() / 1024 / 1024, 2) . " MB)");
            $this->line("  Extensiones:       " . implode(', ', $config->getAllowedExtensions()));

            // 8. Ejemplo de uso
            $this->line("\n💡 Ejemplo de uso:");
            $this->line("  use Core\\Services\\Storage\\StorageService;");
            $this->line("  \$storage = new StorageService();");
            $this->line("  \$url = \$storage->upload(\$_FILES['file'], 'foto.jpg', 'images/');");

            $this->line("\n" . str_repeat("─", 60));
            $this->success("\n✓ Sistema de storage funcionando correctamente\n");

            return true;

        } catch (StorageException $e) {
            $this->error("\n✗ Error: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->error("\n✗ Error inesperado: " . $e->getMessage());
            $this->line("Detalles: " . $e->getTraceAsString());
            return false;
        }
    }
}
