/**
 * Product Create - Lógica de creación
 *
 * FILOSOFÍA LEGO:
 * Formulario con validación client-side y manejo de errores robusto.
 *
 * MEJORAS vs V1/V2:
 * ✅ Validación client-side antes de enviar
 * ✅ Cierra módulo al finalizar
 * ✅ Usa fetch con manejo de errores robusto
 * ✅ Usa window.LegoSelect sin .click() hacks
 */

console.log('[ProductCreate] Inicializando...');

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

        // Enviar con fetch
        const response = await fetch('/api/products', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al crear producto');
        }

        console.log('[ProductCreate] Producto creado:', result.data);
        return result.data;

    } catch (error) {
        console.error('[ProductCreate] Error creando producto:', error);
        alert('Error creando producto: ' + error.message);
        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// MOSTRAR ERRORES DE VALIDACIÓN
// ═══════════════════════════════════════════════════════════════════

function showValidationErrors(errors) {
    // Limpiar errores previos
    document.querySelectorAll('.lego-input__error, .lego-select__error, .lego-textarea__error').forEach(el => {
        el.remove();
    });

    document.querySelectorAll('.lego-input--error, .lego-select--error, .lego-textarea--error').forEach(el => {
        el.classList.remove('lego-input--error', 'lego-select--error', 'lego-textarea--error');
    });

    // Mostrar nuevos errores
    Object.entries(errors).forEach(([field, message]) => {
        const input = document.getElementById(`product-${field}`);
        if (input) {
            const container = input.closest('.lego-input, .lego-select, .lego-textarea');
            if (container) {
                const errorDiv = document.createElement('div');

                // Determinar tipo de campo
                let errorClass = 'lego-input__error';
                let containerErrorClass = 'lego-input--error';
                if (container.classList.contains('lego-select')) {
                    errorClass = 'lego-select__error';
                    containerErrorClass = 'lego-select--error';
                } else if (container.classList.contains('lego-textarea')) {
                    errorClass = 'lego-textarea__error';
                    containerErrorClass = 'lego-textarea--error';
                }

                errorDiv.className = errorClass;
                errorDiv.textContent = Array.isArray(message) ? message[0] : message;
                container.appendChild(errorDiv);
                container.classList.add(containerErrorClass);
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
    console.log('[ProductCreate] DOM listo, configurando...');

    const form = document.getElementById('product-create-form');
    const submitBtn = document.getElementById('product-form-submit-btn');
    const cancelBtn = document.getElementById('product-form-cancel-btn');

    // Submit form
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            console.log('[ProductCreate] Enviando formulario...');

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

                console.log('[ProductCreate] Datos del formulario:', formData);

                // Crear producto
                const newProduct = await createProduct(formData);

                if (newProduct) {
                    // Éxito - mostrar mensaje
                    alert('Producto creado correctamente');

                    // Recargar tabla y cerrar
                    reloadProductsTable();
                    closeModule();
                }

            } catch (error) {
                console.error('[ProductCreate] Error:', error);
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

    console.log('[ProductCreate] Componente listo');
});
