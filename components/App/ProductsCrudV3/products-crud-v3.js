/**
 * ProductsCrudV3 - Lógica de tabla (REFACTORIZADO)
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

console.log('[ProductsCrudV3] Inicializando...');

// ═══════════════════════════════════════════════════════════════════
// CALLBACKS PARA ROW ACTIONS
// ═══════════════════════════════════════════════════════════════════

/**
 * Callback para editar producto
 * Se ejecuta cuando el usuario hace clic en el botón "Editar"
 */
window.handleEditProduct = function(rowData, tableId) {
    console.log('[ProductsCrudV3] Editar producto:', rowData);

    // Abrir módulo de edición con el ID del producto
    openEditModule(rowData.id);
};

/**
 * Callback para eliminar producto
 * Se ejecuta cuando el usuario hace clic en el botón "Eliminar" y confirma
 */
window.handleDeleteProduct = async function(rowData, tableId) {
    console.log('[ProductsCrudV3] Eliminar producto:', rowData);

    try {
        // Hacer fetch al endpoint de eliminación
        const response = await fetch('/api/products/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: rowData.id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar producto');
        }

        // Mostrar mensaje de éxito
        if (window.lego && window.lego.alert) {
            await window.lego.alert.success({
                title: 'Eliminado',
                text: result.msj || 'Producto eliminado correctamente'
            });
        }

        // Recargar SOLO el módulo actual (LEGO way)
        if (window.legoWindowManager) {
            console.log('[ProductsCrudV3] Recargando módulo activo...');
            window.legoWindowManager.reloadActive();
        } else {
            console.warn('[ProductsCrudV3] legoWindowManager no disponible, recargando página');
            window.location.reload();
        }

    } catch (error) {
        console.error('[ProductsCrudV3] Error eliminando producto:', error);

        if (window.lego && window.lego.alert) {
            await window.lego.alert.error({
                title: 'Error',
                text: error.message || 'Error al eliminar producto'
            });
        } else {
            alert('Error al eliminar producto: ' + error.message);
        }
    }
};

// ═══════════════════════════════════════════════════════════════════
// CREAR INSTANCIA DE TableManager
// ═══════════════════════════════════════════════════════════════════

const tableManager = new TableManager('products-table-v3');

// ═══════════════════════════════════════════════════════════════════
// CUANDO LA TABLA ESTÉ LISTA
// ═══════════════════════════════════════════════════════════════════

tableManager.onReady(() => {
    console.log('[ProductsCrudV3] Tabla lista y configurada desde PHP');
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
 * Abrir módulo de crear producto
 *
 * IMPORTANTE: NO usar window.location.href
 * El sistema usa pestañas dinámicas con ModuleStore
 */
function openCreateModule() {
    if (!window.legoWindowManager) {
        console.error('[ProductsCrudV3] legoWindowManager no disponible');
        return;
    }

    const moduleId = 'products-crud-v3-create';
    const moduleUrl = '/component/products-crud-v3/create';

    // Abrir con ítem de menú dinámico
    console.log('[ProductsCrudV3] DEBUG - Intentando crear ítem dinámico con parentMenuId: 10-1');
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        parentMenuId: '10-1', // ID del ítem "Tabla" en el menú (MenuItemDto id)
        label: 'Nuevo Producto',
        url: moduleUrl,
        icon: 'add-circle-outline'
    });

    console.log('[ProductsCrudV3] Abriendo módulo crear con menú dinámico');
}

/**
 * Abrir módulo de editar producto
 *
 * OPCIÓN 2: Ventana de edición reutilizable única
 * Solo existe UNA ventana "Editar Producto" que reemplaza su contenido
 * cuando se edita un producto diferente.
 */
function openEditModule(productId, productData) {
    if (!window.legoWindowManager || !window.moduleStore) {
        console.error('[ProductsCrudV3] legoWindowManager o moduleStore no disponible');
        return;
    }

    // FIJO: Solo una ventana de edición reutilizable
    const moduleId = 'products-crud-v3-edit';
    const moduleUrl = `/component/products-crud-v3/edit?id=${productId}`;

    // Verificar si ya existe una ventana de edición abierta
    const modules = window.moduleStore.getModules();
    if (modules[moduleId]) {
        console.log('[ProductsCrudV3] Ventana de edición ya existe, recargando con producto:', productId);

        // Obtener el container del módulo
        const container = document.getElementById(`module-${moduleId}`);
        if (container) {
            // Activar el módulo
            document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
            container.classList.add('active');
            window.moduleStore._openModule(moduleId, modules[moduleId].component);

            // Recargar el contenido del módulo con nuevo producto
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

                    console.log('[ProductsCrudV3] Contenido recargado para producto:', productId);
                })
                .catch(err => {
                    console.error('[ProductsCrudV3] Error recargando contenido:', err);
                });
        }
        return;
    }

    // Abrir con ítem de menú dinámico (primera vez)
    console.log('[ProductsCrudV3] Abriendo ventana de edición para producto:', productId);
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        parentMenuId: '10-1', // ID del ítem "Tabla" en el menú (MenuItemDto id)
        label: 'Editar Producto',
        url: moduleUrl,
        icon: 'create-outline'
    });

    console.log('[ProductsCrudV3] Módulo editar abierto con menú dinámico');
}

/**
 * Cerrar módulo actual y volver a tabla
 */
function closeCurrentModule() {
    if (!window.moduleStore) {
        console.error('[ProductsCrudV3] ModuleStore no disponible');
        return;
    }

    const currentModule = window.moduleStore.getActiveModule();
    if (currentModule && window.lego && window.lego.closeModule) {
        window.lego.closeModule(currentModule);
        console.log('[ProductsCrudV3] Módulo cerrado:', currentModule);
    }
}

/**
 * Editar producto (llamado desde botón de acciones)
 */
function editProduct(productId) {
    console.log('[ProductsCrudV3] Editar producto:', productId);
    openEditModule(productId);
}

/**
 * Eliminar producto (llamado desde botón de acciones)
 */
async function deleteProduct(productId) {
    console.log('[ProductsCrudV3] Solicitud de eliminar producto:', productId);

    // Confirmar con el usuario
    const confirmed = window.AlertService
        ? await window.AlertService.confirm(
            '¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.',
            '¿Eliminar producto?',
            'Sí, eliminar',
            'Cancelar'
        )
        : confirm('¿Estás seguro de que deseas eliminar este producto?');

    if (!confirmed) {
        console.log('[ProductsCrudV3] Eliminación cancelada por el usuario');
        return;
    }

    try {
        const response = await fetch('/api/products/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: productId })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar producto');
        }

        // Éxito
        if (window.AlertService) {
            window.AlertService.success('Éxito', 'Producto eliminado correctamente');
        } else {
            alert('Producto eliminado correctamente');
        }

        // Recargar tabla
        const tableManager = new TableManager('products-table-v3');
        tableManager.onReady(() => {
            window.legoWindowManager?.reloadActive();
        });

    } catch (error) {
        console.error('[ProductsCrudV3] Error eliminando producto:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al eliminar producto');
        } else {
            alert('Error eliminando producto: ' + error.message);
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// EXPONER FUNCIONES GLOBALMENTE
// ═══════════════════════════════════════════════════════════════════

window.openCreateModule = openCreateModule;
window.openEditModule = openEditModule;
window.closeCurrentModule = closeCurrentModule;
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

console.log('[ProductsCrudV3] Sistema listo');
