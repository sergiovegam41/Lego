/**
 * ScreenComponent JavaScript
 * 
 * Funcionalidad base para screens LEGO.
 * Provee hooks y utilidades para el manejo de ventanas.
 */

/**
 * LegoScreen - Manager para un screen individual
 */
class LegoScreen {
    constructor(element) {
        this.element = element;
        this.id = element.dataset.screenId;
        this.contentElement = element.querySelector('.lego-screen__content');
        this.headerElement = element.querySelector('.lego-screen__header');
        this.footerElement = element.querySelector('.lego-screen__footer');
        
        this._init();
    }
    
    _init() {
        // Register with global manager
        if (window.legoScreenManager) {
            window.legoScreenManager.register(this);
        }
        
        // Dispatch ready event
        this.element.dispatchEvent(new CustomEvent('lego:screen:ready', {
            bubbles: true,
            detail: { screen: this, id: this.id }
        }));
        
    }
    
    /**
     * Muestra el estado de carga
     */
    setLoading(loading) {
        this.element.classList.toggle('lego-screen--loading', loading);
        return this;
    }
    
    /**
     * Muestra un error en el screen
     */
    setError(message) {
        this.element.classList.add('lego-screen--error');
        if (this.contentElement) {
            this.contentElement.innerHTML = `
                <ion-icon name="alert-circle-outline" class="lego-screen__error-icon"></ion-icon>
                <p class="lego-screen__error-message">${this._escapeHtml(message)}</p>
            `;
        }
        return this;
    }
    
    /**
     * Limpia el estado de error
     */
    clearError() {
        this.element.classList.remove('lego-screen--error');
        return this;
    }
    
    /**
     * Obtiene el ID del screen
     */
    getId() {
        return this.id;
    }
    
    /**
     * Obtiene el módulo asociado (si está en uno)
     */
    getModuleId() {
        const moduleContainer = this.element.closest('[id^="module-"]');
        return moduleContainer?.id.replace('module-', '') || null;
    }
    
    /**
     * Recarga el contenido del screen
     */
    async reload() {
        const moduleId = this.getModuleId();
        if (moduleId && window.legoWindowManager) {
            window.legoWindowManager.reloadActive();
        }
    }
    
    /**
     * Cierra el screen
     */
    close() {
        const moduleId = this.getModuleId();
        if (moduleId && window.legoWindowManager) {
            window.legoWindowManager.closeModule(moduleId);
        }
    }
    
    /**
     * Escapa HTML para prevenir XSS
     */
    _escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Destructor - llamar al desmontar
     */
    destroy() {
        if (window.legoScreenManager) {
            window.legoScreenManager.unregister(this.id);
        }
    }
}

/**
 * LegoScreenManager - Manager global de screens
 */
class LegoScreenManager {
    constructor() {
        this.screens = new Map();
        this._init();
    }
    
    _init() {
        // Auto-inicializar screens existentes en el DOM
        this._initExistingScreens();
        
        // Observar nuevos screens agregados al DOM
        this._setupMutationObserver();
        
    }
    
    _initExistingScreens() {
        document.querySelectorAll('.lego-screen[data-screen-id]').forEach(el => {
            if (!this.screens.has(el.dataset.screenId)) {
                new LegoScreen(el);
            }
        });
    }
    
    _setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check if the added node is a screen
                        if (node.classList?.contains('lego-screen') && node.dataset.screenId) {
                            if (!this.screens.has(node.dataset.screenId)) {
                                new LegoScreen(node);
                            }
                        }
                        // Check for screens inside the added node
                        node.querySelectorAll?.('.lego-screen[data-screen-id]')?.forEach(el => {
                            if (!this.screens.has(el.dataset.screenId)) {
                                new LegoScreen(el);
                            }
                        });
                    }
                });
                
                // Handle removed nodes
                mutation.removedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.classList?.contains('lego-screen') && node.dataset.screenId) {
                            this.unregister(node.dataset.screenId);
                        }
                        node.querySelectorAll?.('.lego-screen[data-screen-id]')?.forEach(el => {
                            this.unregister(el.dataset.screenId);
                        });
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    /**
     * Registra un screen
     */
    register(screen) {
        this.screens.set(screen.id, screen);
        
        // Dispatch global event
        window.dispatchEvent(new CustomEvent('lego:screen:registered', {
            detail: { screen, id: screen.id }
        }));
    }
    
    /**
     * Desregistra un screen
     */
    unregister(id) {
        const screen = this.screens.get(id);
        if (screen) {
            this.screens.delete(id);
            
            window.dispatchEvent(new CustomEvent('lego:screen:unregistered', {
                detail: { id }
            }));
        }
    }
    
    /**
     * Obtiene un screen por ID
     */
    get(id) {
        return this.screens.get(id) || null;
    }
    
    /**
     * Obtiene el screen activo (basado en el módulo activo)
     */
    getActive() {
        const activeModuleId = window.moduleStore?.getActiveModule();
        if (!activeModuleId) return null;
        
        const activeModule = document.getElementById(`module-${activeModuleId}`);
        if (!activeModule) return null;
        
        const screenEl = activeModule.querySelector('.lego-screen[data-screen-id]');
        return screenEl ? this.screens.get(screenEl.dataset.screenId) : null;
    }
    
    /**
     * Lista todos los screens registrados
     */
    list() {
        return Array.from(this.screens.keys());
    }
    
    /**
     * Debug info
     */
    debug() {
        return {
            count: this.screens.size,
            screens: this.list(),
            active: this.getActive()?.id || null
        };
    }
}

// Inicializar manager global
if (typeof window.legoScreenManager === 'undefined') {
    window.legoScreenManager = new LegoScreenManager();
}

// Exponer clase para uso externo
window.LegoScreen = LegoScreen;

