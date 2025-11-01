/**
 * Lego Events - Sistema de eventos centralizado para el framework Lego
 *
 * FILOSOFÍA LEGO:
 * Sistema de eventos pub/sub que permite comunicación desacoplada
 * entre componentes sin dependencias directas.
 *
 * PROBLEMA QUE RESUELVE:
 * - DOMContentLoaded no funciona en componentes cargados dinámicamente
 * - Los scripts se cargan DESPUÉS de que el DOM ya está listo
 * - Necesidad de inicializar componentes en el momento correcto
 *
 * USO:
 * ```javascript
 * // Suscribirse a evento de inicialización de componente
 * lego.events.on('component:init', (detail) => {
 *     console.log('Componente inicializado:', detail.componentName);
 * });
 *
 * // En tu componente
 * lego.events.onComponentInit((detail) => {
 *     if (detail.componentName === 'ProductCreateComponent') {
 *         // Inicializar tu componente aquí
 *     }
 * });
 * ```
 *
 * EVENTOS DISPONIBLES:
 * - component:init - Cuando un componente se carga e inicializa
 * - component:ready - Cuando un componente termina de inicializarse
 * - module:opened - Cuando se abre un módulo/pestaña
 * - module:closed - Cuando se cierra un módulo/pestaña
 * - table:ready - Cuando una tabla AG Grid está lista
 * - form:submitted - Cuando se envía un formulario
 */

class LegoEvents {
    constructor() {
        this.listeners = new Map();
        this.eventHistory = [];
        this.maxHistorySize = 50;

        console.log('[LegoEvents] Sistema de eventos inicializado');
    }

    /**
     * Suscribirse a un evento
     * @param {string} eventName - Nombre del evento
     * @param {Function} callback - Función a ejecutar
     * @param {Object} options - Opciones adicionales
     * @returns {Function} Función para desuscribirse
     */
    on(eventName, callback, options = {}) {
        if (!this.listeners.has(eventName)) {
            this.listeners.set(eventName, []);
        }

        const listener = {
            callback,
            once: options.once || false,
            priority: options.priority || 0,
            id: `${eventName}_${Date.now()}_${Math.random()}`
        };

        this.listeners.get(eventName).push(listener);

        // Ordenar por prioridad (mayor prioridad primero)
        this.listeners.get(eventName).sort((a, b) => b.priority - a.priority);

        console.log(`[LegoEvents] Listener registrado: ${eventName}`, options);

        // Retornar función para desuscribirse
        return () => this.off(eventName, listener.id);
    }

    /**
     * Suscribirse a un evento una sola vez
     */
    once(eventName, callback, options = {}) {
        return this.on(eventName, callback, { ...options, once: true });
    }

    /**
     * Desuscribirse de un evento
     */
    off(eventName, listenerId) {
        if (!this.listeners.has(eventName)) return;

        const listeners = this.listeners.get(eventName);
        const index = listeners.findIndex(l => l.id === listenerId);

        if (index !== -1) {
            listeners.splice(index, 1);
            console.log(`[LegoEvents] Listener eliminado: ${eventName}`);
        }
    }

    /**
     * Emitir un evento
     */
    emit(eventName, detail = {}) {
        console.log(`[LegoEvents] Emitiendo evento: ${eventName}`, detail);

        // Guardar en historial
        this.eventHistory.push({
            eventName,
            detail,
            timestamp: new Date().toISOString()
        });

        // Mantener tamaño del historial
        if (this.eventHistory.length > this.maxHistorySize) {
            this.eventHistory.shift();
        }

        // Emitir evento nativo del navegador también
        window.dispatchEvent(new CustomEvent(`lego:${eventName}`, { detail }));

        // Ejecutar listeners registrados
        if (this.listeners.has(eventName)) {
            const listeners = [...this.listeners.get(eventName)];
            const listenersToRemove = [];

            listeners.forEach(listener => {
                try {
                    listener.callback(detail);

                    // Si es "once", marcarlo para eliminar
                    if (listener.once) {
                        listenersToRemove.push(listener.id);
                    }
                } catch (error) {
                    console.error(`[LegoEvents] Error en listener de ${eventName}:`, error);
                }
            });

            // Eliminar listeners "once"
            listenersToRemove.forEach(id => this.off(eventName, id));
        }
    }

    /**
     * Obtener historial de eventos
     */
    getHistory(eventName = null) {
        if (eventName) {
            return this.eventHistory.filter(e => e.eventName === eventName);
        }
        return this.eventHistory;
    }

    /**
     * Limpiar todos los listeners
     */
    clear(eventName = null) {
        if (eventName) {
            this.listeners.delete(eventName);
            console.log(`[LegoEvents] Listeners eliminados para: ${eventName}`);
        } else {
            this.listeners.clear();
            console.log('[LegoEvents] Todos los listeners eliminados');
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // HELPERS ESPECÍFICOS PARA COMPONENTES
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Suscribirse a inicialización de componente
     * Reemplazo de DOMContentLoaded para componentes dinámicos
     *
     * @param {Function} callback - Recibe { componentName, componentId, path }
     * @param {string} componentName - Opcional: filtrar por nombre de componente
     */
    onComponentInit(callback, componentName = null) {
        return this.on('component:init', (detail) => {
            if (!componentName || detail.componentName === componentName) {
                callback(detail);
            }
        });
    }

    /**
     * Suscribirse a componente listo (después de init)
     */
    onComponentReady(callback, componentName = null) {
        return this.on('component:ready', (detail) => {
            if (!componentName || detail.componentName === componentName) {
                callback(detail);
            }
        });
    }

    /**
     * Emitir que un componente se inicializó
     */
    emitComponentInit(componentName, componentId, metadata = {}) {
        this.emit('component:init', {
            componentName,
            componentId,
            timestamp: Date.now(),
            ...metadata
        });
    }

    /**
     * Emitir que un componente está listo
     */
    emitComponentReady(componentName, componentId, metadata = {}) {
        this.emit('component:ready', {
            componentName,
            componentId,
            timestamp: Date.now(),
            ...metadata
        });
    }

    /**
     * Suscribirse a apertura de módulo
     */
    onModuleOpened(callback) {
        return this.on('module:opened', callback);
    }

    /**
     * Suscribirse a cierre de módulo
     */
    onModuleClosed(callback) {
        return this.on('module:closed', callback);
    }

    /**
     * Suscribirse a tabla lista
     */
    onTableReady(callback, tableId = null) {
        return this.on('table:ready', (detail) => {
            if (!tableId || detail.tableId === tableId) {
                callback(detail);
            }
        });
    }
}

// Crear instancia singleton y exponerla globalmente
window.LegoEvents = LegoEvents;
window.legoEvents = new LegoEvents();
