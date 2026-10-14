/**
 * AuthGroupsConfig - Lógica de gestión de grupos de autenticación
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - usa ComponentContext
 * ✅ Maneja callbacks de TableComponent
 * ✅ Navegación a módulos hijos (Create/Edit)
 */


// ═══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN
// ═══════════════════════════════════════════════════════════════════

const SCREEN_CONFIG = {
    screenId: 'auth-groups-config-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    route: '/component/auth-groups-config',
    apiRoute: '/api/auth-groups',
    children: {
        create: 'auth-groups-config-create',
        edit: 'auth-groups-config-edit'
    },
    tableId: 'auth-groups-config-table'
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

const AUTH_GROUPS_CONFIG = COMPONENT_CONFIG;

// ═══════════════════════════════════════════════════════════════════
// CALLBACKS DE TABLECOMPONENT
// ═══════════════════════════════════════════════════════════════════

/**
 * Callback para editar grupo de autenticación
 */
window.handleEditAuthGroup = function(rowData, tableId) {
    // El ID del grupo es el campo 'id', no 'id' numérico
    const groupId = rowData.id || rowData.auth_group_id;
    openEditAuthGroupModule(groupId);
};

/**
 * Callback para eliminar grupo de autenticación
 */
window.handleDeleteAuthGroup = async function(rowData, tableId) {

    try {
        const response = await fetch(apiUrl('delete'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: rowData.id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar grupo');
        }

        if (window.AlertService) {
            await window.AlertService.success('Éxito', result.msj || 'Grupo eliminado correctamente');
        } else {
            alert('Grupo eliminado correctamente');
        }

        // Recargar tabla
        if (window.legoWindowManager) {
            window.legoWindowManager.reloadActive();
        } else {
            window.location.reload();
        }

    } catch (error) {
        console.error('[AuthGroupsConfig] Error eliminando grupo:', error);

        if (window.AlertService) {
            await window.AlertService.error('Error', error.message || 'Error al eliminar grupo');
        } else {
            alert('Error al eliminar grupo: ' + error.message);
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
        console.error('[AuthGroupsConfig] legoWindowManager no disponible');
        return;
    }

    // parentMenuId se obtiene automáticamente desde la BD
    window.legoWindowManager.openModuleWithMenu({
        moduleId: SCREEN_CONFIG.children.create,  // 'auth-groups-config-create'
        label: 'Crear Grupo',
        url: childUrl('create'),
        icon: 'add-circle-outline'
    });

};

/**
 * Abre el módulo de edición de grupos de autenticación
 * NOMBRE ÚNICO: openEditAuthGroupModule para evitar conflictos con otros módulos
 */
window.openEditAuthGroupModule = function(id) {
    if (!window.legoWindowManager) {
        console.error('[AuthGroupsConfig] legoWindowManager no disponible');
        return;
    }

    const moduleId = SCREEN_CONFIG.children.edit; // 'auth-groups-config-edit'
    const url = childUrl('edit', { id: id });
    
    // parentMenuId se obtiene automáticamente desde la BD
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        label: 'Editar Grupo',
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

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

function initAuthGroupsConfig() {
}

// Inicializar cuando el módulo esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthGroupsConfig);
} else {
    initAuthGroupsConfig();
}

// Escuchar eventos de recarga de módulo
window.addEventListener('lego:module:reloaded', function(e) {
    if (e.detail?.moduleId === SCREEN_CONFIG.screenId) {
        initAuthGroupsConfig();
    }
});

