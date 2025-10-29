/**
 * ApiClient - Cliente HTTP agnóstico
 *
 * FILOSOFÍA LEGO:
 * Cliente genérico para comunicarse con cualquier API REST.
 * No tiene referencias hardcodeadas a ninguna entidad específica.
 *
 * USO:
 * const api = new ApiClient('/api/products');
 * await api.list();
 * await api.create({ name: 'Producto 1' });
 */

class ApiClient {
    constructor(baseUrl) {
        if (!baseUrl) throw new Error('baseUrl es requerido');
        this.baseUrl = baseUrl;
    }

    /**
     * GET /list - Obtener todos los registros
     */
    async list() {
        try {
            const response = await fetch(`${this.baseUrl}/list`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('[ApiClient] Error en list():', error);
            throw error;
        }
    }

    /**
     * POST /get - Obtener un registro por ID
     */
    async get(id) {
        try {
            const response = await fetch(`${this.baseUrl}/get`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('[ApiClient] Error en get():', error);
            throw error;
        }
    }

    /**
     * POST /create - Crear nuevo registro
     */
    async create(data) {
        try {
            const response = await fetch(`${this.baseUrl}/create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('[ApiClient] Error en create():', error);
            throw error;
        }
    }

    /**
     * POST /update - Actualizar registro
     */
    async update(data) {
        try {
            const response = await fetch(`${this.baseUrl}/update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('[ApiClient] Error en update():', error);
            throw error;
        }
    }

    /**
     * POST /delete - Eliminar registro
     */
    async delete(id) {
        try {
            const response = await fetch(`${this.baseUrl}/delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('[ApiClient] Error en delete():', error);
            throw error;
        }
    }

    /**
     * Método genérico para cualquier endpoint
     */
    async call(method, endpoint, data = null) {
        try {
            const options = {
                method: method.toUpperCase(),
                headers: { 'Content-Type': 'application/json' }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(`${this.baseUrl}/${endpoint}`, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error(`[ApiClient] Error en call(${method}, ${endpoint}):`, error);
            throw error;
        }
    }
}

// Exportar para uso en navegador o módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ApiClient;
}
