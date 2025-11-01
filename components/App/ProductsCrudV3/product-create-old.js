/**
 * Product Create - Lógica de creación
 *
 * FILOSOFÍA LEGO:
 * Formulario con validación client-side y manejo de errores robusto.
 *
 * MEJORAS vs V1/V2:
 * ✅ Usa ApiClient (no fetch directo)
 * ✅ Validación antes de enviar
 * ✅ Cierra módulo al finalizar (closeCurrentModule)
 * ✅ Manejo de errores específico por tipo
 */

import { api } from '/assets/js/core/api/ApiClient.js';

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN CLIENT-SIDE
// ═══════════════════════════════════════════════════════════════════

function validateForm(formData) {
    const errors = {};

    // Nombre requerido
    if (!formData.name || formData.name.trim() === '') {
        errors.name = 'El nombre es requerido';
    }

    // Precio requerido y válido
    if (!formData.price || formData.price <= 0) {
        errors.price = 'El precio debe ser mayor a 0';
    }

    // Stock requerido y válido
    if (formData.stock === undefined || formData.stock < 0) {
        errors.stock = 'El stock debe ser mayor o igual a 0';
    }

    // Categoría requerida
    if (!formData.category || formData.category === '') {
        errors.category = 'La categoría es requerida';
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

// ═══════════════════════════════════════════════════════════════════
// CREAR PRODUCTO
// ═══════════════════════════════════════════════════════════════════

async function createProduct(formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[ProductCreate] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        // Enviar con ApiClient
        const newProduct = await api.post('/api/products', formData);

        console.log('[ProductCreate] Producto creado:', newProduct);
        return newProduct;

    } catch (error) {
        console.error('[ProductCreate] Error creando producto:', error);

        if (error.isValidationError()) {
            // Errores del servidor
            const serverErrors = error.validationErrors || {};
            showValidationErrors(serverErrors);
        } else if (error.isNetworkError()) {
            alert('Error de conexión. Verifica tu internet.');
        } else {
            alert('Error creando producto. Intenta de nuevo.');
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// MOSTRAR ERRORES DE VALIDACIÓN
// ═══════════════════════════════════════════════════════════════════

function showValidationErrors(errors) {
    // Limpiar errores previos
    document.querySelectorAll('.lego-input__error, .lego-select__error').forEach(el => {
        el.remove();
    });

    // Mostrar nuevos errores
    Object.entries(errors).forEach(([field, message]) => {
        const input = document.getElementById(`product-${field}`);
        if (input) {
            const container = input.closest('.lego-input, .lego-select');
            if (container) {
                const errorDiv = document.createElement('div');
                errorDiv.className = field.includes('category') ? 'lego-select__error' : 'lego-input__error';
                errorDiv.textContent = Array.isArray(message) ? message[0] : message;
                container.appendChild(errorDiv);
                container.classList.add(field.includes('category') ? 'lego-select--error' : 'lego-input--error');
            }
        }
    });
}

// ═══════════════════════════════════════════════════════════════════
// CERRAR MÓDULO (volver a tabla)
// ═══════════════════════════════════════════════════════════════════

function closeModule() {
    if (!window.moduleStore) {
        console.error('[ProductCreate] ModuleStore no disponible');
        return;
    }

    const currentModule = window.moduleStore.getActiveModule();
    if (currentModule) {
        window._closeModule(currentModule);
        console.log('[ProductCreate] Módulo cerrado');
    }
}

// ═══════════════════════════════════════════════════════════════════
// RECARGAR TABLA DE PRODUCTOS
// ═══════════════════════════════════════════════════════════════════

function reloadProductsTable() {
    // Buscar el módulo de la tabla
    const tableModule = Object.keys(window.moduleStore.modules).find(id =>
        id.includes('products-crud-v3') && !id.includes('create') && !id.includes('edit')
    );

    if (tableModule) {
        // Recargar usando legoWindowManager
        const previousModule = window.moduleStore.activeModule;
        window.moduleStore.activeModule = tableModule;
        window.legoWindowManager?.reloadActive();
        window.moduleStore.activeModule = previousModule;
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('product-create-form');
    const submitBtn = document.getElementById('product-form-submit-btn');
    const cancelBtn = document.getElementById('product-form-cancel-btn');
    const closeBtn = document.getElementById('product-form-close-btn');

    // Submit form
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Deshabilitar botón mientras se envía
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creando...';

            try {
                // Recoger datos del formulario
                const formData = {
                    name: document.getElementById('product-name')?.value || '',
                    description: document.getElementById('product-description')?.value || '',
                    price: parseFloat(document.getElementById('product-price')?.value || 0),
                    stock: parseInt(document.getElementById('product-stock')?.value || 0),
                    category: window.LegoSelect?.getValue('product-category') || ''
                };

                // Crear producto
                const newProduct = await createProduct(formData);

                if (newProduct) {
                    // Éxito - recargar tabla y cerrar
                    reloadProductsTable();
                    closeModule();
                }

            } finally {
                // Re-habilitar botón
                submitBtn.disabled = false;
                submitBtn.textContent = 'Crear Producto';
            }
        });
    }

    // Botón cancelar
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            if (confirm('¿Descartar cambios?')) {
                closeModule();
            }
        });
    }

    // Botón cerrar
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            if (confirm('¿Descartar cambios?')) {
                closeModule();
            }
        });
    }

    console.log('[ProductCreate] Componente inicializado');
});

// ═══════════════════════════════════════════════════════════════════
// EXPORT
// ═══════════════════════════════════════════════════════════════════

export {
    validateForm,
    createProduct,
    showValidationErrors,
    closeModule,
    reloadProductsTable
};
