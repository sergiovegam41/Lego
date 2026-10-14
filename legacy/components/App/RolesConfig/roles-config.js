/**
 * RolesConfig - Lógica de gestión de roles
 *
 * FILOSOFÍA LEGO:
 * Gestión del catálogo de roles disponibles por grupo de autenticación.
 */


// ═══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN
// ═══════════════════════════════════════════════════════════════════

const SCREEN_CONFIG = {
    screenId: 'roles-config-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    route: '/component/roles-config',
    apiRoute: '/api/roles-config',
    children: {
        create: 'roles-config-create',
        edit: 'roles-config-edit'
    },
    tableId: 'roles-config-table'
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

const ROLES_CONFIG = COMPONENT_CONFIG;

// ═══════════════════════════════════════════════════════════════════
// CALLBACKS PARA ROW ACTIONS
// ═══════════════════════════════════════════════════════════════════

window.handleEditRole = function(rowData, tableId) {
    openEditRoleModule(rowData.id);
};

window.handleDeleteRole = async function(rowData, tableId) {

    const roleName = `<strong>${rowData.role_id} (${rowData.auth_group_id})</strong>`;
    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.delete(roleName)
        : confirm('¿Estás seguro de que deseas eliminar este rol?');

    if (!confirmed) {
        return;
    }

    try {
        const response = await fetch(ROLES_CONFIG.apiRoute + '/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: rowData.id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar rol');
        }

        if (window.AlertService?.toast) {
            window.AlertService.toast(result.msj || 'Rol eliminado correctamente', 'success');
        } else if (window.AlertService?.success) {
            window.AlertService.success(result.msj || 'Rol eliminado correctamente');
        }

        // Recargar tabla
        if (window.legoWindowManager) {
            window.legoWindowManager.reloadActive();
        } else {
            window.location.reload();
        }

    } catch (error) {
        console.error('[RolesConfig] Error eliminando rol:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al eliminar rol');
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
        console.error('[RolesConfig] legoWindowManager no disponible');
        return;
    }

    const config = getConfig();
    
    window.legoWindowManager.openModuleWithMenu({
        moduleId: SCREEN_CONFIG.children.create,  // 'roles-config-create'
        // parentMenuId se obtiene dinámicamente desde la BD
        label: 'Crear Rol',
        url: childUrl('create'),
        icon: 'add-circle-outline'
    });

};

/**
 * Abre el módulo de edición de roles
 * NOMBRE ÚNICO: openEditRoleModule para evitar conflictos con otros módulos
 */
window.openEditRoleModule = function(id) {
    if (!window.legoWindowManager) {
        console.error('[RolesConfig] legoWindowManager no disponible');
        return;
    }

    const moduleId = SCREEN_CONFIG.children.edit; // 'roles-config-edit'
    const url = childUrl('edit', { id: id });
    
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        // parentMenuId se obtiene dinámicamente desde la BD
        label: 'Editar Rol',
        url: url,
        icon: 'create-outline',
        sourceModuleId: window.moduleStore?.activeModule
    });
    
    // Guardar el ID como parámetro del módulo
    setTimeout(() => {
        if (window.legoWindowManager && window.moduleStore) {
            window.legoWindowManager.setParam('id', id, moduleId);
        }
    }, 100);
};

// Inicialización
(function() {
})();

