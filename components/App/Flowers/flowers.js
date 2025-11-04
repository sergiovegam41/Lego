/**
 * Flowers Component - Main table management
 */

console.log('[Flowers] Script cargado');

// Cargar ImageCarousel utility si no está cargado
if (!window.openImageCarousel) {
    const script = document.createElement('script');
    script.src = '/assets/js/utils/ImageCarousel.js';
    document.head.appendChild(script);
    console.log('[Flowers] Cargando ImageCarousel...');
}

// TableManager - debe ser accesible globalmente para el delete handler
const tableManager = new TableManager('flowers-table');

tableManager.onReady(() => {
    console.log('[Flowers] Tabla lista');
});

// Función global de refresh para compatibilidad
window.legoTable_flowers_table_refresh = function() {
    console.log('[Flowers] Refrescando tabla...');
    tableManager.refreshData();
};

window.handleEditFlower = function(rowData, tableId) {
    console.log('[Flowers] Editar flor:', rowData);
    openEditFlowerModule(rowData.id);
};

window.handleDeleteFlower = async function(rowData, tableId) {
    console.log('[Flowers] Eliminar flor:', rowData);

    const confirmDelete = window.AlertService
        ? await window.AlertService.confirm(
            '¿Eliminar flor?',
            `¿Estás seguro de eliminar "${rowData.name}"?`,
            'Eliminar',
            'Cancelar'
        )
        : confirm(`¿Estás seguro de eliminar "${rowData.name}"?`);

    if (!confirmDelete) return;

    try {
        const response = await fetch('/api/flowers/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: rowData.id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar');
        }

        if (window.AlertService) {
            window.AlertService.success('Éxito', 'Flor eliminada correctamente');
        }

        tableManager.refreshData();

    } catch (error) {
        console.error('[Flowers] Error al eliminar:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message);
        }
    }
};

function openCreateFlowerModule() {
    console.log('[Flowers] Navegando a Crear Flor...');

    // Navegar al menú existente "Crear Flor" (ID: 3-2)
    const menuItem = document.querySelector('[data-menu-item-id="3-2"]');
    if (menuItem) {
        const button = menuItem.querySelector('.custom-button');
        if (button) {
            button.click();
        }
    } else {
        console.error('[Flowers] Menú "Crear Flor" no encontrado');
    }
}

function openEditFlowerModule(flowerId) {
    console.log('[Flowers] Abriendo módulo de edición:', flowerId);

    if (!window.legoWindowManager || !window.moduleStore) {
        console.error('[Flowers] legoWindowManager o moduleStore no disponible');
        return;
    }

    // FIJO: Solo una ventana de edición reutilizable
    const moduleId = 'flowers-edit';
    const moduleUrl = `/component/flowers/edit?id=${flowerId}`;

    // Verificar si ya existe una ventana de edición abierta
    const modules = window.moduleStore.getModules();
    if (modules[moduleId]) {
        console.log('[Flowers] Ventana de edición ya existe, recargando con flor:', flowerId);

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
        parentMenuId: '3-1',
        label: 'Editar Flor',
        url: moduleUrl,
        icon: 'create-outline'
    });
}

window.openCreateFlowerModule = openCreateFlowerModule;
window.openEditFlowerModule = openEditFlowerModule;

console.log('[Flowers] Sistema listo');
