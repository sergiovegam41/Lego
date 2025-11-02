
// export activeMenus;
// console.log("aAaAaaa")
class ModuleStore {
        
    constructor() {
    this.modules = {};
    this.activeModule = null;
    }
    _openModule(id, component) {
    if (!this.modules[id]) {
        this.modules[id] = { component, isActive: false };
    }
    Object.keys(this.modules).forEach(moduleId => {
        this.modules[moduleId].isActive = moduleId === id;
    });
    this.activeModule = id;

    // Sync with menu state manager
    if (window.menuStateManager) {
        window.menuStateManager.syncWithModuleStore();
    }

    // Update breadcrumb
    if (window.legoWindowManager) {
        window.legoWindowManager.updateBreadcrumbFromActiveModule();
    }
    }

    closeModule(id) {
    if (this.modules[id]) {
        delete this.modules[id];
    }
    if (this.activeModule === id) {
        const remainingModules = Object.keys(this.modules);
        this.activeModule = remainingModules.length > 0 ? remainingModules[0] : null;
        if (this.activeModule) {
        this.modules[this.activeModule].isActive = true;

        // Mostrar el nuevo módulo activo
        document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
        const newActiveContainer = document.getElementById(`module-${this.activeModule}`);
        if (newActiveContainer) {
            newActiveContainer.classList.add('active');
        }
        }
    }

    // Sync with menu state manager
    if (window.menuStateManager) {
        window.menuStateManager.syncWithModuleStore();
    }

    // Update breadcrumb
    if (window.legoWindowManager) {
        window.legoWindowManager.updateBreadcrumbFromActiveModule();
    }
    }

    getActiveModule() {
        return this.activeModule;
    }

    getModules() {
        return this.modules;
    }
}

const moduleStore = new ModuleStore();

// Expose moduleStore globally for menu state manager and other components
window.moduleStore = moduleStore;

async function renderModule(id, url, content) {
    let container = document.getElementById(`module-${id}`);
    if (!container) {
        let dataResp = await fetch(url).then(res => res.text());

        container = document.createElement('div');
        container.id = `module-${id}`;
        container.className = 'module-container';


        // Usar insertAdjacentHTML en lugar de innerHTML para ejecutar scripts
        container.insertAdjacentHTML('beforeend', dataResp);

        // Extraer y ejecutar scripts manualmente (porque innerHTML no ejecuta scripts)
        const scripts = container.querySelectorAll('script');

        scripts.forEach((oldScript, index) => {
            const newScript = document.createElement('script');

            // Copiar atributos
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });

            // Copiar contenido
            newScript.textContent = oldScript.textContent;

            // Reemplazar el script viejo con el nuevo (esto hace que se ejecute)
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        document.getElementById('home-page').appendChild(container);
    }
    document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
    container.classList.add('active');
}

export function _closeModule(id) {
    const container = document.getElementById(`module-${id}`);
    if (container) {
    container.remove();
    }
    moduleStore.closeModule(id);
    updateMenu();
                     
    // Abrir el siguiente módulo en la lista si existe
    const nextActiveModule = moduleStore.getActiveModule();
    if (nextActiveModule) {
    renderModule(nextActiveModule, `Contenido dinámico del módulo ${nextActiveModule}`);
    }
}
export function _openModule(id, url) {

    moduleStore._openModule(id, {});
    renderModule(id, url, `Contenido dinámico del módulo ${id}`);
    updateMenu();
}

function updateMenu() {
    const activeModule = moduleStore.getActiveModule();
    const modules = moduleStore.getModules();

    document.querySelectorAll('.menu-item').forEach(item => {
    const id = item.dataset.moduleId;
    if (id === activeModule) {
        item.classList.add('active');
        item.classList.remove('inactive');
    } else if (modules[id]) {
        item.classList.add('inactive');
        item.classList.remove('active');
    } else {
        item.classList.remove('active', 'inactive');
    }
    });
}




export function generateMenuLinks(){


    document.querySelectorAll('.menu_item_openable').forEach(item => {

        item.addEventListener('click', () => {

            const id = item.dataset.moduleId  || item.getAttribute("moduleId");
            const url = item.dataset.moduleUrl  || item.getAttribute("moduleUrl");
            const name = item.querySelector('.text_menu_option')?.textContent || 'Sin nombre';

            if (moduleStore.getActiveModule() !== id) {
                openModule(id, url, name, { url, name });
            } else {
                // Already active - just make sure it's visible
                const container = document.getElementById(`module-${id}`);
                if (container) {
                    document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
                    container.classList.add('active');
                }
            }

        });

    });

}

// Unified openModule function with proper component info
function openModule(id, url, name, component) {
    moduleStore._openModule(id, component);
    renderModule(id, url, `Contenido dinámico del módulo ${id}`);
    updateMenu();
}

// Expose openModule globally for onclick handlers
window.openModule = openModule;


/**
 * LegoWindowManager - Global API for window management
 * 
 * Provides methods for interacting with the module system:
 * - reloadActive(): Reload the currently active module
 * - closeModule(id): Close a specific module
 * - updateBreadcrumb(items): Update breadcrumb navigation
 */
if (typeof window.legoWindowManager === 'undefined') {
    window.legoWindowManager = {
        /**
         * Reload the currently active module
         */
        reloadActive: function() {
            if (!window.moduleStore || !window.moduleStore.activeModule) {
                console.warn('No active module to reload');
                return;
            }

            const activeId = window.moduleStore.activeModule;
            const activeModule = window.moduleStore.modules[activeId];

            if (!activeModule) {
                console.warn('Active module not found in store');
                return;
            }

            // Remove the module container from DOM
            const container = document.getElementById(`module-${activeId}`);
            if (container) {
                container.remove();
            }

            // Remove from store
            delete window.moduleStore.modules[activeId];

            // Re-open the module (will fetch fresh content)
            const url = activeModule.component.url;
            const name = activeModule.component.name;

            openModule(activeId, url, name, activeModule.component);

            console.log('Module reloaded:', activeId);
        },

        /**
         * Close a specific module
         * @param {string} id - Module ID to close
         */
        closeModule: function(id) {
            if (!window.moduleStore) {
                console.warn('ModuleStore not available');
                return;
            }

            // Remove container from DOM
            const container = document.getElementById(`module-${id}`);
            if (container) {
                container.remove();
            }

            // Close via ModuleStore
            window.moduleStore.closeModule(id);

            // Remove dynamic menu item if it exists
            this.removeDynamicMenuItem(id);

            // Sync menu state
            if (window.menuStateManager) {
                window.menuStateManager.syncWithModuleStore();
            }

            // Update breadcrumb
            this.updateBreadcrumbFromActiveModule();

            console.log('Module closed:', id);
        },

        /**
         * Update breadcrumb navigation
         * @param {Array} items - Array of {label, href} objects
         */
        updateBreadcrumb: function(items) {
            if (window.legoBreadcrumb) {
                window.legoBreadcrumb.update(items);
            }
        },

        /**
         * Close the currently active module
         */
        closeCurrentWindow: function() {
            if (!window.moduleStore || !window.moduleStore.activeModule) {
                console.warn('No active module to close');
                return;
            }

            const activeId = window.moduleStore.activeModule;
            this.closeModule(activeId);
            console.log('Current window closed:', activeId);
        },

        /**
         * Add a temporary menu item that appears as a sibling of the parent menu item
         * @param {Object} config - Configuration object
         * @param {string} config.moduleId - Unique ID for the module/window
         * @param {string} config.parentMenuId - ID of the parent menu item to add this as a sibling
         * @param {string} config.label - Display label for the menu item
         * @param {string} config.icon - Icon name (optional, defaults to parent's icon or 'document-outline')
         * @param {string} config.url - URL to load when clicked
         */
        addDynamicMenuItem: function(config) {
            const { moduleId, parentMenuId, label, icon, url } = config;

            console.log('[WindowManager] addDynamicMenuItem called with:', config);

            if (!moduleId || !parentMenuId || !label) {
                console.error('[WindowManager] addDynamicMenuItem: moduleId, parentMenuId, and label are required');
                return;
            }

            // Find the parent menu item
            console.log(`[WindowManager] Buscando parent menu item con selector: [data-menu-item-id="${parentMenuId}"]`);
            const parentMenuItem = document.querySelector(`[data-menu-item-id="${parentMenuId}"]`);
            console.log('[WindowManager] Parent menu item encontrado:', parentMenuItem);

            if (!parentMenuItem) {
                console.warn(`[WindowManager] Parent menu item not found: ${parentMenuId}`);
                // DEBUG: Listar todos los data-menu-item-id disponibles
                const allMenuItems = document.querySelectorAll('[data-menu-item-id]');
                console.log('[WindowManager] DEBUG - IDs de menú disponibles:',
                    Array.from(allMenuItems).map(el => el.getAttribute('data-menu-item-id')));
                return;
            }

            // Check if dynamic item already exists
            const existingItem = document.querySelector(`[data-menu-item-id="${moduleId}"]`);
            if (existingItem) {
                console.log('Dynamic menu item already exists:', moduleId);
                return;
            }

            // Get parent's submenu or create insertion point
            let submenu = parentMenuItem.parentElement.querySelector('.custom-submenu');
            if (!submenu) {
                // If parent doesn't have a submenu, create one
                submenu = document.createElement('div');
                submenu.className = 'custom-submenu';
                submenu.style.display = 'block'; // Ensure it's visible
                parentMenuItem.parentElement.appendChild(submenu);
            }

            // Determine icon to use
            const itemIcon = icon || parentMenuItem.querySelector('ion-icon')?.getAttribute('name') || 'document-outline';

            // Create the dynamic menu item HTML matching MenuItemComponent.php structure
            const menuItemHTML = `
                <div class="custom-menu-section menu_item_openable dynamic-menu-item"
                    moduleId="${moduleId}"
                    moduleUrl="${url}"
                    data-menu-item-id="${moduleId}"
                    data-module-id="${moduleId}"
                    data-module-url="${url}"
                    data-dynamic-item="true">
                    <button class="menu-close-button" title="Cerrar">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                    <button class="custom-button level-0">
                        <ion-icon name="${itemIcon}" class="icon_menu"></ion-icon>
                        <p class="text_menu_option">${label}</p>
                        <div class="menu-state-indicator"></div>
                    </button>
                </div>
            `;

            // Insert into submenu
            submenu.insertAdjacentHTML('beforeend', menuItemHTML);

            // Add click listener to the new item
            const newMenuItem = document.querySelector(`[data-menu-item-id="${moduleId}"]`);
            if (newMenuItem) {
                // Click event should be on the menu section itself (matching generateMenuLinks pattern)
                newMenuItem.addEventListener('click', (e) => {
                    // Prevent triggering if clicking close button
                    if (e.target.closest('.menu-close-button')) return;

                    const id = newMenuItem.getAttribute('moduleId');
                    const itemUrl = newMenuItem.getAttribute('moduleUrl');

                    if (window.moduleStore.getActiveModule() !== id) {
                        openModule(id, itemUrl, label, { url: itemUrl, name: label });
                    } else {
                        // Already active - just make sure it's visible
                        const container = document.getElementById(`module-${id}`);
                        if (container) {
                            document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
                            container.classList.add('active');
                        }
                    }
                });
            }

            console.log('Dynamic menu item added:', moduleId);
        },

        /**
         * Remove a dynamic menu item
         * @param {string} moduleId - Module ID of the dynamic item to remove
         */
        removeDynamicMenuItem: function(moduleId) {
            const menuItem = document.querySelector(`[data-menu-item-id="${moduleId}"][data-dynamic-item="true"]`);
            if (menuItem) {
                // Check if parent submenu will be empty after removal
                const submenu = menuItem.parentElement;
                const siblingDynamicItems = submenu.querySelectorAll('.dynamic-menu-item');

                menuItem.remove();

                // If submenu is now empty and it was created for dynamic items, remove it
                if (siblingDynamicItems.length === 1 && submenu.children.length === 0) {
                    submenu.remove();
                }

                console.log('Dynamic menu item removed:', moduleId);
            }
        },

        /**
         * Open a module with automatic dynamic menu item creation
         * @param {Object} config - Configuration object
         * @param {string} config.moduleId - Unique ID for the module
         * @param {string} config.parentMenuId - ID of the parent menu item
         * @param {string} config.label - Display label
         * @param {string} config.url - URL to load
         * @param {string} config.icon - Icon name (optional)
         */
        openModuleWithMenu: function(config) {
            const { moduleId, parentMenuId, label, url, icon } = config;

            // First, add the dynamic menu item
            this.addDynamicMenuItem({
                moduleId,
                parentMenuId,
                label,
                icon,
                url
            });

            // Then open the module
            openModule(moduleId, url, label, { url, name: label });
        },

        /**
         * Update breadcrumb based on currently active module
         */
        updateBreadcrumbFromActiveModule: function() {
            if (!window.moduleStore || !window.moduleStore.activeModule) {
                // No active module - clear breadcrumb
                this.updateBreadcrumb([]);
                return;
            }

            const activeId = window.moduleStore.activeModule;

            // Find the menu item in the DOM
            const menuItem = document.querySelector(`[data-menu-item-id="${activeId}"]`);

            if (!menuItem) {
                this.updateBreadcrumb([]);
                return;
            }

            // Build breadcrumb by traversing up the menu hierarchy
            const breadcrumbItems = [];

            // Add the current menu item (final level)
            const currentText = menuItem.querySelector('.text_menu_option');
            if (currentText) {
                breadcrumbItems.push({ label: currentText.textContent.trim(), href: '#' });
            }

            // Traverse up to find parent sections
            let currentElement = menuItem.parentElement?.closest('.custom-submenu')?.previousElementSibling;

            while (currentElement) {
                // If we found a parent menu title
                if (currentElement.classList.contains('custom-menu-title')) {
                    const parentText = currentElement.querySelector('.text_menu_option');
                    if (parentText) {
                        const label = parentText.textContent.trim();
                        breadcrumbItems.unshift({ label, href: '#' });
                    }
                    // Continue up the tree - move to parent submenu's parent title
                    currentElement = currentElement.parentElement?.parentElement?.closest('.custom-submenu')?.previousElementSibling;
                } else {
                    break;
                }
            }

            // If no parent was found but we're in a submenu, look for parent section
            if (breadcrumbItems.length === 1) {
                const submenu = menuItem.parentElement;
                if (submenu && submenu.classList.contains('custom-submenu')) {
                    // Find the parent custom-menu-section that contains this submenu
                    const parentSection = submenu.parentElement;
                    if (parentSection && parentSection.classList.contains('custom-menu-section')) {
                        // Look for the custom-menu-title sibling
                        const menuTitle = parentSection.querySelector('.custom-menu-title');
                        if (menuTitle) {
                            const parentText = menuTitle.querySelector('.text_menu_option');
                            if (parentText) {
                                breadcrumbItems.unshift({ label: parentText.textContent.trim(), href: '#' });
                            }
                        }
                    }
                }
            }

            this.updateBreadcrumb(breadcrumbItems);
        }
    };
}
