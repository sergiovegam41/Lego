/**
 * ComponentContext - Contexto automático del componente actual
 * 
 * FILOSOFÍA LEGO:
 * El backend define la verdad (rutas, IDs, relaciones).
 * El frontend la consume sin "magic strings".
 * 
 * CERO CONFIGURACIÓN:
 * El contexto se calcula automáticamente desde:
 * - La ruta actual
 * - El atributo #[ApiComponent] del componente PHP
 * - La estructura del proyecto
 * 
 * USO:
 * ```javascript
 * const ctx = ComponentContext.current();
 * 
 * // Información derivada automáticamente
 * ctx.id              // 'example-crud'
 * ctx.route           // '/component/example-crud'
 * ctx.apiRoute        // '/api/example-crud'
 * ctx.parentMenuId    // 'example-crud' (para ítems dinámicos)
 * 
 * // Helpers para construir rutas
 * ctx.api('delete')   // '/api/example-crud/delete'
 * ctx.api('get', {id: 5})  // '/api/example-crud/get?id=5'
 * ctx.child('edit')   // '/component/example-crud/edit'
 * ctx.child('edit', {id: 5})  // '/component/example-crud/edit?id=5'
 * 
 * // Abrir módulo dinámico (auto-configura menú)
 * ctx.openDynamic('edit', { id: 5 }, {
 *     label: 'Editar Registro',
 *     icon: 'create-outline'
 * });
 * ```
 */

// Evitar redeclaración si ya existe
if (typeof ComponentContext === 'undefined') {
    class ComponentContext {
    constructor(contextData) {
        this._data = contextData || {};
    }

    // ═══════════════════════════════════════════════════════════════════
    // PROPIEDADES BÁSICAS
    // ═══════════════════════════════════════════════════════════════════

    /** ID del componente (slug) */
    get id() {
        return this._data.id || this._deriveIdFromUrl();
    }

    /** Ruta del componente */
    get route() {
        return this._data.route || this._deriveRouteFromUrl();
    }

    /** Ruta base de API */
    get apiRoute() {
        return this._data.apiRoute || this._deriveApiRouteFromUrl();
    }

    /** ID del menú padre (para ítems dinámicos) */
    get parentMenuId() {
        return this._data.parentMenuId || this._deriveParentMenuId();
    }

    /** Nombre de la clase PHP */
    get className() {
        return this._data.className || '';
    }

    /** Namespace PHP */
    get namespace() {
        return this._data.namespace || '';
    }

    // ═══════════════════════════════════════════════════════════════════
    // HELPERS PARA CONSTRUIR RUTAS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Construir URL de API
     * @param {string} action - Acción (ej: 'delete', 'update', 'get')
     * @param {Object} params - Parámetros query (opcional)
     * @returns {string} URL completa
     * 
     * @example
     * ctx.api('delete')           // '/api/example-crud/delete'
     * ctx.api('get', { id: 5 })   // '/api/example-crud/get?id=5'
     */
    api(action, params = null) {
        let url = `${this.apiRoute}/${action}`;
        if (params && Object.keys(params).length > 0) {
            url += '?' + new URLSearchParams(params).toString();
        }
        return url;
    }

    /**
     * Construir URL de componente hijo/relacionado
     * @param {string} childPath - Ruta hija (ej: 'edit', 'create')
     * @param {Object} params - Parámetros query (opcional)
     * @returns {string} URL completa
     * 
     * @example
     * ctx.child('edit')           // '/component/example-crud/edit'
     * ctx.child('edit', { id: 5 })// '/component/example-crud/edit?id=5'
     */
    child(childPath, params = null) {
        // Usar la ruta base (sin sub-rutas)
        const basePath = this.route.split('/').slice(0, 3).join('/');
        let url = `${basePath}/${childPath}`;
        if (params && Object.keys(params).length > 0) {
            url += '?' + new URLSearchParams(params).toString();
        }
        return url;
    }

    // ═══════════════════════════════════════════════════════════════════
    // INTEGRACIÓN CON WINDOW MANAGER
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Abrir módulo dinámico con menú auto-configurado
     * @param {string} childName - Nombre del hijo (ej: 'edit')
     * @param {Object} params - Parámetros (ej: { id: 5 })
     * @param {Object} menuConfig - Configuración del menú
     * @param {string} menuConfig.label - Etiqueta a mostrar
     * @param {string} menuConfig.icon - Ícono (opcional)
     * 
     * @example
     * ctx.openDynamic('edit', { id: 5 }, {
     *     label: 'Editar Registro',
     *     icon: 'create-outline'
     * });
     */
    openDynamic(childName, params = {}, menuConfig = {}) {
        if (!window.legoWindowManager?.openModuleWithMenu) {
            console.error('[ComponentContext] legoWindowManager no disponible');
            return;
        }

        const moduleId = `${this.id}-${childName}`;
        const url = this.child(childName, params);
        const label = menuConfig.label || childName;
        const icon = menuConfig.icon || 'ellipse-outline';

        window.legoWindowManager.openModuleWithMenu({
            moduleId: moduleId,
            parentMenuId: this.parentMenuId,
            label: label,
            url: url,
            icon: icon
        });

        console.log(`[ComponentContext] Módulo dinámico abierto: ${moduleId}`);
    }

    /**
     * Recargar módulo dinámico existente con nuevos parámetros
     * @param {string} childName - Nombre del hijo (ej: 'edit')
     * @param {Object} params - Nuevos parámetros
     */
    reloadDynamic(childName, params = {}) {
        const moduleId = `${this.id}-${childName}`;
        const url = this.child(childName, params);
        
        const container = document.getElementById(`module-${moduleId}`);
        if (!container) {
            console.warn(`[ComponentContext] Módulo ${moduleId} no existe, abriendo nuevo`);
            return false;
        }

        // Activar el módulo
        document.querySelectorAll('.module-container').forEach(m => m.classList.remove('active'));
        container.classList.add('active');
        
        if (window.moduleStore) {
            const modules = window.moduleStore.getModules();
            if (modules[moduleId]) {
                window.moduleStore._openModule(moduleId, modules[moduleId].component);
            }
        }

        // Recargar contenido
        fetch(url)
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                // Re-ejecutar scripts
                container.querySelectorAll('script').forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    newScript.textContent = oldScript.textContent;
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
                console.log(`[ComponentContext] Módulo ${moduleId} recargado`);
            })
            .catch(err => console.error(`[ComponentContext] Error recargando ${moduleId}:`, err));

        return true;
    }

    // ═══════════════════════════════════════════════════════════════════
    // DERIVACIÓN AUTOMÁTICA (FALLBACKS)
    // ═══════════════════════════════════════════════════════════════════

    _deriveIdFromUrl() {
        // /component/example-crud/edit -> example-crud
        const path = window.location.pathname;
        const match = path.match(/\/component\/([^\/]+)/);
        return match ? match[1] : '';
    }

    _deriveRouteFromUrl() {
        // Extraer hasta el segundo segmento
        const path = window.location.pathname;
        const match = path.match(/(\/component\/[^\/]+)/);
        return match ? match[1] : '/component';
    }

    _deriveApiRouteFromUrl() {
        const id = this._deriveIdFromUrl();
        return id ? `/api/${id}` : '/api';
    }

    _deriveParentMenuId() {
        // El ID del componente base es típicamente el ID del menú
        return this._deriveIdFromUrl();
    }

    // ═══════════════════════════════════════════════════════════════════
    // MÉTODOS ESTÁTICOS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Obtener el contexto del componente actual
     * 
     * Prioridad:
     * 1. Contexto inyectado por PHP (window.__componentContext)
     * 2. Derivación automática desde URL
     * 
     * @returns {ComponentContext}
     */
    static current() {
        const data = window.__componentContext || {};
        return new ComponentContext(data);
    }

    /**
     * Obtener contexto desde el módulo activo
     * Útil cuando hay múltiples módulos cargados
     * 
     * @returns {ComponentContext}
     */
    static fromActiveModule() {
        const activeModuleId = window.moduleStore?.getActiveModule();
        if (!activeModuleId) {
            return this.current();
        }

        // Buscar contexto específico del módulo activo
        const container = document.getElementById(`module-${activeModuleId}`);
        if (container) {
            const script = container.querySelector('script');
            // Si el módulo tiene su propio contexto inyectado, usarlo
            // Por ahora, derivar desde el moduleId
            const parts = activeModuleId.split('-');
            const baseId = parts.slice(0, -1).join('-') || parts[0];
            
            return new ComponentContext({
                id: baseId,
                route: `/component/${baseId}`,
                apiRoute: `/api/${baseId}`,
                parentMenuId: baseId
            });
        }

        return this.current();
    }
}

    // Exponer globalmente
    window.ComponentContext = ComponentContext;

    // Shorthand para uso rápido
    window.ctx = () => ComponentContext.current();

    console.log('[Lego] ComponentContext disponible - usa ctx() o ComponentContext.current()');
}

