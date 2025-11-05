<?php

namespace App\Controllers;

use Core\Controllers\CoreController;
use Core\Response;

/**
 * MongoTestController - Controlador de prueba para MongoDB
 *
 * Endpoints para validar la conexión a MongoDB Atlas
 */
class MongoTestController extends CoreController
{
    /**
     * GET /api/mongo/test
     * Prueba básica de conexión - retorna total de documentos
     */
    public function test()
    {
        try {
            // Usar conexión directa para contar documentos
            $connection = \Illuminate\Database\Capsule\Manager::connection('mongodb');
            $db = $connection->getMongoDB();
            $collection = $db->selectCollection('Matches');
            $count = $collection->countDocuments();

            return Response::json(200, [
                'success' => true,
                'message' => 'Conexión a MongoDB exitosa',
                'data' => [
                    'database' => $db->getDatabaseName(),
                    'collection' => 'Matches',
                    'total_documents' => $count
                ]
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al conectar con MongoDB',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/mongo/matches
     * Obtiene los primeros 10 documentos de Matches
     */
    public function getMatches()
    {
        try {
            // Usar conexión directa para obtener documentos
            $connection = \Illuminate\Database\Capsule\Manager::connection('mongodb');
            $db = $connection->getMongoDB();
            $collection = $db->selectCollection('Matches');

            // Obtener los primeros 10 documentos
            $matches = $collection->find([], ['limit' => 10])->toArray();

            return Response::json(200, [
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'data' => $matches,
                'total' => count($matches)
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al obtener datos de MongoDB',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/mongo/stadiums
     * Obtiene estadísticas de estadios (similar al comando AnalyzeMatches)
     */
    public function getStadiums()
    {
        try {
            // Usar conexión directa para obtener todos los documentos
            $connection = \Illuminate\Database\Capsule\Manager::connection('mongodb');
            $db = $connection->getMongoDB();
            $collection = $db->selectCollection('Matches');

            $matches = $collection->find()->toArray();
            $stadiums = [];

            foreach ($matches as $match) {
                if (isset($match['stadium']['name'])) {
                    $stadiumName = $match['stadium']['name'];

                    if (isset($stadiums[$stadiumName])) {
                        $stadiums[$stadiumName]++;
                    } else {
                        $stadiums[$stadiumName] = 1;
                    }
                }
            }

            return Response::json(200, [
                'success' => true,
                'message' => 'Estadísticas de estadios obtenidas',
                'data' => $stadiums,
                'total_stadiums' => count($stadiums)
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error al procesar datos',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/mongo/debug
     * Debug: Muestra información de conexión y colecciones
     */
    public function debug()
    {
        try {
            $connection = \Illuminate\Database\Capsule\Manager::connection('mongodb');
            $db = $connection->getMongoDB();

            // Listar todas las colecciones
            $collections = [];
            foreach ($db->listCollections() as $collection) {
                $collectionName = $collection->getName();
                $count = $db->selectCollection($collectionName)->countDocuments();
                $collections[$collectionName] = $count;
            }

            return Response::json(200, [
                'success' => true,
                'message' => 'Información de debug',
                'data' => [
                    'database_name' => $db->getDatabaseName(),
                    'collections' => $collections
                ]
            ]);

        } catch (\Exception $e) {
            return Response::json(500, [
                'success' => false,
                'message' => 'Error en debug',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
