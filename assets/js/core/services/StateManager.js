/**
 * StateManager - Gestor de estado global con eventos
 *
 * FILOSOFÍA LEGO:
 * Sistema de eventos desacoplado para comunicar cambios entre bloques.
 * Un componente emite eventos, otros se suscriben sin conocerse.
 *
 * USO:
 * const state = new StateManager();
 * state.on('product:selected', (id) => console.log('Producto:', id));
 * state.emit('product:selected', 123);
 */

// Evitar redeclaración si ya existe
if (typeof StateManager === 'undefined') {
    class StateManager {
    constructor() {
        this.state = {};
        this.listeners = {};
    }

    /**
     * Establecer valor de estado y emitir evento
     */
    setState(key, value) {
        const oldValue = this.state[key];
        this.state[key] = value;

        // Emitir evento de cambio
        if (oldValue !== value) {
            this.emit(`${key}:changed`, { old: oldValue, new: value });
            console.log(`[StateManager] ${key}:`, value);
        }

        return value;
    }

    /**
     * Obtener valor del estado
     */
    getState(key) {
        return this.state[key];
    }

    /**
     * Obtener estado completo
     */
    getAll() {
        return { ...this.state };
    }

    /**
     * Suscribirse a un evento
     */
    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);

        // Retornar función para desuscribirse
        return () => {
            this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
        };
    }

    /**
     * Suscribirse una sola vez
     */
    once(event, callback) {
        const unsubscribe = this.on(event, (data) => {
            callback(data);
            unsubscribe();
        });
    }

    /**
     * Emitir un evento
     */
    emit(event, data) {
        if (!this.listeners[event]) return;

        this.listeners[event].forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error(`[StateManager] Error en evento ${event}:`, error);
            }
        });
    }

    /**
     * Limpiar listeners de un evento
     */
    offAll(event) {
        delete this.listeners[event];
    }

    /**
     * Limpiar todo
     */
    reset() {
        this.state = {};
        this.listeners = {};
    }
}

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = StateManager;
    }
    
    // Exponer globalmente
    window.StateManager = StateManager;
}
