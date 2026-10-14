/**
 * UsersConfigCreate - Lógica de creación de usuarios
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - usa ComponentContext
 * ✅ Validación client-side antes de enviar
 * ✅ Cierra módulo al finalizar
 * ✅ Selector de auth_group con opción "Otro" para crear nuevos grupos
 */


// ═══════════════════════════════════════════════════════════════════
// SCREEN CONFIG
// ═══════════════════════════════════════════════════════════════════

const SCREEN_CONFIG = {
    screenId: 'users-config-create',
    parentScreenId: 'users-config-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    apiRoute: '/api/users-config',
    authGroupsApiRoute: '/api/auth-groups'
};

function apiUrl(action) {
    return `${SCREEN_CONFIG.apiRoute}/${action}`;
}

// ═══════════════════════════════════════════════════════════════════
// MANEJO DEL SELECTOR DE AUTH_GROUP Y ROLES
// ═══════════════════════════════════════════════════════════════════

function initializeAuthGroupSelector() {
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) return;

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) return;

    const select = activeModuleContainer.querySelector('#user-auth-group-id');
    if (!select) return;

    // Escuchar cambios en el select
    if (window.LegoSelect) {
        const selectElement = activeModuleContainer.querySelector('[data-lego-select="user-auth-group-id"]');
        if (selectElement) {
            selectElement.addEventListener('change', function(e) {
                const value = e.detail?.value || e.target.value;
                handleAuthGroupChange(value);
                updateRoleOptions(value);
            });
        }
    } else {
        select.addEventListener('change', function(e) {
            const value = e.target.value;
            handleAuthGroupChange(value);
            updateRoleOptions(value);
        });
    }
}

function handleAuthGroupChange(value) {
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) return;

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) return;

    const newGroupContainer = activeModuleContainer.querySelector('#new-group-container');
    const newGroupNameContainer = activeModuleContainer.querySelector('#new-group-name-container');

    if (value === '__OTHER__') {
        if (newGroupContainer) newGroupContainer.style.display = 'block';
        if (newGroupNameContainer) newGroupNameContainer.style.display = 'block';
    } else {
        if (newGroupContainer) newGroupContainer.style.display = 'none';
        if (newGroupNameContainer) newGroupNameContainer.style.display = 'none';
    }
}

function updateRoleOptions(authGroupId) {
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) return;

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) return;

    const roleSelect = activeModuleContainer.querySelector('#user-role-id');
    if (!roleSelect) return;

    if (!authGroupId || authGroupId === '__OTHER__') {
        roleSelect.innerHTML = '<option value="">Primero selecciona un grupo</option>';
        return;
    }

    const roles = window.USERS_CONFIG_ROLES?.[authGroupId] || [];

    if (roles.length === 0) {
        roleSelect.innerHTML = '<option value="">No hay roles disponibles para este grupo</option>';
        return;
    }

    roleSelect.innerHTML = '<option value="">Seleccionar rol...</option>';
    roles.forEach(roleId => {
        const option = document.createElement('option');
        option.value = roleId;
        option.textContent = roleId;
        roleSelect.appendChild(option);
    });
}

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN CLIENT-SIDE
// ═══════════════════════════════════════════════════════════════════

function validateForm(formData) {
    const errors = {};

    if (!formData.name || formData.name.trim() === '') {
        errors.name = 'El nombre es requerido';
    }

    if (!formData.email || formData.email.trim() === '') {
        errors.email = 'El email es requerido';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        errors.email = 'El email no es válido';
    }

    if (!formData.password || formData.password.length < 8) {
        errors.password = 'La contraseña debe tener al menos 8 caracteres';
    }

    if (!formData.auth_group_id || formData.auth_group_id === '') {
        errors.auth_group_id = 'El grupo de autenticación es requerido';
    }

    if (formData.auth_group_id === '__OTHER__') {
        if (!formData.new_group_id || formData.new_group_id.trim() === '') {
            errors.new_group_id = 'El ID del nuevo grupo es requerido';
        }
        if (!formData.new_group_name || formData.new_group_name.trim() === '') {
            errors.new_group_name = 'El nombre del nuevo grupo es requerido';
        }
    }

    if (!formData.role_id || formData.role_id === '') {
        errors.role_id = 'El rol es requerido';
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

// ═══════════════════════════════════════════════════════════════════
// CREAR GRUPO DE AUTENTICACIÓN
// ═══════════════════════════════════════════════════════════════════

async function createAuthGroup(groupId, groupName) {
    try {
        const response = await fetch(SCREEN_CONFIG.authGroupsApiRoute + '/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: groupId,
                name: groupName,
                is_active: true
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al crear grupo de autenticación');
        }

        return result.data.id;

    } catch (error) {
        console.error('[UsersConfigCreate] Error creando grupo:', error);
        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// CREAR USUARIO
// ═══════════════════════════════════════════════════════════════════

async function createUser(formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[UsersConfigCreate] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        let authGroupId = formData.auth_group_id;

        // Si se seleccionó "Otro", crear el nuevo grupo primero
        if (authGroupId === '__OTHER__') {
            authGroupId = await createAuthGroup(
                formData.new_group_id.trim(),
                formData.new_group_name.trim()
            );
        }

        // Preparar datos del usuario
        const userData = {
            name: formData.name.trim(),
            email: formData.email.trim(),
            password: formData.password,
            auth_group_id: authGroupId,
            role_id: formData.role_id,
            status: formData.status || 'active'
        };

        // Enviar con fetch
        const response = await fetch(apiUrl('create'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al crear usuario');
        }

        return result.data;

    } catch (error) {
        console.error('[UsersConfigCreate] Error creando usuario:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al crear usuario');
        } else {
            alert('Error creando usuario: ' + error.message);
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

    document.querySelectorAll('.lego-input--error, .lego-select--error').forEach(el => {
        el.classList.remove('lego-input--error', 'lego-select--error');
    });

    // Mostrar nuevos errores
    Object.entries(errors).forEach(([field, message]) => {
        const input = document.getElementById(`user-${field}`) || document.getElementById(`new-${field}`);
        if (input) {
            const container = input.closest('.lego-input, .lego-select, .example-form__field');
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
        console.error('[UsersConfigCreate] legoWindowManager no disponible');
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

function initializeForm() {

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[UsersConfigCreate] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[UsersConfigCreate] No se encontró container del módulo activo:', activeModuleId);
        return;
    }

    const form = activeModuleContainer.querySelector('#user-create-form');
    const submitBtn = activeModuleContainer.querySelector('#user-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#user-form-cancel-btn');

    if (!form) {
        console.warn('[UsersConfigCreate] Formulario no encontrado en módulo activo, esperando...');
        return;
    }

    // Inicializar selector de auth_group
    initializeAuthGroupSelector();

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();


        submitBtn.disabled = true;
        submitBtn.textContent = 'Creando...';

        try {
            // Recoger datos del formulario
            const authGroupId = window.LegoSelect?.getValue('user-auth-group-id') || 
                               activeModuleContainer.querySelector('#user-auth-group-id')?.value || '';

            const formData = {
                name: activeModuleContainer.querySelector('#user-name')?.value || '',
                email: activeModuleContainer.querySelector('#user-email')?.value || '',
                password: activeModuleContainer.querySelector('#user-password')?.value || '',
                auth_group_id: authGroupId,
                role_id: activeModuleContainer.querySelector('#user-role-id')?.value || '',
                status: window.LegoSelect?.getValue('user-status') || 
                       activeModuleContainer.querySelector('#user-status')?.value || 'active'
            };

            // Si es "Otro", agregar datos del nuevo grupo
            if (authGroupId === '__OTHER__') {
                formData.new_group_id = activeModuleContainer.querySelector('#new-group-id')?.value || '';
                formData.new_group_name = activeModuleContainer.querySelector('#new-group-name')?.value || '';
            }


            // Crear usuario
            const newUser = await createUser(formData);

            if (newUser) {
                // Éxito - mostrar mensaje
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Usuario creado correctamente');
                } else {
                    alert('Usuario creado correctamente');
                }

                // Cerrar y refrescar el módulo origen automáticamente
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow({ refresh: true });
                    }
                }, 500);
            }

        } catch (error) {
            console.error('[UsersConfigCreate] Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Usuario';
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

    const form = activeModuleContainer.querySelector('#user-create-form');

    if (form) {
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();

