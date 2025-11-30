// ═══════════════════════════════════════════════════════════════════
// INICIALIZAR ESTRUCTURA DEL MENÚ EN WINDOW.LEGO
// ═══════════════════════════════════════════════════════════════════

// Este script recibe la estructura del menú desde PHP via ScriptCoreDTO
// y la almacena en window.lego.menu como fuente de verdad única

// El argumento 'arg' es pasado automáticamente por loadModulesWithArguments
// y contiene { menuStructure: [...] } donde menuStructure ya es un array de objetos
if (typeof arg !== 'undefined' && arg.menuStructure) {
    // Inicializar window.lego si no existe
    window.lego = window.lego || {};

    // menuStructure ya viene como array/objeto desde PHP (json_encode lo convirtió automáticamente)
    window.lego.menu = arg.menuStructure;

    console.log('[MenuComponent] Estructura del menú cargada en window.lego.menu:', window.lego.menu);
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

/**
 * MenuStateManager
 * 
 * Manages visual state of menu items based on window state
 * States: closed, open (with dot), active (blue background)
 */
class MenuStateManager {
    constructor() {
        this.setupEventListeners();
    }

    /**
     * Update menu item visual state
     * @param {string} moduleId - Module ID
     * @param {string} state - 'active', 'open', or 'closed'
     */
    setState(moduleId, state) {
        const menuItem = document.querySelector(`[data-menu-item-id="${moduleId}"]`);
        if (!menuItem) return;

        // Remove all states
        menuItem.classList.remove('menu-state--active', 'menu-state--open');

        // Add new state
        if (state === 'active') {
            menuItem.classList.add('menu-state--active');
        } else if (state === 'open') {
            menuItem.classList.add('menu-state--open');
        }
        // 'closed' state means no classes
    }

    /**
     * Sync menu states with ModuleStore
     */
    syncWithModuleStore() {
        if (!window.moduleStore) return;

        const modules = window.moduleStore.modules;
        const activeModuleId = window.moduleStore.activeModule;

        // Get all menu items
        const allMenuItems = document.querySelectorAll('[data-menu-item-id]');

        allMenuItems.forEach(menuItem => {
            const moduleId = menuItem.getAttribute('data-menu-item-id');

            if (moduleId === activeModuleId) {
                this.setState(moduleId, 'active');
            } else if (modules[moduleId]) {
                this.setState(moduleId, 'open');
            } else {
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
        
        console.log('[MenuFilter] Filtro de menú inicializado');
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
        // Crear set de IDs que coinciden (incluyendo sus padres)
        const visibleIds = new Set();
        
        matchingItems.forEach(item => {
            visibleIds.add(item.id);
            // Agregar todos los ancestros (breadcrumb incluye el item mismo)
            if (item.parent_id) {
                // Recorrer el breadcrumb para obtener padres
                this.addAncestorsToSet(item.id, visibleIds);
            }
        });

        // Aplicar visibilidad a cada item del menú
        this.menuContainer.querySelectorAll('[data-menu-item-id]').forEach(menuItem => {
            const itemId = menuItem.getAttribute('data-menu-item-id');
            const textEl = menuItem.querySelector(':scope > .menu_item_clickable .text_menu_option, :scope > .menu_item_openable_content .text_menu_option');
            
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

    clearFilter() {
        this.isFiltering = false;
        this.searchInput.value = '';
        
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
