
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

            this.updateBreadcrumb(breadcrumbItems);
        }
    };
}
