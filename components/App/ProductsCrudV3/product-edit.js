/**
 * Product Edit - Lógica de edición
 *
 * FILOSOFÍA LEGO:
 * Formulario de edición con carga de datos y validación.
 * Mantiene "las mismas distancias" que ProductCreate.
 *
 * MEJORAS vs V1/V2:
 * ✅ Usa ApiClient para GET y PUT
 * ✅ Carga datos del producto al iniciar
 * ✅ Usa SelectComponent.setValue() sin .click() hack
 * ✅ Validación antes de actualizar
 */

import { api } from '/assets/js/core/api/ApiClient.js';

// Reutilizar validación de product-create
import { validateForm, showValidationErrors } from './product-create.js';

// ═══════════════════════════════════════════════════════════════════
// CARGAR DATOS DEL PRODUCTO
// ═══════════════════════════════════════════════════════════════════

async function loadProductData(productId) {
    try {
        console.log('[ProductEdit] Cargando producto:', productId);

        const product = await api.get(`/api/products/${productId}`);

        console.log('[ProductEdit] Producto cargado:', product);
        return product;

    } catch (error) {
        console.error('[ProductEdit] Error cargando producto:', error);

        if (error.isNotFoundError()) {
            alert('Producto no encontrado');
        } else if (error.isNetworkError()) {
            alert('Error de conexión. Verifica tu internet.');
        } else {
            alert('Error cargando producto');
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// POBLAR FORMULARIO CON DATOS
// ═══════════════════════════════════════════════════════════════════

function populateForm(product) {
    // Poblar inputs
    const nameInput = document.getElementById('product-name');
    const descriptionTextarea = document.getElementById('product-description');
    const priceInput = document.getElementById('product-price');
    const stockInput = document.getElementById('product-stock');

    if (nameInput) nameInput.value = product.name || '';
    if (descriptionTextarea) descriptionTextarea.value = product.description || '';
    if (priceInput) priceInput.value = product.price || '';
    if (stockInput) stockInput.value = product.stock || '';

    // Poblar select usando API sin .click() hack
    if (product.category && window.LegoSelect) {
        // Usar silent mode para no emitir eventos innecesarios
        window.LegoSelect.setValue('product-category', product.category, { silent: true });
    }

    console.log('[ProductEdit] Formulario poblado con datos');
}

// ═══════════════════════════════════════════════════════════════════
// ACTUALIZAR PRODUCTO
// ═══════════════════════════════════════════════════════════════════

async function updateProduct(productId, formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[ProductEdit] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        // Actualizar con ApiClient (PUT)
        const updatedProduct = await api.put(`/api/products/${productId}`, formData);

        console.log('[ProductEdit] Producto actualizado:', updatedProduct);
        return updatedProduct;

    } catch (error) {
        console.error('[ProductEdit] Error actualizando producto:', error);

        if (error.isValidationError()) {
            const serverErrors = error.validationErrors || {};
            showValidationErrors(serverErrors);
        } else if (error.isNetworkError()) {
            alert('Error de conexión. Verifica tu internet.');
        } else {
            alert('Error actualizando producto. Intenta de nuevo.');
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// CERRAR MÓDULO (volver a tabla)
// ═══════════════════════════════════════════════════════════════════

function closeModule() {
    if (!window.moduleStore) {
        console.error('[ProductEdit] ModuleStore no disponible');
        return;
    }

    const currentModule = window.moduleStore.getActiveModule();
    if (currentModule) {
        window._closeModule(currentModule);
        console.log('[ProductEdit] Módulo cerrado');
    }
}

// ═══════════════════════════════════════════════════════════════════
// RECARGAR TABLA DE PRODUCTOS
// ═══════════════════════════════════════════════════════════════════

function reloadProductsTable() {
    const tableModule = Object.keys(window.moduleStore.modules).find(id =>
        id.includes('products-crud-v3') && !id.includes('create') && !id.includes('edit')
    );

    if (tableModule) {
        const previousModule = window.moduleStore.activeModule;
        window.moduleStore.activeModule = tableModule;
        window.legoWindowManager?.reloadActive();
        window.moduleStore.activeModule = previousModule;
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', async function() {
    const container = document.querySelector('.product-form');
    const productId = container?.getAttribute('data-product-id');

    if (!productId) {
        console.error('[ProductEdit] No se encontró ID de producto');
        return;
    }

    const form = document.getElementById('product-edit-form');
    const submitBtn = document.getElementById('product-form-submit-btn');
    const cancelBtn = document.getElementById('product-form-cancel-btn');
    const closeBtn = document.getElementById('product-form-close-btn');
    const loading = document.getElementById('product-form-loading');

    // Cargar datos del producto
    try {
        const product = await loadProductData(productId);

        // Ocultar loading, mostrar form
        if (loading) loading.style.display = 'none';
        if (form) form.style.display = 'flex';

        // Poblar formulario
        populateForm(product);

    } catch (error) {
        console.error('[ProductEdit] Error en inicialización:', error);
        if (loading) {
            loading.textContent = 'Error cargando producto';
        }
        return;
    }

    // Submit form
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Deshabilitar botón mientras se envía
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';

            try {
                // Recoger datos del formulario
                const formData = {
                    name: document.getElementById('product-name')?.value || '',
                    description: document.getElementById('product-description')?.value || '',
                    price: parseFloat(document.getElementById('product-price')?.value || 0),
                    stock: parseInt(document.getElementById('product-stock')?.value || 0),
                    category: window.LegoSelect?.getValue('product-category') || ''
                };

                // Actualizar producto
                const updatedProduct = await updateProduct(productId, formData);

                if (updatedProduct) {
                    // Éxito - recargar tabla y cerrar
                    reloadProductsTable();
                    closeModule();
                }

            } finally {
                // Re-habilitar botón
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar Cambios';
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

    console.log('[ProductEdit] Componente inicializado');
});

// ═══════════════════════════════════════════════════════════════════
// EXPORT
// ═══════════════════════════════════════════════════════════════════

export {
    loadProductData,
    populateForm,
    updateProduct,
    closeModule,
    reloadProductsTable
};
