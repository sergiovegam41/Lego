/**
 * DynamicComponentsManager - Cliente JavaScript para componentes dinámicos
 *
 * FILOSOFÍA LEGO:
 * Permite solicitar componentes PHP renderizados desde JavaScript,
 * manteniendo PHP como única fuente de verdad.
 *
 * CARACTERÍSTICAS:
 * - Batch rendering (múltiples componentes en 1 request)
 * - Preservación de orden garantizada
 * - Caché de componentes (opcional)
 * - Manejo de errores robusto
 *
 * USO:
 * ```javascript
 * // Batch rendering (recomendado)
 * const buttons = await window.lego.components
 *     .get('icon-button')
 *     .params([
 *         { icon: 'create-outline', variant: 'primary', onClick: 'edit(1)' },
 *         { icon: 'trash-outline', variant: 'danger', onClick: 'delete(1)' }
 *     ]);
 * // buttons = ['<button>...</button>', '<button>...</button>']
 *
 * // Single rendering
 * const html = await window.lego.components
 *     .get('icon-button')
 *     .params({ icon: 'create-outline', variant: 'primary' });
 * ```
 *
 * API ENDPOINTS:
 * - POST /api/components/batch - Renderizado batch (principal)
 * - GET /api/components/render - Renderizado único
 * - GET /api/components/list - Listar componentes (debug)
 */
class DynamicComponentsManager {
    constructor() {
        this.baseUrl = '/api/components';
        this.cache = new Map(); // Caché opcional
        this.cacheEnabled = false; // Deshabilitado por defecto
    }

    /**
     * Obtener componente para renderizar
     *
     * @param {string} componentId ID del componente (ej: 'icon-button')
     * @returns {ComponentRenderer} Builder para configurar renderizado
     */
    get(componentId) {
        return new ComponentRenderer(componentId, this);
    }

    /**
     * Renderizar múltiples instancias de un componente (batch)
     *
     * IMPORTANTE: Retorna HTML en el mismo orden que los parámetros.
     *
     * @param {string} componentId ID del componente
     * @param {Array<Object>} paramsList Array de objetos de parámetros
     * @returns {Promise<Array<string>>} Array de HTMLs renderizados
     */
    async renderBatch(componentId, paramsList) {
        try {
            // Validar entrada
            if (!componentId || typeof componentId !== 'string') {
                throw new Error('componentId debe ser un string');
            }

            if (!Array.isArray(paramsList)) {
                throw new Error('paramsList debe ser un array');
            }

            if (paramsList.length === 0) {
                return [];
            }

            // Request batch
            const response = await fetch(`${this.baseUrl}/batch`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    component: componentId,
                    renders: paramsList
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || `HTTP ${response.status}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error desconocido');
            }

            // Validar que el orden se preservó
            if (data.html.length !== paramsList.length) {
                console.warn(
                    `[DynamicComponents] Order mismatch: ` +
                    `expected ${paramsList.length}, got ${data.html.length}`
                );
            }

            return data.html;

        } catch (error) {
            console.error(`[DynamicComponents] Error en batch rendering:`, error);
            throw error;
        }
    }

    /**
     * Renderizar una única instancia de un componente
     *
     * @param {string} componentId ID del componente
     * @param {Object} params Parámetros del componente
     * @returns {Promise<string>} HTML renderizado
     */
    async renderSingle(componentId, params) {
        try {
            // Usar batch internamente para consistencia
            const results = await this.renderBatch(componentId, [params]);
            return results[0];

        } catch (error) {
            console.error(`[DynamicComponents] Error en single rendering:`, error);
            throw error;
        }
    }

    /**
     * Listar todos los componentes registrados (debug)
     *
     * @returns {Promise<Array<string>>} Lista de IDs de componentes
     */
    async listComponents() {
        try {
            const response = await fetch(`${this.baseUrl}/list`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error desconocido');
            }

            return data.components;

        } catch (error) {
            console.error(`[DynamicComponents] Error listing components:`, error);
            throw error;
        }
    }

    /**
     * Habilitar/deshabilitar caché
     *
     * @param {boolean} enabled
     */
    setCacheEnabled(enabled) {
        this.cacheEnabled = enabled;
        if (!enabled) {
            this.cache.clear();
        }
    }
}

/**
 * ComponentRenderer - Builder pattern para configurar renderizado
 *
 * Permite syntax fluida:
 * window.lego.components.get('icon-button').params([...])
 */
class ComponentRenderer {
    constructor(componentId, manager) {
        this.componentId = componentId;
        this.manager = manager;
    }

    /**
     * Renderizar con parámetros
     *
     * @param {Object|Array<Object>} params Parámetros o array de parámetros
     * @returns {Promise<string|Array<string>>} HTML o array de HTMLs
     */
    async params(params) {
        if (Array.isArray(params)) {
            // Batch rendering
            return await this.manager.renderBatch(this.componentId, params);
        } else {
            // Single rendering
            return await this.manager.renderSingle(this.componentId, params);
        }
    }
}

// Exportar como módulo ES6
export default DynamicComponentsManager;
