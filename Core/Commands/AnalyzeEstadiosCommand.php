<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * AnalyzeEstadiosCommand - Analiza y cachea estadísticas de partidos por estadio
 *
 * Este comando procesa todos los documentos de la colección Matches en MongoDB,
 * cuenta cuántos partidos se han jugado en cada estadio, y almacena el resultado en Redis.
 *
 * Uso:
 *   php lego analyze:estadios
 *
 * Salida:
 *   - Cachea las estadísticas en Redis con la clave "ESTADIOS"
 *   - Muestra el total de estadios procesados
 */
class AnalyzeEstadiosCommand extends CoreCommand
{
    protected string $name = 'analyze:estadios';
    protected string $description = 'Analiza y cachea estadísticas de partidos por estadio desde MongoDB';
    protected string $signature = 'analyze:estadios';

    /**
     * Execute the command
     */
    public function execute(): bool
    {
        try {
            $this->info('🏟️  Analizando estadios desde MongoDB...');

            // Obtener conexión a MongoDB
            $connection = Capsule::connection('mongodb');
            $db = $connection->getMongoDB();
            $collection = $db->selectCollection('Matches');

            // Obtener todos los documentos
            $matches = $collection->find()->toArray();
            $totalMatches = count($matches);

            $this->info("📊 Procesando {$totalMatches} partidos...");

            // Procesar estadísticas de estadios
            $estadios = [];

            foreach ($matches as $index => $match) {
                // Mostrar progreso cada 100 partidos
                if (($index + 1) % 100 === 0) {
                    $this->progressBar($index + 1, $totalMatches, 'Procesando');
                }

                // Verificar que existe el campo stadium
                if (isset($match['stadium']['name'])) {
                    $stadiumName = $match['stadium']['name'];

                    if (isset($estadios[$stadiumName])) {
                        $estadios[$stadiumName]++;
                    } else {
                        $estadios[$stadiumName] = 1;
                    }
                }
            }

            // Asegurar que se muestre el 100%
            $this->progressBar($totalMatches, $totalMatches, 'Procesando');

            // Conectar a Redis y guardar los datos
            $this->info('💾 Guardando datos en Redis...');

            $redisConfig = [
                'scheme' => 'tcp',
                'host'   => $_ENV['REDIS_HOST'] ?? 'redis',
                'port'   => $_ENV['REDIS_PORT'] ?? 6379,
            ];

            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $redisConfig['password'] = $_ENV['REDIS_PASSWORD'];
            }

            $redis = new \Predis\Client($redisConfig);

            // Guardar en Redis como JSON
            $redis->set('ESTADIOS', json_encode($estadios));

            // Mostrar resumen
            $totalStadiums = count($estadios);
            $this->success("✅ Análisis completado exitosamente");
            $this->info("📈 Total de partidos procesados: {$totalMatches}");
            $this->info("🏟️  Total de estadios encontrados: {$totalStadiums}");

            // Mostrar top 5 estadios
            arsort($estadios);
            $this->line("\n🔝 Top 5 Estadios:");
            $count = 0;
            foreach ($estadios as $stadium => $total) {
                if ($count >= 5) break;
                $this->line("   {$stadium}: {$total} partidos");
                $count++;
            }

            return true;

        } catch (\Exception $e) {
            $this->error("Error al procesar: " . $e->getMessage());
            $this->line($e->getTraceAsString());
            return false;
        }
    }
}
