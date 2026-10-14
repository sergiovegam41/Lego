/**
 * UsersConfig - Lógica de gestión de usuarios
 *
 * FILOSOFÍA LEGO:
 * Gestión de usuarios del sistema.
 */


// ═══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN
// ═══════════════════════════════════════════════════════════════════

const SCREEN_CONFIG = {
    screenId: 'users-config-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    route: '/component/users-config',
    apiRoute: '/api/users-config',
    children: {
        create: 'users-config-create',
        edit: 'users-config-edit'
    },
    tableId: 'users-config-table'
};

const COMPONENT_CONFIG = {
    id: SCREEN_CONFIG.screenId,
    route: SCREEN_CONFIG.route,
    apiRoute: SCREEN_CONFIG.apiRoute,
    // parentMenuId removido - se obtiene dinámicamente desde la BD
    tableId: SCREEN_CONFIG.tableId
};

function getConfig() {
    return COMPONENT_CONFIG;
}

function apiUrl(action, params = null) {
    const config = getConfig();
    let url = `${config.apiRoute}/${action}`;
    if (params && Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    return url;
}

function childUrl(childPath, params = null) {
    const config = getConfig();
    let url = `${config.route}/${childPath}`;
    if (params && Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    return url;
}

const USERS_CONFIG = COMPONENT_CONFIG;

// ═══════════════════════════════════════════════════════════════════
// CALLBACKS PARA ROW ACTIONS
// ═══════════════════════════════════════════════════════════════════

window.handleEditUser = function(rowData, tableId) {
    openEditUserModule(rowData.id);
};

window.handleDeleteUser = async function(rowData, tableId) {

    const userName = `<strong>${rowData.name} (${rowData.email})</strong>`;
    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.delete(userName)
        : confirm('¿Estás seguro de que deseas eliminar este usuario?');

    if (!confirmed) {
        return;
    }

    try {
        const response = await fetch(USERS_CONFIG.apiRoute + '/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: rowData.id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar usuario');
        }

        if (window.AlertService?.toast) {
            window.AlertService.toast(result.msj || 'Usuario eliminado correctamente', 'success');
        } else if (window.AlertService?.success) {
            window.AlertService.success(result.msj || 'Usuario eliminado correctamente');
        }

        // Recargar tabla
        if (window.legoWindowManager) {
            window.legoWindowManager.reloadActive();
        } else {
            window.location.reload();
        }

    } catch (error) {
        console.error('[UsersConfig] Error eliminando usuario:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al eliminar usuario');
        }
    }
};

// ═══════════════════════════════════════════════════════════════════
// NAVEGACIÓN A MÓDULOS HIJOS
// ═══════════════════════════════════════════════════════════════════

/**
 * Abre el módulo de creación
 */
window.openCreateModule = function() {
    if (!window.legoWindowManager) {
        console.error('[UsersConfig] legoWindowManager no disponible');
        return;
    }

    const config = getConfig();
    
    window.legoWindowManager.openModuleWithMenu({
        moduleId: SCREEN_CONFIG.children.create,  // 'users-config-create'
        // parentMenuId se obtiene dinámicamente desde la BD
        label: 'Crear Usuario',
        url: childUrl('create'),
        icon: 'add-circle-outline'
    });

};

/**
 * Abre el módulo de edición de usuarios
 * NOMBRE ÚNICO: openEditUserModule para evitar conflictos con otros módulos
 */
window.openEditUserModule = function(id) {
    if (!window.legoWindowManager) {
        console.error('[UsersConfig] legoWindowManager no disponible');
        return;
    }

    const moduleId = SCREEN_CONFIG.children.edit; // 'users-config-edit'
    const url = childUrl('edit', { id: id });
    
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        // parentMenuId se obtiene dinámicamente desde la BD
        label: 'Editar Usuario',
        url: url,
        icon: 'create-outline'
    });

};


// Inicialización
(function() {
})();

