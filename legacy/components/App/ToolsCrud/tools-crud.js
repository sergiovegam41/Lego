/**
 * ToolsCrud - Lógica de tabla
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - configuración centralizada
 * ✅ Eventos globales para comunicación reactiva
 * ✅ Navegación con módulos dinámicos
 */


// ═══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DEL COMPONENTE
// ═══════════════════════════════════════════════════════════════════

const TOOLS_SCREEN_CONFIG = {
    screenId: 'tools-crud-list',
    // menuGroupId removido - se obtiene dinámicamente desde la BD
    route: '/component/tools-crud',
    apiRoute: '/api/tools',
    children: {
        create: 'tools-crud-create',
        edit: 'tools-crud-edit'
    },
    tableId: 'tools-crud-table'
};

function toolsApiUrl(action, params = null) {
    let url = `${TOOLS_SCREEN_CONFIG.apiRoute}/${action}`;
    if (params && Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    return url;
}

function toolsChildUrl(childPath, params = null) {
    let url = `${TOOLS_SCREEN_CONFIG.route}/${childPath}`;
    if (params && Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    return url;
}


// ═══════════════════════════════════════════════════════════════════
// CALLBACKS PARA ROW ACTIONS
// ═══════════════════════════════════════════════════════════════════

/**
 * Callback para editar herramienta
 */
window.handleEditTool = function(rowData, tableId) {
    openToolsEditModule(rowData.id);
};

/**
 * Callback para eliminar herramienta
 */
window.handleDeleteTool = async function(rowData, tableId) {

    const itemName = `<strong>${rowData.name || 'ID: ' + rowData.id}</strong>`;
    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.delete(itemName)
        : confirm('¿Estás seguro de que deseas eliminar esta herramienta?');

    if (!confirmed) {
        return;
    }

    try {
        const response = await fetch(toolsApiUrl('delete'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: rowData.id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar herramienta');
        }

        if (window.AlertService?.toast) {
            window.AlertService.toast(result.msj || 'Herramienta eliminada correctamente', 'success');
        }

        if (window.legoWindowManager) {
            window.legoWindowManager.reloadActive();
        } else {
            window.location.reload();
        }

    } catch (error) {
        console.error('[ToolsCrud] Error eliminando herramienta:', error);

        if (window.AlertService) {
            await window.AlertService.error(error.message || 'Error al eliminar herramienta');
        } else {
            alert('Error al eliminar herramienta: ' + error.message);
        }
    }
};

// ═══════════════════════════════════════════════════════════════════
// NAVEGACIÓN
// ═══════════════════════════════════════════════════════════════════

/**
 * Abrir módulo de crear herramienta
 */
function openToolsCreateModule() {
    if (!window.legoWindowManager) {
        console.error('[ToolsCrud] legoWindowManager no disponible');
        return;
    }

    window.legoWindowManager.openModuleWithMenu({
        moduleId: TOOLS_SCREEN_CONFIG.children.create,
        // parentMenuId se obtiene dinámicamente desde la BD
        label: 'Nueva Herramienta',
        url: toolsChildUrl('create'),
        icon: 'add-circle-outline'
    });

}

/**
 * Abrir módulo de editar herramienta
 */
function openToolsEditModule(recordId) {
    if (!window.legoWindowManager) {
        console.error('[ToolsCrud] legoWindowManager no disponible');
        return;
    }

    const moduleId = TOOLS_SCREEN_CONFIG.children.edit;
    const url = toolsChildUrl('edit', { id: recordId });

    const modules = window.moduleStore?.getModules() || {};
    
    if (modules[moduleId]) {

        const container = document.getElementById(`module-${moduleId}`);
        if (container) {
            document.querySelectorAll('.module-container').forEach(m => m.classList.remove('active'));
            container.classList.add('active');
            window.moduleStore._openModule(moduleId, modules[moduleId].component);

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    container.querySelectorAll('script').forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                })
                .catch(err => console.error('[ToolsCrud] Error recargando:', err));
        }
        return;
    }

    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        // parentMenuId se obtiene dinámicamente desde la BD
        label: 'Editar Herramienta',
        url: url,
        icon: 'create-outline'
    });

}

// ═══════════════════════════════════════════════════════════════════
// EXPONER FUNCIONES GLOBALMENTE
// ═══════════════════════════════════════════════════════════════════

window.openToolsCreateModule = openToolsCreateModule;
window.openToolsEditModule = openToolsEditModule;


