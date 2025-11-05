<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * AnalyzeMapaCommand - Analiza y cachea estadísticas de partidos por país
 *
 * Este comando procesa todos los documentos de la colección Matches en MongoDB,
 * cuenta cuántos partidos se han jugado en cada país, y almacena el resultado en Redis.
 * Útil para visualizaciones de mapas geográficos.
 *
 * Uso:
 *   php lego analyze:mapa
 *
 * Salida:
 *   - Cachea las estadísticas en Redis con la clave "MAPA"
 *   - Muestra el total de países procesados
 */
class AnalyzeMapaCommand extends CoreCommand
{
    protected string $name = 'analyze:mapa';
    protected string $description = 'Analiza y cachea estadísticas de partidos por país desde MongoDB';
    protected string $signature = 'analyze:mapa';

    /**
     * Execute the command
     */
    public function execute(): bool
    {
        try {
            $this->info('🗺️  Analizando partidos por país desde MongoDB...');

            // Obtener conexión a MongoDB
            $connection = Capsule::connection('mongodb');
            $db = $connection->getMongoDB();
            $collection = $db->selectCollection('Matches');

            // Obtener todos los documentos
            $matches = $collection->find()->toArray();
            $totalMatches = count($matches);

            $this->info("📊 Procesando {$totalMatches} partidos...");

            // Procesar estadísticas por país
            $mapa = [];

            foreach ($matches as $index => $match) {
                // Mostrar progreso cada 100 partidos
                if (($index + 1) % 100 === 0) {
                    $this->progressBar($index + 1, $totalMatches, 'Procesando');
                }

                // Verificar que existe el país del estadio
                if (isset($match['stadium']['country']['name'])) {
                    $countryName = $match['stadium']['country']['name'];

                    if (isset($mapa[$countryName])) {
                        $mapa[$countryName]++;
                    } else {
                        $mapa[$countryName] = 1;
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
            $redis->set('MAPA', json_encode($mapa));

            // Mostrar resumen
            $totalCountries = count($mapa);
            $this->success("✅ Análisis completado exitosamente");
            $this->info("📈 Total de partidos procesados: {$totalMatches}");
            $this->info("🌍 Total de países encontrados: {$totalCountries}");

            // Mostrar top 5 países
            arsort($mapa);
            $this->line("\n🔝 Top 5 Países:");
            $count = 0;
            foreach ($mapa as $country => $total) {
                if ($count >= 5) break;
                $this->line("   {$country}: {$total} partidos");
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
