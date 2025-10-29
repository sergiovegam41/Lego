/**
 * RestClient - Cliente HTTP estandarizado para APIs REST
 *
 * FILOSOFÍA LEGO:
 * Cliente que abstrae las llamadas HTTP y maneja errores de forma consistente.
 * Proporciona métodos convenientes para operaciones CRUD siguiendo convenciones REST.
 *
 * CARACTERÍSTICAS:
 * - Métodos para operaciones CRUD (list, get, create, update, delete)
 * - Manejo automático de errores HTTP
 * - Soporte para diferentes métodos HTTP (GET, POST, PUT, DELETE)
 * - Fallback automático si el servidor no soporta ciertos métodos
 * - Headers consistentes
 * - Parsing automático de JSON
 *
 * EJEMPLO DE USO:
 * ```javascript
 * const productsApi = new RestClient('/api/products');
 *
 * // Listar
 * const products = await productsApi.list();
 *
 * // Obtener uno
 * const product = await productsApi.get(1);
 *
 * // Crear
 * const newProduct = await productsApi.create({ name: 'Laptop', price: 1000 });
 *
 * // Actualizar
 * const updated = await productsApi.update(1, { price: 900 });
 *
 * // Eliminar
 * await productsApi.delete(1);
 * ```
 */

class RestClient {
    /**
     * @param {string} baseUrl - URL base del API (ej: '/api/products')
     * @param {Object} options - Opciones adicionales
     */
    constructor(baseUrl, options = {}) {
        this.baseUrl = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
        this.options = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            timeout: options.timeout || 30000,
            onError: options.onError || null,
            ...options
        };
    }

    /**
     * GET /list - Obtener listado completo
     * @returns {Promise<Object>} Respuesta del servidor
     */
    async list(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `/list?${queryString}` : '/list';
        return this.request('GET', url);
    }

    /**
     * GET /get?id={id} - Obtener un registro por ID
     * @param {number|string} id - ID del registro
     * @returns {Promise<Object>} Respuesta del servidor
     */
    async get(id) {
        return this.request('GET', `/get?id=${id}`);
    }

    /**
     * POST /create - Crear nuevo registro
     * @param {Object} data - Datos del registro
     * @returns {Promise<Object>} Respuesta del servidor
     */
    async create(data) {
        return this.request('POST', '/create', data);
    }

    /**
     * PUT /update (fallback a POST) - Actualizar registro
     * @param {number|string} id - ID del registro (opcional si viene en data)
     * @param {Object} data - Datos a actualizar
     * @returns {Promise<Object>} Respuesta del servidor
     */
    async update(id, data = null) {
        // Si solo se pasa un objeto, asumimos que trae el ID
        if (typeof id === 'object' && data === null) {
            data = id;
        } else {
            data = { id, ...data };
        }

        // Intentar PUT primero, fallback a POST
        try {
            return await this.request('PUT', '/update', data);
        } catch (error) {
            // Si es 405 Method Not Allowed, intentar con POST
            if (error.status === 405) {
                console.warn('[RestClient] PUT not allowed, falling back to POST');
                return this.request('POST', '/update', data);
            }
            throw error;
        }
    }

    /**
     * DELETE /delete (fallback a POST) - Eliminar registro
     * @param {number|string} id - ID del registro
     * @returns {Promise<Object>} Respuesta del servidor
     */
    async delete(id) {
        const data = typeof id === 'object' ? id : { id };

        // Intentar DELETE primero, fallback a POST
        try {
            return await this.request('DELETE', '/delete', data);
        } catch (error) {
            // Si es 405 Method Not Allowed, intentar con POST
            if (error.status === 405) {
                console.warn('[RestClient] DELETE not allowed, falling back to POST');
                return this.request('POST', '/delete', data);
            }
            throw error;
        }
    }

    /**
     * Realizar petición HTTP genérica
     * @param {string} method - Método HTTP (GET, POST, PUT, DELETE)
     * @param {string} endpoint - Endpoint relativo (ej: '/list')
     * @param {Object} data - Datos a enviar (opcional)
     * @returns {Promise<Object>} Respuesta parseada
     */
    async request(method, endpoint, data = null) {
        const url = this.baseUrl + endpoint;

        const config = {
            method: method.toUpperCase(),
            headers: { ...this.options.headers }
        };

        // Solo agregar body si no es GET
        if (data && method.toUpperCase() !== 'GET') {
            config.body = JSON.stringify(data);
        }

        try {
            console.log(`[RestClient] ${method} ${url}`, data || '');

            // Timeout handling
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.options.timeout);
            config.signal = controller.signal;

            const response = await fetch(url, config);
            clearTimeout(timeoutId);

            // Intentar parsear como JSON
            let result;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                // Si el servidor devolvió texto plano en lugar de JSON
                if (!response.ok) {
                    throw new Error(text);
                }
                result = { success: response.ok, data: text };
            }

            // Si la respuesta no es OK, lanzar error
            if (!response.ok) {
                const error = new Error(result.message || `HTTP ${response.status}: ${response.statusText}`);
                error.status = response.status;
                error.response = result;
                throw error;
            }

            console.log(`[RestClient] Response:`, result);
            return result;

        } catch (error) {
            console.error(`[RestClient] Error en ${method} ${url}:`, error);

            // Manejar errores de red
            if (error.name === 'AbortError') {
                error.message = `Request timeout (${this.options.timeout}ms)`;
            }

            // Callback de error personalizado
            if (this.options.onError) {
                this.options.onError(error, { method, endpoint, data });
            }

            throw error;
        }
    }

    /**
     * Realizar petición personalizada a cualquier endpoint
     * @param {string} method - Método HTTP
     * @param {string} endpoint - Endpoint (puede ser completo o relativo)
     * @param {Object} data - Datos
     * @returns {Promise<Object>}
     */
    async custom(method, endpoint, data = null) {
        // Si el endpoint no empieza con /, es relativo
        if (!endpoint.startsWith('/')) {
            endpoint = '/' + endpoint;
        }
        return this.request(method, endpoint, data);
    }
}

// Exportar para uso en módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RestClient;
}

// Hacer disponible globalmente
window.RestClient = RestClient;
