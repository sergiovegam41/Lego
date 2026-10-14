<?php
/**
 * InitStorageCommand - Inicializa el sistema de storage (MinIO)
 *
 * PROPÓSITO:
 * Configura automáticamente MinIO al ejecutar php lego init:storage
 * Crea buckets, aplica políticas y genera estructura de carpetas.
 *
 * FILOSOFÍA LEGO:
 * Setup automático. Zero configuración manual.
 */

namespace Core\Commands;

use Core\Services\Storage\StorageService;
use Core\Services\Storage\StorageException;

class InitStorageCommand extends CoreCommand
{
    protected string $name = 'init:storage';
    protected string $description = 'Initialize storage system (MinIO/S3)';
    protected string $signature = 'init:storage';

    /**
     * Execute the command
     */
    public function execute(): bool
    {
        $this->info("📦 Inicializando sistema de storage...\n");

        try {
            $storage = new StorageService();
            $config = $storage->getConfig();

            // 1. Verificar conexión
            $this->line("→ Verificando conexión con MinIO...");
            if (!$storage->isConnected()) {
                $this->error("✗ MinIO no está disponible");
                $this->line("  Tip: Asegúrate de que MinIO esté corriendo:");
                $this->line("       docker-compose up -d minio");
                return false;
            }
            $this->success("✓ MinIO conectado");

            // 2. Crear bucket si no existe
            $bucketName = $config->getBucket();
            $this->line("\n→ Configurando bucket '{$bucketName}'...");

            if ($storage->bucketExists($bucketName)) {
                $this->success("✓ Bucket '{$bucketName}' ya existe");
            } else {
                $storage->createBucket($bucketName);
                $this->success("✓ Bucket '{$bucketName}' creado");
            }

            // 3. Configurar como público
            $this->line("→ Aplicando política pública...");
            $storage->setBucketPublic($bucketName);
            $this->success("✓ Política pública aplicada");

            // 4. Crear estructura de carpetas
            $this->line("\n→ Creando estructura de carpetas...");
            $folders = [
                'images',
                'documents',
                'videos',
                'audio',
                'users/avatars',
                'temp',
            ];

            foreach ($folders as $folder) {
                $storage->createFolder($folder);
                $this->line("  ✓ {$folder}");
            }

            // 5. Mostrar información
            $this->line("\n" . str_repeat("─", 50));
            $this->success("\n✓ Sistema de storage inicializado correctamente\n");

            $this->line("📍 Información de conexión:");
            $this->line("   Endpoint:  " . $config->getPublicEndpoint());
            $this->line("   Bucket:    " . $bucketName);
            $this->line("   Consola:   http://localhost:" . env('MINIO_CONSOLE_PORT', '9001'));

            $this->line("\n💡 Credenciales de acceso:");
            $this->line("   Usuario:   " . env('MINIO_ROOT_USER', 'minioadmin'));
            $this->line("   Password:  " . env('MINIO_ROOT_PASSWORD', 'minioadmin123'));

            $this->line("\n🔧 Uso desde PHP:");
            $this->line("   use Core\\Services\\Storage\\StorageService;");
            $this->line("   \$storage = new StorageService();");
            $this->line("   \$url = \$storage->upload(\$_FILES['file'], 'nombre.jpg', 'images/');");

            $this->line("");

            return true;

        } catch (StorageException $e) {
            $this->error("\n✗ Error: " . $e->getMessage());
            $this->line("\nCódigo de error: " . $e->getCode());
            return false;
        } catch (\Exception $e) {
            $this->error("\n✗ Error inesperado: " . $e->getMessage());
            return false;
        }
    }
}
