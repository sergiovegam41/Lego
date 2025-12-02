<?php

namespace App\Controllers\MenuConfig\Controllers;

use Core\Controllers\CoreController;
use Core\Response;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;
use App\Models\MenuItem;
use Core\Attributes\ApiRoutes;

/**
 * MenuConfigController - API para configuración del menú
 *
 * FILOSOFÍA LEGO:
 * Controlador para gestionar la configuración del menú de navegación.
 * Permite actualizar: label, icon, display_order, level
 *
 * AUTO-REGISTRO DE RUTAS:
 * POST /api/menu-config/update - Actualizar múltiples items
 * GET  /api/menu-config/list   - Listar todos los items
 */
#[ApiRoutes('/menu-config', preset: 'custom', actions: [
    'update' => ['POST'],
    'list' => ['GET'],
    'create' => ['POST'],
    'delete' => ['POST'],
    'export' => ['GET'],
    'import' => ['POST'],
])]
class MenuConfigController extends CoreController
{
    public function __construct($accion)
    {
        try {
            $this->$accion();
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error en el servidor: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * GET /api/menu-config/list
     * Lista todos los items del menú
     */
    public function list()
    {
        try {
            // Ordenar primero por level, luego por parent_id (nulls last), luego por display_order
            $items = MenuItem::orderBy('level')
                             ->orderByRaw('parent_id IS NULL, parent_id')
                             ->orderBy('display_order')
                             ->get()
                             ->toArray();

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Items del menú obtenidos correctamente',
                $items
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al obtener items: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/menu-config/update
     * Actualiza múltiples items del menú
     * Body: { "items": { "item-id": { "label": "...", "icon": "...", "display_order": 0 }, ... } }
     */
    public function update()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $items = $data['items'] ?? [];

            if (empty($items)) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'No hay items para actualizar',
                    null
                ));
                return;
            }

            $updated = 0;
            $errors = [];

            foreach ($items as $id => $changes) {
                $menuItem = MenuItem::find($id);

                if (!$menuItem) {
                    $errors[] = "Item '{$id}' no encontrado";
                    continue;
                }

                // Aplicar cambios permitidos
                $allowedFields = ['label', 'index_label', 'icon', 'display_order', 'level', 'parent_id', 'route'];
                $updateData = [];

                foreach ($allowedFields as $field) {
                    // Usar array_key_exists en lugar de isset para detectar null explícitamente
                    if (array_key_exists($field, $changes)) {
                        // Convertir 'null' string a null real para parent_id y route
                        if ($field === 'parent_id' || $field === 'route') {
                            if ($changes[$field] === '' || $changes[$field] === 'null' || $changes[$field] === null) {
                                $updateData[$field] = null;
                            } else {
                                $updateData[$field] = $changes[$field];
                            }
                        } else {
                            $updateData[$field] = $changes[$field];
                        }
                    }
                }
                
                // Si se cambia el parent_id, calcular el nivel automáticamente
                if (isset($updateData['parent_id'])) {
                    if ($updateData['parent_id'] === null) {
                        // Si parent_id es null, el item está en nivel raíz
                        $updateData['level'] = 0;
                    } else {
                        // Si tiene padre, calcular nivel desde el padre
                        $parent = MenuItem::find($updateData['parent_id']);
                        if ($parent) {
                            $updateData['level'] = $parent->level + 1;
                        } else {
                            // Si el padre no existe, poner en nivel raíz
                            $updateData['level'] = 0;
                            $updateData['parent_id'] = null;
                            $errors[] = "Item '{$id}': Padre '{$updateData['parent_id']}' no encontrado, movido a nivel raíz";
                        }
                    }
                }

                if (!empty($updateData)) {
                    // Log para debugging
                    error_log("[MenuConfigController] Actualizando item '{$id}': " . json_encode($updateData));
                    
                    // Usar fill() y save() en lugar de update() para manejar correctamente null
                    $menuItem->fill($updateData);
                    $menuItem->save();
                    
                    // Recargar el modelo para obtener los valores actualizados
                    $menuItem->refresh();
                    
                    // Si se cambió el parent_id o level, actualizar recursivamente los niveles de los hijos
                    if (isset($updateData['parent_id']) || isset($updateData['level'])) {
                        $newLevel = $updateData['level'] ?? $menuItem->level;
                        $this->updateChildrenLevels($menuItem, $newLevel);
                    }
                    
                    $updated++;
                }
            }

            $message = "Se actualizaron {$updated} items";
            if (!empty($errors)) {
                $message .= ". Errores: " . implode(', ', $errors);
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                $message,
                ['updated' => $updated, 'errors' => $errors]
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al actualizar items: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * Actualizar recursivamente los niveles de los hijos cuando se mueve un item
     */
    private function updateChildrenLevels(MenuItem $parentItem, int $parentLevel): void
    {
        $children = $parentItem->children()->get();
        
        foreach ($children as $child) {
            $childLevel = $parentLevel + 1;
            $child->update(['level' => $childLevel]);
            
            // Recursivo para nietos
            $this->updateChildrenLevels($child, $childLevel);
        }
    }

    /**
     * POST /api/menu-config/create
     * Crea un nuevo item del menú
     * Body: { "label": "...", "route": "...", "icon": "..." }
     */
    public function create()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $label = $data['label'] ?? '';
            $route = $data['route'] ?? null;
            $icon = $data['icon'] ?? 'ellipse-outline';
            $parentId = $data['parent_id'] ?? null;
            
            if (empty($label)) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'El nombre es requerido',
                    null
                ));
                return;
            }
            
            // Si la ruta está vacía, convertir a null (será un grupo)
            if (empty($route)) {
                $route = null;
            }
            
            // Generar ID único
            $id = 'menu-' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $label));
            $id = trim($id, '-');
            
            // Verificar que el ID no exista
            $counter = 1;
            $originalId = $id;
            while (MenuItem::find($id)) {
                $id = $originalId . '-' . $counter;
                $counter++;
            }
            
            // Determinar nivel
            $level = 0;
            if ($parentId) {
                $parent = MenuItem::find($parentId);
                if ($parent) {
                    $level = $parent->level + 1;
                }
            }
            
            // Obtener el siguiente display_order
            $maxOrder = MenuItem::where('parent_id', $parentId)
                               ->max('display_order') ?? -1;
            $displayOrder = $maxOrder + 1;
            
            // Crear el item
            $menuItem = MenuItem::create([
                'id' => $id,
                'parent_id' => $parentId,
                'label' => $label,
                'index_label' => $label,
                'route' => $route,
                'icon' => $icon,
                'display_order' => $displayOrder,
                'level' => $level,
                'is_visible' => true,
                'is_dynamic' => false
            ]);
            
            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Item creado correctamente',
                $menuItem->toArray()
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al crear item: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/menu-config/delete
     * Elimina un item del menú (y sus hijos si existen)
     * Body: { "id": "item-id" }
     */
    public function delete()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? '';
            
            if (empty($id)) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'El ID del item es requerido',
                    null
                ));
                return;
            }
            
            $menuItem = MenuItem::find($id);
            
            if (!$menuItem) {
                Response::json(StatusCodes::HTTP_NOT_FOUND, (array)new ResponseDTO(
                    false,
                    "Item '{$id}' no encontrado",
                    null
                ));
                return;
            }
            
            // Eliminar recursivamente los hijos
            $this->deleteChildren($menuItem);
            
            // Eliminar el item
            $menuItem->delete();
            
            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                'Item eliminado correctamente',
                null
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al eliminar item: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * Eliminar recursivamente los hijos de un item
     */
    private function deleteChildren(MenuItem $parentItem): void
    {
        $children = $parentItem->children()->get();
        
        foreach ($children as $child) {
            $this->deleteChildren($child);
            $child->delete();
        }
    }

    /**
     * GET /api/menu-config/export
     * Exporta toda la estructura del menú en formato JSON
     */
    public function export()
    {
        try {
            // Obtener todos los items ordenados
            $items = MenuItem::orderBy('level')
                           ->orderByRaw('parent_id IS NULL, parent_id')
                           ->orderBy('display_order')
                           ->get()
                           ->map(function($item) {
                               return [
                                   'id' => $item->id,
                                   'parent_id' => $item->parent_id,
                                   'label' => $item->label,
                                   'index_label' => $item->index_label,
                                   'route' => $item->route,
                                   'icon' => $item->icon,
                                   'display_order' => $item->display_order,
                                   'level' => $item->level,
                                   'is_visible' => $item->is_visible,
                                   'is_dynamic' => $item->is_dynamic,
                               ];
                           })
                           ->toArray();

            $exportData = [
                'version' => '1.0',
                'exported_at' => date('Y-m-d H:i:s'),
                'items' => $items,
                'total_items' => count($items)
            ];

            // Devolver como JSON descargable
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="menu-structure-' . date('Y-m-d_His') . '.json"');
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al exportar menú: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * POST /api/menu-config/import
     * Importa una estructura del menú desde JSON
     * Body: { "data": {...}, "mode": "replace"|"merge" }
     */
    public function import()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $importData = $data['data'] ?? null;
            $mode = $data['mode'] ?? 'replace'; // 'replace' o 'merge'
            
            if (!$importData || !isset($importData['items'])) {
                Response::json(StatusCodes::HTTP_BAD_REQUEST, (array)new ResponseDTO(
                    false,
                    'Formato de importación inválido. Se requiere un objeto con campo "items"',
                    null
                ));
                return;
            }

            $items = $importData['items'];
            $imported = 0;
            $updated = 0;
            $errors = [];

            // Si el modo es 'replace', eliminar todos los items existentes primero
            if ($mode === 'replace') {
                MenuItem::truncate();
            }

            // Validar y procesar cada item
            foreach ($items as $itemData) {
                try {
                    // Validar campos requeridos
                    if (!isset($itemData['id']) || !isset($itemData['label'])) {
                        $errors[] = "Item sin ID o label: " . json_encode($itemData);
                        continue;
                    }

                    $id = $itemData['id'];
                    $existingItem = MenuItem::find($id);

                    // Validar parent_id si existe
                    $parentId = $itemData['parent_id'] ?? null;
                    if ($parentId !== null) {
                        // En modo merge, verificar que el padre exista o se creará después
                        if ($mode === 'merge' && !MenuItem::find($parentId)) {
                            // El padre se creará después, continuar
                        }
                    }

                    // Calcular nivel basado en parent_id
                    $level = 0;
                    if ($parentId !== null) {
                        $parent = MenuItem::find($parentId);
                        if ($parent) {
                            $level = $parent->level + 1;
                        } else {
                            // Si el padre no existe aún, calcular desde el itemData
                            $level = isset($itemData['level']) ? $itemData['level'] : 0;
                        }
                    }

                    $itemDataToSave = [
                        'id' => $id,
                        'parent_id' => $parentId,
                        'label' => $itemData['label'],
                        'index_label' => $itemData['index_label'] ?? $itemData['label'],
                        'route' => $itemData['route'] ?? null,
                        'icon' => $itemData['icon'] ?? 'ellipse-outline',
                        'display_order' => $itemData['display_order'] ?? 0,
                        'level' => $level,
                        'is_visible' => $itemData['is_visible'] ?? true,
                        'is_dynamic' => $itemData['is_dynamic'] ?? false,
                    ];

                    if ($existingItem) {
                        // Actualizar item existente
                        $existingItem->fill($itemDataToSave);
                        $existingItem->save();
                        $updated++;
                    } else {
                        // Crear nuevo item
                        MenuItem::create($itemDataToSave);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error procesando item '{$itemData['id']}': " . $e->getMessage();
                }
            }

            // Recalcular niveles de todos los items (por si acaso)
            $this->recalculateAllLevels();

            $message = "Importación completada: {$imported} nuevos, {$updated} actualizados";
            if (!empty($errors)) {
                $message .= ". Errores: " . count($errors);
            }

            Response::json(StatusCodes::HTTP_OK, (array)new ResponseDTO(
                true,
                $message,
                [
                    'imported' => $imported,
                    'updated' => $updated,
                    'errors' => $errors
                ]
            ));
        } catch (\Exception $e) {
            Response::json(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, (array)new ResponseDTO(
                false,
                'Error al importar menú: ' . $e->getMessage(),
                null
            ));
        }
    }

    /**
     * Recalcular niveles de todos los items basándose en parent_id
     */
    private function recalculateAllLevels(): void
    {
        $items = MenuItem::all();
        
        foreach ($items as $item) {
            $level = 0;
            if ($item->parent_id !== null) {
                $parent = MenuItem::find($item->parent_id);
                if ($parent) {
                    $level = $parent->level + 1;
                }
            }
            
            if ($item->level !== $level) {
                $item->update(['level' => $level]);
            }
        }
    }
}

