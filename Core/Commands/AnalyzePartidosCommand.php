<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * AnalyzePartidosCommand - Analiza y cachea estadísticas de partidos por competición
 *
 * Este comando procesa todos los documentos de la colección Matches en MongoDB,
 * cuenta cuántos partidos hay por cada competición, y almacena el resultado en Redis.
 *
 * Uso:
 *   php lego analyze:partidos
 *
 * Salida:
 *   - Cachea las estadísticas en Redis con la clave "PARTIDOS"
 *   - Muestra el total de partidos procesados
 */
class AnalyzePartidosCommand extends CoreCommand
{
    protected string $name = 'analyze:partidos';
    protected string $description = 'Analiza y cachea estadísticas de partidos por competición desde MongoDB';
    protected string $signature = 'analyze:partidos';

    /**
     * Execute the command
     */
    public function execute(): bool
    {
        try {
            $this->info('🔍 Analizando partidos desde MongoDB...');

            // Obtener conexión a MongoDB
            $connection = Capsule::connection('mongodb');
            $db = $connection->getMongoDB();
            $collection = $db->selectCollection('Matches');

            // Obtener todos los documentos
            $matches = $collection->find()->toArray();
            $totalMatches = count($matches);

            $this->info("📊 Procesando {$totalMatches} partidos...");

            // Procesar estadísticas de competiciones
            $partidos = [];

            foreach ($matches as $index => $match) {
                // Mostrar progreso cada 100 partidos
                if (($index + 1) % 100 === 0) {
                    $this->progressBar($index + 1, $totalMatches, 'Procesando');
                }

                // Verificar que existe el campo competition
                if (isset($match['competition']['competition_name'])) {
                    $competitionName = $match['competition']['competition_name'];

                    if (isset($partidos[$competitionName])) {
                        $partidos[$competitionName]++;
                    } else {
                        $partidos[$competitionName] = 1;
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

            // Agregar password si está configurado
            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $redisConfig['password'] = $_ENV['REDIS_PASSWORD'];
            }

            $redis = new \Predis\Client($redisConfig);

            // Guardar en Redis como JSON
            $redis->set('PARTIDOS', json_encode($partidos));

            // Mostrar resumen
            $totalCompetitions = count($partidos);
            $this->success("✅ Análisis completado exitosamente");
            $this->info("📈 Total de partidos procesados: {$totalMatches}");
            $this->info("🏆 Total de competiciones encontradas: {$totalCompetitions}");

            // Mostrar top 5 competiciones
            arsort($partidos);
            $this->line("\n🔝 Top 5 Competiciones:");
            $count = 0;
            foreach ($partidos as $competition => $total) {
                if ($count >= 5) break;
                $this->line("   {$competition}: {$total} partidos");
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
