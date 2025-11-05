/**
 * Flower Create
 */

console.log('[FlowerCreate] Script cargado');

async function createFlower(formData) {
    try {
        const response = await fetch('/api/flowers/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || result.message || 'Error al crear flor');
        }

        console.log('[FlowerCreate] Flor creada:', result.data);
        return result.data;

    } catch (error) {
        console.error('[FlowerCreate] Error:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message);
        } else {
            alert('Error: ' + error.message);
        }
        throw error;
    }
}

function initializeForm() {
    console.log('[FlowerCreate] Inicializando formulario...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[FlowerCreate] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[FlowerCreate] No se encontró container');
        return;
    }

    const form = activeModuleContainer.querySelector('#flower-create-form');
    const submitBtn = activeModuleContainer.querySelector('#flower-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#flower-form-cancel-btn');

    if (!form) {
        console.warn('[FlowerCreate] Formulario no encontrado');
        return;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        console.log('[FlowerCreate] Enviando formulario...');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Creando...';

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

            console.log('[FlowerCreate] Datos:', formData);

            const newFlower = await createFlower(formData);

            if (newFlower) {
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Flor creada correctamente');
                } else {
                    alert('Flor creada correctamente');
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

                                // Esperar a que la tabla esté lista y refrescarla
                                let attempts = 0;
                                const maxAttempts = 20;
                                const checkAndRefresh = () => {
                                    const refreshFn = window.legoTable_flowers_table_refresh;
                                    if (refreshFn && typeof refreshFn === 'function') {
                                        console.log('[FlowerCreate] Refrescando tabla...');
                                        refreshFn();
                                    } else if (attempts < maxAttempts) {
                                        attempts++;
                                        setTimeout(checkAndRefresh, 100);
                                    } else {
                                        console.warn('[FlowerCreate] No se pudo refrescar tabla (timeout)');
                                    }
                                };
                                setTimeout(checkAndRefresh, 200);
                            }
                        }
                    }, 100);
                }
            }

        } catch (error) {
            console.error('[FlowerCreate] Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Flor';
        }
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            if (window.legoWindowManager) {
                window.legoWindowManager.closeCurrentWindow();
            }
        });
    }

    console.log('[FlowerCreate] Formulario inicializado');
}

let attempts = 0;
const maxAttempts = 50;

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

    const form = activeModuleContainer.querySelector('#flower-create-form');

    if (form) {
        console.log('[FlowerCreate] Formulario encontrado, inicializando...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();
