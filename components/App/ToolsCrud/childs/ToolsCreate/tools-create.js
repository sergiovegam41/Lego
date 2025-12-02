/**
 * Tools Create - Lógica de creación
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - usa configuración
 * ✅ Validación client-side antes de enviar
 * ✅ Manejo de características dinámicas
 * ✅ Cierra módulo al finalizar
 */

console.log('[ToolsCreate] Script cargado');

// ═══════════════════════════════════════════════════════════════════
// SCREEN CONFIG
// ═══════════════════════════════════════════════════════════════════

const TOOLS_CREATE_CONFIG = {
    screenId: 'tools-crud-create',
    parentScreenId: 'tools-crud-list',
    menuGroupId: 'tools-crud',
    apiRoute: '/api/tools'
};

function toolsCreateApiUrl(action) {
    return `${TOOLS_CREATE_CONFIG.apiRoute}/${action}`;
}

// ═══════════════════════════════════════════════════════════════════
// GESTIÓN DE CARACTERÍSTICAS
// ═══════════════════════════════════════════════════════════════════

/**
 * Agregar nueva característica
 */
window.addFeature = function() {
    const container = document.getElementById('tool-features-container');
    if (!container) return;

    const index = container.children.length;
    const item = document.createElement('div');
    item.className = 'tools-form__feature-item';
    item.dataset.index = index;
    item.innerHTML = `
        <input 
            type="text" 
            class="tools-form__feature-input" 
            name="features[]" 
            placeholder="Ej: Material de acero inoxidable"
        >
        <button type="button" class="tools-form__feature-remove" onclick="removeFeature(this)" title="Eliminar">
            <ion-icon name="close-outline"></ion-icon>
        </button>
    `;
    container.appendChild(item);

    // Focus en el nuevo input
    item.querySelector('input').focus();
};

/**
 * Eliminar característica
 */
window.removeFeature = function(button) {
    const container = document.getElementById('tool-features-container');
    const item = button.closest('.tools-form__feature-item');
    
    // No permitir eliminar si es la última
    if (container && container.children.length > 1) {
        item.remove();
    } else {
        // Limpiar el input en lugar de eliminar
        item.querySelector('input').value = '';
    }
};

/**
 * Obtener todas las características
 */
function getFeatures() {
    const inputs = document.querySelectorAll('.tools-form__feature-input');
    const features = [];
    
    inputs.forEach(input => {
        const value = input.value.trim();
        if (value) {
            features.push(value);
        }
    });
    
    return features;
}

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN
// ═══════════════════════════════════════════════════════════════════

function validateToolsForm(formData) {
    const errors = {};

    if (!formData.name || formData.name.trim() === '') {
        errors.name = 'El nombre es requerido';
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

function showToolsValidationErrors(errors, activeModuleContainer) {
    // Limpiar errores previos
    activeModuleContainer.querySelectorAll('.lego-input__error, .lego-textarea__error').forEach(el => {
        el.remove();
    });

    activeModuleContainer.querySelectorAll('.lego-input--error, .lego-textarea--error').forEach(el => {
        el.classList.remove('lego-input--error', 'lego-textarea--error');
    });

    // Mostrar nuevos errores
    Object.entries(errors).forEach(([field, message]) => {
        const input = activeModuleContainer.querySelector(`#tool-${field}`);
        if (input) {
            const container = input.closest('.lego-input, .lego-textarea');
            if (container) {
                const errorDiv = document.createElement('div');
                const errorClass = container.classList.contains('lego-textarea') 
                    ? 'lego-textarea__error' 
                    : 'lego-input__error';
                const containerErrorClass = container.classList.contains('lego-textarea') 
                    ? 'lego-textarea--error' 
                    : 'lego-input--error';

                errorDiv.className = errorClass;
                errorDiv.textContent = Array.isArray(message) ? message[0] : message;
                container.appendChild(errorDiv);
                container.classList.add(containerErrorClass);
            }
        }
    });
}

// ═══════════════════════════════════════════════════════════════════
// CREAR HERRAMIENTA
// ═══════════════════════════════════════════════════════════════════

async function createTool(formData, activeModuleContainer) {
    try {
        const validation = validateToolsForm(formData);
        if (!validation.isValid) {
            console.error('[ToolsCreate] Validación fallida:', validation.errors);
            showToolsValidationErrors(validation.errors, activeModuleContainer);
            return null;
        }

        const response = await fetch(toolsCreateApiUrl('create'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al crear herramienta');
        }

        console.log('[ToolsCreate] Herramienta creada:', result.data);
        return result.data;

    } catch (error) {
        console.error('[ToolsCreate] Error creando herramienta:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al crear herramienta');
        } else {
            alert('Error creando herramienta: ' + error.message);
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// CERRAR MÓDULO
// ═══════════════════════════════════════════════════════════════════

function closeToolsModule() {
    if (window.legoWindowManager) {
        window.legoWindowManager.closeCurrentWindow();
        console.log('[ToolsCreate] Módulo cerrado');
    } else {
        console.error('[ToolsCreate] legoWindowManager no disponible');
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

function initializeToolsForm() {
    console.log('[ToolsCreate] Inicializando formulario...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[ToolsCreate] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[ToolsCreate] No se encontró container del módulo activo');
        return;
    }

    const form = activeModuleContainer.querySelector('#tools-create-form');
    const submitBtn = activeModuleContainer.querySelector('#tools-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#tools-form-cancel-btn');

    if (!form) {
        console.warn('[ToolsCreate] Formulario no encontrado');
        return;
    }

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        console.log('[ToolsCreate] Enviando formulario...');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Creando...';

        try {
            const formData = {
                name: activeModuleContainer.querySelector('#tool-name')?.value || '',
                description: activeModuleContainer.querySelector('#tool-description')?.value || '',
                features: getFeatures()
            };

            // Obtener IDs de imágenes de FilePond
            const imageIds = window.FilePondComponent?.getImageIds('tool-images') || [];
            if (imageIds.length > 0) {
                formData.image_ids = imageIds;
            }

            console.log('[ToolsCreate] Datos del formulario:', formData);

            const newTool = await createTool(formData, activeModuleContainer);

            if (newTool) {
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Herramienta creada correctamente');
                } else {
                    alert('Herramienta creada correctamente');
                }

                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow({ refresh: true });
                    }
                }, 500);
            }

        } catch (error) {
            console.error('[ToolsCreate] Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Herramienta';
        }
    });

    // Botón cancelar
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeToolsModule();
        });
    }

    console.log('[ToolsCreate] Formulario inicializado correctamente');
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN CON RETRY
// ═══════════════════════════════════════════════════════════════════

let attempts = 0;
const maxAttempts = 40;

function tryInitialize() {
    const activeModuleId = window.moduleStore?.getActiveModule();

    if (!activeModuleId) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInitialize, 50);
        }
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInitialize, 50);
        }
        return;
    }

    const form = activeModuleContainer.querySelector('#tools-create-form');

    if (form) {
        console.log('[ToolsCreate] Formulario encontrado, inicializando...');
        initializeToolsForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    } else {
        console.error('[ToolsCreate] No se pudo encontrar el formulario');
    }
}

tryInitialize();

