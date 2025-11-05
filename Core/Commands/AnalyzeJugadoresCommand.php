<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * AnalyzeJugadoresCommand - Analiza y cachea estadísticas de partidos por manager
 *
 * Este comando procesa todos los documentos de la colección Matches en MongoDB,
 * cuenta cuántos partidos ha dirigido cada manager (director técnico),
 * y almacena el resultado en Redis.
 *
 * Uso:
 *   php lego analyze:jugadores
 *
 * Salida:
 *   - Cachea las estadísticas en Redis con la clave "JUGADORES"
 *   - Muestra el total de managers procesados
 */
class AnalyzeJugadoresCommand extends CoreCommand
{
    protected string $name = 'analyze:jugadores';
    protected string $description = 'Analiza y cachea estadísticas de partidos por manager desde MongoDB';
    protected string $signature = 'analyze:jugadores';

    /**
     * Execute the command
     */
    public function execute(): bool
    {
        try {
            $this->info('👔 Analizando managers desde MongoDB...');

            // Obtener conexión a MongoDB
            $connection = Capsule::connection('mongodb');
            $db = $connection->getMongoDB();
            $collection = $db->selectCollection('Matches');

            // Obtener todos los documentos
            $matches = $collection->find()->toArray();
            $totalMatches = count($matches);

            $this->info("📊 Procesando {$totalMatches} partidos...");

            // Procesar estadísticas de managers
            $jugadores = [];

            foreach ($matches as $index => $match) {
                // Mostrar progreso cada 100 partidos
                if (($index + 1) % 100 === 0) {
                    $this->progressBar($index + 1, $totalMatches, 'Procesando');
                }

                // Verificar que existe el manager del equipo local
                if (isset($match['home_team']['managers'][0]['name'])) {
                    $managerName = $match['home_team']['managers'][0]['name'];

                    if (isset($jugadores[$managerName])) {
                        $jugadores[$managerName]++;
                    } else {
                        $jugadores[$managerName] = 1;
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
            $redis->set('JUGADORES', json_encode($jugadores));

            // Mostrar resumen
            $totalManagers = count($jugadores);
            $this->success("✅ Análisis completado exitosamente");
            $this->info("📈 Total de partidos procesados: {$totalMatches}");
            $this->info("👔 Total de managers encontrados: {$totalManagers}");

            // Mostrar top 5 managers
            arsort($jugadores);
            $this->line("\n🔝 Top 5 Managers:");
            $count = 0;
            foreach ($jugadores as $manager => $total) {
                if ($count >= 5) break;
                $this->line("   {$manager}: {$total} partidos");
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
