<?php

namespace App\Controllers\Menu\Controllers;

use Core\Controllers\CoreController;
use App\Models\MenuItem;
use Flight;

/**
 * MenuSearchController
 * 
 * Endpoint para buscar items del menú desde la base de datos.
 * 
 * REGLAS DE BÚSQUEDA:
 * - Incluye items con is_visible = true (normales)
 * - Incluye items con is_visible = false (ocultos pero buscables)
 * - EXCLUYE items con is_dynamic = true (requieren contexto)
 * 
 * La búsqueda se realiza en los campos:
 * - label (nombre del item)
 * - index_label (nombre alternativo cuando tiene hijos)
 */
class MenuSearchController extends CoreController
{
    public function __construct()
    {
        $this->search();
    }

    /**
     * Buscar items del menú
     * 
     * GET /api/menu/search?q=texto
     */
    private function search(): void
    {
        try {
            $query = Flight::request()->query['q'] ?? '';
            
            if (strlen($query) < 1) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => [],
                    'message' => 'Query too short'
                ]);
                return;
            }

            // Buscar items usando el scope searchable (excluye dinámicos)
            $items = MenuItem::searchable()
                ->where(function($q) use ($query) {
                    $q->where('label', 'LIKE', "%{$query}%")
                      ->orWhere('index_label', 'LIKE', "%{$query}%");
                })
                ->orderBy('level')
                ->orderBy('display_order')
                ->limit(10)
                ->get();

            // Formatear resultados con breadcrumb
            $results = $items->map(function($item) {
                $breadcrumb = $item->getBreadcrumb();
                $breadcrumbLabels = array_map(fn($b) => $b['label'], $breadcrumb);
                
                return [
                    'id' => $item->id,
                    'label' => $item->label,
                    'route' => $item->route,
                    'icon' => $item->icon,
                    'level' => $item->level,
                    'is_visible' => $item->is_visible,
                    'breadcrumb' => $breadcrumbLabels,
                    'breadcrumb_text' => implode(' › ', $breadcrumbLabels),
                    'parent_id' => $item->parent_id
                ];
            })->toArray();

            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'count' => count($results),
                'query' => $query
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error searching menu: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Respuesta JSON
     */
    private function jsonResponse(array $data, int $status = 200): void
    {
        Flight::json($data, $status);
    }
}

