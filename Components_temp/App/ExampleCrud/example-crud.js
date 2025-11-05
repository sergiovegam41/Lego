/**
 * ExampleCrudV3 - Lógica de tabla (REFACTORIZADO)
 *
 * FILOSOFÍA LEGO:
 * Lógica limpia usando módulos, TableManager y ApiClient.
 * Navegación usando sistema de pestañas, no window.location.href.
 *
 * MEJORAS vs V1/V2:
 * ✅ Usa TableManager para gestión de tabla
 * ✅ Usa ApiClient con validación (no fetch directo)
 * ✅ Navegación con módulos (openCreateModule, openEditModule)
 * ✅ Custom cell renderer para acciones
 * ✅ Manejo robusto de errores
 * ✅ Sin código duplicado
 *
 * CONSISTENCIA DIMENSIONAL:
 * "Las distancias importan" - misma arquitectura que otros componentes
 */

console.log('[ExampleCrud] Inicializando...');

// ═══════════════════════════════════════════════════════════════════
// CALLBACKS PARA ROW ACTIONS
// ═══════════════════════════════════════════════════════════════════

/**
 * Callback para editar registro
 * Se ejecuta cuando el usuario hace clic en el botón "Editar"
 */
window.handleEditRecord = function(rowData, tableId) {
    console.log('[ExampleCrud] Editar registro:', rowData);

    // Abrir módulo de edición con el ID del registro
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
        // Hacer fetch al endpoint de eliminación
        const response = await fetch('/api/example-crud/delete', {
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

        // Mostrar mensaje de éxito
        if (window.AlertService) {
            await window.AlertService.success(result.msj || 'Registro eliminado correctamente');
        }

        // Recargar SOLO el módulo actual (LEGO way)
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
// CREAR INSTANCIA DE TableManager
// ═══════════════════════════════════════════════════════════════════

const tableManager = new TableManager('example-crud-table');

// ═══════════════════════════════════════════════════════════════════
// CUANDO LA TABLA ESTÉ LISTA
// ═══════════════════════════════════════════════════════════════════

tableManager.onReady(() => {
    console.log('[ExampleCrud] Tabla lista y configurada desde PHP');
    // La tabla ya viene completamente configurada desde PHP con:
    // - Columnas con anchos porcentuales
    // - cellRenderer inline para acciones
    // - valueFormatter para precio
    // No necesitamos reconfigurar nada aquí
});

// ═══════════════════════════════════════════════════════════════════
// NAVEGACIÓN - Usando sistema de módulos
// ═══════════════════════════════════════════════════════════════════

/**
 * Abrir módulo de crear registro
 *
 * IMPORTANTE: NO usar window.location.href
 * El sistema usa pestañas dinámicas con ModuleStore
 */
function openCreateModule() {
    if (!window.legoWindowManager) {
        console.error('[ExampleCrud] legoWindowManager no disponible');
        return;
    }

    const moduleId = 'example-crud-create';
    const moduleUrl = '/component/example-crud/create';

    // Abrir con ítem de menú dinámico
    // parentMenuId: '10' es el grupo "Example CRUD" (padre conceptual correcto)
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        parentMenuId: '10', // ID del grupo "Example CRUD" en el menú
        label: 'Nuevo Registro',
        url: moduleUrl,
        icon: 'add-circle-outline'
    });

    console.log('[ExampleCrud] Abriendo módulo crear con menú dinámico');
}

/**
 * Abrir módulo de editar registro
 *
 * OPCIÓN 2: Ventana de edición reutilizable única
 * Solo existe UNA ventana "Editar Registro" que reemplaza su contenido
 * cuando se edita un registro diferente.
 */
function openEditModule(recordId, recordData) {
    if (!window.legoWindowManager || !window.moduleStore) {
        console.error('[ExampleCrud] legoWindowManager o moduleStore no disponible');
        return;
    }

    // FIJO: Solo una ventana de edición reutilizable
    const moduleId = 'example-crud-edit';
    const moduleUrl = `/component/example-crud/edit?id=${recordId}`;

    // Verificar si ya existe una ventana de edición abierta
    const modules = window.moduleStore.getModules();
    if (modules[moduleId]) {
        console.log('[ExampleCrud] Ventana de edición ya existe, recargando con registro:', recordId);

        // Obtener el container del módulo
        const container = document.getElementById(`module-${moduleId}`);
        if (container) {
            // Activar el módulo
            document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
            container.classList.add('active');
            window.moduleStore._openModule(moduleId, modules[moduleId].component);

            // Actualizar breadcrumb para el módulo activo
            if (window.legoWindowManager) {
                window.legoWindowManager.updateBreadcrumbFromActiveModule();
            }

            // Recargar el contenido del módulo con nuevo registro
            fetch(moduleUrl)
                .then(res => res.text())
                .then(html => {
                    // Reemplazar contenido
                    container.innerHTML = html;

                    // Ejecutar scripts manualmente
                    const scripts = container.querySelectorAll('script');
                    scripts.forEach((oldScript) => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    console.log('[ExampleCrud] Contenido recargado para registro:', recordId);
                })
                .catch(err => {
                    console.error('[ExampleCrud] Error recargando contenido:', err);
                });
        }
        return;
    }

    // Abrir con ítem de menú dinámico (primera vez)
    // parentMenuId: '10' es el grupo "Example CRUD" (padre conceptual correcto)
    console.log('[ExampleCrud] Abriendo ventana de edición para registro:', recordId);
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        parentMenuId: '10', // ID del grupo "Example CRUD" en el menú
        label: 'Editar Registro',
        url: moduleUrl,
        icon: 'create-outline'
    });

    console.log('[ExampleCrud] Módulo editar abierto con menú dinámico');
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

    // Confirmar eliminación usando ConfirmationService
    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.delete(`registro <strong>#${recordId}</strong>`)
        : confirm('¿Estás seguro de que deseas eliminar este registro?');

    if (!confirmed) {
        console.log('[ExampleCrud] Eliminación cancelada por el usuario');
        return;
    }

    try {
        const response = await fetch('/api/example-crud/delete', {
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

        // Éxito
        if (window.AlertService) {
            await window.AlertService.success('Registro eliminado correctamente');
        } else {
            alert('Registro eliminado correctamente');
        }

        // Recargar tabla
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
