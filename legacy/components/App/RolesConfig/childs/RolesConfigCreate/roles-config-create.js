/**
 * RolesConfigCreate - Lógica de creación de roles
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
    screenId: 'roles-config-create',
    parentScreenId: 'roles-config-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    apiRoute: '/api/roles-config',
    authGroupsApiRoute: '/api/auth-groups'
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
// MANEJO DEL SELECTOR DE AUTH_GROUP
// ═══════════════════════════════════════════════════════════════════

function initializeAuthGroupSelector() {
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) return;

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) return;

    // Escuchar cambios en el select usando múltiples métodos
    // Método 1: LegoSelect (si está disponible)
    if (window.LegoSelect) {
        const selectElement = activeModuleContainer.querySelector('[data-lego-select="role-auth-group-id"]');
        if (selectElement) {
            // Remover listener previo si existe
            selectElement.removeEventListener('change', handleAuthGroupChangeEvent);
            selectElement.addEventListener('change', handleAuthGroupChangeEvent);
        }
    }
    
    // Método 2: Select nativo
    const select = activeModuleContainer.querySelector('#role-auth-group-id');
    if (select) {
        select.removeEventListener('change', handleAuthGroupChangeNative);
        select.addEventListener('change', handleAuthGroupChangeNative);
    }
    
    // Método 3: Usar el método getValue de LegoSelect periódicamente (fallback)
    // También verificar el valor inicial
    setTimeout(() => {
        const currentValue = window.LegoSelect?.getValue('role-auth-group-id') || 
                           activeModuleContainer.querySelector('#role-auth-group-id')?.value || '';
        if (currentValue) {
            handleAuthGroupChange(currentValue);
        }
    }, 100);
}

function handleAuthGroupChangeEvent(e) {
    const value = e.detail?.value || e.target?.value || '';
    handleAuthGroupChange(value);
}

function handleAuthGroupChangeNative(e) {
    const value = e.target?.value || '';
    handleAuthGroupChange(value);
}

function handleAuthGroupChange(value) {
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) return;

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) return;

    const newGroupContainer = activeModuleContainer.querySelector('#new-group-container');
    const newGroupNameContainer = activeModuleContainer.querySelector('#new-group-name-container');


    if (value === '__OTHER__') {
        // Mostrar campos para nuevo grupo
        if (newGroupContainer) {
            newGroupContainer.style.display = 'block';
        }
        if (newGroupNameContainer) {
            newGroupNameContainer.style.display = 'block';
        }
        
        // Agregar listener para normalizar el ID mientras se escribe
        const newGroupIdInput = activeModuleContainer.querySelector('#new-group-id');
        if (newGroupIdInput) {
            newGroupIdInput.removeEventListener('input', normalizeGroupIdInput);
            newGroupIdInput.addEventListener('input', normalizeGroupIdInput);
        }
    } else {
        // Ocultar campos para nuevo grupo
        if (newGroupContainer) {
            newGroupContainer.style.display = 'none';
            // Limpiar el campo
            const newGroupIdInput = activeModuleContainer.querySelector('#new-group-id');
            if (newGroupIdInput) newGroupIdInput.value = '';
        }
        if (newGroupNameContainer) {
            newGroupNameContainer.style.display = 'none';
            // Limpiar el campo
            const newGroupNameInput = activeModuleContainer.querySelector('#new-group-name');
            if (newGroupNameInput) newGroupNameInput.value = '';
        }
    }
}

function normalizeGroupIdInput(e) {
    const cursorPos = e.target.selectionStart;
    const normalized = normalizeId(e.target.value);
    e.target.value = normalized;
    // Restaurar posición del cursor
    e.target.setSelectionRange(cursorPos, cursorPos);
}

// ═══════════════════════════════════════════════════════════════════
// MANEJO DEL INPUT DE ROLE_ID (normalización en tiempo real)
// ═══════════════════════════════════════════════════════════════════

function initializeRoleIdInput() {
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) return;

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) return;

    // Agregar listener para normalizar el ID mientras se escribe
    const roleIdInput = activeModuleContainer.querySelector('#role-role-id');
    if (roleIdInput) {
        roleIdInput.removeEventListener('input', normalizeRoleIdInput);
        roleIdInput.addEventListener('input', normalizeRoleIdInput);
    }
}

function normalizeRoleIdInput(e) {
    const cursorPos = e.target.selectionStart;
    const normalized = normalizeId(e.target.value);
    e.target.value = normalized;
    // Restaurar posición del cursor
    e.target.setSelectionRange(cursorPos, cursorPos);
}

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN CLIENT-SIDE
// ═══════════════════════════════════════════════════════════════════

function validateForm(formData) {
    const errors = {};

    // Auth group requerido
    if (!formData.auth_group_id || formData.auth_group_id === '') {
        errors.auth_group_id = 'El grupo de autenticación es requerido';
    }

    // Si es "Otro", validar campos del nuevo grupo
    if (formData.auth_group_id === '__OTHER__') {
        if (!formData.new_group_id || formData.new_group_id.trim() === '') {
            errors.new_group_id = 'El ID del nuevo grupo es requerido';
        }
        // El nombre del grupo es opcional, se usará el ID si no se proporciona
    }

    // Role ID requerido
    if (!formData.role_id || formData.role_id.trim() === '') {
        errors.role_id = 'El ID del rol es requerido';
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
        // Normalizar el ID antes de verificar/crear
        const normalizedId = normalizeId(groupId);
        
        if (!normalizedId) {
            throw new Error('El ID del grupo no puede estar vacío después de normalizar');
        }
        
        // Primero verificar si el grupo ya existe
        const checkResponse = await fetch(`${SCREEN_CONFIG.authGroupsApiRoute}/get?id=${normalizedId}`);
        const checkResult = await checkResponse.json();
        
        if (checkResponse.ok && checkResult.success && checkResult.data) {
            // El grupo ya existe, retornar su ID
            return normalizedId;
        }
        
        // El grupo no existe, crearlo
        const response = await fetch(SCREEN_CONFIG.authGroupsApiRoute + '/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: normalizedId,
                name: groupName || normalizedId,
                is_active: true
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al crear grupo de autenticación');
        }

        return result.data.id;

    } catch (error) {
        console.error('[RolesConfigCreate] Error creando grupo:', error);
        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// CREAR ROL EN EL CATÁLOGO (si no existe)
// ═══════════════════════════════════════════════════════════════════

async function ensureRoleExists(roleId, authGroupId, roleName) {
    try {
        // Normalizar el ID antes de verificar/crear
        const normalizedId = normalizeId(roleId);
        
        if (!normalizedId) {
            throw new Error('El ID del rol no puede estar vacío después de normalizar');
        }
        
        // Verificar si el rol ya existe buscando en la lista de roles del grupo
        try {
            const listResponse = await fetch(`${SCREEN_CONFIG.apiRoute}/list`);
            const listResult = await listResponse.json();
            
            if (listResponse.ok && listResult.success && listResult.data) {
                // Buscar si ya existe un rol con ese auth_group_id y role_id
                const existingRole = listResult.data.find(role => 
                    role.auth_group_id === authGroupId && role.role_id === normalizedId
                );
                
                if (existingRole) {
                    return normalizedId;
                }
            }
        } catch (checkError) {
        }
        
        // El rol no existe, crearlo en el catálogo
        const createResponse = await fetch(SCREEN_CONFIG.apiRoute + '/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                auth_group_id: authGroupId,
                role_id: normalizedId,
                role_name: roleName || normalizedId,
                description: null,
                is_active: true
            })
        });

        const createResult = await createResponse.json();

        if (!createResponse.ok || !createResult.success) {
            // Si el error es por duplicado, el rol ya existe (puede haber sido creado entre la verificación y la creación)
            if (createResponse.status === 409 || (createResult.msj && createResult.msj.includes('Ya existe'))) {
                return normalizedId;
            }
            throw new Error(createResult.msj || 'Error al crear rol en catálogo');
        }

        return normalizedId;

    } catch (error) {
        console.error('[RolesConfigCreate] Error asegurando rol en catálogo:', error);
        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// CREAR ROL
// ═══════════════════════════════════════════════════════════════════

async function createRole(formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[RolesConfigCreate] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        let authGroupId = formData.auth_group_id;

        // Si se seleccionó "Otro", crear el nuevo grupo primero
        if (authGroupId === '__OTHER__') {
            // Usar el ID del grupo como nombre si no se proporciona nombre
            const groupName = formData.new_group_name?.trim() || formData.new_group_id.trim();
            authGroupId = await createAuthGroup(
                formData.new_group_id.trim(),
                groupName
            );
        }

        // Normalizar el role_id (siempre es un campo de texto directo)
        const roleId = normalizeId(formData.role_id.trim());
        
        if (!roleId) {
            throw new Error('El ID del rol no puede estar vacío después de normalizar');
        }
        
        // Verificar si el rol ya existe antes de intentar crearlo
        const listResponse = await fetch(`${SCREEN_CONFIG.apiRoute}/list`);
        const listResult = await listResponse.json();
        
        if (listResponse.ok && listResult.success && listResult.data) {
            // Buscar si ya existe un rol con ese auth_group_id y role_id
            const existingRole = listResult.data.find(role => 
                role.auth_group_id === authGroupId && role.role_id === roleId
            );
            
            if (existingRole) {
                throw new Error(`Ya existe un rol con el ID "${roleId}" en el grupo "${authGroupId}". Por favor, usa un ID diferente.`);
            }
        }

        // Preparar datos del rol (crear directamente, no necesitamos ensureRoleExists)
        const roleData = {
            auth_group_id: authGroupId,
            role_id: roleId,
            role_name: formData.role_name?.trim() || null,
            description: formData.description?.trim() || null,
            is_active: formData.is_active !== false
        };

        // Enviar con fetch para crear el rol
        const response = await fetch(apiUrl('create'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(roleData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            // Si el error es por duplicado, mostrar mensaje más claro
            if (response.status === 409 || (result.msj && result.msj.includes('Ya existe'))) {
                throw new Error(`Ya existe un rol con el ID "${roleId}" en el grupo "${authGroupId}". Por favor, usa un ID diferente.`);
            }
            throw new Error(result.msj || 'Error al crear rol');
        }

        return result.data;

    } catch (error) {
        console.error('[RolesConfigCreate] Error creando rol:', error);

        // Usar AlertService si está disponible
        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al crear rol');
        } else {
            alert('Error creando rol: ' + error.message);
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
        console.error('[RolesConfigCreate] legoWindowManager no disponible');
    }
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

function initializeForm() {

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[RolesConfigCreate] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[RolesConfigCreate] No se encontró container del módulo activo:', activeModuleId);
        return;
    }

    const form = activeModuleContainer.querySelector('#role-create-form');
    const submitBtn = activeModuleContainer.querySelector('#role-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#role-form-cancel-btn');

    if (!form) {
        console.warn('[RolesConfigCreate] Formulario no encontrado en módulo activo, esperando...');
        return;
    }

    // Inicializar selectores y inputs
    initializeAuthGroupSelector();
    initializeRoleIdInput();
    
        // Verificar valor inicial del auth_group después de un pequeño delay
        setTimeout(() => {
            const activeModuleId = window.moduleStore?.getActiveModule();
            if (!activeModuleId) return;
            
            const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
            if (!activeModuleContainer) return;
            
            // Verificar auth_group
            let authGroupValue = '';
            if (window.LegoSelect) {
                authGroupValue = window.LegoSelect.getValue('role-auth-group-id') || '';
            }
            if (!authGroupValue) {
                const select = activeModuleContainer.querySelector('#role-auth-group-id');
                if (select) authGroupValue = select.value || '';
            }
            if (authGroupValue) {
                handleAuthGroupChange(authGroupValue);
            }
        }, 300);

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();


        submitBtn.disabled = true;
        submitBtn.textContent = 'Creando...';

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
            
            // Obtener role_id del input de texto (siempre es un campo directo)
            const roleIdInput = activeModuleContainer.querySelector('#role-role-id');
            const roleId = roleIdInput?.value || '';

            const formData = {
                auth_group_id: authGroupId,
                role_id: roleId,
                role_name: activeModuleContainer.querySelector('#role-name')?.value || '',
                description: activeModuleContainer.querySelector('#role-description')?.value || '',
                is_active: activeModuleContainer.querySelector('#role-is-active')?.checked !== false
            };


            // Si es "Otro" en auth_group, agregar datos del nuevo grupo
            if (authGroupId === '__OTHER__') {
                const newGroupId = activeModuleContainer.querySelector('#new-group-id')?.value || '';
                const newGroupName = activeModuleContainer.querySelector('#new-group-name')?.value || '';
                formData.new_group_id = newGroupId;
                formData.new_group_name = newGroupName;
            }


            // Crear rol
            const newRole = await createRole(formData);

            if (newRole) {
                // Éxito - mostrar mensaje
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Rol creado correctamente');
                } else {
                    alert('Rol creado correctamente');
                }

                // Cerrar y refrescar el módulo origen automáticamente
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow({ refresh: true });
                    }
                }, 500);
            }

        } catch (error) {
            console.error('[RolesConfigCreate] Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Rol';
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

    const form = activeModuleContainer.querySelector('#role-create-form');

    if (form) {
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();

