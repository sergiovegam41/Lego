<?php

namespace Core\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * AnalyzeBarrasCommand - Analiza competiciones por año (desde 2018)
 *
 * Este comando procesa todos los documentos de la colección Matches en MongoDB,
 * agrupa los partidos por año y por competición, creando un dataset temporal
 * para visualizaciones de barras o series de tiempo.
 *
 * Uso:
 *   php lego analyze:barras
 *
 * Salida:
 *   - Cachea las estadísticas en Redis con la clave "BARRAS"
 *   - Datos agrupados por año desde 2018
 */
class AnalyzeBarrasCommand extends CoreCommand
{
    protected string $name = 'analyze:barras';
    protected string $description = 'Analiza y cachea competiciones por año desde MongoDB (2018+)';
    protected string $signature = 'analyze:barras';

    /**
     * Execute the command
     */
    public function execute(): bool
    {
        try {
            $this->info('📊 Analizando competiciones por año desde MongoDB...');

            // Obtener conexión a MongoDB
            $connection = Capsule::connection('mongodb');
            $db = $connection->getMongoDB();
            $collection = $db->selectCollection('Matches');

            // Obtener todos los documentos
            $matches = $collection->find()->toArray();
            $totalMatches = count($matches);

            $this->info("📅 Procesando {$totalMatches} partidos...");

            // Procesar estadísticas por año y competición
            $barras = [];

            foreach ($matches as $index => $match) {
                // Mostrar progreso cada 100 partidos
                if (($index + 1) % 100 === 0) {
                    $this->progressBar($index + 1, $totalMatches, 'Procesando');
                }

                // Obtener año de la fecha del partido
                if (isset($match['match_date'])) {
                    $year = substr($match['match_date'], 0, 4);

                    // Solo procesar desde 2018
                    if (intval($year) >= 2018) {
                        // Inicializar año si no existe
                        if (!isset($barras[$year])) {
                            $barras[$year] = [
                                "La Liga" => 0,
                                "Indian Super league" => 0,
                                "UEFA Women's Euro" => 0,
                                "Champions League" => 0,
                                "FA Women's Super League" => 0,
                                "Women's World Cup" => 0,
                                "FIFA World Cup" => 0,
                                "NWSL" => 0,
                                "UEFA Euro" => 0,
                                "Premier League" => 0
                            ];
                        }

                        // Incrementar contador de competición
                        if (isset($match['competition']['competition_name'])) {
                            $competitionName = $match['competition']['competition_name'];

                            if (isset($barras[$year][$competitionName])) {
                                $barras[$year][$competitionName]++;
                            } else {
                                $barras[$year][$competitionName] = 1;
                            }
                        }
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
            $redis->set('BARRAS', json_encode($barras));

            // Mostrar resumen
            $totalYears = count($barras);
            $this->success("✅ Análisis completado exitosamente");
            $this->info("📈 Total de partidos procesados: {$totalMatches}");
            $this->info("📅 Total de años analizados: {$totalYears}");

            // Mostrar resumen por año
            ksort($barras);
            $this->line("\n📊 Resumen por Año:");
            foreach ($barras as $year => $competitions) {
                $totalForYear = array_sum($competitions);
                $this->line("   {$year}: {$totalForYear} partidos");
            }

            return true;

        } catch (\Exception $e) {
            $this->error("Error al procesar: " . $e->getMessage());
            $this->line($e->getTraceAsString());
            return false;
        }
    }
}
