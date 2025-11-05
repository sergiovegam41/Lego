<?php

namespace App\Controllers;

use Core\Controllers\CoreController;
use Core\Response;

/**
 * AnalysisController - Expone datos estadísticos procesados y cacheados en Redis
 *
 * Este controlador sirve los datos generados por los comandos de análisis
 * que procesan información de MongoDB y la cachean en Redis para un acceso rápido.
 *
 * Endpoints disponibles:
 *   GET /api/analysis/partidos  - Estadísticas de partidos por competición
 *   GET /api/analysis/estadios  - Estadísticas de partidos por estadio
 *   GET /api/analysis/barras    - Competiciones por año (desde 2018)
 *   GET /api/analysis/jugadores - Estadísticas de partidos por manager
 *   GET /api/analysis/mapa      - Estadísticas de partidos por país
 *   GET /api/analysis/status    - Estado del cache
 */
class AnalysisController extends CoreController
{
    /**
     * Obtiene conexión a Redis
     */
    private function getRedis(): \Predis\Client
    {
        $redisConfig = [
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'] ?? 'redis',
            'port'   => $_ENV['REDIS_PORT'] ?? 6379,
        ];

        // Agregar password si está configurado
        if (!empty($_ENV['REDIS_PASSWORD'])) {
            $redisConfig['password'] = $_ENV['REDIS_PASSWORD'];
        }

        return new \Predis\Client($redisConfig);
    }

    /**
     * GET /api/analysis/partidos
     * Obtiene estadísticas de partidos por competición desde Redis
     */
    public function getPartidos()
    {
        try {
            $redis = $this->getRedis();

            // Obtener datos de Redis
            $data = $redis->get('PARTIDOS');

            if (!$data) {
                return Response::json(404, [
                    'success' => false,
                    'message' => 'No hay datos disponibles. Ejecuta: php lego analyze:partidos',
                    'data' => null
                ]);
            }

            // Decodificar JSON
            $partidos = json_decode($data, true);

            return Response::json(200, [
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'data' => $partidos,
                'total_competitions' => count($partidos)
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al obtener datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/analysis/estadios
     * Obtiene estadísticas de partidos por estadio desde Redis
     */
    public function getEstadios()
    {
        try {
            $redis = $this->getRedis();
            $data = $redis->get('ESTADIOS');

            if (!$data) {
                return Response::json(404, [
                    'success' => false,
                    'message' => 'No hay datos disponibles. Ejecuta: php lego analyze:estadios',
                    'data' => null
                ]);
            }

            $estadios = json_decode($data, true);

            return Response::json(200, [
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'data' => $estadios,
                'total_stadiums' => count($estadios)
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al obtener datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/analysis/barras
     * Obtiene competiciones por año desde Redis
     */
    public function getBarras()
    {
        try {
            $redis = $this->getRedis();
            $data = $redis->get('BARRAS');

            if (!$data) {
                return Response::json(404, [
                    'success' => false,
                    'message' => 'No hay datos disponibles. Ejecuta: php lego analyze:barras',
                    'data' => null
                ]);
            }

            $barras = json_decode($data, true);

            return Response::json(200, [
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'data' => $barras,
                'total_years' => count($barras)
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al obtener datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/analysis/jugadores
     * Obtiene estadísticas de partidos por manager desde Redis
     */
    public function getJugadores()
    {
        try {
            $redis = $this->getRedis();
            $data = $redis->get('JUGADORES');

            if (!$data) {
                return Response::json(404, [
                    'success' => false,
                    'message' => 'No hay datos disponibles. Ejecuta: php lego analyze:jugadores',
                    'data' => null
                ]);
            }

            $jugadores = json_decode($data, true);

            return Response::json(200, [
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'data' => $jugadores,
                'total_managers' => count($jugadores)
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al obtener datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/analysis/mapa
     * Obtiene estadísticas de partidos por país desde Redis
     */
    public function getMapa()
    {
        try {
            $redis = $this->getRedis();
            $data = $redis->get('MAPA');

            if (!$data) {
                return Response::json(404, [
                    'success' => false,
                    'message' => 'No hay datos disponibles. Ejecuta: php lego analyze:mapa',
                    'data' => null
                ]);
            }

            $mapa = json_decode($data, true);

            return Response::json(200, [
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'data' => $mapa,
                'total_countries' => count($mapa)
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al obtener datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/analysis/status
     * Verifica qué análisis están disponibles en cache
     */
    public function getStatus()
    {
        try {
            $redis = $this->getRedis();

            $status = [
                'partidos'  => $redis->exists('PARTIDOS') ? 'available' : 'missing',
                'estadios'  => $redis->exists('ESTADIOS') ? 'available' : 'missing',
                'barras'    => $redis->exists('BARRAS') ? 'available' : 'missing',
                'jugadores' => $redis->exists('JUGADORES') ? 'available' : 'missing',
                'mapa'      => $redis->exists('MAPA') ? 'available' : 'missing',
            ];

            return Response::json(200, [
                'success' => true,
                'message' => 'Estado del cache',
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al verificar estado',
                'error' => $e->getMessage()
            ]);
        }
    }
}
