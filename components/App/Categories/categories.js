/**
 * Categories - Lógica de tabla
 * Basado en ExampleCrud V3
 */

console.log('[Categories] Inicializando...');

// Cargar ImageCarousel utility si no está cargado
if (!window.openImageCarousel) {
    const script = document.createElement('script');
    script.src = '/assets/js/utils/ImageCarousel.js';
    document.head.appendChild(script);
    console.log('[Categories] Cargando ImageCarousel...');
}

// Callbacks para RowActions
window.handleEditCategory = function(rowData, tableId) {
    console.log('[Categories] Editar categoría:', rowData);
    openEditCategoryModule(rowData.id);
};

window.handleDeleteCategory = async function(rowData, tableId) {
    console.log('[Categories] Solicitud de eliminar categoría:', rowData);

    const itemName = `<strong>${rowData.name || 'ID: ' + rowData.id}</strong>`;
    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.delete(itemName)
        : confirm('¿Estás seguro de que deseas eliminar esta categoría?');

    if (!confirmed) {
        console.log('[Categories] Eliminación cancelada');
        return;
    }

    try {
        const response = await fetch('/api/categories/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: rowData.id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || result.message || 'Error al eliminar categoría');
        }

        if (window.AlertService) {
            await window.AlertService.success(result.msj || result.message || 'Categoría eliminada');
        }

        if (window.legoWindowManager) {
            window.legoWindowManager.reloadActive();
        }

    } catch (error) {
        console.error('[Categories] Error eliminando:', error);
        if (window.AlertService) {
            await window.AlertService.error(error.message || 'Error al eliminar categoría');
        } else {
            alert('Error: ' + error.message);
        }
    }
};

// TableManager
const tableManager = new TableManager('categories-table');

tableManager.onReady(() => {
    console.log('[Categories] Tabla lista');
});

// Navegación
function openCreateCategoryModule() {
    console.log('[Categories] Navegando a Crear Categoría...');

    // Navegar al menú existente "Crear Categoría" (ID: 2-2)
    const menuItem = document.querySelector('[data-menu-item-id="2-2"]');
    if (menuItem) {
        const button = menuItem.querySelector('.custom-button');
        if (button) {
            button.click();
        }
    } else {
        console.error('[Categories] Menú "Crear Categoría" no encontrado');
    }
}

function openEditCategoryModule(categoryId) {
    if (!window.legoWindowManager || !window.moduleStore) {
        console.error('[Categories] legoWindowManager o moduleStore no disponible');
        return;
    }

    console.log('[Categories] Abriendo editar categoría:', categoryId);

    // FIJO: Solo una ventana de edición reutilizable
    const moduleId = 'categories-edit';
    const moduleUrl = `/component/categories/edit?id=${categoryId}`;

    // Verificar si ya existe una ventana de edición abierta
    const modules = window.moduleStore.getModules();
    if (modules[moduleId]) {
        console.log('[Categories] Ventana de edición ya existe, recargando con categoría:', categoryId);

        // Obtener el container del módulo
        const container = document.getElementById(`module-${moduleId}`);
        if (container) {
            // Activar el módulo
            document.querySelectorAll('.module-container').forEach(m => m.classList.remove('active'));
            container.classList.add('active');
            window.moduleStore._openModule(moduleId, modules[moduleId].component);

            // Actualizar breadcrumb
            if (window.legoWindowManager) {
                window.legoWindowManager.updateBreadcrumbFromActiveModule();
            }

            // Recargar contenido con el nuevo ID
            fetch(moduleUrl)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    // Re-ejecutar scripts
                    const scripts = container.querySelectorAll('script');
                    scripts.forEach((oldScript) => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                });
        }
        return;
    }

    // Si no existe, crear nueva ventana
    window.legoWindowManager.openModuleWithMenu({
        moduleId: moduleId,
        parentMenuId: '2-1',
        label: 'Editar Categoría',
        url: moduleUrl,
        icon: 'create-outline'
    });

    console.log('[Categories] Módulo editar abierto');
}

window.openCreateCategoryModule = openCreateCategoryModule;
window.openEditCategoryModule = openEditCategoryModule;

console.log('[Categories] Sistema listo');
