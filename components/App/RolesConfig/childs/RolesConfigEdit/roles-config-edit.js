/**
 * RolesConfigEdit - Lógica de edición de roles
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
    screenId: 'roles-config-edit',
    parentScreenId: 'roles-config-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    apiRoute: '/api/roles-config'
};

function apiUrl(action) {
    return `${SCREEN_CONFIG.apiRoute}/${action}`;
}

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN CLIENT-SIDE
// ═══════════════════════════════════════════════════════════════════

function validateForm(formData) {
    const errors = {};

    if (!formData.auth_group_id || formData.auth_group_id === '') {
        errors.auth_group_id = 'El grupo de autenticación es requerido';
    }

    if (!formData.role_id || formData.role_id.trim() === '') {
        errors.role_id = 'El ID del rol es requerido';
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

// ═══════════════════════════════════════════════════════════════════
// CARGAR DATOS DEL ROL
// ═══════════════════════════════════════════════════════════════════

async function loadRoleData(roleId) {
    try {
        const response = await fetch(`${apiUrl('get')}?id=${encodeURIComponent(roleId)}`);
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al cargar datos del rol');
        }

        return result.data;
    } catch (error) {
        console.error('[RolesConfigEdit] Error cargando datos:', error);
        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// ACTUALIZAR ROL
// ═══════════════════════════════════════════════════════════════════

async function updateRole(formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[RolesConfigEdit] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        // Preparar datos del rol
        const roleData = {
            id: formData.id,
            auth_group_id: formData.auth_group_id.trim(),
            role_id: formData.role_id.trim(),
            role_name: formData.role_name?.trim() || null,
            description: formData.description?.trim() || null,
            is_active: formData.is_active !== false
        };

        // Enviar con fetch
        const response = await fetch(apiUrl('update'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(roleData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al actualizar rol');
        }

        return result.data;

    } catch (error) {
        console.error('[RolesConfigEdit] Error actualizando rol:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al actualizar rol');
        } else {
            alert('Error actualizando rol: ' + error.message);
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
        const input = document.getElementById(`role-${field}`) || document.getElementById(`new-${field}`);
        if (input) {
            const container = input.closest('.lego-input, .lego-select, .lego-textarea, .example-form__field');
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
        console.error('[RolesConfigEdit] legoWindowManager no disponible');
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

function initializeForm() {
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[RolesConfigEdit] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[RolesConfigEdit] No se encontró container del módulo activo:', activeModuleId);
        return;
    }

    const form = activeModuleContainer.querySelector('#role-edit-form');
    const submitBtn = activeModuleContainer.querySelector('#role-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#role-form-cancel-btn');

    if (!form) {
        console.warn('[RolesConfigEdit] Formulario no encontrado en módulo activo, esperando...');
        return;
    }

    // Obtener el ID del rol desde los parámetros del módulo o la URL
    let roleId = null;
    
    // Intentar obtener desde el input hidden (si fue pasado desde PHP)
    const originalIdInput = activeModuleContainer.querySelector('#role-original-id');
    if (originalIdInput && originalIdInput.value) {
        roleId = originalIdInput.value;
    }
    
    // Si no se obtuvo, intentar desde los parámetros del módulo
    if (!roleId && window.legoWindowManager && activeModuleId) {
        const params = window.legoWindowManager.getParams(activeModuleId);
        roleId = params?.id || null;
    }
    
    // Si no se obtuvo, intentar desde la URL
    if (!roleId) {
        const urlParams = new URLSearchParams(window.location.search);
        roleId = urlParams.get('id');
    }

    if (!roleId) {
        console.warn('[RolesConfigEdit] No se proporcionó ID del rol, el formulario mostrará estado vacío');
        return;
    }

    // Cargar datos del rol
    loadRoleData(roleId)
        .then(roleData => {
            // Llenar formulario con los datos
            if (originalIdInput) {
                originalIdInput.value = roleData.id;
            }
            
            // Grupo de autenticación (readonly)
            if (window.LegoSelect) {
                window.LegoSelect.setValue('role-auth-group-id', roleData.auth_group_id);
            } else {
                const authGroupSelect = activeModuleContainer.querySelector('#role-auth-group-id');
                if (authGroupSelect) {
                    authGroupSelect.value = roleData.auth_group_id;
                }
            }
            
            // ID del rol (readonly)
            const roleIdInput = activeModuleContainer.querySelector('#role-role-id');
            if (roleIdInput) {
                roleIdInput.value = roleData.role_id || '';
            }
            
            // Nombre del rol
            const roleNameInput = activeModuleContainer.querySelector('#role-name');
            if (roleNameInput) {
                roleNameInput.value = roleData.role_name || '';
            }
            
            // Descripción
            const descriptionInput = activeModuleContainer.querySelector('#role-description');
            if (descriptionInput) {
                descriptionInput.value = roleData.description || '';
            }
            
            // Activo
            const isActiveInput = activeModuleContainer.querySelector('#role-is-active');
            if (isActiveInput) {
                isActiveInput.checked = roleData.is_active !== false;
            }
        })
        .catch(error => {
            console.error('[RolesConfigEdit] Error cargando datos:', error);
            if (window.AlertService) {
                window.AlertService.error('Error', error.message || 'Error al cargar datos del rol');
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
            // Obtener auth_group_id del select (puede ser de LegoSelect o nativo)
            let authGroupId = '';
            if (window.LegoSelect) {
                authGroupId = window.LegoSelect.getValue('role-auth-group-id') || '';
            }
            if (!authGroupId) {
                const authGroupSelect = activeModuleContainer.querySelector('#role-auth-group-id');
                if (authGroupSelect) {
                    authGroupId = authGroupSelect.value || '';
                }
            }
            
            // Obtener role_id del input (readonly)
            const roleIdInput = activeModuleContainer.querySelector('#role-role-id');
            const roleIdValue = roleIdInput?.value || '';

            const formData = {
                id: roleId,
                auth_group_id: authGroupId,
                role_id: roleIdValue,
                role_name: activeModuleContainer.querySelector('#role-name')?.value || '',
                description: activeModuleContainer.querySelector('#role-description')?.value || '',
                is_active: activeModuleContainer.querySelector('#role-is-active')?.checked !== false
            };

            // Actualizar rol
            const updatedRole = await updateRole(formData);

            if (updatedRole) {
                // Éxito - mostrar mensaje
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Rol actualizado correctamente');
                } else {
                    alert('Rol actualizado correctamente');
                }

                // Cerrar y refrescar el módulo origen automáticamente
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow({ refresh: true });
                    }
                }, 500);
            }

        } catch (error) {
            console.error('[RolesConfigEdit] Error:', error);
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

    const form = activeModuleContainer.querySelector('#role-edit-form');

    if (form) {
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();

