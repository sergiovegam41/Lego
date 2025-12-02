<?php
namespace Components\App\MenuConfig;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Core\Contracts\ScreenInterface;
use Core\Traits\ScreenTrait;
use App\Models\MenuItem;

/**
 * MenuConfigComponent - Configuración del menú de navegación
 *
 * FILOSOFÍA LEGO:
 * Screen OCULTA pero BUSCABLE para configurar el menú de navegación.
 * Permite editar: nombre, icono, orden y nivel de los items.
 *
 * SCREEN PATTERN:
 * - SCREEN_VISIBLE = true: Aparece en el menú lateral
 * - SCREEN_DYNAMIC = false: No requiere contexto dinámico
 * 
 * ACCESO:
 * - Buscar "Configuración" o "Menú" en el buscador
 * - URL directa: /component/menu-config
 */
#[ApiComponent('/menu-config', methods: ['GET'])]
class MenuConfigComponent extends CoreComponent implements ScreenInterface
{
    use ScreenTrait;
    
    // ═══════════════════════════════════════════════════════════════════
    // SCREEN IDENTITY
    // ═══════════════════════════════════════════════════════════════════
    
    public const SCREEN_ID = 'menu-config';
    public const SCREEN_LABEL = 'Configuración del Menú';
    public const SCREEN_ICON = 'settings-outline';
    public const SCREEN_ROUTE = '/component/menu-config';
    public const SCREEN_PARENT = null;
    public const SCREEN_ORDER = 999;
    public const SCREEN_VISIBLE = true;  // Visible en el menú lateral
    public const SCREEN_DYNAMIC = false; // No requiere contexto
    
    // ═══════════════════════════════════════════════════════════════════
    
    protected $CSS_PATHS = [
        "components/Core/Screen/screen.css",
        "./menu-config.css"
    ];
    protected $JS_PATHS = [
        "components/Core/Screen/screen.js",
        "./menu-config.js"
    ];

    protected function component(): string
    {
        // Obtener todos los items del menú ordenados
        // Ordenar primero por level, luego por parent_id (nulls last), luego por display_order
        $menuItems = MenuItem::orderBy('level')
                             ->orderByRaw('parent_id IS NULL, parent_id')
                             ->orderBy('display_order')
                             ->get();

        // Construir árbol para renderizar
        $menuTree = $this->buildMenuTree($menuItems);
        $menuHtml = $this->renderMenuTree($menuTree);

        // Lista de iconos disponibles de Ionicons
        $commonIcons = [
            'home-outline', 'settings-outline', 'construct-outline', 'cube-outline',
            'list-outline', 'add-circle-outline', 'create-outline', 'trash-outline',
            'folder-outline', 'document-outline', 'people-outline', 'person-outline',
            'cart-outline', 'bag-outline', 'pricetag-outline', 'calendar-outline',
            'time-outline', 'analytics-outline', 'bar-chart-outline', 'pie-chart-outline',
            'globe-outline', 'cloud-outline', 'server-outline', 'code-outline',
            'terminal-outline', 'git-branch-outline', 'shield-outline', 'lock-closed-outline',
            'key-outline', 'mail-outline', 'chatbubble-outline', 'notifications-outline',
            'bookmark-outline', 'heart-outline', 'star-outline', 'flag-outline',
            'camera-outline', 'image-outline', 'film-outline', 'musical-notes-outline',
            'flower-outline', 'leaf-outline', 'planet-outline', 'rocket-outline',
            'airplane-outline', 'car-outline', 'bicycle-outline', 'bus-outline',
            'hammer-outline', 'build-outline', 'extension-puzzle-outline', 'layers-outline'
        ];

        $iconsJson = json_encode($commonIcons);
        $screenId = self::SCREEN_ID;
        $screenLabel = self::SCREEN_LABEL;

        return <<<HTML
        <div class="lego-screen lego-screen--padded" data-screen-id="{$screenId}">
            <div class="lego-screen__content">
                <div class="menu-config">
                    <!-- Header -->
                    <div class="menu-config__header">
                        <div class="menu-config__header-info">
                            <h1 class="menu-config__title">
                                <ion-icon name="settings-outline"></ion-icon>
                                {$screenLabel}
                            </h1>
                            <p class="menu-config__subtitle">
                                Arrastra para reordenar, haz clic para editar nombre e icono
                            </p>
                        </div>
                        <div class="menu-config__header-actions">
                            <!-- Menú móvil (solo visible en móviles) -->
                            <div class="menu-config__mobile-menu">
                                <button 
                                    class="menu-config__mobile-menu-btn" 
                                    id="menu-config-mobile-menu-btn"
                                    onclick="toggleMobileMenu(event)"
                                    title="Más opciones"
                                >
                                    <ion-icon name="ellipsis-vertical-outline"></ion-icon>
                                </button>
                                <div class="menu-config__mobile-menu-dropdown" id="menu-config-mobile-menu-dropdown">
                                    <button 
                                        class="menu-config__mobile-menu-item" 
                                        onclick="openNewItemModal(); closeMobileMenu();"
                                    >
                                        <ion-icon name="add-outline"></ion-icon>
                                        <span>Nuevo</span>
                                    </button>
                                    <button 
                                        class="menu-config__mobile-menu-item" 
                                        onclick="exportMenuStructure(); closeMobileMenu();"
                                    >
                                        <ion-icon name="download-outline"></ion-icon>
                                        <span>Exportar</span>
                                    </button>
                                    <button 
                                        class="menu-config__mobile-menu-item" 
                                        onclick="openImportModal(); closeMobileMenu();"
                                    >
                                        <ion-icon name="cloud-upload-outline"></ion-icon>
                                        <span>Importar</span>
                                    </button>
                                    <button 
                                        class="menu-config__mobile-menu-item menu-config__mobile-menu-item--primary" 
                                        onclick="saveMenuConfig(); closeMobileMenu();"
                                    >
                                        <ion-icon name="save-outline"></ion-icon>
                                        <span>Guardar</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Botones desktop (ocultos en móviles) -->
                            <div class="menu-config__desktop-actions">
                                <button 
                                    class="menu-config__btn menu-config__btn--secondary" 
                                    id="menu-config-new-btn"
                                    onclick="openNewItemModal()"
                                >
                                    <ion-icon name="add-outline"></ion-icon>
                                    Nuevo
                                </button>
                                <button 
                                    class="menu-config__btn menu-config__btn--secondary" 
                                    id="menu-config-export-btn"
                                    onclick="exportMenuStructure()"
                                    title="Exportar estructura del menú"
                                >
                                    <ion-icon name="download-outline"></ion-icon>
                                    Exportar
                                </button>
                                <button 
                                    class="menu-config__btn menu-config__btn--secondary" 
                                    id="menu-config-import-btn"
                                    onclick="openImportModal()"
                                    title="Importar estructura del menú"
                                >
                                    <ion-icon name="cloud-upload-outline"></ion-icon>
                                    Importar
                                </button>
                                <button 
                                    class="menu-config__save-btn" 
                                    id="menu-config-save-btn"
                                    onclick="saveMenuConfig()"
                                >
                                    <ion-icon name="save-outline"></ion-icon>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de items -->
                    <div class="menu-config__list" id="menu-config-list">
                        {$menuHtml}
                    </div>

                    <!-- Modal de nuevo item -->
                    <div class="menu-config__modal" id="menu-new-modal" style="display: none;">
                        <div class="menu-config__modal-overlay" onclick="closeNewItemModal()"></div>
                        <div class="menu-config__modal-content">
                            <div class="menu-config__modal-header">
                                <h3>Nuevo Item del Menú</h3>
                                <button class="menu-config__modal-close" onclick="closeNewItemModal()">
                                    <ion-icon name="close-outline"></ion-icon>
                                </button>
                            </div>
                            <form id="menu-new-form" onsubmit="return false;">
                                <div class="menu-config__form-group">
                                    <label for="new-label">Nombre</label>
                                    <input type="text" id="new-label" placeholder="Nombre del item" required>
                                </div>

                                <div class="menu-config__form-group">
                                    <label for="new-route">Ruta del componente</label>
                                    <input type="text" id="new-route" placeholder="/component/mi-item">
                                    <small class="menu-config__form-help">Opcional. Si no tiene ruta, será un grupo. Puedes arrastrar items dentro después.</small>
                                </div>

                                <div class="menu-config__form-group">
                                    <label>Icono</label>
                                    <div class="menu-config__icon-preview" id="new-icon-preview">
                                        <ion-icon name="ellipse-outline" id="new-current-icon"></ion-icon>
                                        <span id="new-current-icon-name">ellipse-outline</span>
                                    </div>
                                    <div class="menu-config__icon-grid" id="new-icon-grid"></div>
                                </div>

                                <div class="menu-config__form-actions">
                                    <button type="button" class="menu-config__btn menu-config__btn--secondary" onclick="closeNewItemModal()">
                                        Cancelar
                                    </button>
                                    <button type="button" class="menu-config__btn menu-config__btn--primary" onclick="createNewItem()">
                                        Crear
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal de edición -->
                    <div class="menu-config__modal" id="menu-edit-modal" style="display: none;">
                        <div class="menu-config__modal-overlay" onclick="closeEditModal()"></div>
                        <div class="menu-config__modal-content">
                            <div class="menu-config__modal-header">
                                <h3>Editar Item</h3>
                                <button class="menu-config__modal-close" onclick="closeEditModal()">
                                    <ion-icon name="close-outline"></ion-icon>
                                </button>
                            </div>
                            <form id="menu-edit-form" onsubmit="return false;">
                                <input type="hidden" id="edit-item-id">
                                
                                <div class="menu-config__form-group">
                                    <label for="edit-label">Nombre</label>
                                    <input type="text" id="edit-label" placeholder="Nombre del item">
                                </div>

                                <div class="menu-config__form-group">
                                    <label for="edit-route">Ruta del componente</label>
                                    <input type="text" id="edit-route" placeholder="/component/mi-item">
                                    <small class="menu-config__form-help">Dejar vacío si es solo un grupo</small>
                                </div>

                                <div class="menu-config__form-group">
                                    <label>Icono</label>
                                    <div class="menu-config__icon-preview" id="icon-preview">
                                        <ion-icon name="ellipse-outline" id="current-icon"></ion-icon>
                                        <span id="current-icon-name">ellipse-outline</span>
                                    </div>
                                    <div class="menu-config__icon-grid" id="icon-grid"></div>
                                </div>

                                <div class="menu-config__form-actions">
                                    <button type="button" class="menu-config__btn menu-config__btn--secondary" onclick="closeEditModal()">
                                        Cancelar
                                    </button>
                                    <button type="button" class="menu-config__btn menu-config__btn--primary" onclick="applyEditChanges()">
                                        Aplicar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal de importación -->
                    <div class="menu-config__modal" id="menu-import-modal" style="display: none;">
                        <div class="menu-config__modal-overlay" onclick="closeImportModal()"></div>
                        <div class="menu-config__modal-content">
                            <div class="menu-config__modal-header">
                                <h3>Importar Estructura del Menú</h3>
                                <button class="menu-config__modal-close" onclick="closeImportModal()">
                                    <ion-icon name="close-outline"></ion-icon>
                                </button>
                            </div>
                            <form id="menu-import-form" onsubmit="return false;">
                                <div class="menu-config__form-group">
                                    <label for="import-mode">Modo de importación</label>
                                    <select id="import-mode" class="menu-config__form-control">
                                        <option value="replace">Reemplazar todo (elimina items existentes)</option>
                                        <option value="merge">Fusionar (actualiza existentes, agrega nuevos)</option>
                                    </select>
                                    <small class="menu-config__form-help">
                                        <strong>Reemplazar:</strong> Elimina todos los items actuales e importa los nuevos.<br>
                                        <strong>Fusionar:</strong> Actualiza items existentes (mismo ID) y agrega los nuevos.
                                    </small>
                                </div>

                                <div class="menu-config__form-group">
                                    <label for="import-file">Archivo JSON</label>
                                    <input type="file" id="import-file" accept=".json" onchange="handleImportFileSelect(event)">
                                    <small class="menu-config__form-help">Selecciona un archivo JSON exportado previamente</small>
                                </div>

                                <div class="menu-config__form-group" id="import-preview" style="display: none;">
                                    <label>Vista previa</label>
                                    <div style="background: var(--bg-input); padding: 12px; border-radius: 6px; max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                                        <div id="import-preview-content"></div>
                                    </div>
                                </div>

                                <div class="menu-config__form-actions">
                                    <button type="button" class="menu-config__btn menu-config__btn--secondary" onclick="closeImportModal()">
                                        Cancelar
                                    </button>
                                    <button type="button" class="menu-config__btn menu-config__btn--primary" id="import-submit-btn" onclick="importMenuStructure()" disabled>
                                        <ion-icon name="cloud-upload-outline"></ion-icon>
                                        Importar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Iconos disponibles
            window.MENU_CONFIG_ICONS = {$iconsJson};
        </script>
        HTML;
    }

    /**
     * Construir árbol de menú desde lista plana
     */
    private function buildMenuTree($items): array
    {
        $tree = [];
        $itemsById = [];

        // Indexar por ID
        foreach ($items as $item) {
            $itemsById[$item->id] = [
                'id' => $item->id,
                'parent_id' => $item->parent_id,
                'label' => $item->label,
                'index_label' => $item->index_label,
                'icon' => $item->icon,
                'route' => $item->route,
                'display_order' => $item->display_order,
                'level' => $item->level,
                'is_visible' => $item->is_visible,
                'is_dynamic' => $item->is_dynamic,
                'children' => []
            ];
        }

        // Construir árbol usando referencias para mantener la estructura
        foreach ($itemsById as $id => &$item) {
            if ($item['parent_id'] === null) {
                $tree[] = &$item;
            } else {
                if (isset($itemsById[$item['parent_id']])) {
                    $itemsById[$item['parent_id']]['children'][] = &$item;
                }
            }
        }
        unset($item); // Limpiar referencia
        
        // Ordenar el árbol recursivamente
        $this->sortMenuTree($tree);

        return $tree;
    }

    /**
     * Ordenar árbol de menú recursivamente por display_order
     */
    private function sortMenuTree(array &$items): void
    {
        usort($items, function($a, $b) {
            return ($a['display_order'] ?? 0) <=> ($b['display_order'] ?? 0);
        });
        
        foreach ($items as &$item) {
            if (!empty($item['children'])) {
                $this->sortMenuTree($item['children']);
            }
        }
    }

    /**
     * Renderizar árbol de menú como HTML
     */
    private function renderMenuTree(array $items, int $level = 0): string
    {
        if (empty($items)) return '';

        $html = '<ul class="menu-config__items" data-level="' . $level . '">';

        foreach ($items as $item) {
            $icon = htmlspecialchars($item['icon'] ?? 'ellipse-outline');
            $label = htmlspecialchars($item['label']);
            $id = htmlspecialchars($item['id']);
            $isVisible = $item['is_visible'] ? 'visible' : 'hidden';
            $isDynamic = $item['is_dynamic'] ? 'dynamic' : 'static';
            $hasChildren = !empty($item['children']);

            $badges = '';
            if (!$item['is_visible']) {
                $badges .= '<span class="menu-config__badge menu-config__badge--hidden" title="Oculto">Oculto</span>';
            }
            if ($item['is_dynamic']) {
                $badges .= '<span class="menu-config__badge menu-config__badge--dynamic" title="Dinámico">Dinámico</span>';
            }
            // Indicador de grupo: si tiene hijos o no tiene ruta
            $hasChildren = isset($item['children']) && is_array($item['children']) && count($item['children']) > 0;
            $isGroup = $hasChildren || empty($item['route']) || $item['route'] === null;
            if ($isGroup) {
                $badges .= '<span class="menu-config__badge menu-config__badge--group" title="Grupo">Grupo</span>';
            }

            // Construir botones según el contexto
            $leftButton = '';
            // Botón de izquierda solo si tiene parent_id Y está en nivel > 0
            $itemLevel = (int)($item['level'] ?? 0);
            if ($item['parent_id'] && $itemLevel > 0) {
                $escapedId = htmlspecialchars($id, ENT_QUOTES);
                $leftButton = '<button class="menu-config__arrow-btn" onclick="moveItemLeft(\'' . $escapedId . '\')" title="Subir de nivel (más superficial)">
                                <ion-icon name="arrow-back-outline"></ion-icon>
                            </button>';
            }
            
            // Contar items en el mismo nivel para ocultar botones arriba/abajo
            $itemsInLevel = count($items);
            $upButton = $itemsInLevel > 1 ? '<button class="menu-config__arrow-btn" onclick="moveItemUp(\'' . htmlspecialchars($id, ENT_QUOTES) . '\')" title="Mover arriba (mismo nivel)">
                                <ion-icon name="arrow-up-outline"></ion-icon>
                            </button>' : '';
            $downButton = $itemsInLevel > 1 ? '<button class="menu-config__arrow-btn" onclick="moveItemDown(\'' . htmlspecialchars($id, ENT_QUOTES) . '\')" title="Mover abajo (mismo nivel)">
                                <ion-icon name="arrow-down-outline"></ion-icon>
                            </button>' : '';

            // Construir botones del menú móvil fuera del heredoc
            $mobileMenuButtons = '';
            if ($itemsInLevel > 1) {
                $escapedId = htmlspecialchars($id, ENT_QUOTES);
                $mobileMenuButtons .= '<button class="menu-config__item-mobile-menu-item" onclick="moveItemUp(\'' . $escapedId . '\'); closeItemMobileMenu(\'' . $escapedId . '\');"><ion-icon name="arrow-up-outline"></ion-icon><span>Mover arriba</span></button>';
                $mobileMenuButtons .= '<button class="menu-config__item-mobile-menu-item" onclick="moveItemDown(\'' . $escapedId . '\'); closeItemMobileMenu(\'' . $escapedId . '\');"><ion-icon name="arrow-down-outline"></ion-icon><span>Mover abajo</span></button>';
            }
            if ($leftButton) {
                $escapedId = htmlspecialchars($id, ENT_QUOTES);
                $mobileMenuButtons .= '<button class="menu-config__item-mobile-menu-item" onclick="moveItemLeft(\'' . $escapedId . '\'); closeItemMobileMenu(\'' . $escapedId . '\');"><ion-icon name="arrow-back-outline"></ion-icon><span>Subir nivel</span></button>';
            }

            $html .= <<<HTML
            <li class="menu-config__item" 
                data-id="{$id}" 
                data-parent="{$item['parent_id']}"
                data-order="{$item['display_order']}"
                data-level="{$item['level']}"
                data-visible="{$isVisible}"
                data-dynamic="{$isDynamic}"
                draggable="true"
            >
                <div class="menu-config__item-content">
                    <span class="menu-config__drag-handle">
                        <ion-icon name="reorder-three-outline"></ion-icon>
                    </span>
                    <span class="menu-config__item-icon">
                        <ion-icon name="{$icon}"></ion-icon>
                    </span>
                    <span class="menu-config__item-label">{$label}</span>
                    {$badges}
                    <div class="menu-config__item-actions">
                        <!-- Menú móvil para acciones (solo visible en móviles) -->
                        <div class="menu-config__item-mobile-menu">
                            <button 
                                class="menu-config__item-mobile-menu-btn" 
                                onclick="toggleItemMobileMenu(event, '{$id}')"
                                title="Más opciones"
                            >
                                <ion-icon name="ellipsis-vertical-outline"></ion-icon>
                            </button>
                            <div class="menu-config__item-mobile-menu-dropdown" id="item-menu-{$id}">
                                {$mobileMenuButtons}
                                <button class="menu-config__item-mobile-menu-item" onclick="openEditModal('{$id}', '{$label}', '{$icon}', '{$item['route']}'); closeItemMobileMenu('{$id}');">
                                    <ion-icon name="create-outline"></ion-icon>
                                    <span>Editar</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Botones desktop (ocultos en móviles) -->
                        <div class="menu-config__item-desktop-actions">
                            <div class="menu-config__arrow-controls">
                                {$upButton}
                                {$downButton}
                                {$leftButton}
                            </div>
                            <button class="menu-config__edit-btn" onclick="openEditModal('{$id}', '{$label}', '{$icon}', '{$item['route']}')">
                                <ion-icon name="create-outline"></ion-icon>
                            </button>
                        </div>
                    </div>
                </div>
            HTML;

            if ($hasChildren) {
                $html .= $this->renderMenuTree($item['children'], $level + 1);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }
}

