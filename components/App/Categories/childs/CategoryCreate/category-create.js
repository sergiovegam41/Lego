/**
 * Category Create - Basado en ExampleCreate
 */

console.log('[CategoryCreate] Script cargado');

async function createCategory(formData) {
    try {
        const response = await fetch('/api/categories/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || result.message || 'Error al crear categoría');
        }

        console.log('[CategoryCreate] Categoría creada:', result.data);
        return result.data;

    } catch (error) {
        console.error('[CategoryCreate] Error:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message);
        } else {
            alert('Error: ' + error.message);
        }
        throw error;
    }
}

function initializeForm() {
    console.log('[CategoryCreate] Inicializando formulario...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[CategoryCreate] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[CategoryCreate] No se encontró container');
        return;
    }

    const form = activeModuleContainer.querySelector('#category-create-form');
    const submitBtn = activeModuleContainer.querySelector('#category-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#category-form-cancel-btn');

    if (!form) {
        console.warn('[CategoryCreate] Formulario no encontrado');
        return;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        console.log('[CategoryCreate] Enviando formulario...');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Creando...';

        try {
            const formData = {
                name: activeModuleContainer.querySelector('#category-name')?.value || '',
                description: activeModuleContainer.querySelector('#category-description')?.value || ''
            };

            const imageIds = window.FilePondComponent?.getImageIds('category-image') || [];
            if (imageIds.length > 0) {
                formData.image_ids = imageIds;
            }

            console.log('[CategoryCreate] Datos:', formData);

            const newCategory = await createCategory(formData);

            if (newCategory) {
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Categoría creada correctamente');
                } else {
                    alert('Categoría creada correctamente');
                }

                // Cerrar ventana actual y navegar a "Ver Categorías"
                if (window.legoWindowManager) {
                    window.legoWindowManager.closeCurrentWindow();

                    // Navegar al menú "Ver Categorías" (ID: 2-1)
                    setTimeout(() => {
                        const menuItem = document.querySelector('[data-menu-item-id="2-1"]');
                        if (menuItem) {
                            const button = menuItem.querySelector('.custom-button');
                            if (button) {
                                button.click();

                                // Esperar a que la tabla esté lista y refrescarla
                                let attempts = 0;
                                const maxAttempts = 20;
                                const checkAndRefresh = () => {
                                    const refreshFn = window.legoTable_categories_table_refresh;
                                    if (refreshFn && typeof refreshFn === 'function') {
                                        console.log('[CategoryCreate] Refrescando tabla...');
                                        refreshFn();
                                    } else if (attempts < maxAttempts) {
                                        attempts++;
                                        setTimeout(checkAndRefresh, 100);
                                    } else {
                                        console.warn('[CategoryCreate] No se pudo refrescar tabla (timeout)');
                                        // Fallback: reload complete module
                                        if (window.legoWindowManager) {
                                            window.legoWindowManager.reloadActive();
                                        }
                                    }
                                };
                                setTimeout(checkAndRefresh, 200);
                            }
                        }
                    }, 100);
                }
            }

        } catch (error) {
            console.error('[CategoryCreate] Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Categoría';
        }
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            if (window.legoWindowManager) {
                window.legoWindowManager.closeCurrentWindow();
            }
        });
    }

    console.log('[CategoryCreate] Formulario inicializado');
}

let attempts = 0;
const maxAttempts = 40;

function tryInitialize() {
    const activeModuleId = window.moduleStore?.getActiveModule();

    if (!activeModuleId) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInitialize, 50);
        }
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInitialize, 50);
        }
        return;
    }

    const form = activeModuleContainer.querySelector('#category-create-form');

    if (form) {
        console.log('[CategoryCreate] Formulario encontrado, inicializando...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();
