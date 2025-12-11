// ═══════════════════════════════════════════════════════════════════
// INICIALIZAR ESTRUCTURA DEL MENÚ EN WINDOW.LEGO
// ═══════════════════════════════════════════════════════════════════

// Este script recibe la estructura del menú desde PHP via ScriptCoreDTO
// y la almacena en window.lego.menu como fuente de verdad única

// El argumento 'arg' es pasado automáticamente por loadModulesWithArguments
// y contiene { menuStructure: [...] } donde menuStructure ya es un array de objetos
if (typeof arg !== 'undefined' && arg && arg.menuStructure) {
    // Inicializar window.lego si no existe
    window.lego = window.lego || {};

    // menuStructure ya viene como array/objeto desde PHP (json_encode lo convirtió automáticamente)
    window.lego.menu = arg.menuStructure;
} else {
    console.warn('[MenuComponent] No se recibió estructura del menú. arg:', typeof arg !== 'undefined' ? arg : 'undefined');
    window.lego = window.lego || {};
    window.lego.menu = [];
}

// ═══════════════════════════════════════════════════════════════════
// SIDEBAR RESIZE FUNCTIONALITY
// ═══════════════════════════════════════════════════════════════════

let sidebarResizing = false;
        let resizeStartX = 0;
        let resizeStartWidth = 0;
        
        function startSidebarResize(e) {
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar || sidebar.classList.contains('close')) return;
            
            sidebarResizing = true;342
            resizeStartX = e.clientX;
            resizeStartWidth = sidebar.offsetWidth;
            
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            
            // Add visual feedback
            const handle = e.target;
            handle.style.width = '8px !important';
            
            e.preventDefault();
            e.stopPropagation();
        }
        
        document.addEventListener('mousemove', function(e) {
            if (!sidebarResizing) return;
            
            const sidebar = document.querySelector('.sidebar');
            const contentShade = document.getElementById('content-sidebar-shade');
            if (!sidebar) return;
            
            const newWidth = resizeStartWidth + (e.clientX - resizeStartX);
            
            // Constrain between 200px and 400px
            if (newWidth >= 200 && newWidth <= 400) {
                sidebar.style.width = newWidth + 'px';
                
                // Update CSS variable for other elements
                const widthRem = newWidth / 16;
                document.documentElement.style.setProperty('--sidebar-width', widthRem + 'rem');
                
                // Update content shade if it exists
                if (contentShade) {
                    contentShade.style.minWidth = newWidth + 'px';
                }
            }
        });
        
        document.addEventListener('mouseup', function() {
            if (sidebarResizing) {
                sidebarResizing = false;
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                
                // Reset handle appearance
                const handle = document.querySelector('.sidebar-resize-handle');
           
                
                // Save the new width using unified storage manager
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && window.storageManager) {
                    window.storageManager.setSidebarWidth(sidebar.offsetWidth);
                } else if (sidebar) {
                    // Fallback to localStorage if storage manager not available
                    localStorage.setItem('lego_sidebar_width', sidebar.offsetWidth);
                }
            }
        });
        
        // Load saved width on page load
            let savedWidth;
            
            // Try to get width from unified storage manager first
            if (window.storageManager) {
                savedWidth = window.storageManager.getSidebarWidth();
            } else {
                // Fallback to localStorage
                savedWidth = localStorage.getItem('lego_sidebar_width');
            }
            
            if (savedWidth && savedWidth >= 200 && savedWidth <= 400) {
                const sidebar = document.querySelector('.sidebar');
                const contentShade = document.getElementById('content-sidebar-shade');
                
                if (sidebar) {
                    const widthRem = savedWidth / 16;
                    document.documentElement.style.setProperty('--sidebar-width', widthRem + 'rem');
                    sidebar.style.width = savedWidth + 'px';
                    
                    if (contentShade) {
                        contentShade.style.minWidth = savedWidth + 'px';
                    }
                }
            }
        
        // Hide handle when sidebar is collapsed
        function updateResizeHandleVisibility() {
            const sidebar = document.querySelector('.sidebar');
            const handle = document.querySelector('.sidebar-resize-handle');
            
            if (sidebar && handle) {
                if (sidebar.classList.contains('close')) {
                    handle.style.display = 'none';
                } else {
                    handle.style.display = 'block';
                }
            }
        }
        
        // Watch for sidebar toggle
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    updateResizeHandleVisibility();
                }
            });
        });
        
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                observer.observe(sidebar, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }

        // Agregar event listener al resize handle
        const resizeHandle = document.querySelector('.sidebar-resize-handle');
        if (resizeHandle) {
            resizeHandle.addEventListener('mousedown', startSidebarResize);
        } else {
            console.warn('No se encontró el resize handle');
        }

        // ═══════════════════════════════════════════════════════════════════
        // SIDEBAR STATE MANAGER - Gestión de estado con localStorage
        // ═══════════════════════════════════════════════════════════════════
        
        /**
         * SidebarStateManager
         * 
         * Gestiona el estado del sidebar (colapsado/expandido) usando localStorage.
         * Solo guarda estado en desktop, en móvil siempre está colapsado.
         */
        class SidebarStateManager {
            static STORAGE_KEY = 'lego_sidebar_state';
            static STATE_COLLAPSED = 'collapsed';
            static STATE_EXPANDED = 'expanded';
            static MOBILE_BREAKPOINT = 768;
            
            /**
             * Obtener estado guardado del sidebar
             * @returns {string|null} 'collapsed', 'expanded' o null si no hay estado guardado
             */
            static getState() {
                try {
                    return localStorage.getItem(this.STORAGE_KEY);
                } catch (e) {
                    console.warn('[SidebarStateManager] Error leyendo localStorage:', e);
                    return null;
                }
            }
            
            /**
             * Guardar estado del sidebar
             * @param {string} state - 'collapsed' o 'expanded'
             */
            static saveState(state) {
                try {
                    if (state === this.STATE_COLLAPSED || state === this.STATE_EXPANDED) {
                        localStorage.setItem(this.STORAGE_KEY, state);
                        const verify = localStorage.getItem(this.STORAGE_KEY);
                        if (verify !== state) {
                            console.error(`[SidebarStateManager] Error: estado no se guardó correctamente`);
                        }
                    }
                } catch (e) {
                    console.warn('[SidebarStateManager] Error guardando en localStorage:', e);
                }
            }
            
            /**
             * Verificar si estamos en móvil
             * @returns {boolean}
             */
            static isMobile() {
                return window.innerWidth <= this.MOBILE_BREAKPOINT;
            }
            
            /**
             * Aplicar estado al sidebar
             * @param {HTMLElement} sidebar - Elemento del sidebar
             * @param {HTMLElement} sidebarShade - Elemento del shade
             * @param {string} state - 'collapsed' o 'expanded'
             */
            static applyState(sidebar, sidebarShade, state) {
                if (!sidebar) return;
                
                if (state === this.STATE_COLLAPSED) {
                    sidebar.classList.add('close');
                    document.body.classList.add('sidebar-collapsed');
                    
                    if (sidebarShade) {
                        const collapsedWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width-collapsed');
                        const collapsedWidthPx = parseFloat(collapsedWidth) * 16;
                        sidebarShade.style.minWidth = collapsedWidthPx + 'px';
                        sidebarShade.style.width = collapsedWidthPx + 'px';
                    }
                } else {
                    sidebar.classList.remove('close');
                    document.body.classList.remove('sidebar-collapsed');
                    
                    if (sidebarShade) {
                        const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
                        if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
                            sidebarShade.style.minWidth = currentWidth + 'px';
                            sidebarShade.style.width = currentWidth + 'px';
                        } else {
                            const defaultWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
                            const defaultWidthPx = parseFloat(defaultWidth) * 16;
                            sidebarShade.style.minWidth = defaultWidthPx + 'px';
                            sidebarShade.style.width = defaultWidthPx + 'px';
                        }
                    }
                }
            }
        }
        
        // Exponer SidebarStateManager globalmente para que SidebarScript.js pueda usarlo
        window.SidebarStateManager = SidebarStateManager;
        
        // ═══════════════════════════════════════════════════════════════════
        // RESPONSIVE SIDEBAR - Colapsar/expandir automáticamente
        // ═══════════════════════════════════════════════════════════════════
        
        let wasMobile = SidebarStateManager.isMobile(); // Trackear si estábamos en móvil
        
        function handleResponsiveSidebar(forceMobileCollapse = false) {
            const sidebar = document.querySelector('nav.sidebar');
            const sidebarShade = document.querySelector('#content-sidebar-shade');
            if (!sidebar) return;
            
            const isMobile = SidebarStateManager.isMobile();
            const switchedToDesktop = wasMobile && !isMobile;
            const switchedToMobile = !wasMobile && isMobile;
            
            wasMobile = isMobile; // Actualizar estado
            
            if (isMobile) {
                // En móviles: siempre colapsado
                // El usuario puede expandirlo manualmente, pero no guardamos ese estado
                SidebarStateManager.applyState(sidebar, sidebarShade, SidebarStateManager.STATE_COLLAPSED);
            } else if (forceMobileCollapse && isMobile) {
                // Solo forzar colapso si realmente estamos en móvil
                SidebarStateManager.applyState(sidebar, sidebarShade, SidebarStateManager.STATE_COLLAPSED);
            } else if (switchedToDesktop) {
                // Al cambiar de móvil a desktop: restaurar estado guardado
                const savedState = SidebarStateManager.getState();
                const targetState = savedState || SidebarStateManager.STATE_EXPANDED;
                SidebarStateManager.applyState(sidebar, sidebarShade, targetState);
            } else {
                // En desktop: SIEMPRE restaurar estado guardado
                const savedState = SidebarStateManager.getState();
                
                if (savedState) {
                    // SIEMPRE aplicar el estado guardado, sin importar el estado actual
                    SidebarStateManager.applyState(sidebar, sidebarShade, savedState);
                } else {
                    // No hay estado guardado, usar expandido por defecto
                    if (sidebar.classList.contains('close')) {
                        SidebarStateManager.applyState(sidebar, sidebarShade, SidebarStateManager.STATE_EXPANDED);
                    }
                }
            }
        }
        
        // Ejecutar al cargar (después de un pequeño delay para asegurar que el DOM esté listo)
        setTimeout(function() {
            const isMobile = SidebarStateManager.isMobile();
            if (isMobile) {
                // Solo forzar colapso si estamos en móvil
                handleResponsiveSidebar(true);
            } else {
                // En desktop, restaurar estado guardado
                handleResponsiveSidebar(false);
            }
        }, 200);
        
        // Ejecutar al redimensionar ventana (con debounce)
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                const isMobile = SidebarStateManager.isMobile();
                handleResponsiveSidebar(isMobile); // Forzar colapsado si cambiamos a móvil
            }, 150);
        });
        
        // Guardar estado cuando el usuario colapsa/expande manualmente (solo en desktop)
        (function() {
            const sidebarElement = document.querySelector('nav.sidebar');
            if (!sidebarElement) return;
            
            let isManualToggle = false; // Flag para detectar cambios manuales del usuario
            
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const isMobile = SidebarStateManager.isMobile();
                        const isCollapsed = sidebarElement.classList.contains('close');
                        
                        if (!isMobile && isManualToggle) {
                            // En desktop: guardar estado cuando el usuario lo cambia manualmente
                            const state = isCollapsed ? SidebarStateManager.STATE_COLLAPSED : SidebarStateManager.STATE_EXPANDED;
                            SidebarStateManager.saveState(state);
                            isManualToggle = false; // Resetear flag
                        }
                    }
                });
            });
            
            observer.observe(sidebarElement, {
                attributes: true,
                attributeFilter: ['class']
            });
            
            // Detectar clicks en el botón toggle y el botón de búsqueda
            const toggle = document.querySelector('.toggle');
            const searchBtn = document.querySelector('.search-box');
            
            if (toggle) {
                toggle.addEventListener('click', function() {
                    if (!SidebarStateManager.isMobile()) {
                        isManualToggle = true; // Marcar como acción manual en desktop
                    }
                });
            }
            
            if (searchBtn) {
                searchBtn.addEventListener('click', function() {
                    if (!SidebarStateManager.isMobile()) {
                        isManualToggle = true; // Marcar como acción manual en desktop
                    }
                });
            }
        })();
        

/**
 * MenuStateManager
 * 
 * Manages visual state of menu items based on window state
 * States: closed, open (with dot), active (blue background)
 */
class MenuStateManager {
    constructor() {
        this.setupEventListeners();
        this.setupModuleActivationListener();
    }
    
    /**
     * Setup listener for module activation events
     * This ensures menu state is updated when modules are activated
     */
    setupModuleActivationListener() {
        window.addEventListener('lego:module:activated', (event) => {
            const { moduleId } = event.detail || {};
            // Small delay to ensure DOM is updated
            setTimeout(() => {
                this.syncWithModuleStore();
                // Also try to set state directly for the activated module
                if (moduleId) {
                    this.setState(moduleId, 'active');
                }
            }, 100);
        });
    }

    /**
     * Update menu item visual state
     * @param {string} moduleId - Module ID
     * @param {string} state - 'active', 'open', or 'closed'
     */
    setState(moduleId, state) {
        // Only search in the main sidebar menu, not in popovers
        const menuContainer = document.querySelector('.custom-menu');
        if (!menuContainer) {
            console.warn(`[MenuStateManager] Menu container no encontrado para setState: ${moduleId}`);
            return;
        }
        
        // Buscar TODOS los items con ese ID (por si hay duplicados)
        const menuItems = menuContainer.querySelectorAll(`[data-menu-item-id="${moduleId}"]:not([data-temp-item="true"])`);
        
        if (menuItems.length === 0) {
            console.warn(`[MenuStateManager] Item no encontrado en menú: ${moduleId}`);
            return;
        }
        
        // Si hay múltiples items con el mismo ID, es un problema - loguear advertencia
        if (menuItems.length > 1) {
            console.warn(`[MenuStateManager] ⚠️ ADVERTENCIA: Múltiples items encontrados con ID ${moduleId} (${menuItems.length} items). Esto puede causar problemas de estado.`);
        }

        // Aplicar estado a TODOS los items encontrados (aunque no debería haber duplicados)
        menuItems.forEach(menuItem => {
            // Remove all states
            menuItem.classList.remove('menu-state--active', 'menu-state--open');

            // Add new state
            if (state === 'active') {
                menuItem.classList.add('menu-state--active');
            } else if (state === 'open') {
                menuItem.classList.add('menu-state--open');
            }
            // 'closed' state means no classes
        });
        
        if (state === 'active') {
            console.log(`[MenuStateManager] Estado 'active' aplicado a: ${moduleId} (${menuItems.length} item(s))`);
        } else if (state === 'open') {
            console.log(`[MenuStateManager] Estado 'open' aplicado a: ${moduleId} (${menuItems.length} item(s))`);
        }
    }

    /**
     * Sync menu states with ModuleStore
     */
    syncWithModuleStore() {
        if (!window.moduleStore) {
            console.warn('[MenuStateManager] ModuleStore no disponible');
            return;
        }

        const modules = window.moduleStore.modules;
        const activeModuleId = window.moduleStore.activeModule;

        console.log(`[MenuStateManager] Sincronizando estado. Módulo activo: ${activeModuleId}, Módulos abiertos:`, Object.keys(modules));

        // Get all menu items - ONLY from the main sidebar menu, not from popovers
        const menuContainer = document.querySelector('.custom-menu');
        if (!menuContainer) {
            console.warn('[MenuStateManager] Menu container no encontrado');
            return;
        }

        const allMenuItems = menuContainer.querySelectorAll('[data-menu-item-id]');
        console.log(`[MenuStateManager] Items encontrados en menú: ${allMenuItems.length}`);

        // PRIMERO: Remover el estado 'active' de TODOS los items
        // Solo UN item puede estar activo a la vez
        allMenuItems.forEach(menuItem => {
            menuItem.classList.remove('menu-state--active');
        });

        // SEGUNDO: Aplicar estados correctos
        allMenuItems.forEach(menuItem => {
            const moduleId = menuItem.getAttribute('data-menu-item-id');
            if (!moduleId) return;

            // Verificar si el item realmente está abierto en moduleStore
            const isInModuleStore = modules[moduleId] !== undefined;
            
            // Debug: loguear si un item dinámico está en moduleStore cuando no debería
            const isDynamic = menuItem.hasAttribute('data-dynamic-item');
            if (isDynamic && isInModuleStore && moduleId !== activeModuleId) {
                const itemUrl = menuItem.getAttribute('moduleUrl') || menuItem.getAttribute('data-module-url') || '';
                console.warn(`[MenuStateManager] ⚠️ Item dinámico ${moduleId} está en moduleStore pero no es activo. URL: ${itemUrl}`);
            }

            if (moduleId === activeModuleId) {
                // Solo el módulo activo debe tener estado 'active'
                console.log(`[MenuStateManager] Marcando como activo: ${moduleId}`);
                this.setState(moduleId, 'active');
            } else if (isInModuleStore) {
                // Solo los módulos que realmente están en moduleStore deben tener estado 'open'
                console.log(`[MenuStateManager] Marcando como abierto: ${moduleId}`);
                this.setState(moduleId, 'open');
            } else {
                // Items que no están en moduleStore no tienen estado
                this.setState(moduleId, 'closed');
            }
        });
    }

    /**
     * Setup event listeners for close buttons
     */
    setupEventListeners() {
        // Delegate event listener for close buttons
        document.addEventListener('click', (e) => {
            const closeButton = e.target.closest('.menu-close-button');
            if (!closeButton) return;

            e.preventDefault();
            e.stopPropagation();

            const menuItem = closeButton.closest('[data-menu-item-id]');
            if (!menuItem) return;

            const moduleId = menuItem.getAttribute('data-menu-item-id');

            // Close the module via legoWindowManager
            if (window.legoWindowManager) {
                window.legoWindowManager.closeModule(moduleId);
            }
        });
    }
}

// Initialize MenuStateManager
if (typeof window.menuStateManager === 'undefined') {
    window.menuStateManager = new MenuStateManager();
}

// ═══════════════════════════════════════════════════════════════════
// MENU HIDDEN ITEMS VISIBILITY MANAGER
// ═══════════════════════════════════════════════════════════════════

/**
 * MenuHiddenItemsManager
 * 
 * Cuando se abre un item oculto, muestra toda su jerarquía en el menú
 * (padres e hijos) para facilitar el acceso a contenido relacionado
 */
class MenuHiddenItemsManager {
    constructor() {
        this.visibleHierarchies = new Map(); // Guarda las jerarquías visibles por módulo
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Escuchar cuando se activa un módulo
        window.addEventListener('lego:module:activated', (e) => {
            const moduleId = e.detail?.moduleId;
            if (moduleId) {
                this.handleModuleActivated(moduleId);
            }
        });

        // Escuchar cuando se cierra un módulo
        if (window.legoWindowManager) {
            // Interceptar closeModule para limpiar jerarquías
            const originalCloseModule = window.legoWindowManager.closeModule;
            if (originalCloseModule) {
                window.legoWindowManager.closeModule = (moduleId, options) => {
                    this.handleModuleClosed(moduleId);
                    return originalCloseModule.call(window.legoWindowManager, moduleId, options);
                };
            }
        }
    }

    /**
     * Manejar cuando se activa un módulo
     */
    async handleModuleActivated(moduleId) {
        // Verificar si el módulo ya está en el menú visible (como item dinámico o normal)
        const existsInMenu = document.querySelector(`[data-menu-item-id="${moduleId}"]:not([data-temp-item="true"])`);
        
        if (existsInMenu) {
            // Ya está visible, no hacer nada
            return;
        }

        // Verificar si es un item oculto consultando la API
        try {
            const response = await fetch(`/api/menu/item-hierarchy?id=${encodeURIComponent(moduleId)}`);
            const result = await response.json();

            if (result.success && result.data) {
                const hierarchy = result.data;
                const item = hierarchy.item;

                // Solo mostrar si es oculto (is_visible = false) y no es dinámico
                if (!item.is_visible && !item.is_dynamic) {
                    // Usar addDynamicMenuItem para agregarlo al menú como item dinámico
                    await this.addHiddenItemAsDynamic(moduleId, hierarchy);
                }
            }
        } catch (error) {
            console.error('[MenuHiddenItems] Error obteniendo jerarquía:', error);
        }
    }

    /**
     * Agregar item oculto al menú usando addDynamicMenuItem (como items dinámicos)
     */
    async addHiddenItemAsDynamic(moduleId, hierarchy) {
        const item = hierarchy.item;
        const ancestors = hierarchy.ancestors || [];
        const siblings = hierarchy.siblings || [];
        const menuContainer = document.querySelector('.custom-menu');
        
        if (!menuContainer) {
            console.error('[MenuHiddenItems] Menu container no encontrado');
            return;
        }
        
        // Determinar el parentMenuId
        // Si tiene ancestros, usar el último ancestro como padre
        // Si no tiene ancestros, agregar directamente al menú (no usar addDynamicMenuItem)
        let parentMenuId = null;
        
        // Asegurar que los ancestros también estén en el menú (de raíz a hoja)
        for (let i = 0; i < ancestors.length; i++) {
            const ancestor = ancestors[i];
            const ancestorExists = menuContainer.querySelector(`[data-menu-item-id="${ancestor.id}"]:not([data-temp-item="true"])`);
            
            if (!ancestorExists) {
                // Determinar el parent del ancestro
                // El primer ancestro (i=0) va a la raíz, los demás van como hijos del ancestro anterior
                const ancestorParentId = i > 0 ? ancestors[i - 1].id : null;
                
                // Si no hay parent, agregar directamente al menú
                if (!ancestorParentId) {
                    this.addItemDirectlyToMenu(ancestor, menuContainer, 0);
                } else {
                    // Verificar que el parent existe antes de agregar
                    const parentExists = menuContainer.querySelector(`[data-menu-item-id="${ancestorParentId}"]:not([data-temp-item="true"])`);
                    // Solo agregar ancestros con ruta válida (no '#')
                    const ancestorUrl = ancestor.url || ancestor.route || '#';
                    if (parentExists && ancestorUrl !== '#' && ancestorUrl && window.legoWindowManager && window.legoWindowManager.addDynamicMenuItem) {
                        window.legoWindowManager.addDynamicMenuItem({
                            moduleId: ancestor.id,
                            parentMenuId: ancestorParentId,
                            label: ancestor.display_name || ancestor.label,
                            icon: ancestor.icon || 'folder-outline',
                            url: ancestorUrl
                        });
                    } else if (ancestorUrl === '#' || !ancestorUrl) {
                        console.log(`[MenuHiddenItems] Saltando ancestro ${ancestor.id}: no tiene ruta válida (url: ${ancestorUrl})`);
                    }
                }
            }
        }
        
        // Determinar el parent del item principal
        if (ancestors.length > 0) {
            const lastAncestor = ancestors[ancestors.length - 1];
            // Verificar que el ancestro existe en el DOM antes de usarlo como parent
            const ancestorExists = menuContainer.querySelector(`[data-menu-item-id="${lastAncestor.id}"]:not([data-temp-item="true"])`);
            if (ancestorExists) {
                parentMenuId = lastAncestor.id;
            }
        }
        
        // Combinar el item principal y sus hermanos, ordenar por display_order, y agregar en orden
        // PERO excluir los items dinámicos (is_dynamic: true) - esos solo se muestran cuando se abren explícitamente
        const sortedSiblings = siblings
            .filter(sibling => sibling.is_dynamic !== true) // Excluir dinámicos
            .sort((a, b) => (a.display_order || 0) - (b.display_order || 0)); // Ordenar por display_order
        
        // Combinar item principal y hermanos en un solo array para ordenar
        const allItemsToInsert = [
            { ...item, isMainItem: true }
        ];
        
        sortedSiblings.forEach(sibling => {
            allItemsToInsert.push({ ...sibling, isMainItem: false });
        });
        
        // Ordenar todos los items por display_order
        allItemsToInsert.sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
        
        const siblingIds = [];
        let lastInsertedId = null;
        
        // Agregar todos los items en orden
        for (const currentItem of allItemsToInsert) {
            // Verificar que el item no existe ya en el menú
            const itemExists = menuContainer.querySelector(`[data-menu-item-id="${currentItem.id}"]:not([data-temp-item="true"])`);
            
            if (!itemExists && parentMenuId) {
                // Solo agregar items con ruta válida (no '#')
                const itemUrl = currentItem.url || currentItem.route || '#';
                if (itemUrl === '#' || !itemUrl) {
                    console.log(`[MenuHiddenItems] Saltando item ${currentItem.id}: no tiene ruta válida (url: ${itemUrl})`);
                    // Aún así, actualizar lastInsertedId para mantener el orden
                    lastInsertedId = currentItem.id;
                    continue;
                }
                
                if (window.legoWindowManager && window.legoWindowManager.addDynamicMenuItem) {
                    window.legoWindowManager.addDynamicMenuItem({
                        moduleId: currentItem.id,
                        parentMenuId: parentMenuId,
                        label: currentItem.display_name || currentItem.label,
                        icon: currentItem.icon || 'settings-outline',
                        url: itemUrl,
                        insertAfter: lastInsertedId // Insertar después del último item agregado
                    });
                    
                    if (!currentItem.isMainItem) {
                        siblingIds.push(currentItem.id);
                    }
                    
                    // Agregar hijos del item recursivamente (mantener jerarquía completa)
                    const itemChildren = currentItem.children || [];
                    const itemChildrenIds = [];
                    for (const child of itemChildren) {
                        // Excluir hijos dinámicos también
                        if (child.is_dynamic === true) {
                            continue;
                        }
                        const childIds = await this.addChildAsDynamic(child, currentItem.id);
                        itemChildrenIds.push(...childIds);
                    }
                    
                    // Registrar item en visibleHierarchies con sus hijos
                    if (!this.visibleHierarchies.has(currentItem.id)) {
                        this.visibleHierarchies.set(currentItem.id, {
                            itemId: currentItem.id,
                            parentId: parentMenuId,
                            childrenIds: itemChildrenIds,
                            ancestorIds: ancestors.map(a => a.id),
                            isSibling: !currentItem.isMainItem // Marcar como hermano si no es el item principal
                        });
                    }
                    
                    lastInsertedId = currentItem.id; // Actualizar para el siguiente item
                }
            } else if (itemExists) {
                // Si el item ya existe, actualizar lastInsertedId para mantener el orden
                lastInsertedId = currentItem.id;
            }
        }
        
        // Si no tiene padre, agregar directamente al menú
        if (!parentMenuId) {
            this.addItemDirectlyToMenu(item, menuContainer, 0);
        }
        
        // Agregar hijos del item principal recursivamente (solo después de que el item principal se haya agregado)
        const children = hierarchy.children || [];
        const childrenIds = [];
        for (const child of children) {
            // Excluir hijos dinámicos también
            if (child.is_dynamic === true) {
                continue;
            }
            const childIds = await this.addChildAsDynamic(child, moduleId);
            childrenIds.push(...childIds);
            
            // Registrar cada hijo en visibleHierarchies para poder limpiarlos cuando se cierre el padre
            // Esto permite que cuando se cierre el padre, se cierren todos los hijos también
            if (!this.visibleHierarchies.has(child.id)) {
                this.visibleHierarchies.set(child.id, {
                    itemId: child.id,
                    parentId: moduleId, // El padre es el item principal
                    childrenIds: [],
                    ancestorIds: [...ancestors.map(a => a.id), moduleId] // Incluir ancestros + padre
                });
            }
        }
        
        // Guardar referencia para limpiar después
        this.visibleHierarchies.set(moduleId, {
            itemId: moduleId,
            parentId: parentMenuId,
            childrenIds: childrenIds,
            ancestorIds: ancestors.map(a => a.id),
            siblingIds: siblingIds // Guardar IDs de hermanos para limpieza
        });
    }
    
    /**
     * Agregar item directamente al menú (sin padre)
     */
    addItemDirectlyToMenu(item, menuContainer, level) {
        const itemId = item.id;
        const label = item.display_name || item.label;
        const icon = item.icon || 'settings-outline';
        const url = item.url || item.route || '#';
        const hasChildren = item.has_children || (item.children && item.children.length > 0);
        
        // Verificar si ya existe
        const exists = menuContainer.querySelector(`[data-menu-item-id="${itemId}"]:not([data-temp-item="true"])`);
        if (exists) {
            return;
        }
        
        let html = '';
        if (hasChildren) {
            // Item con hijos - crear como grupo
            html = `
                <div class="custom-menu-section" data-menu-item-id="${itemId}" data-dynamic-item="true">
                    <div class="custom-menu-title level-${level}">
                        <ion-icon name="${icon}" class="icon_menu icon_menu_parent"></ion-icon>
                        <p class="text_menu_option">${label}</p>
                        <ion-icon name="chevron-forward-outline" class="icon_menu icon_menu_chevron"></ion-icon>
                    </div>
                    <div class="custom-submenu section-level-${level + 1}" style="display: block;">
                    </div>
                </div>
            `;
        } else {
            // Item simple
            html = `
                <div class="custom-menu-section menu_item_openable dynamic-menu-item"
                    moduleId="${itemId}"
                    moduleUrl="${url}"
                    data-menu-item-id="${itemId}"
                    data-module-id="${itemId}"
                    data-module-url="${url}"
                    data-dynamic-item="true">
                    <button class="menu-close-button" title="Cerrar">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                    <button class="custom-button level-${level}">
                        <ion-icon name="${icon}" class="icon_menu"></ion-icon>
                        <p class="text_menu_option">${label}</p>
                        <div class="menu-state-indicator"></div>
                    </button>
                </div>
            `;
        }
        
        menuContainer.insertAdjacentHTML('beforeend', html);
        
        // Agregar listener si es item abrible
        const newItem = menuContainer.querySelector(`[data-menu-item-id="${itemId}"]`);
        if (newItem && newItem.classList.contains('menu_item_openable')) {
            newItem.addEventListener('click', function(e) {
                if (e.target.closest('.menu-close-button')) return;
                
                const id = this.getAttribute('moduleId') || this.getAttribute('data-menu-item-id');
                const itemUrl = this.getAttribute('moduleUrl') || '#';
                const name = this.querySelector('.text_menu_option')?.textContent || id;
                
                console.log(`[MenuHiddenItems] Click en item agregado directamente: id=${id}, url=${itemUrl}, name=${name}`);
                
                if (window.moduleStore && window.moduleStore.getActiveModule() !== id) {
                    if (typeof window.openModule === 'function') {
                        console.log(`[MenuHiddenItems] Abriendo módulo: ${id}`);
                        window.openModule(id, itemUrl, name, { url: itemUrl, name });
                        // Sincronizar estado después de abrir
                        setTimeout(() => {
                            if (window.menuStateManager) {
                                console.log(`[MenuHiddenItems] Sincronizando estado después de abrir: ${id}`);
                                window.menuStateManager.syncWithModuleStore();
                            }
                        }, 100);
                    }
                } else {
                    // Ya está activo, solo sincronizar estado
                    if (window.menuStateManager) {
                        window.menuStateManager.syncWithModuleStore();
                    }
                }
            });
        }
        
        // Agregar a window.lego.menu
        if (window.lego && window.lego.menu && Array.isArray(window.lego.menu)) {
            window.lego.menu.push({
                id: itemId,
                name: label,
                url: url,
                iconName: icon,
                level: level,
                childs: [],
                isDynamic: true
            });
        }
    }

    /**
     * Agregar un hijo como item dinámico (recursivo)
     * Retorna array de IDs de hijos agregados
     */
    async addChildAsDynamic(child, parentId) {
        const menuContainer = document.querySelector('.custom-menu');
        if (!menuContainer) return [];
        
        // Verificar si ya existe
        const exists = menuContainer.querySelector(`[data-menu-item-id="${child.id}"]:not([data-temp-item="true"])`);
        if (exists) return [child.id];
        
        // Verificar que el parent existe antes de agregar
        const parentExists = menuContainer.querySelector(`[data-menu-item-id="${parentId}"]:not([data-temp-item="true"])`);
        if (!parentExists) {
            console.warn(`[MenuHiddenItems] Parent ${parentId} no existe, no se puede agregar hijo ${child.id}`);
            return [];
        }
        
        // Agregar el hijo usando addDynamicMenuItem (siempre tiene padre)
        if (window.legoWindowManager && window.legoWindowManager.addDynamicMenuItem) {
            window.legoWindowManager.addDynamicMenuItem({
                moduleId: child.id,
                parentMenuId: parentId,
                label: child.display_name || child.label,
                icon: child.icon || 'ellipse-outline',
                url: child.url || child.route || '#'
            });
        }
        
        const childIds = [child.id];
        
        // Agregar hijos recursivamente
        const children = child.children || [];
        for (const grandChild of children) {
            const grandChildIds = await this.addChildAsDynamic(grandChild, child.id);
            childIds.push(...grandChildIds);
        }
        
        return childIds;
    }


    /**
     * Manejar cuando se cierra un módulo
     */
    handleModuleClosed(moduleId) {
        const hierarchy = this.visibleHierarchies.get(moduleId);
        if (!hierarchy) {
            // Si no está en visibleHierarchies, puede ser un hijo de otro item dinámico
            // Buscar si algún item dinámico tiene este como hijo
            for (const [parentId, parentHierarchy] of this.visibleHierarchies.entries()) {
                if (parentHierarchy.childrenIds && parentHierarchy.childrenIds.includes(moduleId)) {
                    // Este es un hijo de otro item dinámico
                    // Si se cierra un hijo, no necesariamente cerramos el padre
                    // Pero sí removemos este hijo de la lista
                    const childIndex = parentHierarchy.childrenIds.indexOf(moduleId);
                    if (childIndex > -1) {
                        parentHierarchy.childrenIds.splice(childIndex, 1);
                    }
                    // Remover solo este hijo
                    if (window.legoWindowManager && window.legoWindowManager.removeDynamicMenuItem) {
                        window.legoWindowManager.removeDynamicMenuItem(moduleId);
                    }
                    return;
                }
            }
            return;
        }

        // Remover usando removeDynamicMenuItem (más limpio)
        if (window.legoWindowManager && window.legoWindowManager.removeDynamicMenuItem) {
            // Primero, remover todos los hijos recursivamente
            const removeChildrenRecursively = (childIds) => {
                if (!childIds || childIds.length === 0) return;
                
                childIds.forEach(childId => {
                    const childHierarchy = this.visibleHierarchies.get(childId);
                    if (childHierarchy && childHierarchy.childrenIds && childHierarchy.childrenIds.length > 0) {
                        // Remover hijos del hijo primero
                        removeChildrenRecursively(childHierarchy.childrenIds);
                    }
                    // Remover el hijo
                    window.legoWindowManager.removeDynamicMenuItem(childId);
                    this.visibleHierarchies.delete(childId);
                });
            };
            
            // Remover hijos primero (recursivamente)
            if (hierarchy.childrenIds && hierarchy.childrenIds.length > 0) {
                removeChildrenRecursively([...hierarchy.childrenIds]);
            }
            
            // Remover el item principal
            window.legoWindowManager.removeDynamicMenuItem(moduleId);
            
            // Remover hermanos solo si no están abiertos en otros módulos
            if (hierarchy.siblingIds && hierarchy.siblingIds.length > 0) {
                hierarchy.siblingIds.forEach(siblingId => {
                    const siblingHierarchy = this.visibleHierarchies.get(siblingId);
                    // Solo remover si es un hermano (marcado como tal) y no está abierto
                    if (siblingHierarchy && siblingHierarchy.isSibling) {
                        // Verificar si el hermano está abierto en algún módulo
                        const isOpen = window.moduleStore && window.moduleStore.modules && window.moduleStore.modules[siblingId];
                        if (!isOpen) {
                            window.legoWindowManager.removeDynamicMenuItem(siblingId);
                            this.visibleHierarchies.delete(siblingId);
                        }
                    }
                });
            }
            
            // Remover ancestros solo si no tienen otros hijos
            if (hierarchy.ancestorIds && hierarchy.ancestorIds.length > 0) {
                [...hierarchy.ancestorIds].reverse().forEach(ancestorId => {
                    // Verificar si el ancestro tiene otros hijos antes de removerlo
                    const ancestorElement = document.querySelector(`[data-menu-item-id="${ancestorId}"]`);
                    if (ancestorElement) {
                        const submenu = ancestorElement.querySelector('.custom-submenu');
                        const otherDynamicChildren = submenu?.querySelectorAll('[data-dynamic-item="true"][data-menu-item-id]');
                        // Solo remover si no tiene otros hijos dinámicos
                        if (!otherDynamicChildren || otherDynamicChildren.length === 0) {
                            window.legoWindowManager.removeDynamicMenuItem(ancestorId);
                            this.visibleHierarchies.delete(ancestorId);
                        }
                    }
                });
            }
        }

        this.visibleHierarchies.delete(moduleId);
    }
}

// Initialize MenuHiddenItemsManager
if (typeof window.menuHiddenItemsManager === 'undefined') {
    window.menuHiddenItemsManager = new MenuHiddenItemsManager();
}

// ═══════════════════════════════════════════════════════════════════
// MENU SEARCH/FILTER FUNCTIONALITY
// ═══════════════════════════════════════════════════════════════════

/**
 * MenuFilterManager
 * 
 * Filtra el menú lateral en tiempo real mientras el usuario escribe.
 * - Oculta items que no coinciden
 * - Expande submenús que contienen coincidencias
 * - Resalta el texto que coincide
 * - Busca desde la base de datos (incluye ocultos, excluye dinámicos)
 */
class MenuFilterManager {
    constructor() {
        this.searchInput = document.getElementById('search-menu');
        this.menuContainer = document.querySelector('.custom-menu');
        this.debounceTimer = null;
        this.originalStates = new Map(); // Guardar estados originales
        this.isFiltering = false;
        
        if (this.searchInput && this.menuContainer) {
            this.init();
        }
    }

    init() {
        // Guardar el HTML original de los textos para poder restaurarlos
        this.saveOriginalTexts();
        
        // Event listeners
        this.searchInput.addEventListener('input', (e) => this.handleInput(e));
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.clearFilter();
                this.searchInput.blur();
            }
        });
    }

    saveOriginalTexts() {
        // Guardar el texto original de cada item
        this.menuContainer.querySelectorAll('.text_menu_option').forEach(el => {
            el.dataset.originalText = el.textContent;
        });
    }

    handleInput(e) {
        const query = e.target.value.trim().toLowerCase();
        
        clearTimeout(this.debounceTimer);
        
        if (query.length === 0) {
            this.clearFilter();
            return;
        }
        
        this.debounceTimer = setTimeout(() => {
            this.filterMenu(query);
        }, 150);
    }

    async filterMenu(query) {
        this.isFiltering = true;
        
        try {
            // Buscar en la base de datos
            const response = await fetch(`/api/menu/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                this.applyFilter(data.data, query);
            }
        } catch (error) {
            console.error('[MenuFilter] Error:', error);
            // Fallback: filtrar localmente por texto visible
            this.filterLocally(query);
        }
    }

    applyFilter(matchingItems, query) {
        // Si no hay coincidencias, mostrar todos los items (no ocultar todo el menú)
        // PERO mantener el texto en el input
        if (matchingItems.length === 0) {
            this.clearFilter(false); // No limpiar el input
            return;
        }

        // Separar items visibles (ya en el DOM) de items ocultos (no en el DOM)
        const visibleItems = [];
        const hiddenItems = [];
        
        matchingItems.forEach(item => {
            const existsInDOM = this.menuContainer.querySelector(`[data-menu-item-id="${item.id}"]`);
            if (existsInDOM) {
                visibleItems.push(item);
            } else {
                hiddenItems.push(item);
            }
        });

        // Crear set de IDs que coinciden (incluyendo sus padres)
        const visibleIds = new Set();
        
        // Procesar items visibles
        visibleItems.forEach(item => {
            visibleIds.add(item.id);
            if (item.parent_id) {
                this.addAncestorsToSet(item.id, visibleIds);
            }
        });

        // Agregar items ocultos temporalmente al menú con su jerarquía completa
        if (hiddenItems.length > 0) {
            this.addHiddenItemsToMenu(hiddenItems);
            // Agregar los IDs de los items ocultos y sus ancestros
            hiddenItems.forEach(item => {
                visibleIds.add(item.id);
                if (item.breadcrumb && item.breadcrumb.length > 1) {
                    // Agregar todos los ancestros del breadcrumb
                    item.breadcrumb.slice(0, -1).forEach(ancestor => {
                        // Buscar el ID del ancestro en window.lego.menu o crear temporalmente
                        const ancestorId = this.findOrCreateAncestor(ancestor, item);
                        if (ancestorId) {
                            visibleIds.add(ancestorId);
                        }
                    });
                }
            });
        }

        // Aplicar visibilidad a cada item del menú
        this.menuContainer.querySelectorAll('[data-menu-item-id]').forEach(menuItem => {
            const itemId = menuItem.getAttribute('data-menu-item-id');
            const textEl = menuItem.querySelector(':scope > .menu_item_clickable .text_menu_option, :scope > .menu_item_openable_content .text_menu_option, :scope > .custom-button .text_menu_option');
            
            if (visibleIds.has(itemId)) {
                // Mostrar item
                menuItem.style.display = '';
                menuItem.classList.add('menu-filter-visible');
                menuItem.classList.remove('menu-filter-hidden');
                
                // Resaltar texto si coincide directamente
                const isDirectMatch = matchingItems.some(m => m.id === itemId);
                if (textEl && isDirectMatch) {
                    this.highlightText(textEl, query);
                } else if (textEl) {
                    this.restoreText(textEl);
                }
                
                // Expandir submenú si tiene hijos visibles
                const submenu = menuItem.querySelector(':scope > .custom-submenu');
                if (submenu) {
                    submenu.classList.add('menu-filter-expanded');
                    submenu.style.display = 'block';
                }
            } else {
                // Ocultar item
                menuItem.style.display = 'none';
                menuItem.classList.add('menu-filter-hidden');
                menuItem.classList.remove('menu-filter-visible');
            }
        });

        // Agregar clase al contenedor para indicar modo filtrado
        this.menuContainer.classList.add('menu-filtering');
    }

    /**
     * Agregar items ocultos temporalmente al menú con su jerarquía completa
     * Los items aparecen como si siempre hubieran estado en el menú
     */
    addHiddenItemsToMenu(hiddenItems) {
        // Crear contenedor temporal para items ocultos si no existe
        let tempContainer = document.getElementById('menu-search-temp-items');
        if (!tempContainer) {
            tempContainer = document.createElement('div');
            tempContainer.id = 'menu-search-temp-items';
            tempContainer.className = 'menu-search-temp-container';
            this.menuContainer.appendChild(tempContainer);
        } else {
            tempContainer.innerHTML = '';
        }

        // Agrupar items por su breadcrumb para construir jerarquías compartidas
        const groupsByPath = {};
        
        hiddenItems.forEach(item => {
            const breadcrumb = item.breadcrumb || [];
            if (breadcrumb.length === 0) {
                // Item sin breadcrumb, agregarlo directamente
                breadcrumb.push(item.label);
            }
            
            // Construir path único para agrupar
            const pathKey = breadcrumb.join('|');
            if (!groupsByPath[pathKey]) {
                groupsByPath[pathKey] = {
                    breadcrumb: breadcrumb,
                    items: []
                };
            }
            groupsByPath[pathKey].items.push(item);
        });

        // Renderizar cada grupo con su jerarquía
        Object.values(groupsByPath).forEach(group => {
            const breadcrumb = group.breadcrumb;
            const items = group.items;
            
            // Construir la jerarquía desde la raíz
            let currentContainer = tempContainer;
            
            // Crear contenedores para cada nivel del breadcrumb (excepto el último que es el item)
            for (let i = 0; i < breadcrumb.length - 1; i++) {
                const ancestorLabel = breadcrumb[i];
                const ancestorId = `temp-ancestor-${i}-${ancestorLabel.replace(/\s+/g, '-').toLowerCase()}`;
                
                let ancestorContainer = currentContainer.querySelector(`[data-temp-group-id="${ancestorId}"]`);
                if (!ancestorContainer) {
                    ancestorContainer = document.createElement('div');
                    ancestorContainer.className = 'custom-menu-section menu-search-temp-group';
                    ancestorContainer.setAttribute('data-temp-group-id', ancestorId);
                    ancestorContainer.setAttribute('data-menu-item-id', ancestorId);
                    ancestorContainer.innerHTML = `
                        <div class="custom-menu-title level-${i}">
                            <ion-icon name="folder-outline" class="icon_menu icon_menu_parent"></ion-icon>
                            <p class="text_menu_option">${ancestorLabel}</p>
                        </div>
                        <div class="custom-submenu section-level-${i + 1}" style="display: block;">
                        </div>
                    `;
                    currentContainer.appendChild(ancestorContainer);
                }
                currentContainer = ancestorContainer.querySelector('.custom-submenu');
            }

            // Agregar los items finales en el último nivel
            items.forEach(item => {
                const label = item.label;
                const route = item.route || '#';
                const icon = item.icon || 'ellipse-outline';
                const level = breadcrumb.length - 1;
                
                const itemHtml = `
                    <div class="custom-menu-section menu_item_openable menu-search-temp-item" 
                         moduleId="${item.id}" 
                         moduleUrl="${route}" 
                         data-menu-item-id="${item.id}"
                         data-temp-item="true">
                        <button class="menu-close-button" title="Cerrar">
                            <ion-icon name="close-outline"></ion-icon>
                        </button>
                        <button class="custom-button level-${level}">
                            <ion-icon name="${icon}" class="icon_menu"></ion-icon>
                            <p class="text_menu_option">${label}</p>
                            <div class="menu-state-indicator"></div>
                        </button>
                    </div>
                `;
                currentContainer.insertAdjacentHTML('beforeend', itemHtml);
            });
        });

        // Agregar event listeners a los items temporales usando event delegation
        // (más eficiente que agregar listeners individuales)
        if (!tempContainer.dataset.listenersAdded) {
            tempContainer.addEventListener('click', async function(e) {
                const menuItem = e.target.closest('.menu_item_openable');
                if (!menuItem || e.target.closest('.menu-close-button')) return;
                
                const id = menuItem.getAttribute('moduleId') || menuItem.getAttribute('data-menu-item-id');
                const url = menuItem.getAttribute('moduleUrl') || '#';
                const name = menuItem.querySelector('.text_menu_option')?.textContent || id;

                if (window.moduleStore && window.moduleStore.getActiveModule() !== id) {
                    // Obtener el parent_id correcto desde la base de datos
                    let parentMenuId = null;
                    try {
                        const hierarchyResponse = await fetch(`/api/menu/item-hierarchy?id=${encodeURIComponent(id)}`);
                        const hierarchyResult = await hierarchyResponse.json();
                        if (hierarchyResult.success && hierarchyResult.data && hierarchyResult.data.item) {
                            parentMenuId = hierarchyResult.data.item.parent_id || null;
                            console.log(`[MenuFilterManager] parent_id obtenido desde BD para ${id}:`, parentMenuId);
                        }
                    } catch (error) {
                        console.error('[MenuFilterManager] Error obteniendo parent_id:', error);
                    }

                    if (window.legoWindowManager && window.legoWindowManager.openModuleWithMenu) {
                        window.legoWindowManager.openModuleWithMenu({
                            moduleId: id,
                            parentMenuId: parentMenuId,
                            label: name,
                            url: url,
                            icon: 'settings-outline'
                        });
                    } else if (window.openModule) {
                        window.openModule(id, url, name, { url, name });
                    }
                }
            });
            tempContainer.dataset.listenersAdded = 'true';
        }
    }

    /**
     * Buscar o crear un ancestro temporal
     */
    findOrCreateAncestor(ancestor, item) {
        // Buscar si ya existe en el DOM
        const existing = this.menuContainer.querySelector(`[data-menu-item-id="${ancestor.id || ancestor}"]`);
        if (existing) {
            return ancestor.id || ancestor;
        }
        // Si no existe, se creará en addHiddenItemsToMenu
        return ancestor.id || `temp-${ancestor}`;
    }

    addAncestorsToSet(itemId, visibleIds) {
        // Buscar en window.lego.menu la estructura para obtener padres
        const findParent = (items, targetId) => {
            for (const item of items) {
                if (item.id === targetId) {
                    return null; // Encontrado, no tiene padre en este nivel
                }
                if (item.children) {
                    for (const child of item.children) {
                        if (child.id === targetId) {
                            return item.id;
                        }
                        // Buscar en subhijos
                        const found = findParent([child], targetId);
                        if (found !== undefined) {
                            visibleIds.add(item.id);
                            return found;
                        }
                    }
                }
            }
            return undefined;
        };

        // Buscar recursivamente hasta la raíz
        let currentId = itemId;
        let parentId = findParent(window.lego?.menu || [], currentId);
        
        while (parentId) {
            visibleIds.add(parentId);
            currentId = parentId;
            parentId = findParent(window.lego?.menu || [], currentId);
        }
    }

    filterLocally(query) {
        // Fallback: filtrar por texto visible en el DOM
        const matchingIds = new Set();
        
        this.menuContainer.querySelectorAll('[data-menu-item-id]').forEach(menuItem => {
            const textEl = menuItem.querySelector('.text_menu_option');
            if (textEl) {
                const text = (textEl.dataset.originalText || textEl.textContent).toLowerCase();
                if (text.includes(query)) {
                    matchingIds.add(menuItem.getAttribute('data-menu-item-id'));
                    // Agregar padres
                    let parent = menuItem.parentElement?.closest('[data-menu-item-id]');
                    while (parent) {
                        matchingIds.add(parent.getAttribute('data-menu-item-id'));
                        parent = parent.parentElement?.closest('[data-menu-item-id]');
                    }
                }
            }
        });

        // Aplicar filtro
        this.menuContainer.querySelectorAll('[data-menu-item-id]').forEach(menuItem => {
            const itemId = menuItem.getAttribute('data-menu-item-id');
            const textEl = menuItem.querySelector(':scope > .menu_item_clickable .text_menu_option, :scope > .menu_item_openable_content .text_menu_option');
            
            if (matchingIds.has(itemId)) {
                menuItem.style.display = '';
                menuItem.classList.add('menu-filter-visible');
                
                // Resaltar si coincide directamente
                if (textEl) {
                    const text = (textEl.dataset.originalText || textEl.textContent).toLowerCase();
                    if (text.includes(query)) {
                        this.highlightText(textEl, query);
                    } else {
                        this.restoreText(textEl);
                    }
                }
                
                // Expandir submenú
                const submenu = menuItem.querySelector(':scope > .custom-submenu');
                if (submenu) {
                    submenu.classList.add('menu-filter-expanded');
                }
            } else {
                menuItem.style.display = 'none';
                menuItem.classList.add('menu-filter-hidden');
            }
        });

        this.menuContainer.classList.add('menu-filtering');
    }

    highlightText(element, query) {
        const originalText = element.dataset.originalText || element.textContent;
        const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
        element.innerHTML = originalText.replace(regex, '<mark class="menu-highlight">$1</mark>');
    }

    restoreText(element) {
        if (element.dataset.originalText) {
            element.textContent = element.dataset.originalText;
        }
    }

    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    clearFilter(clearInput = false) {
        this.isFiltering = false;
        if (this.searchInput && clearInput) {
            this.searchInput.value = '';
        }
        
        // Eliminar items temporales de búsqueda (items ocultos agregados)
        const tempContainer = document.getElementById('menu-search-temp-items');
        if (tempContainer) {
            tempContainer.remove();
        }
        
        // Restaurar todos los items
        this.menuContainer.querySelectorAll('[data-menu-item-id]').forEach(menuItem => {
            menuItem.style.display = '';
            menuItem.classList.remove('menu-filter-visible', 'menu-filter-hidden');
            
            // Restaurar texto
            const textEl = menuItem.querySelector('.text_menu_option');
            if (textEl) {
                this.restoreText(textEl);
            }
            
            // Colapsar submenús expandidos por filtro
            const submenu = menuItem.querySelector(':scope > .custom-submenu');
            if (submenu) {
                submenu.classList.remove('menu-filter-expanded');
            }
        });

        this.menuContainer.classList.remove('menu-filtering');
    }
}

// Initialize MenuFilterManager
if (typeof window.menuFilterManager === 'undefined') {
    window.menuFilterManager = new MenuFilterManager();
}

// ═══════════════════════════════════════════════════════════════════
// MENU RELOAD FUNCTIONALITY
// ═══════════════════════════════════════════════════════════════════

/**
 * MenuReloadManager
 * 
 * Gestiona la recarga del menú cuando se actualiza la configuración
 */
class MenuReloadManager {
    constructor() {
        this.menuContainer = document.querySelector('.custom-menu');
        this.setupEventListeners();
    }

    /**
     * Configurar listeners de eventos
     */
    setupEventListeners() {
        // Escuchar evento de actualización del menú
        if (window.lego && window.lego.events) {
            window.lego.events.on('menu:updated', () => {
                this.reloadMenu();
            });
        }

        // También escuchar evento nativo del navegador
        window.addEventListener('lego:menu:updated', () => {
            this.reloadMenu();
        });
    }

    /**
     * Recargar el menú desde el servidor
     */
    async reloadMenu() {
        try {
            // Obtener nueva estructura del menú
            const response = await fetch('/api/menu/structure');
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.msj || 'Error al obtener estructura del menú');
            }

            const menuStructure = result.data || [];

            // Actualizar window.lego.menu
            if (window.lego) {
                window.lego.menu = menuStructure;
            }

            // Re-renderizar el menú en el DOM
            this.renderMenu(menuStructure);
        } catch (error) {
            console.error('[MenuReload] Error al recargar menú:', error);
        }
    }

    /**
     * Renderizar el menú en el DOM
     */
    renderMenu(menuStructure) {
        if (!this.menuContainer) {
            console.warn('[MenuReload] No se encontró el contenedor del menú');
            return;
        }

        // Limpiar contenido actual
        this.menuContainer.innerHTML = '';

        // Renderizar cada item del menú
        menuStructure.forEach(item => {
            const html = this.renderMenuItem(item, 0);
            this.menuContainer.insertAdjacentHTML('beforeend', html);
        });

        // Re-inicializar el filtro de menú si existe
        if (window.menuFilterManager) {
            window.menuFilterManager.saveOriginalTexts();
        }

        // Re-sincronizar estados del menú
        if (window.menuStateManager) {
            window.menuStateManager.syncWithModuleStore();
        }
        
        // Restaurar estado de expansión guardado (con delay para asegurar que el DOM esté listo)
        if (window.MenuExpansionManager) {
            setTimeout(() => {
                window.MenuExpansionManager.restoreExpandedState();
            }, 100);
        }
    }

    /**
     * Renderizar un item del menú (recursivo)
     */
    renderMenuItem(item, level) {
        const id = this.escapeHtml(item.id);
        const name = this.escapeHtml(item.name);
        const iconName = item.iconName || 'document-text-outline';
        const url = item.url || '#';
        const hasChildren = item.childs && item.childs.length > 0;
        const levelAux = level;

        if (!hasChildren) {
            // Item simple con link
            return `
                <div class="custom-menu-section menu_item_openable" moduleId="${id}" moduleUrl="${url}" data-menu-item-id="${id}">
                    <button class="menu-close-button" title="Cerrar">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                    <button class="custom-button level-${levelAux}">
                        <ion-icon name="${iconName}" class="icon_menu"></ion-icon>
                        <p class="text_menu_option">${name}</p>
                        <div class="menu-state-indicator"></div>
                    </button>
                </div>
            `;
        } else {
            // Item con submenú
            let childrenHtml = '';
            item.childs.forEach(child => {
                childrenHtml += this.renderMenuItem(child, level + 1);
            });

            return `
                <div class="custom-menu-section" data-menu-item-id="${id}">
                    <div class="custom-menu-title level-${levelAux}" onclick="toggleSubMenu(this)">
                        <ion-icon name="${iconName}" class="icon_menu icon_menu_parent"></ion-icon>
                        <p class="text_menu_option">${name}</p>
                        <ion-icon name="chevron-forward-outline" class="icon_menu icon_menu_chevron"></ion-icon>
                    </div>
                    <div class="custom-submenu section-level-${level + 1}">
                        ${childrenHtml}
                    </div>
                </div>
            `;
        }
    }

    /**
     * Escapar HTML para prevenir XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize MenuReloadManager (después de que el DOM esté listo)
function initMenuReloadManager() {
    if (typeof window.menuReloadManager === 'undefined') {
        // Esperar a que el contenedor del menú esté disponible
        const checkMenuContainer = setInterval(() => {
            const menuContainer = document.querySelector('.custom-menu');
            if (menuContainer) {
                clearInterval(checkMenuContainer);
                window.menuReloadManager = new MenuReloadManager();
            }
        }, 100);

        // Timeout de seguridad (5 segundos)
        setTimeout(() => {
            clearInterval(checkMenuContainer);
            if (typeof window.menuReloadManager === 'undefined') {
                console.warn('[MenuReload] No se pudo inicializar MenuReloadManager: contenedor no encontrado');
            }
        }, 5000);
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initMenuReloadManager();
        // Restaurar estado de expansión después de que el menú se renderice
        // Usar múltiples intentos para asegurar que el menú esté completamente renderizado
        const restoreMenuState = () => {
            if (window.MenuExpansionManager) {
                window.MenuExpansionManager.restoreExpandedState();
            }
        };
        
        // Intentar varias veces con delays incrementales
        setTimeout(restoreMenuState, 300);
        setTimeout(restoreMenuState, 600);
        setTimeout(restoreMenuState, 1000);
    });
} else {
    // DOM ya está listo
    initMenuReloadManager();
    // Restaurar estado de expansión después de que el menú se renderice
    const restoreMenuState = () => {
        if (window.MenuExpansionManager) {
            window.MenuExpansionManager.restoreExpandedState();
        }
    };
    
    // Intentar varias veces con delays incrementales
    setTimeout(restoreMenuState, 300);
    setTimeout(restoreMenuState, 600);
    setTimeout(restoreMenuState, 1000);
}
