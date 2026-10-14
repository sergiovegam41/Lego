export function activeMenu() {

    addEventForToggle();
    
    // Add click handlers for parent menu items
    document.querySelectorAll('.menu-parent').forEach(parent => {
        parent.querySelector('a').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            // Toggle active class
            parent.classList.toggle('active');

            // Close other open menus
            document.querySelectorAll('.menu-parent.active').forEach(item => {
                if (item !== parent) {
                    item.classList.remove('active');
                }
            });
        });
    });
    
}

function addEventForToggle() {
    const body = document.querySelector('body'),
          sidebar = body.querySelector('nav'),
          toggle = body.querySelector(".toggle"),
          searchBtn = body.querySelector(".search-box"),
          sidebarShade = document.querySelector('#content-sidebar-shade');

    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
        const isNowCollapsed = sidebar.classList.contains("close");

        if (isNowCollapsed) {
            // Cuando el sidebar está cerrado - usar valor real de CSS variable
            const collapsedWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width-collapsed');
            const collapsedWidthPx = parseFloat(collapsedWidth) * 16; // Convertir rem a px
            
            sidebarShade.style.minWidth = collapsedWidthPx + "px";
            sidebarShade.style.width = collapsedWidthPx + "px";
            
            // Agregar clase para CSS adicional si es necesario
            document.body.classList.add('sidebar-collapsed');
            
            // Guardar estado: colapsado (solo en desktop)
            if (window.SidebarStateManager && !window.SidebarStateManager.isMobile()) {
                window.SidebarStateManager.saveState(window.SidebarStateManager.STATE_COLLAPSED);
            }
        } else {
            // Cuando el sidebar está abierto - usar el ancho actual o por defecto
            const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
            if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
                sidebarShade.style.minWidth = currentWidth + "px";
                sidebarShade.style.width = currentWidth + "px";
            } else {
                const defaultWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
                const defaultWidthPx = parseFloat(defaultWidth) * 16; // Convertir rem a px
                sidebarShade.style.minWidth = defaultWidthPx + "px";
                sidebarShade.style.width = defaultWidthPx + "px";
            }
            
            // Remover clase CSS
            document.body.classList.remove('sidebar-collapsed');
            
            // Guardar estado: expandido (solo en desktop)
            if (window.SidebarStateManager && !window.SidebarStateManager.isMobile()) {
                window.SidebarStateManager.saveState(window.SidebarStateManager.STATE_EXPANDED);
            }
        }
    });

    searchBtn.addEventListener("click", () => {
        sidebar.classList.remove("close");

        // Al abrir el sidebar con el botón de búsqueda - usar el ancho guardado o por defecto
        const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
        if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
            sidebarShade.style.minWidth = currentWidth + "px";
            sidebarShade.style.width = currentWidth + "px";
        } else {
            const defaultWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
            const defaultWidthPx = parseFloat(defaultWidth) * 16; // Convertir rem a px
            sidebarShade.style.minWidth = defaultWidthPx + "px";
            sidebarShade.style.width = defaultWidthPx + "px";
        }
        
        // Remover clase CSS
        document.body.classList.remove('sidebar-collapsed');
    });
    
    // Inicializar el estado correcto del shade al cargar la página
    initializeSidebarShade();
}

/**
 * Inicializa el shade del sidebar con el ancho correcto
 * basado en el estado actual del sidebar (collapsed o no)
 */
function initializeSidebarShade() {
    const sidebar = document.querySelector('nav.sidebar');
    const sidebarShade = document.querySelector('#content-sidebar-shade');
    
    if (!sidebar || !sidebarShade) return;
    
    if (sidebar.classList.contains("close")) {
        // Sidebar está colapsado
        const collapsedWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width-collapsed');
        const collapsedWidthPx = parseFloat(collapsedWidth) * 16; // Convertir rem a px
        
        sidebarShade.style.minWidth = collapsedWidthPx + "px";
        sidebarShade.style.width = collapsedWidthPx + "px";
        document.body.classList.add('sidebar-collapsed');
    } else {
        // Sidebar está abierto - usar ancho guardado o por defecto
        const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
        if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
            sidebarShade.style.minWidth = currentWidth + "px";
            sidebarShade.style.width = currentWidth + "px";
        } else {
            const defaultWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
            const defaultWidthPx = parseFloat(defaultWidth) * 16; // Convertir rem a px
            sidebarShade.style.minWidth = defaultWidthPx + "px";
            sidebarShade.style.width = defaultWidthPx + "px";
        }
        document.body.classList.remove('sidebar-collapsed');
    }
}

/**
 * MenuExpansionManager
 * 
 * Gestiona el estado de expansión de los items del menú usando localStorage
 */
class MenuExpansionManager {
    static STORAGE_KEY = 'lego_menu_expanded_items';
    
    /**
     * Obtener lista de items expandidos desde localStorage
     * @returns {Set<string>} Set con los IDs de items expandidos
     */
    static getExpandedItems() {
        try {
            const stored = localStorage.getItem(this.STORAGE_KEY);
            if (stored) {
                const items = JSON.parse(stored);
                return new Set(Array.isArray(items) ? items : []);
            }
        } catch (e) {
            console.warn('[MenuExpansionManager] Error leyendo localStorage:', e);
        }
        return new Set();
    }
    
    /**
     * Guardar lista de items expandidos en localStorage
     * @param {Set<string>} expandedItems - Set con los IDs de items expandidos
     */
    static saveExpandedItems(expandedItems) {
        try {
            const itemsArray = Array.from(expandedItems);
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(itemsArray));
        } catch (e) {
            console.warn('[MenuExpansionManager] Error guardando en localStorage:', e);
        }
    }
    
    /**
     * Agregar un item a la lista de expandidos
     * @param {string} itemId - ID del item del menú
     */
    static addExpandedItem(itemId) {
        if (!itemId) {
            console.warn('[MenuExpansionManager] addExpandedItem: itemId es null o undefined');
            return;
        }
        const expanded = this.getExpandedItems();
        expanded.add(itemId);
        this.saveExpandedItems(expanded);
        
        // Verificar que se guardó correctamente
        const verify = this.getExpandedItems();
        if (!verify.has(itemId)) {
            console.error(`[MenuExpansionManager] ERROR: No se pudo guardar itemId ${itemId} en localStorage`);
        }
    }
    
    /**
     * Remover un item de la lista de expandidos
     * @param {string} itemId - ID del item del menú
     */
    static removeExpandedItem(itemId) {
        if (!itemId) {
            console.warn('[MenuExpansionManager] removeExpandedItem: itemId es null o undefined');
            return;
        }
        const expanded = this.getExpandedItems();
        expanded.delete(itemId);
        this.saveExpandedItems(expanded);
        
        // Verificar que se removió correctamente
        const verify = this.getExpandedItems();
        if (verify.has(itemId)) {
            console.error(`[MenuExpansionManager] ERROR: No se pudo remover itemId ${itemId} de localStorage`);
        }
    }
    
    /**
     * Verificar si un item está expandido
     * @param {string} itemId - ID del item del menú
     * @returns {boolean}
     */
    static isExpanded(itemId) {
        return this.getExpandedItems().has(itemId);
    }
    
    /**
     * Restaurar estado de expansión de todos los items del menú
     */
    static restoreExpandedState() {
        const expandedItems = this.getExpandedItems();
        if (expandedItems.size === 0) {
            return;
        }
        
        // Función para intentar restaurar el estado
        const attemptRestore = () => {
            let restoredCount = 0;
            
            expandedItems.forEach(itemId => {
                const menuSection = document.querySelector(`[data-menu-item-id="${itemId}"]`);
                if (!menuSection) {
                    console.warn(`[MenuExpansionManager] No se encontró menuSection para itemId: ${itemId}`);
                    return;
                }
                
                const title = menuSection.querySelector('.custom-menu-title');
                if (!title) {
                    console.warn(`[MenuExpansionManager] No se encontró title para itemId: ${itemId}`);
                    return;
                }
                
                const submenu = title.nextElementSibling;
                if (!submenu) {
                    console.warn(`[MenuExpansionManager] No se encontró submenu para itemId: ${itemId}`);
                    return;
                }
                
                if (!submenu.classList.contains('custom-submenu')) {
                    console.warn(`[MenuExpansionManager] El elemento siguiente no es custom-submenu para itemId: ${itemId}`);
                    return;
                }
                
                // Usar setProperty con !important para asegurar que sobrescriba el CSS
                submenu.style.setProperty('display', 'block', 'important');
                
                // También verificar que se aplicó correctamente
                const computedDisplay = window.getComputedStyle(submenu).display;
                if (computedDisplay === 'none') {
                    console.error(`[MenuExpansionManager] ERROR: display sigue siendo 'none' para itemId: ${itemId} después de intentar restaurar`);
                }
                
                const chevronIcon = title.querySelector(".icon_menu_chevron");
                if (chevronIcon) {
                    chevronIcon.setAttribute("name", "chevron-down-outline");
                }
                
                restoredCount++;
            });
            
            return restoredCount;
        };
        
        // Intentar restaurar inmediatamente
        let restored = attemptRestore();
        
        // Si no se restauraron todos, intentar varias veces con intervalos
        if (restored < expandedItems.size) {
            let attempts = 0;
            const maxAttempts = 10;
            const interval = setInterval(() => {
                attempts++;
                restored = attemptRestore();
                
                // Si se restauraron todos o se alcanzó el máximo de intentos, detener
                if (restored >= expandedItems.size || attempts >= maxAttempts) {
                    clearInterval(interval);
                    if (restored === 0) {
                        console.warn(`[MenuExpansionManager] No se pudo restaurar ningún item después de ${attempts} intentos`);
                    }
                }
            }, 200);
        } else if (restored === 0) {
            console.warn(`[MenuExpansionManager] No se encontraron elementos para restaurar`);
        }
    }
}

// Función para restaurar estado cuando el menú esté listo
function initMenuExpansionRestore() {
    // Verificar que el contenedor del menú exista
    const menuContainer = document.querySelector('.custom-menu');
    if (!menuContainer) {
        // Si no existe, intentar de nuevo después de un delay
        setTimeout(initMenuExpansionRestore, 200);
        return;
    }
    
    // Restaurar estado
    MenuExpansionManager.restoreExpandedState();
}

// Restaurar estado al cargar la página
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Esperar un poco más para que el menú se renderice
        setTimeout(initMenuExpansionRestore, 300);
    });
} else {
    // DOM ya está listo, pero esperar a que el menú se renderice
    setTimeout(initMenuExpansionRestore, 300);
}

// También restaurar después de que se renderice el menú
if (window.lego && window.lego.events) {
    window.lego.events.on('menu:rendered', () => {
        MenuExpansionManager.restoreExpandedState();
    });
}

// MutationObserver para detectar cuando algo colapsa los submenús y restaurarlos
let expansionObserver = null;
function setupExpansionProtection() {
    const menuContainer = document.querySelector('.custom-menu');
    if (!menuContainer) {
        setTimeout(setupExpansionProtection, 200);
        return;
    }
    
    // Si ya existe un observer, desconectarlo
    if (expansionObserver) {
        expansionObserver.disconnect();
    }
    
    const expandedItems = MenuExpansionManager.getExpandedItems();
    if (expandedItems.size === 0) return;
    
    expansionObserver = new MutationObserver((mutations) => {
        let needsRestore = false;
        
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                const target = mutation.target;
                if (target.classList.contains('custom-submenu')) {
                    const itemId = target.closest('[data-menu-item-id]')?.getAttribute('data-menu-item-id');
                    if (itemId && expandedItems.has(itemId)) {
                        const display = window.getComputedStyle(target).display;
                        if (display === 'none') {
                            // Obtener el stack trace para ver qué está causando el colapso
                            const stack = new Error().stack;
                            console.warn(`[MenuExpansionManager] ⚠️ Detectado colapso no autorizado de ${itemId}`);
                            console.warn(`[MenuExpansionManager] Stack trace:`, stack);
                            console.warn(`[MenuExpansionManager] Elemento:`, target);
                            console.warn(`[MenuExpansionManager] Estilo aplicado:`, target.style.display);
                            needsRestore = true;
                        }
                    }
                }
            }
        });
        
        if (needsRestore && !isRestoring) {
            // Usar un delay más largo para evitar loops infinitos
            setTimeout(() => {
                if (!isRestoring) {
                    MenuExpansionManager.restoreExpandedState();
                }
            }, 200);
        }
    });
    
    // Observar cambios en los submenús
    expandedItems.forEach(itemId => {
        const menuSection = document.querySelector(`[data-menu-item-id="${itemId}"]`);
        if (menuSection) {
            const submenu = menuSection.querySelector('.custom-submenu');
            if (submenu) {
                expansionObserver.observe(submenu, {
                    attributes: true,
                    attributeFilter: ['style']
                });
            }
        }
    });
}

// Flag para evitar múltiples restauraciones simultáneas
let isRestoring = false;
let lastRestoreTime = 0;
const RESTORE_DEBOUNCE_MS = 500; // Solo restaurar una vez cada 500ms

// Configurar protección después de restaurar el estado
const originalRestore = MenuExpansionManager.restoreExpandedState;
MenuExpansionManager.restoreExpandedState = function() {
    const now = Date.now();
    
    // Si ya se está restaurando o se restauró recientemente, ignorar
    if (isRestoring || (now - lastRestoreTime) < RESTORE_DEBOUNCE_MS) {
        return;
    }
    
    isRestoring = true;
    lastRestoreTime = now;
    
    originalRestore.call(this);
    
    // Configurar protección después de un delay más largo para evitar conflictos
    setTimeout(() => {
        setupExpansionProtection();
        isRestoring = false;
    }, 1000); // Esperar 1 segundo antes de activar protección
    
    // También configurar una verificación periódica para asegurar que los submenús permanezcan abiertos
    if (MenuExpansionManager._protectionInterval) {
        clearInterval(MenuExpansionManager._protectionInterval);
    }
    
    const expandedItems = MenuExpansionManager.getExpandedItems();
    if (expandedItems.size > 0) {
        // Esperar más tiempo antes de empezar la verificación periódica
        setTimeout(() => {
            MenuExpansionManager._protectionInterval = setInterval(() => {
                let needsRestore = false;
                expandedItems.forEach(itemId => {
                    const menuSection = document.querySelector(`[data-menu-item-id="${itemId}"]`);
                    if (menuSection) {
                        const submenu = menuSection.querySelector('.custom-submenu');
                        if (submenu) {
                            const display = window.getComputedStyle(submenu).display;
                            if (display === 'none') {
                                console.warn(`[MenuExpansionManager] Verificación periódica: ${itemId} está cerrado, restaurando...`);
                                needsRestore = true;
                            }
                        }
                    }
                });
                
                if (needsRestore && !isRestoring) {
                    originalRestore.call(MenuExpansionManager);
                }
            }, 1000); // Verificar cada 1 segundo (menos agresivo)
            
            // Limpiar el intervalo después de 15 segundos
            setTimeout(() => {
                if (MenuExpansionManager._protectionInterval) {
                    clearInterval(MenuExpansionManager._protectionInterval);
                    MenuExpansionManager._protectionInterval = null;
                }
            }, 15000);
        }, 2000); // Esperar 2 segundos antes de empezar la verificación
    }
};

// Event listener delegado para capturar todos los clicks en custom-menu-title
// Esto asegura que localStorage se actualice incluso si el onclick inline no funciona
// Usamos una flag para evitar doble ejecución
let isToggling = false;
document.addEventListener('click', (e) => {
    // Verificar si el click fue en un custom-menu-title o en un elemento hijo
    const menuTitle = e.target.closest('.custom-menu-title');
    if (menuTitle) {
        // Si ya estamos procesando un toggle, ignorar
        if (isToggling) {
            return;
        }
        
        // Prevenir el comportamiento por defecto para asegurar que nuestro código se ejecute
        e.stopPropagation();
        e.preventDefault();
        
        // Marcar que estamos procesando
        isToggling = true;
        
        // Llamar a toggleSubMenu inmediatamente para asegurar que se actualice localStorage
        if (window.toggleSubMenu) {
            window.toggleSubMenu(menuTitle);
        } else {
            console.error('[Event Delegator] window.toggleSubMenu no está disponible!');
        }
        
        // Resetear la flag después de un pequeño delay
        setTimeout(() => {
            isToggling = false;
        }, 200);
    }
}, true); // Usar capture phase para que se ejecute ANTES que otros listeners

// Flag para evitar loops infinitos
let isInitializing = false;

// Función para inicializar listeners en elementos existentes
function initializeMenuTitleListeners() {
    // Prevenir loops infinitos
    if (isInitializing) {
        return;
    }
    
    isInitializing = true;
    
    // Desconectar temporalmente el observer para evitar loops
    if (menuObserver) {
        menuObserver.disconnect();
    }
    
    const menuTitles = document.querySelectorAll('.custom-menu-title');
    
    menuTitles.forEach((title) => {
        // Verificar si ya tiene el listener (usando una data attribute)
        if (title.dataset.listenerAttached === 'true') {
            return; // Ya tiene listener, saltar
        }
        
        // Remover el onclick inline
        if (title.hasAttribute('onclick')) {
            title.removeAttribute('onclick');
        }
        
        // Agregar listener directo que siempre actualiza localStorage
        const clickHandler = function(e) {
            // NO prevenir el comportamiento por defecto, solo actualizar localStorage
            // El toggle visual ya lo hace el onclick inline o el CSS
            
            // Asegurar que estamos usando la función correcta
            if (window.toggleSubMenu) {
                window.toggleSubMenu(this);
            } else if (typeof toggleSubMenu === 'function') {
                toggleSubMenu(this);
            } else {
                console.error('[Direct Listener] toggleSubMenu no está disponible!');
            }
        };
        
        title.addEventListener('click', clickHandler, false); // Usar bubble phase normal
        
        // Marcar que ya tiene listener
        title.dataset.listenerAttached = 'true';
    });
    
    // Reconectar el observer
    const menuContainer = document.querySelector('.custom-menu');
    if (menuContainer && menuObserver) {
        menuObserver.observe(menuContainer, {
            childList: true,
            subtree: true
        });
    }
    
    isInitializing = false;
}

// Inicializar listeners cuando el DOM esté listo
function initListenersWhenReady() {
    const menuContainer = document.querySelector('.custom-menu');
    if (!menuContainer) {
        setTimeout(initListenersWhenReady, 200);
        return;
    }
    
    const menuTitles = menuContainer.querySelectorAll('.custom-menu-title');
    if (menuTitles.length === 0) {
        setTimeout(initListenersWhenReady, 200);
        return;
    }
    
    initializeMenuTitleListeners();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(initListenersWhenReady, 500);
    });
} else {
    setTimeout(initListenersWhenReady, 500);
}

// También inicializar cuando se agreguen nuevos elementos al menú
let menuObserver = null;
if (typeof MutationObserver !== 'undefined') {
    menuObserver = new MutationObserver((mutations) => {
        // Ignorar si estamos inicializando
        if (isInitializing) return;
        
        let needsInit = false;
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        if (node.classList && node.classList.contains('custom-menu-title')) {
                            // Verificar que no tenga listener ya
                            if (node.dataset.listenerAttached !== 'true') {
                                needsInit = true;
                            }
                        } else if (node.querySelector && node.querySelector('.custom-menu-title')) {
                            // Verificar que al menos uno no tenga listener
                            const titles = node.querySelectorAll('.custom-menu-title');
                            for (let title of titles) {
                                if (title.dataset.listenerAttached !== 'true') {
                                    needsInit = true;
                                    break;
                                }
                            }
                        }
                    }
                });
            }
        });
        
        if (needsInit) {
            setTimeout(initializeMenuTitleListeners, 200);
        }
    });
}

// Observar cambios en el contenedor del menú
const menuContainer = document.querySelector('.custom-menu');
if (menuContainer) {
    menuObserver.observe(menuContainer, {
        childList: true,
        subtree: true
    });
} else {
    // Si no existe, intentar de nuevo después de un delay
    setTimeout(() => {
        const container = document.querySelector('.custom-menu');
        if (container && menuObserver) {
            menuObserver.observe(container, {
                childList: true,
                subtree: true
            });
        }
    }, 1000);
}

// Exportar MenuExpansionManager para uso global
export { MenuExpansionManager };

export function toggleSubMenu(element) {
    // Asegurar que element sea un elemento DOM válido
    if (!element || !element.nodeType) {
        console.error('[toggleSubMenu] Elemento inválido:', element);
        return;
    }
    
    const submenu = element.nextElementSibling;
    if (!submenu) {
        console.error('[toggleSubMenu] No se encontró submenu para:', element);
        return;
    }
    
    const chevronIcon = element.querySelector(".icon_menu_chevron");
    
    // Obtener el ID del item del menú desde el elemento padre
    const menuSection = element.closest('[data-menu-item-id]');
    const itemId = menuSection ? menuSection.getAttribute('data-menu-item-id') : null;
    
    if (!itemId) {
        console.warn('[toggleSubMenu] No se pudo obtener itemId del elemento:', element);
        return; // Salir si no hay itemId, no podemos guardar el estado
    }

    // Verificar el estado actual usando getComputedStyle para ser más preciso
    const currentDisplay = window.getComputedStyle(submenu).display;
    const isCurrentlyExpanded = currentDisplay === 'block' || submenu.style.display === 'block';

    if (isCurrentlyExpanded) {
        // Colapsar
        submenu.style.setProperty('display', 'none', 'important');
        if (chevronIcon) {
            chevronIcon.setAttribute("name", "chevron-forward-outline");
        }
        // Guardar estado: colapsado
        if (itemId) {
            MenuExpansionManager.removeExpandedItem(itemId);
        }
    } else {
        // Expandir
        submenu.style.setProperty('display', 'block', 'important');
        if (chevronIcon) {
            chevronIcon.setAttribute("name", "chevron-down-outline");
        }
        // Guardar estado: expandido
        if (itemId) {
            MenuExpansionManager.addExpandedItem(itemId);
        }
    }
}