
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
