<?php

namespace App\Controllers\Menu\Controllers;

use Core\Controllers\CoreController;
use App\Models\MenuItem;
use Flight;

/**
 * MenuSystemItemsController
 * 
 * Endpoint para obtener items ocultos del sistema (is_visible=false, is_dynamic=false).
 * Estos items no aparecen en el menú lateral pero sí en el dropdown de configuración del header.
 * 
 * REGLAS:
 * - Incluye items con is_visible = false (ocultos)
 * - EXCLUYE items con is_dynamic = true (requieren contexto)
 * - Solo items raíz (sin parent_id) o items de nivel 0-1 para evitar demasiada profundidad
 */
class MenuSystemItemsController extends CoreController
{
    public function __construct()
    {
        $this->getSystemItems();
    }

    /**
     * Obtener items ocultos del sistema
     * 
     * GET /api/menu/system-items
     */
    private function getSystemItems(): void
    {
        try {
            // Obtener items ocultos pero no dinámicos
            // Solo items raíz (sin parent_id) para evitar demasiada profundidad
            $items = MenuItem::where('is_visible', false)
                ->where(function($q) {
                    $q->where('is_dynamic', false)
                      ->orWhereNull('is_dynamic');
                })
                ->whereNull('parent_id') // Solo items raíz
                ->orderBy('display_order')
                ->get();

            // Formatear resultados con información de si tiene hijos
            $results = $items->map(function($item) {
                // Verificar si tiene hijos (solo visibles u ocultos, no dinámicos)
                $hasChildren = MenuItem::where('parent_id', $item->id)
                    ->where(function($q) {
                        $q->where('is_dynamic', false)
                          ->orWhereNull('is_dynamic');
                    })
                    ->exists();

                return [
                    'id' => $item->id,
                    'label' => $item->label,
                    'index_label' => $item->index_label,
                    'route' => $item->route,
                    'icon' => $item->icon,
                    'level' => $item->level,
                    'display_order' => $item->display_order,
                    'has_children' => $hasChildren
                ];
            })->toArray();

            $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error obteniendo items del sistema: ' . $e->getMessage(),
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

