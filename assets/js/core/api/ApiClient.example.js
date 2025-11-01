/**
 * ApiClient - Ejemplos de uso
 *
 * Archivo de referencia que muestra cómo usar el ApiClient
 * correctamente en diferentes escenarios.
 */

import { ApiClient, ApiError, api } from './ApiClient.js';

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 1: Uso básico con instancia global
// ═══════════════════════════════════════════════════════════════════

async function ejemploBasico() {
    try {
        // GET request simple
        const products = await api.get('/api/products');
        console.log('Productos:', products);

        // GET con query params
        const filtered = await api.get('/api/products?category=electronics&limit=10');
        console.log('Productos filtrados:', filtered);

    } catch (error) {
        console.error('Error:', error.message);
    }
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 2: POST request con validación
// ═══════════════════════════════════════════════════════════════════

async function ejemploPost() {
    try {
        const newProduct = await api.post('/api/products', {
            name: 'Laptop',
            price: 999.99,
            category: 'electronics'
        });

        console.log('Producto creado:', newProduct);

    } catch (error) {
        if (error instanceof ApiError) {
            if (error.isValidationError()) {
                console.error('Errores de validación:', error.validationErrors);
                // { name: ['required'], price: ['must be positive'] }
            } else if (error.isAuthError()) {
                console.error('No autenticado - redirigir a login');
            } else if (error.isServerError()) {
                console.error('Error del servidor - reintentar más tarde');
            }
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 3: PUT/PATCH para actualizar
// ═══════════════════════════════════════════════════════════════════

async function ejemploUpdate() {
    try {
        // PUT - reemplazo completo
        const updated = await api.put('/api/products/123', {
            name: 'Laptop Pro',
            price: 1299.99,
            category: 'electronics'
        });

        // PATCH - actualización parcial
        const patched = await api.patch('/api/products/123', {
            price: 1199.99  // Solo actualizar precio
        });

        console.log('Actualizado:', updated);

    } catch (error) {
        console.error('Error actualizando:', error.message);
    }
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 4: DELETE request
// ═══════════════════════════════════════════════════════════════════

async function ejemploDelete() {
    try {
        await api.delete('/api/products/123');
        console.log('Producto eliminado');

    } catch (error) {
        if (error.isNotFoundError()) {
            console.error('Producto no encontrado');
        } else {
            console.error('Error eliminando:', error.message);
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 5: Instancia personalizada con configuración
// ═══════════════════════════════════════════════════════════════════

function ejemploInstanciaPersonalizada() {
    const apiV2 = new ApiClient({
        baseURL: '/api/v2',
        timeout: 10000,  // 10 segundos
        headers: {
            'X-Custom-Header': 'value'
        }
    });

    return apiV2.get('/products');
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 6: Request interceptors (autenticación)
// ═══════════════════════════════════════════════════════════════════

function ejemploInterceptors() {
    // Agregar token a todas las requests
    api.addRequestInterceptor(async (config) => {
        const token = localStorage.getItem('authToken');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    });

    // Logging de requests
    api.addRequestInterceptor(async (config) => {
        console.log(`[API] ${config.method} ${config.url}`);
        return config;
    });

    // Manejar respuestas 401 globalmente
    api.addResponseInterceptor(async (response) => {
        if (response.status === 401) {
            // Redirigir a login
            window.location.href = '/login';
        }
        return response;
    });
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 7: FormData (para upload de archivos)
// ═══════════════════════════════════════════════════════════════════

async function ejemploFormData() {
    const formData = new FormData();
    formData.append('name', 'Product with image');
    formData.append('price', 99.99);
    formData.append('image', fileInput.files[0]);

    try {
        const result = await api.post('/api/products', formData);
        console.log('Producto con imagen creado:', result);
    } catch (error) {
        console.error('Error:', error.message);
    }
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 8: Diferentes tipos de respuesta
// ═══════════════════════════════════════════════════════════════════

async function ejemploResponseTypes() {
    // HTML response
    const html = await api.get('/component/products-table', {
        responseType: 'text'
    });

    // Blob (para descargas)
    const blob = await api.get('/api/products/export.pdf', {
        responseType: 'blob'
    });

    // Download del blob
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'products.pdf';
    a.click();
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 9: Manejo completo de errores
// ═══════════════════════════════════════════════════════════════════

async function ejemploManejoErrores() {
    try {
        const data = await api.get('/api/products');
        return data;

    } catch (error) {
        if (!(error instanceof ApiError)) {
            // Error inesperado (no ApiError)
            console.error('Error inesperado:', error);
            throw error;
        }

        // Errores de red
        if (error.isNetworkError()) {
            console.error('Sin conexión a internet');
            return { error: 'offline', retry: true };
        }

        // Errores específicos por código
        switch (error.status) {
            case 400:
                console.error('Datos inválidos:', error.validationErrors);
                return { error: 'validation', errors: error.validationErrors };

            case 401:
                console.error('No autenticado');
                window.location.href = '/login';
                break;

            case 403:
                console.error('Sin permisos');
                return { error: 'forbidden' };

            case 404:
                console.error('Recurso no encontrado');
                return { error: 'not_found' };

            case 500:
            case 502:
            case 503:
                console.error('Error del servidor');
                return { error: 'server', retry: true };

            default:
                console.error('Error desconocido:', error.message);
                return { error: 'unknown' };
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// EJEMPLO 10: Uso en ProductsCrudV3 (reemplaza fetch directo)
// ═══════════════════════════════════════════════════════════════════

async function ejemploProductsCrud() {
    try {
        // ❌ ANTES (V2): fetch sin validación
        // const response = await fetch(url, { method: 'GET' });
        // const html = await response.text();

        // ✅ AHORA (V3): ApiClient con validación
        const html = await api.get('/component/products-crud-v3/product-form-page', {
            responseType: 'text'
        });

        return html;

    } catch (error) {
        if (error.isNetworkError()) {
            console.error('[ProductsCrud] Sin conexión');
            // Mostrar mensaje al usuario
        } else if (error.isServerError()) {
            console.error('[ProductsCrud] Error del servidor');
            // Reintentar o mostrar error
        }
        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// EXPORT para uso en otros módulos
// ═══════════════════════════════════════════════════════════════════

export {
    ejemploBasico,
    ejemploPost,
    ejemploUpdate,
    ejemploDelete,
    ejemploInstanciaPersonalizada,
    ejemploInterceptors,
    ejemploFormData,
    ejemploResponseTypes,
    ejemploManejoErrores,
    ejemploProductsCrud
};
