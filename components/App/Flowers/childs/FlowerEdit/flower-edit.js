/**
 * Flower Edit
 */

console.log('[FlowerEdit] Script cargado');

async function loadFlower(flowerId) {
    try {
        const response = await fetch(`/api/flowers/get?id=${flowerId}`);
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al cargar flor');
        }

        return result.data;
    } catch (error) {
        console.error('[FlowerEdit] Error cargando:', error);
        throw error;
    }
}

async function updateFlower(flowerId, formData) {
    try {
        const response = await fetch('/api/flowers/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...formData, id: flowerId })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al actualizar');
        }

        return result.data;
    } catch (error) {
        console.error('[FlowerEdit] Error:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message);
        }
        throw error;
    }
}

async function initializeForm() {
    console.log('[FlowerEdit] Inicializando formulario...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[FlowerEdit] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[FlowerEdit] No se encontró container del módulo');
        return;
    }

    const form = activeModuleContainer.querySelector('.flower-form');
    if (!form) {
        console.warn('[FlowerEdit] Formulario no encontrado en módulo activo');
        return;
    }

    const flowerId = parseInt(form.dataset.flowerId);
    if (!flowerId) {
        console.error('[FlowerEdit] ID de flor no válido');
        return;
    }

    const loadingDiv = activeModuleContainer.querySelector('#flower-form-loading');
    const formEl = activeModuleContainer.querySelector('#flower-edit-form');

    try {
        const flower = await loadFlower(flowerId);
        console.log('[FlowerEdit] Flor cargada:', flower);

        const nameInput = activeModuleContainer.querySelector('#flower-name');
        const priceInput = activeModuleContainer.querySelector('#flower-price');
        const descInput = activeModuleContainer.querySelector('#flower-description');

        if (nameInput) nameInput.value = flower.name || '';
        if (priceInput) priceInput.value = flower.price || '0';

        if (flower.category_id && window.LegoSelect) {
            window.LegoSelect.setValue('flower-category', flower.category_id);
        }

        if (descInput && flower.description) {
            descInput.value = flower.description;
        }

        // Las imágenes ya se cargan automáticamente via initialImages en FilePond

        if (loadingDiv) loadingDiv.style.display = 'none';
        if (formEl) formEl.style.display = 'block';

        formEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('[FlowerEdit] Enviando formulario...');

            const submitBtn = activeModuleContainer.querySelector('#flower-form-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Guardando...';
            }

            try {
                const formData = {
                    name: activeModuleContainer.querySelector('#flower-name')?.value || '',
                    price: parseFloat(activeModuleContainer.querySelector('#flower-price')?.value || '0'),
                    category_id: window.LegoSelect?.getValue('flower-category') || null,
                    description: activeModuleContainer.querySelector('#flower-description')?.value || ''
                };

                const imageIds = window.FilePondComponent?.getImageIds('flower-images') || [];
                if (imageIds.length > 0) {
                    formData.image_ids = imageIds;
                }

                console.log('[FlowerEdit] Datos a enviar:', formData);
                await updateFlower(flowerId, formData);

                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Flor actualizada');
                }

                // Cerrar ventana actual y navegar a "Ver Flores"
                if (window.legoWindowManager) {
                    window.legoWindowManager.closeCurrentWindow();

                    // Navegar al menú "Ver Flores" (ID: 3-1)
                    setTimeout(() => {
                        const menuItem = document.querySelector('[data-menu-item-id="3-1"]');
                        if (menuItem) {
                            const button = menuItem.querySelector('.custom-button');
                            if (button) {
                                button.click();

                                // Refrescar tabla después de que se abra el módulo
                                setTimeout(() => {
                                    const refreshFn = window.legoTable_flowers_table_refresh;
                                    if (refreshFn) refreshFn();
                                }, 300);
                            }
                        }
                    }, 100);
                }

            } catch (error) {
                console.error('[FlowerEdit] Error en submit:', error);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Guardar Cambios';
                }
            }
        });

        const cancelBtn = activeModuleContainer.querySelector('#flower-form-cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                if (window.legoWindowManager) {
                    window.legoWindowManager.closeCurrentWindow();
                }
            });
        }

        console.log('[FlowerEdit] Formulario inicializado correctamente');

    } catch (error) {
        console.error('[FlowerEdit] Error al cargar:', error);
        if (loadingDiv) {
            loadingDiv.textContent = 'Error al cargar flor: ' + error.message;
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
            console.error('[FlowerEdit] Timeout: No se encontró módulo activo');
        }
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInit, 50);
        } else {
            console.error('[FlowerEdit] Timeout: No se encontró container del módulo');
        }
        return;
    }

    const form = activeModuleContainer.querySelector('.flower-form');

    if (form) {
        console.log('[FlowerEdit] Formulario encontrado, inicializando...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInit, 50);
    } else {
        console.error('[FlowerEdit] Timeout: Formulario no encontrado después de', maxAttempts, 'intentos');
    }
}

tryInit();
