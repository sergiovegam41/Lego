<?php

namespace Core\Commands;

use Core\Commands\AnalyzeEstadiosCommand;
use Core\Commands\AnalyzePartidosCommand;
use Core\Commands\AnalyzeJugadoresCommand;
use Core\Commands\AnalyzeMapaCommand;
use Core\Commands\AnalyzeBarrasCommand;

/**
 * AnalyzeAllCommand - Ejecuta todos los comandos de análisis
 *
 * Este comando ejecuta todos los comandos de análisis disponibles en secuencia:
 * - analyze:estadios
 * - analyze:partidos
 * - analyze:jugadores
 * - analyze:mapa
 * - analyze:barras
 *
 * Uso:
 *   php lego analyze:all
 *
 * Salida:
 *   - Ejecuta cada comando de análisis y muestra su progreso
 *   - Muestra un resumen final de todos los análisis completados
 */
class AnalyzeAllCommand extends CoreCommand
{
    protected string $name = 'analyze:all';
    protected string $description = 'Ejecuta todos los comandos de análisis disponibles';
    protected string $signature = 'analyze:all';

    /**
     * Execute the command
     */
    public function execute(): bool
    {
        $this->info('🚀 Iniciando análisis completo de datos...');
        $this->line('');

        $commands = [
            'estadios' => AnalyzeEstadiosCommand::class,
            'partidos' => AnalyzePartidosCommand::class,
            'jugadores' => AnalyzeJugadoresCommand::class,
            'mapa' => AnalyzeMapaCommand::class,
            'barras' => AnalyzeBarrasCommand::class,
        ];

        $results = [];
        $startTime = microtime(true);

        foreach ($commands as $name => $commandClass) {
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info("📊 Ejecutando análisis: {$name}");
            $this->line('');

            try {
                $command = new $commandClass();
                $success = $command->execute();
                $results[$name] = $success;

                $this->line('');
                if ($success) {
                    $this->success("✅ Análisis de {$name} completado exitosamente");
                } else {
                    $this->error("❌ Análisis de {$name} falló");
                }
            } catch (\Exception $e) {
                $results[$name] = false;
                $this->error("❌ Error en análisis de {$name}: " . $e->getMessage());
            }

            $this->line('');
        }

        // Resumen final
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📋 RESUMEN DE ANÁLISIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        $successful = 0;
        $failed = 0;

        foreach ($results as $name => $success) {
            if ($success) {
                $this->success("✅ {$name}");
                $successful++;
            } else {
                $this->error("❌ {$name}");
                $failed++;
            }
        }

        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("✅ Completados: {$successful}");
        if ($failed > 0) {
            $this->error("❌ Fallidos: {$failed}");
        }
        $this->info("⏱️  Tiempo total: {$duration}s");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return $failed === 0;
    }
}
