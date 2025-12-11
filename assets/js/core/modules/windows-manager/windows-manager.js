
// export activeMenus;
class ModuleStore {
        
    constructor() {
    this.modules = {};
    this.activeModule = null;
    }

    _openModule(id, component, options = {}) {
        const { sourceModuleId = null } = options;
        
    if (!this.modules[id]) {
            // Nuevo módulo: inicializar con params vacíos y origen
            this.modules[id] = { 
                component, 
                isActive: false,
                params: {},  // Parámetros persistentes del módulo
                sourceModuleId: sourceModuleId  // De dónde vino este módulo
            };
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
    
    // Dispatch event for module activation
    window.dispatchEvent(new CustomEvent('lego:module:activated', {
        detail: { moduleId: id, params: this.modules[id].params }
    }));
    }

    closeModule(id, options = {}) {
        const { returnTo = null } = options;
        
        // Obtener el sourceModuleId antes de eliminar
        const sourceModuleId = this.modules[id]?.sourceModuleId;
        
    if (this.modules[id]) {
            delete this.modules[id]; // Al cerrar, se eliminan los params
    }
        
    if (this.activeModule === id) {
            // Prioridad: 1. returnTo explícito, 2. sourceModuleId, 3. primer módulo disponible
            let nextModule = returnTo || sourceModuleId;
            
            // Verificar que el módulo destino existe
            if (nextModule && !this.modules[nextModule]) {
                nextModule = null;
            }
            
            // Fallback: primer módulo disponible
            if (!nextModule) {
        const remainingModules = Object.keys(this.modules);
                nextModule = remainingModules.length > 0 ? remainingModules[0] : null;
            }
            
            this.activeModule = nextModule;
            
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
    
    // Dispatch event for module closed
    window.dispatchEvent(new CustomEvent('lego:module:closed', {
        detail: { moduleId: id, activeModuleId: this.activeModule }
    }));
    }

    getActiveModule() {
        return this.activeModule;
    }

    getModules() {
        return this.modules;
    }

    // ═══════════════════════════════════════════════════════════════════
    // GESTIÓN DE PARÁMETROS PERSISTENTES POR MÓDULO
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Establecer un parámetro para un módulo
     * @param {string} moduleId - ID del módulo
     * @param {string} key - Clave del parámetro
     * @param {*} value - Valor del parámetro
     */
    setParam(moduleId, key, value) {
        if (!this.modules[moduleId]) {
            console.warn(`[ModuleStore] Módulo no encontrado: ${moduleId}`);
            return;
        }
        this.modules[moduleId].params[key] = value;
    }

    /**
     * Obtener un parámetro de un módulo
     * @param {string} moduleId - ID del módulo
     * @param {string} key - Clave del parámetro
     * @param {*} defaultValue - Valor por defecto si no existe
     * @returns {*} Valor del parámetro
     */
    getParam(moduleId, key, defaultValue = null) {
        if (!this.modules[moduleId]) {
            return defaultValue;
        }
        return this.modules[moduleId].params[key] ?? defaultValue;
    }

    /**
     * Obtener todos los parámetros de un módulo
     * @param {string} moduleId - ID del módulo
     * @returns {Object} Objeto con todos los parámetros
     */
    getParams(moduleId) {
        if (!this.modules[moduleId]) {
            return {};
        }
        return { ...this.modules[moduleId].params };
    }

    /**
     * Eliminar un parámetro específico
     * @param {string} moduleId - ID del módulo
     * @param {string} key - Clave del parámetro a eliminar
     */
    removeParam(moduleId, key) {
        if (this.modules[moduleId] && this.modules[moduleId].params[key] !== undefined) {
            delete this.modules[moduleId].params[key];
        }
    }

    /**
     * Limpiar todos los parámetros de un módulo
     * @param {string} moduleId - ID del módulo
     */
    clearParams(moduleId) {
        if (this.modules[moduleId]) {
            this.modules[moduleId].params = {};
        }
    }
}

const moduleStore = new ModuleStore();

// Expose moduleStore globally for menu state manager and other components
window.moduleStore = moduleStore;

async function renderModule(id, url, content) {
    let container = document.getElementById(`module-${id}`);
    if (!container) {
        const response = await fetch(url);
        let dataResp = await response.text();
        
        // Detectar si la respuesta es el login (sesión expirada)
        // Verificar por: redirección exacta a /login, o contenido específico de login
        const responseUrlPath = new URL(response.url, window.location.origin).pathname;
        const isLoginPage = responseUrlPath === '/login' || 
                           responseUrlPath === '/' ||
                           dataResp.includes('id="login-form"') ||
                           dataResp.includes('class="login-container"');
        
        if (isLoginPage) {
            console.warn('[WindowManager] Sesión expirada, redirigiendo a login...');
            window.location.href = '/login';
            return;
        }

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
function openModule(id, url, name, component, options = {}) {
    // Capturar el módulo origen automáticamente si no se especifica
    const sourceModuleId = options.sourceModuleId ?? moduleStore.activeModule;
    
    moduleStore._openModule(id, component, { sourceModuleId });
    renderModule(id, url, `Contenido dinámico del módulo ${id}`);
    updateMenu();
}

// Expose openModule globally for onclick handlers
window.openModule = openModule;


/**
 * LegoWindowManager - Global API for window management
 * 
 * Provides methods for interacting with the module system:
 * - reloadActive(): Reload the currently active module (preserving params)
 * - closeModule(id, [options]): Close a specific module
 * - closeCurrentWindow([options]): Close active module (options: refresh, returnTo)
 * - updateBreadcrumb(items): Update breadcrumb navigation
 * - setParam(key, value, [moduleId]): Set a persistent parameter (default: active module)
 * - getParam(key, [default], [moduleId]): Get a persistent parameter (default: active module)
 * - getParams([moduleId]): Get all persistent parameters (default: active module)
 * - removeParam(key, [moduleId]): Remove a persistent parameter (default: active module)
 * - clearParams([moduleId]): Clear all persistent parameters (default: active module)
 * 
 * Module Navigation:
 * - When opening a module, the current active module is saved as sourceModuleId
 * - When closing, the system returns to sourceModuleId automatically
 * - Use returnTo option to override: closeCurrentWindow({ returnTo: 'module-id' })
 */
if (typeof window.legoWindowManager === 'undefined') {
    window.legoWindowManager = {
        /**
         * Reload the currently active module, preserving persistent params
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

            // IMPORTANTE: Preservar todo el estado antes de eliminar el módulo
            const preservedParams = { ...activeModule.params };
            const preservedComponent = { ...activeModule.component };
            const preservedSourceModuleId = activeModule.sourceModuleId;


            // Remove the module container from DOM
            const container = document.getElementById(`module-${activeId}`);
            if (container) {
                container.remove();
            }

            // Remove from store (esto elimina los params temporalmente)
            delete window.moduleStore.modules[activeId];

            // Re-open the module (will fetch fresh content)
            // Pasar sourceModuleId explícitamente para evitar que capture el módulo actual (que somos nosotros)
            const url = preservedComponent.url;
            const name = preservedComponent.name;

            openModule(activeId, url, name, preservedComponent, { sourceModuleId: preservedSourceModuleId });

            // Restaurar los params después de reabrir
            if (window.moduleStore.modules[activeId]) {
                window.moduleStore.modules[activeId].params = preservedParams;
            }

            // Emitir evento para que el módulo pueda aplicar sus params
            // El módulo puede escuchar este evento y restaurar su estado
            setTimeout(() => {
                const event = new CustomEvent('lego:module:reloaded', {
                    detail: {
                        moduleId: activeId,
                        params: preservedParams
                    }
                });
                document.dispatchEvent(event);
            }, 100); // Pequeño delay para que el DOM esté listo

        },

        /**
         * Close a specific module
         * @param {string} id - Module ID to close
         * @param {Object} [options] - Options
         * @param {string} [options.returnTo] - Module ID to return to (overrides sourceModuleId)
         */
        closeModule: function(id, options = {}) {
            if (!window.moduleStore) {
                console.warn('ModuleStore not available');
                return;
            }

            // Remove container from DOM
            const container = document.getElementById(`module-${id}`);
            if (container) {
                container.remove();
            }

            // Close via ModuleStore (passing returnTo option)
            window.moduleStore.closeModule(id, options);

            // Notify MenuHiddenItemsManager to clean up hierarchy
            if (window.menuHiddenItemsManager) {
                window.menuHiddenItemsManager.handleModuleClosed(id);
            }

            // Remove dynamic menu item if it exists
            this.removeDynamicMenuItem(id);

            // Sync menu state
            if (window.menuStateManager) {
                window.menuStateManager.syncWithModuleStore();
            }

            // Update breadcrumb
            this.updateBreadcrumbFromActiveModule();

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
         * @param {Object} options - Optional configuration
         * @param {boolean} options.refresh - If true, refresh the module that becomes active after closing
         * @param {string} options.returnTo - Module ID to return to (overrides automatic sourceModuleId)
         */
        closeCurrentWindow: function(options = {}) {
            if (!window.moduleStore || !window.moduleStore.activeModule) {
                console.warn('No active module to close');
                return;
            }

            const activeId = window.moduleStore.activeModule;
            const shouldRefresh = options.refresh === true;
            const returnTo = options.returnTo || null;

            this.closeModule(activeId, { returnTo });

            // If refresh requested, reload the new active module
            if (shouldRefresh) {
                // Small delay to ensure module switch is complete
                setTimeout(() => {
                    if (window.moduleStore.activeModule) {
                        this.reloadActive();
                    }
                }, 50);
            }
        },

        // ═══════════════════════════════════════════════════════════════════
        // PARÁMETROS PERSISTENTES POR MÓDULO
        // Permite guardar estado (filtros, búsquedas, etc.) que persiste
        // durante reloads pero se elimina al cerrar el módulo
        // ═══════════════════════════════════════════════════════════════════

        /**
         * Establecer un parámetro persistente
         * @param {string} key - Nombre del parámetro
         * @param {*} value - Valor del parámetro (cualquier tipo serializable)
         * @param {string} [moduleId] - ID del módulo (opcional, usa el activo si no se pasa)
         * 
         * @example
         * // Guardar en módulo activo
         * legoWindowManager.setParam('tableFilter', 'nombre:Juan');
         * 
         * // Guardar en módulo específico
         * legoWindowManager.setParam('currentPage', 3, 'products-list');
         */
        setParam: function(key, value, moduleId = null) {
            if (!window.moduleStore) {
                console.warn('[WindowManager] ModuleStore not available');
                return;
            }
            const targetModule = moduleId || window.moduleStore.activeModule;
            if (!targetModule) {
                console.warn('[WindowManager] No module specified and no active module');
                return;
            }
            window.moduleStore.setParam(targetModule, key, value);
        },

        /**
         * Obtener un parámetro persistente
         * @param {string} key - Nombre del parámetro
         * @param {*} [defaultValue=null] - Valor por defecto si no existe
         * @param {string} [moduleId] - ID del módulo (opcional, usa el activo si no se pasa)
         * @returns {*} Valor del parámetro
         * 
         * @example
         * // Obtener del módulo activo
         * const filter = legoWindowManager.getParam('tableFilter', '');
         * 
         * // Obtener de módulo específico
         * const page = legoWindowManager.getParam('currentPage', 1, 'products-list');
         */
        getParam: function(key, defaultValue = null, moduleId = null) {
            if (!window.moduleStore) {
                return defaultValue;
            }
            const targetModule = moduleId || window.moduleStore.activeModule;
            if (!targetModule) {
                return defaultValue;
            }
            return window.moduleStore.getParam(targetModule, key, defaultValue);
        },

        /**
         * Obtener todos los parámetros persistentes
         * @param {string} [moduleId] - ID del módulo (opcional, usa el activo si no se pasa)
         * @returns {Object} Objeto con todos los parámetros
         * 
         * @example
         * // Del módulo activo
         * const params = legoWindowManager.getParams();
         * 
         * // De módulo específico
         * const params = legoWindowManager.getParams('products-list');
         */
        getParams: function(moduleId = null) {
            if (!window.moduleStore) {
                return {};
            }
            const targetModule = moduleId || window.moduleStore.activeModule;
            if (!targetModule) {
                return {};
            }
            return window.moduleStore.getParams(targetModule);
        },

        /**
         * Eliminar un parámetro específico
         * @param {string} key - Nombre del parámetro a eliminar
         * @param {string} [moduleId] - ID del módulo (opcional, usa el activo si no se pasa)
         */
        removeParam: function(key, moduleId = null) {
            if (!window.moduleStore) {
                return;
            }
            const targetModule = moduleId || window.moduleStore.activeModule;
            if (!targetModule) {
                return;
            }
            window.moduleStore.removeParam(targetModule, key);
        },

        /**
         * Limpiar todos los parámetros
         * @param {string} [moduleId] - ID del módulo (opcional, usa el activo si no se pasa)
         */
        clearParams: function(moduleId = null) {
            if (!window.moduleStore) {
                return;
            }
            const targetModule = moduleId || window.moduleStore.activeModule;
            if (!targetModule) {
                return;
            }
            window.moduleStore.clearParams(targetModule);
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
            const { moduleId, parentMenuId, label, icon, url, insertAfter } = config;


            if (!moduleId || !parentMenuId || !label) {
                console.error('[WindowManager] addDynamicMenuItem: moduleId, parentMenuId, and label are required');
                return false; // Return false on failure
            }

            // Find the parent menu item
            const parentMenuItem = document.querySelector(`[data-menu-item-id="${parentMenuId}"]`);

            if (!parentMenuItem) {
                console.warn(`[WindowManager] Parent menu item not found: ${parentMenuId}`);
                return false; // Return false on failure
            }

            // Check if dynamic item already exists - buscar SOLO en el menú principal, no en popovers
            const menuContainer = document.querySelector('.custom-menu');
            if (menuContainer) {
                const existingItem = menuContainer.querySelector(`[data-menu-item-id="${moduleId}"]:not([data-temp-item="true"])`);
                if (existingItem) {
                    console.warn(`[WindowManager] Item ${moduleId} ya existe en el menú, no se agregará duplicado`);
                    return false; // Return false on failure
                }
            } else {
                // Si no hay menuContainer, buscar en todo el documento pero con advertencia
                const existingItem = document.querySelector(`[data-menu-item-id="${moduleId}"]:not([data-temp-item="true"])`);
                if (existingItem) {
                    console.warn(`[WindowManager] Item ${moduleId} ya existe en el DOM, no se agregará duplicado`);
                    return false; // Return false on failure
                }
            }

            // Get parent's submenu or create insertion point
            // IMPORTANTE: El submenu está DENTRO del parentMenuItem, no en su parentElement
            let submenu = parentMenuItem.querySelector('.custom-submenu');
            
            // Si no hay submenu, intentar encontrarlo por clase section-level
            if (!submenu) {
                submenu = parentMenuItem.querySelector('[class*="section-level"]');
            }
            
            // Debug: verificar que el submenu existe
            if (!submenu) {
                console.warn(`[WindowManager] Submenu no encontrado para parent: ${parentMenuId}`, parentMenuItem);
            }
            
            if (!submenu) {
                // If parent doesn't have a submenu, create one
                // Verificar si el parent es un grupo (tiene custom-menu-title) o un item simple
                const hasTitle = parentMenuItem.querySelector('.custom-menu-title');
                if (hasTitle) {
                    // Es un grupo, crear submenu después del title
                    submenu = document.createElement('div');
                    submenu.className = 'custom-submenu section-level-1';
                    submenu.style.display = 'block'; // Ensure it's visible
                    parentMenuItem.appendChild(submenu);
                } else {
                    // Es un item simple, convertirlo en grupo
                    const button = parentMenuItem.querySelector('.custom-button');
                    if (button) {
                        // Convertir el button en title
                        const title = document.createElement('div');
                        title.className = 'custom-menu-title';
                        title.setAttribute('onclick', 'toggleSubMenu(this)');
                        title.innerHTML = button.innerHTML;
                        button.replaceWith(title);
                        
                        // Crear submenu
                        submenu = document.createElement('div');
                        submenu.className = 'custom-submenu section-level-1';
                        submenu.style.display = 'block';
                        parentMenuItem.appendChild(submenu);
                    }
                }
            }
            
            // Asegurar que el submenu esté visible
            if (submenu) {
                submenu.style.display = 'block';
                parentMenuItem.classList.add('expanded');
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
            if (!submenu) {
                console.error(`[WindowManager] No se puede insertar item ${moduleId}: submenu no encontrado`);
                return;
            }
            
            // Si se especifica insertAfter, insertar después de ese item
            if (insertAfter) {
                const insertAfterElement = submenu.querySelector(`[data-menu-item-id="${insertAfter}"]`);
                if (insertAfterElement) {
                    insertAfterElement.insertAdjacentHTML('afterend', menuItemHTML);
                } else {
                    // Si no se encuentra, insertar al final
                    submenu.insertAdjacentHTML('beforeend', menuItemHTML);
                }
            } else {
                // Insertar al final por defecto
                submenu.insertAdjacentHTML('beforeend', menuItemHTML);
            }

            // Add click listener to the new item
            const newMenuItem = document.querySelector(`[data-menu-item-id="${moduleId}"]`);
            
            // Debug: verificar que el item se agregó
            if (!newMenuItem) {
                console.error(`[WindowManager] Item ${moduleId} no se agregó correctamente al DOM`);
            }
            if (newMenuItem) {
                // Click event should be on the menu section itself (matching generateMenuLinks pattern)
                newMenuItem.addEventListener('click', async (e) => {
                    // Prevent triggering if clicking close button
                    if (e.target.closest('.menu-close-button')) return;

                    const id = newMenuItem.getAttribute('moduleId');
                    const itemUrl = newMenuItem.getAttribute('moduleUrl');
                    
                    console.log(`[WindowManager] Click en item dinámico: id=${id}, url=${itemUrl}, label=${label}`);

                    // Si el item no tiene ruta válida o es solo un contenedor, no abrirlo como módulo
                    if (!itemUrl || itemUrl === '#' || itemUrl === '') {
                        console.log(`[WindowManager] Item ${id} no tiene ruta válida, no se abrirá como módulo`);
                        return;
                    }

                    // Verificar si el item es un grupo (tiene submenu con hijos)
                    // Si es un grupo, no abrirlo como módulo, solo expandir/colapsar
                    const submenu = newMenuItem.querySelector('.custom-submenu');
                    const hasChildren = submenu && submenu.querySelectorAll('[data-menu-item-id]').length > 0;
                    
                    if (hasChildren) {
                        console.log(`[WindowManager] Item ${id} es un grupo con hijos, no se abrirá como módulo`);
                        // Solo expandir/colapsar el submenu
                        if (submenu) {
                            const isExpanded = submenu.style.display !== 'none' && submenu.style.display !== '';
                            submenu.style.display = isExpanded ? 'none' : 'block';
                            newMenuItem.classList.toggle('expanded', !isExpanded);
                        }
                        return;
                    }

                    // NO usar openSystemMenuItem para items dinámicos - abrir directamente
                    // openSystemMenuItem está diseñado para items del popover de configuración, no para items dinámicos del menú
                    if (window.moduleStore.getActiveModule() !== id) {
                        // Fallback: usar openModule directamente
                        console.log(`[WindowManager] Abriendo módulo: ${id}`);
                        openModule(id, itemUrl, label, { url: itemUrl, name: label });
                        // Sincronizar estado después de abrir
                        setTimeout(() => {
                            if (window.menuStateManager) {
                                console.log(`[WindowManager] Sincronizando estado después de abrir: ${id}`);
                                window.menuStateManager.syncWithModuleStore();
                            }
                        }, 100);
                    } else {
                        // Already active - just make sure it's visible
                        const container = document.getElementById(`module-${id}`);
                        if (container) {
                            document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
                            container.classList.add('active');
                        }
                        // Sincronizar estado para asegurar que esté marcado como activo
                        if (window.menuStateManager) {
                            window.menuStateManager.syncWithModuleStore();
                        }
                    }
                });
            }

            // IMPORTANTE: También agregar el ítem dinámico a window.lego.menu
            // para que el breadcrumb funcione correctamente
            this._addDynamicItemToMenuStructure(moduleId, parentMenuId, label, url, itemIcon);

            return true; // Return true on success
        },

        /**
         * Agregar ítem dinámico a window.lego.menu
         * Esto mantiene la estructura del menú sincronizada con el DOM
         *
         * @param {string} moduleId - ID del módulo dinámico
         * @param {string} parentMenuId - ID del menú padre
         * @param {string} label - Texto a mostrar
         * @param {string} url - URL del módulo
         * @param {string} iconName - Nombre del ícono
         * @private
         */
        _addDynamicItemToMenuStructure: function(moduleId, parentMenuId, label, url, iconName) {
            if (!window.lego || !window.lego.menu || !Array.isArray(window.lego.menu)) {
                console.warn('[WindowManager] window.lego.menu no disponible, no se puede agregar ítem dinámico');
                return;
            }

            // Buscar el padre recursivamente
            const findParentAndAdd = (items, targetParentId) => {
                for (const item of items) {
                    if (item.id === targetParentId) {
                        // Verificar si el ítem dinámico ya existe
                        const exists = item.childs.some(child => child.id === moduleId);
                        if (!exists) {
                            // Agregar el ítem dinámico como hijo
                            item.childs.push({
                                id: moduleId,
                                name: label,
                                url: url,
                                iconName: iconName,
                                level: (item.level || 0) + 1,
                                childs: [],
                                isDynamic: true // Marcar como dinámico para poder removerlo después
                            });
                        }
                        return true;
                    }

                    // Buscar recursivamente en los hijos
                    if (item.childs && item.childs.length > 0) {
                        if (findParentAndAdd(item.childs, targetParentId)) {
                            return true;
                        }
                    }
                }
                return false;
            };

            const found = findParentAndAdd(window.lego.menu, parentMenuId);
            if (!found) {
                console.warn(`[WindowManager] No se encontró el padre "${parentMenuId}" en window.lego.menu`);
            }
        },

        /**
         * Remove a dynamic menu item
         * @param {string} moduleId - Module ID of the dynamic item to remove
         */
        removeDynamicMenuItem: function(moduleId) {
            const menuItem = document.querySelector(`[data-menu-item-id="${moduleId}"][data-dynamic-item="true"]`);
            if (menuItem) {
                // Si el item está directamente en el menú (sin submenu), simplemente removerlo
                const parentElement = menuItem.parentElement;
                const isInSubmenu = parentElement && parentElement.classList.contains('custom-submenu');
                
                if (!isInSubmenu) {
                    // Item está directamente en el menú, simplemente removerlo
                    menuItem.remove();
                } else {
                    // Item está en un submenu
                    const submenu = parentElement;
                    const siblingDynamicItems = submenu.querySelectorAll('.dynamic-menu-item');

                    menuItem.remove();

                    // If submenu is now empty and it was created for dynamic items, remove it
                    if (siblingDynamicItems.length === 1 && submenu.children.length === 0) {
                        submenu.remove();
                    }
                }

                // También remover de window.lego.menu
                this._removeDynamicItemFromMenuStructure(moduleId);

            }
        },

        /**
         * Remover ítem dinámico de window.lego.menu
         *
         * @param {string} moduleId - ID del módulo dinámico a remover
         * @private
         */
        _removeDynamicItemFromMenuStructure: function(moduleId) {
            if (!window.lego || !window.lego.menu || !Array.isArray(window.lego.menu)) {
                return;
            }

            // Buscar y remover recursivamente
            const removeFromChildren = (items) => {
                for (const item of items) {
                    if (item.childs && item.childs.length > 0) {
                        // Filtrar para remover el ítem dinámico
                        const initialLength = item.childs.length;
                        item.childs = item.childs.filter(child => child.id !== moduleId);

                        if (item.childs.length < initialLength) {
                            return true;
                        }

                        // Continuar buscando recursivamente
                        if (removeFromChildren(item.childs)) {
                            return true;
                        }
                    }
                }
                return false;
            };

            removeFromChildren(window.lego.menu);
        },

        /**
         * Open a module with automatic dynamic menu item creation
         * @param {Object} config - Configuration object
         * @param {string} config.moduleId - Unique ID for the module
         * @param {string} config.parentMenuId - ID of the parent menu item (optional, will be fetched from DB if not provided)
         * @param {string} config.label - Display label
         * @param {string} config.url - URL to load
         * @param {string} config.icon - Icon name (optional)
         * @param {string} config.sourceModuleId - Module to return to when closing (optional, defaults to current active)
         */
        openModuleWithMenu: async function(config) {
            const { moduleId, parentMenuId, label, url, icon, sourceModuleId } = config;

            // FILOSOFÍA LEGO: La BD es la fuente de verdad
            // Siempre intentar obtener parent_id desde BD primero (procedural)
            let actualParentMenuId = null;
            try {
                const hierarchyResponse = await fetch(`/api/menu/item-hierarchy?id=${encodeURIComponent(moduleId)}`);
                const hierarchyResult = await hierarchyResponse.json();
                if (hierarchyResult.success && hierarchyResult.data && hierarchyResult.data.item) {
                    actualParentMenuId = hierarchyResult.data.item.parent_id || null;
                    console.log(`[WindowManager] parent_id obtenido desde BD para ${moduleId}:`, actualParentMenuId);
                }
            } catch (error) {
                console.error(`[WindowManager] Error obteniendo parent_id para ${moduleId}:`, error);
            }
            
            // Fallback: usar parentMenuId proporcionado si no se encontró en BD
            if (!actualParentMenuId && parentMenuId) {
                actualParentMenuId = parentMenuId;
                console.log(`[WindowManager] Usando parentMenuId proporcionado como fallback para ${moduleId}:`, actualParentMenuId);
            }

            // First, add the dynamic menu item
            const menuItemAdded = this.addDynamicMenuItem({
                moduleId,
                parentMenuId: actualParentMenuId,
                label,
                icon,
                url
            });

            // Only open the module if the menu item was successfully added
            if (menuItemAdded) {
                // Then open the module (sourceModuleId defaults to current active in openModule)
                openModule(moduleId, url, label, { url, name: label }, { sourceModuleId });
            } else {
                console.error(`[WindowManager] No se pudo agregar el item del menú para ${moduleId}, no se abrirá el módulo`);
                if (window.AlertService) {
                    window.AlertService.error('Error al agregar el item al menú. El módulo no se abrirá.');
                }
            }
        },

        /**
         * Update breadcrumb based on currently active module
         *
         * FUENTE DE VERDAD: window.lego.menu (NO el DOM)
         * Usa la estructura JSON del menú pasada desde PHP para construir breadcrumbs
         * de manera confiable sin depender de la estructura DOM que puede cambiar.
         */
        updateBreadcrumbFromActiveModule: function() {
            if (!window.moduleStore || !window.moduleStore.activeModule) {
                // No active module - clear breadcrumb
                this.updateBreadcrumb([]);
                return;
            }

            const activeId = window.moduleStore.activeModule;

            // Verificar que tengamos la estructura del menú disponible
            if (!window.lego || !window.lego.menu || !Array.isArray(window.lego.menu)) {
                console.warn('[WindowManager] window.lego.menu no disponible, usando fallback DOM');
                this._updateBreadcrumbFromDOM(activeId);
                return;
            }

            // Buscar el ítem en la estructura del menú (recursivamente)
            const breadcrumbItems = this._buildBreadcrumbFromMenuStructure(activeId, window.lego.menu);

            if (breadcrumbItems.length === 0) {
                // Si no se encuentra en la estructura JSON, intentar con DOM (fallback para ítems dinámicos)
                this._updateBreadcrumbFromDOM(activeId);
            } else {
                this.updateBreadcrumb(breadcrumbItems);
            }
        },

        /**
         * Construye breadcrumb desde la estructura JSON del menú
         * Busca recursivamente el ítem activo y construye el path de padres
         *
         * @param {string} targetId - ID del módulo a buscar
         * @param {Array} menuItems - Array de items del menú
         * @param {Array} parentChain - Cadena de padres acumulada (para recursión)
         * @returns {Array} Array de breadcrumb items { label, href }
         */
        _buildBreadcrumbFromMenuStructure: function(targetId, menuItems, parentChain = []) {
            for (const item of menuItems) {
                // Si encontramos el ítem que buscamos
                if (item.id === targetId) {
                    // Retornar la cadena de padres + el ítem actual
                    return [
                        ...parentChain,
                        { label: item.name, href: '#' }
                    ];
                }

                // Si el ítem tiene hijos, buscar recursivamente
                if (item.childs && item.childs.length > 0) {
                    const result = this._buildBreadcrumbFromMenuStructure(
                        targetId,
                        item.childs,
                        [...parentChain, { label: item.name, href: '#' }]
                    );

                    if (result.length > 0) {
                        return result;
                    }
                }
            }

            // No encontrado en este nivel
            return [];
        },

        /**
         * Fallback: Construir breadcrumb desde el DOM (método legacy)
         * Se usa cuando window.lego.menu no está disponible o para ítems dinámicos
         *
         * @param {string} activeId - ID del módulo activo
         * @private
         */
        _updateBreadcrumbFromDOM: function(activeId) {
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
