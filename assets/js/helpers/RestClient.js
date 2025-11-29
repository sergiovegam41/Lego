/**
 * @deprecated DEPRECATED - Usar ApiClient en su lugar
 *
 * RestClient ha sido reemplazado por ApiClient (assets/js/core/api/ApiClient.js).
 * Este archivo se mantiene por compatibilidad pero será eliminado en futuras versiones.
 *
 * MIGRACIÓN:
 * 
 * ANTES (RestClient):
 * const api = new RestClient('/api/products');
 * const products = await api.list();
 * await api.create({ name: 'Product' });
 * 
 * AHORA (ApiClient):
 * const api = new ApiClient({ baseURL: '/api/products' });
 * const products = await api.get('/list');
 * await api.post('/create', { name: 'Product' });
 *
 * O usar la instancia global:
 * const products = await window.api.get('/api/products/list');
 */

class RestClient {
    /**
     * @param {string} baseUrl - URL base del API (ej: '/api/products')
     * @param {Object} options - Opciones adicionales
     */
    constructor(baseUrl, options = {}) {
        console.warn('[RestClient] DEPRECATED: Usar ApiClient en su lugar');
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
     * @deprecated Usar ApiClient.get('/list')
     */
    async list(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `/list?${queryString}` : '/list';
        return this.request('GET', url);
    }

    /**
     * @deprecated Usar ApiClient.get('/get?id=X')
     */
    async get(id) {
        return this.request('GET', `/get?id=${id}`);
    }

    /**
     * @deprecated Usar ApiClient.post('/create', data)
     */
    async create(data) {
        return this.request('POST', '/create', data);
    }

    /**
     * @deprecated Usar ApiClient.put('/update', data)
     */
    async update(id, data = null) {
        if (typeof id === 'object' && data === null) {
            data = id;
        } else {
            data = { id, ...data };
        }

        try {
            return await this.request('PUT', '/update', data);
        } catch (error) {
            if (error.status === 405) {
                console.warn('[RestClient] PUT not allowed, falling back to POST');
                return this.request('POST', '/update', data);
            }
            throw error;
        }
    }

    /**
     * @deprecated Usar ApiClient.delete('/delete')
     */
    async delete(id) {
        const data = typeof id === 'object' ? id : { id };

        try {
            return await this.request('DELETE', '/delete', data);
        } catch (error) {
            if (error.status === 405) {
                console.warn('[RestClient] DELETE not allowed, falling back to POST');
                return this.request('POST', '/delete', data);
            }
            throw error;
        }
    }

    /**
     * Realizar petición HTTP genérica
     */
    async request(method, endpoint, data = null) {
        const url = this.baseUrl + endpoint;

        const config = {
            method: method.toUpperCase(),
            headers: { ...this.options.headers }
        };

        if (data && method.toUpperCase() !== 'GET') {
            config.body = JSON.stringify(data);
        }

        try {
            console.log(`[RestClient] ${method} ${url}`, data || '');

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.options.timeout);
            config.signal = controller.signal;

            const response = await fetch(url, config);
            clearTimeout(timeoutId);

            let result;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                if (!response.ok) {
                    throw new Error(text);
                }
                result = { success: response.ok, data: text };
            }

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

            if (error.name === 'AbortError') {
                error.message = `Request timeout (${this.options.timeout}ms)`;
            }

            if (this.options.onError) {
                this.options.onError(error, { method, endpoint, data });
            }

            throw error;
        }
    }

    /**
     * @deprecated Usar ApiClient.request()
     */
    async custom(method, endpoint, data = null) {
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
