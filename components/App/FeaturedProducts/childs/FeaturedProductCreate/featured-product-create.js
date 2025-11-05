/**
 * Featured Product Create Component JavaScript
 * Handles featured product creation form logic
 */

console.log('[FeaturedProductCreate] Script loaded');

// Get HOST_NAME from window global
const HOST_NAME = window.HOST_NAME || '';

function initializeForm() {
    console.log('[FeaturedProductCreate] Initializing form...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[FeaturedProductCreate] No active module');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[FeaturedProductCreate] Module container not found');
        return;
    }

    const form = activeModuleContainer.querySelector('#featured-product-create-form');
    const submitBtn = activeModuleContainer.querySelector('#featured-product-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#featured-product-form-cancel-btn');

    if (!form) {
        console.warn('[FeaturedProductCreate] Form not found');
        return;
    }

    console.log('[FeaturedProductCreate] Form found, attaching listeners');

    // Cancel button - close window
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            console.log('[FeaturedProductCreate] Cancel clicked');
            if (window.legoWindowManager) {
                window.legoWindowManager.closeCurrentWindow();
            }
        });
    }

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('[FeaturedProductCreate] Form submitted');

        // Get form data using activeModuleContainer
        const productIdInput = activeModuleContainer.querySelector('#featured-product-product-id');
        const tagInput = activeModuleContainer.querySelector('#featured-product-tag');
        const descriptionInput = activeModuleContainer.querySelector('#featured-product-description');
        const sortOrderInput = activeModuleContainer.querySelector('#featured-product-sort-order');
        const isActiveInput = activeModuleContainer.querySelector('#featured-product-is-active');

        if (!productIdInput || !tagInput) {
            console.error('[FeaturedProductCreate] Required form fields not found');
            return;
        }

        const productId = productIdInput.value.trim();
        const tag = tagInput.value.trim();
        const description = descriptionInput?.value.trim() || '';
        const sortOrder = parseInt(sortOrderInput?.value || '0');
        const isActive = isActiveInput?.checked ?? true;

        // Validation
        if (!productId || !tag) {
            if (window.AlertService) {
                window.AlertService.error('Campos requeridos', 'Por favor completa todos los campos obligatorios');
            } else {
                alert('Por favor completa todos los campos obligatorios');
            }
            return;
        }

        // Prepare data
        const featuredProductData = {
            product_id: parseInt(productId),
            tag,
            description,
            sort_order: sortOrder,
            is_active: isActive
        };

        console.log('[FeaturedProductCreate] Featured product data:', featuredProductData);

        try {
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creando...';

            // Show loading
            if (window.legoLoading) {
                window.legoLoading.show();
            }

            // Submit to API
            const response = await fetch(`${HOST_NAME}/api/featured-products`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(featuredProductData)
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Error al crear el producto destacado');
            }

            const result = await response.json();
            console.log('[FeaturedProductCreate] Featured product created:', result);

            // Success - show message
            if (window.AlertService) {
                window.AlertService.success('Éxito', 'Producto destacado creado exitosamente');
            } else {
                alert('Producto destacado creado exitosamente');
            }

            // Close current window
            if (window.legoWindowManager) {
                window.legoWindowManager.closeCurrentWindow();

                // Navigate to featured products list and refresh table
                setTimeout(() => {
                    const menuItem = document.querySelector('[data-menu-item-id="5-1"]');
                    if (menuItem) {
                        const button = menuItem.querySelector('.custom-button');
                        if (button) {
                            button.click();

                            // Wait for table to be ready and refresh it
                            let attempts = 0;
                            const maxAttempts = 20;
                            const checkAndRefresh = () => {
                                const refreshFn = window.legoTable_featured_products_table_refresh;
                                if (refreshFn && typeof refreshFn === 'function') {
                                    console.log('[FeaturedProductCreate] Refreshing table...');
                                    refreshFn();
                                } else if (attempts < maxAttempts) {
                                    attempts++;
                                    setTimeout(checkAndRefresh, 100);
                                } else {
                                    console.warn('[FeaturedProductCreate] Could not refresh table (timeout)');
                                }
                            };
                            setTimeout(checkAndRefresh, 200);
                        }
                    }
                }, 100);
            }

        } catch (error) {
            console.error('[FeaturedProductCreate] Error creating featured product:', error);

            if (window.AlertService) {
                window.AlertService.error('Error', error.message);
            } else {
                alert(`Error al crear el producto destacado: ${error.message}`);
            }

            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Producto Destacado';
        } finally {
            // Hide loading
            if (window.legoLoading) {
                window.legoLoading.hide();
            }
        }
    });

    console.log('[FeaturedProductCreate] Form initialized successfully');
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

    const form = activeModuleContainer.querySelector('#featured-product-create-form');

    if (form) {
        console.log('[FeaturedProductCreate] Form found, initializing...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();
