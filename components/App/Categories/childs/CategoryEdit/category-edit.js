/**
 * Category Edit - Similar a ExampleEdit
 */

console.log('[CategoryEdit] Script cargado');

async function loadCategory(categoryId) {
    try {
        const response = await fetch(`/api/categories/get?id=${categoryId}`);
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al cargar categoría');
        }

        return result.data;
    } catch (error) {
        console.error('[CategoryEdit] Error cargando:', error);
        throw error;
    }
}

async function updateCategory(categoryId, formData) {
    try {
        const response = await fetch('/api/categories/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...formData, id: categoryId })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al actualizar');
        }

        return result.data;
    } catch (error) {
        console.error('[CategoryEdit] Error:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message);
        }
        throw error;
    }
}

async function initializeForm() {
    console.log('[CategoryEdit] Inicializando formulario...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[CategoryEdit] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[CategoryEdit] No se encontró container del módulo');
        return;
    }

    const form = activeModuleContainer.querySelector('.category-form');
    if (!form) {
        console.warn('[CategoryEdit] Formulario no encontrado en módulo activo');
        return;
    }

    const categoryId = parseInt(form.dataset.categoryId);
    if (!categoryId) {
        console.error('[CategoryEdit] ID de categoría no válido');
        return;
    }

    const loadingDiv = activeModuleContainer.querySelector('#category-form-loading');
    const formEl = activeModuleContainer.querySelector('#category-edit-form');

    try {
        const category = await loadCategory(categoryId);
        console.log('[CategoryEdit] Categoría cargada:', category);

        const nameInput = activeModuleContainer.querySelector('#category-name');
        const descInput = activeModuleContainer.querySelector('#category-description');

        if (nameInput) nameInput.value = category.name || '';
        if (descInput) descInput.value = category.description || '';

        // Las imágenes ya se cargan automáticamente via initialImages en FilePond

        if (loadingDiv) loadingDiv.style.display = 'none';
        if (formEl) formEl.style.display = 'block';

        formEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('[CategoryEdit] Enviando formulario...');

            const submitBtn = activeModuleContainer.querySelector('#category-form-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Guardando...';
            }

            try {
                const formData = {
                    name: activeModuleContainer.querySelector('#category-name')?.value || '',
                    description: activeModuleContainer.querySelector('#category-description')?.value || ''
                };

                const imageIds = window.FilePondComponent?.getImageIds('category-image') || [];
                if (imageIds.length > 0) {
                    formData.image_ids = imageIds;
                }

                console.log('[CategoryEdit] Datos a enviar:', formData);
                await updateCategory(categoryId, formData);

                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Categoría actualizada');
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

                                // Refrescar tabla después de que se abra el módulo
                                setTimeout(() => {
                                    if (window.legoWindowManager) {
                                        window.legoWindowManager.reloadActive();
                                    }
                                }, 300);
                            }
                        }
                    }, 100);
                }

            } catch (error) {
                console.error('[CategoryEdit] Error en submit:', error);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Guardar Cambios';
                }
            }
        });

        const cancelBtn = activeModuleContainer.querySelector('#category-form-cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                if (window.legoWindowManager) {
                    window.legoWindowManager.closeCurrentWindow();
                }
            });
        }

        console.log('[CategoryEdit] Formulario inicializado correctamente');

    } catch (error) {
        console.error('[CategoryEdit] Error al cargar:', error);
        if (loadingDiv) {
            loadingDiv.textContent = 'Error al cargar categoría: ' + error.message;
        }
    }
}

let attempts = 0;
const maxAttempts = 50;

function tryInit() {
    const activeModuleId = window.moduleStore?.getActiveModule();

    if (!activeModuleId) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInit, 50);
        } else {
            console.error('[CategoryEdit] Timeout: No se encontró módulo activo');
        }
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInit, 50);
        } else {
            console.error('[CategoryEdit] Timeout: No se encontró container del módulo');
        }
        return;
    }

    const form = activeModuleContainer.querySelector('.category-form');

    if (form) {
        console.log('[CategoryEdit] Formulario encontrado, inicializando...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInit, 50);
    } else {
        console.error('[CategoryEdit] Timeout: Formulario no encontrado después de', maxAttempts, 'intentos');
    }
}

tryInit();
