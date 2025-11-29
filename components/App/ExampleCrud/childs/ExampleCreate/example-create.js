/**
 * Example Create - Lógica de creación (con ComponentContext)
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - usa ComponentContext
 * ✅ Validación client-side antes de enviar
 * ✅ Cierra módulo al finalizar
 * ✅ Usa fetch con manejo de errores robusto
 */

console.log('[ExampleCreate] Script cargado');

// ═══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DEL COMPONENTE
// ═══════════════════════════════════════════════════════════════════

const COMPONENT_CONFIG = {
    id: 'example-crud-create',
    apiRoute: '/api/example-crud'
};

function apiUrl(action) {
    return `${COMPONENT_CONFIG.apiRoute}/${action}`;
}

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

async function createRecord(formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[ExampleCreate] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        // Enviar con fetch
        const response = await fetch(apiUrl('create'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al crear registro');
        }

        console.log('[ExampleCreate] Registro creado:', result.data);
        return result.data;

    } catch (error) {
        console.error('[ExampleCreate] Error creando registro:', error);

        // Usar AlertService si está disponible
        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al crear registro');
        } else {
            alert('Error creando registro: ' + error.message);
        }

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
        const input = document.getElementById(`example-${field}`);
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
// LIMPIAR FORMULARIO
// ═══════════════════════════════════════════════════════════════════

function clearForm() {
    console.log('[ExampleCreate] Limpiando formulario...');

    // Limpiar campos de texto
    const nameInput = document.getElementById('example-name');
    const descriptionInput = document.getElementById('example-description');
    const priceInput = document.getElementById('example-price');
    const stockInput = document.getElementById('example-stock');

    if (nameInput) nameInput.value = '';
    if (descriptionInput) descriptionInput.value = '';
    if (priceInput) priceInput.value = '';
    if (stockInput) stockInput.value = '';

    // Limpiar select usando LegoSelect
    if (window.LegoSelect) {
        window.LegoSelect.setValue('example-category', '');
    }

    // Limpiar errores de validación
    document.querySelectorAll('.lego-input__error, .lego-select__error, .lego-textarea__error').forEach(el => {
        el.remove();
    });

    document.querySelectorAll('.lego-input--error, .lego-select--error, .lego-textarea--error').forEach(el => {
        el.classList.remove('lego-input--error', 'lego-select--error', 'lego-textarea--error');
    });

    console.log('[ExampleCreate] Formulario limpiado');
}

// ═══════════════════════════════════════════════════════════════════
// CERRAR MÓDULO (volver a tabla)
// ═══════════════════════════════════════════════════════════════════

function closeModule() {
    if (!window.moduleStore) {
        console.error('[ExampleCreate] ModuleStore no disponible');
        return;
    }

    const currentModule = window.moduleStore.getActiveModule();
    if (currentModule && window.lego && window.lego.closeModule) {
        window.lego.closeModule(currentModule);
        console.log('[ExampleCreate] Módulo cerrado');
    }
}

// ═══════════════════════════════════════════════════════════════════
// RECARGAR TABLA DE PRODUCTOS
// ═══════════════════════════════════════════════════════════════════

function reloadExampleCrudTable() {
    // Recargar la tabla usando la función global de refresh
    const refreshFn = window.legoTable_example_crud_table_refresh;

    if (refreshFn) {
        console.log('[ExampleCreate] Recargando tabla de registros...');
        refreshFn();
    } else {
        console.warn('[ExampleCreate] Función de recarga de tabla no encontrada');
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN CON LEGO EVENTS
// ═══════════════════════════════════════════════════════════════════

function initializeForm() {
    console.log('[ExampleCreate] Inicializando formulario...');

    // IMPORTANTE: Buscar elementos SOLO dentro del módulo activo
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[ExampleCreate] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[ExampleCreate] No se encontró container del módulo activo:', activeModuleId);
        return;
    }

    // Buscar elementos DENTRO del módulo activo
    const form = activeModuleContainer.querySelector('#example-create-form');
    const submitBtn = activeModuleContainer.querySelector('#example-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#example-form-cancel-btn');

    if (!form) {
        console.warn('[ExampleCreate] Formulario no encontrado en módulo activo, esperando...');
        return;
    }

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        console.log('[ExampleCreate] Enviando formulario...');

        // Deshabilitar botón mientras se envía
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creando...';

        try {
            // Recoger datos del formulario desde el módulo activo
            const formData = {
                name: activeModuleContainer.querySelector('#example-name')?.value || '',
                description: activeModuleContainer.querySelector('#example-description')?.value || '',
                price: parseFloat(activeModuleContainer.querySelector('#example-price')?.value || 0),
                stock: parseInt(activeModuleContainer.querySelector('#example-stock')?.value || 0),
                category: window.LegoSelect?.getValue('example-category') || ''
            };

            // Obtener IDs de imágenes de FilePond
            const imageIds = window.FilePondComponent?.getImageIds('example-images') || [];
            if (imageIds.length > 0) {
                formData.image_ids = imageIds;
            }

            console.log('[ExampleCreate] Datos del formulario:', formData);

            // Crear registro
            const newRecord = await createRecord(formData);

            if (newRecord) {
                // Éxito - mostrar mensaje
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Registro creado correctamente');
                } else {
                    alert('Registro creado correctamente');
                }

                // Recargar tabla
                reloadExampleCrudTable();

                // Cerrar automáticamente el formulario después de un breve delay
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow();
                    }
                }, 500); // Delay para que el usuario vea el mensaje de éxito
            }

        } catch (error) {
            console.error('[ExampleCreate] Error:', error);
        } finally {
            // Re-habilitar botón
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Registro';
        }
    });

    // Botón cancelar
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeModule();
        });
    }

    console.log('[ExampleCreate] Formulario inicializado correctamente');
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN INMEDIATA
// ═══════════════════════════════════════════════════════════════════

// Intentar inicializar inmediatamente
// Si el DOM no está listo, reintentar cada 50ms hasta 2 segundos
let attempts = 0;
const maxAttempts = 40; // 40 * 50ms = 2 segundos

function tryInitialize() {
    // IMPORTANTE: Buscar el módulo activo PRIMERO para evitar conflictos con otros módulos
    const activeModuleId = window.moduleStore?.getActiveModule();

    if (!activeModuleId) {
        if (attempts < maxAttempts) {
            attempts++;
            console.log(`[ExampleCreate] ModuleStore no disponible, reintentando... (${attempts}/${maxAttempts})`);
            setTimeout(tryInitialize, 50);
        } else {
            console.error('[ExampleCreate] ModuleStore no disponible después de 2 segundos');
        }
        return;
    }

    // Buscar elementos SOLO dentro del módulo activo
    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            console.log(`[ExampleCreate] Container del módulo activo no encontrado, reintentando... (${attempts}/${maxAttempts})`);
            setTimeout(tryInitialize, 50);
        } else {
            console.error('[ExampleCreate] Container del módulo activo no encontrado después de 2 segundos');
        }
        return;
    }

    // Buscar el formulario DENTRO del módulo activo
    const form = activeModuleContainer.querySelector('#example-create-form');

    if (form) {
        console.log('[ExampleCreate] Formulario encontrado en módulo activo, inicializando...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        console.log(`[ExampleCreate] Formulario no encontrado en módulo activo, reintentando... (${attempts}/${maxAttempts})`);
        setTimeout(tryInitialize, 50);
    } else {
        console.error('[ExampleCreate] No se pudo encontrar el formulario después de 2 segundos');
    }
}

// Iniciar
tryInitialize();
