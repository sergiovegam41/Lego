/**
 * AuthGroupsConfigEdit - Lógica de edición de grupos de autenticación
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - usa ComponentContext
 * ✅ Validación client-side antes de enviar
 * ✅ Cierra módulo al finalizar
 * ✅ Carga datos existentes desde la API
 */


// ═══════════════════════════════════════════════════════════════════
// SCREEN CONFIG
// ═══════════════════════════════════════════════════════════════════

const SCREEN_CONFIG = {
    screenId: 'auth-groups-config-edit',
    parentScreenId: 'auth-groups-config-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    apiRoute: '/api/auth-groups'
};

function apiUrl(action) {
    return `${SCREEN_CONFIG.apiRoute}/${action}`;
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
// CARGAR DATOS DEL GRUPO
// ═══════════════════════════════════════════════════════════════════

async function loadGroupData(groupId) {
    try {
        const response = await fetch(`${apiUrl('get')}?id=${encodeURIComponent(groupId)}`);
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al cargar datos del grupo');
        }

        return result.data;
    } catch (error) {
        console.error('[AuthGroupsConfigEdit] Error cargando datos:', error);
        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// ACTUALIZAR GRUPO
// ═══════════════════════════════════════════════════════════════════

async function updateAuthGroup(formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[AuthGroupsConfigEdit] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        // Preparar datos del grupo
        const groupData = {
            id: formData.id.trim(),
            name: formData.name.trim(),
            description: formData.description?.trim() || null,
            is_active: formData.is_active !== false
        };

        // Enviar con fetch
        const response = await fetch(apiUrl('update'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(groupData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al actualizar grupo');
        }

        return result.data;

    } catch (error) {
        console.error('[AuthGroupsConfigEdit] Error actualizando grupo:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al actualizar grupo');
        } else {
            alert('Error actualizando grupo: ' + error.message);
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
        console.error('[AuthGroupsConfigEdit] legoWindowManager no disponible');
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

function initializeForm() {

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[AuthGroupsConfigEdit] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[AuthGroupsConfigEdit] No se encontró container del módulo activo:', activeModuleId);
        return;
    }

    const form = activeModuleContainer.querySelector('#auth-group-edit-form');
    const submitBtn = activeModuleContainer.querySelector('#auth-group-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#auth-group-form-cancel-btn');

    if (!form) {
        console.warn('[AuthGroupsConfigEdit] Formulario no encontrado en módulo activo, esperando...');
        return;
    }

    // Obtener el ID del grupo desde los parámetros del módulo o la URL
    let groupId = null;
    
    // Intentar obtener desde el input hidden (si fue pasado desde PHP)
    const originalIdInput = activeModuleContainer.querySelector('#auth-group-original-id');
    if (originalIdInput && originalIdInput.value) {
        groupId = originalIdInput.value;
    }
    
    // Si no se obtuvo, intentar desde los parámetros del módulo
    if (!groupId && window.legoWindowManager && activeModuleId) {
        const params = window.legoWindowManager.getParams(activeModuleId);
        groupId = params?.id || null;
    }
    
    // Si no se obtuvo, intentar desde la URL
    if (!groupId) {
        const urlParams = new URLSearchParams(window.location.search);
        groupId = urlParams.get('id');
    }

    if (!groupId) {
        console.warn('[AuthGroupsConfigEdit] No se proporcionó ID del grupo, el formulario mostrará estado vacío');
        // No cerrar el módulo, solo mostrar el estado vacío
        return;
    }

    // Cargar datos del grupo
    loadGroupData(groupId)
        .then(groupData => {
            // Llenar formulario con los datos
            activeModuleContainer.querySelector('#auth-group-original-id').value = groupData.id;
            activeModuleContainer.querySelector('#auth-group-id').value = groupData.id;
            activeModuleContainer.querySelector('#auth-group-name').value = groupData.name || '';
            activeModuleContainer.querySelector('#auth-group-description').value = groupData.description || '';
            activeModuleContainer.querySelector('#auth-group-is-active').checked = groupData.is_active !== false;

        })
        .catch(error => {
            console.error('[AuthGroupsConfigEdit] Error cargando datos:', error);
            if (window.AlertService) {
                window.AlertService.error('Error', error.message || 'Error al cargar datos del grupo');
            }
            setTimeout(() => closeModule(), 2000);
        });

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();


        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';

        try {
            // Recoger datos del formulario
            const formData = {
                id: activeModuleContainer.querySelector('#auth-group-id')?.value || '',
                name: activeModuleContainer.querySelector('#auth-group-name')?.value || '',
                description: activeModuleContainer.querySelector('#auth-group-description')?.value || '',
                is_active: activeModuleContainer.querySelector('#auth-group-is-active')?.checked !== false
            };


            // Actualizar grupo
            const updatedGroup = await updateAuthGroup(formData);

            if (updatedGroup) {
                // Éxito - mostrar mensaje
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Grupo actualizado correctamente');
                } else {
                    alert('Grupo actualizado correctamente');
                }

                // Cerrar y refrescar el módulo origen automáticamente
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow({ refresh: true });
                    }
                }, 500);
            }

        } catch (error) {
            console.error('[AuthGroupsConfigEdit] Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar Cambios';
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

    const form = activeModuleContainer.querySelector('#auth-group-edit-form');

    if (form) {
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();

