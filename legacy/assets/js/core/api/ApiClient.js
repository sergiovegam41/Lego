/**
 * ApiClient - Cliente HTTP centralizado con validación
 *
 * FILOSOFÍA LEGO:
 * Cliente HTTP robusto que valida respuestas y maneja errores
 * de forma consistente en toda la aplicación.
 *
 * PROBLEMAS RESUELTOS:
 * ❌ ANTES: fetch sin validación de response.ok
 * ✅ AHORA: Validación automática con errores tipo-safe
 *
 * ❌ ANTES: POST usado para GET (antipatrón)
 * ✅ AHORA: Métodos HTTP correctos (GET, POST, PUT, DELETE)
 *
 * ❌ ANTES: Manejo de errores inconsistente
 * ✅ AHORA: ApiError centralizado con tipos específicos
 *
 * CONSISTENCIA DIMENSIONAL:
 * "Las distancias importan" - todas las llamadas API usan
 * la misma interfaz, manteniendo proporciones de código.
 *
 * EJEMPLO:
 * const api = new ApiClient({ baseURL: '/api' });
 *
 * // GET request
 * const products = await api.get('/products');
 *
 * // POST request
 * const newProduct = await api.post('/products', {
 *     name: 'Product',
 *     price: 100
 * });
 *
 * // Error handling
 * try {
 *     await api.get('/products');
 * } catch (error) {
 *     if (error.isNetworkError()) {
 *     } else if (error.isValidationError()) {
 *     }
 * }
 */

/**
 * ApiError - Error tipo-safe para respuestas HTTP
 */
export class ApiError extends Error {
    constructor(message, response, data = null) {
        super(message);
        this.name = 'ApiError';
        this.response = response;
        this.status = response?.status;
        this.data = data;
    }

    /**
     * Error de red (sin respuesta del servidor)
     */
    isNetworkError() {
        return !this.response;
    }

    /**
     * Error de validación (400)
     */
    isValidationError() {
        return this.status === 400;
    }

    /**
     * Error de autenticación (401)
     */
    isAuthError() {
        return this.status === 401;
    }

    /**
     * Error de autorización (403)
     */
    isForbiddenError() {
        return this.status === 403;
    }

    /**
     * Recurso no encontrado (404)
     */
    isNotFoundError() {
        return this.status === 404;
    }

    /**
     * Error del servidor (5xx)
     */
    isServerError() {
        return this.status >= 500 && this.status < 600;
    }

    /**
     * Obtener errores de validación si existen
     */
    get validationErrors() {
        if (this.isValidationError() && this.data?.errors) {
            return this.data.errors;
        }
        return null;
    }
}

/**
 * ApiClient - Cliente HTTP con validación
 */
export class ApiClient {
    constructor(options = {}) {
        this.baseURL = options.baseURL || '';
        this.timeout = options.timeout || 30000; // 30s default
        this.credentials = options.credentials || 'include';
        this.headers = options.headers || {};

        // Interceptors
        this.requestInterceptors = [];
        this.responseInterceptors = [];
    }

    /**
     * Agregar interceptor de request
     */
    addRequestInterceptor(interceptor) {
        this.requestInterceptors.push(interceptor);
    }

    /**
     * Agregar interceptor de response
     */
    addResponseInterceptor(interceptor) {
        this.responseInterceptors.push(interceptor);
    }

    /**
     * GET request
     */
    async get(url, options = {}) {
        return this.request(url, {
            ...options,
            method: 'GET'
        });
    }

    /**
     * POST request
     */
    async post(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'POST',
            body: data
        });
    }

    /**
     * PUT request
     */
    async put(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'PUT',
            body: data
        });
    }

    /**
     * DELETE request
     */
    async delete(url, options = {}) {
        return this.request(url, {
            ...options,
            method: 'DELETE'
        });
    }

    /**
     * PATCH request
     */
    async patch(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'PATCH',
            body: data
        });
    }

    /**
     * Request principal con validación
     */
    async request(url, options = {}) {
        const fullURL = this.buildURL(url);
        let config = this.buildConfig(options);

        // Aplicar request interceptors
        for (const interceptor of this.requestInterceptors) {
            config = await interceptor(config);
        }

        try {
            // Crear AbortController para timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.timeout);

            // Ejecutar request
            let response = await fetch(fullURL, {
                ...config,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            // Aplicar response interceptors
            for (const interceptor of this.responseInterceptors) {
                response = await interceptor(response);
            }

            // Validar respuesta
            if (!response.ok) {
                await this.handleErrorResponse(response);
            }

            // Parsear respuesta según Content-Type
            return await this.parseResponse(response, options.responseType);

        } catch (error) {
            // Manejo de errores de red
            if (error.name === 'AbortError') {
                throw new ApiError(
                    `Request timeout after ${this.timeout}ms`,
                    null
                );
            }

            if (error instanceof ApiError) {
                throw error;
            }

            throw new ApiError(
                'Network error: ' + error.message,
                null
            );
        }
    }

    /**
     * Construir URL completa
     */
    buildURL(url) {
        if (url.startsWith('http://') || url.startsWith('https://')) {
            return url;
        }
        return this.baseURL + url;
    }

    /**
     * Construir configuración de fetch
     */
    buildConfig(options) {
        const config = {
            method: options.method || 'GET',
            credentials: options.credentials || this.credentials,
            headers: {
                ...this.headers,
                ...options.headers
            }
        };

        // Agregar body si existe
        if (options.body) {
            if (options.body instanceof FormData) {
                // FormData - no establecer Content-Type (el browser lo hace)
                config.body = options.body;
            } else if (typeof options.body === 'object') {
                // JSON
                config.headers['Content-Type'] = 'application/json';
                config.body = JSON.stringify(options.body);
            } else {
                // String u otro
                config.body = options.body;
            }
        }

        return config;
    }

    /**
     * Parsear respuesta según Content-Type
     */
    async parseResponse(response, responseType) {
        // Si se especificó tipo de respuesta
        if (responseType === 'text') {
            return await response.text();
        }
        if (responseType === 'blob') {
            return await response.blob();
        }
        if (responseType === 'arrayBuffer') {
            return await response.arrayBuffer();
        }

        // Auto-detectar por Content-Type
        const contentType = response.headers.get('Content-Type') || '';

        if (contentType.includes('application/json')) {
            return await response.json();
        }

        if (contentType.includes('text/')) {
            return await response.text();
        }

        // Default: intentar JSON, si falla devolver texto
        try {
            return await response.json();
        } catch {
            return await response.text();
        }
    }

    /**
     * Manejar respuesta de error
     */
    async handleErrorResponse(response) {
        let errorData = null;

        try {
            const contentType = response.headers.get('Content-Type') || '';
            if (contentType.includes('application/json')) {
                errorData = await response.json();
            } else {
                errorData = await response.text();
            }
        } catch {
            // Ignorar errores al parsear error data
        }

        const message = errorData?.message || `HTTP ${response.status}: ${response.statusText}`;

        throw new ApiError(message, response, errorData);
    }
}

/**
 * Instancia global por defecto
 */
export const api = new ApiClient();

/**
 * Exponer en window para uso sin módulos
 */
if (typeof window !== 'undefined') {
    window.ApiClient = ApiClient;
    window.ApiError = ApiError;
    window.api = api;
}
