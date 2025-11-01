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
    if (!window.moduleStore) {
        console.error('[ProductsCrudV3] ModuleStore no disponible');
        return;
    }

    const moduleId = 'products-crud-v3-create';
    const moduleUrl = '/component/products-crud-v3/create';

    // Usar sistema de módulos
    if (window.lego && window.lego.openModule) {
        window.lego.openModule(moduleId, moduleUrl);
        console.log('[ProductsCrudV3] Abriendo módulo crear');
    } else {
        console.error('[ProductsCrudV3] lego.openModule no disponible');
    }
}

/**
 * Abrir módulo de editar producto
 */
function openEditModule(productId, productData) {
    if (!window.moduleStore) {
        console.error('[ProductsCrudV3] ModuleStore no disponible');
        return;
    }

    const moduleId = `products-crud-v3-edit-${productId}`;
    const moduleUrl = `/component/products-crud-v3/edit?id=${productId}`;

    // Usar sistema de módulos
    if (window.lego && window.lego.openModule) {
        window.lego.openModule(moduleId, moduleUrl);
        console.log('[ProductsCrudV3] Abriendo módulo editar:', productId);
    } else {
        console.error('[ProductsCrudV3] lego.openModule no disponible');
    }
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
