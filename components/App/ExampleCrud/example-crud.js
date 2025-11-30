/**
 * ExampleCrud - Lógica de tabla
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - configuración centralizada
 * ✅ Eventos globales para comunicación reactiva
 * ✅ Navegación con módulos dinámicos
 * ✅ Persistencia de filtros entre navegaciones
 */

console.log('[ExampleCrud] Inicializando...');

// ═══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DEL COMPONENTE
// ═══════════════════════════════════════════════════════════════════

/**
 * Configuración base del componente.
 * Derivada del contexto PHP o definida aquí como fuente de verdad JS.
 * 
 * NOTA: Idealmente esto vendría 100% del PHP, pero como fallback
 * seguro, definimos los valores conocidos del componente.
 */
const COMPONENT_CONFIG = {
    id: 'example-crud',
    route: '/component/example-crud',
    apiRoute: '/api/example-crud',
    parentMenuId: 'example-crud',
    tableId: 'example-crud-table'
};

/**
 * Obtiene la configuración del componente.
 * Usa SIEMPRE la configuración local como fuente de verdad.
 */
function getConfig() {
    return COMPONENT_CONFIG;
}

/**
 * Construye URL de API
 */
function apiUrl(action, params = null) {
    const config = getConfig();
    let url = `${config.apiRoute}/${action}`;
    if (params && Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    return url;
}

/**
 * Construye URL de componente hijo
 */
function childUrl(childPath, params = null) {
    const config = getConfig();
    let url = `${config.route}/${childPath}`;
    if (params && Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    return url;
}

console.log('[ExampleCrud] Config:', getConfig());

// ═══════════════════════════════════════════════════════════════════
// CALLBACKS PARA ROW ACTIONS
// ═══════════════════════════════════════════════════════════════════

/**
 * Callback para editar registro
 * Se ejecuta cuando el usuario hace clic en el botón "Editar"
 */
window.handleEditRecord = function(rowData, tableId) {
    console.log('[ExampleCrud] Editar registro:', rowData);
    openEditModule(rowData.id);
};

/**
 * Callback para eliminar registro
 * Se ejecuta cuando el usuario hace clic en el botón "Eliminar" y confirma
 */
window.handleDeleteRecord = async function(rowData, tableId) {
    console.log('[ExampleCrud] Solicitud de eliminar registro:', rowData);

    // Confirmar eliminación usando ConfirmationService
    const itemName = `<strong>${rowData.name || 'ID: ' + rowData.id}</strong>`;
    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.delete(itemName)
        : confirm('¿Estás seguro de que deseas eliminar este registro?');

    if (!confirmed) {
        console.log('[ExampleCrud] Eliminación cancelada por el usuario');
        return;
    }

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
            throw new Error(result.msj || 'Error al eliminar registro');
        }

        // Mostrar mensaje de éxito (toast sin bloquear)
        if (window.AlertService?.toast) {
            window.AlertService.toast(result.msj || 'Registro eliminado correctamente', 'success');
        } else if (window.AlertService?.success) {
            // Fallback sin await para no bloquear
            window.AlertService.success(result.msj || 'Registro eliminado correctamente');
        }

        // Recargar INMEDIATAMENTE el módulo actual
        if (window.legoWindowManager) {
            console.log('[ExampleCrud] Recargando módulo activo...');
            window.legoWindowManager.reloadActive();
        } else {
            console.warn('[ExampleCrud] legoWindowManager no disponible, recargando página');
            window.location.reload();
        }

    } catch (error) {
        console.error('[ExampleCrud] Error eliminando registro:', error);

        if (window.AlertService) {
            await window.AlertService.error(error.message || 'Error al eliminar registro');
        } else {
            alert('Error al eliminar registro: ' + error.message);
        }
    }
};

// ═══════════════════════════════════════════════════════════════════
// PERSISTENCIA DE FILTROS (LEGO Pattern)
// 
// Usa eventos globales para:
// 1. Detectar cuando la tabla está lista → restaurar filtros
// 2. Detectar cambios de filtros → persistirlos
//
// IMPORTANTE: Determinamos el módulo propietario desde el DOM, no desde
// un registro global, para soportar múltiples instancias del mismo componente.
// ═══════════════════════════════════════════════════════════════════

/**
 * Encuentra el moduleId al que pertenece una tabla buscando en el DOM
 * @param {string} tableId - ID de la tabla
 * @returns {string|null} - El moduleId o null si no se encuentra
 */
function findTableOwnerModule(tableId) {
    const tableContainer = document.getElementById(tableId);
    if (!tableContainer) return null;
    
    // Buscar el contenedor de módulo más cercano (module-{id})
    const moduleContainer = tableContainer.closest('[id^="module-"]');
    if (!moduleContainer) return null;
    
    // Extraer el moduleId del id del contenedor (ej: "module-example-crud-list" → "example-crud-list")
    return moduleContainer.id.replace('module-', '');
}

/**
 * Restaurar filtros para una tabla específica
 * @param {string} tableId - ID de la tabla
 * @param {Object} api - AG Grid API
 */
function restoreFiltersForTable(tableId, api) {
    const ownerModuleId = findTableOwnerModule(tableId);
    if (!ownerModuleId) return;
    
    const params = window.legoWindowManager?.getParams(ownerModuleId);
    
    if (params?.columnFilters && Object.keys(params.columnFilters).length > 0) {
        console.log('[ExampleCrud] Restaurando filtros para módulo', ownerModuleId, ':', params.columnFilters);
        api.setFilterModel(params.columnFilters);
    }
}

// Evitar registrar listeners duplicados cuando el módulo se recarga
const LISTENER_KEY = `__lego_${COMPONENT_CONFIG.id}_listeners`;

if (!window[LISTENER_KEY]) {
    window[LISTENER_KEY] = true;
    
    /**
     * Restaurar filtros persistidos cuando la tabla esté lista
     */
    window.addEventListener('lego:table:ready', (event) => {
        const { tableId, api } = event.detail;
        
        // Solo procesar si es nuestra tabla (por tableId)
        if (tableId !== COMPONENT_CONFIG.tableId) return;
        
        restoreFiltersForTable(tableId, api);
    });
    
    /**
     * Persistir filtros cuando cambien
     */
    window.addEventListener('lego:table:filterChanged', (event) => {
        const { tableId, filterModel } = event.detail;
        
        // Solo procesar si es nuestra tabla (por tableId)
        if (tableId !== COMPONENT_CONFIG.tableId) return;
        
        // Determinar el módulo propietario desde el DOM
        const ownerModuleId = findTableOwnerModule(tableId);
        if (!ownerModuleId) return;
        
        if (window.legoWindowManager) {
            if (Object.keys(filterModel).length > 0) {
                window.legoWindowManager.setParam('columnFilters', filterModel, ownerModuleId);
            } else {
                window.legoWindowManager.removeParam('columnFilters', ownerModuleId);
            }
        }
    });
    
    console.log('[ExampleCrud] Listeners de filtros registrados');
}

// ═══════════════════════════════════════════════════════════════════
// VERIFICAR SI LA TABLA YA ESTÁ LISTA (race condition fix)
// Si el evento lego:table:ready ya se disparó antes de registrar el
// listener, verificamos manualmente y restauramos filtros.
// ═══════════════════════════════════════════════════════════════════

// Usar setTimeout para asegurar que el DOM esté actualizado
setTimeout(() => {
    const tableId = COMPONENT_CONFIG.tableId;
    const existingTable = window.LEGO_TABLES?.[tableId];
    
    if (existingTable?.api) {
        console.log('[ExampleCrud] Tabla ya existía, verificando filtros pendientes...');
        restoreFiltersForTable(tableId, existingTable.api);
    }
}, 0);


// ═══════════════════════════════════════════════════════════════════
// NAVEGACIÓN - Usando configuración del componente
// ═══════════════════════════════════════════════════════════════════

/**
 * Abrir módulo de crear registro
 */
function openCreateModule() {
    if (!window.legoWindowManager) {
        console.error('[ExampleCrud] legoWindowManager no disponible');
        return;
    }

    const config = getConfig();

    window.legoWindowManager.openModuleWithMenu({
        moduleId: `${config.id}-create`,
        parentMenuId: config.parentMenuId,
        label: 'Nuevo Registro',
        url: childUrl('create'),
        icon: 'add-circle-outline'
    });

    console.log('[ExampleCrud] Abriendo módulo crear');
}

/**
 * Abrir módulo de editar registro
 * Ventana de edición reutilizable única
 */
function openEditModule(recordId) {
    if (!window.legoWindowManager) {
        console.error('[ExampleCrud] legoWindowManager no disponible');
        return;
    }

    const config = getConfig();
    const moduleId = `${config.id}-edit`;
    const url = childUrl('edit', { id: recordId });

    // Verificar si ya existe una ventana de edición abierta
    const modules = window.moduleStore?.getModules() || {};
    
    if (modules[moduleId]) {
        console.log('[ExampleCrud] Ventana de edición ya existe, recargando con registro:', recordId);

        // Activar el módulo existente
        const container = document.getElementById(`module-${moduleId}`);
        if (container) {
            document.querySelectorAll('.module-container').forEach(m => m.classList.remove('active'));
            container.classList.add('active');
            window.moduleStore._openModule(moduleId, modules[moduleId].component);

            // Recargar contenido
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    // Re-ejecutar scripts
                    container.querySelectorAll('script').forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                    console.log('[ExampleCrud] Contenido recargado para registro:', recordId);
                })
                .catch(err => console.error('[ExampleCrud] Error recargando:', err));
        }
        return;
    }

    // Abrir nuevo módulo dinámico
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        parentMenuId: config.parentMenuId,
        label: 'Editar Registro',
        url: url,
        icon: 'create-outline'
    });

    console.log('[ExampleCrud] Módulo editar abierto');
}

/**
 * Cerrar módulo actual y volver a tabla
 */
function closeCurrentModule() {
    if (!window.moduleStore) {
        console.error('[ExampleCrud] ModuleStore no disponible');
        return;
    }

    const currentModule = window.moduleStore.getActiveModule();
    if (currentModule && window.lego && window.lego.closeModule) {
        window.lego.closeModule(currentModule);
        console.log('[ExampleCrud] Módulo cerrado:', currentModule);
    }
}

/**
 * Editar registro (llamado desde botón de acciones)
 */
function editRecord(recordId) {
    console.log('[ExampleCrud] Editar registro:', recordId);
    openEditModule(recordId);
}

/**
 * Eliminar registro (llamado desde botón de acciones - LEGACY)
 */
async function deleteRecord(recordId) {
    console.log('[ExampleCrud] Solicitud de eliminar registro:', recordId);

    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.delete(`registro <strong>#${recordId}</strong>`)
        : confirm('¿Estás seguro de que deseas eliminar este registro?');

    if (!confirmed) {
        console.log('[ExampleCrud] Eliminación cancelada por el usuario');
        return;
    }

    try {
        const response = await fetch(apiUrl('delete'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: recordId })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar registro');
        }

        // Mostrar mensaje de éxito (toast sin bloquear)
        if (window.AlertService?.toast) {
            window.AlertService.toast('Registro eliminado correctamente', 'success');
        } else if (window.AlertService?.success) {
            window.AlertService.success('Registro eliminado correctamente');
        }

        // Recargar INMEDIATAMENTE
        if (window.legoWindowManager) {
            window.legoWindowManager.reloadActive();
        }

    } catch (error) {
        console.error('[ExampleCrud] Error eliminando registro:', error);

        if (window.AlertService) {
            await window.AlertService.error(error.message || 'Error al eliminar registro');
        } else {
            alert('Error eliminando registro: ' + error.message);
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// EXPONER FUNCIONES GLOBALMENTE
// ═══════════════════════════════════════════════════════════════════

window.openCreateModule = openCreateModule;
window.openEditModule = openEditModule;
window.closeCurrentModule = closeCurrentModule;
window.editRecord = editRecord;
window.deleteRecord = deleteRecord;

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

console.log('[ExampleCrud] Sistema listo');
