/**
 * Tools Edit - Lógica de edición
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - usa configuración
 * ✅ Carga datos de la herramienta al iniciar
 * ✅ Manejo de características dinámicas
 * ✅ Validación antes de actualizar
 */

// ═══════════════════════════════════════════════════════════════════
// SCREEN CONFIG
// ═══════════════════════════════════════════════════════════════════

const TOOLS_EDIT_CONFIG = {
    screenId: 'tools-crud-edit',
    parentScreenId: 'tools-crud-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    apiRoute: '/api/tools',
    isDynamic: true
};

function toolsEditApiUrl(action, params = null) {
    let url = `${TOOLS_EDIT_CONFIG.apiRoute}/${action}`;
    if (params && Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    return url;
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
    item.querySelector('input').focus();
};

/**
 * Eliminar característica
 */
window.removeFeature = function(button) {
    const container = document.getElementById('tool-features-container');
    const item = button.closest('.tools-form__feature-item');
    
    if (container && container.children.length > 1) {
        item.remove();
    } else {
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

/**
 * Poblar características existentes
 */
function populateFeatures(features, container) {
    container.innerHTML = '';
    
    if (!features || features.length === 0) {
        // Agregar un campo vacío si no hay características
        const item = document.createElement('div');
        item.className = 'tools-form__feature-item';
        item.dataset.index = 0;
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
        return;
    }

    features.forEach((feature, index) => {
        const item = document.createElement('div');
        item.className = 'tools-form__feature-item';
        item.dataset.index = index;
        item.innerHTML = `
            <input 
                type="text" 
                class="tools-form__feature-input" 
                name="features[]" 
                placeholder="Ej: Material de acero inoxidable"
                value="${feature.replace(/"/g, '&quot;')}"
            >
            <button type="button" class="tools-form__feature-remove" onclick="removeFeature(this)" title="Eliminar">
                <ion-icon name="close-outline"></ion-icon>
            </button>
        `;
        container.appendChild(item);
    });
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
    activeModuleContainer.querySelectorAll('.lego-input__error, .lego-textarea__error').forEach(el => {
        el.remove();
    });

    activeModuleContainer.querySelectorAll('.lego-input--error, .lego-textarea--error').forEach(el => {
        el.classList.remove('lego-input--error', 'lego-textarea--error');
    });

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
// CARGAR DATOS
// ═══════════════════════════════════════════════════════════════════

async function loadToolData(toolId) {
    try {

        const response = await fetch(toolsEditApiUrl('get', { id: toolId }));
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al cargar herramienta');
        }

        return result.data;

    } catch (error) {
        console.error('[ToolsEdit] Error cargando herramienta:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al cargar herramienta');
        } else {
            alert('Error cargando herramienta: ' + error.message);
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// POBLAR FORMULARIO
// ═══════════════════════════════════════════════════════════════════

function populateToolForm(tool, activeModuleContainer) {

    const nameInput = activeModuleContainer.querySelector('#tool-name');
    const descriptionTextarea = activeModuleContainer.querySelector('#tool-description');
    const featuresContainer = activeModuleContainer.querySelector('#tool-features-container');

    if (nameInput) nameInput.value = tool.name || '';
    if (descriptionTextarea) descriptionTextarea.value = tool.description || '';

    // Poblar características
    if (featuresContainer) {
        populateFeatures(tool.features_list || [], featuresContainer);
    }

}

// ═══════════════════════════════════════════════════════════════════
// ACTUALIZAR HERRAMIENTA
// ═══════════════════════════════════════════════════════════════════

async function updateTool(toolId, formData, activeModuleContainer) {
    try {
        const validation = validateToolsForm(formData);
        if (!validation.isValid) {
            console.error('[ToolsEdit] Validación fallida:', validation.errors);
            showToolsValidationErrors(validation.errors, activeModuleContainer);
            return null;
        }


        const response = await fetch(toolsEditApiUrl('update'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: toolId,
                ...formData
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al actualizar herramienta');
        }

        return result.data;

    } catch (error) {
        console.error('[ToolsEdit] Error actualizando herramienta:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al actualizar herramienta');
        } else {
            alert('Error actualizando herramienta: ' + error.message);
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
    } else {
        console.error('[ToolsEdit] legoWindowManager no disponible');
    }
}

// ═══════════════════════════════════════════════════════════════════
// CARGAR IMÁGENES EN FILEPOND
// ═══════════════════════════════════════════════════════════════════

function loadToolImages(images) {

    const waitForFilePond = setInterval(() => {
        const pond = window.FilePondComponent?.getInstance('tool-images');

        if (pond) {
            clearInterval(waitForFilePond);

            images.forEach(image => {
                pond.addFile(image.id.toString(), {
                    type: 'local',
                    file: {
                        name: image.original_name || 'image.jpg',
                        size: image.size || 0,
                        type: image.mime_type || 'image/jpeg'
                    },
                    metadata: {
                        poster: image.url,
                        imageId: image.id
                    }
                }).then(file => {
                }).catch(error => {
                    console.error('[ToolsEdit] Error agregando imagen:', error);
                });
            });
        }
    }, 100);

    setTimeout(() => {
        clearInterval(waitForFilePond);
    }, 5000);
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

async function initializeToolsEditForm() {

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[ToolsEdit] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[ToolsEdit] No se encontró container del módulo activo');
        return;
    }

    // Verificar estado sin contexto
    const emptyState = activeModuleContainer.querySelector('.tools-form--no-context');
    if (emptyState) {
        return;
    }

    const container = activeModuleContainer.querySelector('.tools-form[data-tool-id]');
    const toolId = container?.getAttribute('data-tool-id');

    if (!toolId) {
        return;
    }

    const form = activeModuleContainer.querySelector('#tools-edit-form');
    const submitBtn = activeModuleContainer.querySelector('#tools-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#tools-form-cancel-btn');
    const loading = activeModuleContainer.querySelector('#tools-form-loading');

    if (!form || !loading) {
        console.warn('[ToolsEdit] Elementos no encontrados');
        return;
    }

    // Cargar datos
    try {
        const tool = await loadToolData(toolId);

        loading.style.display = 'none';
        form.style.display = 'flex';

        populateToolForm(tool, activeModuleContainer);

        // Cargar imágenes en FilePond
        if (tool.images && tool.images.length > 0) {
            loadToolImages(tool.images);
        }

    } catch (error) {
        console.error('[ToolsEdit] Error en inicialización:', error);
        if (loading) {
            loading.textContent = 'Error cargando herramienta';
            loading.style.color = 'red';
        }
        return;
    }

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';

        try {
            const formData = {
                name: activeModuleContainer.querySelector('#tool-name')?.value || '',
                description: activeModuleContainer.querySelector('#tool-description')?.value || '',
                features: getFeatures()
            };

            const imageIds = window.FilePondComponent?.getImageIds('tool-images') || [];
            if (imageIds.length > 0) {
                formData.image_ids = imageIds;
            }

            const updatedTool = await updateTool(toolId, formData, activeModuleContainer);

            if (updatedTool) {
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Herramienta actualizada correctamente');
                } else {
                    alert('Herramienta actualizada correctamente');
                }

                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow({ refresh: true });
                    }
                }, 500);
            }

        } catch (error) {
            console.error('[ToolsEdit] Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar';
        }
    });

    // Botón cancelar
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeToolsModule();
        });
    }

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

    const emptyState = activeModuleContainer.querySelector('.tools-form--no-context');
    if (emptyState) {
        return;
    }

    const container = activeModuleContainer.querySelector('.tools-form[data-tool-id]');
    const form = activeModuleContainer.querySelector('#tools-edit-form');

    if (container && form) {
        initializeToolsEditForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    } else {
        console.error('[ToolsEdit] No se pudieron encontrar los elementos');
    }
}

tryInitialize();

