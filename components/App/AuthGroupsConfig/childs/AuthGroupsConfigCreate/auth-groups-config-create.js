/**
 * AuthGroupsConfigCreate - Lógica de creación de grupos de autenticación
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - usa ComponentContext
 * ✅ Validación client-side antes de enviar
 * ✅ Cierra módulo al finalizar
 * ✅ Normalización automática de IDs
 */


// ═══════════════════════════════════════════════════════════════════
// SCREEN CONFIG
// ═══════════════════════════════════════════════════════════════════

const SCREEN_CONFIG = {
    screenId: 'auth-groups-config-create',
    parentScreenId: 'auth-groups-config-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    apiRoute: '/api/auth-groups'
};

function apiUrl(action) {
    return `${SCREEN_CONFIG.apiRoute}/${action}`;
}

// ═══════════════════════════════════════════════════════════════════
// NORMALIZACIÓN DE IDs
// ═══════════════════════════════════════════════════════════════════

/**
 * Normaliza un ID: mayúsculas, sin acentos, sin caracteres especiales
 * @param {string} id - ID a normalizar
 * @returns {string} - ID normalizado
 */
function normalizeId(id) {
    if (!id) return '';
    
    // Convertir a mayúsculas
    let normalized = id.toUpperCase();
    
    // Mapa de acentos y caracteres especiales
    const accentMap = {
        'Á': 'A', 'É': 'E', 'Í': 'I', 'Ó': 'O', 'Ú': 'U',
        'À': 'A', 'È': 'E', 'Ì': 'I', 'Ò': 'O', 'Ù': 'U',
        'Ä': 'A', 'Ë': 'E', 'Ï': 'I', 'Ö': 'O', 'Ü': 'U',
        'Â': 'A', 'Ê': 'E', 'Î': 'I', 'Ô': 'O', 'Û': 'U',
        'Ã': 'A', 'Õ': 'O',
        'Ç': 'C', 'Ñ': 'N'
    };
    
    // Reemplazar acentos
    normalized = normalized.replace(/[ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÂÊÎÔÛÃÕÇÑ]/g, (char) => accentMap[char] || char);
    
    // Eliminar caracteres especiales (solo permitir letras, números y guiones bajos)
    normalized = normalized.replace(/[^A-Z0-9_]/g, '');
    
    // Eliminar espacios y guiones múltiples
    normalized = normalized.replace(/\s+/g, '');
    normalized = normalized.replace(/_+/g, '_');
    
    // Eliminar guiones bajos al inicio y final
    normalized = normalized.replace(/^_+|_+$/g, '');
    
    return normalized;
}

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN CLIENT-SIDE
// ═══════════════════════════════════════════════════════════════════

function validateForm(formData) {
    const errors = {};

    if (!formData.id || formData.id.trim() === '') {
        errors.id = 'El ID del grupo es requerido';
    }

    if (!formData.name || formData.name.trim() === '') {
        errors.name = 'El nombre del grupo es requerido';
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

// ═══════════════════════════════════════════════════════════════════
// CREAR GRUPO
// ═══════════════════════════════════════════════════════════════════

async function createAuthGroup(formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[AuthGroupsConfigCreate] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        // Normalizar el ID
        const normalizedId = normalizeId(formData.id.trim());
        
        if (!normalizedId) {
            throw new Error('El ID del grupo no puede estar vacío después de normalizar');
        }
        
        // Verificar si el grupo ya existe antes de intentar crearlo
        const listResponse = await fetch(`${SCREEN_CONFIG.apiRoute}/list`);
        const listResult = await listResponse.json();
        
        if (listResponse.ok && listResult.success && listResult.data) {
            // Buscar si ya existe un grupo con ese ID
            const existingGroup = listResult.data.find(group => group.id === normalizedId);
            
            if (existingGroup) {
                throw new Error(`Ya existe un grupo con el ID "${normalizedId}". Por favor, usa un ID diferente.`);
            }
        }

        // Preparar datos del grupo
        const groupData = {
            id: normalizedId,
            name: formData.name.trim(),
            description: formData.description?.trim() || null,
            is_active: formData.is_active !== false
        };

        // Enviar con fetch
        const response = await fetch(apiUrl('create'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(groupData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            // Si el error es por duplicado, mostrar mensaje más claro
            if (response.status === 409 || (result.msj && result.msj.includes('Ya existe'))) {
                throw new Error(`Ya existe un grupo con el ID "${normalizedId}". Por favor, usa un ID diferente.`);
            }
            throw new Error(result.msj || 'Error al crear grupo');
        }

        return result.data;

    } catch (error) {
        console.error('[AuthGroupsConfigCreate] Error creando grupo:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al crear grupo');
        } else {
            alert('Error creando grupo: ' + error.message);
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// MOSTRAR ERRORES DE VALIDACIÓN
// ═══════════════════════════════════════════════════════════════════

function showValidationErrors(errors) {
    // Limpiar errores previos
    document.querySelectorAll('.lego-input__error, .lego-textarea__error').forEach(el => {
        el.remove();
    });

    document.querySelectorAll('.lego-input--error, .lego-textarea--error').forEach(el => {
        el.classList.remove('lego-input--error', 'lego-textarea--error');
    });

    // Mostrar nuevos errores
    Object.entries(errors).forEach(([field, message]) => {
        const input = document.getElementById(`auth-group-${field}`);
        if (input) {
            const container = input.closest('.lego-input, .lego-textarea, .example-form__field');
            if (container) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'lego-input__error';
                errorDiv.textContent = Array.isArray(message) ? message[0] : message;
                container.appendChild(errorDiv);
                if (input.classList) {
                    input.classList.add('lego-input--error');
                }
            }
        }
    });
}

// ═══════════════════════════════════════════════════════════════════
// CERRAR MÓDULO
// ═══════════════════════════════════════════════════════════════════

function closeModule() {
    if (window.legoWindowManager) {
        window.legoWindowManager.closeCurrentWindow();
    } else {
        console.error('[AuthGroupsConfigCreate] legoWindowManager no disponible');
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

function initializeForm() {

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[AuthGroupsConfigCreate] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[AuthGroupsConfigCreate] No se encontró container del módulo activo:', activeModuleId);
        return;
    }

    const form = activeModuleContainer.querySelector('#auth-group-create-form');
    const submitBtn = activeModuleContainer.querySelector('#auth-group-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#auth-group-form-cancel-btn');

    if (!form) {
        console.warn('[AuthGroupsConfigCreate] Formulario no encontrado en módulo activo, esperando...');
        return;
    }

    // Agregar listener para normalizar el ID mientras se escribe
    const groupIdInput = activeModuleContainer.querySelector('#auth-group-id');
    if (groupIdInput) {
        groupIdInput.addEventListener('input', function(e) {
            const cursorPos = e.target.selectionStart;
            const normalized = normalizeId(e.target.value);
            e.target.value = normalized;
            e.target.setSelectionRange(cursorPos, cursorPos);
        });
    }

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();


        submitBtn.disabled = true;
        submitBtn.textContent = 'Creando...';

        try {
            // Recoger datos del formulario
            const formData = {
                id: activeModuleContainer.querySelector('#auth-group-id')?.value || '',
                name: activeModuleContainer.querySelector('#auth-group-name')?.value || '',
                description: activeModuleContainer.querySelector('#auth-group-description')?.value || '',
                is_active: activeModuleContainer.querySelector('#auth-group-is-active')?.checked !== false
            };


            // Crear grupo
            const newGroup = await createAuthGroup(formData);

            if (newGroup) {
                // Éxito - mostrar mensaje
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Grupo creado correctamente');
                } else {
                    alert('Grupo creado correctamente');
                }

                // Cerrar y refrescar el módulo origen automáticamente
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow({ refresh: true });
                    }
                }, 500);
            }

        } catch (error) {
            console.error('[AuthGroupsConfigCreate] Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Grupo';
        }
    });

    // Botón cancelar
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeModule();
        });
    }

}

// Intentar inicializar inmediatamente
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

    const form = activeModuleContainer.querySelector('#auth-group-create-form');

    if (form) {
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();

